ALTER TABLE cr_items ADD COLUMN ticket_url TEXT NULL AFTER ticket_no;
ALTER TABLE cr_items ADD COLUMN figma_url TEXT NULL AFTER ticket_url;
