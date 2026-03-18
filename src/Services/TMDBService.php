<?php
/**
 * CineWeeknd — TMDBService
 * Busca dados e posters de filmes via The Movie Database API
 *
 * API Key configurada em config.php: TMDB_API_KEY
 */

class TMDBService {

    private static string $base    = 'https://api.themoviedb.org/3';
    private static string $imgBase = 'https://image.tmdb.org/t/p/w500';

    // ── Busca filme por título — tenta múltiplas estratégias ──
    public static function search(string $title, int $year = 0): ?array {
        // Estratégia 1: PT-BR com ano
        if ($year) {
            $r = self::searchRaw($title, 'pt-BR', $year);
            if ($r) return $r;
        }
        // Estratégia 2: PT-BR sem ano
        $r = self::searchRaw($title, 'pt-BR');
        if ($r) return $r;

        // Estratégia 3: EN sem ano (pega título original)
        $r = self::searchRaw($title, 'en-US');
        if ($r) return $r;

        // Estratégia 4: Remove artigos e tenta de novo
        $simplified = preg_replace('/^(O |A |Os |As |Um |Uma |The |An |A )/i', '', $title);
        if ($simplified !== $title) {
            $r = self::searchRaw($simplified, 'pt-BR');
            if ($r) return $r;
            $r = self::searchRaw($simplified, 'en-US');
            if ($r) return $r;
        }

        return null;
    }

    private static function searchRaw(string $title, string $lang, int $year = 0): ?array {
        $query = urlencode($title);
        $url   = self::$base . "/search/movie?api_key=" . TMDB_API_KEY
                 . "&query={$query}&language={$lang}"
                 . ($year ? "&year={$year}" : '');
        $data  = self::get($url);
        if (empty($data['results'])) return null;
        // Prefere resultado com poster
        foreach ($data['results'] as $r) {
            if (!empty($r['poster_path'])) return $r;
        }
        return $data['results'][0];
    }

    // ── Busca detalhes completos por ID TMDB ──────────────
    public static function details(int $tmdbId): ?array {
        $url  = self::$base . "/movie/{$tmdbId}?api_key=" . TMDB_API_KEY
                . "&language=pt-BR&append_to_response=credits";
        return self::get($url);
    }

    // ── Baixa e salva o poster localmente ────────────────
    public static function downloadPoster(string $posterPath, string $slug): ?string {
        if (empty($posterPath)) return null;

        $saveDir = UPLOAD_DIR; // definido em config.php
        if (!is_dir($saveDir)) mkdir($saveDir, 0755, true);

        $filename  = $slug . '.jpg';
        $localPath = $saveDir . $filename;

        // Já existe? Não baixa de novo
        if (file_exists($localPath)) return $filename;

        $url  = self::$imgBase . $posterPath;
        $data = @file_get_contents($url);
        if ($data === false) {
            // Tenta via cURL
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $data = curl_exec($ch);
            curl_close($ch);
        }

        if ($data && strlen($data) > 1000) {
            file_put_contents($localPath, $data);
            return $filename;
        }
        return null;
    }

    // ── Busca E baixa poster para um filme do banco ──────
    public static function fetchPosterForMovie(array $movie): ?string {
        $year   = (int)($movie['release_year'] ?? 0);
        $result = self::search($movie['title'], $year);

        // Fallback: tenta com o slug convertido em título legível
        if (!$result || empty($result['poster_path'])) {
            $titleFromSlug = ucwords(str_replace('-', ' ', $movie['slug']));
            $result = self::search($titleFromSlug, $year);
        }

        if (!$result || empty($result['poster_path'])) return null;

        $filename = self::downloadPoster($result['poster_path'], $movie['slug']);

        // Se baixou, salva também o poster_path TMDB para não buscar de novo
        return $filename;
    }

    // ── Atualiza todos os filmes sem poster no banco ──────
    public static function updateMissingPosters(): int {
        $db    = getDB();
        $stmt  = $db->query("SELECT id, title, slug, release_year, poster FROM movies WHERE poster IS NULL OR poster = '' ORDER BY popularity_score DESC");
        $movies = $stmt->fetchAll();
        $count  = 0;

        foreach ($movies as $movie) {
            $filename = self::fetchPosterForMovie($movie);
            if ($filename) {
                $db->prepare("UPDATE movies SET poster = ? WHERE id = ?")
                   ->execute([$filename, $movie['id']]);
                $count++;
                // Pausa mínima para não sobrecarregar a API
                usleep(300000); // 0.3s
            }
        }
        return $count;
    }

