-- =====================================================
-- ACTUALIZACIÓN DE ZONAS - DEPARTAMENTOS DE GUATEMALA
-- =====================================================
-- Este script actualiza la tabla 'zonas' para incluir
-- los 22 departamentos de Guatemala según la división
-- política y administrativa oficial.
-- Referencia: https://departamentos.deguate.com/
-- =====================================================

-- Limpiar datos existentes de zonas
DELETE FROM `zonas`;

-- Reiniciar el AUTO_INCREMENT
ALTER TABLE `zonas` AUTO_INCREMENT = 1;

-- Insertar los 22 departamentos de Guatemala
INSERT INTO `zonas` (`nombre_zona`, `registration_date`, `state`) VALUES
-- Región I - Metropolitana
('GUATEMALA', NOW(), 1),

-- Región II - Norte
('ALTA VERAPAZ', NOW(), 1),
('BAJA VERAPAZ', NOW(), 1),

-- Región III - Noreste
('IZABAL', NOW(), 1),
('CHIQUIMULA', NOW(), 1),
('ZACAPA', NOW(), 1),
('EL PROGRESO', NOW(), 1),

-- Región IV - Sureste
('JALAPA', NOW(), 1),
('JUTIAPA', NOW(), 1),
('SANTA ROSA', NOW(), 1),

-- Región V - Central
('SACATEPÉQUEZ', NOW(), 1),
('CHIMALTENANGO', NOW(), 1),
('ESCUINTLA', NOW(), 1),

-- Región VI - Suroccidente
('SANTA LUCÍA COTZUMALGUAPA', NOW(), 1),
('MAZATENANGO', NOW(), 1),
('RETALHULEU', NOW(), 1),
('SAN MARCOS', NOW(), 1),

-- Región VII - Noroccidente
('HUEHUETENANGO', NOW(), 1),
('QUICHÉ', NOW(), 1),
('TOTONICAPÁN', NOW(), 1),
('SOLOLÁ', NOW(), 1),

-- Región VIII - Petén
('PETÉN', NOW(), 1);

-- Verificar la inserción
SELECT COUNT(*) as 'Total Departamentos Insertados' FROM `zonas` WHERE `state` = 1;

-- Mostrar todos los departamentos insertados
SELECT `id`, `nombre_zona`, `registration_date`, `state` FROM `zonas` ORDER BY `id`;

-- =====================================================
-- NOTAS IMPORTANTES:
-- =====================================================
-- 1. Este script elimina todas las zonas existentes
-- 2. Los departamentos están organizados por regiones
-- 3. Todos los departamentos se marcan como activos (state = 1)
-- 4. Se utiliza NOW() para la fecha de registro
-- 5. Los nombres están en mayúsculas para consistencia
-- =====================================================