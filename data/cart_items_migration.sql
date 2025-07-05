-- Migration to add status and is_hidden columns to cart_items table
-- Run this SQL to add the necessary columns for cart status management

ALTER TABLE cart_items 
ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER condition_snapshot,
ADD COLUMN is_hidden TINYINT(1) DEFAULT 0 AFTER status;

-- Update existing records to have default values
UPDATE cart_items SET status = 'active', is_hidden = 0 WHERE status IS NULL OR is_hidden IS NULL;

-- Add index for better performance on hidden items
CREATE INDEX idx_cart_items_status_hidden ON cart_items(status, is_hidden);

-- Optional: Add comment to document the purpose
ALTER TABLE cart_items COMMENT 'Cart items with status tracking - active/sold and visibility control';
