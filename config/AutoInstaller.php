<?php
/**
 * CineWeeknd — Auto Installer
 * Roda silenciosamente na primeira vez que o site é acessado.
 * Após instalar, cria o arquivo /storage/installed.lock
 * e nunca mais executa.
 */

class AutoInstaller {

    private static string $lockFile = '';

    // Flag acessível globalmente para mostrar loader
    public static bool $isFirstInstall = false;

    public static function boot(): void {
        self::$lockFile = APP_ROOT . '/storage/installed.lock';

        // Já foi instalado? Verifica se precisa buscar posters pendentes
        if (file_exists(self::$lockFile)) {
            self::fetchPendingPosters();
            return;
        }

        self::$isFirstInstall = true;

        // Tenta instalar (silenciosamente — erros vão para error_log)
        try {
            self::install();
        } catch (\Throwable $e) {
            error_log('[CineWeeknd AutoInstaller] ' . $e->getMessage());
        }
    }

    private static function fetchPendingPosters(): void {
        $flag = APP_ROOT . '/storage/fetch_posters.flag';
        if (!file_exists($flag)) return;

        // Remove flag imediatamente para não rodar duas vezes
        unlink($flag);

        // Busca posters dos filmes que não têm
        try {
            require_once APP_ROOT . '/src/Services/TMDBService.php';
            $db   = getDB();
            $rows = $db->query("SELECT id, title, slug, release_year FROM movies WHERE (poster IS NULL OR poster = '') LIMIT 20")->fetchAll();
            foreach ($rows as $movie) {
                $file = TMDBService::fetchPosterForMovie($movie);
                if ($file) {
                    $db->prepare("UPDATE movies SET poster = ? WHERE id = ?")->execute([$file, $movie['id']]);
                }
                usleep(250000); // 0.25s entre requests
            }
            // Se ainda tiver filmes sem poster, agenda nova rodada
            $remaining = (int)$db->query("SELECT COUNT(*) FROM movies WHERE poster IS NULL OR poster = ''")->fetchColumn();
            if ($remaining > 0) {
                file_put_contents(APP_ROOT . '/storage/fetch_posters.flag', '1');
            }
        } catch (\Throwable $e) {
            error_log('[AutoInstaller Posters] ' . $e->getMessage());
        }
    }

    private static function install(): void {
        // Cria pasta storage se não existir
        $storageDir = APP_ROOT . '/storage';
        if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);

        // Conecta sem selecionar banco ainda (para poder criar o banco)
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Cria banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");

        // Checa se tabela movies já existe com dados
        $exists = $pdo->query("SHOW TABLES LIKE 'movies'")->fetch();
        if ($exists) {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
            if ($count > 10) {
                // Banco já populado — só cria o lock
                self::createLock();
                return;
            }
        }

        // Executa o SQL de instalação
        self::runSQL($pdo);
        self::createLock();

