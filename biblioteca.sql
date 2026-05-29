-- Ejecutar en phpMyAdmin → pestaña SQL
CREATE DATABASE IF NOT EXISTS biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE biblioteca;

CREATE TABLE socios (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nro_socio INT          NOT NULL UNIQUE,
    nombre    VARCHAR(100) NOT NULL,
    email     VARCHAR(150) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    rol       ENUM('socio','admin') NOT NULL DEFAULT 'socio'
);

-- Admin por defecto: admin@biblioteca.com / admin1234
INSERT INTO socios (nro_socio, nombre, email, password, rol) VALUES
(1, 'Administrador', 'admin@biblioteca.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE libros (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    isbn       VARCHAR(20)  NOT NULL UNIQUE,
    titulo     VARCHAR(200) NOT NULL,
    autor      VARCHAR(150) NOT NULL,
    tema       VARCHAR(100) NOT NULL,
    editorial  VARCHAR(150) NOT NULL,
    anio       YEAR         NOT NULL,
    stock_total INT         NOT NULL DEFAULT 1,
    stock_disp  INT         NOT NULL DEFAULT 1
);

INSERT INTO libros (isbn, titulo, autor, tema, editorial, anio, stock_total, stock_disp) VALUES
('978-0001', 'Harry Potter y la Piedra Filosofal', 'J.K. Rowling',          'Fantasía',       'Salamandra', 1997, 5, 5),
('978-0002', '1984',                               'George Orwell',         'Distopía',       'Debolsillo', 1949, 3, 3),
('978-0003', 'El Principito',                      'Saint-Exupéry',         'Ficción clásica','Planeta',    1943, 4, 4),
('978-0004', 'Moby Dick',                          'Herman Melville',       'Aventura',       'Norma',      1851, 5, 5),
('978-0005', 'Cien años de soledad',               'Gabriel García Márquez','Realismo mágico','Sudamericana',1967,2, 2);

CREATE TABLE prestamos (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    socio_id         INT  NOT NULL,
    libro_id         INT  NOT NULL,
    fecha_retiro     DATE NOT NULL DEFAULT (CURRENT_DATE),
    fecha_devolucion DATE,
    FOREIGN KEY (socio_id) REFERENCES socios(id),
    FOREIGN KEY (libro_id) REFERENCES libros(id)
);
