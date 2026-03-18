<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineWeeknd — Setup</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0e0e0e; color: #f0ece4; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
        .card { background: #1c1c1c; border: 1px solid #2e2e2e; border-radius: 16px; padding: 2.5rem; width: 100%; max-width: 560px; }
        h1 { font-size: 1.8rem; margin-bottom: .3rem; }
        h1 span { color: #c9a84c; }
        .sub { color: #6e6a66; font-size: .9rem; margin-bottom: 2rem; }
        label { display: block; font-size: .85rem; color: #9e9690; margin-bottom: .4rem; margin-top: 1rem; }
        input { width: 100%; background: #252525; border: 1px solid #2e2e2e; border-radius: 8px; color: #f0ece4; padding: .65rem 1rem; font-size: .92rem; }
        input:focus { outline: none; border-color: #c9a84c; }
        button { width: 100%; background: #c9a84c; color: #080808; border: none; border-radius: 10px; padding: .85rem; font-size: 1rem; font-weight: 700; margin-top: 1.5rem; cursor: pointer; }
        button:hover { background: #e8c96d; }
        .msg { margin-top: 1.2rem; padding: 1rem; border-radius: 10px; font-size: .9rem; line-height: 1.6; }
        .msg.success { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.2); color: #10b981; }
        .msg.error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.2);  color: #ef4444; }
        .step { display: flex; align-items: flex-start; gap: .6rem; margin-bottom: .4rem; font-size: .87rem; }
        .ok  { color: #10b981; font-weight: 700; flex-shrink: 0; }
        .err { color: #ef4444; font-weight: 700; flex-shrink: 0; }
        .go-btn { display: block; text-align: center; background: #c9a84c; color: #080808; border-radius: 10px; padding: .85rem; font-weight: 700; text-decoration: none; margin-top: 1rem; }
        hr { border-color: #2e2e2e; margin: 1.5rem 0; }
        .note { color: #5e5a56; font-size: .78rem; text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>🎬 Cine<span>Weeknd</span></h1>
    <p class="sub">Configuração automática — preencha e clique em instalar.</p>

<?php
$done   = false;
$steps  = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host   = trim($_POST['db_host']   ?? 'localhost');
    $db_name   = trim($_POST['db_name']   ?? 'cineweeknd');
    $db_user   = trim($_POST['db_user']   ?? 'root');
    $db_pass   = $_POST['db_pass']        ?? '';
    $adm_email = trim($_POST['adm_email'] ?? '');
    $adm_name  = trim($_POST['adm_name']  ?? 'Admin');
    $adm_pass  = $_POST['adm_pass']       ?? '';
    $app_url   = rtrim(trim($_POST['app_url'] ?? 'http://localhost/CineWeeknd_final/public'), '/');

    // STEP 1: Connect
    try {
        $pdo = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $steps[] = ['ok', 'Conexão com MySQL estabelecida'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao conectar no MySQL: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 2: Create database
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db_name}`");
        $steps[] = ['ok', "Banco '{$db_name}' criado/selecionado"];
    } catch (Exception $e) {
        $errors[] = 'Erro ao criar banco: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 3: Create tables one by one
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS genres (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(60) NOT NULL UNIQUE,
            slug VARCHAR(60) NOT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
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
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            synopsis TEXT,
            director VARCHAR(120) DEFAULT NULL,
            cast_list TEXT DEFAULT NULL,
            release_year YEAR DEFAULT NULL,
            duration_min SMALLINT UNSIGNED DEFAULT NULL,
            rating DECIMAL(3,1) DEFAULT 0.0,
            genre_id INT UNSIGNED DEFAULT NULL,
            poster VARCHAR(255) DEFAULT NULL,
            trailer_url VARCHAR(500) DEFAULT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            is_featured TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS combos (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            image VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(30) NOT NULL UNIQUE,
            discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
            discount_value DECIMAL(10,2) NOT NULL,
            min_order_value DECIMAL(10,2) DEFAULT 0.00,
            max_uses INT UNSIGNED DEFAULT NULL,
            used_count INT UNSIGNED DEFAULT 0,
            expires_at DATETIME DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            coupon_id INT UNSIGNED DEFAULT NULL,
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
            notes TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            item_type ENUM('movie','combo') NOT NULL,
            item_id INT UNSIGNED NOT NULL,
            item_name VARCHAR(200) NOT NULL,
            quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS favorites (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            movie_id INT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_movie (user_id, movie_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            movie_id INT UNSIGNED NOT NULL,
            rating TINYINT UNSIGNED NOT NULL,
            comment TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_movie (user_id, movie_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        $steps[] = ['ok', 'Todas as tabelas criadas'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao criar tabelas: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 4: Seed genres
    try {
        $gs = $pdo->prepare("INSERT IGNORE INTO genres (name, slug) VALUES (?, ?)");
        foreach ([
            ['Action','action'],['Drama','drama'],['Comedy','comedy'],
            ['Sci-Fi','sci-fi'],['Horror','horror'],['Thriller','thriller'],
            ['Animation','animation'],['Romance','romance'],['Adventure','adventure'],['Crime','crime']
        ] as $g) { $gs->execute($g); }
        $steps[] = ['ok', 'Gêneros inseridos'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao inserir gêneros: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 5: Seed movies
    try {
        $ms = $pdo->prepare("INSERT IGNORE INTO movies (title,slug,synopsis,director,release_year,duration_min,rating,genre_id,price,is_featured) VALUES (?,?,?,?,?,?,?,?,?,?)");
        foreach ([
            ['Interstellar','interstellar','A team of explorers travel through a wormhole in space to ensure humanitys survival.','Christopher Nolan',2014,169,8.7,4,35.90,1],
            ['The Dark Knight','the-dark-knight','When the Joker wreaks havoc on Gotham City, Batman faces his greatest test of justice.','Christopher Nolan',2008,152,9.0,2,32.90,1],
            ['Inception','inception','A thief who steals secrets through dream-sharing is given the task of planting an idea into a CEOs mind.','Christopher Nolan',2010,148,8.8,6,32.90,1],
            ['The Matrix','the-matrix','A computer hacker learns the truth about his reality and his role in the war against its controllers.','The Wachowskis',1999,136,8.7,4,28.90,0],
            ['Parasite','parasite','Greed and class discrimination threaten the relationship between the wealthy Parks and the destitute Kims.','Bong Joon Ho',2019,132,8.5,10,30.90,1],
            ['Pulp Fiction','pulp-fiction','The lives of two hitmen, a boxer and a gangster intertwine in four tales of violence and redemption.','Quentin Tarantino',1994,154,8.9,10,28.90,0],
            ['Spirited Away','spirited-away','A young girl wanders into a world ruled by gods, witches and spirits while her parents are transformed.','Hayao Miyazaki',2001,125,8.6,7,27.90,0],
            ['The Godfather','the-godfather','A crime dynastys patriarch transfers control of his empire to his reluctant son.','Francis Ford Coppola',1972,175,9.2,10,29.90,0],
            ['Blade Runner 2049','blade-runner-2049','A blade runners discovery of a buried secret leads him to track down former runner Rick Deckard.','Denis Villeneuve',2017,164,8.0,4,33.90,0],
            ['Dune','dune','A noble family is embroiled in a war for the galaxys most valuable asset while its heir has dark visions.','Denis Villeneuve',2021,155,8.0,4,36.90,1],
            ['The Grand Budapest Hotel','grand-budapest-hotel','A writer hears of a lobby boys early years under an exceptional concierge at an aging hotel.','Wes Anderson',2014,99,8.1,3,26.90,0],
        ] as $m) { $ms->execute($m); }
        $steps[] = ['ok', '11 filmes inseridos'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao inserir filmes: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 6: Seed combos
    try {
        $cs = $pdo->prepare("INSERT IGNORE INTO combos (name, description, price) VALUES (?, ?, ?)");
        foreach ([
            ['Classic Duo','1 large popcorn + 1 medium soda. The perfect pair for movie night.',24.90],
            ['Mega Family','2 large popcorns + 2 medium sodas + 1 nachos. Feed the whole crew.',59.90],
            ['Solo VIP','1 premium popcorn with butter and cheese + 1 large soda + 1 candy box.',34.90],
            ['Sweet Dreams','1 large caramel popcorn + 2 churros + 1 hot chocolate.',38.90],
            ['Beer and Bites','2 craft beers + 1 nachos with dips + 1 mini pizza.',49.90],
        ] as $c) { $cs->execute($c); }
        $steps[] = ['ok', '5 combos inseridos'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao inserir combos: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 7: Seed coupons
    try {
        $pdo->exec("INSERT IGNORE INTO coupons (code,discount_type,discount_value,min_order_value,max_uses,expires_at) VALUES ('WELCOME10','percent',10.00,20.00,100,DATE_ADD(NOW(),INTERVAL 1 YEAR))");
        $pdo->exec("INSERT IGNORE INTO coupons (code,discount_type,discount_value,min_order_value,max_uses,expires_at) VALUES ('FIRSTBUY','fixed',15.00,50.00,50,DATE_ADD(NOW(),INTERVAL 6 MONTH))");
        $steps[] = ['ok', 'Cupons inseridos'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao inserir cupons: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 8: Create admin user
    try {
        $hash = password_hash($adm_pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE password=VALUES(password), is_admin=1, name=VALUES(name)");
        $stmt->execute([$adm_name, $adm_email, $hash]);
        $steps[] = ['ok', "Admin criado: {$adm_email}"];
    } catch (Exception $e) {
        $errors[] = 'Erro ao criar admin: ' . $e->getMessage();
        goto show_result;
    }

    // STEP 9: Update config files
    try {
        $dbConfig = '<?php' . "\n" .
            'define(\'DB_HOST\', ' . var_export($db_host, true) . ");\n" .
            'define(\'DB_NAME\', ' . var_export($db_name, true) . ");\n" .
            'define(\'DB_USER\', ' . var_export($db_user, true) . ");\n" .
            'define(\'DB_PASS\', ' . var_export($db_pass, true) . ");\n" .
            "define('DB_CHARSET', 'utf8mb4');\n\n" .
            "function getDB(): PDO {\n" .
            "    static \$pdo = null;\n" .
            "    if (\$pdo === null) {\n" .
            "        \$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;\n" .
            "        \$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];\n" .
            "        try { \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options); }\n" .
            "        catch (PDOException \$e) { error_log('DB failed: ' . \$e->getMessage()); die('Database connection failed.'); }\n" .
            "    }\n" .
            "    return \$pdo;\n" .
            "}\n";
        file_put_contents(__DIR__ . '/../config/database.php', $dbConfig);

        $configPath = __DIR__ . '/../config/config.php';
        $configContent = file_get_contents($configPath);
        $configContent = preg_replace("/define\('APP_URL',\s*'[^']*'\)/", "define('APP_URL', " . var_export($app_url, true) . ")", $configContent);
        file_put_contents($configPath, $configContent);

        $steps[] = ['ok', 'Arquivos de configuração atualizados'];
    } catch (Exception $e) {
        $errors[] = 'Erro ao salvar configs: ' . $e->getMessage();
        goto show_result;
    }

    $done = true;
    show_result:
}
?>

<?php if ($done && empty($errors)): ?>
    <div class="msg success">
        <?php foreach ($steps as $s): ?>
        <div class="step"><span class="ok">✓</span> <?= htmlspecialchars($s[1]) ?></div>
        <?php endforeach; ?>
    </div>
    <hr>
    <p style="color:#9e9690;font-size:.9rem;margin-bottom:.5rem">✅ Instalação concluída! Suas credenciais:</p>
    <p style="font-size:.9rem;margin-bottom:.3rem">📧 Email: <strong style="color:#c9a84c"><?= htmlspecialchars($_POST['adm_email']) ?></strong></p>
    <p style="font-size:.9rem;margin-bottom:1rem">🔑 Senha: <strong style="color:#c9a84c"><?= htmlspecialchars($_POST['adm_pass']) ?></strong></p>
    <a href="<?= htmlspecialchars($_POST['app_url'] ?? '/') ?>" class="go-btn">🎬 Abrir CineWeeknd</a>
    <p class="note">⚠️ Delete o arquivo setup.php depois de usar!</p>

<?php elseif (!empty($errors)): ?>
    <?php if (!empty($steps)): ?>
    <div class="msg success" style="margin-bottom:.8rem">
        <?php foreach ($steps as $s): ?>
        <div class="step"><span class="ok">✓</span> <?= htmlspecialchars($s[1]) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="msg error">
        <?php foreach ($errors as $err): ?>
        <div class="step"><span class="err">✗</span> <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
    <button onclick="history.back()" style="margin-top:1rem;background:#252525;color:#f0ece4">← Tentar novamente</button>

<?php else: ?>
<form method="POST">
    <label>Host do MySQL</label>
    <input type="text" name="db_host" value="localhost">
    <label>Nome do Banco</label>
    <input type="text" name="db_name" value="cineweeknd">
    <label>Usuário MySQL</label>
    <input type="text" name="db_user" value="root">
    <label>Senha MySQL <small style="color:#5e5a56">(deixe vazio no XAMPP padrão)</small></label>
    <input type="password" name="db_pass" placeholder="vazio = sem senha">
    <hr>
    <label>URL do site</label>
    <input type="text" name="app_url" value="http://localhost/CineWeeknd_final/public">
    <hr>
    <label>Nome do Admin</label>
    <input type="text" name="adm_name" value="Admin">
    <label>Email do Admin</label>
    <input type="email" name="adm_email" value="pedro@cineweeknd.com">
    <label>Senha do Admin</label>
    <input type="password" name="adm_pass" value="MinhaS3nha!">
    <button type="submit">⚡ Instalar CineWeeknd</button>
</form>
<?php endif; ?>

</div>
</body>
</html>
