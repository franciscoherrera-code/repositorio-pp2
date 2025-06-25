-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2025 a las 23:26:30
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
-- Base de datos: `packtracker_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id` int(11) NOT NULL,
  `codigo_seguimiento` varchar(20) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado` varchar(50) DEFAULT 'En preparación',
  `nombre_destinatario` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `prioridad` enum('Normal','Urgente') DEFAULT 'Normal',
  `origen` varchar(255) DEFAULT NULL,
  `lat_origen` decimal(10,8) DEFAULT NULL,
  `lon_origen` decimal(11,8) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `envios`
--

INSERT INTO `envios` (`id`, `codigo_seguimiento`, `usuario_id`, `destino`, `peso`, `fecha_envio`, `estado`, `nombre_destinatario`, `telefono`, `prioridad`, `origen`, `lat_origen`, `lon_origen`, `latitud`, `longitud`) VALUES
(47, 'PKG-2025-00047', 1, 'Rosario, SF, Argentina', 23.00, '2025-06-24 15:07:08', 'En preparación', 'nati', '3333', 'Normal', NULL, NULL, NULL, NULL, NULL),
(48, 'PKG-2025-00048', 1, 'San Juan Province, Argentina', 10.00, '2025-06-24 15:26:16', 'En preparación', 'ul1', '1', 'Normal', NULL, NULL, NULL, NULL, NULL),
(49, 'PKG-2025-00049', 1, 'Rosario, SF, Argentina', 50.00, '2025-06-24 15:40:09', 'En preparación', 'vale', '123', 'Normal', 'San Nicolás de los Arroyos, BA, Argentina', -33.33578000, -60.22523000, NULL, NULL),
(50, 'PKG-2025-00050', 1, 'Rosario, SF, Argentina', 54.00, '2025-06-24 16:18:49', 'En preparación', 'juan', '12345', 'Normal', 'San Nicolás de los Arroyos, BA, Argentina', -33.33425000, -60.21080000, NULL, NULL),
(51, 'PKG-2025-00051', 1, 'Rosario, SF, Argentina', 12.00, '2025-06-24 17:09:52', 'En preparación', 'olid', '2', 'Normal', 'San Nicolás de los Arroyos, BA, Argentina', -33.33578000, -60.22523000, -32.94228400, -60.66145300),
(52, 'PKG-2025-00052', 1, 'Rosario, SF, Argentina', 32.00, '2025-06-24 17:47:10', 'En preparación', 'paquer', '2', 'Normal', 'San Nicolás de los Arroyos, BA, Argentina', -33.33578000, -60.22523000, -32.94228400, -60.66145300),
(53, 'PKG-2025-00053', 1, 'Córdoba, Argentina', 12.00, '2025-06-24 17:48:42', 'En preparación', 'u', '1', 'Urgente', 'San Juan Province, Argentina', -30.89483700, -68.86546100, -31.42857900, -64.18488400),
(54, 'PKG-2025-00054', 1, 'San Juan Province, Argentina', 10.00, '2025-06-25 12:43:29', 'Cancelado', 'martin', '4444444444444', 'Urgente', 'San Nicolas, Buenos Aires, CF, Argentina', -34.60368600, -58.38051400, -32.94228400, -60.66145300);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `envio_id` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `fecha_actualizacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`id`, `envio_id`, `estado`, `fecha_actualizacion`) VALUES
(40, 47, 'En preparación', '2025-06-24 15:07:08'),
(41, 48, 'En preparación', '2025-06-24 15:26:16'),
(42, 49, 'En preparación', '2025-06-24 15:40:09'),
(43, 50, 'En preparación', '2025-06-24 16:18:49'),
(44, 51, 'En preparación', '2025-06-24 17:09:52'),
(45, 52, 'En preparación', '2025-06-24 17:47:10'),
(46, 53, 'En preparación', '2025-06-24 17:48:42'),
(47, 54, 'En preparación', '2025-06-25 12:43:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `contrasena_hash`, `fecha_registro`) VALUES
(1, 'user1', '$2y$10$VjsIkkdr/ihm6nUZhlbJJ.kXf/DoHumktmDesrQ856scfG1SyfEUu', '2025-06-22 16:56:44');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `envios`
--
ALTER TABLE `envios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `envio_id` (`envio_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `envios`
--
ALTER TABLE `envios`
  ADD CONSTRAINT `envios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`envio_id`) REFERENCES `envios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
