-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-05-2025 a las 19:52:16
-- Versión del servidor: 10.11.10-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `online`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ap_clientes`
--

CREATE TABLE `ap_clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `version` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ap_clientes`
--

INSERT INTO `ap_clientes` (`id`, `nombre`, `ip`, `version`) VALUES
(1, 'Sectorial', '192.16.90.2', 'Stable');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ap_emisor`
--

CREATE TABLE `ap_emisor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `version` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ap_receptor`
--

CREATE TABLE `ap_receptor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `version` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `size` int(11) NOT NULL,
  `ruta` text NOT NULL,
  `tabla` varchar(100) NOT NULL,
  `object_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `nombre`, `tipo`, `size`, `ruta`, `tabla`, `object_id`) VALUES
(1, 'b0diTGxZdzNyQkNyM1FiejJUaHBOQT09.pdf', 'application/pdf', 19247, 'Uploads/ap_clientes/1/b0diTGxZdzNyQkNyM1FiejJUaHBOQT09.pdf', 'ap_clientes', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backups`
--

CREATE TABLE `backups` (
  `id` bigint(20) NOT NULL,
  `archive` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `size` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bills`
--

CREATE TABLE `bills` (
  `id` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `voucherid` bigint(20) NOT NULL,
  `serieid` bigint(20) NOT NULL,
  `internal_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `correlative` bigint(20) NOT NULL,
  `date_issue` date NOT NULL,
  `expiration_date` date NOT NULL,
  `billed_month` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `remaining_amount` decimal(12,2) NOT NULL,
  `type` bigint(20) NOT NULL,
  `sales_method` bigint(20) NOT NULL,
  `observation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `promise_enabled` tinyint(4) NOT NULL,
  `promise_date` date DEFAULT NULL,
  `promise_set_date` date DEFAULT NULL,
  `promise_comment` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 2,
  `compromise_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `bills`
--

INSERT INTO `bills` (`id`, `userid`, `clientid`, `voucherid`, `serieid`, `internal_code`, `correlative`, `date_issue`, `expiration_date`, `billed_month`, `subtotal`, `discount`, `total`, `amount_paid`, `remaining_amount`, `type`, `sales_method`, `observation`, `promise_enabled`, `promise_date`, `promise_set_date`, `promise_comment`, `state`, `compromise_date`) VALUES
(1, 1, 1, 1, 1, 'V00001', 1, '2025-04-08', '2025-05-01', '2025-04-01', 35.00, 0.00, 35.00, 35.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(2, 1, 1, 1, 1, 'V00002', 2, '2025-04-08', '2025-04-08', '0000-00-00', 15.00, 0.00, 15.00, 15.00, 0.00, 1, 1, '', 0, NULL, NULL, '', 1, NULL),
(3, 1, 1, 1, 1, 'V00003', 3, '2025-04-08', '2025-04-08', '0000-00-00', 20.00, 0.00, 20.00, 20.00, 0.00, 1, 2, '', 0, NULL, NULL, '', 1, NULL),
(4, 1, 1, 1, 1, 'V00004', 4, '2025-04-08', '2025-06-01', '2025-05-01', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(5, 1, 1, 1, 1, 'V00005', 5, '2025-04-08', '2025-07-01', '2025-06-01', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(6, 1, 1, 1, 1, 'V00006', 6, '2025-04-08', '2025-08-01', '2025-07-01', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(7, 1, 1, 1, 1, 'V00007', 7, '2025-04-08', '2025-09-01', '2025-08-01', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(8, 1, 1, 1, 1, 'V00008', 8, '2025-04-08', '2025-10-01', '2025-09-01', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, '2025-04-11', '2025-04-09', 'nuevapromesa', 1, NULL),
(9, 1, 1, 1, 1, 'V00009', 9, '2025-10-01', '2025-11-01', '2025-10-01', 50.00, 0.00, 50.00, 0.00, 50.00, 2, 2, '', 0, NULL, NULL, '', 2, NULL),
(10, 1, 1, 1, 1, 'V00010', 10, '2025-11-01', '2025-12-01', '2025-11-01', 50.00, 0.00, 50.00, 0.00, 50.00, 2, 2, '', 0, NULL, NULL, '', 2, NULL),
(11, 1, 2, 1, 1, 'V00011', 11, '2025-04-20', '2025-04-20', '0000-00-00', 20.00, 0.00, 20.00, 20.00, 0.00, 1, 2, '', 0, '2025-04-21', '2025-04-20', 'Okkkj', 1, NULL),
(12, 1, 2, 1, 1, 'V00012', 12, '2025-04-20', '2025-05-21', '2025-04-21', 15.00, 5.00, 10.00, 10.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(13, 1, 2, 1, 1, 'V00013', 13, '2025-05-21', '2025-06-21', '2025-05-21', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(14, 1, 2, 1, 1, 'V00014', 14, '2025-04-20', '2025-07-21', '2025-06-21', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(15, 1, 2, 1, 1, 'V00015', 15, '2025-04-20', '2025-08-21', '2025-07-21', 50.00, 0.00, 50.00, 0.00, 50.00, 2, 2, '', 0, NULL, NULL, '', 2, NULL),
(16, 1, 3, 1, 1, 'V00016', 16, '2025-04-20', '2025-05-01', '2025-04-01', 15.00, 0.00, 15.00, 15.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(17, 1, 3, 1, 1, 'V00017', 17, '2025-04-20', '2025-06-01', '2025-05-01', 50.00, 0.00, 50.00, 50.00, 0.00, 2, 2, '', 0, NULL, NULL, '', 1, NULL),
(18, 1, 3, 1, 1, 'V00018', 18, '2025-04-20', '2025-07-01', '2025-06-01', 50.00, 0.00, 50.00, 10.00, 40.00, 2, 2, '', 0, NULL, NULL, '', 2, NULL),
(19, 1, 4, 1, 1, 'V00019', 19, '2025-05-04', '2025-06-05', '2025-05-05', 42.00, 0.00, 42.00, 0.00, 42.00, 2, 2, '', 0, NULL, NULL, '', 2, NULL),
(20, 1, 4, 1, 1, 'V00020', 20, '2025-05-04', '2025-07-05', '2025-06-05', 50.00, 0.00, 50.00, 0.00, 50.00, 2, 2, '', 0, NULL, NULL, '', 2, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `business`
--

CREATE TABLE `business` (
  `id` bigint(20) NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `ruc` char(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `business_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `tradename` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `slogan` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile_refrence` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `password` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `server_host` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `port` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `department` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `province` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `district` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ubigeo` char(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `footer_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `currencyid` bigint(20) NOT NULL,
  `print_format` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `logotyope` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `logo_login` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `logo_email` varchar(1000) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `favicon` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `country_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `google_apikey` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `reniec_apikey` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `background` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `whatsapp_api` varchar(100) DEFAULT NULL,
  `whatsapp_key` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `business`
--

INSERT INTO `business` (`id`, `documentid`, `ruc`, `business_name`, `tradename`, `slogan`, `mobile`, `mobile_refrence`, `email`, `password`, `server_host`, `port`, `address`, `department`, `province`, `district`, `ubigeo`, `footer_text`, `currencyid`, `print_format`, `logotyope`, `logo_login`, `logo_email`, `favicon`, `country_code`, `google_apikey`, `reniec_apikey`, `background`, `whatsapp_api`, `whatsapp_key`) VALUES
(4, 1, '132369076', 'NETWORK SRL', 'SUPORTEC NETWORK SRL', '', '928237596', '8297989774', '', '', '', '465', 'BONAO REP. DOM.', '', 'BONAO', 'MONSENOR NOUEL', '42000', '', 1, 'ticket', 'logo_0bbfd1974a6307327c659379b52671e6.png', 'login_0058d000c18f70d604f64b51ec17ec4a.png', '', 'favicon_3f7114e86862f23a1f4b9b1ed428921c.png', '51', 'AIzaSyCqBa0JUtU2HSOYdpiKinJvJ4ZtjCEyjBw', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIyNTAiLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJjb25zdWx0b3IifQ.cQ6ZbYWbRkIK1-wmbU9rye-p12Wo7eRhQqS-cJlWJpc', 'bg-8.jpeg', 'https://api.conectate-ya.net.pe', '75116544');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `business_wsp`
--

CREATE TABLE `business_wsp` (
  `id` varchar(100) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `contenido` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `business_wsp`
--

INSERT INTO `business_wsp` (`id`, `titulo`, `contenido`) VALUES
('PAGO_MASSIVE', 'Confirmación de Registro de Pago', 'Estimado(a) *{cliente}*,\n\nNos complace informarle que hemos registrado su pago de *{payment_total}*, correspondiente al recibo de *{payment_months}*.\n\nAgradecemos su puntualidad y le agradecemos por continuar confiando en nuestros servicios.\n\nPara su conveniencia, puede descargar su recibo en el siguiente enlace:\n{list_payments}\n\nQuedamos a su disposición para cualquier consulta adicional.\n\nAtentamente,\n*{business_name}*'),
('SUPPORT_TECNICO', 'SOPORTE TECNICO', 'Estimado(a) *{cliente}*,\n\nLe informamos que se ha generado el ticket Nº *{ticket_num}* para atender su solicitud. Un técnico se estará comunicando con usted a la brevedad para brindarle asistencia y solucionar el inconveniente con su servicio.\n\nQuedamos a su disposición para cualquier consulta adicional.\n\nAtentamente,\n*{business_name}*'),
('PAYMENT_PENDING', 'PAGO PENDIENTE', 'Estimado cliente *{cliente}*, \nle recordamos que tiene una deuda *PENDIENTE* por el monto \n*TOTAL* de {debt_amount}, correspondiente a los siguientes *MESES:*\n\n{debt_list}\n\nGracias por formar parte de nuestra familia {business_name}, esperamos su pronto pago.\n\nAtte. {business_name}'),
('PAYMENT_CONFIRMED', 'CONFIRMACIÓN DE PAGO', 'Estimado(a) {cliente},\n\nLe informamos que se ha registrado su pago de {payment_total} correspondiente al recibo número {payment_num} de {payment_months}, quedando un saldo pendiente de {payment_pending}.\n\nPara su conveniencia, puede revisar los detalles de su pago en el siguiente enlace:\n{payment_links}\n\nAgradecemos su pago y la confianza depositada en nuestros servicios.\n\nAtentamente,\n{business_name}'),
('CLIENT_ACTIVED', 'Restablecimiento de su Servicio', 'Estimado(a) *{cliente}*,\n\nLe informamos que su servicio ha sido restaurado con éxito. Ahora puede disfrutar nuevamente de su conexión sin inconvenientes.\n\nSi requiere asistencia adicional, no dude en contactarnos.\n\nGracias por su confianza.\n\nAtentamente,\n*{business_name}*'),
('CLIENT_SUSPENDED', 'Suspensión de Servicio por Falta de Pago', 'Estimado(a) *{cliente}*,\n\nLe informamos que su servicio ha sido *suspendido* debido a la falta de pago. Actualmente, mantiene un saldo pendiente de {debt_total_list}, correspondiente a {debt_total_month_count}.\n\nPara regularizar su situación y restablecer el servicio, le solicitamos realizar el pago a la brevedad posible. Si ya ha efectuado el pago, le agradeceríamos que nos envíe el comprobante para su verificación.\n\nSi necesita más información o asistencia, no dude en comunicarse con nosotros a través de nuestros canales de atención.\n\nAtentamente,\n*{business_name}*'),
('CLIENT_CANCELLED', 'Confirmación de Cancelación de Servicio', 'Estimado(a) *{cliente}*,\n\nLe informamos que su servicio ha sido cancelado.\n\nLamentamos su partida y agradecemos la confianza que nos brindó durante el tiempo que estuvo con nosotros. Si en el futuro decide regresar, estaremos encantados de recibirlo nuevamente.\n\nQuedamos a su disposición para cualquier consulta.\n\nAtentamente,\n*{business_name}*');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_nap`
--

CREATE TABLE `caja_nap` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `longitud` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitud` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `puertos` int(11) NOT NULL,
  `detalles` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(100) NOT NULL DEFAULT 'nap',
  `color_tubo` varchar(100) DEFAULT NULL,
  `color_hilo` varchar(100) DEFAULT NULL,
  `zonaId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `caja_nap`
--

INSERT INTO `caja_nap` (`id`, `nombre`, `longitud`, `latitud`, `puertos`, `detalles`, `ubicacion`, `tipo`, `color_tubo`, `color_hilo`, `zonaId`) VALUES
(1, 'MUFA', '-76.79842705', '-9.853997513927462', 0, 'nada', 'nose ok', 'mufa', '#f00505', '#05d5ff', 1),
(2, 'otro', '-78.545253221875', '-9.076466599972003', 0, 'dsds', 'saas', 'mufa', '#1486f0', '#76ff05', 2),
(3, 'caja', '-78.545253221875', '-9.076466599972003', 8, 'detallrs ok', 'aaaa', 'nap', '#f09999', '#ff5900', 3),
(4, 'caja cedro', '-77.0087831', '-11.853836', 12, 'ssa', 'los cedros', 'nap', '#e81111', '#115bee', 2),
(5, 'Prueba', '-78.54748340532923', '-9.072336119128117', 20, 'Ok', 'JR prueba ', 'nap', '#ff0000', '#00ffff', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_nap_clientes`
--

CREATE TABLE `caja_nap_clientes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nap_id` int(11) NOT NULL,
  `puerto` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `caja_nap_clientes`
--

INSERT INTO `caja_nap_clientes` (`id`, `cliente_id`, `nap_id`, `puerto`) VALUES
(1, 0, 3, '1'),
(2, 0, 3, '2'),
(3, 0, 3, '3'),
(4, 0, 3, '4'),
(5, 0, 3, '5'),
(6, 0, 3, '6'),
(7, 0, 3, '7'),
(8, 0, 3, '8'),
(9, 0, 4, '1'),
(10, 0, 4, '2'),
(11, 0, 4, '3'),
(12, 0, 4, '4'),
(13, 0, 4, '5'),
(14, 0, 4, '6'),
(15, 0, 4, '7'),
(16, 0, 4, '8'),
(17, 0, 4, '9'),
(18, 0, 4, '10'),
(19, 0, 4, '11'),
(20, 0, 4, '12'),
(21, 0, 5, '1'),
(22, 0, 5, '2'),
(23, 0, 5, '3'),
(24, 0, 5, '4'),
(25, 0, 5, '5'),
(26, 0, 5, '6'),
(27, 0, 5, '7'),
(28, 0, 5, '8'),
(29, 0, 5, '9'),
(30, 0, 5, '10'),
(31, 0, 5, '11'),
(32, 0, 5, '12'),
(33, 0, 5, '13'),
(34, 0, 5, '14'),
(35, 0, 5, '15'),
(36, 0, 5, '16'),
(37, 0, 5, '17'),
(38, 0, 5, '18'),
(39, 0, 5, '19'),
(40, 0, 5, '20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) NOT NULL,
  `names` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `surnames` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `document` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile_optional` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `reference` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `note` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `latitud` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `longitud` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1,
  `net_router` int(11) NOT NULL,
  `net_name` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `net_password` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `net_localaddress` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `net_ip` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `nap_cliente_id` int(11) DEFAULT NULL,
  `ap_cliente_id` int(11) DEFAULT NULL,
  `zonaid` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `clients`
--

INSERT INTO `clients` (`id`, `names`, `surnames`, `documentid`, `document`, `mobile`, `mobile_optional`, `email`, `address`, `reference`, `note`, `latitud`, `longitud`, `state`, `net_router`, `net_name`, `net_password`, `net_localaddress`, `net_ip`, `nap_cliente_id`, `ap_cliente_id`, `zonaid`) VALUES
(1, 'WALTER JUNIOR', 'RENGIFO ESPINOZA', 2, '92823759', '999220735', '123456789', 'ejemplo@gmail.com', 'DIRECCION', 'REFERENCIA', 'LA NOTA', '-9.303541', '-76.403265', 1, 4, 'WALTER-JUNIOR-RENGIFO-ESPINOZA', 'pOjxQpT/vp7ZcrngOdsd5nZLckIyL2JjQURKZ0U0dVFSQkp1VFRKRWR6bnlmQkhwczF0Q2ZJVGJwMnc9', '18.19.20.1', '18.19.20.3', 0, 1, 2),
(2, 'PRUEBA', 'PRUEBA P', 2, '45454545', '969696969', '', '', 'SAN JUAN', 'PLAZA', 'OK', '15.12653009035351', '-91.81569952620707', 1, 5, 'PRUEBA-PRUEBA-P', 'd6xvGeynvQRSKIN+l7MMC3djajBJTDI4TkRVbktNR3RzSWwrT0Rob3V1ZStqbzRlWkVUNGtSZ1pWUjg9', '192.168.10.1', '192.168.10.2', 1, 0, 1),
(3, 'WALTER JAVIER', 'RENGIFO ESPINOZA', 2, '75116541', '999220735', '972766166', 'ejemplo@gmail.com', 'DIRECCION DEL CLIENTE', 'REFRENCIA DEL DOMICILIO', 'MI PRIMO', '-11.8549809', '-77.0182917', 1, 5, 'WALTER-JAVIER-RENGIFO-ESPINOZA', 'OFv6Mu2iauO8y4geQad9ylQvZmM4aVcra3NxZmRzai9yeGtwT2NFd3gvYlJ3dW1CZXV0UU9YdThySjQ9', '192.168.10.1', '192.168.10.30', 5, 0, 0),
(4, 'JUAN', 'PEREZ SOTO', 3, '722794222', '958956952', '', 'juan@gmail.com', 'JR. JUAN', '', 'SERVICIO PRO2025', '-12.0356864', '-76.9622016', 1, 5, 'JUAN-PEREZ-SOTO', '2WK686h8hrAhMyNx+L3/fXpOWXR3cS9xWW9leHY0T3d1K2F2VEE9PQ==', '', '', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contracts`
--

CREATE TABLE `contracts` (
  `id` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `internal_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `payday` bigint(20) NOT NULL,
  `create_invoice` bigint(20) NOT NULL,
  `days_grace` bigint(20) NOT NULL,
  `discount` bigint(20) NOT NULL,
  `discount_price` decimal(12,2) NOT NULL,
  `months_discount` bigint(20) NOT NULL,
  `remaining_discount` bigint(20) NOT NULL,
  `contract_date` datetime NOT NULL,
  `suspension_date` date NOT NULL,
  `finish_date` date NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `contracts`
--

INSERT INTO `contracts` (`id`, `userid`, `clientid`, `internal_code`, `payday`, `create_invoice`, `days_grace`, `discount`, `discount_price`, `months_discount`, `remaining_discount`, `contract_date`, `suspension_date`, `finish_date`, `state`) VALUES
(1, 1, 1, 'CT00001', 1, 0, 8, 0, 0.00, 0, 0, '2025-04-08 23:06:08', '0000-00-00', '0000-00-00', 2),
(2, 1, 2, 'CT00002', 21, 0, 0, 0, 0.00, 0, 0, '2025-04-20 06:53:22', '0000-00-00', '0000-00-00', 2),
(3, 1, 3, 'CT00003', 1, 0, 5, 0, 0.00, 0, 0, '2025-04-20 13:29:14', '2025-04-20', '0000-00-00', 3),
(4, 1, 4, 'CT00004', 5, 0, 5, 0, 0.00, 0, 0, '2025-05-04 12:24:11', '0000-00-00', '0000-00-00', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cronjobs`
--

CREATE TABLE `cronjobs` (
  `id` int(11) NOT NULL,
  `description` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `frequency` int(11) NOT NULL,
  `parm` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parmdesc` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parmx` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastrun` int(11) NOT NULL,
  `lastresult` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `cronjobs`
--

INSERT INTO `cronjobs` (`id`, `description`, `frequency`, `parm`, `parmdesc`, `parmx`, `lastrun`, `lastresult`, `code`, `status`) VALUES
(1, 'Notificación de deuda, mismo dia de pago (API-WhatsApp)', 1440, '', '', '', 1746378425, 'Ejecuci&oacute;n autom&aacute;tica exitosa', 'IN001', 1),
(2, 'Notificación de deudas a todos los clientes (API-WhatsApp)\r\n', 43200, '', '', '', 1737049735, 'Ejecuci&oacute;n autom&aacute;tica exitosa\r\n', 'IN002', 1),
(3, 'Corte de servicio de las facturas vencidas (respetando dia de gracia y promesas)', 1440, '', '', '', 1743718115, 'Prueba exitosa', 'IN003', 0),
(4, 'Envio masivo de deudas por correo electronico\r\n', 21600, '', '', '', 1743718062, 'Prueba exitosa', 'CI001', 0),
(5, 'Backup base de datos', 43200, '', 'Días antes de expirar', '%x% días después', 1745068913, 'El backup ya existe!!!', 'IN004', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cronjobs_core`
--

CREATE TABLE `cronjobs_core` (
  `id` int(11) NOT NULL,
  `lastrun` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `cronjobs_core`
--

INSERT INTO `cronjobs_core` (`id`, `lastrun`) VALUES
(1, 1742076842);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cronjobs_exceptions`
--

CREATE TABLE `cronjobs_exceptions` (
  `id` int(11) NOT NULL,
  `cronjobid` int(11) NOT NULL,
  `clientid` int(11) NOT NULL,
  `param_name` varchar(64) NOT NULL,
  `param_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cronjobs_history`
--

CREATE TABLE `cronjobs_history` (
  `id` int(11) NOT NULL,
  `cronjobid` int(11) NOT NULL,
  `result` varchar(128) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `cronjobs_history`
--

INSERT INTO `cronjobs_history` (`id`, `cronjobid`, `result`, `date`) VALUES
(1, 5, 'Backup generado!!!', 1745068891),
(2, 5, 'El backup ya existe!!!', 1745068913),
(3, 1, 'Ejecuci&oacute;n autom&aacute;tica exitosa', 1746378425);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `currency`
--

CREATE TABLE `currency` (
  `id` bigint(20) NOT NULL,
  `currency_iso` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `language` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `currency_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `money` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `money_plural` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `symbol` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `currency`
--

INSERT INTO `currency` (`id`, `currency_iso`, `language`, `currency_name`, `money`, `money_plural`, `symbol`, `registration_date`, `state`) VALUES
(1, 'PES', 'ES', 'PESOS COLOMBIANOS', 'PESO', 'PESOS', '$', '2022-07-07 19:57:37', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departures`
--

CREATE TABLE `departures` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `productid` bigint(20) NOT NULL,
  `departure_date` datetime NOT NULL,
  `description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `quantity_departures` bigint(20) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detail_bills`
--

CREATE TABLE `detail_bills` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `type` bigint(20) NOT NULL,
  `serproid` bigint(20) NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `detail_bills`
--

INSERT INTO `detail_bills` (`id`, `billid`, `type`, `serproid`, `description`, `quantity`, `price`, `total`) VALUES
(1, 1, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE ABRIL PRORRATEADO', 1, 35.00, 35.00),
(2, 2, 1, 0, 'TARJETA DE RED', 1, 15.00, 15.00),
(3, 3, 1, 0, 'PALE BOND', 1, 20.00, 20.00),
(4, 4, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE MAYO', 1, 50.00, 50.00),
(5, 5, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE JUNIO', 1, 50.00, 50.00),
(6, 6, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE JULIO', 1, 50.00, 50.00),
(7, 7, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE AGOSTO', 1, 50.00, 50.00),
(10, 8, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE SEPTIEMBRE', 1, 50.00, 50.00),
(11, 9, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE OCTUBRE', 1, 50.00, 50.00),
(12, 10, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE NOVIEMBRE', 1, 50.00, 50.00),
(13, 11, 1, 0, 'SERVICIO DE INSTALACIÓN', 1, 20.00, 20.00),
(14, 12, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE ABRIL PRORRATEADO', 1, 15.00, 15.00),
(15, 13, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE MAYO', 1, 50.00, 50.00),
(16, 14, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE JUNIO', 1, 50.00, 50.00),
(17, 15, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE JULIO', 1, 50.00, 50.00),
(18, 16, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE ABRIL PRORRATEADO', 1, 15.00, 15.00),
(19, 17, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE MAYO', 1, 50.00, 50.00),
(20, 18, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE JUNIO', 1, 50.00, 50.00),
(21, 19, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE MAYO PRORRATEADO', 1, 42.00, 42.00),
(22, 20, 2, 1, 'SERVICIO DE INTERNET HOGAR, MES DE JUNIO', 1, 50.00, 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detail_contracts`
--

CREATE TABLE `detail_contracts` (
  `id` bigint(20) NOT NULL,
  `contractid` bigint(20) NOT NULL,
  `serviceid` bigint(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `detail_contracts`
--

INSERT INTO `detail_contracts` (`id`, `contractid`, `serviceid`, `price`, `registration_date`, `state`) VALUES
(1, 1, 1, 50.00, '2025-04-08 23:06:08', 1),
(2, 2, 1, 50.00, '2025-04-20 06:53:22', 1),
(3, 3, 1, 50.00, '2025-04-20 13:29:14', 2),
(4, 4, 1, 50.00, '2025-05-04 12:24:11', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detail_facility`
--

CREATE TABLE `detail_facility` (
  `id` bigint(20) NOT NULL,
  `facilityid` bigint(20) NOT NULL,
  `technicalid` bigint(20) NOT NULL,
  `opening_date` datetime NOT NULL,
  `closing_date` datetime NOT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL,
  `red_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `detail_facility`
--

INSERT INTO `detail_facility` (`id`, `facilityid`, `technicalid`, `opening_date`, `closing_date`, `comment`, `state`, `red_type`) VALUES
(1, 1, 1, '2025-04-08 23:06:35', '2025-04-08 23:06:55', 'CLIENTE NUEVO', 1, '2'),
(2, 2, 1, '2025-04-20 07:05:46', '2025-04-20 07:06:27', '', 1, ''),
(3, 3, 1, '2025-04-20 13:29:36', '2025-04-20 13:29:57', 'CLIENTE NUEVO', 1, '2'),
(4, 4, 1, '2025-05-04 12:24:33', '2025-05-04 12:25:03', 'SE REALIZO LA INSTALACIUON', 1, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_type`
--

CREATE TABLE `document_type` (
  `id` bigint(20) NOT NULL,
  `document` varchar(100) NOT NULL,
  `maxlength` int(2) NOT NULL DEFAULT 8,
  `is_required` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `document_type`
--

INSERT INTO `document_type` (`id`, `document`, `maxlength`, `is_required`) VALUES
(1, 'SIN DOCUMENTO', 8, 0),
(2, 'INE', 8, 1),
(3, 'SAT', 11, 1),
(4, 'CARNET DE EXTRANJERIA', 20, 0),
(5, 'PASAPORTE', 20, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emails`
--

CREATE TABLE `emails` (
  `id` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `affair` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `sender` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `files` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `type_file` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `template_email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facility`
--

CREATE TABLE `facility` (
  `id` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `technical` bigint(20) NOT NULL,
  `attention_date` datetime NOT NULL,
  `opening_date` datetime NOT NULL,
  `closing_date` datetime NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `detail` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `facility`
--

INSERT INTO `facility` (`id`, `clientid`, `userid`, `technical`, `attention_date`, `opening_date`, `closing_date`, `cost`, `detail`, `registration_date`, `state`) VALUES
(1, 1, 1, 1, '2025-04-08 23:04:00', '2025-04-08 23:06:35', '2025-04-08 23:06:55', 0.00, '', '2025-04-08 23:06:08', 1),
(2, 2, 1, 1, '2025-04-20 06:49:00', '2025-04-20 07:05:46', '2025-04-20 07:06:27', 20.00, 'Ok', '2025-04-20 06:53:22', 1),
(3, 3, 1, 1, '2025-04-20 13:26:00', '2025-04-20 13:29:36', '2025-04-20 13:29:57', 0.00, '', '2025-04-20 13:29:14', 1),
(4, 4, 1, 1, '2025-05-04 12:11:00', '2025-05-04 12:24:33', '2025-05-04 12:25:03', 0.00, 'se instalo correctamente con todos los procedimientos.', '2025-05-04 12:24:11', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `forms_payment`
--

CREATE TABLE `forms_payment` (
  `id` bigint(20) NOT NULL,
  `payment_type` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `forms_payment`
--

INSERT INTO `forms_payment` (`id`, `payment_type`, `registration_date`, `state`) VALUES
(1, 'EFECTIVO', '2022-07-07 21:50:55', 1),
(2, 'TRASNFERENCIA BANCARIA', '2024-10-24 19:32:46', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `type` bigint(20) NOT NULL,
  `typeid` bigint(20) NOT NULL,
  `registration_date` datetime NOT NULL,
  `image` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `clientid`, `userid`, `type`, `typeid`, `registration_date`, `image`) VALUES
(1, 1, 1, 3, 0, '2025-04-08 23:13:46', 'walter_junior_rengifo_espinoza_277755b5375d1bd09c46b56654f16a4d.png'),
(2, 1, 1, 3, 0, '2025-04-12 06:15:40', 'walter_junior_rengifo_espinoza_6d929fb39e3fbd97ebd2bbb56b39e0c7.jpeg'),
(4, 2, 1, 3, 0, '2025-04-20 07:12:32', 'prueba_prueba_p_25ce65d37ea7e4698a1126867101d7a3.jpeg'),
(5, 4, 1, 1, 4, '2025-05-04 12:25:00', 'juan_perez_soto_1a7584dde122ea0c67850ecc42ea3f3b.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidents`
--

CREATE TABLE `incidents` (
  `id` bigint(20) NOT NULL,
  `incident` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `incidents`
--

INSERT INTO `incidents` (`id`, `incident`, `registration_date`, `state`) VALUES
(1, 'SIN INTERNET', '2025-04-08 23:13:02', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `income`
--

CREATE TABLE `income` (
  `id` bigint(20) NOT NULL,
  `productid` bigint(20) NOT NULL,
  `income_date` datetime NOT NULL,
  `description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `quantity_income` bigint(20) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `income`
--

INSERT INTO `income` (`id`, `productid`, `income_date`, `description`, `quantity_income`, `unit_price`, `total_cost`) VALUES
(1, 1, '2025-04-19 08:13:03', 'COMPRA DE PRODUCTO (MEDIANTE REGISTRO)', 10, 10.00, 100.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modules`
--

CREATE TABLE `modules` (
  `id` bigint(20) NOT NULL,
  `module` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `modules`
--

INSERT INTO `modules` (`id`, `module`, `state`) VALUES
(1, 'Dashboard', 1),
(2, 'Clientes', 1),
(3, 'Usuarios', 1),
(4, 'Tickets', 1),
(5, 'Incidencias', 1),
(6, 'Facturas', 1),
(7, 'Productos', 1),
(8, 'Categorias', 1),
(9, 'Proveedores', 1),
(10, 'Pagos', 1),
(11, 'Servicios', 1),
(12, 'Empresa', 1),
(13, 'Instalaciones', 1),
(14, 'Divisas', 1),
(15, 'Formas de pago', 1),
(16, 'Comprobantes', 1),
(17, 'Unidades', 1),
(18, 'Correos', 1),
(19, 'Gestión de Red', 1),
(20, 'Campaña', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `network_routers`
--

CREATE TABLE `network_routers` (
  `id` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ip` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `port` int(11) NOT NULL,
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ip_range` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `zoneid` int(11) NOT NULL,
  `identity` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `board_name` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `version` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `status` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `network_routers`
--

INSERT INTO `network_routers` (`id`, `name`, `ip`, `port`, `username`, `password`, `ip_range`, `zoneid`, `identity`, `board_name`, `version`, `status`) VALUES
(4, 'ejemplo cake', '146.190.61.104', 80, 'wispprored', 'Hx6Eboh/YO71J8AyNZKkZU16MFhORUpxZjRGdmgwWVo3Q01PN0E9PQ==', '18.19.20.1/24', 2, '', '', '', ''),
(5, 'Hhh', '146.190.61.104', 80, 'wispprored', 'T2WAQhx+Bsd0/q24GGWNYGpRRmdWM2VWYy9xMVN0Z1FuTXBTbFE9PQ==', '192.168.10.2/24', 1, '', '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `network_zones`
--

CREATE TABLE `network_zones` (
  `id` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `mode` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `network_zones`
--

INSERT INTO `network_zones` (`id`, `name`, `mode`) VALUES
(1, 'PPPoE', 2),
(2, 'Simple Queues', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `otros_ingresos`
--

CREATE TABLE `otros_ingresos` (
  `id` int(11) NOT NULL,
  `tipo` enum('INGRESO','EGRESO') NOT NULL,
  `fecha` date DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `monto` decimal(12,2) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `state` enum('NORMAL','PENDIENTE','PAGADO') NOT NULL DEFAULT 'NORMAL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `internal_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `paytypeid` bigint(20) NOT NULL,
  `payment_date` datetime NOT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `amount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remaining_credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `state` bigint(20) NOT NULL DEFAULT 1,
  `ticket_number` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `reference_number` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id`, `billid`, `userid`, `clientid`, `internal_code`, `paytypeid`, `payment_date`, `comment`, `amount_paid`, `amount_total`, `remaining_credit`, `state`, `ticket_number`, `reference_number`) VALUES
(1, 2, 1, 1, 'T00001', 1, '2025-04-08 23:14:24', '', 0.00, 15.00, 15.00, 0, NULL, NULL),
(2, 1, 1, 1, 'T00002', 1, '2025-04-08 23:15:00', '', 35.00, 35.00, 0.00, 1, NULL, NULL),
(3, 3, 1, 1, 'T00003', 1, '2025-04-08 23:22:00', '', 20.00, 20.00, 0.00, 1, NULL, NULL),
(4, 4, 1, 1, 'T00004', 1, '2025-04-08 23:24:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(5, 5, 1, 1, 'T00005', 1, '2025-04-08 23:26:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(6, 6, 1, 1, 'T00006', 1, '2025-04-08 23:27:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(7, 7, 1, 1, 'T00007', 1, '2025-04-08 23:28:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(8, 8, 1, 1, 'T00008', 1, '2025-04-09 10:36:00', '', 50.00, 50.00, 0.00, 2, NULL, NULL),
(9, 8, 1, 1, 'T00009', 1, '2025-04-09 10:36:00', '', 50.00, 50.00, 0.00, 2, NULL, NULL),
(10, 8, 1, 1, 'T00010', 1, '2025-04-10 19:34:00', '', 10.00, 50.00, 0.00, 1, NULL, NULL),
(11, 11, 1, 2, 'T00011', 2, '2025-04-20 07:22:00', '', 20.00, 20.00, 0.00, 1, NULL, NULL),
(12, 12, 1, 2, 'T00012', 1, '2025-04-20 07:25:00', '', 10.00, 10.00, 0.00, 1, NULL, NULL),
(13, 13, 1, 2, 'T00013', 1, '2025-04-20 07:25:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(14, 8, 1, 1, 'T00014', 1, '2025-04-20 07:31:00', '', 40.00, 50.00, 0.00, 1, NULL, NULL),
(15, 14, 1, 2, 'T00015', 1, '2025-04-20 07:42:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(16, 16, 1, 3, 'T00016', 1, '2025-04-20 13:30:00', '', 15.00, 15.00, 0.00, 1, NULL, NULL),
(17, 17, 1, 3, 'T00017', 1, '2025-04-20 13:31:00', '', 50.00, 50.00, 0.00, 1, NULL, NULL),
(18, 18, 1, 3, 'T00018', 1, '2025-04-20 13:31:00', '', 10.00, 50.00, 0.00, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permits`
--

CREATE TABLE `permits` (
  `id` bigint(20) NOT NULL,
  `profileid` bigint(20) NOT NULL,
  `moduleid` bigint(20) NOT NULL,
  `r` bigint(20) NOT NULL,
  `a` bigint(20) NOT NULL,
  `e` bigint(20) NOT NULL,
  `v` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `permits`
--

INSERT INTO `permits` (`id`, `profileid`, `moduleid`, `r`, `a`, `e`, `v`) VALUES
(19, 2, 1, 0, 0, 0, 1),
(20, 2, 2, 1, 0, 0, 1),
(21, 2, 3, 0, 0, 0, 0),
(22, 2, 4, 1, 1, 0, 1),
(23, 2, 5, 0, 0, 0, 0),
(24, 2, 6, 0, 0, 0, 0),
(25, 2, 7, 0, 0, 0, 0),
(26, 2, 8, 0, 0, 0, 0),
(27, 2, 9, 0, 0, 0, 0),
(28, 2, 10, 1, 0, 0, 1),
(29, 2, 11, 0, 0, 0, 0),
(30, 2, 12, 0, 0, 0, 0),
(31, 2, 13, 1, 1, 0, 1),
(32, 2, 14, 0, 0, 0, 0),
(33, 2, 15, 0, 0, 0, 0),
(34, 2, 16, 0, 0, 0, 0),
(35, 2, 17, 0, 0, 0, 0),
(36, 2, 18, 0, 0, 0, 0),
(199, 3, 1, 1, 0, 1, 1),
(200, 3, 2, 1, 1, 0, 1),
(201, 3, 3, 1, 0, 0, 1),
(202, 3, 4, 1, 1, 0, 1),
(203, 3, 5, 1, 0, 0, 1),
(204, 3, 6, 1, 0, 0, 1),
(205, 3, 7, 1, 0, 0, 1),
(206, 3, 8, 1, 0, 0, 1),
(207, 3, 9, 1, 0, 0, 1),
(208, 3, 10, 1, 0, 0, 1),
(209, 3, 11, 1, 0, 0, 1),
(210, 3, 12, 1, 0, 0, 1),
(211, 3, 13, 1, 1, 1, 1),
(212, 3, 14, 1, 0, 0, 1),
(213, 3, 15, 1, 0, 0, 1),
(214, 3, 16, 1, 0, 0, 1),
(215, 3, 17, 1, 0, 0, 1),
(216, 3, 18, 1, 0, 0, 1),
(276, 1, 1, 1, 1, 1, 1),
(277, 1, 2, 1, 1, 1, 1),
(278, 1, 3, 1, 1, 1, 1),
(279, 1, 4, 1, 1, 1, 1),
(280, 1, 5, 1, 1, 1, 1),
(281, 1, 6, 1, 1, 1, 1),
(282, 1, 7, 1, 1, 1, 1),
(283, 1, 8, 1, 1, 1, 1),
(284, 1, 9, 1, 1, 1, 1),
(285, 1, 10, 1, 1, 1, 1),
(286, 1, 11, 1, 1, 1, 1),
(287, 1, 12, 1, 1, 1, 1),
(288, 1, 13, 1, 1, 1, 1),
(289, 1, 14, 1, 1, 1, 1),
(290, 1, 15, 1, 1, 1, 1),
(291, 1, 16, 1, 1, 1, 1),
(292, 1, 17, 1, 1, 1, 1),
(293, 1, 18, 1, 1, 1, 1),
(294, 1, 19, 1, 1, 1, 1),
(295, 1, 20, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `internal_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `barcode` varchar(13) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `product` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `model` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `brand` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `extra_info` bigint(20) NOT NULL,
  `serial_number` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mac` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `sale_price` decimal(12,2) NOT NULL,
  `purchase_price` decimal(12,2) NOT NULL,
  `stock` bigint(20) NOT NULL,
  `stock_alert` bigint(20) NOT NULL,
  `categoryid` bigint(20) NOT NULL,
  `unitid` bigint(20) NOT NULL,
  `providerid` bigint(20) NOT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `internal_code`, `barcode`, `product`, `model`, `brand`, `extra_info`, `serial_number`, `mac`, `description`, `sale_price`, `purchase_price`, `stock`, `stock_alert`, `categoryid`, `unitid`, `providerid`, `image`, `registration_date`) VALUES
(1, 'P00001', '23232', 'DATA', 'DSDAS', '', 0, '', '', '', 20.00, 10.00, 10, 1, 1, 1, 1, 'no_image.jpg', '2025-04-19 08:13:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_category`
--

CREATE TABLE `product_category` (
  `id` bigint(20) NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `product_category`
--

INSERT INTO `product_category` (`id`, `category`, `description`, `registration_date`, `state`) VALUES
(1, 'EJEMPLO', '', '2025-04-19 08:12:22', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) NOT NULL,
  `profile` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `description` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `profiles`
--

INSERT INTO `profiles` (`id`, `profile`, `description`, `registration_date`, `state`) VALUES
(1, 'ADMINISTRADOR', 'ACCESOS A TODOS LOS MODULOS', '2022-07-07 15:51:53', 1),
(2, 'TECNICO', 'CLIENTES, TICKET Y COBRANZA, CON RESTRICCIONES', '2022-07-07 15:51:53', 1),
(3, 'COBRANZA', 'COBRANZA DE FACTURAS PENDIENTES', '2022-07-07 15:51:53', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `providers`
--

CREATE TABLE `providers` (
  `id` bigint(20) NOT NULL,
  `provider` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `document` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `providers`
--

INSERT INTO `providers` (`id`, `provider`, `documentid`, `document`, `mobile`, `email`, `address`, `registration_date`, `state`) VALUES
(1, 'DATA', 1, '32323232', '432432423', '', '', '2025-04-19 08:12:35', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `p_campos`
--

CREATE TABLE `p_campos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `obligatorio` tinyint(1) DEFAULT NULL,
  `tablaId` int(11) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `campo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `p_tabla`
--

CREATE TABLE `p_tabla` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tabla` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `p_tabla`
--

INSERT INTO `p_tabla` (`id`, `nombre`, `tabla`) VALUES
(1, 'Ap Clientes', 'ap_clientes'),
(2, 'Ap Emisor', 'ap_emisor'),
(3, 'Ap Receptor', 'ap_receptor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services`
--

CREATE TABLE `services` (
  `id` bigint(20) NOT NULL,
  `internal_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `service` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `type` bigint(20) NOT NULL,
  `rise` bigint(20) NOT NULL,
  `rise_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `descent` bigint(20) NOT NULL,
  `descent_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `details` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `routers` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `services`
--

INSERT INTO `services` (`id`, `internal_code`, `service`, `type`, `rise`, `rise_type`, `descent`, `descent_type`, `price`, `details`, `routers`, `registration_date`, `state`) VALUES
(1, 'S00001', 'INTERNET HOGAR', 1, 2, 'MBPS', 5, 'MBPS', 50.00, '', '', '2025-04-08 23:05:43', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `technical` bigint(20) NOT NULL,
  `incidentsid` bigint(20) NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `priority` bigint(20) NOT NULL,
  `attention_date` datetime NOT NULL,
  `opening_date` datetime NOT NULL,
  `closing_date` datetime NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `tickets`
--

INSERT INTO `tickets` (`id`, `userid`, `clientid`, `technical`, `incidentsid`, `description`, `priority`, `attention_date`, `opening_date`, `closing_date`, `registration_date`, `state`) VALUES
(1, 1, 1, 1, 1, 'TICKET NUEVO', 1, '2025-04-08 23:13:00', '2025-04-08 23:13:35', '2025-04-20 07:20:42', '2025-04-08 23:13:18', 1),
(2, 1, 2, 0, 1, 'OK', 1, '2025-04-20 07:12:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2025-04-20 07:12:13', 5),
(3, 1, 1, 0, 1, '', 3, '2025-04-20 07:19:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2025-04-20 07:19:58', 5),
(4, 1, 3, 1, 1, 'hola no tengo internet amigo adolfo', 2, '2025-04-20 15:36:00', '2025-04-20 13:37:58', '2025-04-20 13:38:04', '2025-04-20 13:37:20', 1),
(5, 1, 1, 0, 1, '', 1, '2025-04-26 10:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2025-04-26 10:01:08', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_solution`
--

CREATE TABLE `ticket_solution` (
  `id` bigint(20) NOT NULL,
  `ticketid` bigint(20) NOT NULL,
  `technicalid` bigint(20) NOT NULL,
  `opening_date` datetime NOT NULL,
  `closing_date` datetime NOT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `ticket_solution`
--

INSERT INTO `ticket_solution` (`id`, `ticketid`, `technicalid`, `opening_date`, `closing_date`, `comment`, `state`) VALUES
(1, 1, 1, '2025-04-08 23:13:35', '2025-04-20 07:20:42', 'OK', 1),
(2, 4, 1, '2025-04-20 13:37:58', '2025-04-20 13:38:04', 'SDSDSDSADS', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tools`
--

CREATE TABLE `tools` (
  `id` bigint(20) NOT NULL,
  `facilityid` bigint(20) NOT NULL,
  `productid` bigint(20) NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `product_condition` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `serie` varchar(150) DEFAULT NULL,
  `mac` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unit`
--

CREATE TABLE `unit` (
  `id` bigint(20) NOT NULL,
  `code` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `united` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `unit`
--

INSERT INTO `unit` (`id`, `code`, `united`, `registration_date`, `state`) VALUES
(1, 'UN', 'UNIDAD', '2022-07-07 21:03:40', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `names` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `surnames` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `document` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `profileid` bigint(20) NOT NULL,
  `username` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `image` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `names`, `surnames`, `documentid`, `document`, `mobile`, `email`, `profileid`, `username`, `password`, `token`, `image`, `registration_date`, `state`) VALUES
(1, 'ADMIN', 'ADMIN', 2, '04801044274', '85251599', 'rias12112@gmail.com', 1, 'admin', 'RWJ1OEhjSzNGd1c4TitTK0hkQ3VJUT09', '', 'user_default.png', '2022-07-07 19:39:22', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) NOT NULL,
  `voucher` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher`, `registration_date`, `state`) VALUES
(1, 'RECIBO', '2022-07-07 17:37:14', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `voucher_series`
--

CREATE TABLE `voucher_series` (
  `id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `serie` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `fromc` bigint(20) NOT NULL,
  `until` bigint(20) NOT NULL,
  `voucherid` bigint(20) NOT NULL,
  `available` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `voucher_series`
--

INSERT INTO `voucher_series` (`id`, `date`, `serie`, `fromc`, `until`, `voucherid`, `available`) VALUES
(1, '2022-07-07', 'R001', 1, 1000000, 1, 999980);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `id` bigint(20) NOT NULL,
  `nombre_zona` varchar(500) NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

--
-- Volcado de datos para la tabla `zonas`
--

INSERT INTO `zonas` (`id`, `nombre_zona`, `registration_date`, `state`) VALUES
(1, 'BOLIVIA', '2025-04-09 16:09:42', 1),
(2, 'PERU', '2025-04-09 16:09:46', 1),
(3, 'CHILE', '2025-04-09 16:09:49', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ap_clientes`
--
ALTER TABLE `ap_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ap_emisor`
--
ALTER TABLE `ap_emisor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ap_receptor`
--
ALTER TABLE `ap_receptor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serviceid` (`clientid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `voucherid` (`voucherid`),
  ADD KEY `serieid` (`serieid`);

--
-- Indices de la tabla `business`
--
ALTER TABLE `business`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentid` (`documentid`),
  ADD KEY `currencyid` (`currencyid`);

--
-- Indices de la tabla `caja_nap`
--
ALTER TABLE `caja_nap`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_nap_clientes`
--
ALTER TABLE `caja_nap_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentid` (`documentid`);

--
-- Indices de la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`),
  ADD KEY `clientid` (`clientid`);

--
-- Indices de la tabla `cronjobs`
--
ALTER TABLE `cronjobs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cronjobs_core`
--
ALTER TABLE `cronjobs_core`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cronjobs_exceptions`
--
ALTER TABLE `cronjobs_exceptions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cronjobs_history`
--
ALTER TABLE `cronjobs_history`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `departures`
--
ALTER TABLE `departures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billid` (`billid`),
  ADD KEY `productid` (`productid`);

--
-- Indices de la tabla `detail_bills`
--
ALTER TABLE `detail_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billid` (`billid`);

--
-- Indices de la tabla `detail_contracts`
--
ALTER TABLE `detail_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contractid` (`contractid`),
  ADD KEY `serviceid` (`serviceid`);

--
-- Indices de la tabla `detail_facility`
--
ALTER TABLE `detail_facility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facilityid` (`facilityid`),
  ADD KEY `technicalid` (`technicalid`);

--
-- Indices de la tabla `document_type`
--
ALTER TABLE `document_type`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clientid` (`clientid`),
  ADD KEY `billid` (`billid`);

--
-- Indices de la tabla `facility`
--
ALTER TABLE `facility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technicalid` (`userid`),
  ADD KEY `clientid` (`clientid`);

--
-- Indices de la tabla `forms_payment`
--
ALTER TABLE `forms_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clientid` (`clientid`),
  ADD KEY `userid` (`userid`);

--
-- Indices de la tabla `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `productid` (`productid`);

--
-- Indices de la tabla `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `network_routers`
--
ALTER TABLE `network_routers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `network_zones`
--
ALTER TABLE `network_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `otros_ingresos`
--
ALTER TABLE `otros_ingresos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billid` (`billid`),
  ADD KEY `clientid` (`clientid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `paytypeid` (`paytypeid`);

--
-- Indices de la tabla `permits`
--
ALTER TABLE `permits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profileid` (`profileid`),
  ADD KEY `moduleid` (`moduleid`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoryid` (`categoryid`),
  ADD KEY `unitid` (`unitid`),
  ADD KEY `providerid` (`providerid`);

--
-- Indices de la tabla `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentid` (`documentid`);

--
-- Indices de la tabla `p_campos`
--
ALTER TABLE `p_campos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `p_tabla`
--
ALTER TABLE `p_tabla`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`),
  ADD KEY `clientid` (`clientid`),
  ADD KEY `incidentsid` (`incidentsid`);

--
-- Indices de la tabla `ticket_solution`
--
ALTER TABLE `ticket_solution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticketid` (`ticketid`),
  ADD KEY `technicalid` (`technicalid`);

--
-- Indices de la tabla `tools`
--
ALTER TABLE `tools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facilityid` (`facilityid`),
  ADD KEY `productid` (`productid`);

--
-- Indices de la tabla `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentid` (`documentid`),
  ADD KEY `profileid` (`profileid`);

--
-- Indices de la tabla `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `voucher_series`
--
ALTER TABLE `voucher_series`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucherid` (`voucherid`);

--
-- Indices de la tabla `zonas`
--
ALTER TABLE `zonas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ap_clientes`
--
ALTER TABLE `ap_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ap_emisor`
--
ALTER TABLE `ap_emisor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ap_receptor`
--
ALTER TABLE `ap_receptor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `backups`
--
ALTER TABLE `backups`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `bills`
--
ALTER TABLE `bills`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `business`
--
ALTER TABLE `business`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `caja_nap`
--
ALTER TABLE `caja_nap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `caja_nap_clientes`
--
ALTER TABLE `caja_nap_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cronjobs`
--
ALTER TABLE `cronjobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cronjobs_core`
--
ALTER TABLE `cronjobs_core`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cronjobs_exceptions`
--
ALTER TABLE `cronjobs_exceptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cronjobs_history`
--
ALTER TABLE `cronjobs_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `currency`
--
ALTER TABLE `currency`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `departures`
--
ALTER TABLE `departures`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detail_bills`
--
ALTER TABLE `detail_bills`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `detail_contracts`
--
ALTER TABLE `detail_contracts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detail_facility`
--
ALTER TABLE `detail_facility`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `document_type`
--
ALTER TABLE `document_type`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `emails`
--
ALTER TABLE `emails`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facility`
--
ALTER TABLE `facility`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `forms_payment`
--
ALTER TABLE `forms_payment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `income`
--
ALTER TABLE `income`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `network_routers`
--
ALTER TABLE `network_routers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `network_zones`
--
ALTER TABLE `network_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `otros_ingresos`
--
ALTER TABLE `otros_ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `permits`
--
ALTER TABLE `permits`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `product_category`
--
ALTER TABLE `product_category`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `providers`
--
ALTER TABLE `providers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `p_campos`
--
ALTER TABLE `p_campos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `p_tabla`
--
ALTER TABLE `p_tabla`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ticket_solution`
--
ALTER TABLE `ticket_solution`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tools`
--
ALTER TABLE `tools`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `unit`
--
ALTER TABLE `unit`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `voucher_series`
--
ALTER TABLE `voucher_series`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_ibfk_5` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_ibfk_6` FOREIGN KEY (`voucherid`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bills_ibfk_7` FOREIGN KEY (`serieid`) REFERENCES `voucher_series` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `business`
--
ALTER TABLE `business`
  ADD CONSTRAINT `business_ibfk_1` FOREIGN KEY (`documentid`) REFERENCES `document_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `business_ibfk_2` FOREIGN KEY (`currencyid`) REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`documentid`) REFERENCES `document_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `departures`
--
ALTER TABLE `departures`
  ADD CONSTRAINT `departures_ibfk_1` FOREIGN KEY (`productid`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detail_bills`
--
ALTER TABLE `detail_bills`
  ADD CONSTRAINT `detail_bills_ibfk_1` FOREIGN KEY (`billid`) REFERENCES `bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detail_contracts`
--
ALTER TABLE `detail_contracts`
  ADD CONSTRAINT `detail_contracts_ibfk_1` FOREIGN KEY (`contractid`) REFERENCES `contracts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_contracts_ibfk_2` FOREIGN KEY (`serviceid`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detail_facility`
--
ALTER TABLE `detail_facility`
  ADD CONSTRAINT `detail_facility_ibfk_1` FOREIGN KEY (`facilityid`) REFERENCES `facility` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_facility_ibfk_2` FOREIGN KEY (`technicalid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `emails_ibfk_1` FOREIGN KEY (`billid`) REFERENCES `bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emails_ibfk_2` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `facility`
--
ALTER TABLE `facility`
  ADD CONSTRAINT `facility_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `facility_ibfk_3` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`productid`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
