-- Script para actualizar la tabla zonas con los 22 departamentos de Guatemala
-- Configuración de codificación UTF-8
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Limpiar datos existentes
DELETE FROM `zonas`;

-- Reiniciar AUTO_INCREMENT
ALTER TABLE `zonas` AUTO_INCREMENT = 1;

-- Insertar los 22 departamentos de Guatemala con codificación correcta
INSERT INTO `zonas` (`nombre_zona`, `registration_date`, `state`) VALUES
-- Región I - Metropolitana
('GUATEMALA', NOW(), 1),

-- Región II - Norte  
('ALTA VERAPAZ', NOW(), 1),
('BAJA VERAPAZ', NOW(), 1),

-- Región III - Nororiente
('IZABAL', NOW(), 1),
('CHIQUIMULA', NOW(), 1),
('ZACAPA', NOW(), 1),
('EL PROGRESO', NOW(), 1),

-- Región IV - Suroriente
('JALAPA', NOW(), 1),
('JUTIAPA', NOW(), 1),
('SANTA ROSA', NOW(), 1),

-- Región V - Central
('SACATEPÉQUEZ', NOW(), 1),
('CHIMALTENANGO', NOW(), 1),
('ESCUINTLA', NOW(), 1),

-- Región VI - Suroccidente
('SOLOLÁ', NOW(), 1),
('TOTONICAPÁN', NOW(), 1),
('QUETZALTENANGO', NOW(), 1),
('SUCHITEPÉQUEZ', NOW(), 1),
('RETALHULEU', NOW(), 1),
('SAN MARCOS', NOW(), 1),

-- Región VII - Noroccidente
('HUEHUETENANGO', NOW(), 1),
('QUICHÉ', NOW(), 1),

-- Región VIII - Petén
('PETÉN', NOW(), 1);

-- Verificación de los datos insertados
SELECT COUNT(*) as 'Total Departamentos Insertados' FROM `zonas` WHERE `state` = 1;

-- Mostrar todos los departamentos insertados
SELECT `id`, `nombre_zona`, `registration_date`, `state` FROM `zonas` ORDER BY `id`;