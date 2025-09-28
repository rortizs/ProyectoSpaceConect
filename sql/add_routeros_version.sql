-- Add RouterOS version detection to network_routers table

-- Add routeros_version column if it doesn't exist
ALTER TABLE network_routers ADD COLUMN IF NOT EXISTS routeros_version VARCHAR(32) DEFAULT NULL AFTER version;

-- Add api_type column if it doesn't exist  
ALTER TABLE network_routers ADD COLUMN IF NOT EXISTS api_type ENUM('auto', 'legacy', 'rest') DEFAULT 'auto' AFTER routeros_version;

-- Update existing routers to detect automatically (only if api_type column has NULL values)
UPDATE network_routers SET api_type = 'auto' WHERE api_type IS NULL;