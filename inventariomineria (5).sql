-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-04-2025 a las 04:29:37
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inventariomineria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos`
--

CREATE TABLE `activos` (
  `idactivo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `idcategoria` int(11) NOT NULL,
  `nro_asignacion` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `img` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `idubicacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `activos`
--

INSERT INTO `activos` (`idactivo`, `nombre`, `idcategoria`, `nro_asignacion`, `estado`, `descripcion`, `cantidad`, `img`, `fecha_registro`, `idubicacion`) VALUES
(6, 'Rotor ', 4, '234312', 'Bueno', '', 4, '1743991758_1742930848_activo_images (1).jpg', '2025-03-25 16:27:28', 3),
(7, 'Turbina ', 5, '45454', 'Regular', '', 3, '1743991742_Mantenimiento-Turbinas.jpg', '2025-03-25 16:28:27', 4),
(8, 'Motor', 1, '12321', '', '', 2, '1743991727_1742928661_activo_motor-eberle.jpg', '2025-03-25 17:10:51', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos_usados`
--

CREATE TABLE `activos_usados` (
  `id` int(11) NOT NULL,
  `envio_id` int(11) NOT NULL,
  `activo_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `idcategoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`idcategoria`, `nombre`) VALUES
(1, 'Herramienta'),
(2, 'Material'),
(3, 'Insumo'),
(4, 'Maquinaria'),
(5, 'Maquinaria Pesada'),
(6, 'EPP');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `contacto` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `nombre`, `rut`, `contacto`, `correo`, `direccion`, `empresa`) VALUES
(1, 'Juan Rosales', '20152517-8', '+56999961702', 'Asmeco@gmail.com', 'Chañaral 297', 'Asmeco'),
(2, 'Victor cardenas', '20152516-7', '+56 912345678', 'ingsm@gmail.com', 'Calle Ficticia 123', 'IngSm'),
(6, 'Manuel Herrera', '29827261-1', '+56 987654321', 'sanpedro@gmail.com', 'Camino de Ensueño 101', 'SanPedrotech'),
(7, 'Manto Verde', '21938367-2', '93884832', 'mantos@gmail.com', 'Manto verde', 'Mantos Cooper');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones`
--

CREATE TABLE `devoluciones` (
  `id` int(11) NOT NULL,
  `trabajo_producto_id` int(11) DEFAULT NULL,
  `trabajo_id` int(11) DEFAULT NULL,
  `devueltos` int(11) DEFAULT NULL,
  `usados` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `devoluciones`
--

INSERT INTO `devoluciones` (`id`, `trabajo_producto_id`, `trabajo_id`, `devueltos`, `usados`, `fecha`) VALUES
(111, 69, 69, 1, 0, '2025-03-29 20:59:17'),
(112, 65, 76, 4, 1, '2025-03-30 20:28:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

CREATE TABLE `documento` (
  `iddocumento` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `usuario_origen` int(11) NOT NULL,
  `usuario_destino` int(11) DEFAULT NULL,
  `eliminado` tinyint(1) DEFAULT 0,
  `eliminado_origen` tinyint(1) DEFAULT 0,
  `eliminado_destino` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documento`
--