        // Busca posters via TMDB em background (não bloqueia o usuário)
        // Roda assíncrono via requisição separada
        self::schedulePosterFetch();
    }

    private static function schedulePosterFetch(): void {
        // Marca que precisa buscar posters
        $storageDir = APP_ROOT . '/storage';
        file_put_contents($storageDir . '/fetch_posters.flag', '1');
    }

    private static function runSQL(PDO $pdo): void {
        // Executa cada statement do SQL inline
        $statements = self::getInstallSQL();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($statements as $sql) {
            $sql = trim($sql);
            if (empty($sql)) continue;
            try {
                $pdo->exec($sql);
            } catch (\PDOException $e) {
                // Ignora erros de "já existe" (tabelas duplicadas etc)
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    error_log('[AutoInstaller SQL] ' . $e->getMessage() . ' | SQL: ' . substr($sql, 0, 100));
                }
            }
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    private static function createLock(): void {
        file_put_contents(self::$lockFile, date('Y-m-d H:i:s'));
    }

    // ══════════════════════════════════════════════════════════
    // SQL DE INSTALAÇÃO COMPLETO (embutido no PHP)
    // ══════════════════════════════════════════════════════════
    private static function getInstallSQL(): array {
        return [
            // ── TABELAS ──────────────────────────────────────
            "CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                avatar VARCHAR(255) DEFAULT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                reset_token VARCHAR(64) DEFAULT NULL,
                reset_expires DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS genres (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(60) NOT NULL UNIQUE,
                slug VARCHAR(60) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS movies (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(220) NOT NULL UNIQUE,
                synopsis TEXT,
                director VARCHAR(100),
                release_year SMALLINT,
                duration_min SMALLINT,
                rating DECIMAL(3,1) DEFAULT 0.0,
                genre_id INT UNSIGNED,
                price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
                price_digital DECIMAL(8,2) NOT NULL DEFAULT 2.90,
                price_cinema  DECIMAL(8,2) NOT NULL DEFAULT 5.90,
                is_featured TINYINT(1) DEFAULT 0,
                poster VARCHAR(300) DEFAULT NULL,
                video_url VARCHAR(500) DEFAULT NULL,
                trailer_url VARCHAR(500) DEFAULT NULL,
                is_national TINYINT(1) DEFAULT 0,
                is_besteirol TINYINT(1) DEFAULT 0,
                is_oscar TINYINT(1) DEFAULT 0,
                is_bestseller TINYINT(1) DEFAULT 0,
                popularity_score INT DEFAULT 0,
                featured_rank INT DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS combos (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                price DECIMAL(8,2) NOT NULL,
                image VARCHAR(300) DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS coupons (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                discount_type ENUM('percent','fixed') NOT NULL,
                discount_value DECIMAL(8,2) NOT NULL,
                min_order_value DECIMAL(8,2) DEFAULT 0,
                max_uses INT DEFAULT NULL,
                uses INT DEFAULT 0,
                expires_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS orders (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                coupon_id INT UNSIGNED DEFAULT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                discount DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2) NOT NULL,
                status ENUM('pending','confirmed','cancelled') DEFAULT 'confirmed',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS order_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                item_type ENUM('movie','combo') NOT NULL,
                item_id INT UNSIGNED NOT NULL,
                item_name VARCHAR(200) NOT NULL,
                quantity TINYINT UNSIGNED NOT NULL DEFAULT 1,
                unit_price DECIMAL(8,2) NOT NULL,
                seat VARCHAR(10) DEFAULT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS favorites (
                user_id INT UNSIGNED NOT NULL,
                movie_id INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, movie_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS reviews (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                movie_id INT UNSIGNED NOT NULL,
                rating TINYINT NOT NULL,
                comment TEXT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_user_movie (user_id, movie_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS screenings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                movie_id INT UNSIGNED NOT NULL,
                starts_at DATETIME NOT NULL,
                ends_at DATETIME NOT NULL,
                room VARCHAR(30) DEFAULT 'Sala 1',
                capacity INT DEFAULT 50,
                seats_taken INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS access_tokens (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                screening_id INT UNSIGNED NOT NULL,
                movie_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                valid_from DATETIME NOT NULL,
                valid_until DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (screening_id) REFERENCES screenings(id) ON DELETE CASCADE,
                FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            // ── GÊNEROS ───────────────────────────────────────
            "INSERT IGNORE INTO genres (name, slug) VALUES
            ('Ação','acao'),('Drama','drama'),('Comédia','comedia'),
            ('Terror','terror'),('Ficção Científica','ficcao-cientifica'),
            ('Animação','animacao'),('Suspense','suspense'),('Romance','romance'),
            ('Documentário','documentario'),('Crime','crime'),('Aventura','aventura'),
            ('Fantasia','fantasia'),('Musical','musical'),('Biografia','biografia'),
            ('Guerra','guerra'),('Western','western'),('Nacional','nacional')",

            // ── ADMIN ─────────────────────────────────────────
            "INSERT IGNORE INTO users (name, email, password, is_admin) VALUES
            ('Admin CineWeeknd', 'admin@cineweeknd.com',
             '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 1)",

            // ── FILMES ────────────────────────────────────────
            "INSERT IGNORE INTO movies
            (title,slug,synopsis,director,release_year,duration_min,rating,genre_id,price,price_digital,price_cinema,is_featured,is_oscar,popularity_score,featured_rank)
            VALUES
            ('Parasita','parasita','Uma família pobre se infiltra na vida de uma família rica com consequências inesperadas.','Bong Joon-ho',2019,132,8.6,2,24.90,2.90,5.90,1,1,98,1,
            ('Coringa','coringa','A origem do vilão mais famoso do Batman em um drama psicológico intenso.','Todd Phillips',2019,122,8.4,2,24.90,2.90,5.90,1,0,95,2,
            ('Duna','duna','Um jovem nobre lidera tribos do deserto em uma guerra por um planeta precioso.','Denis Villeneuve',2021,155,8.0,5,29.90,2.90,5.90,1,1,93,3,
            ('Oppenheimer','oppenheimer','A história do físico que criou a bomba atômica e seus dilemas morais.','Christopher Nolan',2023,180,8.9,2,34.90,2.90,5.90,1,1,99,4,
            ('Barbie','barbie','Barbie e Ken viajam para o mundo real em uma aventura hilária e reflexiva.','Greta Gerwig',2023,114,7.0,3,29.90,2.90,5.90,1,0,97,5,
            ('Clube da Luta','clube-da-luta','Um homem insatisfeito funda um clube secreto de luta que evolui para algo maior.','David Fincher',1999,139,8.8,3,19.90,2.90,5.90,1,0,94,6,
            ('Interestelar','interestelar','Astronautas viajam por um buraco de minhoca em busca de um novo lar para a humanidade.','Christopher Nolan',2014,169,8.6,5,24.90,2.90,5.90,1,0,96,7,
            ('O Poderoso Chefão','o-poderoso-chefao','A saga da família Corleone, uma das mais poderosas máfias americanas.','Francis Ford Coppola',1972,175,9.2,10,29.90,2.90,5.90,1,1,97,8,
            ('Senhor dos Anéis: O Retorno do Rei','senhor-dos-aneis-retorno-rei','A batalha final pela Terra Média e o destino do Um Anel.','Peter Jackson',2003,201,9.0,12,29.90,2.90,5.90,1,1,96,9,
            ('A Lista de Schindler','a-lista-de-schindler','A história real de Oskar Schindler, que salvou judeus durante o Holocausto.','Steven Spielberg',1993,195,9.0,2,24.90,2.90,5.90,1,1,94,10,
            ('Matrix','matrix','Um hacker descobre que a realidade é uma simulação controlada por máquinas.','Wachowski',1999,136,8.7,5,19.90,2.90,5.90,1,0,93,11,
            ('Forrest Gump','forrest-gump','Um homem simples testemunha os grandes eventos históricos americanos do século XX.','Robert Zemeckis',1994,142,8.8,2,19.90,2.90,5.90,1,1,95,12,
            ('Whiplash','whiplash','Um jovem baterista enfrenta um professor obsessivo em busca da perfeição.','Damien Chazelle',2014,107,8.5,2,22.90,2.90,5.90,1,1,90,13,
            ('Missão Impossível','missao-impossivel','Ethan Hunt enfrenta missões impossíveis em mais um espetáculo de ação.','Christopher McQuarrie',2023,163,7.8,1,34.90,2.90,5.90,1,0,88,14,
            ('Homem-Aranha: Sem Volta para Casa','homem-aranha-sem-volta','Peter Parker pede ajuda ao Doutor Estranho e o multiverso se rompe.','Jon Watts',2021,148,8.2,1,29.90,2.90,5.90,1,0,96,15,
            ('Vingadores: Ultimato','vingadores-ultimato','Os heróis tentam reverter a destruição causada por Thanos.','Russo Brothers',2019,181,8.4,1,29.90,2.90,5.90,1,0,97,16,
            ('Top Gun: Maverick','top-gun-maverick','Maverick retorna para treinar uma nova geração de pilotos para uma missão suicida.','Joseph Kosinski',2022,130,8.2,1,29.90,2.90,5.90,1,0,94,17,
            ('Everything Everywhere All at Once','everything-everywhere','Uma mulher descobre que pode acessar habilidades de versões alternativas de si mesma.','Daniels',2022,139,7.8,5,24.90,2.90,5.90,1,1,89,18,
            ('Pobres Criaturas','pobres-criaturas','Uma mulher ressuscitada explora o mundo com uma perspectiva única e perturbadora.','Yorgos Lanthimos',2023,141,8.0,12,27.90,2.90,5.90,1,1,85,19,
            ('Zona de Interesse','zona-de-interesse','O comandante de Auschwitz e sua família constroem uma vida idílica ao lado do campo.','Jonathan Glazer',2023,105,7.8,2,24.90,2.90,5.90,1,1,82,20,
            ('A Origem','a-origem','Um ladrão invade sonhos alheios para plantar uma ideia na mente de um empresário.','Christopher Nolan',2010,148,8.8,1,24.90,2.90,5.90,1,0,95,21,
            ('Pulp Fiction','pulp-fiction','Histórias entrelaçadas do submundo criminoso de Los Angeles.','Quentin Tarantino',1994,154,8.9,10,19.90,2.90,5.90,1,0,93,22,
            ('O Silêncio dos Inocentes','o-silencio-dos-inocentes','Uma agente do FBI busca a ajuda de um serial killer para capturar outro criminoso.','Jonathan Demme',1991,118,8.6,7,19.90,2.90,5.90,1,1,90,23,
            ('Gladiador','gladiador','Um general romano busca vingança após ser traído e escravizado.','Ridley Scott',2000,155,8.5,1,22.90,2.90,5.90,1,1,91,24,
            ('Toy Story','toy-story','Os brinquedos de Andy ganham vida quando ele não está olhando.','John Lasseter',1995,81,8.3,6,19.90,2.90,5.90,1,0,88,25,
            ('WALL-E','wall-e','Um robô solitário na Terra abandonada encontra amor e esperança.','Andrew Stanton',2008,98,8.4,6,19.90,2.90,5.90,1,1,87,26,
            ('Cidade de Deus','cidade-de-deus','A história de dois jovens que crescem em uma favela no Rio de Janeiro.','Fernando Meirelles',2002,130,8.6,10,22.90,2.90,5.90,1,0,94,27,
            ('Tropa de Elite','tropa-de-elite','O capitão Nascimento enfrenta dilemas morais no BOPE do Rio de Janeiro.','José Padilha',2007,115,8.0,10,19.90,2.90,5.90,0,0,88,0,
            ('Central do Brasil','central-do-brasil','Uma ex-professora e um menino órfão buscam o pai biológico no sertão.','Walter Salles',1998,110,7.9,2,19.90,2.90,5.90,0,0,82,0,
            ('O Auto da Compadecida','o-auto-da-compadecida','João Grilo e Chicó enfrentam situações absurdas e cômicas no sertão nordestino.','Guel Arraes',2000,104,8.3,3,19.90,2.90,5.90,0,0,90,0,
            ('Bacurau','bacurau','Uma cidade do sertão nordestino desaparece do mapa e enfrenta caçadores estrangeiros.','Kleber Mendonça',2019,131,7.2,7,22.90,2.90,5.90,0,0,79,0,
            ('Minha Mãe É uma Peça','minha-mae-e-uma-peca','Dona Hermínia, a mãe mais dramática do Brasil, enfrenta o ninho vazio.','André Pellenz',2013,82,7.0,3,17.90,2.90,5.90,0,0,91,0,
            ('Se Eu Fosse Você','se-eu-fosse-voce','Um casal troca de corpo e precisa viver a vida um do outro.','Daniel Filho',2006,106,6.8,3,17.90,2.90,5.90,0,0,89,0,
            ('American Pie','american-pie','Quatro amigos fazem um pacto para perder a virgindade antes da formatura.','Paul Weitz',1999,95,7.0,3,17.90,2.90,5.90,0,1,92,0,
            ('Superbad','superbad','Dois melhores amigos tentam aproveitar a última festa do ensino médio.','Greg Mottola',2007,113,7.6,3,19.90,2.90,5.90,0,1,88,0,
            ('Passeio dos Mortos','shaun-of-the-dead','Um perdedor sem ambição tenta salvar seus amigos durante um apocalipse zumbi.','Edgar Wright',2004,99,7.9,3,19.90,2.90,5.90,0,1,86,0,
            ('The Room','the-room','O melodrama mais famoso do cinema trash americano.','Tommy Wiseau',2003,99,3.7,2,14.90,2.90,5.90,0,1,85,0,
            ('Birdemic','birdemic','Pássaros atacam uma cidade pequena em um dos piores filmes já feitos.','James Nguyen',2010,95,1.8,7,12.90,2.90,5.90,0,1,87,0,
            ('Plan 9 from Outer Space','plan-9-from-outer-space','Aliens ressuscitam mortos para conquistar a Terra neste clássico trash.','Ed Wood',1957,79,4.0,5,12.90,2.90,5.90,0,1,83,0,
            ('Troll 2','troll-2','Considerado um dos piores filmes da história, sem nenhum troll na história.','Claudio Fragasso',1990,95,2.9,12,12.90,2.90,5.90,0,1,86,0,
            ('O Senhor dos Anéis: A Sociedade do Anel','senhor-dos-aneis-sociedade','Um hobbit recebe um anel mágico e parte em uma perigosa jornada.','Peter Jackson',2001,178,8.8,12,29.90,2.90,5.90,1,1,97,0,
            ('Amadeus','amadeus','A rivalidade entre Mozart e Salieri na corte do Imperador austríaco.','Miloš Forman',1984,160,8.4,2,22.90,2.90,5.90,1,1,87,0,
            ('Braveheart','braveheart','William Wallace lidera a Escócia na luta pela independência da Inglaterra.','Mel Gibson',1995,178,8.3,15,22.90,2.90,5.90,1,1,89,0,
            ('O Pianista','o-pianista','Um pianista judeu sobrevive ao Holocausto escondido nas ruínas de Varsóvia.','Roman Polanski',2002,150,8.5,2,24.90,2.90,5.90,1,1,88,0,
            ('Crash','crash','Vidas se cruzam em Los Angeles revelando preconceitos e conflitos raciais.','Paul Haggis',2004,112,7.7,2,19.90,2.90,5.90,1,1,82,0,
            ('No Country for Old Men','no-country-for-old-men','Um caçador encontra dinheiro de um cartel e é perseguido por um assassino implacável.','Coen Brothers',2007,122,8.1,10,22.90,2.90,5.90,1,1,87,0,
            ('Moonlight','moonlight','A jornada de um jovem negro gay crescendo nas ruas de Miami.','Barry Jenkins',2016,111,7.4,2,22.90,2.90,5.90,1,1,83,0,
            ('12 Years a Slave','12-years-a-slave','A história real de Solomon Northup, homem livre escravizado por 12 anos.','Steve McQueen',2013,134,8.1,2,22.90,2.90,5.90,1,1,85,0,
            ('O Artista','o-artista','Um ator do cinema mudo tenta se adaptar à chegada do cinema falado.','Michel Hazanavicius',2011,100,7.9,13,19.90,2.90,5.90,1,1,79,0,
            ('A Forma da Água','a-forma-da-agua','Uma faxineira muda se apaixona por uma criatura anfíbia em um laboratório secreto.','Guillermo del Toro',2017,123,7.3,12,22.90,2.90,5.90,1,1,80,0,
            ('Green Book','green-book','Um motorista branco transporta um pianista negro pelo sul racista dos EUA nos anos 60.','Peter Farrelly',2018,130,8.2,2,22.90,2.90,5.90,1,1,87,0,
            ('Coda','coda','Uma garota ouvinte em uma família surda luta para seguir seu sonho de cantar.','Sian Heder',2021,111,7.9,2,22.90,2.90,5.90,1,1,82,0,
            ('Os Favelados','os-favelados','Uma família pobre tenta sobreviver em uma favela carioca.','Nelson Pereira',1962,90,7.2,2,15.90,2.90,5.90,0,0,70,0,
            ('Eu Sei Que Vou Te Amar','eu-sei-que-vou-te-amar','Um casal enfrenta a separação em um drama romântico brasileiro intenso.','Arnaldo Jabor',1986,107,7.0,8,17.90,2.90,5.90,0,0,68,0,
            ('Carandiru','carandiru','A história do maior presídio da América Latina e o massacre de 1992.','Hector Babenco',2003,145,7.7,10,19.90,2.90,5.90,0,0,80,0,
            ('Meu Nome Não É Johnny','meu-nome-nao-e-johnny','A história real de Joaninho, traficante de cocaína da classe média carioca.','Mauro Lima',2008,110,7.8,10,19.90,2.90,5.90,0,0,82,0,
            ('O Homem do Futuro','o-homem-do-futuro','Um físico viaja ao passado e tenta mudar sua vida, alterando o futuro.','Cláudio Torres',2011,105,7.1,3,17.90,2.90,5.90,0,0,78,0",

            // ── VENUES (Shoppings de Brasília) ───────────────
            "INSERT IGNORE INTO venues (name, address, city) VALUES
            ('Conjunto Nacional',       'Asa Norte, SCEN Trecho 2',          'Brasília'),
            ('ParkShopping',            'SMAS Trecho 1, Guará',              'Brasília'),
            ('Brasília Shopping',       'SCS Qd. 7 Bl. A',                   'Brasília'),
            ('Shopping Iguatemi',       'SHIN QL 5 Conj. 3, Lago Norte',     'Brasília'),
            ('Pátio Brasil Shopping',   'SCS Quadra 7 Bloco A',              'Brasília'),
            ('Taguatinga Shopping',     'C7 Área Especial, Taguatinga',      'Brasília'),
            ('JK Shopping',             'SEPN 510/511 Bloco C, Asa Norte',   'Brasília')",

            // ── COMBOS ───────────────────────────────────────
            "INSERT IGNORE INTO combos (name, description, price) VALUES
            ('Combo Pipoca P', 'Pipoca pequena + refrigerante 300ml', 18.90),
            ('Combo Pipoca M', 'Pipoca média + refrigerante 500ml', 24.90),
            ('Combo Pipoca G', 'Pipoca grande + refrigerante 700ml + bombom', 34.90),
            ('Combo Família', '2 pipocas grandes + 4 refrigerantes + 4 bombons', 69.90),
            ('Combo Casal', '2 pipocas médias + 2 refrigerantes + 2 chocolates', 44.90)",

            // ── CUPONS ───────────────────────────────────────
            "INSERT IGNORE INTO coupons (code, discount_type, discount_value, min_order_value, max_uses, expires_at) VALUES
            ('BEMVINDO10', 'percent', 10, 20.00, 100, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
            ('NACIONAL15', 'percent', 15, 30.00, 50,  DATE_ADD(NOW(), INTERVAL 6 MONTH)),
            ('OSCAR20',    'percent', 20, 50.00, 30,  DATE_ADD(NOW(), INTERVAL 6 MONTH)),
            ('COMBO5',     'fixed',    5,  0.00, 200, DATE_ADD(NOW(), INTERVAL 1 YEAR))",
        ];
    }
}
