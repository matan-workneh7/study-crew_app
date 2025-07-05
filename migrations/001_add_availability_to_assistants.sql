-- Migration to add availability column to assistants table
ALTER TABLE assistants ADD COLUMN IF NOT EXISTS availability TEXT;
