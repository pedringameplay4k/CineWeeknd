CREATE TABLE IF NOT EXISTS genres (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(60) NOT NULL UNIQUE,
    slug       VARCHAR(60) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    avatar        VARCHAR(255) DEFAULT NULL,
    is_admin      TINYINT(1) DEFAULT 0,
    is_active     TINYINT(1) DEFAULT 1,
    reset_token   VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS movies (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NOT NULL,
    slug          VARCHAR(200) NOT NULL UNIQUE,
    synopsis      TEXT,
    director      VARCHAR(120) DEFAULT NULL,
    cast_list     TEXT DEFAULT NULL,
    release_year  YEAR DEFAULT NULL,
    duration_min  SMALLINT UNSIGNED DEFAULT NULL,
    rating        DECIMAL(3,1) DEFAULT 0.0,
    genre_id      INT UNSIGNED DEFAULT NULL,
    poster        VARCHAR(255) DEFAULT NULL,
    trailer_url   VARCHAR(500) DEFAULT NULL,
    price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_featured   TINYINT(1) DEFAULT 0,
    is_active     TINYINT(1) DEFAULT 1,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL,
    INDEX idx_genre (genre_id),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS combos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image       VARCHAR(255) DEFAULT NULL,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS coupons (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code             VARCHAR(30) NOT NULL UNIQUE,
    discount_type    ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value   DECIMAL(10,2) NOT NULL,
    min_order_value  DECIMAL(10,2) DEFAULT 0.00,
    max_uses         INT UNSIGNED DEFAULT NULL,
    used_count       INT UNSIGNED DEFAULT 0,
    expires_at       DATETIME DEFAULT NULL,
    is_active        TINYINT(1) DEFAULT 1,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB
---
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
    INDEX idx_user (user_id)
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    item_type   ENUM('movie','combo') NOT NULL,
    item_id     INT UNSIGNED NOT NULL,
    item_name   VARCHAR(200) NOT NULL,
    quantity    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price  DECIMAL(10,2) NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS favorites (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    movie_id   INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie (user_id, movie_id),
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB
---
CREATE TABLE IF NOT EXISTS reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    movie_id   INT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL,
    comment    TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie (user_id, movie_id),
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    INDEX idx_movie (movie_id)
) ENGINE=InnoDB
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Action','action')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Drama','drama')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Comedy','comedy')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Sci-Fi','sci-fi')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Horror','horror')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Thriller','thriller')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Animation','animation')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Romance','romance')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Adventure','adventure')
---
INSERT IGNORE INTO genres (name, slug) VALUES ('Crime','crime')
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Interstellar','interstellar','A team of explorers travel through a wormhole in space in an attempt to ensure humanitys survival.','Christopher Nolan',2014,169,8.7,4,35.90,1)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('The Dark Knight','the-dark-knight','When the Joker wreaks havoc on Gotham City, Batman must accept one of the greatest tests of his ability to fight injustice.','Christopher Nolan',2008,152,9.0,2,32.90,1)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Inception','inception','A thief who steals corporate secrets through dream-sharing technology is given the task of planting an idea into a CEOs mind.','Christopher Nolan',2010,148,8.8,6,32.90,1)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('The Matrix','the-matrix','A computer hacker learns about the true nature of his reality and his role in the war against its controllers.','The Wachowskis',1999,136,8.7,4,28.90,0)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Parasite','parasite','Greed and class discrimination threaten the symbiotic relationship between the wealthy Park family and the destitute Kim clan.','Bong Joon Ho',2019,132,8.5,10,30.90,1)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Pulp Fiction','pulp-fiction','The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.','Quentin Tarantino',1994,154,8.9,10,28.90,0)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Spirited Away','spirited-away','A sullen girl wanders into a world ruled by gods, witches, and spirits while her parents undergo a mysterious transformation.','Hayao Miyazaki',2001,125,8.6,7,27.90,0)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('The Godfather','the-godfather','An organized crime dynastys aging patriarch transfers control of his empire to his reluctant son.','Francis Ford Coppola',1972,175,9.2,10,29.90,0)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Blade Runner 2049','blade-runner-2049','A young blade runners discovery of a long-buried secret leads him to track down former blade runner Rick Deckard.','Denis Villeneuve',2017,164,8.0,4,33.90,0)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('Dune','dune','A noble family becomes embroiled in a war for the galaxys most valuable asset while its heir is troubled by dark visions.','Denis Villeneuve',2021,155,8.0,4,36.90,1)
---
INSERT IGNORE INTO movies (title, slug, synopsis, director, release_year, duration_min, rating, genre_id, price, is_featured) VALUES ('The Grand Budapest Hotel','grand-budapest-hotel','A writer encounters the owner of an aging hotel who tells of his early years as a lobby boy under an exceptional concierge.','Wes Anderson',2014,99,8.1,3,26.90,0)
---
INSERT IGNORE INTO combos (name, description, price) VALUES ('Classic Duo','1 large popcorn + 1 medium soda. The perfect pair for movie night.',24.90)
---
INSERT IGNORE INTO combos (name, description, price) VALUES ('Mega Family','2 large popcorns + 2 medium sodas + 1 nachos. Feed the whole crew.',59.90)
---
INSERT IGNORE INTO combos (name, description, price) VALUES ('Solo VIP','1 premium popcorn with butter and cheese + 1 large soda + 1 candy box.',34.90)
---
INSERT IGNORE INTO combos (name, description, price) VALUES ('Sweet Dreams','1 large caramel popcorn + 2 churros + 1 hot chocolate.',38.90)
---
INSERT IGNORE INTO combos (name, description, price) VALUES ('Beer and Bites','2 craft beers + 1 nachos with dips + 1 mini pizza.',49.90)
---
INSERT IGNORE INTO coupons (code, discount_type, discount_value, min_order_value, max_uses, expires_at) VALUES ('WELCOME10','percent',10.00,20.00,100,DATE_ADD(NOW(), INTERVAL 1 YEAR))
---
INSERT IGNORE INTO coupons (code, discount_type, discount_value, min_order_value, max_uses, expires_at) VALUES ('FIRSTBUY','fixed',15.00,50.00,50,DATE_ADD(NOW(), INTERVAL 6 MONTH))