INSERT INTO `documento` (`iddocumento`, `nombre`, `archivo`, `tipo`, `fecha_subida`, `usuario_origen`, `usuario_destino`, `eliminado`, `eliminado_origen`, `eliminado_destino`) VALUES
(12, 'aa', '1743140446_1742576254_revision_1742513671_permiso_517dc6b1-57e2-45ae-a898-62b805072dd4.jpg', 'jpg', '2025-03-28 02:40:46', 1, 3, 0, 1, 0),
(13, '1', '1743140472_checklist.xlsx', 'xlsx', '2025-03-28 02:41:12', 1, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `idempleado` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL COMMENT 'Fecha en la que el empleado ingresó a la empresa',
  `estado` enum('Activo','Inactivo','Suspendido') DEFAULT 'Activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en la que se registró el empleado en el sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`idempleado`, `nombre`, `rut`, `cargo`, `telefono`, `correo`, `fecha_ingreso`, `estado`, `fecha_creacion`) VALUES
(1, 'Juan Astorga', '12345677-3', 'Mecanico', '+56 9 7788 9900', 'juan@gmail.com', '2025-03-19', 'Activo', '2025-03-19 18:44:54'),
(2, 'Ana perez', '20938323-4', 'supervisora', '+56 9 2233 4455', 'anaperez@gmail.com', '2025-03-19', 'Activo', '2025-03-19 18:45:30'),
(3, 'Joao', '12345678-9', 'Mecanico', '999961603', 'joao@gmail.com', '1232-03-12', 'Activo', '2025-03-31 02:32:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio`
--

CREATE TABLE `envio` (
  `idenvio` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `ubicacion_id` int(11) NOT NULL,
  `estado_devolucion` enum('Pendiente','Devuelto') DEFAULT 'Pendiente',
  `observacion` text DEFAULT NULL,
  `devuelto` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio`
--

INSERT INTO `envio` (`idenvio`, `fecha`, `ubicacion_id`, `estado_devolucion`, `observacion`, `devuelto`) VALUES
(15, '2025-03-27 17:02:32', 2, 'Pendiente', NULL, 1),
(16, '2025-03-30 20:25:45', 5, 'Pendiente', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_detalle`
--

CREATE TABLE `envio_detalle` (
  `iddetalle` int(11) NOT NULL,
  `envio_id` int(11) NOT NULL,
  `activo_id` int(11) NOT NULL,
  `cantidad_enviada` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio_detalle`
--

INSERT INTO `envio_detalle` (`iddetalle`, `envio_id`, `activo_id`, `cantidad_enviada`) VALUES
(21, 15, 8, 1),
(22, 16, 8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_devolucion`
--

CREATE TABLE `envio_devolucion` (
  `iddevolucion` int(11) NOT NULL,
  `envio_id` int(11) NOT NULL,
  `activo_id` int(11) NOT NULL,
  `cantidad_devuelta` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio_devolucion`
--

INSERT INTO `envio_devolucion` (`iddevolucion`, `envio_id`, `activo_id`, `cantidad_devuelta`, `fecha`, `observacion`) VALUES
(28, 15, 8, 1, '2025-03-27 17:03:11', ''),
(29, 16, 8, 1, '2025-03-30 20:26:18', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_producto`
--

CREATE TABLE `envio_producto` (
  `idenvio` int(11) NOT NULL,
  `ubicacion_id` int(11) NOT NULL,
  `encargado` varchar(100) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `devuelto` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio_producto`
--

INSERT INTO `envio_producto` (`idenvio`, `ubicacion_id`, `encargado`, `fecha`, `devuelto`) VALUES
(49, 2, 'Jhonatan', '2025-03-30 20:22:45', 1),
(50, 5, 'a', '2025-03-30 20:24:34', 1),
(51, 1, 'a', '2025-03-30 20:40:49', 1),
(52, 1, 'Sistema', '2025-03-30 20:47:22', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_producto_detalle`
--

CREATE TABLE `envio_producto_detalle` (
  `iddetalle` int(11) NOT NULL,
  `envio_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad_enviada` int(11) NOT NULL,
  `cantidad_devuelta` int(11) DEFAULT 0,
  `fecha_devolucion` datetime(6) DEFAULT NULL,
  `cantidad_usada` int(11) DEFAULT 0,
  `cantidad_perdida` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envio_producto_detalle`
--

INSERT INTO `envio_producto_detalle` (`iddetalle`, `envio_id`, `producto_id`, `cantidad_enviada`, `cantidad_devuelta`, `fecha_devolucion`, `cantidad_usada`, `cantidad_perdida`) VALUES
(59, 49, 3, 3, 3, '2025-03-30 20:23:13.000000', 0, 0),
(60, 49, 8, 2, 1, '2025-03-30 20:23:13.000000', 1, 0),
(61, 50, 8, 1, 1, '2025-03-30 20:24:46.000000', 0, 0),
(62, 51, 8, 1, 1, '2025-03-30 20:41:27.000000', 0, 0),
(63, 52, 29, 3, 0, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `idhistorial` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `accion` varchar(255) NOT NULL COMMENT 'Ejemplo: Creación, Actualización, Eliminación',
  `entidad` varchar(100) NOT NULL,
  `entidad_id` int(11) NOT NULL COMMENT 'ID del elemento modificado',
  `detalle` text DEFAULT NULL COMMENT 'Descripción de la modificación',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que realizó la acción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`idhistorial`, `fecha`, `accion`, `entidad`, `entidad_id`, `detalle`, `usuario_id`) VALUES
(811, '2025-04-06 18:26:31', 'Limpieza Historial', 'historial', 0, 'Se eliminó todo el historial del sistema.', 1),
(812, '2025-04-06 18:26:40', 'Edición de Producto', 'producto', 7, 'Cambios: Cantidad: \'4\' → \'3\'', 1),
(813, '2025-04-06 20:16:08', 'Eliminación', 'trabajo', 69, 'Se eliminó el trabajo \'Mantención en Eje de Rotor Industrial\' (ID: 69) de la empresa \'1 - Asmeco\'', 1),
(814, '2025-04-06 20:40:12', 'Edición de Vehículo', 'vehiculo', 5, 'Se editó el vehículo \"Mahindra Pik-Up DCAB CRDE MT 4X4 2.2\" (ID: 5).', 1),
(815, '2025-04-06 20:42:06', 'Edición de Vehículo', 'vehiculo', 5, 'Se editó el vehículo \"Mahindra Pik-Up DCAB CRDE MT 4X4 2.2\" (ID: 5).', 1),
(816, '2025-04-06 20:44:40', 'Edición de Vehículo', 'vehiculo', 6, 'Se editó el vehículo \"2025 Chevrolet N400 Max 1.5 Van AC S\" (ID: 6).', 1),
(817, '2025-04-06 20:45:02', 'Edición de Vehículo', 'vehiculo', 5, 'Se editó el vehículo \"Mahindra Pik-Up DCAB CRDE MT 4X4 2.2\" (ID: 5).', 1),
(818, '2025-04-06 22:07:57', 'Actualización', 'activo', 8, 'Se actualizó el activo \"Motor\" (ID: 8). Estado: de Inservible a ', 1),
(819, '2025-04-06 22:08:00', 'Actualización', 'activo', 8, 'Se actualizó el activo \"Motor\" (ID: 8). ', 1),
(820, '2025-04-06 22:08:07', 'Actualización', 'activo', 8, 'Se actualizó el activo \"Motor\" (ID: 8). ', 1),
(821, '2025-04-06 22:08:15', 'Actualización', 'activo', 8, 'Se actualizó el activo \"Motor\" (ID: 8). ', 1),
(822, '2025-04-06 22:08:47', 'Actualización', 'activo', 8, 'Se actualizó el activo \"Motor\" (ID: 8). ', 1),
(823, '2025-04-06 22:09:02', 'Actualización', 'activo', 7, 'Se actualizó el activo \"Turbina \" (ID: 7). ', 1),
(824, '2025-04-06 22:09:18', 'Actualización', 'activo', 6, 'Se actualizó el activo \"Rotor \" (ID: 6). ', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `operaciones`
--

CREATE TABLE `operaciones` (
  `idoperacion` int(11) NOT NULL,
  `trabajo_id` int(11) NOT NULL,
  `operacion` varchar(50) NOT NULL,
  `pto_trab` varchar(100) NOT NULL,
  `desc_pto_trab` varchar(255) NOT NULL,
  `desc_oper` varchar(255) NOT NULL,
  `n_pers` int(11) NOT NULL,
  `h_est` int(11) NOT NULL,
  `hh_tot_prog` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `operaciones`
--

INSERT INTO `operaciones` (`idoperacion`, `trabajo_id`, `operacion`, `pto_trab`, `desc_pto_trab`, `desc_oper`, `n_pers`, `h_est`, `hh_tot_prog`) VALUES
(336, 73, '011', 'a', 'a', 'a', 2, 12, 60),
(402, 76, '', '', '', '', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `idproducto` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `estado` varchar(255) DEFAULT 'Disponible',
  `descripcion` text DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `nro_asignacion` varchar(100) DEFAULT NULL COMMENT 'Número de asignación del producto',
  `trabajo_id` int(11) DEFAULT NULL COMMENT 'Trabajo al que está asignado',
  `categoria_id` int(11) NOT NULL,
  `en_uso` int(11) DEFAULT 0,
  `disponibles` int(11) GENERATED ALWAYS AS (`cantidad` - if(`en_uso`,1,0)) STORED COMMENT 'Cantidad disponible automáticamente calculada',
  `img` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen del producto',
  `numero_parte` varchar(100) DEFAULT NULL COMMENT 'Número de parte del producto',
  `eliminado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`idproducto`, `nombre`, `precio`, `estado`, `descripcion`, `cantidad`, `nro_asignacion`, `trabajo_id`, `categoria_id`, `en_uso`, `img`, `numero_parte`, `eliminado`) VALUES
(1, 'Electrodo punto amarillo 1/8', 6290.00, 'Excelente', 'Ninguna', 16, '1341231', NULL, 2, 0, NULL, NULL, 0),
(2, 'discos de corte 4,5', 4990.00, 'Regular', '', 11, '2323421', NULL, 3, 0, NULL, NULL, 0),
(3, 'Perfil Cuadrado Acero 40x40x2 mm 6 m', 17390.00, 'Deteriorado', NULL, 11, '23423424', NULL, 1, 0, NULL, NULL, 0),
(4, 'Electrodo 6011 3/32', 6990.00, 'Inservible', NULL, 10, '298398242', NULL, 1, 0, NULL, NULL, 0),
(5, 'Casco de Seguridad Tipo 1 -HDPE- Clase C, G, E', 23000.00, 'Excelente', NULL, 10, NULL, NULL, 6, 0, NULL, NULL, 0),
(6, 'Cubre Puños Manos Con Chiporro Protector De Frio Impermeable', 8990.00, 'Excelente', NULL, 12, NULL, NULL, 6, 3, NULL, NULL, 0),
(7, 'Guante cuero supervisor lavable, clarino executive negro', 9190.00, 'Excelente', '', 3, '', NULL, 6, 0, NULL, NULL, 0),
(8, 'Calzado de Seguridad HW Explorer Beige', 44990.00, 'Inservible', NULL, 11, NULL, NULL, 6, -1, NULL, NULL, 0),
(9, 'Esmeril angular eléctrico 4,5\\\" 710 W', 36990.00, 'Excelente', NULL, 11, NULL, NULL, 1, 0, NULL, NULL, 0),
(10, 'Soldador a CUT-100 REDBO', 1400000.00, 'Bueno', NULL, 11, NULL, NULL, 4, 0, NULL, NULL, 0),
(11, 'Fierro A-63 12x12 mm 6 m', 6920.00, 'Excelente', NULL, 10, NULL, NULL, 2, 0, NULL, NULL, 0),
(28, 'pie de rey', 5000.00, 'Excelente', '', 3, '', NULL, 1, 0, NULL, NULL, 0),
(29, 'Producto 1', 1333.00, 'Excelente', NULL, 3, NULL, NULL, 3, 0, NULL, NULL, 1),
(31, 'Producto test', 9999.99, 'Excelente', 'PRUEBA DESCRIPCION', 10, '0', NULL, 1, 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajo`
--

CREATE TABLE `trabajo` (
  `idtrabajo` int(11) NOT NULL,
  `tipo` enum('OT','OM') NOT NULL COMMENT 'OT: Orden de Trabajo, OM: Orden de Mantenimiento',
  `titulo` varchar(255) NOT NULL DEFAULT 'Orden de Trabajo',
  `nro_orden` varchar(100) NOT NULL COMMENT 'Número de la orden de trabajo',
  `fecha_creacion` date NOT NULL DEFAULT current_timestamp(),
  `cliente_id` int(11) NOT NULL COMMENT 'ID del cliente asignado',
  `fecha_entrega` date DEFAULT NULL COMMENT 'Fecha de entrega del trabajo',
  `estado` enum('Pendiente','En Proceso','Completado','Cancelado') DEFAULT 'Pendiente',
  `archivo_orden_compra` varchar(255) DEFAULT NULL COMMENT 'Archivo de orden de compra asociado',
  `archivo_adicional` varchar(255) DEFAULT NULL COMMENT 'Otros archivos adjuntos',
  `numero_cotizacion` varchar(100) NOT NULL COMMENT 'Número de la cotización asociada',
  `fecha_cotizacion` date NOT NULL COMMENT 'Fecha en que se generó la cotización',
  `duracion_dias` int(11) NOT NULL COMMENT 'Duración estimada en días',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio del trabajo',
  `total_estimado` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo total estimado',
  `descripcion_orden` text DEFAULT NULL COMMENT 'Descripción detallada de la orden',
  `descripcion_detallada` text NOT NULL,
  `nro_aviso` varchar(50) DEFAULT NULL COMMENT 'Número de aviso asociado',
  `ubicacion_tecnica` varchar(255) DEFAULT NULL COMMENT 'Ubicación técnica del equipo',
  `equipo` varchar(255) NOT NULL,
  `equipo_id` int(11) DEFAULT NULL COMMENT 'ID del equipo relacionado',
  `den_equipo` varchar(255) DEFAULT NULL COMMENT 'Denominación del equipo',
  `clase_orden` varchar(100) DEFAULT NULL COMMENT 'Ej: ZM05-MC-Orden Planes Mantto',
  `clase_actividad_pl` varchar(50) DEFAULT NULL COMMENT 'Ej: 128',
  `prioridad` varchar(50) DEFAULT NULL,
  `revision` varchar(50) NOT NULL,
  `reserva` varchar(50) DEFAULT NULL,
  `grp_planificacion` varchar(100) DEFAULT NULL COMMENT 'Grupo de planificación',
  `pto_trab_responsable` varchar(100) DEFAULT NULL COMMENT 'Punto de trabajo responsable',
  `fecha_ini_prog` date DEFAULT NULL COMMENT 'Fecha inicio programado',
  `hora_inicio` time DEFAULT NULL COMMENT 'Hora de inicio programada',
  `fecha_fin_prog` date DEFAULT NULL COMMENT 'Fecha fin programado',
  `hora_fin` time DEFAULT NULL COMMENT 'Hora de finalización programada',
  `sol_ped` enum('Servicios','Materiales') DEFAULT NULL COMMENT 'Solicitud de pedido',
  `fecha_real_ejecucion` date DEFAULT NULL COMMENT 'Fecha real de ejecución',
  `comentarios` text DEFAULT NULL COMMENT 'Comentarios sobre la orden'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajo`
--

INSERT INTO `trabajo` (`idtrabajo`, `tipo`, `titulo`, `nro_orden`, `fecha_creacion`, `cliente_id`, `fecha_entrega`, `estado`, `archivo_orden_compra`, `archivo_adicional`, `numero_cotizacion`, `fecha_cotizacion`, `duracion_dias`, `fecha_inicio`, `total_estimado`, `descripcion_orden`, `descripcion_detallada`, `nro_aviso`, `ubicacion_tecnica`, `equipo`, `equipo_id`, `den_equipo`, `clase_orden`, `clase_actividad_pl`, `prioridad`, `revision`, `reserva`, `grp_planificacion`, `pto_trab_responsable`, `fecha_ini_prog`, `hora_inicio`, `fecha_fin_prog`, `hora_fin`, `sol_ped`, `fecha_real_ejecucion`, `comentarios`) VALUES
(73, 'OT', 'Cambio de rodamiento reductor TXT8', '0000002', '2025-03-25', 7, '2025-03-29', 'Pendiente', NULL, NULL, '', '0000-00-00', 6, '2025-03-25', 0.00, 'orden de compra', 'Reparacion cambio de rodamiento y ajuste de rodamientos', '1', 'Correa CV03 Polea de Cola', 'Apilador', NULL, 'Cambio de rodamiento reductor TXT8', 'Emergencia', 'Reparacion', 'Alta', 'Primera revision', '0000000000', '', 'Area mecanica', NULL, '00:00:00', NULL, '23:59:00', 'Servicios', NULL, ''),
(76, 'OT', 'prueba 1', '0000003', '2025-03-29', 1, '2322-03-12', 'Pendiente', NULL, NULL, '', '0000-00-00', 4, '1231-03-12', 0.00, 'prueba 1', 'a', 'prueba 1', 'prueba 1', 'prueba 1', NULL, 'prueba 1', 'prueba 1', 'prueba 1', 'prueba 1', 'prueba 1', '23', 'prueba 1', 'prueba 1', NULL, '19:26:00', NULL, '19:26:00', 'Servicios', NULL, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajo_producto`
--

CREATE TABLE `trabajo_producto` (
  `idtrabajo_producto` int(11) NOT NULL,
  `trabajo_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL COMMENT 'Cantidad de productos asignados al trabajo',
  `descripcion` varchar(255) DEFAULT NULL,
  `numero_parte` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajo_producto`
--

INSERT INTO `trabajo_producto` (`idtrabajo_producto`, `trabajo_id`, `producto_id`, `cantidad`, `descripcion`, `numero_parte`) VALUES
(65, 76, 3, 5, '', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajo_trabajadores`
--

CREATE TABLE `trabajo_trabajadores` (
  `id` int(11) NOT NULL,
  `id_trabajo` int(11) NOT NULL,
  `id_trabajador` int(11) NOT NULL,
  `horas_trabajadas` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajo_trabajadores`
--

INSERT INTO `trabajo_trabajadores` (`id`, `id_trabajo`, `id_trabajador`, `horas_trabajadas`) VALUES
(10, 73, 2, 12.00),
(15, 76, 2, 17.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `idubicacion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ubicaciones`
--

INSERT INTO `ubicaciones` (`idubicacion`, `nombre`, `descripcion`) VALUES
(1, 'Sucursal Caldera', ''),
(2, 'Sucursal Copiapo', ''),
(3, 'Sucursal Tierra amarilla', ''),
(4, 'Sucursal Paipote', ''),
(5, 'Sucursal Manto verde', ''),
(7, 'Sucursal Los Andes', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Contraseña encriptada del usuario',
  `rol` varchar(50) NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `email`, `password`, `rol`) VALUES
(1, 'Cristian', 'vega@gmail.com', '$2y$10$9VmWBL4h9ZC9jzHImXZRn.rNE.pzlrd7aCUu7W2DXOuHgbP.Z95pC', 'Administrador'),
(3, 'juanito', 'juanito@gmail.com', '$2y$10$Kyv6PmgRYTz1NTT03f8xBeCYulpvpoBwJIDYS5M98lYd7RRyHZQua', 'usuario'),
(6, 'camilo', 'camilo@gmail.com', '$2y$10$Nx2ptLazowAzsOAi/zxf6uJDITRpNZgpZo7wnHGsgRBQh0.e5thi2', 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo`
--

CREATE TABLE `vehiculo` (
  `idvehiculo` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `patente` varchar(25) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `anio` int(4) NOT NULL,
  `precio` decimal(15,2) DEFAULT NULL,
  `revision_tecnica` varchar(255) DEFAULT NULL COMMENT 'Documento o estado de revisión técnica',
  `permiso_circulacion` varchar(255) DEFAULT NULL COMMENT 'Documento de permiso de circulación',
  `estado` varchar(255) DEFAULT 'Disponible',
  `descripcion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de ingreso del vehículo al sistema',
  `img` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen del vehículo',
  `ultima_mantencion` date DEFAULT NULL COMMENT 'Última fecha en que se hizo mantenimiento general',
  `fecha_cambio_aceite` date DEFAULT NULL COMMENT 'Fecha del último cambio de aceite',
  `vencimiento_cambio_aceite` date DEFAULT NULL,
  `permiso_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio del permiso de circulación',
  `permiso_fin` date DEFAULT NULL COMMENT 'Fecha de vencimiento del permiso de circulación',
  `revision_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio de la revisión técnica',
  `revision_fin` date DEFAULT NULL COMMENT 'Fecha de vencimiento de la revisión técnica'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculo`
--

INSERT INTO `vehiculo` (`idvehiculo`, `nombre`, `patente`, `marca`, `modelo`, `anio`, `precio`, `revision_tecnica`, `permiso_circulacion`, `estado`, `descripcion`, `fecha_registro`, `img`, `ultima_mantencion`, `fecha_cambio_aceite`, `vencimiento_cambio_aceite`, `permiso_inicio`, `permiso_fin`, `revision_inicio`, `revision_fin`) VALUES
(5, 'Mahindra Pik-Up DCAB CRDE MT 4X4 2.2', '6WMKV', 'Mahindra pik-up', 'DCAB CRDE MT 4X4 2.2', 2025, 16990000.00, '1743986702_revision_Sin-título1.png', '1743986702_permiso_mfujcvc1signwubopcm7.jpg', 'Activo', '123123', '2025-03-20 23:34:31', '1743986526_imagen_01011190793-1-1-3.png', '2025-03-26', '2025-03-17', '2025-03-31', '2025-03-22', '2025-11-30', '2025-03-21', '2025-03-26'),
(6, '2025 Chevrolet N400 Max 1.5 Van AC S', '3KMWJH', 'Chevrolet', 'N400 Max 1.5', 2025, 8000000.00, '1743986680_revision_Sin-título1.png', '1743986680_permiso_mfujcvc1signwubopcm7.jpg', 'Activo', 'aaaa', '2025-03-22 21:42:08', '1742679728_imagen_1742513671_imagen_CHEVROLET-N400MAX-sqjryj.jpg', '2025-03-19', '2025-03-22', '2025-03-23', '2025-03-22', '2025-03-27', '2025-03-22', '2025-03-23');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`idactivo`),
  ADD KEY `idcategoria` (`idcategoria`),
  ADD KEY `fk_activo_ubicacion` (`idubicacion`);

--
-- Indices de la tabla `activos_usados`
--
ALTER TABLE `activos_usados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `envio_id` (`envio_id`),
  ADD KEY `activo_id` (`activo_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`);

--
-- Indices de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`iddocumento`),
  ADD KEY `usuario_origen` (`usuario_origen`),
  ADD KEY `usuario_destino` (`usuario_destino`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`idempleado`);

--
-- Indices de la tabla `envio`
--
ALTER TABLE `envio`
  ADD PRIMARY KEY (`idenvio`),
  ADD KEY `ubicacion_id` (`ubicacion_id`);

--
-- Indices de la tabla `envio_detalle`
--
ALTER TABLE `envio_detalle`
  ADD PRIMARY KEY (`iddetalle`),
  ADD KEY `envio_id` (`envio_id`),
  ADD KEY `activo_id` (`activo_id`);

--
-- Indices de la tabla `envio_devolucion`
--
ALTER TABLE `envio_devolucion`
  ADD PRIMARY KEY (`iddevolucion`),
  ADD KEY `envio_id` (`envio_id`),
  ADD KEY `activo_id` (`activo_id`);

--
-- Indices de la tabla `envio_producto`
--
ALTER TABLE `envio_producto`
  ADD PRIMARY KEY (`idenvio`),
  ADD KEY `ubicacion_id` (`ubicacion_id`);

--
-- Indices de la tabla `envio_producto_detalle`
--
ALTER TABLE `envio_producto_detalle`
  ADD PRIMARY KEY (`iddetalle`),
  ADD KEY `envio_id` (`envio_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`idhistorial`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `operaciones`
--
ALTER TABLE `operaciones`
  ADD PRIMARY KEY (`idoperacion`),
  ADD KEY `trabajo_id` (`trabajo_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`idproducto`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `fk_producto_trabajo` (`trabajo_id`);

--
-- Indices de la tabla `trabajo`
--
ALTER TABLE `trabajo`
  ADD PRIMARY KEY (`idtrabajo`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `trabajo_producto`
--
ALTER TABLE `trabajo_producto`
  ADD PRIMARY KEY (`idtrabajo_producto`),
  ADD KEY `trabajo_id` (`trabajo_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `trabajo_trabajadores`
--
ALTER TABLE `trabajo_trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_trabajo` (`id_trabajo`),
  ADD KEY `id_trabajador` (`id_trabajador`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`idubicacion`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  ADD PRIMARY KEY (`idvehiculo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `idactivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `activos_usados`
--
ALTER TABLE `activos_usados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT de la tabla `documento`
--
ALTER TABLE `documento`
  MODIFY `iddocumento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `idempleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `envio`
--
ALTER TABLE `envio`
  MODIFY `idenvio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `envio_detalle`
--
ALTER TABLE `envio_detalle`
  MODIFY `iddetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `envio_devolucion`
--
ALTER TABLE `envio_devolucion`
  MODIFY `iddevolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `envio_producto`
--
ALTER TABLE `envio_producto`
  MODIFY `idenvio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `envio_producto_detalle`
--
ALTER TABLE `envio_producto_detalle`
  MODIFY `iddetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `idhistorial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=825;

--
-- AUTO_INCREMENT de la tabla `operaciones`
--
ALTER TABLE `operaciones`
  MODIFY `idoperacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=410;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `idproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `trabajo`
--
ALTER TABLE `trabajo`
  MODIFY `idtrabajo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `trabajo_producto`
--
ALTER TABLE `trabajo_producto`
  MODIFY `idtrabajo_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `trabajo_trabajadores`
--
ALTER TABLE `trabajo_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `idubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  MODIFY `idvehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activos`
--
ALTER TABLE `activos`
  ADD CONSTRAINT `activos_ibfk_1` FOREIGN KEY (`idcategoria`) REFERENCES `categoria` (`idcategoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_activo_ubicacion` FOREIGN KEY (`idubicacion`) REFERENCES `ubicaciones` (`idubicacion`);

--
-- Filtros para la tabla `activos_usados`
--
ALTER TABLE `activos_usados`
  ADD CONSTRAINT `activos_usados_ibfk_1` FOREIGN KEY (`envio_id`) REFERENCES `envio` (`idenvio`),
  ADD CONSTRAINT `activos_usados_ibfk_2` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`idactivo`);

--
-- Filtros para la tabla `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`usuario_origen`) REFERENCES `usuario` (`idusuario`),
  ADD CONSTRAINT `documento_ibfk_2` FOREIGN KEY (`usuario_destino`) REFERENCES `usuario` (`idusuario`);

--
-- Filtros para la tabla `envio`
--
ALTER TABLE `envio`
  ADD CONSTRAINT `envio_ibfk_1` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`idubicacion`);

--
-- Filtros para la tabla `envio_detalle`
--
ALTER TABLE `envio_detalle`
  ADD CONSTRAINT `envio_detalle_ibfk_1` FOREIGN KEY (`envio_id`) REFERENCES `envio` (`idenvio`) ON DELETE CASCADE,
  ADD CONSTRAINT `envio_detalle_ibfk_2` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`idactivo`);

--
-- Filtros para la tabla `envio_devolucion`
--
ALTER TABLE `envio_devolucion`
  ADD CONSTRAINT `envio_devolucion_ibfk_1` FOREIGN KEY (`envio_id`) REFERENCES `envio` (`idenvio`),
  ADD CONSTRAINT `envio_devolucion_ibfk_2` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`idactivo`);

--
-- Filtros para la tabla `envio_producto`
--
ALTER TABLE `envio_producto`
  ADD CONSTRAINT `envio_producto_ibfk_1` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`idubicacion`);

--
-- Filtros para la tabla `envio_producto_detalle`
--
ALTER TABLE `envio_producto_detalle`
  ADD CONSTRAINT `envio_producto_detalle_ibfk_1` FOREIGN KEY (`envio_id`) REFERENCES `envio_producto` (`idenvio`),
  ADD CONSTRAINT `envio_producto_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`idproducto`);

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `operaciones`
--
ALTER TABLE `operaciones`
  ADD CONSTRAINT `operaciones_ibfk_1` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajo` (`idtrabajo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_producto_trabajo` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajo` (`idtrabajo`) ON DELETE SET NULL,
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`idcategoria`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trabajo`
--
ALTER TABLE `trabajo`
  ADD CONSTRAINT `trabajo_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trabajo_producto`
--
ALTER TABLE `trabajo_producto`
  ADD CONSTRAINT `trabajo_producto_ibfk_1` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajo` (`idtrabajo`) ON DELETE CASCADE,
  ADD CONSTRAINT `trabajo_producto_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`idproducto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trabajo_trabajadores`
--
ALTER TABLE `trabajo_trabajadores`
  ADD CONSTRAINT `trabajo_trabajadores_ibfk_1` FOREIGN KEY (`id_trabajo`) REFERENCES `trabajo` (`idtrabajo`) ON DELETE CASCADE,
  ADD CONSTRAINT `trabajo_trabajadores_ibfk_2` FOREIGN KEY (`id_trabajador`) REFERENCES `empleado` (`idempleado`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
