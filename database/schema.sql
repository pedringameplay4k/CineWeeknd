-- ============================================================
-- CineWeeknd v2.0 - Database Schema
-- Run this file to set up the database from scratch
-- ============================================================

CREATE DATABASE IF NOT EXISTS cineweeknd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cineweeknd;

-- -------------------------------------------------------
-- Users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    avatar      VARCHAR(255) DEFAULT NULL,
    is_admin    TINYINT(1) DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Genres
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS genres (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(60) NOT NULL UNIQUE,
    slug       VARCHAR(60) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Movies
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS movies (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(200) NOT NULL,
    slug             VARCHAR(220) NOT NULL UNIQUE,
    synopsis         TEXT,
    director         VARCHAR(120) DEFAULT NULL,
    cast_list        TEXT DEFAULT NULL,
    release_year     YEAR DEFAULT NULL,
    duration_min     SMALLINT UNSIGNED DEFAULT NULL,
    rating           DECIMAL(3,1) DEFAULT 0.0,
    genre_id         INT UNSIGNED DEFAULT NULL,
    poster           VARCHAR(300) DEFAULT NULL,
    video_url        VARCHAR(500) DEFAULT NULL,
    gdrive_url       VARCHAR(500) DEFAULT NULL,
    trailer_url      VARCHAR(500) DEFAULT NULL,
    price            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    price_digital    DECIMAL(8,2) NOT NULL DEFAULT 2.90,
    price_cinema     DECIMAL(8,2) NOT NULL DEFAULT 5.90,
    is_featured      TINYINT(1) DEFAULT 0,
    is_active        TINYINT(1) DEFAULT 1,
    is_national      TINYINT(1) DEFAULT 0,
    is_besteirol     TINYINT(1) DEFAULT 0,
    is_oscar         TINYINT(1) DEFAULT 0,
    is_bestseller    TINYINT(1) DEFAULT 0,
    popularity_score INT DEFAULT 0,
    featured_rank    INT DEFAULT 0,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL,
    INDEX idx_genre (genre_id),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    FULLTEXT idx_search (title, synopsis, director)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Combos (snack packages)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS combos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image       VARCHAR(255) DEFAULT NULL,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Coupons
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(30) NOT NULL UNIQUE,
    discount_type   ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value  DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(10,2) DEFAULT 0.00,
    max_uses        INT UNSIGNED DEFAULT NULL,
    used_count      INT UNSIGNED DEFAULT 0,
    expires_at      DATETIME DEFAULT NULL,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Orders
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    coupon_id   INT UNSIGNED DEFAULT NULL,
    subtotal    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status      ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    notes       TEXT DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Order Items
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    item_type    ENUM('movie','combo') NOT NULL,
    item_id      INT UNSIGNED NOT NULL,
    item_name    VARCHAR(200) NOT NULL,
    quantity     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price   DECIMAL(10,2) NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Favorites
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    movie_id   INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie (user_id, movie_id),
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Reviews
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    movie_id   INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment    TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie (user_id, movie_id),
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    INDEX idx_movie (movie_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Venues (Shoppings de Brasília)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS venues (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    address    VARCHAR(200) DEFAULT NULL,
    city       VARCHAR(100) DEFAULT NULL,
    is_active  TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Screenings
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS screenings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id    INT UNSIGNED NOT NULL,
    venue_id    INT UNSIGNED DEFAULT NULL,
    mode        ENUM('digital','cinema') NOT NULL DEFAULT 'digital',
    starts_at   DATETIME NOT NULL,
    ends_at     DATETIME NOT NULL,
    room        VARCHAR(30) DEFAULT 'Sala 1',
    capacity    INT DEFAULT 50,
    seats_taken INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Access Tokens (ingressos digitais)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS access_tokens (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    screening_id INT UNSIGNED NOT NULL,
    movie_id     INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    token        VARCHAR(64) NOT NULL UNIQUE,
    valid_from   DATETIME NOT NULL,
    valid_until  DATETIME NOT NULL,
    used_at      DATETIME DEFAULT NULL,
    is_active    TINYINT(1) DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)     REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (screening_id) REFERENCES screenings(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id)     REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)      REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Genres
INSERT IGNORE INTO genres (name, slug) VALUES
('Ação',             'action'),
('Drama',            'drama'),
('Comédia',          'comedy'),
('Ficção Científica','sci-fi'),
('Terror',           'horror'),
('Suspense',         'thriller'),
('Animação',         'animation'),
('Romance',          'romance'),
('Aventura',         'adventure'),
('Crime',            'crime');

-- Admin user (password: Admin@1234)
INSERT IGNORE INTO users (name, email, password, is_admin) VALUES
('Admin', 'admin@cineweeknd.com', '$2y$12$8zJQCyHf4bH7kD2eOv3xFuNlrW6fBKDsHYtZxJmVpA8q1X4D5Ywq2', 1);
-- NOTE: Replace the hash above by running: password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost'=>12])

-- Sample movies
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, poster, gdrive_url, price, is_featured) VALUES
('Batman: O Cavaleiro das Trevas', 'batman-cavaleiro-das-trevas',
 'Quando o cruel vilão Coringa causa caos e destruição em Gotham City, Batman precisa aceitar um dos maiores testes psicológicos e físicos de sua capacidade de lutar contra a injustiça. Com a cidade à beira do colapso, o Homem-Morcego enfrenta escolhas impossíveis que irão definir o que significa ser um verdadeiro herói.',
 'Christopher Nolan', 2008, 152, 9.0, 1, 'the-dark-knight.jpg',
 'https://drive.google.com/file/d/1a_L2_RfDC9-A-uDewo_k_0HEXZPbEe0F/view?usp=drive_link',
 32.90, 1);

-- Combos
INSERT IGNORE INTO combos (name, description, price) VALUES
('Classic Duo',    '1 large popcorn + 1 medium soda. The perfect pair for movie night.', 24.90),
('Mega Family',    '2 large popcorns + 2 medium sodas + 1 nachos. Feed the whole crew.', 59.90),
('Solo VIP',       '1 premium popcorn (butter & cheese) + 1 large soda + 1 candy box.', 34.90),
('Sweet Dreams',   '1 large popcorn (caramel) + 2 churros + 1 hot chocolate. Dessert lovers delight.', 38.90),
('Beer & Bites',   '2 craft beers + 1 nachos with dips + 1 mini pizza. Adult movie night essential.', 49.90);

-- Sample coupon
INSERT IGNORE INTO coupons (code, discount_type, discount_value, min_order_value, max_uses, expires_at) VALUES
('WELCOME10', 'percent', 10.00, 20.00, 100, DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('FIRSTBUY',  'fixed',   15.00, 50.00, 50,  DATE_ADD(NOW(), INTERVAL 6 MONTH));
