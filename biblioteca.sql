-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 03-11-2026 a las 20:26:42
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
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `prefijo` varchar(10) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'MAT',
  `desde_num` int NOT NULL DEFAULT '1',
  `hasta_num` int NOT NULL DEFAULT '9999',
  `actual_num` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uq_categorias_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `activo`, `prefijo`, `desde_num`, `hasta_num`, `actual_num`) VALUES
(1, 'Libros', 'Libros y publicaciones', 1, 'LIB', 1, 9999, 61),
(2, 'Revistas', 'Revistas y publicaciones periódicas', 0, 'REV', 1, 9999, 0),
(3, 'Mapas', 'Mapas, planos y material geográfico', 1, 'MAP', 1, 9999, 14),
(4, 'Reglas', 'Reglas, escuadras y elementos de medición', 1, 'REG', 1, 9999, 93),
(5, 'Tableros', 'Tableros de dibujo técnico', 1, 'TAB', 1, 9999, 0),
(6, 'Cajas', 'Cajas y elementos de almacenamiento', 1, 'CAJ', 1, 9999, 0),
(7, 'Juegos Didácticos', 'Juegos de mesa y Juegos educativos', 1, 'DID', 1, 9999, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_material`
--

DROP TABLE IF EXISTS `estado_material`;
CREATE TABLE IF NOT EXISTS `estado_material` (
  `id_estado_material` int NOT NULL AUTO_INCREMENT,
  `desc_estado_material` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_estado_material`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `estado_material`
--

INSERT INTO `estado_material` (`id_estado_material`, `desc_estado_material`) VALUES
(1, 'buen estado'),
(2, 'roto'),
(3, 'Nuevo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_prestamo`
--

DROP TABLE IF EXISTS `estado_prestamo`;
CREATE TABLE IF NOT EXISTS `estado_prestamo` (
  `id_estado_prestamo` int NOT NULL AUTO_INCREMENT,
  `desc_estado_prestamo` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_estado_prestamo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `estado_prestamo`
--

INSERT INTO `estado_prestamo` (`id_estado_prestamo`, `desc_estado_prestamo`) VALUES
(1, 'Devuelto'),
(2, 'No devuelto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instrumentos_medicion`
--

DROP TABLE IF EXISTS `instrumentos_medicion`;
CREATE TABLE IF NOT EXISTS `instrumentos_medicion` (
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL COMMENT 'regla de 90, regla de 45, compas, etc',
  `longitud_cm` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `instrumentos_medicion`
--

INSERT INTO `instrumentos_medicion` (`id_material`, `tipo`, `longitud_cm`) VALUES
('REG0001', 'regla 45°', NULL),
('REG0002', 'regla 45°', NULL),
('REG0003', 'regla 45°', NULL),
('REG0004', 'regla 45°', NULL),
('REG0005', 'regla 45°', NULL),
('REG0006', 'regla 45°', NULL),
('REG0007', 'regla 45°', NULL),
('REG0008', 'regla 45°', NULL),
('REG0009', 'regla 45°', NULL),
('REG0010', 'regla 45°', NULL),
('REG0011', 'regla 45°', NULL),
('REG0012', 'regla 45°', NULL),
('REG0013', 'regla 45°', NULL),
('REG0014', 'regla 45°', NULL),
('REG0015', 'regla 45°', NULL),
('REG0016', 'regla 45°', NULL),
('REG0017', 'regla 45°', NULL),
('REG0018', 'regla 45°', NULL),
('REG0019', 'regla 45°', NULL),
('REG0020', 'regla 45°', NULL),
('REG0021', 'regla 45°', NULL),
('REG0022', 'regla 45°', NULL),
('REG0023', 'regla 45°', NULL),
('REG0024', 'regla 45°', NULL),
('REG0025', 'regla 45°', NULL),
('REG0026', 'regla 45°', NULL),
('REG0027', 'regla 45°', NULL),
('REG0028', 'regla 45°', NULL),
('REG0029', 'regla 45°', NULL),
('REG0030', 'regla 45°', NULL),
('REG0031', 'regla 45°', NULL),
('REG0032', 'regla 45°', NULL),
('REG0033', 'regla 45°', NULL),
('REG0034', 'regla 45°', NULL),
('REG0035', 'regla 45°', NULL),
('REG0036', 'regla 45°', NULL),
('REG0037', 'regla 45°', NULL),
('REG0038', 'regla 60°', NULL),
('REG0039', 'regla 60°', NULL),
('REG0040', 'regla 60°', NULL),
('REG0041', 'regla 60°', NULL),
('REG0042', 'regla 60°', NULL),
('REG0043', 'regla 60°', NULL),
('REG0044', 'regla 60°', NULL),
('REG0045', 'regla 60°', NULL),
('REG0046', 'regla 60°', NULL),
('REG0047', 'regla 60°', NULL),
('REG0048', 'regla 60°', NULL),
('REG0049', 'regla 60°', NULL),
('REG0050', 'regla 60°', NULL),
('REG0051', 'regla 60°', NULL),
('REG0052', 'regla 60°', NULL),
('REG0053', 'regla 60°', NULL),
('REG0054', 'regla 60°', NULL),
('REG0055', 'regla 60°', NULL),
('REG0056', 'regla 60°', NULL),
('REG0057', 'regla 60°', NULL),
('REG0058', 'regla 60°', NULL),
('REG0059', 'regla 60°', NULL),
('REG0060', 'regla 60°', NULL),
('REG0061', 'regla 60°', NULL),
('REG0062', 'regla 60°', NULL),
('REG0063', 'regla 60°', NULL),
('REG0064', 'regla 60°', NULL),
('REG0065', 'regla 60°', NULL),
('REG0066', 'regla 60°', NULL),
('REG0067', 'regla 60°', NULL),
('REG0068', 'regla 60°', NULL),
('REG0069', 'regla 60°', NULL),
('REG0070', 'regla 60°', NULL),
('REG0071', 'regla 60°', NULL),
('REG0072', 'regla 60°', NULL),
('REG0073', 'regla 60°', NULL),
('REG0074', 'regla 60°', NULL),
('REG0075', 'regla 60°', NULL),
('REG0076', 'regla 60°', NULL),
('REG0077', 'regla 60°', NULL),
('REG0078', 'regla 60°', NULL),
('REG0079', 'regla 60°', NULL),
('REG0080', 'regla 60°', NULL),
('REG0081', 'regla 60°', NULL),
('REG0082', 'regla 60°', NULL),
('REG0083', 'regla 60°', NULL),
('REG0084', 'regla 60°', NULL),
('REG0085', 'regla circulos', NULL),
('REG0086', 'regla 380°', NULL),
('REG0087', 'regla con onda', NULL),
('REG0088', 'regla 60°', NULL),
('REG0089', 'regla curbigrafo', NULL),
('REG0090', 'regla curbigrafo', NULL),
('REG0091', 'regla curbigrafo', NULL),
('REG0092', 'regla curbigrafo', NULL),
('REG0093', 'escuadra', 20.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

DROP TABLE IF EXISTS `inventario`;
CREATE TABLE IF NOT EXISTS `inventario` (
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_viejo` int DEFAULT NULL,
  `fecha_hora_ingreso` datetime NOT NULL,
  `dni_registro` int NOT NULL COMMENT 'DNI del bibliotecario que ingresó el material',
  `id_categoria` int NOT NULL,
  `qr_material` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_estado_material` int NOT NULL,
  PRIMARY KEY (`id_material`),
  UNIQUE KEY `id_material` (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Tabla maestro de los materiales que hay en la biblioteca';

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_material`, `id_viejo`, `fecha_hora_ingreso`, `dni_registro`, `id_categoria`, `qr_material`, `id_estado_material`) VALUES
('CAJ0001', 114, '2026-10-15 16:00:00', 48322983, 6, NULL, 1),
('CAJ0002', 116, '2026-10-15 16:00:00', 48322983, 6, NULL, 1),
('CAJ0003', 115, '2026-10-15 16:00:00', 48322983, 6, NULL, 1),
('CAJ0004', 113, '2026-10-15 16:00:00', 48322983, 6, NULL, 1),
('LIB0001', 0, '2026-08-19 18:38:25', 48322983, 1, '', 2),
('LIB0002', 0, '2026-09-01 21:48:57', 48322983, 1, '', 1),
('LIB0003', 0, '2026-09-01 21:49:05', 48322983, 1, '', 3),
('LIB0004', 0, '2026-09-01 21:49:09', 48322983, 1, '', 1),
('LIB0005', 0, '2026-08-09 12:48:35', 48322983, 1, '', 1),
('LIB0006', 0, '2026-09-02 14:23:25', 48322983, 1, '', 1),
('LIB0007', 0, '2026-09-02 22:50:49', 48322983, 1, '', 3),
('LIB0008', 0, '2026-09-02 22:52:48', 48322983, 1, '', 3),
('LIB0009', 0, '2026-09-02 23:16:12', 48322983, 1, '', 3),
('LIB0010', 0, '2026-09-03 11:12:03', 48322983, 1, '', 1),
('LIB0011', 0, '2026-09-03 16:33:23', 48322983, 1, '', 3),
('LIB0012', 0, '2026-09-07 09:10:15', 48322983, 1, '', 3),
('LIB0013', 0, '2026-09-07 09:50:24', 48322983, 1, '', 1),
('LIB0014', NULL, '2026-10-15 11:09:53', 48322983, 1, NULL, 1),
('LIB0015', NULL, '2026-10-15 11:24:38', 48322983, 1, NULL, 1),
('LIB0016', 2, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0017', 200, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0018', 201, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0019', 202, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0020', 204, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0021', 205, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0022', 206, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0023', 207, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0024', 208, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0025', 209, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0026', 210, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0027', 211, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0028', 212, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0029', 213, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0030', 214, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0031', 215, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0032', 216, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0033', 217, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0034', 218, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0035', 219, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0036', 220, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0037', 221, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0038', 222, '2026-10-15 16:09:56', 48322983, 1, '', 1),
('LIB0040', 241, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0041', 242, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0042', 243, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0043', 244, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0044', 245, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0045', 247, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0046', 248, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0047', 249, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0048', 250, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0049', 251, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0050', 252, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0051', 253, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0052', 254, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0053', 255, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0054', 256, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0055', 258, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0056', 259, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0057', 260, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0058', 261, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0059', 262, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0060', 263, '2026-10-15 16:14:15', 48322983, 1, '', 1),
('LIB0061', NULL, '2026-10-15 16:43:06', 48322983, 1, NULL, 1),
('MAP0001', 0, '2026-09-02 22:55:43', 48322983, 3, '', 3),
('MAP0002', 0, '2026-09-03 15:04:37', 48322983, 3, '', 1),
('MAP0003', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0004', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0005', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0006', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0007', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0008', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0009', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0010', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0011', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0012', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0013', 0, '2026-10-15 15:19:22', 48322983, 3, '', 1),
('MAP0014', NULL, '2026-10-30 10:22:59', 48322983, 3, NULL, 1),
('REG0001', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 2),
('REG0002', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 2),
('REG0003', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 2),
('REG0004', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0005', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0006', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0007', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0008', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0009', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0010', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0011', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0012', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0013', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0014', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0015', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0016', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0017', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0018', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0019', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0020', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0021', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0022', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0023', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0024', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0025', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0026', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0027', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0028', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0029', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0030', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0031', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0032', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0033', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0034', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0035', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0036', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0037', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0038', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0039', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0040', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0041', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0042', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0043', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0044', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0045', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0046', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0047', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0048', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0049', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0050', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0051', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0052', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0053', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0054', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0055', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0056', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0057', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0058', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0059', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0060', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0061', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0062', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0063', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0064', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0065', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0066', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0067', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0068', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0069', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0070', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0071', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0072', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0073', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0074', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0075', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0076', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0077', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0078', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0079', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0080', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0081', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0082', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0083', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0084', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0085', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0086', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0087', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0088', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0089', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0090', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0091', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0092', NULL, '2026-10-15 15:48:02', 48322983, 4, NULL, 1),
('REG0093', NULL, '2026-10-30 10:10:12', 48322983, 4, NULL, 3),
('TAB0001', 33, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0002', 119, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0003', 115, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0004', 124, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0005', 121, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0006', 123, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0007', 32, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0008', 116, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0009', 79, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0010', 48, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0011', 80, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0012', 63, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0013', 75, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0014', 52, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0015', 76, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0016', 65, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0017', 43, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0018', 53, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0019', 71, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0020', 49, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0021', 70, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0022', 77, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0023', 50, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0024', 64, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0025', 78, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0026', 67, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0027', 68, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0028', 66, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0029', 55, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0030', 44, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0031', 45, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0032', 46, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0033', 47, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0034', 41, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0035', 42, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0036', 39, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0037', 40, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0038', 37, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0039', 38, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0040', 7, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0041', 11, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0042', 1, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0043', 2, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0044', 10, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0045', 8, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0046', 6, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0047', 3, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0048', 4, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0049', 5, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0050', 9, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0051', 74, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0052', 73, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0053', 72, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0054', 118, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0055', 117, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0056', 51, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0057', 54, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0058', 56, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0059', 57, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0060', 58, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0061', 59, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0062', 60, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0063', 61, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0064', 62, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0065', 104, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0066', 27, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0067', 25, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0068', 18, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0069', 29, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0070', 15, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0071', 105, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0072', 23, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0073', 24, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0074', 12, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0075', 106, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0076', 22, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0077', 31, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0078', 17, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0079', 20, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0080', 108, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0081', 13, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0082', 103, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0083', 16, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0084', 19, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0085', 26, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0086', 21, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0087', 28, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0088', 107, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0089', 102, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0090', 30, '2026-10-15 16:00:00', 48322983, 5, NULL, 3),
('TAB0091', 14, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0092', 101, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0093', 122, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0094', 114, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0095', 120, '2026-10-15 16:00:00', 48322983, 5, NULL, 2),
('TAB0096', 35, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0097', 36, '2026-10-15 16:00:00', 48322983, 5, NULL, 1),
('TAB0098', 113, '2026-10-15 16:00:00', 48322983, 5, NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

DROP TABLE IF EXISTS `libros`;
CREATE TABLE IF NOT EXISTS `libros` (
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `autor` varchar(200) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `id_estado` int NOT NULL,
  `genero` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_publicaciones_autor` (`autor`),
  KEY `id_estado` (`id_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id_material`, `nombre`, `autor`, `id_estado`, `genero`) VALUES
('0', 'Poesia Clásica Japonesa', 'Torquil de Oriente', 1, 'Poesía'),
('LIB0001', 'Teatro x la identidad', 'Juan Daniel Batllosera', 1, 'Política educativa'),
('LIB0002', 'Teatro x la identidad', 'Juan Daniel Batllosera', 1, 'Política educativa'),
('LIB0003', 'Teatro x la identidad', 'Juan Daniel Batllosera', 1, 'Política educativa'),
('LIB0004', 'Teatro x la identidad', 'Juan Daniel Batllosera', 1, 'Política educativa'),
('LIB0005', 'Teatro x la identidad', 'Juan Daniel Batllosera', 1, 'Política educativa'),
('LIB0006', 'Teatro x la identidad', 'Juan Daniel Batllosera', 1, 'Política educativa'),
('LIB0007', 'poesia argentina de siglo xx', 'nuestra cultura', 3, 'Poesía'),
('LIB0008', 'Poesia Clásica Japonesa', 'Torquil de Oriente', 3, 'Poesía'),
('LIB0009', 'lengua y herida', 'antonio gamoneda', 3, 'antalogia'),
('LIB0010', 'kavafis - seferis - ritsos - elytis - vretakos -varvitsiotis', 'seis poetas griegos', 1, 'antalogia'),
('LIB0011', 'george', 'nnn', 3, 'antalogia'),
('LIB0012', 'largo dia de colera', 'leopoldo marechal', 3, 'antalogia'),
('LIB0013', 'marimba', 'jorge boccanera', 1, 'antalogia'),
('LIB0014', 'asia I', 'billiken', 1, 'atlas'),
('LIB0015', 'asia II', 'billiken', 1, 'atlas'),
('LIB0016', 'lengua y herida', 'antonio gamoneda', 1, 'antologia'),
('LIB0017', 'grandes construcciones', 'billiken', 1, 'atlas'),
('LIB0018', 'grandes construcciones', 'billiken', 1, 'atlas'),
('LIB0019', 'asia I', 'billiken', 1, 'atlas'),
('LIB0020', 'asia II', 'billiken', 1, 'atlas'),
('LIB0021', 'la independencia I', 'billiken', 1, 'atlas'),
('LIB0022', 'la independencia II', 'billiken', 1, 'atlas'),
('LIB0023', 'mayo de 1810 (I)', 'billiken', 1, 'atlas'),
('LIB0024', 'mayo de 1810 (II)', 'billiken', 1, 'atlas'),
('LIB0025', 'dioses y leyendas VIII', 'billiken', 1, 'atlas'),
('LIB0026', 'dioses y leyendas IV', 'billiken', 1, 'atlas'),
('LIB0027', 'dioses y leyendas V', 'billiken', 1, 'atlas'),
('LIB0028', 'dioses y leyendas III', 'billiken', 1, 'atlas'),
('LIB0029', 'dioses y leyendas VI', 'billiken', 1, 'atlas'),
('LIB0030', 'venecia', 'billiken', 1, 'atlas'),
('LIB0031', 'nueva york', 'billiken', 1, 'atlas'),
('LIB0032', 'estambul', 'billiken', 1, 'atlas'),
('LIB0033', 'san petersburgo', 'billiken', 1, 'atlas'),
('LIB0034', 'pekin', 'billiken', 1, 'atlas'),
('LIB0035', 'san martin', 'pekin', 1, 'atlas'),
('LIB0036', 'jerusalen', 'billiken', 1, 'atlas'),
('LIB0037', 'domingo F. sarmiento', 'billiken', 1, 'atlas'),
('LIB0038', 'lo medios', 'billiken', 1, 'atlas'),
('LIB0039', 'paris', 'billiken', 1, 'atlas'),
('LIB0040', 'El mundo de la magia', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0041', 'Extraños Mundos', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0042', 'A gusto del consumidor', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0043', 'Sobre las Tablas', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0044', '¿Quién más, quién menos?', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0045', 'Cifras a medida', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0046', 'Un mundo perdido', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0047', 'Letras sobre rieles', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0048', 'Uno mas, uno menos', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0049', 'Un mundo para todos', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0050', '¿Hay un lugar para los números?', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0051', 'Grandes Viajeros', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0052', 'Un mundo inquieto', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0053', 'Múltiples Problemas', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0054', 'Vamos por más', 'serie piedra libre', 1, 'Serie piedra libre'),
('LIB0055', 'Ciencia y Ficcion 3', 'Ministerio de educacion', 1, 'Ciencia Ficcion'),
('LIB0056', 'Ciencia y Ficcion 4', 'Ministerio de educacion', 1, 'Ciencia Ficcion'),
('LIB0057', 'Ciencia y Ficcion 5', 'Ministerio de educacion', 1, 'Ciencia Ficcion'),
('LIB0058', 'Ciencia y Ficcion 6', 'Ministerio de educacion', 1, 'Ciencia Ficcion'),
('LIB0059', 'Ciencia y Ficcion 1', 'Ministerio de educacion', 1, 'Ciencia y Ficcion'),
('LIB0060', 'Ciencia y Ficcion 2', 'Ministerio de educacion', 1, 'Ciencia y Ficcion'),
('LIB0061', 'Londres', 'billiken', 1, 'atlas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_dibujo_tecnico`
--

DROP TABLE IF EXISTS `materiales_dibujo_tecnico`;
CREATE TABLE IF NOT EXISTS `materiales_dibujo_tecnico` (
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL COMMENT 'tablero, soporte de tablero, cinta de papel',
  `descripcion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Tabla detalle de los materiales de Dibujo técnico';

--
-- Volcado de datos para la tabla `materiales_dibujo_tecnico`
--

INSERT INTO `materiales_dibujo_tecnico` (`id_material`, `tipo`, `descripcion`) VALUES
('CAJ0001', 'soporte de tablero', 'ocupada por un tablero'),
('CAJ0002', 'soporte de tablero', 'ocupada por un tablero'),
('CAJ0003', 'soporte de tablero', 'ocupada por un tablero'),
('CAJ0004', 'soporte de tablero', 'ocupada por un tablero'),
('TAB0001', 'tablero', NULL),
('TAB0002', 'tablero', 'Sin hilo guia ni regla'),
('TAB0003', 'tablero', 'Sin hilo guia ni regla, caja en mal estado'),
('TAB0004', 'tablero', 'falta el mango a la caja'),
('TAB0005', 'tablero', 'Sin hilo guia ni regla'),
('TAB0006', 'tablero', 'Sin hilo guia ni regla'),
('TAB0007', 'tablero', NULL),
('TAB0008', 'tablero', 'Sin hilo guia ni regla'),
('TAB0009', 'tablero', 'caja rota'),
('TAB0010', 'tablero', NULL),
('TAB0011', 'tablero', 'manija rota'),
('TAB0012', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0013', 'tablero', 'sin manija'),
('TAB0014', 'tablero', NULL),
('TAB0015', 'tablero', NULL),
('TAB0016', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0017', 'tablero', NULL),
('TAB0018', 'tablero', NULL),
('TAB0019', 'tablero', NULL),
('TAB0020', 'tablero', NULL),
('TAB0021', 'tablero', 'sin manija'),
('TAB0022', 'tablero', 'sin hilo, manija rota'),
('TAB0023', 'tablero', NULL),
('TAB0024', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0025', 'tablero', NULL),
('TAB0026', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0027', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0028', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0029', 'tablero', NULL),
('TAB0030', 'tablero', 'no tiene manija'),
('TAB0031', 'tablero', NULL),
('TAB0032', 'tablero', NULL),
('TAB0033', 'tablero', NULL),
('TAB0034', 'tablero', NULL),
('TAB0035', 'tablero', 'sin hilo'),
('TAB0036', 'tablero', NULL),
('TAB0037', 'tablero', NULL),
('TAB0038', 'tablero', NULL),
('TAB0039', 'tablero', NULL),
('TAB0040', 'tablero', NULL),
('TAB0041', 'tablero', NULL),
('TAB0042', 'tablero', NULL),
('TAB0043', 'tablero', NULL),
('TAB0044', 'tablero', NULL),
('TAB0045', 'tablero', NULL),
('TAB0046', 'tablero', NULL),
('TAB0047', 'tablero', NULL),
('TAB0048', 'tablero', 'Regla suelta, hilos sueltos y se salio la gomita'),
('TAB0049', 'tablero', 'Hilos sueltos'),
('TAB0050', 'tablero', NULL),
('TAB0051', 'tablero', NULL),
('TAB0052', 'tablero', NULL),
('TAB0053', 'tablero', 'sin manija'),
('TAB0054', 'tablero', 'no tiene hilo guia ni regla'),
('TAB0055', 'tablero', 'Hilo guia suelto'),
('TAB0056', 'tablero', NULL),
('TAB0057', 'tablero', NULL),
('TAB0058', 'tablero', NULL),
('TAB0059', 'tablero', NULL),
('TAB0060', 'tablero', NULL),
('TAB0061', 'tablero', 'hilo roto, manija rota'),
('TAB0062', 'tablero', 'hilo roto'),
('TAB0063', 'tablero', NULL),
('TAB0064', 'tablero', 'sin caja, sin regla, sin hilo'),
('TAB0065', 'tablero', 'No tienen hilo guia ni regla'),
('TAB0066', 'tablero', NULL),
('TAB0067', 'tablero', NULL),
('TAB0068', 'tablero', NULL),
('TAB0069', 'tablero', NULL),
('TAB0070', 'tablero', NULL),
('TAB0071', 'tablero', 'No tienen hilo guia ni regla, la caja no tiene mango'),
('TAB0072', 'tablero', NULL),
('TAB0073', 'tablero', NULL),
('TAB0074', 'tablero', NULL),
('TAB0075', 'tablero', 'No tienen hilo guia ni regla'),
('TAB0076', 'tablero', NULL),
('TAB0077', 'tablero', NULL),
('TAB0078', 'tablero', NULL),
('TAB0079', 'tablero', NULL),
('TAB0080', 'tablero', 'No tienen hilo guia ni regla'),
('TAB0081', 'tablero', 'falta hilo'),
('TAB0082', 'tablero', 'No tienen hilo guia ni regla, no tiene mango'),
('TAB0083', 'tablero', NULL),
('TAB0084', 'tablero', ''),
('TAB0085', 'tablero', NULL),
('TAB0086', 'tablero', NULL),
('TAB0087', 'tablero', NULL),
('TAB0088', 'tablero', 'No tienen hilo guia ni regla'),
('TAB0089', 'tablero', 'No tienen hilo guia ni regla'),
('TAB0090', 'tablero', NULL),
('TAB0091', 'tablero', NULL),
('TAB0092', 'tablero', 'No tienen hilo guia ni regla'),
('TAB0093', 'tablero', 'Sin hilo guia ni regla'),
('TAB0094', 'tablero', 'Sin hilo guia ni regla'),
('TAB0095', 'tablero', 'Sin hilo guia ni regla'),
('TAB0096', 'tablero', NULL),
('TAB0097', 'tablero', NULL),
('TAB0098', 'tablero', 'Sin hilo guia ni regla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_didacticos`
--

DROP TABLE IF EXISTS `materiales_didacticos`;
CREATE TABLE IF NOT EXISTS `materiales_didacticos` (
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL COMMENT 'juego de mesa, revista, rompecabezas, etc',
  `tematica` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `piezas` int DEFAULT NULL COMMENT 'cantidad de piezas',
  PRIMARY KEY (`id_material`),
  KEY `idx_didacticos_tematica` (`tematica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Tabla de detalle de materiales didacticos';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_geograficos`
--

DROP TABLE IF EXISTS `materiales_geograficos`;
CREATE TABLE IF NOT EXISTS `materiales_geograficos` (
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL COMMENT 'tipo material, mapa, globo terráqueo',
  `region` varchar(150) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `escala` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `anio` year DEFAULT NULL,
  PRIMARY KEY (`id_material`),
  KEY `idx_geograficos_tipo` (`tipo`),
  KEY `idx_geograficos_region` (`region`),
  KEY `idx_geograficos_escala` (`escala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Tabla de detalle de materiales geográficos';

--
-- Volcado de datos para la tabla `materiales_geograficos`
--

INSERT INTO `materiales_geograficos` (`id_material`, `tipo`, `region`, `escala`, `anio`) VALUES
('MAP0001', 'mapa', 'Misiones', '1:50', '2002'),
('MAP0002', 'Mapa planisferio de Argentina para pizarra', 'Argentina', '1:50.000', '2000'),
('MAP0003', 'mapa físico-político', 'Europa', '1:5.450.000', NULL),
('MAP0004', 'mapa físico-político', 'Argentina', '1:6.300.000', NULL),
('MAP0005', 'mapa físico-político', 'República Argentina', '1:6.300.000', NULL),
('MAP0006', 'Mapa físico-político', 'América', '1:13.000.000', NULL),
('MAP0007', 'Mapa físico-político', 'América', '1:13.000.000', NULL),
('MAP0008', 'Mapa físico-político', 'Oceanía', '1:31.525.200', NULL),
('MAP0009', 'Mapa físico-político', 'Africa', '1:8.463.000', NULL),
('MAP0010', 'Mapa planisferio físico-político', 'Mercator', '1:32.700.000', NULL),
('MAP0011', 'Mapa físico-político', 'Asia', '1:11.501.000', NULL),
('MAP0012', 'Mapa planisferio físico-político', 'Mercator', '1:32.700.000', NULL),
('MAP0013', 'Mapa físico-político', 'República Argentina', '1:3.175.000', NULL),
('MAP0014', 'mapa', 'planisferio', '1:50.000', '0000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

DROP TABLE IF EXISTS `prestamos`;
CREATE TABLE IF NOT EXISTS `prestamos` (
  `id_prestamo` int NOT NULL AUTO_INCREMENT,
  `id_material` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `dni_usuario` int NOT NULL COMMENT 'DNI del usuario que lleva prestado un material',
  `dni_bibliotecario` int NOT NULL COMMENT 'DNI del bibliotecario que registró el prestamo',
  `fecha_hora_registro` datetime NOT NULL,
  `fecha_hora_devolucion` datetime DEFAULT NULL,
  `fecha_hora_limite_prestamo` datetime NOT NULL,
  `id_estado_prestamo` int NOT NULL COMMENT 'devuelto, retrasado, no devuelto, robado',
  PRIMARY KEY (`id_prestamo`),
  KEY `dni_bibliotecario` (`dni_bibliotecario`),
  KEY `dni_usuario` (`dni_usuario`),
  KEY `id_material` (`id_material`),
  KEY `id_estado_prestamo` (`id_estado_prestamo`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Prestamos';

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_material`, `dni_usuario`, `dni_bibliotecario`, `fecha_hora_registro`, `fecha_hora_devolucion`, `fecha_hora_limite_prestamo`, `id_estado_prestamo`) VALUES
(1, 'LIB0001', 48500239, 48322983, '2026-08-06 19:47:29', '2026-08-06 19:47:29', '2026-08-06 18:50:00', 1),
(2, 'LIB0002', 48500239, 48322983, '2026-08-06 19:48:24', '2026-08-06 19:48:24', '2026-08-06 18:50:00', 1),
(3, 'LIB0001', 48500239, 48322983, '2026-08-06 19:49:11', '2026-08-06 19:49:11', '2026-08-06 18:50:00', 1),
(4, 'LIB0002', 48500239, 48322983, '2026-08-06 19:49:22', '2026-08-06 19:49:22', '2026-08-06 18:50:00', 1),
(5, 'LIB0003', 48500239, 48322983, '2026-08-06 19:51:57', '2026-08-06 19:51:57', '2026-08-06 18:50:00', 1),
(6, 'LIB0004', 48500239, 48322983, '2026-08-10 12:26:39', '2026-08-10 12:26:39', '2026-08-10 18:50:00', 1),
(7, 'LIB0005', 48500239, 48322983, '2026-08-10 09:37:12', '2026-08-10 09:37:12', '2026-08-10 11:50:00', 1),
(8, 'LIB0006', 48500239, 48322983, '2026-09-02 09:41:52', '2026-09-02 09:41:52', '2026-09-02 11:50:00', 1),
(9, 'LIB0001', 48500239, 48322983, '2026-09-02 11:29:59', '2026-09-02 11:29:59', '2026-09-02 11:50:00', 1),
(10, 'LIB0001', 48500239, 48322983, '2026-09-02 13:32:15', '2026-09-02 13:32:15', '2026-09-02 18:50:00', 1),
(11, 'LIB0002', 48544233, 48322983, '2026-09-02 13:32:40', '2026-09-02 13:32:40', '2026-09-02 18:50:00', 1),
(12, 'LIB0002', 48500239, 48322983, '2026-09-02 16:40:41', '2026-09-02 16:40:41', '2026-09-02 18:50:00', 1),
(13, 'LIB0006', 48500239, 48322983, '2026-09-02 16:53:21', '2026-09-02 16:53:21', '2026-09-02 18:50:00', 1),
(14, 'LIB0009', 48500239, 48322983, '2026-09-03 15:22:40', '2026-09-03 15:22:40', '2026-09-03 18:50:00', 1),
(15, 'MAP0001', 48500239, 48322983, '2026-09-03 16:40:40', '2026-09-03 16:40:40', '2026-09-03 18:50:00', 1),
(16, 'MAP0002', 48544233, 48322983, '2026-10-15 15:13:22', '2026-10-15 15:13:22', '2026-10-15 18:50:00', 1),
(17, 'MAP0002', 48500239, 48322983, '2026-10-15 15:15:35', '2026-10-15 15:15:35', '2026-10-15 18:50:00', 1),
(18, 'LIB0015', 48500239, 48322983, '2026-10-15 15:16:59', '2026-10-15 15:16:59', '2026-10-15 18:50:00', 1),
(19, 'LIB0014', 48500239, 48322983, '2026-10-15 15:17:10', '2026-10-15 15:17:10', '2026-10-15 18:50:00', 1),
(20, 'MAP0001', 48500239, 48322983, '2026-10-15 17:05:11', '2026-10-15 17:05:11', '2026-10-15 18:50:00', 1),
(21, 'MAP0013', 48500239, 48322983, '2026-10-15 17:05:34', '2026-10-15 17:05:34', '2026-10-15 18:50:00', 1),
(22, 'REG0019', 48500239, 48322983, '2026-10-20 08:28:15', '2026-10-20 08:28:15', '2026-10-20 11:50:00', 1),
(23, 'LIB0001', 48500239, 48322983, '2026-10-21 11:04:52', '2026-10-21 11:12:39', '2026-10-21 11:50:00', 1),
(24, 'LIB0001', 48322983, 48322983, '2026-10-28 11:27:01', '2026-10-29 07:35:49', '2026-10-28 11:50:00', 1),
(25, 'tab0001', 48544233, 48322983, '2026-10-28 11:28:53', '2026-10-29 07:35:46', '2026-10-28 11:50:00', 1),
(26, 'reg0001', 48544233, 48322983, '2026-10-28 11:29:56', '2026-10-29 07:35:44', '2026-10-28 11:50:00', 1),
(27, 'reg0002', 48544233, 48322983, '2026-10-28 11:30:25', '2026-10-28 17:09:02', '2026-10-28 11:50:00', 1),
(28, 'reg0003', 48544233, 48322983, '2026-10-28 11:33:32', '2026-10-28 17:08:59', '2026-10-28 11:50:00', 1),
(29, 'TAB0095', 48544233, 48322983, '2026-10-28 17:22:17', '2026-10-28 17:23:20', '2026-10-28 18:50:00', 1),
(30, 'LIB0053', 48544233, 48322983, '2026-10-29 07:48:18', '2026-10-29 07:49:57', '2026-10-29 11:50:00', 1),
(31, 'LIB0052', 48544233, 48322983, '2026-10-29 07:48:33', '2026-10-29 07:49:54', '2026-10-29 11:50:00', 1),
(32, 'LIB0054', 48544233, 48322983, '2026-10-29 07:48:50', '2026-10-29 07:49:50', '2026-10-29 11:50:00', 1),
(33, 'LIB0058', 48544233, 48322983, '2026-10-29 08:23:04', '2026-10-29 08:48:17', '2026-10-29 11:50:00', 1),
(34, 'LIB0054', 48544233, 48322983, '2026-10-29 08:23:23', '2026-10-29 08:24:06', '2026-10-29 11:50:00', 1),
(35, 'LIB0053', 48544233, 48322983, '2026-10-29 08:23:42', '2026-10-29 08:23:51', '2026-10-29 11:50:00', 1),
(36, 'LIB0053', 48544233, 48322983, '2026-10-29 08:50:02', '2026-10-29 08:50:07', '2026-10-29 11:50:00', 1),
(37, 'LIB0053', 48544233, 48322983, '2026-10-29 09:15:58', '2026-10-29 09:16:33', '2026-10-29 11:50:00', 1),
(38, 'LIB0052', 48322983, 48322983, '2026-10-29 09:19:24', NULL, '2026-10-29 11:50:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `desc_rol` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='roles ';

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
  PRIMARY KEY (`dni_usuario`),
  KEY `id_rol` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci COMMENT='Tabla usuarios (simulación)';

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`dni_usuario`, `nombre`, `apellido`, `gmail`, `contra`, `id_rol`) VALUES
(28344988, 'Marianela', 'Labat', 'Daiannalabat@gmail.com', 'labat1234', 3),
(48322983, 'Ruben', 'Acuña', 'admin@biblioteca.gob', 'biblioteca2026', 1),
(48500239, 'Lucia ', 'Barros', 'Luciabarros@gmail.com', 'lucia123', 4),
(48544233, 'Sara', 'Bermudez', 'Sara@gmail.com', 'sara123', 5);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_inventario_completo`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vista_inventario_completo`;
CREATE TABLE IF NOT EXISTS `vista_inventario_completo` (
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_prestamos_detalle`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vista_prestamos_detalle`;
CREATE TABLE IF NOT EXISTS `vista_prestamos_detalle` (
`apellido_usuario` varchar(255)
,`atributo_detalle` varchar(200)
,`desc_estado_material` varchar(255)
,`dni_bibliotecario` int
,`dni_usuario` int
,`fecha_hora_devolucion` datetime
,`fecha_hora_registro` datetime
,`id_material` varchar(200)
,`id_prestamo` int
,`nombre_categoria` varchar(100)
,`nombre_material` varchar(255)
,`nombre_usuario` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_inventario_completo`
--
DROP TABLE IF EXISTS `vista_inventario_completo`;

DROP VIEW IF EXISTS `vista_inventario_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_inventario_completo`  AS SELECT `i`.`id_material` AS `id_material`, `c`.`nombre` AS `categoria`, coalesce(`l`.`nombre`,`md`.`tipo`,`mg`.`tipo`,`im`.`tipo`,`mdt`.`tipo`) AS `nombre_o_tipo`, coalesce(`l`.`autor`,`md`.`tematica`,`mg`.`region`,`im`.`material_fabricacion`,`mdt`.`marca`) AS `detalle_secundario`, `em`.`desc_estado_material` AS `estado_material`, `i`.`fecha_hora_ingreso` AS `fecha_hora_ingreso`, `l`.`autor` AS `libro_autor`, `l`.`genero` AS `libro_genero`, `md`.`tematica` AS `didactico_tematica`, `mg`.`region` AS `geografico_region`, `im`.`material_fabricacion` AS `medicion_material`, `mdt`.`marca` AS `dibujo_marca` FROM (((((((`inventario` `i` join `categorias` `c` on((`i`.`id_categoria` = `c`.`id_categoria`))) join `estado_material` `em` on((`i`.`id_estado_material` = `em`.`id_estado_material`))) left join `libros` `l` on((`i`.`id_material` = `l`.`id_material`))) left join `materiales_didacticos` `md` on((`i`.`id_material` = `md`.`id_material`))) left join `materiales_geograficos` `mg` on((`i`.`id_material` = `mg`.`id_material`))) left join `instrumentos_medicion` `im` on((`i`.`id_material` = `im`.`id_material`))) left join `materiales_dibujo_tecnico` `mdt` on((`i`.`id_material` = `mdt`.`id_material`))) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_prestamos_detalle`
--
DROP TABLE IF EXISTS `vista_prestamos_detalle`;

DROP VIEW IF EXISTS `vista_prestamos_detalle`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_prestamos_detalle`  AS SELECT `p`.`id_prestamo` AS `id_prestamo`, `p`.`id_material` AS `id_material`, `p`.`fecha_hora_registro` AS `fecha_hora_registro`, `p`.`fecha_hora_devolucion` AS `fecha_hora_devolucion`, `p`.`dni_usuario` AS `dni_usuario`, `p`.`dni_bibliotecario` AS `dni_bibliotecario`, `c`.`nombre` AS `nombre_categoria`, `em`.`desc_estado_material` AS `desc_estado_material`, coalesce(`l`.`nombre`,`im`.`tipo`,`md`.`tipo`,`mg`.`tipo`,`mdt`.`tipo`,'Material sin nombre') AS `nombre_material`, coalesce(`l`.`autor`,cast(`im`.`longitud_cm` as char charset utf8mb4),`md`.`tematica`,`mg`.`region`,`mdt`.`descripcion`,'-') AS `atributo_detalle`, `u`.`nombre` AS `nombre_usuario`, `u`.`apellido` AS `apellido_usuario` FROM (((((((((`prestamos` `p` join `inventario` `i` on((`i`.`id_material` = `p`.`id_material`))) join `categorias` `c` on((`c`.`id_categoria` = `i`.`id_categoria`))) join `estado_material` `em` on((`em`.`id_estado_material` = `i`.`id_estado_material`))) left join `libros` `l` on((`i`.`id_material` = `l`.`id_material`))) left join `instrumentos_medicion` `im` on((`i`.`id_material` = `im`.`id_material`))) left join `materiales_didacticos` `md` on((`i`.`id_material` = `md`.`id_material`))) left join `materiales_geograficos` `mg` on((`i`.`id_material` = `mg`.`id_material`))) left join `materiales_dibujo_tecnico` `mdt` on((`i`.`id_material` = `mdt`.`id_material`))) left join `usuario` `u` on((`u`.`dni_usuario` = `p`.`dni_usuario`))) ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
