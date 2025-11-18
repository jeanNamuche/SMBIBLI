-- Tabla para preguntas del quiz
CREATE TABLE IF NOT EXISTS quiz_preguntas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_libro INT NOT NULL,
    texto_pregunta VARCHAR(500) NOT NULL,
    tipo ENUM('multiple_choice', 'verdadero_falso') DEFAULT 'multiple_choice',
    numero_pregunta INT,
    estado INT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_libro) REFERENCES libro(id) ON DELETE CASCADE,
    UNIQUE KEY unique_libro_numero (id_libro, numero_pregunta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla para opciones de respuesta (para multiple_choice)
CREATE TABLE IF NOT EXISTS quiz_opciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pregunta INT NOT NULL,
    texto_opcion VARCHAR(255) NOT NULL,
    es_correcta INT DEFAULT 0,
    orden INT,
    estado INT DEFAULT 1,
    FOREIGN KEY (id_pregunta) REFERENCES quiz_preguntas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla para rompecabezas
CREATE TABLE IF NOT EXISTS quiz_rompecabezas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_libro INT NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    instrucciones VARCHAR(500),
    estado INT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_libro) REFERENCES libro(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla para piezas del rompecabezas
CREATE TABLE IF NOT EXISTS quiz_rompecabezas_piezas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_rompecabezas INT NOT NULL,
    texto_pieza VARCHAR(255) NOT NULL,
    posicion_correcta INT NOT NULL,
    orden_display INT,
    estado INT DEFAULT 1,
    FOREIGN KEY (id_rompecabezas) REFERENCES quiz_rompecabezas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla para registrar intentos del estudiante
CREATE TABLE IF NOT EXISTS quiz_intentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_libro INT NOT NULL,
    tipo ENUM('quiz', 'rompecabezas') NOT NULL,
    puntuacion INT,
    respuestas JSON,
    estado INT DEFAULT 1,
    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libro(id) ON DELETE CASCADE,
    INDEX idx_estudiante_libro (id_estudiante, id_libro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
