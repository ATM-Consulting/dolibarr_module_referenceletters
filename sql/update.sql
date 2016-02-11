ALTER TABLE llx_referenceletters_chapters ADD COLUMN readonly integer NOT NULL DEFAULT 0 AFTER options_text;
ALTER TABLE llx_referenceletters_elements ADD COLUMN title varchar(255) NULL AFTER ref_int;
ALTER TABLE llx_referenceletters_elements ADD COLUMN outputref integer DEFAULT 1 AFTER title;