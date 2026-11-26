-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-11-2026 a las 03:55:04
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
-- Base de datos: `sistema_libreria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `activa`) VALUES
(1, 'Cuadernos', 'Cuadernos y libretas para estudio y oficina', 1),
(2, 'Escritura', 'Lapiceros, lapices y marcadores', 1),
(3, 'Papel y archivo', 'Papel, folders y materiales de archivo', 1),
(4, 'Dibujo y arte', 'Colores y materiales para dibujo', 1),
(5, 'Libros', 'Libros de apoyo y lectura', 1),
(6, 'benjamin', 'chanfle', 1),
(7, 'Papelería', 'Cuaderno, pliego papel bond', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `nombre_completo` varchar(120) NOT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre_completo`, `telefono`, `correo`, `direccion`, `fecha_registro`, `activo`) VALUES
(1, 'Ana Martinez', '7123-4567', 'ana.martinez@email.test', 'Ayutuxtepeque, San Salvador', '2026-10-05 10:15:00', 1),
(2, 'Luis Gomez', '7234-5678', 'luis.gomez@email.test', 'Mejicanos, San Salvador', '2026-10-06 11:20:00', 1),
(3, 'Maria Lopez', '7345-6789', 'maria.lopez@email.test', 'San Salvador', '2026-10-08 14:00:00', 1),
(4, 'Jose Ramirez', '7456-7890', 'jose.ramirez@email.test', 'Cuscatancingo, San Salvador', '2026-10-10 09:45:00', 0),
(5, 'Sofia Hernandez', '7567-8901', NULL, 'Apopa, San Salvador', '2026-10-12 15:30:00', 1),
(6, 'Alexis Benjamin Cisneros Luciano', '69242927', 'bluciano658@gmail.com', 'Col San Benito Av. Las Palmas', '2026-11-18 22:18:54', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id_compra` bigint(20) UNSIGNED NOT NULL,
  `id_proveedor` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha_compra` datetime NOT NULL DEFAULT current_timestamp(),
  `numero_documento` varchar(60) DEFAULT NULL,
  `observaciones` varchar(500) DEFAULT NULL,
  `estado` enum('RECIBIDA','ANULADA') NOT NULL DEFAULT 'RECIBIDA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id_compra`, `id_proveedor`, `id_usuario`, `fecha_compra`, `numero_documento`, `observaciones`, `estado`) VALUES
(1, 1, 1, '2026-10-15 09:30:00', 'FAC-PROV-1045', 'Compra de cuadernos y lapiceros recibida', 'RECIBIDA'),
(2, 2, 2, '2026-10-28 10:45:00', 'FAC-PROV-1088', 'Reposicion de papel y articulos escolares', 'RECIBIDA'),
(3, 3, 1, '2026-11-17 23:55:28', '7777', 'opcional', 'RECIBIDA'),
(4, 3, 1, '2026-11-17 23:56:12', '7777', NULL, 'ANULADA'),
(5, 2, 1, '2026-11-18 23:24:27', '0002', 'abastecimiento papeleria', 'RECIBIDA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
--

CREATE TABLE `detalle_compra` (
  `id_detalle_compra` bigint(20) UNSIGNED NOT NULL,
  `id_compra` bigint(20) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `costo_unitario`) STORED
) ;

--
-- Volcado de datos para la tabla `detalle_compra`
--

INSERT INTO `detalle_compra` (`id_detalle_compra`, `id_compra`, `id_producto`, `cantidad`, `costo_unitario`) VALUES
(1, 1, 1, 20, 1.60),
(2, 1, 3, 50, 0.15),
(3, 2, 7, 8, 4.25),
(4, 2, 8, 30, 0.18),
(5, 3, 5, 5, 0.10),
(6, 3, 1, 1, 1.60),
(7, 4, 7, 5, 4.25),
(8, 5, 6, 20, 0.15),
(9, 5, 14, 10, 2.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_reserva`
--

CREATE TABLE `detalle_reserva` (
  `id_detalle_reserva` bigint(20) UNSIGNED NOT NULL,
  `id_reserva` bigint(20) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL
) ;

--
-- Volcado de datos para la tabla `detalle_reserva`
--

INSERT INTO `detalle_reserva` (`id_detalle_reserva`, `id_reserva`, `id_producto`, `cantidad`) VALUES
(1, 1, 5, 4),
(2, 1, 6, 2),
(3, 2, 12, 1),
(4, 3, 1, 3),
(5, 4, 5, 8),
(6, 5, 11, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id_detalle_venta` bigint(20) UNSIGNED NOT NULL,
  `id_venta` bigint(20) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  `utilidad` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * (`precio_unitario` - `costo_unitario`)) STORED
) ;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id_detalle_venta`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `costo_unitario`) VALUES
(1, 1, 1, 2, 2.50, 1.60),
(2, 1, 3, 5, 0.30, 0.15),
(3, 2, 7, 1, 5.75, 4.25),
(4, 2, 10, 1, 2.80, 1.90),
(5, 3, 12, 2, 6.50, 4.50),
(6, 3, 5, 9, 0.25, 0.10),
(7, 3, 8, 6, 0.40, 0.18),
(8, 4, 9, 70, 0.20, 0.08),
(9, 5, 11, 20, 1.25, 0.70);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` bigint(20) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `tipo` enum('ENTRADA_COMPRA','SALIDA_VENTA','AJUSTE_ENTRADA','AJUSTE_SALIDA') NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `existencia_anterior` int(10) UNSIGNED NOT NULL,
  `existencia_nueva` int(10) UNSIGNED NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `referencia` varchar(100) DEFAULT NULL,
  `observaciones` varchar(500) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id_movimiento`, `id_producto`, `id_usuario`, `tipo`, `cantidad`, `existencia_anterior`, `existencia_nueva`, `fecha_movimiento`, `referencia`, `observaciones`) VALUES
(1, 1, 1, 'ENTRADA_COMPRA', 20, 25, 45, '2026-10-15 09:35:00', 'COMPRA-1', 'Ingreso de cuadernos'),
(2, 3, 2, 'ENTRADA_COMPRA', 50, 30, 80, '2026-10-15 09:40:00', 'COMPRA-1', 'Ingreso de lapiceros azules'),
(3, 7, 1, 'ENTRADA_COMPRA', 8, 4, 12, '2026-10-28 10:50:00', 'COMPRA-2', 'Ingreso de resmas de papel'),
(4, 13, 1, 'AJUSTE_SALIDA', 8, 90, 82, '2026-11-17 12:07:18', NULL, 'defectuosos'),
(5, 5, 1, 'ENTRADA_COMPRA', 5, 90, 95, '2026-11-17 23:55:28', 'COMPRA #3', 'Entrada por compra'),
(6, 1, 1, 'ENTRADA_COMPRA', 1, 5, 6, '2026-11-17 23:55:28', 'COMPRA #3', 'Entrada por compra'),
(7, 7, 1, 'ENTRADA_COMPRA', 5, 12, 17, '2026-11-17 23:56:12', 'COMPRA #4', 'Entrada por compra'),
(8, 7, 1, 'AJUSTE_SALIDA', 5, 17, 12, '2026-11-18 11:07:18', 'ANULACION COMPRA #4', 'Reversión de compra anulada'),
(9, 13, 1, 'AJUSTE_ENTRADA', 8, 82, 90, '2026-11-18 23:20:03', NULL, ''),
(10, 6, 1, 'ENTRADA_COMPRA', 20, 40, 60, '2026-11-18 23:24:27', 'COMPRA #5', 'Entrada por compra'),
(11, 14, 1, 'ENTRADA_COMPRA', 10, 10, 20, '2026-11-18 23:24:27', 'COMPRA #5', 'Entrada por compra'),
(12, 12, 1, 'SALIDA_VENTA', 2, 2, 0, '2026-11-21 18:10:42', 'VENTA #3', 'Salida por venta'),
(13, 5, 1, 'SALIDA_VENTA', 9, 95, 86, '2026-11-21 18:10:42', 'VENTA #3', 'Salida por venta'),
(14, 8, 1, 'SALIDA_VENTA', 6, 55, 49, '2026-11-21 18:10:42', 'VENTA #3', 'Salida por venta'),
(15, 12, 1, 'AJUSTE_ENTRADA', 2, 0, 2, '2026-11-21 18:23:49', 'ANULACION VENTA #3', 'Devolución por anulación de venta'),
(16, 5, 1, 'AJUSTE_ENTRADA', 9, 86, 95, '2026-11-21 18:23:49', 'ANULACION VENTA #3', 'Devolución por anulación de venta'),
(17, 8, 1, 'AJUSTE_ENTRADA', 6, 49, 55, '2026-11-21 18:23:49', 'ANULACION VENTA #3', 'Devolución por anulación de venta'),
(18, 9, 1, 'SALIDA_VENTA', 70, 70, 0, '2026-11-21 22:42:24', 'VENTA #4', 'Salida por venta'),
(19, 11, 1, 'AJUSTE_SALIDA', 20, 24, 4, '2026-11-22 23:06:04', 'RESERVA #5', 'Salida por creación de reservación'),
(20, 11, 1, 'AJUSTE_ENTRADA', 20, 4, 24, '2026-11-22 23:08:23', 'RESERVA #5', 'Devolución por cancelación de reservación'),
(21, 11, 1, 'AJUSTE_SALIDA', 20, 24, 4, '2026-11-22 23:08:49', 'RESERVA #5', 'Salida por reactivación de reservación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED DEFAULT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL,
  `existencia` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stock_minimo` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `codigo`, `nombre`, `descripcion`, `precio_compra`, `precio_venta`, `existencia`, `stock_minimo`, `activo`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 1, 'CUA-001', 'Cuaderno universitario 100 hojas', 'Cuaderno rayado de espiral', 1.60, 2.50, 6, 10, 1, '2026-11-16 20:08:04', '2026-11-17 23:55:28'),
