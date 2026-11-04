-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-11-2026 a las 21:53:38
-- Versión del servidor: 10.4.32-MariaDB
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
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `desc_categoria` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Categoria de materiales en la biblioteca';

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `desc_categoria`) VALUES
(1, 'LIBRO'),
(2, 'TABLERO'),
(3, 'HERRAMIENTA'),
(4, 'MAPA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `activo`) VALUES
(1, 'Libros', 'Libros y publicaciones', 1),
(2, 'Revistas', 'Revistas y publicaciones periódicas', 1),
(3, 'Mapas', 'Mapas, planos y material geográfico', 1),
(4, 'Reglas', 'Reglas, escuadras y elementos de medición', 1),
(5, 'Tableros', 'Tableros y juegos de mesa', 1),
(6, 'Cajas', 'Cajas y elementos de almacenamiento', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_material`
--

CREATE TABLE `estado_material` (
  `id_estado_material` int(11) NOT NULL,
  `desc_estado_material` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `estado_material`
--

INSERT INTO `estado_material` (`id_estado_material`, `desc_estado_material`) VALUES
(1, 'buen estado'),
(2, 'roto'),
(3, 'Nuevo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `id_genero` int(11) NOT NULL,
  `desc_genero` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`id_genero`, `desc_genero`) VALUES
(1, 'Lengua y Literatura'),
(2, 'Biología'),
(3, 'Matemática'),
(4, 'Ética'),
(5, 'Poesía'),
(6, 'Talleres');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_acciones`
--

CREATE TABLE `historial_acciones` (
  `id_accion` int(11) NOT NULL,
  `tipo_accion` varchar(50) NOT NULL COMMENT 'prestamo, devolucion, baja_material, alta_material, modificacion_estado, observacion, etc',
  `tabla_afectada` varchar(50) DEFAULT NULL COMMENT 'prestamos, inventario, libros, etc',
  `id_registro` varchar(200) DEFAULT NULL COMMENT 'ID del registro afectado (id_prestamo, id_material, etc)',
  `descripcion` text NOT NULL COMMENT 'Descripción legible de la acción',
  `datos_antes` text DEFAULT NULL COMMENT 'JSON con datos antes del cambio (opcional)',
  `datos_despues` text DEFAULT NULL COMMENT 'JSON con datos después del cambio (opcional)',
  `dni_usuario` int(11) NOT NULL COMMENT 'DNI del usuario que realizó la acción',
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Log completo de acciones del sistema (historial universal)';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_observaciones`
--

CREATE TABLE `historial_observaciones` (
  `id_observacion` int(11) NOT NULL,
  `id_prestamo` int(11) NOT NULL,
  `dni_usuario` int(11) NOT NULL COMMENT 'DNI del bibliotecario que registra la observación',
  `observacion` text NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Observaciones sobre préstamos del historial (solo lectura)';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instrumentos_medicion`
--

CREATE TABLE `instrumentos_medicion` (
  `id_material` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `material_fabricacion` varchar(100) DEFAULT NULL,
  `longitud_cm` decimal(8,2) DEFAULT NULL,
  `ancho_cm` decimal(8,2) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_material` int(11) NOT NULL,
  `fecha_hora_ingreso` datetime NOT NULL,
  `dni_registro` varchar(255) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_estado_material` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Listado completo de los materiales que hay en la biblioteca';

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_material`, `fecha_hora_ingreso`, `dni_registro`, `id_categoria`, `id_estado_material`) VALUES
(1, '0000-00-00 00:00:00', '', 1, 0),
(3, '0000-00-00 00:00:00', '', 1, 0),
(4, '0000-00-00 00:00:00', '', 2, 0),
(5, '0000-00-00 00:00:00', '', 2, 0),
(6, '2026-08-09 12:48:35', '48322983', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_dibujo_tecnico`
--

CREATE TABLE `materiales_dibujo_tecnico` (
  `id_material` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_didacticos`
--

CREATE TABLE `materiales_didacticos` (
  `id_material` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `tematica` varchar(150) DEFAULT NULL,
  `material_fabricacion` varchar(100) DEFAULT NULL,
  `edad_recomendada_min` tinyint(3) UNSIGNED DEFAULT NULL,
  `edad_recomendada_max` tinyint(3) UNSIGNED DEFAULT NULL,
  `cantidad_piezas` int(10) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_geograficos`
--

CREATE TABLE `materiales_geograficos` (
  `id_material` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `region` varchar(150) DEFAULT NULL,
  `escala` varchar(50) DEFAULT NULL,
  `anio` year(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `fecha_hora_registro` datetime NOT NULL,
  `fecha_hora_devolucion` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Listado completo de los prestamos realizados';

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_material`, `fecha_hora_registro`, `fecha_hora_devolucion`) VALUES
(1, 4, '2026-08-06 19:47:29', NULL),
(2, 4, '2026-08-06 19:48:24', NULL),
(3, 4, '2026-08-06 19:49:11', NULL),
(4, 5, '2026-08-06 19:49:22', NULL),
(5, 4, '2026-08-06 19:51:57', NULL),
(6, 5, '2026-08-10 12:26:39', NULL),
(7, 4, '2026-08-10 09:37:12', NULL),
(8, 3, '2026-08-10 09:41:52', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id_material` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `autor` varchar(200) DEFAULT NULL,
  `editorial` varchar(150) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `anio_publicacion` year(4) DEFAULT NULL,
  `numero_edicion` varchar(50) DEFAULT NULL,
  `cantidad_paginas` int(10) UNSIGNED DEFAULT NULL,
  `genero` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `desc_rol` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `desc_rol`) VALUES
(1, 'Admin'),
(2, 'Preceptor'),
(3, 'Profesor'),
(4, 'Alumno'),
(5, 'Bibliotecario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `dni_usuario` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `gmail` varchar(255) NOT NULL,
  `contra` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`dni_usuario`, `nombre`, `apellido`, `gmail`, `contra`, `id_rol`) VALUES
(48544233, 'Sara', 'Bermudez', 'Sara@gmail.com', 'sara123', 5),
(48322983, 'Ruben', 'Acuña', 'admin@biblioteca.gob', 'biblioteca2026', 1),
(3, 'Lucia ', 'Barros', 'Luciabarros@gmail.com', 'lucia123', 4),
(28344988, 'Marianela', 'Labat', 'Daiannalabat@gmail.com', 'labat1234', 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uq_categorias_nombre` (`nombre`);

--
-- Indices de la tabla `estado_material`
--
ALTER TABLE `estado_material`
  ADD PRIMARY KEY (`id_estado_material`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`id_genero`);

--
-- Indices de la tabla `historial_acciones`
--
ALTER TABLE `historial_acciones`
  ADD PRIMARY KEY (`id_accion`),
  ADD KEY `idx_tipo_accion` (`tipo_accion`),
  ADD KEY `idx_tabla_afectada` (`tabla_afectada`),
  ADD KEY `idx_id_registro` (`id_registro`),
  ADD KEY `idx_dni_usuario` (`dni_usuario`),
  ADD KEY `idx_fecha_hora` (`fecha_hora`);

--
-- Indices de la tabla `historial_observaciones`
--
ALTER TABLE `historial_observaciones`
  ADD PRIMARY KEY (`id_observacion`),
  ADD KEY `idx_obs_prestamo` (`id_prestamo`),
  ADD KEY `idx_obs_usuario` (`dni_usuario`);

--
-- Indices de la tabla `instrumentos_medicion`
--
ALTER TABLE `instrumentos_medicion`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `idx_instrumentos_tipo` (`tipo`),
  ADD KEY `idx_instrumentos_longitud` (`longitud_cm`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_material`);

--
-- Indices de la tabla `materiales_dibujo_tecnico`
--
ALTER TABLE `materiales_dibujo_tecnico`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `idx_dibujo_tecnico_tipo` (`tipo`),
  ADD KEY `idx_dibujo_tecnico_marca` (`marca`),
  ADD KEY `idx_dibujo_tecnico_modelo` (`modelo`);

--
-- Indices de la tabla `materiales_didacticos`
--
ALTER TABLE `materiales_didacticos`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `idx_didacticos_tipo` (`tipo`),
  ADD KEY `idx_didacticos_tematica` (`tematica`);

--
-- Indices de la tabla `materiales_geograficos`
--
ALTER TABLE `materiales_geograficos`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `idx_geograficos_tipo` (`tipo`),
  ADD KEY `idx_geograficos_region` (`region`),
  ADD KEY `idx_geograficos_escala` (`escala`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `idx_publicaciones_autor` (`autor`),
  ADD KEY `idx_publicaciones_genero` (`genero`),
  ADD KEY `idx_publicaciones_editorial` (`editorial`),
  ADD KEY `idx_publicaciones_isbn` (`isbn`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`dni_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estado_material`
--
ALTER TABLE `estado_material`
  MODIFY `id_estado_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `id_genero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historial_acciones`
--
ALTER TABLE `historial_acciones`
  MODIFY `id_accion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_observaciones`
--
ALTER TABLE `historial_observaciones`
  MODIFY `id_observacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
