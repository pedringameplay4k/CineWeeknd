<?php
/**
 * CineWeeknd — Configurações
 * Copie este arquivo para config.php e preencha suas credenciais
 */

// ── Banco de Dados ────────────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'cineweeknd');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');

// ── URL Base ──────────────────────────────────────────────────
define('APP_URL', 'http://localhost/CineWeeknd_final/public');

// ── APIs ──────────────────────────────────────────────────────
define('TMDB_API_KEY',   'sua_chave_tmdb_aqui');
define('RESEND_API_KEY', 'sua_chave_resend_aqui');
define('RESEND_FROM',    'CineWeeknd <onboarding@resend.dev>');

// ── Upload de pôsteres ────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/posters/');
define('UPLOAD_URL', APP_URL . '/uploads/posters/');
define('DEFAULT_POSTER', APP_URL . '/assets/img/no-poster.jpg');

// ── Segurança ─────────────────────────────────────────────────
define('CSRF_TOKEN_NAME',    '_csrf_token');
define('MIN_PASSWORD_LENGTH', 6);
