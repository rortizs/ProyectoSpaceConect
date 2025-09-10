-- Script de Inicialización para Nuevos Clientes
-- Sistema de Gestión de Internet - Versión Limpia
-- Fecha: 2025-09-09

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `internet_online_clean`
--

-- ======================================
-- CREACIÓN DE ESTRUCTURA DE TABLAS
-- ======================================

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `ap_clientes`
--- Servicios básicos de internet (PERSONALIZAR SEGÚN NECESIDADES) - Precios en Quetzales
INSERT INTO `services` (`id`, `internal_code`, `service`, `type`, `rise`, `rise_type`, `descent`, `descent_type`, `price`, `details`, `routers`, `registration_date`, `state`) VALUES
(1, 'S00001', 'PLAN BÁSICO - 5 MBPS', 1, 5, 'MBPS', 5, 'MBPS', 150.00, 'Plan básico de internet residencial', '', NOW(), 1),
(2, 'S00002', 'PLAN ESTÁNDAR - 10 MBPS', 1, 10, 'MBPS', 10, 'MBPS', 250.00, 'Plan estándar de internet residencial', '', NOW(), 1),
(3, 'S00003', 'PLAN PREMIUM - 20 MBPS', 1, 20, 'MBPS', 20, 'MBPS', 350.00, 'Plan premium de internet residencial', '', NOW(), 1),
(4, 'S00004', 'PLAN EMPRESARIAL - 50 MBPS', 1, 50, 'MBPS', 50, 'MBPS', 600.00, 'Plan empresarial de internet', '', NOW(), 1);----------------------------------------------------

CREATE TABLE `ap_clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `version` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `ap_emisor`
-- --------------------------------------------------------

CREATE TABLE `ap_emisor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `version` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `ap_receptor`
-- --------------------------------------------------------

CREATE TABLE `ap_receptor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `ip` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `version` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `archivos`
-- --------------------------------------------------------

CREATE TABLE `archivos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `size` int(11) NOT NULL,
  `ruta` text NOT NULL,
  `tabla` varchar(100) NOT NULL,
  `object_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `backups`
-- --------------------------------------------------------

CREATE TABLE `backups` (
  `id` bigint(20) NOT NULL,
  `archive` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `size` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `bills`
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `business`
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `business_wsp`
-- --------------------------------------------------------

CREATE TABLE `business_wsp` (
  `id` varchar(100) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `contenido` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `caja_nap`
-- --------------------------------------------------------

CREATE TABLE `caja_nap` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `longitud` decimal(10,7) NOT NULL,
  `latitud` decimal(10,7) NOT NULL,
  `puertos` int(11) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `color_tubo` varchar(50) DEFAULT NULL,
  `color_hilo` varchar(50) DEFAULT NULL,
  `zonaId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `caja_nap_clientes`
-- --------------------------------------------------------

CREATE TABLE `caja_nap_clientes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nap_id` int(11) NOT NULL,
  `puerto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `clients`
-- --------------------------------------------------------

CREATE TABLE `clients` (
  `id` bigint(20) NOT NULL,
  `names` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `surnames` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `document` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile_optional` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `reference` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `state` bigint(20) NOT NULL,
  `net_router` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `net_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `net_password` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `net_localaddress` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `net_ip` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL,
  `nap_cliente_id` int(11) DEFAULT NULL,
  `ap_cliente_id` int(11) DEFAULT NULL,
  `zonaid` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `contracts`
-- --------------------------------------------------------

CREATE TABLE `contracts` (
  `id` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `internal_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `payday` bigint(20) NOT NULL,
  `create_invoice` tinyint(4) NOT NULL,
  `days_grace` bigint(20) NOT NULL,
  `discount` bigint(20) NOT NULL,
  `discount_price` decimal(12,2) NOT NULL,
  `months_discount` bigint(20) NOT NULL,
  `remaining_discount` bigint(20) NOT NULL,
  `contract_date` date NOT NULL,
  `suspension_date` date DEFAULT NULL,
  `finish_date` date DEFAULT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cronjobs`
-- --------------------------------------------------------

CREATE TABLE `cronjobs` (
  `id` int(11) NOT NULL,
  `description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `frequency` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `parm` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `parmdesc` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `parmx` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `lastrun` datetime NOT NULL,
  `lastresult` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cronjobs_core`
-- --------------------------------------------------------

CREATE TABLE `cronjobs_core` (
  `id` bigint(20) NOT NULL,
  `lastrun` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cronjobs_exceptions`
