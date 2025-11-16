-- =====================================================
-- SCRIPT DE MIGRACIÓN: Tabla estudiante para importación
-- BiblioSM v1.0 - rptListadoEstudiantes.xlsx
-- =====================================================
-- INSTRUCCIONES:
-- 1. Abre phpMyAdmin
-- 2. Selecciona tu BD (biblioteca_mvc)
-- 3. Pestaña: SQL
-- 4. Copia TODA esta consulta
-- 5. Pega en el área de texto
-- 6. Haz clic: Ejecutar
-- 7. Espera mensaje "Consultas ejecutadas correctamente"
-- 8. Verifica: Tabla estudiante > Estructura > deben aparecer nuevas columnas
-- =====================================================

-- PASO 1: Eliminar columnas antiguas (solo si existen)
ALTER TABLE estudiante DROP COLUMN IF EXISTS carrera;
ALTER TABLE estudiante DROP COLUMN IF EXISTS direccion;
ALTER TABLE estudiante DROP COLUMN IF EXISTS telefono;

-- PASO 2: Agregar nuevas columnas para almacenar datos separados
ALTER TABLE estudiante
  ADD COLUMN IF NOT EXISTS grado VARCHAR(50) NULL COMMENT 'Grado académico (ej: PRIMERO, SEGUNDO)',
  ADD COLUMN IF NOT EXISTS seccion VARCHAR(50) NULL COMMENT 'Sección o turno',
  ADD COLUMN IF NOT EXISTS apellido_paterno VARCHAR(100) NULL COMMENT 'Apellido paterno del estudiante',
  ADD COLUMN IF NOT EXISTS apellido_materno VARCHAR(100) NULL COMMENT 'Apellido materno del estudiante',
  ADD COLUMN IF NOT EXISTS id_usuario INT(11) NULL COMMENT 'FK a tabla usuarios para login automático';

-- PASO 3: Crear relación con tabla usuarios (para login automático)
-- Primero, verifica que no exista ya la constraint
-- Si da error 1064 o 1091, ignóralo — significa que ya existe
ALTER TABLE estudiante
  ADD CONSTRAINT IF NOT EXISTS fk_estudiante_usuario
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

-- =====================================================
-- VERIFICACIÓN (ejecuta esto después para confirmar)
-- =====================================================
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_SCHEMA = DATABASE() 
--   AND TABLE_NAME = 'estudiante'
-- ORDER BY ORDINAL_POSITION;
-- =====================================================

-- Fin del script
COMMIT;
