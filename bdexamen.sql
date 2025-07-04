CREATE DATABASE IF NOT EXISTS examenes;
USE examenes;

-- Tabla de Preguntas
CREATE TABLE Preguntas (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    texto_pregunta TEXT NOT NULL,
    area ENUM('Area1', 'Area2', 'Area3', 'Area4', 'Area5') NOT NULL,
    tipo_pregunta ENUM('Abierta', 'Cerrada') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Opciones
CREATE TABLE Opcion (
    id_opcion INT AUTO_INCREMENT PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    id_pregunta INT NOT NULL,
    resultado TEXT,
    FOREIGN KEY (id_pregunta) REFERENCES Preguntas(id_pregunta) ON DELETE CASCADE
);

-- Tabla de Examenes
CREATE TABLE Examenes (
    id_examen INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla intermedia ExamenPregunta
CREATE TABLE ExamenPregunta (
    id_examen INT NOT NULL,
    id_pregunta INT NOT NULL,
    orden INT NOT NULL,
    PRIMARY KEY (id_examen, id_pregunta),
    FOREIGN KEY (id_examen) REFERENCES Examenes(id_examen) ON DELETE CASCADE,
    FOREIGN KEY (id_pregunta) REFERENCES Preguntas(id_pregunta) ON DELETE CASCADE
);
