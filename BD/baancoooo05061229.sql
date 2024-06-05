CREATE DATABASE CineWeeknd;

USE CineWeeknd;

CREATE TABLE users_register (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    UNIQUE (email)
);

CREATE TABLE filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255),
    ano INT,
    genero VARCHAR(100),
    preco INT
);

INSERT INTO filmes (titulo, ano, genero, preco) VALUES
('Inatividade Paranormal', 2009, 'Terror/Comédia', 20),
('Vampiros que se mordam', 2009, 'Comédia', 20),
('Espartalhoes', 2010, 'Comédia', 20),
('Super Velozes Mega Furiosos', 2010, 'Comédia', 20),
('Super-Heroi: O Filme', 2011, 'Comédia', 20),
('Liga da Injustiça', 2009, 'Comédia', 20),
('KickAss', 2014, 'Ação/Comédia', 20),
('Todo Mundo em Panico', 2000, 'Terror/Comédia', 20);

UPDATE filmes SET preco = 15 WHERE preco != 15;

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filme_id INT NOT NULL,
    quantity INT NOT NULL,
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users_register(id),
    FOREIGN KEY (filme_id) REFERENCES filmes(id)
);

ALTER TABLE purchases ADD COLUMN combo_id INT AFTER filme_id;
ALTER TABLE purchases MODIFY filme_id INT NULL;



drop table purchases;

select * from filmes;

CREATE TABLE combos(
id INT AUTO_INCREMENT PRIMARY KEY,
nome varchar (100),
preco int
);

INSERT INTO combos (id, nome, preco) VALUES
(1, 'Pipoca M + Coca-Cola 300Ml', 20),
(2, '2 Pipocas G + 2 Coca-Cola 500Ml (Combo Casal)', 40),
(3, 'Pipoca GG + Coca-Cola 1L', 35);

select * from combos;