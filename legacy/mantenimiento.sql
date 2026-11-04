-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.27-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.3.0.6589
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para mantenimiento
CREATE DATABASE IF NOT EXISTS `mantenimiento` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `mantenimiento`;

-- Volcando estructura para tabla mantenimiento.areas
CREATE TABLE IF NOT EXISTS `areas` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando datos para la tabla mantenimiento.areas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mantenimiento.direcciones
CREATE TABLE IF NOT EXISTS `direcciones` (
  `codigo` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla mantenimiento.direcciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mantenimiento.estadotarea
CREATE TABLE IF NOT EXISTS `estadotarea` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla mantenimiento.estadotarea: ~5 rows (aproximadamente)
INSERT INTO `estadotarea` (`id`, `nombre`) VALUES
	(1, 'Pendiente'),
	(2, 'En Progreso'),
	(3, 'Completo'),
	(4, 'Cancelado'),
	(5, 'Eliminado');

-- Volcando estructura para tabla mantenimiento.tareas
CREATE TABLE IF NOT EXISTS `tareas` (
  `nroArreglo` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(1000) DEFAULT NULL,
  `nota_electronica` varchar(45) DEFAULT NULL,
  `nombreApellidoAfectado` varchar(100) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `solucion` varchar(500) DEFAULT NULL,
  `fechaProblema` datetime DEFAULT NULL,
  `fechaSolucion` datetime DEFAULT NULL,
  `estadoTarea_id` int(11) DEFAULT NULL,
  `direccion_codigo` int(11) DEFAULT NULL,
  `motivoCancelacion` varchar(500) DEFAULT NULL,
  `motivoEliminacion` varchar(500) DEFAULT NULL,
  `fechaEliminado` datetime DEFAULT NULL,
  `codigoArea3` int(11) DEFAULT NULL,
  `fechaCreada` date DEFAULT NULL,
  `usuarioCreado` varchar(100) DEFAULT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`nroArreglo`),
  KEY `fk_tareas_estadoTarea1_idx` (`estadoTarea_id`),
  KEY `fk_tareas_area1_idx` (`direccion_codigo`),
  KEY `fk_codigoArea3` (`codigoArea3`),
  CONSTRAINT `fk_codigoArea3` FOREIGN KEY (`codigoArea3`) REFERENCES `areas` (`codigo`),
  CONSTRAINT `fk_tareas_direccion` FOREIGN KEY (`direccion_codigo`) REFERENCES `direcciones` (`codigo`),
  CONSTRAINT `fk_tareas_estadoTarea1` FOREIGN KEY (`estadoTarea_id`) REFERENCES `estadotarea` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla mantenimiento.tareas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla mantenimiento.tipousuario
CREATE TABLE IF NOT EXISTS `tipousuario` (
  `idrol` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idrol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla mantenimiento.tipousuario: ~2 rows (aproximadamente)
INSERT INTO `tipousuario` (`idrol`, `nombre`) VALUES
	(3, 'Administrador'),
	(4, 'Administrativo');

-- Volcando estructura para tabla mantenimiento.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `legajo` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  `apellido` varchar(45) DEFAULT NULL,
  `correo` varchar(45) DEFAULT NULL,
  `usuario` varchar(45) DEFAULT NULL,
  `contraseña` varchar(200) DEFAULT NULL,
  `idRol2` int(11) DEFAULT NULL,
  `motivoBaja` varchar(500) DEFAULT NULL,
  `ultimoAcceso` datetime DEFAULT NULL,
  PRIMARY KEY (`legajo`) USING BTREE,
  KEY `fk_usuario_rol_idx` (`idRol2`),
  CONSTRAINT `fk_idRol2` FOREIGN KEY (`idRol2`) REFERENCES `tipousuario` (`idrol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Volcando datos para la tabla mantenimiento.usuario: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
