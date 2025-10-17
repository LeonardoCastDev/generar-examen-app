-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 03-09-2025 a las 18:53:44
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `generador_examenes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banco_preguntas`
--

CREATE TABLE `banco_preguntas` (
  `id_pregunta_banco` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `enunciado` text NOT NULL,
  `tipo_pregunta` enum('multiple','verdadero_falso','abierta') NOT NULL,
  `nivel_dificultad` enum('facil','medio','dificil') DEFAULT 'medio',
  `puntuacion` decimal(3,1) DEFAULT 1.0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `banco_preguntas`
--

INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `fecha_creacion`, `activa`) VALUES
(1, 1, '¿Cuál es el resultado de 2 + 2?', 'multiple', 'facil', 1.0, '2025-08-20 18:36:36', 1),
(2, 1, '¿Es verdadero que 5 > 3?', 'verdadero_falso', 'facil', 1.0, '2025-08-20 18:36:36', 1),
(3, 1, 'Resuelve: 3x + 5 = 14', 'abierta', 'medio', 2.0, '2025-08-20 18:36:36', 1),
(4, 1, '¿Cuál es la raíz cuadrada de 16?', 'multiple', 'medio', 1.5, '2025-08-20 18:36:36', 1),
(5, 1, 'El número π es irracional', 'verdadero_falso', 'medio', 1.0, '2025-08-20 18:36:36', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_generador`
--

CREATE TABLE `configuracion_generador` (
  `id_config` int(11) NOT NULL,
  `clave_config` varchar(100) NOT NULL,
  `valor_config` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_dato` enum('string','number','boolean','json') DEFAULT 'string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_generador`
--

INSERT INTO `configuracion_generador` (`id_config`, `clave_config`, `valor_config`, `descripcion`, `tipo_dato`) VALUES
(1, 'preguntas_por_examen', '10', 'Número de preguntas por examen generado', 'number'),
(2, 'ruta_pdfs', 'uploads/pdfs/', 'Ruta donde se guardan los PDFs', 'string'),
(3, 'formato_nombre_pdf', 'examen_[ID]_[FECHA].pdf', 'Formato del nombre de archivo PDF', 'string'),
(4, 'permitir_repetir_preguntas', 'false', 'Permitir que se repitan preguntas en el mismo examen', 'boolean'),
(5, 'tiempo_limite_generacion', '30', 'Tiempo límite en segundos para generar un PDF', 'number');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_generados`
--

CREATE TABLE `examenes_generados` (
  `id_examen` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `total_preguntas` int(11) DEFAULT 10,
  `nivel_dificultad` enum('facil','medio','dificil','mixto') DEFAULT 'mixto',
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_guardado` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `examenes_generados`
--

INSERT INTO `examenes_generados` (`id_examen`, `id_usuario`, `id_materia`, `titulo`, `descripcion`, `total_preguntas`, `nivel_dificultad`, `fecha_generacion`, `fecha_guardado`, `activo`) VALUES
(1, 2, 1, 'prueba1', 'ola', 5, 'mixto', '2025-08-27 21:52:47', '2025-08-27 21:52:47', 1),
(3, 2, 1, 'Examen de Matemáticas - 29/8/2025', 'el jiras la chupa', 5, 'mixto', '2025-08-29 18:43:11', '2025-08-29 18:43:11', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_generaciones`
--

CREATE TABLE `historial_generaciones` (
  `id_historial` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `tipo_accion` enum('generar_examen','guardar_examen','generar_pdf','descargar_pdf') NOT NULL,
  `fecha_accion` timestamp NOT NULL DEFAULT current_timestamp(),
  `detalles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_generaciones`
--

INSERT INTO `historial_generaciones` (`id_historial`, `id_usuario`, `id_materia`, `tipo_accion`, `fecha_accion`, `detalles`) VALUES
(1, 2, 1, 'generar_examen', '2025-08-27 21:52:47', '{\"id_examen\":\"1\",\"total_preguntas\":5,\"dificultad\":\"mixto\"}'),
(2, 2, 1, 'generar_examen', '2025-08-29 18:43:11', '{\"id_examen\":\"3\",\"total_preguntas\":5,\"dificultad\":\"mixto\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `descripcion`, `activa`, `fecha_creacion`) VALUES
(1, 'Matemáticas', 'Preguntas de matemáticas básicas y avanzadas', 1, '2025-08-20 18:36:36'),
(2, 'Historia', 'Historia universal y nacional', 1, '2025-08-20 18:36:36'),
(3, 'Ciencias Naturales', 'Biología, física y química', 1, '2025-08-20 18:36:36'),
(4, 'Literatura', 'Literatura clásica y contemporánea', 1, '2025-08-20 18:36:36'),
(5, 'Geografía', 'Geografía mundial y local', 1, '2025-08-20 18:36:36'),
(6, 'Inglés', 'Gramática y vocabulario en inglés', 1, '2025-08-20 18:36:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones_banco`
--

CREATE TABLE `opciones_banco` (
  `id_opcion_banco` int(11) NOT NULL,
  `id_pregunta_banco` int(11) NOT NULL,
  `letra_opcion` char(1) NOT NULL,
  `texto_opcion` text NOT NULL,
  `es_correcta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `opciones_banco`
--

INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(1, 1, 'A', '3', 0),
(2, 1, 'B', '4', 1),
(3, 1, 'C', '5', 0),
(4, 1, 'D', '6', 0),
(5, 4, 'A', '2', 0),
(6, 4, 'B', '3', 0),
(7, 4, 'C', '4', 1),
(8, 4, 'D', '8', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pdfs_examenes`
--

CREATE TABLE `pdfs_examenes` (
  `id_pdf` int(11) NOT NULL,
  `id_examen` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tamano_archivo` bigint(20) DEFAULT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_pdf` enum('examen','examen_respuestas','solo_respuestas') DEFAULT 'examen',
  `estado` enum('generando','completado','error') DEFAULT 'completado',
  `descargas` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_pdf`
--

CREATE TABLE `plantillas_pdf` (
  `id_plantilla` int(11) NOT NULL,
  `nombre_plantilla` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `configuracion_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracion_json`)),
  `es_default` tinyint(1) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantillas_pdf`
--

INSERT INTO `plantillas_pdf` (`id_plantilla`, `nombre_plantilla`, `descripcion`, `configuracion_json`, `es_default`, `activa`, `fecha_creacion`) VALUES
(1, 'Plantilla Básica', 'Plantilla simple en blanco y negro', '{\"fuente\": \"Arial\", \"tamaño_fuente\": 12, \"margen_superior\": 20, \"margen_inferior\": 20, \"margen_izquierdo\": 15, \"margen_derecho\": 15, \"mostrar_logo\": false, \"color_titulo\": \"#000000\"}', 1, 1, '2025-08-20 18:36:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_examen`
--

CREATE TABLE `preguntas_examen` (
  `id_pregunta_examen` int(11) NOT NULL,
  `id_examen` int(11) NOT NULL,
  `id_pregunta_banco` int(11) NOT NULL,
  `numero_pregunta` int(11) NOT NULL,
  `puntuacion_asignada` decimal(3,1) DEFAULT 1.0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_examen`
--

INSERT INTO `preguntas_examen` (`id_pregunta_examen`, `id_examen`, `id_pregunta_banco`, `numero_pregunta`, `puntuacion_asignada`) VALUES
(1, 1, 2, 1, 1.0),
(2, 1, 5, 2, 1.0),
(3, 1, 3, 3, 2.0),
(4, 1, 1, 4, 1.0),
(5, 1, 4, 5, 1.5),
(6, 3, 2, 1, 1.0),
(7, 3, 1, 2, 1.0),
(8, 3, 3, 3, 2.0),
(9, 3, 5, 4, 1.0),
(10, 3, 4, 5, 1.5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_banco`
--

CREATE TABLE `respuestas_banco` (
  `id_respuesta_banco` int(11) NOT NULL,
  `id_pregunta_banco` int(11) NOT NULL,
  `respuesta_correcta` text NOT NULL,
  `es_exacta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuestas_banco`
--

INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(1, 3, 'x = 3', 1),
(2, 3, '3', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `username`, `email`, `password_hash`, `nombre`, `apellido`, `fecha_registro`, `ultimo_acceso`, `activo`) VALUES
(2, 'gudman', 'saulgud@gmail.com', '$2y$10$5Dj40Hvjvc3Bebiacz4Q8OBGHNTnBTczo9wHPLZDlpd84W7ypLeaq', 'saul', 'gudman', '2025-08-27 17:06:45', '2025-08-29 18:41:52', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `banco_preguntas`
--
ALTER TABLE `banco_preguntas`
  ADD PRIMARY KEY (`id_pregunta_banco`),
  ADD KEY `idx_materia_dificultad` (`id_materia`,`nivel_dificultad`),
  ADD KEY `idx_tipo_pregunta` (`tipo_pregunta`),
  ADD KEY `idx_banco_materia` (`id_materia`),
  ADD KEY `idx_banco_activa` (`activa`);

--
-- Indices de la tabla `configuracion_generador`
--
ALTER TABLE `configuracion_generador`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `clave_config` (`clave_config`);

--
-- Indices de la tabla `examenes_generados`
--
ALTER TABLE `examenes_generados`
  ADD PRIMARY KEY (`id_examen`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`fecha_generacion`),
  ADD KEY `idx_examenes_usuario` (`id_usuario`);

--
-- Indices de la tabla `historial_generaciones`
--
ALTER TABLE `historial_generaciones`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`fecha_accion`),
  ADD KEY `idx_tipo_accion` (`tipo_accion`),
  ADD KEY `idx_historial_usuario` (`id_usuario`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`);

--
-- Indices de la tabla `opciones_banco`
--
ALTER TABLE `opciones_banco`
  ADD PRIMARY KEY (`id_opcion_banco`),
  ADD KEY `idx_pregunta_opcion` (`id_pregunta_banco`,`letra_opcion`);

--
-- Indices de la tabla `pdfs_examenes`
--
ALTER TABLE `pdfs_examenes`
  ADD PRIMARY KEY (`id_pdf`),
  ADD KEY `idx_usuario_fecha` (`id_usuario`,`fecha_generacion`),
  ADD KEY `idx_examen_tipo` (`id_examen`,`tipo_pdf`),
  ADD KEY `idx_pdfs_usuario` (`id_usuario`);

--
-- Indices de la tabla `plantillas_pdf`
--
ALTER TABLE `plantillas_pdf`
  ADD PRIMARY KEY (`id_plantilla`);

--
-- Indices de la tabla `preguntas_examen`
--
ALTER TABLE `preguntas_examen`
  ADD PRIMARY KEY (`id_pregunta_examen`),
  ADD UNIQUE KEY `unique_examen_pregunta` (`id_examen`,`numero_pregunta`),
  ADD KEY `id_pregunta_banco` (`id_pregunta_banco`),
  ADD KEY `idx_examen_orden` (`id_examen`,`numero_pregunta`);

--
-- Indices de la tabla `respuestas_banco`
--
ALTER TABLE `respuestas_banco`
  ADD PRIMARY KEY (`id_respuesta_banco`),
  ADD KEY `id_pregunta_banco` (`id_pregunta_banco`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `banco_preguntas`
--
ALTER TABLE `banco_preguntas`
  MODIFY `id_pregunta_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `configuracion_generador`
--
ALTER TABLE `configuracion_generador`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `examenes_generados`
--
ALTER TABLE `examenes_generados`
  MODIFY `id_examen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `historial_generaciones`
--
ALTER TABLE `historial_generaciones`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `opciones_banco`
--
ALTER TABLE `opciones_banco`
  MODIFY `id_opcion_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pdfs_examenes`
--
ALTER TABLE `pdfs_examenes`
  MODIFY `id_pdf` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plantillas_pdf`
--
ALTER TABLE `plantillas_pdf`
  MODIFY `id_plantilla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `preguntas_examen`
--
ALTER TABLE `preguntas_examen`
  MODIFY `id_pregunta_examen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `respuestas_banco`
--
ALTER TABLE `respuestas_banco`
  MODIFY `id_respuesta_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `banco_preguntas`
--
ALTER TABLE `banco_preguntas`
  ADD CONSTRAINT `banco_preguntas_ibfk_1` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`);

--
-- Filtros para la tabla `examenes_generados`
--
ALTER TABLE `examenes_generados`
  ADD CONSTRAINT `examenes_generados_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `examenes_generados_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`);

--
-- Filtros para la tabla `historial_generaciones`
--
ALTER TABLE `historial_generaciones`
  ADD CONSTRAINT `historial_generaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_generaciones_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`);

--
-- Filtros para la tabla `opciones_banco`
--
ALTER TABLE `opciones_banco`
  ADD CONSTRAINT `opciones_banco_ibfk_1` FOREIGN KEY (`id_pregunta_banco`) REFERENCES `banco_preguntas` (`id_pregunta_banco`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pdfs_examenes`
--
ALTER TABLE `pdfs_examenes`
  ADD CONSTRAINT `pdfs_examenes_ibfk_1` FOREIGN KEY (`id_examen`) REFERENCES `examenes_generados` (`id_examen`) ON DELETE CASCADE,
  ADD CONSTRAINT `pdfs_examenes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `preguntas_examen`
--
ALTER TABLE `preguntas_examen`
  ADD CONSTRAINT `preguntas_examen_ibfk_1` FOREIGN KEY (`id_examen`) REFERENCES `examenes_generados` (`id_examen`) ON DELETE CASCADE,
  ADD CONSTRAINT `preguntas_examen_ibfk_2` FOREIGN KEY (`id_pregunta_banco`) REFERENCES `banco_preguntas` (`id_pregunta_banco`);

--
-- Filtros para la tabla `respuestas_banco`
--
ALTER TABLE `respuestas_banco`
  ADD CONSTRAINT `respuestas_banco_ibfk_1` FOREIGN KEY (`id_pregunta_banco`) REFERENCES `banco_preguntas` (`id_pregunta_banco`) ON DELETE CASCADE;



-- *** INSERTS PARA banco_preguntas (Total 55 preguntas nuevas, el id_pregunta_banco comienza en 6) ***

-- Materia 1: Matemáticas (5 nuevas preguntas, id_materia=1)
INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `activa`) VALUES
(6, 1, '¿Cuántos lados tiene un hexágono?', 'multiple', 'facil', 1.0, 1),
(7, 1, 'Un triángulo con 3 lados iguales se llama isósceles.', 'verdadero_falso', 'facil', 1.0, 1),
(8, 1, 'Calcula el área de un cuadrado de lado 5 cm.', 'abierta', 'medio', 1.5, 1),
(9, 1, '¿Cuál es el valor de 2^3?', 'multiple', 'medio', 1.0, 1),
(10, 1, 'La suma de los ángulos internos de un cuadrilátero es 360 grados.', 'verdadero_falso', 'facil', 1.0, 1);

-- Materia 2: Historia (10 preguntas, id_materia=2)
INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `activa`) VALUES
(11, 2, '¿En qué año cayó el Muro de Berlín?', 'multiple', 'medio', 1.0, 1),
(12, 2, '¿Quién fue el primer presidente de los Estados Unidos?', 'multiple', 'facil', 1.0, 1),
(13, 2, 'El Renacimiento comenzó en Italia.', 'verdadero_falso', 'facil', 1.0, 1),
(14, 2, 'Escribe el nombre del explorador que completó la primera circunnavegación del mundo (aunque murió en el camino).', 'abierta', 'dificil', 2.0, 1),
(15, 2, '¿Qué civilización construyó las pirámides de Giza?', 'multiple', 'facil', 1.0, 1),
(16, 2, '¿En qué siglo ocurrió la Revolución Francesa?', 'multiple', 'medio', 1.5, 1),
(17, 2, 'La Segunda Guerra Mundial terminó en 1945.', 'verdadero_falso', 'facil', 1.0, 1),
(18, 2, '¿Quién es conocido como el "Padre de la Historia"?', 'multiple', 'dificil', 1.5, 1),
(19, 2, 'Explica brevemente la importancia de la Batalla de Stalingrado en la Segunda Guerra Mundial.', 'abierta', 'dificil', 2.5, 1),
(20, 2, 'La Edad Media comienza con la caída del Imperio Romano de Occidente.', 'verdadero_falso', 'medio', 1.0, 1);

-- Materia 3: Ciencias Naturales (10 preguntas, id_materia=3)
INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `activa`) VALUES
(21, 3, '¿Cuál es la fórmula química del agua?', 'multiple', 'facil', 1.0, 1),
(22, 3, 'El sol es una estrella.', 'verdadero_falso', 'facil', 1.0, 1),
(23, 3, 'Describe el proceso de fotosíntesis.', 'abierta', 'medio', 2.0, 1),
(24, 3, '¿Qué órgano del cuerpo humano bombea la sangre?', 'multiple', 'facil', 1.0, 1),
(25, 3, '¿Cuál es la unidad básica de la vida?', 'multiple', 'medio', 1.0, 1),
(26, 3, 'La fuerza de gravedad nos mantiene unidos a la Tierra.', 'verdadero_falso', 'facil', 1.0, 1),
(27, 3, '¿Qué gas es el más abundante en la atmósfera terrestre?', 'multiple', 'dificil', 1.5, 1),
(28, 3, '¿Cuál es la ley que establece que "la materia no se crea ni se destruye, solo se transforma"?', 'multiple', 'dificil', 2.0, 1),
(29, 3, 'Menciona los tres estados fundamentales de la materia.', 'abierta', 'medio', 1.5, 1),
(30, 3, 'Los virus son organismos vivos.', 'verdadero_falso', 'medio', 1.0, 1);

-- Materia 4: Literatura (10 preguntas, id_materia=4)
INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `activa`) VALUES
(31, 4, '¿Quién escribió "Cien años de soledad"?', 'multiple', 'facil', 1.0, 1),
(32, 4, 'Un soneto es un tipo de poema que tiene siempre 14 versos.', 'verdadero_falso', 'facil', 1.0, 1),
(33, 4, 'Define el concepto de "Metáfora" en la poesía.', 'abierta', 'medio', 2.0, 1),
(34, 4, '¿Cuál es la obra más famosa de Miguel de Cervantes?', 'multiple', 'facil', 1.0, 1),
(35, 4, '¿Quién es el autor de "Romeo y Julieta"?', 'multiple', 'facil', 1.0, 1),
(36, 4, 'El "boom" latinoamericano fue un movimiento de arte plástico.', 'verdadero_falso', 'medio', 1.0, 1),
(37, 4, '¿Qué tipo de narrador utiliza el pronombre "yo"?', 'multiple', 'medio', 1.5, 1),
(38, 4, '¿En qué país se desarrolla la novela "Crimen y castigo"?', 'multiple', 'dificil', 1.5, 1),
(39, 4, 'Menciona tres géneros literarios principales.', 'abierta', 'medio', 1.5, 1),
(40, 4, 'Un cuento es generalmente más extenso que una novela.', 'verdadero_falso', 'facil', 1.0, 1);

-- Materia 5: Geografía (10 preguntas, id_materia=5)
INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `activa`) VALUES
(41, 5, '¿Cuál es el océano más grande del mundo?', 'multiple', 'facil', 1.0, 1),
(42, 5, 'La capital de Australia es Sídney.', 'verdadero_falso', 'facil', 1.0, 1),
(43, 5, '¿Qué es un paralelo en términos geográficos?', 'abierta', 'medio', 2.0, 1),
(44, 5, '¿En qué continente se encuentra el desierto del Sahara?', 'multiple', 'facil', 1.0, 1),
(45, 5, '¿Cuál es la montaña más alta del mundo?', 'multiple', 'medio', 1.0, 1),
(46, 5, 'Brasil es el país más grande de Sudamérica.', 'verdadero_falso', 'facil', 1.0, 1),
(47, 5, '¿Qué nombre recibe la línea imaginaria que divide la Tierra en hemisferio oriental y occidental?', 'multiple', 'dificil', 1.5, 1),
(48, 5, '¿Cuál es el río más caudaloso del mundo?', 'multiple', 'dificil', 1.5, 1),
(49, 5, 'Menciona el nombre de la capital de Japón.', 'abierta', 'facil', 1.0, 1),
(50, 5, 'El clima polar se encuentra solo en el Polo Norte.', 'verdadero_falso', 'medio', 1.0, 1);

-- Materia 6: Inglés (10 preguntas, id_materia=6)
INSERT INTO `banco_preguntas` (`id_pregunta_banco`, `id_materia`, `enunciado`, `tipo_pregunta`, `nivel_dificultad`, `puntuacion`, `activa`) VALUES
(51, 6, 'What is the plural of "child"?', 'multiple', 'facil', 1.0, 1),
(52, 6, '"She go to the store every day" is grammatically correct.', 'verdadero_falso', 'facil', 1.0, 1),
(53, 6, 'Write the past tense of the verb "to eat".', 'abierta', 'facil', 1.0, 1),
(54, 6, 'Which word is a synonym for "happy"?', 'multiple', 'medio', 1.0, 1),
(55, 6, 'Complete the sentence: "I ___ watching TV when the phone rang."', 'multiple', 'medio', 1.5, 1),
(56, 6, 'The word "library" means bookstore in Spanish.', 'verdadero_falso', 'medio', 1.0, 1),
(57, 6, 'What is the meaning of the idiom "break a leg"?', 'multiple', 'dificil', 1.5, 1),
(58, 6, 'Which of these is a modal verb?', 'multiple', 'medio', 1.0, 1),
(59, 6, 'Write a sentence in the present perfect tense.', 'abierta', 'dificil', 2.0, 1),
(60, 6, 'The contraction "I''ll" stands for "I will".', 'verdadero_falso', 'facil', 1.0, 1);


-- *** INSERTS PARA opciones_banco (Opciones de preguntas de Opción Múltiple, id_opcion_banco comienza en 9) ***

-- Matemáticas
INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(9, 6, 'A', '5', 0),
(10, 6, 'B', '6', 1),
(11, 6, 'C', '7', 0),
(12, 6, 'D', '8', 0),
(13, 9, 'A', '6', 0),
(14, 9, 'B', '8', 1),
(15, 9, 'C', '9', 0),
(16, 9, 'D', '4', 0);

-- Historia
INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(17, 11, 'A', '1989', 1),
(18, 11, 'B', '1991', 0),
(19, 11, 'C', '1985', 0),
(20, 11, 'D', '1990', 0),
(21, 12, 'A', 'Thomas Jefferson', 0),
(22, 12, 'B', 'George Washington', 1),
(23, 12, 'C', 'Abraham Lincoln', 0),
(24, 12, 'D', 'John Adams', 0),
(25, 15, 'A', 'Romanos', 0),
(26, 15, 'B', 'Sumerios', 0),
(27, 15, 'C', 'Egipcios', 1),
(28, 15, 'D', 'Griegos', 0),
(29, 16, 'A', 'XVII', 0),
(30, 16, 'B', 'XVIII', 1),
(31, 16, 'C', 'XIX', 0),
(32, 16, 'D', 'XVI', 0),
(33, 18, 'A', 'Tucídides', 0),
(34, 18, 'B', 'Heródoto', 1),
(35, 18, 'C', 'Platón', 0),
(36, 18, 'D', 'Aristóteles', 0);

-- Ciencias Naturales
INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(37, 21, 'A', 'CO2', 0),
(38, 21, 'B', 'H2O', 1),
(39, 21, 'C', 'O2', 0),
(40, 21, 'D', 'NaCl', 0),
(41, 24, 'A', 'Pulmón', 0),
(42, 24, 'B', 'Hígado', 0),
(43, 24, 'C', 'Corazón', 1),
(44, 24, 'D', 'Estómago', 0),
(45, 25, 'A', 'Átomo', 0),
(46, 25, 'B', 'Molécula', 0),
(47, 25, 'C', 'Célula', 1),
(48, 25, 'D', 'Tejido', 0),
(49, 27, 'A', 'Oxígeno', 0),
(50, 27, 'B', 'Nitrógeno', 1),
(51, 27, 'C', 'Argón', 0),
(52, 27, 'D', 'Dióxido de Carbono', 0),
(53, 28, 'A', 'Ley de Ohm', 0),
(54, 28, 'B', 'Ley de la Conservación de la Energía', 0),
(55, 28, 'C', 'Ley de la Conservación de la Materia', 1),
(56, 28, 'D', 'Ley de Boyle', 0);

-- Literatura
INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(57, 31, 'A', 'Julio Cortázar', 0),
(58, 31, 'B', 'Gabriel García Márquez', 1),
(59, 31, 'C', 'Mario Vargas Llosa', 0),
(60, 31, 'D', 'Carlos Fuentes', 0),
(61, 34, 'A', 'La Galatea', 0),
(62, 34, 'B', 'Novelas Ejemplares', 0),
(63, 34, 'C', 'Don Quijote de la Mancha', 1),
(64, 34, 'D', 'Viaje del Parnaso', 0),
(65, 35, 'A', 'Charles Dickens', 0),
(66, 35, 'B', 'William Shakespeare', 1),
(67, 35, 'C', 'Jane Austen', 0),
(68, 35, 'D', 'Edgar Allan Poe', 0),
(69, 37, 'A', 'Omnisciente', 0),
(70, 37, 'B', 'Testigo', 0),
(71, 37, 'C', 'Protagonista', 1),
(72, 37, 'D', 'Tercera persona', 0),
(73, 38, 'A', 'Francia', 0),
(74, 38, 'B', 'Reino Unido', 0),
(75, 38, 'C', 'Rusia', 1),
(76, 38, 'D', 'Italia', 0);

-- Geografía
INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(77, 41, 'A', 'Atlántico', 0),
(78, 41, 'B', 'Índico', 0),
(79, 41, 'C', 'Pacífico', 1),
(80, 41, 'D', 'Ártico', 0),
(81, 44, 'A', 'Asia', 0),
(82, 44, 'B', 'África', 1),
(83, 44, 'C', 'América del Sur', 0),
(84, 44, 'D', 'Oceanía', 0),
(85, 45, 'A', 'K2', 0),
(86, 45, 'B', 'Everest', 1),
(87, 45, 'C', 'Kangchenjunga', 0),
(88, 45, 'D', 'Lhotse', 0),
(89, 47, 'A', 'Ecuador', 0),
(90, 47, 'B', 'Meridiano de Greenwich', 1),
(91, 47, 'C', 'Trópico de Cáncer', 0),
(92, 47, 'D', 'Círculo Polar Ártico', 0),
(93, 48, 'A', 'Nilo', 0),
(94, 48, 'B', 'Misisipi', 0),
(95, 48, 'C', 'Amazonas', 1),
(96, 48, 'D', 'Yangtsé', 0);

-- Inglés
INSERT INTO `opciones_banco` (`id_opcion_banco`, `id_pregunta_banco`, `letra_opcion`, `texto_opcion`, `es_correcta`) VALUES
(97, 51, 'A', 'Childs', 0),
(98, 51, 'B', 'Children', 1),
(99, 51, 'C', 'Childes', 0),
(100, 51, 'D', 'Child''s', 0),
(101, 54, 'A', 'Sad', 0),
(102, 54, 'B', 'Joyful', 1),
(103, 54, 'C', 'Angry', 0),
(104, 54, 'D', 'Tired', 0),
(105, 55, 'A', 'am', 0),
(106, 55, 'B', 'was', 1),
(107, 55, 'C', 'were', 0),
(108, 55, 'D', 'is', 0),
(109, 57, 'A', 'Be careful', 0),
(110, 57, 'B', 'Good luck', 1),
(111, 57, 'C', 'Hurry up', 0),
(112, 57, 'D', 'Be quiet', 0),
(113, 58, 'A', 'Running', 0),
(114, 58, 'B', 'Will', 1),
(115, 58, 'C', 'Eats', 0),
(116, 58, 'D', 'Quickly', 0);


-- *** INSERTS PARA respuestas_banco (Respuestas de preguntas Abiertas, id_respuesta_banco comienza en 3) ***

-- Matemáticas
INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(3, 8, '25', 1);

-- Historia
INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(4, 14, 'Fernando de Magallanes', 1),
(5, 19, 'Fue un punto de inflexión en el Frente Oriental que detuvo el avance alemán.', 0);

-- Ciencias Naturales
INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(6, 23, 'Proceso por el cual las plantas convierten la luz solar, agua y dióxido de carbono en glucosa y oxígeno.', 0),
(7, 29, 'Sólido, Líquido, Gaseoso', 0);

-- Literatura
INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(8, 33, 'Figura retórica que consiste en identificar un término real con otro imaginario para expresar una idea.', 0),
(9, 39, 'Lírico, Narrativo, Dramático', 0);

-- Geografía
INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(10, 43, 'Círculo imaginario paralelo al Ecuador que mide la latitud.', 0),
(11, 49, 'Tokio', 1);

-- Inglés
INSERT INTO `respuestas_banco` (`id_respuesta_banco`, `id_pregunta_banco`, `respuesta_correcta`, `es_exacta`) VALUES
(12, 53, 'ate', 1),
(13, 59, 'I have eaten breakfast.', 0);


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