-- --------------------------------------------------------

CREATE TABLE `cronjobs_exceptions` (
  `id` bigint(20) NOT NULL,
  `cronjobid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cronjobs_history`
-- --------------------------------------------------------

CREATE TABLE `cronjobs_history` (
  `id` bigint(20) NOT NULL,
  `cronjobid` bigint(20) NOT NULL,
  `result` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `currency`
-- --------------------------------------------------------

CREATE TABLE `currency` (
  `id` bigint(20) NOT NULL,
  `currency_iso` char(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `language` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `currency_name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `money` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `money_plural` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `symbol` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `departures`
-- --------------------------------------------------------

CREATE TABLE `departures` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `productid` bigint(20) NOT NULL,
  `departure_date` datetime NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `observation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `detail_bills`
-- --------------------------------------------------------

CREATE TABLE `detail_bills` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `type` bigint(20) NOT NULL,
  `serproid` bigint(20) NOT NULL,
  `description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `detail_contracts`
-- --------------------------------------------------------

CREATE TABLE `detail_contracts` (
  `id` bigint(20) NOT NULL,
  `contractid` bigint(20) NOT NULL,
  `serviceid` bigint(20) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `detail_facility`
-- --------------------------------------------------------

CREATE TABLE `detail_facility` (
  `id` bigint(20) NOT NULL,
  `facilityid` bigint(20) NOT NULL,
  `technicalid` bigint(20) NOT NULL,
  `opening_date` datetime NOT NULL,
  `closing_date` datetime NOT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL,
  `red_type` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `document_type`
-- --------------------------------------------------------

CREATE TABLE `document_type` (
  `id` bigint(20) NOT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `maxlength` bigint(20) NOT NULL,
  `is_required` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `emails`
-- --------------------------------------------------------

CREATE TABLE `emails` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `subject` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `date_send` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `facility`
-- --------------------------------------------------------

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
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `forms_payment`
-- --------------------------------------------------------

CREATE TABLE `forms_payment` (
  `id` bigint(20) NOT NULL,
  `payment_type` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `gallery_images`
-- --------------------------------------------------------

CREATE TABLE `gallery_images` (
  `id` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `type` bigint(20) NOT NULL,
  `typeid` bigint(20) NOT NULL,
  `registration_date` datetime NOT NULL,
  `image` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `incidents`
-- --------------------------------------------------------

CREATE TABLE `incidents` (
  `id` bigint(20) NOT NULL,
  `incident` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `income`
-- --------------------------------------------------------

CREATE TABLE `income` (
  `id` bigint(20) NOT NULL,
  `productid` bigint(20) NOT NULL,
  `income_date` datetime NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `observation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `kardex`
-- --------------------------------------------------------

CREATE TABLE `kardex` (
  `id` bigint(20) NOT NULL,
  `productid` bigint(20) NOT NULL,
  `movement` bigint(20) NOT NULL,
  `date` datetime NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `observation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `otros_pagos`
-- --------------------------------------------------------

CREATE TABLE `otros_pagos` (
  `id` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `payments`
-- --------------------------------------------------------

CREATE TABLE `payments` (
  `id` bigint(20) NOT NULL,
  `billid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `clientid` bigint(20) NOT NULL,
  `payment_method` bigint(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `permissions`
-- --------------------------------------------------------

CREATE TABLE `permissions` (
  `id` bigint(20) NOT NULL,
  `profileid` bigint(20) NOT NULL,
  `moduleid` bigint(20) NOT NULL,
  `v` tinyint(4) NOT NULL,
  `a` tinyint(4) NOT NULL,
  `e` tinyint(4) NOT NULL,
  `d` tinyint(4) NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `products`
-- --------------------------------------------------------

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `categoryid` bigint(20) NOT NULL,
  `unitid` bigint(20) NOT NULL,
  `code` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `brand` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `model` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `stock` bigint(20) NOT NULL,
  `minimum` bigint(20) NOT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `profiles`
-- --------------------------------------------------------

CREATE TABLE `profiles` (
  `id` bigint(20) NOT NULL,
  `profile` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `description` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `providers`
-- --------------------------------------------------------

CREATE TABLE `providers` (
  `id` bigint(20) NOT NULL,
  `provider` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `document` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `contact` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `reference` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `services`
-- --------------------------------------------------------

CREATE TABLE `services` (
  `id` bigint(20) NOT NULL,
  `internal_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `service` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `type` bigint(20) NOT NULL,
  `rise` bigint(20) NOT NULL,
  `rise_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `descent` bigint(20) NOT NULL,
  `descent_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `details` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `routers` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `tickets`
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `units`
-- --------------------------------------------------------

CREATE TABLE `units` (
  `id` bigint(20) NOT NULL,
  `unit` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `names` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `surnames` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `documentid` bigint(20) NOT NULL,
  `document` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `mobile` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `profileid` bigint(20) NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `password` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `token` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `image` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `vouchers`
-- --------------------------------------------------------

CREATE TABLE `vouchers` (
  `id` bigint(20) NOT NULL,
  `voucher` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `voucher_series`
-- --------------------------------------------------------

CREATE TABLE `voucher_series` (
  `id` bigint(20) NOT NULL,
  `voucherid` bigint(20) NOT NULL,
  `serie` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `numbering` bigint(20) NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `zones`
-- --------------------------------------------------------

CREATE TABLE `zones` (
  `id` bigint(20) NOT NULL,
  `zone` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `registration_date` datetime NOT NULL,
  `state` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- ======================================
-- DATOS ESENCIALES DEL SISTEMA
-- ======================================

-- 1. Moneda (Tabla: currency) - Solo Quetzal Guatemalteco
INSERT INTO `currency` (`id`, `currency_iso`, `language`, `currency_name`, `money`, `money_plural`, `symbol`, `registration_date`, `state`) VALUES
(1, 'GTQ', 'ES', 'QUETZALES GUATEMALTECOS', 'QUETZAL', 'QUETZALES', 'Q', NOW(), 1);

-- 2. Tipos de documento (Tabla: document_type)
INSERT INTO `document_type` (`id`, `document`, `maxlength`, `is_required`) VALUES
(1, 'SIN DOCUMENTO', 8, 0),
(2, 'CÉDULA DE IDENTIDAD', 13, 1),
(3, 'RUC', 11, 1),
(4, 'CARNET DE EXTRANJERÍA', 20, 0),
(5, 'PASAPORTE', 20, 0);

-- 3. Formas de pago (Tabla: forms_payment)
INSERT INTO `forms_payment` (`id`, `payment_type`, `registration_date`, `state`) VALUES
(1, 'EFECTIVO', NOW(), 1),
(2, 'TRANSFERENCIA BANCARIA', NOW(), 1),
(3, 'TARJETA DE CRÉDITO', NOW(), 1),
(4, 'TARJETA DE DÉBITO', NOW(), 1);

-- 4. Tipos de comprobante (Tabla: vouchers)
INSERT INTO `vouchers` (`id`, `voucher`, `registration_date`, `state`) VALUES
(1, 'RECIBO', NOW(), 1),
(2, 'FACTURA', NOW(), 1);

-- 5. Perfiles de usuario (Tabla: profiles)
INSERT INTO `profiles` (`id`, `profile`, `description`, `registration_date`, `state`) VALUES
(1, 'ADMINISTRADOR', 'ACCESO COMPLETO A TODOS LOS MÓDULOS', NOW(), 1),
(2, 'TÉCNICO', 'CLIENTES, TICKETS Y COBRANZA CON RESTRICCIONES', NOW(), 1),
(3, 'COBRANZA', 'COBRANZA DE FACTURAS PENDIENTES', NOW(), 1),
(4, 'OPERADOR', 'GESTIÓN BÁSICA DE CLIENTES', NOW(), 1);

-- ======================================
-- USUARIO ADMINISTRADOR POR DEFECTO
-- ======================================

-- Usuario administrador (Contraseña: admin123)
INSERT INTO `users` (`id`, `names`, `surnames`, `documentid`, `document`, `mobile`, `email`, `profileid`, `username`, `password`, `token`, `image`, `registration_date`, `state`) VALUES
(1, 'ADMINISTRADOR', 'SISTEMA', 2, '00000000000', '0000000000', 'admin@empresa.com', 1, 'admin', 'RWJ1OEhjSzNGd1c4TitTK0hkQ3VJUT09', '', 'user_default.png', NOW(), 1);

-- ======================================
-- CONFIGURACIÓN DE EMPRESA (PERSONALIZAR)
-- ======================================

-- Configuración básica de empresa (DEBE SER PERSONALIZADA)
INSERT INTO `business` (`id`, `documentid`, `ruc`, `business_name`, `tradename`, `slogan`, `mobile`, `mobile_refrence`, `email`, `password`, `server_host`, `port`, `address`, `department`, `province`, `district`, `ubigeo`, `footer_text`, `currencyid`, `print_format`, `logotyope`, `logo_login`, `logo_email`, `favicon`, `country_code`, `google_apikey`, `reniec_apikey`, `background`, `whatsapp_api`, `whatsapp_key`) VALUES
(1, 1, '000000000', 'MI EMPRESA ISP', 'MI EMPRESA ISP', 'Conectando tu mundo', '0000000000', '0000000000', 'info@miempresa.com', '', '', '25', 'Dirección de la empresa', 'Departamento', 'Provincia', 'Distrito', '00000', 'Gracias por su preferencia', 1, 'ticket', 'logo_default.png', 'login_default.png', '', 'favicon_default.png', '502', '', '', 'bg-default.jpeg', '', '');

-- ======================================
-- SERVICIOS DE EJEMPLO
-- ======================================

-- Servicios básicos de internet (PERSONALIZAR SEGÚN NECESIDADES)
INSERT INTO `services` (`id`, `internal_code`, `service`, `type`, `rise`, `rise_type`, `descent`, `descent_type`, `price`, `details`, `routers`, `registration_date`, `state`) VALUES
(1, 'S00001', 'PLAN BÁSICO - 5 MBPS', 1, 5, 'MBPS', 5, 'MBPS', 25.00, 'Plan básico de internet residencial', '', NOW(), 1),
(2, 'S00002', 'PLAN ESTÁNDAR - 10 MBPS', 1, 10, 'MBPS', 10, 'MBPS', 40.00, 'Plan estándar de internet residencial', '', NOW(), 1),
(3, 'S00003', 'PLAN PREMIUM - 20 MBPS', 1, 20, 'MBPS', 20, 'MBPS', 60.00, 'Plan premium de internet residencial', '', NOW(), 1),
(4, 'S00004', 'PLAN EMPRESARIAL - 50 MBPS', 1, 50, 'MBPS', 50, 'MBPS', 150.00, 'Plan empresarial de internet', '', NOW(), 1);

-- ======================================
-- INCIDENCIAS COMUNES
-- ======================================

-- Tipos de incidencias para tickets
INSERT INTO `incidents` (`id`, `incident`, `registration_date`, `state`) VALUES
(1, 'SIN INTERNET', NOW(), 1),
(2, 'INTERNET LENTO', NOW(), 1),
(3, 'PROBLEMA CON ROUTER', NOW(), 1),
(4, 'INSTALACIÓN NUEVA', NOW(), 1),
(5, 'CAMBIO DE PLAN', NOW(), 1),
(6, 'SUSPENSIÓN DE SERVICIO', NOW(), 1),
(7, 'RECONEXIÓN DE SERVICIO', NOW(), 1),
(8, 'SOPORTE TÉCNICO', NOW(), 1);

-- ======================================
-- CONFIGURACIONES DE CRONJOBS
-- ======================================

-- Jobs automáticos del sistema
INSERT INTO `cronjobs` (`id`, `description`, `frequency`, `parm`, `parmdesc`, `parmx`, `lastrun`, `lastresult`, `code`, `status`) VALUES
(1, 'Generación automática de facturas', 'monthly', '1', 'Día del mes', '', '0000-00-00 00:00:00', '', 'BILL_GENERATE', 1),
(2, 'Suspensión automática por mora', 'daily', '5', 'Días de gracia', '', '0000-00-00 00:00:00', '', 'CLIENT_SUSPEND', 1),
(3, 'Backup de base de datos', 'daily', '02:00', 'Hora de backup', '', '0000-00-00 00:00:00', '', 'BACKUP_DB', 1),
(4, 'Envío de recordatorios WhatsApp', 'daily', '3', 'Días antes vencimiento', '', '0000-00-00 00:00:00', '', 'WSP_REMINDER', 0);

-- ======================================
-- CONFIGURACIÓN INICIAL DE PLANTILLAS WSP
-- ======================================

-- Plantillas básicas de WhatsApp
INSERT INTO `business_wsp` (`id`, `titulo`, `contenido`) VALUES
('mensaje_bienvenida', 'Mensaje de Bienvenida', '¡Bienvenido a nuestro servicio de internet! Su servicio ha sido activado correctamente.'),
('recordatorio_pago', 'Recordatorio de Pago', 'Estimado cliente, le recordamos que su factura vence el {fecha_vencimiento}. Total: {monto}'),
('suspension_aviso', 'Aviso de Suspensión', 'Su servicio de internet ha sido suspendido por mora. Para reactivar contacte con nosotros.'),
('pago_recibido', 'Confirmación de Pago', 'Hemos recibido su pago por {monto}. Su servicio continuará activo. ¡Gracias!');

-- ======================================
-- NOTAS IMPORTANTES
-- ======================================

/*
CONFIGURACIONES PENDIENTES PARA PERSONALIZAR:

1. EMPRESA (tabla: business):
   - Actualizar datos reales de la empresa
   - Configurar logos e imágenes
   - Configurar API keys de servicios externos

2. ROUTERS MIKROTIK (tablas: ap_clientes, ap_emisor, ap_receptor):
   - Agregar configuración de routers según instalación

3. ZONAS Y UBICACIONES (tabla: zones):
   - Definir zonas de cobertura

4. PLANES Y PRECIOS:
   - Ajustar servicios según ofertas comerciales

5. CONFIGURACIÓN DE WHATSAPP:
   - Configurar API de WhatsApp en tabla business

6. PERMISOS:
   - Configurar permisos específicos por perfil si es necesario

USUARIOS POR DEFECTO:
- Usuario: admin
- Contraseña: admin123
- CAMBIAR INMEDIATAMENTE DESPUÉS DE LA INSTALACIÓN
*/

-- ======================================
-- ÍNDICES Y CLAVES PRIMARIAS
-- ======================================

-- Claves primarias e índices para todas las tablas
ALTER TABLE `ap_clientes` ADD PRIMARY KEY (`id`);
ALTER TABLE `ap_emisor` ADD PRIMARY KEY (`id`);
ALTER TABLE `ap_receptor` ADD PRIMARY KEY (`id`);
ALTER TABLE `archivos` ADD PRIMARY KEY (`id`);
ALTER TABLE `backups` ADD PRIMARY KEY (`id`);
ALTER TABLE `bills` ADD PRIMARY KEY (`id`);
ALTER TABLE `business` ADD PRIMARY KEY (`id`);
ALTER TABLE `business_wsp` ADD PRIMARY KEY (`id`);
ALTER TABLE `caja_nap` ADD PRIMARY KEY (`id`);
ALTER TABLE `caja_nap_clientes` ADD PRIMARY KEY (`id`);
ALTER TABLE `clients` ADD PRIMARY KEY (`id`);
ALTER TABLE `contracts` ADD PRIMARY KEY (`id`);
ALTER TABLE `cronjobs` ADD PRIMARY KEY (`id`);
ALTER TABLE `cronjobs_core` ADD PRIMARY KEY (`id`);
ALTER TABLE `cronjobs_exceptions` ADD PRIMARY KEY (`id`);
ALTER TABLE `cronjobs_history` ADD PRIMARY KEY (`id`);
ALTER TABLE `currency` ADD PRIMARY KEY (`id`);
ALTER TABLE `departures` ADD PRIMARY KEY (`id`);
ALTER TABLE `detail_bills` ADD PRIMARY KEY (`id`);
ALTER TABLE `detail_contracts` ADD PRIMARY KEY (`id`);
ALTER TABLE `detail_facility` ADD PRIMARY KEY (`id`);
ALTER TABLE `document_type` ADD PRIMARY KEY (`id`);
ALTER TABLE `emails` ADD PRIMARY KEY (`id`);
ALTER TABLE `facility` ADD PRIMARY KEY (`id`);
ALTER TABLE `forms_payment` ADD PRIMARY KEY (`id`);
ALTER TABLE `gallery_images` ADD PRIMARY KEY (`id`);
ALTER TABLE `incidents` ADD PRIMARY KEY (`id`);
ALTER TABLE `income` ADD PRIMARY KEY (`id`);
ALTER TABLE `kardex` ADD PRIMARY KEY (`id`);
ALTER TABLE `otros_pagos` ADD PRIMARY KEY (`id`);
ALTER TABLE `payments` ADD PRIMARY KEY (`id`);
ALTER TABLE `permissions` ADD PRIMARY KEY (`id`);
ALTER TABLE `products` ADD PRIMARY KEY (`id`);
ALTER TABLE `profiles` ADD PRIMARY KEY (`id`);
ALTER TABLE `providers` ADD PRIMARY KEY (`id`);
ALTER TABLE `services` ADD PRIMARY KEY (`id`);
ALTER TABLE `tickets` ADD PRIMARY KEY (`id`);
ALTER TABLE `units` ADD PRIMARY KEY (`id`);
ALTER TABLE `users` ADD PRIMARY KEY (`id`);
ALTER TABLE `vouchers` ADD PRIMARY KEY (`id`);
ALTER TABLE `voucher_series` ADD PRIMARY KEY (`id`);
ALTER TABLE `zones` ADD PRIMARY KEY (`id`);

-- Auto incrementos
ALTER TABLE `ap_clientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_emisor` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_receptor` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `archivos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `backups` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `bills` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `business` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `caja_nap` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `caja_nap_clientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `clients` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `contracts` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cronjobs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `cronjobs_core` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cronjobs_exceptions` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cronjobs_history` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `currency` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `departures` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `detail_bills` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `detail_contracts` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `detail_facility` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `document_type` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `emails` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `facility` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `forms_payment` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `gallery_images` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `incidents` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
ALTER TABLE `income` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `kardex` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `otros_pagos` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `payments` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `permissions` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `products` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `profiles` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `providers` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `services` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `tickets` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `units` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `vouchers` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `voucher_series` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `zones` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
