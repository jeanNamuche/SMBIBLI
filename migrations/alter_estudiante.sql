-- =====================================================
-- Migration: Update estudiante table to new schema
-- =====================================================
-- IMPORTANT: Make a backup of your database before running this!
-- Run these commands in phpMyAdmin or MySQL CLI.

-- Step 1: Remove old unused columns if they exist
-- (carrera, direccion, telefono are no longer used in new schema)
-- If you want to keep them, comment these out:
ALTER TABLE estudiante DROP COLUMN IF EXISTS carrera;
ALTER TABLE estudiante DROP COLUMN IF EXISTS direccion;
ALTER TABLE estudiante DROP COLUMN IF EXISTS telefono;

-- Step 2: Ensure the main identifier fields are set correctly
ALTER TABLE estudiante 
  MODIFY COLUMN codigo VARCHAR(50) NOT NULL UNIQUE,
  MODIFY COLUMN dni VARCHAR(50) NOT NULL UNIQUE;

-- Step 3: Add new columns for grade, section, and name parts
ALTER TABLE estudiante 
  ADD COLUMN IF NOT EXISTS grado VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS seccion VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS apellido_paterno VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS apellido_materno VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS id_usuario INT(11) NULL;

-- Step 4: Create foreign key constraint for id_usuario if not exists
-- First check if constraint exists; if not, add it
ALTER TABLE estudiante 
  ADD CONSTRAINT IF NOT EXISTS fk_estudiante_usuario 
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- Notes:
-- =====================================================
-- - 'nombre' column now holds the full name (nombres)
-- - You may need to populate grado, seccion, apellido_paterno, apellido_materno manually
--   or via a data migration script if you have existing data
-- - The import feature will handle these fields from the Excel file
-- - If you want to revert, see REVERT_MIGRATION.sql

COMMIT;
