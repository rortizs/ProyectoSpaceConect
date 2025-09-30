-- =====================================================
-- SCRIPT DE MIGRACIÓN - QUEUE TREE FUNCTIONALITY
-- =====================================================
-- Este script agrega las tablas necesarias para la funcionalidad de Queue Tree
-- Ejecutar este script en la base de datos del cliente para resolver problemas de guardado

-- Verificar si las tablas ya existen antes de crearlas
SET @table_exists = 0;

-- =====================================================
-- TABLA: queue_tree_policies
-- =====================================================
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'queue_tree_policies';

SET @sql = IF(@table_exists = 0, 
'CREATE TABLE `queue_tree_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `router_id` int(11) DEFAULT NULL,
  `parent_queue` varchar(100) DEFAULT NULL,
  `target` varchar(100) DEFAULT NULL,
  `max_limit` varchar(50) DEFAULT NULL,
  `max_limit_upload` varchar(50) DEFAULT NULL,
  `max_limit_download` varchar(50) DEFAULT NULL,
  `burst_limit` varchar(50) DEFAULT NULL,
  `burst_threshold` varchar(50) DEFAULT NULL,
  `burst_time` varchar(50) DEFAULT NULL,
  `priority` int(11) DEFAULT 4,
  `queue_type` varchar(50) DEFAULT ''default'',
  `packet_mark` varchar(100) DEFAULT NULL,
  `connection_mark` varchar(100) DEFAULT NULL,
  `status` enum(''active'',''inactive'') DEFAULT ''active'',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_router_id` (`router_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;', 
'SELECT "Table queue_tree_policies already exists" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- TABLA: client_queue_assignments
-- =====================================================
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'client_queue_assignments';

SET @sql = IF(@table_exists = 0, 
'CREATE TABLE `client_queue_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) NOT NULL,
  `queue_policy_id` int(11) NOT NULL,
  `client_ip` varchar(50) NOT NULL,
  `upload_limit` varchar(50) DEFAULT NULL,
  `download_limit` varchar(50) DEFAULT NULL,
  `priority` int(11) DEFAULT 4,
  `status` enum(''active'',''inactive'',''suspended'') DEFAULT ''active'',
  `sync_status` enum(''pending'',''synced'',''error'') DEFAULT ''pending'',
  `last_sync` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_queue_policy_id` (`queue_policy_id`),
  KEY `idx_client_ip` (`client_ip`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `unique_client_active` (`client_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;', 
'SELECT "Table client_queue_assignments already exists" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- TABLA: queue_tree_templates (opcional)
-- =====================================================
SELECT COUNT(*) INTO @table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'queue_tree_templates';

SET @sql = IF(@table_exists = 0, 
'CREATE TABLE `queue_tree_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text,
  `config_json` text,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;', 
'SELECT "Table queue_tree_templates already exists" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- INSERTAR DATOS INICIALES
-- =====================================================

-- Insertar templates básicos si la tabla está vacía
INSERT IGNORE INTO `queue_tree_templates` (`id`, `name`, `category`, `description`, `config_json`, `is_active`) VALUES
(1, 'Residencial Básico', 'Residencial', 'Template para clientes residenciales básicos', '{"max_limit":"5M/10M","priority":4,"queue_type":"default"}', 1),
(2, 'Empresarial', 'Empresarial', 'Template para clientes empresariales', '{"max_limit":"10M/20M","priority":3,"queue_type":"default"}', 1),
(3, 'Premium', 'Premium', 'Template para clientes premium', '{"max_limit":"20M/50M","priority":2,"queue_type":"default"}', 1);

-- =====================================================
-- VERIFICAR ESTRUCTURA DE TABLA CLIENTS
-- =====================================================

-- Verificar si los campos de red existen en la tabla clients
SET @column_exists = 0;

SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'clients' 
AND column_name = 'net_ip';

-- Si el campo net_ip no existe, agregarlo
SET @sql = IF(@column_exists = 0, 
'ALTER TABLE `clients` ADD COLUMN `net_ip` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci DEFAULT NULL;', 
'SELECT "Column net_ip already exists in clients table" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar campo nap_cliente_id
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'clients' 
AND column_name = 'nap_cliente_id';

SET @sql = IF(@column_exists = 0, 
'ALTER TABLE `clients` ADD COLUMN `nap_cliente_id` int(11) DEFAULT NULL;', 
'SELECT "Column nap_cliente_id already exists in clients table" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar campo ap_cliente_id
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'clients' 
AND column_name = 'ap_cliente_id';

SET @sql = IF(@column_exists = 0, 
'ALTER TABLE `clients` ADD COLUMN `ap_cliente_id` int(11) DEFAULT NULL;', 
'SELECT "Column ap_cliente_id already exists in clients table" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- MENSAJE FINAL
-- =====================================================
SELECT 'Migración completada exitosamente. Las tablas de Queue Tree han sido creadas.' as resultado;