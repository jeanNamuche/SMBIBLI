-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-12-2025 a las 15:22:38
-- Versión del servidor: 8.0.42
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autor`
--

CREATE TABLE `autor` (
  `id` int NOT NULL,
  `autor` varchar(150) NOT NULL,
  `imagen` varchar(100) NOT NULL,
  `estado` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` text NOT NULL,
  `correo` varchar(100) NOT NULL,
  `foto` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_permisos`
--

CREATE TABLE `detalle_permisos` (
  `id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_permiso` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editorial`
--

CREATE TABLE `editorial` (
  `id` int NOT NULL,
  `editorial` varchar(150) NOT NULL,
  `estado` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `id` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `dni` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `estado` int NOT NULL DEFAULT '1',
  `grado` varchar(50) DEFAULT NULL,
  `seccion` varchar(50) DEFAULT NULL,
  `nivel` varchar(20) DEFAULT 'Primaria',
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `id_usuario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libro`
--

CREATE TABLE `libro` (
  `id` int NOT NULL,
  `titulo` text NOT NULL,
  `cantidad` int NOT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `lugar_estante` varchar(50) DEFAULT NULL,
  `id_autor` int NOT NULL,
  `id_editorial` int NOT NULL,
  `anio_edicion` date NOT NULL,
  `id_materia` int NOT NULL,
  `num_pagina` int NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(100) NOT NULL,
  `estado` int NOT NULL DEFAULT '1',
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `id` int NOT NULL,
  `materia` text NOT NULL,
  `estado` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamo`
--

CREATE TABLE `prestamo` (
  `id` int NOT NULL,
  `id_estudiante` int NOT NULL,
  `id_libro` int NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_devolucion` date NOT NULL,
  `cantidad` int NOT NULL,
  `observacion` text NOT NULL,
  `estado` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quiz_intentos`
--

CREATE TABLE `quiz_intentos` (
  `id` int NOT NULL,
  `id_estudiante` int NOT NULL,
  `id_libro` int NOT NULL,
  `tipo` enum('quiz','rompecabezas') NOT NULL,
  `puntuacion` int DEFAULT NULL,
  `respuestas` json DEFAULT NULL,
  `estado` int DEFAULT '1',
  `fecha_intento` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quiz_opciones`
--

CREATE TABLE `quiz_opciones` (
  `id` int NOT NULL,
  `id_pregunta` int NOT NULL,
  `texto_opcion` varchar(255) NOT NULL,
  `es_correcta` int DEFAULT '0',
  `orden` int DEFAULT NULL,
  `estado` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quiz_preguntas`
--

CREATE TABLE `quiz_preguntas` (
  `id` int NOT NULL,
  `id_libro` int NOT NULL,
  `texto_pregunta` varchar(500) NOT NULL,
  `tipo` enum('multiple_choice','verdadero_falso') DEFAULT 'multiple_choice',
  `numero_pregunta` int DEFAULT NULL,
  `estado` int DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quiz_rompecabezas`
--

CREATE TABLE `quiz_rompecabezas` (
  `id` int NOT NULL,
  `id_libro` int NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `instrucciones` varchar(500) DEFAULT NULL,
  `estado` int DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quiz_rompecabezas_piezas`
--

CREATE TABLE `quiz_rompecabezas_piezas` (
  `id` int NOT NULL,
  `id_rompecabezas` int NOT NULL,
  `texto_pieza` varchar(255) NOT NULL,
  `posicion_correcta` int NOT NULL,
  `orden_display` int DEFAULT NULL,
  `estado` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `estado` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autor`
--
ALTER TABLE `autor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_permisos`
--
ALTER TABLE `detalle_permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_permiso` (`id_permiso`);

--
-- Indices de la tabla `editorial`
--
ALTER TABLE `editorial`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_estudiante_usuario` (`id_usuario`);

--
-- Indices de la tabla `libro`
--
ALTER TABLE `libro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_autor` (`id_autor`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_editorial` (`id_editorial`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prestamo`
--
ALTER TABLE `prestamo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_libro` (`id_libro`);

--
-- Indices de la tabla `quiz_intentos`
--
ALTER TABLE `quiz_intentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_libro` (`id_libro`),
  ADD KEY `idx_estudiante_libro` (`id_estudiante`,`id_libro`);

--
-- Indices de la tabla `quiz_opciones`
--
ALTER TABLE `quiz_opciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `quiz_preguntas`
--
ALTER TABLE `quiz_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_libro_numero` (`id_libro`,`numero_pregunta`);

--
-- Indices de la tabla `quiz_rompecabezas`
--
ALTER TABLE `quiz_rompecabezas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_libro` (`id_libro`);

--
-- Indices de la tabla `quiz_rompecabezas_piezas`
--
ALTER TABLE `quiz_rompecabezas_piezas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rompecabezas` (`id_rompecabezas`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autor`
--
ALTER TABLE `autor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_permisos`
--
ALTER TABLE `detalle_permisos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `editorial`
--
ALTER TABLE `editorial`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `libro`
--
ALTER TABLE `libro`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prestamo`
--
ALTER TABLE `prestamo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quiz_intentos`
--
ALTER TABLE `quiz_intentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quiz_opciones`
--
ALTER TABLE `quiz_opciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quiz_preguntas`
--
ALTER TABLE `quiz_preguntas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quiz_rompecabezas`
--
ALTER TABLE `quiz_rompecabezas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `quiz_rompecabezas_piezas`
--
ALTER TABLE `quiz_rompecabezas_piezas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_permisos`
--
ALTER TABLE `detalle_permisos`
  ADD CONSTRAINT `detalle_permisos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `detalle_permisos_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id`);

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `fk_estudiante_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `libro`
--
ALTER TABLE `libro`
  ADD CONSTRAINT `libro_ibfk_1` FOREIGN KEY (`id_autor`) REFERENCES `autor` (`id`),
  ADD CONSTRAINT `libro_ibfk_2` FOREIGN KEY (`id_editorial`) REFERENCES `editorial` (`id`),
  ADD CONSTRAINT `libro_ibfk_3` FOREIGN KEY (`id_materia`) REFERENCES `materia` (`id`);

--
-- Filtros para la tabla `prestamo`
--
ALTER TABLE `prestamo`
  ADD CONSTRAINT `prestamo_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`),
  ADD CONSTRAINT `prestamo_ibfk_2` FOREIGN KEY (`id_libro`) REFERENCES `libro` (`id`);

--
-- Filtros para la tabla `quiz_intentos`
--
ALTER TABLE `quiz_intentos`
  ADD CONSTRAINT `quiz_intentos_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_intentos_ibfk_2` FOREIGN KEY (`id_libro`) REFERENCES `libro` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `quiz_opciones`
--
ALTER TABLE `quiz_opciones`
  ADD CONSTRAINT `quiz_opciones_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `quiz_preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `quiz_preguntas`
--
ALTER TABLE `quiz_preguntas`
  ADD CONSTRAINT `quiz_preguntas_ibfk_1` FOREIGN KEY (`id_libro`) REFERENCES `libro` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `quiz_rompecabezas`
--
ALTER TABLE `quiz_rompecabezas`
  ADD CONSTRAINT `quiz_rompecabezas_ibfk_1` FOREIGN KEY (`id_libro`) REFERENCES `libro` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `quiz_rompecabezas_piezas`
--
ALTER TABLE `quiz_rompecabezas_piezas`
  ADD CONSTRAINT `quiz_rompecabezas_piezas_ibfk_1` FOREIGN KEY (`id_rompecabezas`) REFERENCES `quiz_rompecabezas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
