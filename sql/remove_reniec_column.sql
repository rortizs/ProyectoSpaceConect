-- =====================================================
-- SCRIPT PARA REMOVER LA API RENIEC DEL SISTEMA
-- =====================================================
-- 
-- Este script elimina la columna 'reniec_apikey' de la tabla 'business'
-- ya que RENIEC es una API peruana que no funciona en Guatemala.
--
-- Ejecutar este script después de hacer backup de la base de datos
-- 
-- INSTRUCCIONES:
-- 1. Hacer backup de la base de datos antes de ejecutar
-- 2. Ejecutar este script en phpMyAdmin o cliente MySQL
-- 3. Verificar que la columna ha sido eliminada
-- =====================================================

-- Verificar si la columna existe antes de eliminarla
SET @exist := (SELECT count(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'business' 
               AND COLUMN_NAME = 'reniec_apikey');

-- Eliminar la columna solo si existe
SET @sqlstmt := IF(@exist > 0, 
                   'ALTER TABLE business DROP COLUMN reniec_apikey', 
                   'SELECT "La columna reniec_apikey no existe" as mensaje');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mostrar resultado
SELECT 
    CASE 
        WHEN @exist > 0 THEN 'Columna reniec_apikey eliminada exitosamente'
        ELSE 'La columna reniec_apikey no existía en la tabla'
    END as resultado;
