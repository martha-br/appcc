CREATE DATABASE IF NOT EXISTS appcc
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE appcc;

CREATE TABLE equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(100),
    temp_min DECIMAL(5,2) NOT NULL
);

CREATE TABLE registros_temperatura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipo_id INT NOT NULL,
    temperatura DECIMAL(5,2) NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    operario VARCHAR(100) NOT NULL,
    observaciones VARCHAR(400),
    FOREIGN KEY (equipo_id) REFERENCES equipos(id)
);