    // ── Busca filme por nome para o painel admin ──────────
    public static function searchForAdmin(string $query): array {
        $encoded = urlencode($query);
        $url     = self::$base . "/search/movie?api_key=" . TMDB_API_KEY
                   . "&query={$encoded}&language=pt-BR&page=1";
        $data    = self::get($url);
        $results = $data['results'] ?? [];

        return array_slice(array_map(function($r) {
            return [
                'tmdb_id'     => $r['id'],
                'title'       => $r['title'] ?? $r['original_title'],
                'year'        => substr($r['release_date'] ?? '0000', 0, 4),
                'overview'    => $r['overview'] ?? '',
                'poster_path' => $r['poster_path'] ?? '',
                'rating'      => $r['vote_average'] ?? 0,
                'poster_url'  => $r['poster_path'] ? 'https://image.tmdb.org/t/p/w200' . $r['poster_path'] : '',
            ];
        }, $results), 0, 8);
    }

    // ── Importa um filme do TMDB direto para o banco ──────
    public static function importMovie(int $tmdbId, float $price, bool $isFeatured = false): ?int {
        $data = self::details($tmdbId);
        if (!$data) return null;

        $db = getDB();

        // Gênero principal
        $genreId = null;
        if (!empty($data['genres'])) {
            $genreName = $data['genres'][0]['name'];
            $genreSlug = self::slugify($genreName);
            $db->prepare("INSERT IGNORE INTO genres (name, slug) VALUES (?, ?)")
               ->execute([$genreName, $genreSlug]);
            $row = $db->prepare("SELECT id FROM genres WHERE slug = ?");
            $row->execute([$genreSlug]);
            $genreId = $row->fetchColumn() ?: null;
        }

        // Diretor
        $director = '';
        foreach (($data['credits']['crew'] ?? []) as $c) {
            if ($c['job'] === 'Director') { $director = $c['name']; break; }
        }

        $title    = $data['title'] ?? $data['original_title'];
        $slug     = self::slugify($title) . '-' . substr($data['release_date'] ?? '0000', 0, 4);
        $synopsis = $data['overview'] ?? '';
        $year     = (int) substr($data['release_date'] ?? '0000', 0, 4);
        $duration = (int) ($data['runtime'] ?? 0);
        $rating   = round($data['vote_average'] ?? 0, 1);
        $popular  = (int) ($data['popularity'] ?? 0);

        // Baixa poster
        $posterFile = null;
        if (!empty($data['poster_path'])) {
            $posterFile = self::downloadPoster($data['poster_path'], $slug);
        }

        // Trailer YouTube
        $trailerUrl = null;
        $videos = self::get(self::$base . "/movie/{$tmdbId}/videos?api_key=" . TMDB_API_KEY);
        foreach (($videos['results'] ?? []) as $v) {
            if ($v['site'] === 'YouTube' && $v['type'] === 'Trailer') {
                $trailerUrl = 'https://www.youtube.com/watch?v=' . $v['key'];
                break;
            }
        }

        try {
            $stmt = $db->prepare("
                INSERT IGNORE INTO movies
                (title, slug, synopsis, director, release_year, duration_min, rating,
                 genre_id, price, is_featured, poster, trailer_url, popularity_score)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $slug, $synopsis, $director, $year, $duration, $rating,
                $genreId, $price, $isFeatured ? 1 : 0,
                $posterFile, $trailerUrl, $popular
            ]);
            return (int) $db->lastInsertId() ?: null;
        } catch (\Throwable $e) {
            error_log('TMDB import error: ' . $e->getMessage());
            return null;
        }
    }

    // ── Helpers ───────────────────────────────────────────
    private static function get(string $url): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body ?: '{}', true) ?? [];
    }

    public static function slugify(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $map  = ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','é'=>'e','ê'=>'e','è'=>'e',
                 'í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n'];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}
