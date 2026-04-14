-- Municipal Network Admin Module - Database Schema
-- Extends the existing WISP system with municipal department-based network management
-- Requires: network_routers, content_filter_categories, users tables to exist

-- =============================================
-- Table: muni_departments
-- Organizational departments of the municipality
-- =============================================
CREATE TABLE IF NOT EXISTS `muni_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `router_id` int(11) NOT NULL COMMENT 'FK to network_routers',
  `name` varchar(100) NOT NULL COMMENT 'Department name (e.g., Finanzas, RRHH)',
  `description` text COMMENT 'Department description',
  `ip_range` varchar(50) NOT NULL COMMENT 'Simple range: start-end (e.g., 192.168.88.10-192.168.88.50)',
  `priority` tinyint(4) NOT NULL DEFAULT 4 COMMENT 'MikroTik queue priority 1-8 (1=highest)',
  `default_upload` varchar(20) DEFAULT '5M' COMMENT 'Default upload limit for users',
  `default_download` varchar(20) DEFAULT '10M' COMMENT 'Default download limit for users',
  `burst_upload` varchar(20) DEFAULT NULL COMMENT 'Burst upload limit',
  `burst_download` varchar(20) DEFAULT NULL COMMENT 'Burst download limit',
  `burst_threshold_up` varchar(20) DEFAULT NULL COMMENT 'Burst threshold upload',
  `burst_threshold_down` varchar(20) DEFAULT NULL COMMENT 'Burst threshold download',
  `burst_time` varchar(20) DEFAULT NULL COMMENT 'Burst time (e.g., 8s/4s)',
  `qos_max_limit` varchar(40) DEFAULT NULL COMMENT 'Dept bandwidth cap reference (informational, Queue Trees managed by Digicom)',
  `qos_queue_tree_id` varchar(20) DEFAULT NULL COMMENT 'Legacy: MikroTik .id reference (Queue Trees now read-only)',
  `qos_sync_status` enum('pending','synced','error') DEFAULT 'pending' COMMENT 'Legacy: QoS sync state (Queue Trees now read-only)',
  `status` tinyint(4) DEFAULT 1 COMMENT '1=active, 0=inactive',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dept_name` (`name`),
  KEY `fk_dept_router` (`router_id`),
  KEY `idx_dept_status` (`status`),
  CONSTRAINT `fk_dept_router` FOREIGN KEY (`router_id`) REFERENCES `network_routers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: muni_users
-- Office network users (NOT ISP clients)
-- =============================================
CREATE TABLE IF NOT EXISTS `muni_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL COMMENT 'FK to muni_departments (optional grouping)',
  `router_id` int(11) NOT NULL COMMENT 'FK to network_routers',
  `name` varchar(150) NOT NULL COMMENT 'Full name of office user',
  `ip_address` varchar(45) NOT NULL COMMENT 'Static IP assigned to user',
  `mac_address` varchar(17) DEFAULT NULL COMMENT 'MAC address (AA:BB:CC:DD:EE:FF)',
  `custom_upload` varchar(20) DEFAULT '5M' COMMENT 'Upload limit',
  `custom_download` varchar(20) DEFAULT '10M' COMMENT 'Download limit',
  `queue_name` varchar(100) DEFAULT NULL COMMENT 'MikroTik Simple Queue name reference',
  `queue_sync_status` enum('pending','synced','error','disabled') DEFAULT 'pending' COMMENT 'Queue sync state',
  `status` tinyint(4) DEFAULT 1 COMMENT '1=active, 0=disabled',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_ip` (`ip_address`),
  KEY `fk_user_dept` (`department_id`),
  KEY `fk_user_router` (`router_id`),
  KEY `idx_user_status` (`status`),
  KEY `idx_user_dept_status` (`department_id`, `status`),
  CONSTRAINT `fk_user_dept` FOREIGN KEY (`department_id`) REFERENCES `muni_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_router` FOREIGN KEY (`router_id`) REFERENCES `network_routers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: muni_dept_filter_policies
-- Links departments to content filter categories (block/allow per dept)
-- =============================================
CREATE TABLE IF NOT EXISTS `muni_dept_filter_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL COMMENT 'FK to muni_departments',
  `category_id` int(11) NOT NULL COMMENT 'FK to content_filter_categories',
  `action` enum('block','allow') DEFAULT 'block' COMMENT 'Action for this category in this dept',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dept_category` (`department_id`, `category_id`),
  KEY `fk_deptfilter_dept` (`department_id`),
  KEY `fk_deptfilter_category` (`category_id`),
  CONSTRAINT `fk_deptfilter_dept` FOREIGN KEY (`department_id`) REFERENCES `muni_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_deptfilter_category` FOREIGN KEY (`category_id`) REFERENCES `content_filter_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: muni_dept_whitelist
-- Per-department domain whitelist overrides (NULL dept = global whitelist)
-- =============================================
CREATE TABLE IF NOT EXISTS `muni_dept_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL COMMENT 'FK to muni_departments (NULL = global)',
  `domain` varchar(255) NOT NULL COMMENT 'Whitelisted domain',
  `added_by` bigint DEFAULT NULL COMMENT 'FK to users (who added this)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_whitelist_dept` (`department_id`),
  KEY `fk_whitelist_user` (`added_by`),
  KEY `idx_whitelist_domain` (`domain`),
  CONSTRAINT `fk_whitelist_dept` FOREIGN KEY (`department_id`) REFERENCES `muni_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_whitelist_user` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Table: muni_audit_log
-- Audit trail for all municipal network actions
-- =============================================
CREATE TABLE IF NOT EXISTS `muni_audit_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL COMMENT 'FK to users (admin who performed action)',
  `action` varchar(50) NOT NULL COMMENT 'Action type (create_user, sync_qos, block_domain, etc)',
  `entity_type` varchar(30) DEFAULT NULL COMMENT 'Entity type (department, user, filter, qos)',
  `entity_id` int(11) DEFAULT NULL COMMENT 'ID of affected entity',
  `details` text COMMENT 'JSON with change details',
  `status` enum('success','error') DEFAULT 'success',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- Module & Permissions Setup
-- =============================================

-- Register the Municipal Network module (ID 21)
INSERT INTO `modules` (`id`, `module`, `state`)
VALUES (21, 'Red Municipal', 1)
ON DUPLICATE KEY UPDATE `module` = VALUES(`module`);

-- Create the Municipal Admin profile
INSERT INTO `profiles` (`profile`, `description`, `registration_date`, `state`)
VALUES ('Administrador Municipal', 'Acceso exclusivo al módulo de red municipal', NOW(), 1);

-- Grant permissions to the new profile
SET @muni_profile_id = (SELECT id FROM `profiles` WHERE `profile` = 'Administrador Municipal' LIMIT 1);

-- DASHBOARD module (view only)
INSERT INTO `permits` (`profileid`, `moduleid`, `v`, `r`, `a`, `e`)
VALUES (@muni_profile_id, 1, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE `v` = 1;

-- MUNI module (full CRUD)
INSERT INTO `permits` (`profileid`, `moduleid`, `v`, `r`, `a`, `e`)
VALUES (@muni_profile_id, 21, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE `v` = 1, `r` = 1, `a` = 1, `e` = 1;
