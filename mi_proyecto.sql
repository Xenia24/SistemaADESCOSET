-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-03-2025 a las 04:09:42
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
-- Base de datos: `mi_proyecto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `tipo_usuario` enum('admin','empleado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `tipo_usuario`) VALUES
(1, 'Juan Admin', 'admin@ejemplo.com', '0192023a7bbd73250516f069df18b500', 'admin'),
(2, 'Pedro Empleado', 'empleado@ejemplo.com', 'da0f7659b41b24a826cc1673ac948843', 'empleado');


--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; 

CREATE TABLE agregarderechohabiente (
    codigo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    identificacion VARCHAR(20) NOT NULL UNIQUE,
    direccion VARCHAR(150) NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    tipo_derechohabiente ENUM('natural', 'juridica') NOT NULL
);

CREATE TABLE recibos (
    numero_recibo INT AUTO_INCREMENT PRIMARY KEY,
    fecha_emision DATE,
    fecha_vencimiento DATE,
    propietario VARCHAR(100),
    direccion VARCHAR(200),
    fecha_lectura DATE,
    numero_suministro VARCHAR(50),
    numero_medidor VARCHAR(50),
    metros_cubicos DECIMAL(10,2),
    lectura_anterior DECIMAL(10,2),
    lectura_actual DECIMAL(10,2),
    meses_pendiente INT,
    multas DECIMAL(10,2),
    total DECIMAL(10,2),
    estado_pago ENUM('Pagado', 'No pagado', 'En mora', 'Pagado fuera de fecha') DEFAULT 'No pagado'
);

CREATE TABLE usuariosag (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    numero_dui VARCHAR(20) NOT NULL UNIQUE,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,  
    estado ENUM('activo', 'inactivo') NOT NULL,
    tipo_usuario ENUM('Administrador', 'General') NOT NULL
);

CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(100) NOT NULL,
    cantidad INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100) NOT NULL
);
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  `fecha` DATETIME NULL,
  `descripcion` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    cantidad_comprada INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);


-- -- Tabla productos
-- CREATE TABLE productos (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     nombre_producto VARCHAR(100),
--     cantidad INT,
--     precio DECIMAL(10, 2),
--     categoria VARCHAR(100)
-- ) ENGINE=InnoDB;

-- -- Tabla usuarios
-- CREATE TABLE usuarios (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     nombre_completo VARCHAR(100),
--     correo VARCHAR(100),
--     telefono VARCHAR(20),
--     numero_dui VARCHAR(20),
--     nombre_usuario VARCHAR(50),
--     estado VARCHAR(20),
--     tipo_usuario VARCHAR(20)
-- ) ENGINE=InnoDB;


-- CREATE TABLE historial_retiros (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     producto_id INT,
--     usuario_id INT,
--     cantidad INT,
--     fecha DATETIME,
--     FOREIGN KEY (id) REFERENCES productos(id),
--     FOREIGN KEY (id) REFERENCES usuarios(id)
-- );



