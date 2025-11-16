-- =====================================================
-- REVERT Migration: Restore estudiante to original schema
-- =====================================================
-- Use this if you need to undo the changes from alter_estudiante.sql
-- IMPORTANT: This will DROP new columns and recreate the old ones.

-- Step 1: Drop the foreign key constraint
ALTER TABLE estudiante DROP FOREIGN KEY IF EXISTS fk_estudiante_usuario;

-- Step 2: Drop new columns
ALTER TABLE estudiante 
  DROP COLUMN IF EXISTS grado,
  DROP COLUMN IF EXISTS seccion,
  DROP COLUMN IF EXISTS apellido_paterno,
  DROP COLUMN IF EXISTS apellido_materno,
  DROP COLUMN IF EXISTS id_usuario;

-- Step 3: Recreate the old columns (carrera, direccion, telefono)
ALTER TABLE estudiante 
  ADD COLUMN carrera VARCHAR(255) NULL,
  ADD COLUMN direccion TEXT NULL,
  ADD COLUMN telefono VARCHAR(15) NULL;

-- Step 4: Remove UNIQUE constraints if they were added
ALTER TABLE estudiante 
  DROP INDEX IF EXISTS codigo,
  DROP INDEX IF EXISTS dni;

COMMIT;