(2, 1, 'CUA-002', 'Cuaderno pequeno cosido', 'Cuaderno de 50 hojas', 0.65, 1.00, 32, 8, 1, '2026-11-16 20:08:04', '2026-11-16 20:08:04'),
(3, 2, 'ESC-001', 'Lapicero azul', 'Lapicero de tinta azul', 0.15, 0.30, 80, 20, 1, '2026-11-16 20:08:04', '2026-11-16 20:08:04'),
(4, 2, 'ESC-002', 'Lapicero negro', 'Lapicero de tinta negra', 0.15, 0.30, 65, 20, 1, '2026-11-16 20:08:04', '2026-11-16 20:08:04'),
(5, 2, 'ESC-003', 'Lapiz grafito HB', 'Lapiz numero 2', 0.10, 0.25, 95, 25, 1, '2026-11-16 20:08:04', '2026-11-21 18:23:49'),
(6, 2, 'ESC-004', 'Borrador blanco', 'Borrador escolar', 0.15, 0.25, 60, 10, 1, '2026-11-16 20:08:04', '2026-11-18 23:24:27'),
(7, 3, 'PAP-001', 'Resma de papel carta', 'Papel bond carta, 500 hojas', 4.25, 5.75, 12, 5, 1, '2026-11-16 20:08:04', '2026-11-18 11:07:17'),
(8, 3, 'PAP-002', 'Folder tamano carta', 'Folder manila carta', 0.18, 0.40, 55, 15, 1, '2026-11-16 20:08:04', '2026-11-21 18:23:49'),
(9, 3, 'PAP-003', 'Pliego de papel bond', 'Pliego individual', 0.08, 0.20, 0, 20, 1, '2026-11-16 20:08:04', '2026-11-21 22:42:24'),
(10, 4, 'ART-001', 'Colores de madera x12', 'Caja de 12 colores', 1.90, 2.80, 18, 6, 1, '2026-11-16 20:08:04', '2026-11-16 20:08:04'),
(11, 4, 'ART-002', 'Marcador permanente', 'Marcador punta mediana', 0.70, 1.25, 4, 8, 1, '2026-11-16 20:08:04', '2026-11-22 23:08:49'),
(12, 5, 'LIB-001', 'Libro de lectura', 'Libro de apoyo escolar', 4.50, 6.50, 2, 3, 1, '2026-11-16 20:08:04', '2026-11-21 18:23:49'),
(13, 7, '001', 'lapiz', 'no lo se', 20.00, 25.00, 90, 22, 1, '2026-11-16 23:30:26', '2026-11-18 23:20:13'),
(14, 1, '807', 'prueba', '', 2.50, 2.50, 20, 5, 1, '2026-11-18 23:21:04', '2026-11-18 23:24:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(10) UNSIGNED NOT NULL,
  `nombre_empresa` varchar(150) NOT NULL,
  `nombre_contacto` varchar(120) DEFAULT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `nombre_contacto`, `telefono`, `correo`, `direccion`, `activo`, `fecha_registro`) VALUES
