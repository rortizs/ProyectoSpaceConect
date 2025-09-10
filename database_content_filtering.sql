-- Content Filtering Module Database Schema
-- This extends the existing ISP management system with content filtering capabilities

-- Table: content_filter_categories
-- Stores predefined content categories for filtering
CREATE TABLE IF NOT EXISTS `content_filter_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Category name (e.g., Social Media, Adult Content)',
  `description` text COMMENT 'Category description',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Icon class for UI',
  `color` varchar(7) DEFAULT '#007bff' COMMENT 'Color for UI display',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: content_filter_domains
-- Stores domains associated with each category
CREATE TABLE IF NOT EXISTS `content_filter_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL COMMENT 'Domain name (e.g., facebook.com)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_domains_category` (`category_id`),
  KEY `idx_domain` (`domain`),
  CONSTRAINT `fk_domains_category` FOREIGN KEY (`category_id`) REFERENCES `content_filter_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: content_filter_policies
-- Stores filtering policies that can be applied to clients
CREATE TABLE IF NOT EXISTS `content_filter_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Policy name',
  `description` text COMMENT 'Policy description',
  `is_default` tinyint(1) DEFAULT 0 COMMENT '1=Default policy for new clients',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: content_filter_policy_categories
-- Links policies with blocked categories (many-to-many)
CREATE TABLE IF NOT EXISTS `content_filter_policy_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `action` enum('block','allow') DEFAULT 'block' COMMENT 'Action to take for this category',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_category` (`policy_id`, `category_id`),
  KEY `fk_policy_cats_policy` (`policy_id`),
  KEY `fk_policy_cats_category` (`category_id`),
  CONSTRAINT `fk_policy_cats_policy` FOREIGN KEY (`policy_id`) REFERENCES `content_filter_policies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_policy_cats_category` FOREIGN KEY (`category_id`) REFERENCES `content_filter_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: content_filter_client_policies
-- Links clients with filtering policies
CREATE TABLE IF NOT EXISTS `content_filter_client_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` bigint NOT NULL,
  `policy_id` int(11) NOT NULL,
  `router_id` int(11) NOT NULL COMMENT 'Router where the policy is applied',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `applied_at` timestamp NULL DEFAULT NULL COMMENT 'When the policy was applied to router',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_router` (`client_id`, `router_id`),
  KEY `fk_client_policies_client` (`client_id`),
  KEY `fk_client_policies_policy` (`policy_id`),
  KEY `fk_client_policies_router` (`router_id`),
  CONSTRAINT `fk_client_policies_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_client_policies_policy` FOREIGN KEY (`policy_id`) REFERENCES `content_filter_policies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_client_policies_router` FOREIGN KEY (`router_id`) REFERENCES `network_routers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: content_filter_custom_domains
-- Client-specific custom blocked/allowed domains
CREATE TABLE IF NOT EXISTS `content_filter_custom_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` bigint NOT NULL,
  `domain` varchar(255) NOT NULL,
  `action` enum('block','allow') DEFAULT 'block',
  `comment` text COMMENT 'Reason for custom rule',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_domain` (`client_id`, `domain`),
  KEY `fk_custom_domains_client` (`client_id`),
  CONSTRAINT `fk_custom_domains_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: content_filter_logs
-- Logs filtering activities for monitoring and reporting
CREATE TABLE IF NOT EXISTS `content_filter_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` bigint NOT NULL,
  `router_id` int(11) NOT NULL,
  `action` enum('apply','remove','update') NOT NULL,
  `policy_id` int(11) DEFAULT NULL,
  `details` json DEFAULT NULL COMMENT 'Additional details about the action',
  `status` enum('success','error','pending') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_logs_client` (`client_id`),
  KEY `fk_logs_router` (`router_id`),
  KEY `fk_logs_policy` (`policy_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_logs_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logs_router` FOREIGN KEY (`router_id`) REFERENCES `network_routers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logs_policy` FOREIGN KEY (`policy_id`) REFERENCES `content_filter_policies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default categories
INSERT INTO `content_filter_categories` (`name`, `description`, `icon`, `color`) VALUES
('Redes Sociales', 'Bloquear acceso a redes sociales como Facebook, Instagram, Twitter', 'fas fa-users', '#1877f2'),
('YouTube', 'Bloquear acceso a YouTube y videos', 'fab fa-youtube', '#ff0000'),
('Contenido Adulto', 'Bloquear sitios web con contenido para adultos', 'fas fa-exclamation-triangle', '#dc3545'),
('Entretenimiento', 'Bloquear sitios de entretenimiento y juegos', 'fas fa-gamepad', '#6f42c1'),
('Compras Online', 'Bloquear sitios de comercio electrónico', 'fas fa-shopping-cart', '#28a745'),
('Streaming', 'Bloquear plataformas de streaming como Netflix, Spotify', 'fas fa-play-circle', '#ffc107');

-- Insert default domains for each category
INSERT INTO `content_filter_domains` (`category_id`, `domain`) VALUES
-- Redes Sociales
(1, 'facebook.com'), (1, 'instagram.com'), (1, 'twitter.com'), (1, 'tiktok.com'), 
(1, 'linkedin.com'), (1, 'snapchat.com'), (1, 'whatsapp.com'), (1, 'telegram.org'),
-- YouTube
(2, 'youtube.com'), (2, 'youtu.be'), (2, 'googlevideo.com'),
-- Contenido Adulto (sample domains - add more as needed)
(3, 'pornhub.com'), (3, 'xvideos.com'), (3, 'xnxx.com'), (3, 'redtube.com'),
-- Entretenimiento
(4, 'twitch.tv'), (4, 'steam.com'), (4, 'epicgames.com'), (4, 'roblox.com'),
-- Compras Online
(5, 'amazon.com'), (5, 'mercadolibre.com'), (5, 'ebay.com'), (5, 'aliexpress.com'),
-- Streaming
(6, 'netflix.com'), (6, 'spotify.com'), (6, 'hulu.com'), (6, 'disneyplus.com');

-- Create a default "Basic Filter" policy
INSERT INTO `content_filter_policies` (`name`, `description`, `is_default`) VALUES
('Filtro Básico', 'Política básica que bloquea contenido adulto y redes sociales', 1);

-- Link the default policy with content categories
INSERT INTO `content_filter_policy_categories` (`policy_id`, `category_id`, `action`) VALUES
(1, 1, 'block'), -- Redes Sociales
(1, 3, 'block'); -- Contenido Adulto