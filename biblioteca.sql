-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-08-2026 a las 23:02:46
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

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

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `desc_categoria` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Categoria de materiales en la biblioteca';

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

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uq_categorias_nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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

DROP TABLE IF EXISTS `estado_material`;
CREATE TABLE IF NOT EXISTS `estado_material` (
  `id_estado_material` int NOT NULL AUTO_INCREMENT,
  `desc_estado_material` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_estado_material`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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

DROP TABLE IF EXISTS `genero`;
CREATE TABLE IF NOT EXISTS `genero` (
  `id_genero` int NOT NULL AUTO_INCREMENT,
  `desc_genero` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_genero`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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
-- Estructura de tabla para la tabla `instrumentos_medicion`
--

DROP TABLE IF EXISTS `instrumentos_medicion`;
CREATE TABLE IF NOT EXISTS `instrumentos_medicion` (
  `id_material` int UNSIGNED NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `material_fabricacion` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `longitud_cm` decimal(8,2) DEFAULT NULL,
  `ancho_cm` decimal(8,2) DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_instrumentos_tipo` (`tipo`),
  KEY `idx_instrumentos_longitud` (`longitud_cm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id_material` int NOT NULL AUTO_INCREMENT,
  `fecha_hora_ingreso` datetime NOT NULL,
  `dni_registro` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_categoria` int NOT NULL,
  `id_estado_material` int NOT NULL,
  PRIMARY KEY (`id_material`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Listado completo de los materiales que hay en la biblioteca';

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

DROP TABLE IF EXISTS `materiales_dibujo_tecnico`;
CREATE TABLE IF NOT EXISTS `materiales_dibujo_tecnico` (
  `id_material` int UNSIGNED NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_dibujo_tecnico_tipo` (`tipo`),
  KEY `idx_dibujo_tecnico_marca` (`marca`),
  KEY `idx_dibujo_tecnico_modelo` (`modelo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_didacticos`
--

DROP TABLE IF EXISTS `materiales_didacticos`;
CREATE TABLE IF NOT EXISTS `materiales_didacticos` (
  `id_material` int UNSIGNED NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `tematica` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `material_fabricacion` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `edad_recomendada_min` tinyint UNSIGNED DEFAULT NULL,
  `edad_recomendada_max` tinyint UNSIGNED DEFAULT NULL,
  `cantidad_piezas` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_didacticos_tipo` (`tipo`),
  KEY `idx_didacticos_tematica` (`tematica`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_geograficos`
--

DROP TABLE IF EXISTS `materiales_geograficos`;
CREATE TABLE IF NOT EXISTS `materiales_geograficos` (
  `id_material` int UNSIGNED NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `region` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `escala` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `anio` year DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_geograficos_tipo` (`tipo`),
  KEY `idx_geograficos_region` (`region`),
  KEY `idx_geograficos_escala` (`escala`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

DROP TABLE IF EXISTS `prestamos`;
CREATE TABLE IF NOT EXISTS `prestamos` (
  `id_prestamo` int NOT NULL AUTO_INCREMENT,
  `id_material` int NOT NULL,
  `fecha_hora_registro` datetime NOT NULL,
  `fecha_hora_devolucion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_prestamo`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Listado completo de los prestamos realizados';

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

DROP TABLE IF EXISTS `publicaciones`;
CREATE TABLE IF NOT EXISTS `publicaciones` (
  `id_material` int UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `autor` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `editorial` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `isbn` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `anio_publicacion` year DEFAULT NULL,
  `numero_edicion` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `cantidad_paginas` int UNSIGNED DEFAULT NULL,
  `genero` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_publicaciones_autor` (`autor`),
  KEY `idx_publicaciones_genero` (`genero`),
  KEY `idx_publicaciones_editorial` (`editorial`),
  KEY `idx_publicaciones_isbn` (`isbn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `desc_rol` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `dni_usuario` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `gmail` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `contra` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_rol` int NOT NULL,
  PRIMARY KEY (`dni_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`dni_usuario`, `nombre`, `apellido`, `gmail`, `contra`, `id_rol`) VALUES
(48544233, 'Sara', 'Bermudez', 'Sara@gmail.com', 'sara123', 5),
(48322983, 'Ruben', 'Acuña', 'admin@biblioteca.gob', 'biblioteca2026', 1),
(3, 'Lucia ', 'Barros', 'Luciabarros@gmail.com', 'lucia123', 4),
(28344988, 'Marianela', 'Labat', 'Daiannalabat@gmail.com', 'labat1234', 3);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