(1, 'Distribuidora Escola', 'Roberto Diaz', '2201-1000', 'ventas@distribuidora.test', 'San Salvador', 1, '2026-09-15 08:00:00'),
(2, 'Papeleria Central', 'Laura Mendez', '2201-2000', 'contacto@central.test', 'Soyapango', 1, '2026-09-18 08:00:00'),
(3, 'Comercial El Estudiante', 'Pedro', '2201-3000', 'pedidos@estudiante.test', 'San Salvador', 1, '2026-09-20 08:00:00'),
(5, 'Papeleria Central', 'Laura Mendez', '2201-2000', 'contacto@central.test', 'las palmas', 1, '2026-11-17 20:13:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` bigint(20) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `fecha_reserva` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_limite_retiro` date DEFAULT NULL,
  `estado` enum('PENDIENTE','CONFIRMADA','ENTREGADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  `observaciones` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_cliente`, `fecha_reserva`, `fecha_limite_retiro`, `estado`, `observaciones`) VALUES
(1, 2, '2026-11-10 09:15:00', '2026-11-15', 'PENDIENTE', 'Retiro durante el fin de semana'),
(2, 4, '2026-11-11 11:30:00', '2026-11-16', 'CONFIRMADA', 'Reserva confirmada por telefono'),
(3, 5, '2026-11-12 14:00:00', '2026-11-17', 'CANCELADA', '{\"registrado_por\":\"No disponible\",\"observaciones\":\"Retiro en tienda\",\"inventario_descontado\":false}'),
(4, 2, '2026-11-22 19:40:18', '2026-11-24', 'ENTREGADA', '{\"registrado_por\":\"Alexis Benjamin Cisneros Luciano\",\"observaciones\":null}'),
(5, 1, '2026-11-22 23:06:04', '2026-11-03', 'ENTREGADA', '{\"registrado_por\":\"Alexis Benjamin Cisneros Luciano\",\"observaciones\":null,\"inventario_descontado\":true}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(40) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso a la administracion completa del sistema'),
(2, 'Empleado', 'Acceso a operaciones de ventas y consulta'),
(3, 'Cliente', 'Compras en la tienda');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre_completo` varchar(120) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `correo` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_completo`, `nombre_usuario`, `correo`, `password_hash`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'Alexis Benjamin Cisneros Luciano', 'admin.acisneros', 'alexis.cisneros@papelito.test', '$2y$10$10B6InHHJdxUu.QQUjiipuATaksxuPuWmm7Z6YkQbH8Ocwbd6B.uS', 1, '2026-10-01 08:00:00'),
(2, 1, 'Carlos Mendez Rivera', 'admin.cmendez', 'carlos.mendez@papelito.test', '$2y$10$HASH_DEMO_NO_VALIDO_PARA_LOGIN', 1, '2026-10-02 08:30:00'),
(3, 2, 'Daniela Flores Castro', 'dflores', 'daniela.flores@papelito.test', '$2y$10$HASH_DEMO_NO_VALIDO_PARA_LOGIN', 1, '2026-10-03 09:00:00'),
(4, 3, 'Cliente', 'Cliente', 'cliente@gmail.com', '$2y$10$ptK8t6meOyYxCPAFQ2gxV.9vPGn6Wq0/9kBBWg6qj3p5cBv.B.ApS', 1, '2026-11-22 00:57:07'),
(5, 3, 'Ingrid Guyedes', 'admin.ingrid', 'ingridguyedes@gmail.com', '$2y$10$bQuUEnnSho55JFOD8kHXV.jZV.5m0CsDYdbjrqoHsF7CsIsz18Ere', 1, '2026-11-23 22:24:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` bigint(20) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha_venta` datetime NOT NULL DEFAULT current_timestamp(),
  `observaciones` varchar(500) DEFAULT NULL,
  `estado` enum('COMPLETADA','ANULADA') NOT NULL DEFAULT 'COMPLETADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_cliente`, `id_usuario`, `fecha_venta`, `observaciones`, `estado`) VALUES
(1, 1, 3, '2026-11-05 10:20:00', 'Venta de utiles escolares', 'COMPLETADA'),
(2, 3, 1, '2026-11-08 16:10:00', 'Venta de articulos de papeleria', 'COMPLETADA'),
(3, 6, 1, '2026-11-21 18:10:42', NULL, 'ANULADA'),
(4, NULL, 1, '2026-11-21 22:42:24', NULL, 'COMPLETADA'),
(5, 1, 1, '2026-11-22 23:09:04', 'Venta generada por entrega de reservación #5', 'COMPLETADA');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `idx_clientes_nombre` (`nombre_completo`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `fk_compras_usuarios` (`id_usuario`),
  ADD KEY `idx_compras_fecha` (`fecha_compra`),
  ADD KEY `idx_compras_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`id_detalle_compra`),
  ADD KEY `idx_detalle_compra_compra` (`id_compra`),
  ADD KEY `idx_detalle_compra_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_reserva`
--
ALTER TABLE `detalle_reserva`
  ADD PRIMARY KEY (`id_detalle_reserva`),
  ADD KEY `idx_detalle_reserva_reserva` (`id_reserva`),
  ADD KEY `idx_detalle_reserva_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id_detalle_venta`),
  ADD KEY `idx_detalle_venta_venta` (`id_venta`),
  ADD KEY `idx_detalle_venta_producto` (`id_producto`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_movimientos_usuario` (`id_usuario`),
  ADD KEY `idx_movimientos_producto_fecha` (`id_producto`,`fecha_movimiento`),
  ADD KEY `idx_movimientos_fecha` (`fecha_movimiento`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_productos_categorias` (`id_categoria`),
  ADD KEY `idx_productos_nombre` (`nombre`),
  ADD KEY `idx_productos_activo` (`activo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD KEY `idx_proveedores_nombre` (`nombre_empresa`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `fk_reservas_clientes` (`id_cliente`),
  ADD KEY `idx_reservas_estado` (`estado`),
  ADD KEY `idx_reservas_fecha` (`fecha_reserva`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `fk_usuarios_roles` (`id_rol`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `fk_ventas_usuarios` (`id_usuario`),
  ADD KEY `idx_ventas_fecha` (`fecha_venta`),
  ADD KEY `idx_ventas_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id_compra` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `id_detalle_compra` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_reserva`
--
ALTER TABLE `detalle_reserva`
  MODIFY `id_detalle_reserva` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id_detalle_venta` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id_movimiento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `fk_detalle_compra_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_compra_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_reserva`
--
ALTER TABLE `detalle_reserva`
  ADD CONSTRAINT `fk_detalle_reserva_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_reserva_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_detalle_venta_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_venta_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reservas_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
