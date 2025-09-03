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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
