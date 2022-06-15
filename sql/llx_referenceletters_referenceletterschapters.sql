-- Copyright (C) 2022 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_referenceletters_referenceletterschapters(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_referenceletters integer NOT NULL, 
	lang varchar(5) NOT NULL, 
	sort_order integer DEFAULT 1 NOT NULL, 
	title varchar(100) NOT NULL, 
	content_text text NOT NULL, 
	options_text text, 
	readonly boolean, 
	same_page boolean, 
	import_key varchar(14), 
	fk_user_creat integer NOT NULL, 
	date_creation datetime NOT NULL, 
	fk_user_modif integer, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    entity integer DEFAULT 1 NOT NULL
    -- END MODULEBUILDER FIELDS
) ENGINE=innodb;
