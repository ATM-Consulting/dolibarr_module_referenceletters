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


CREATE TABLE llx_referenceletters_referenceletters(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	element_type varchar(50) NOT NULL, 
	title varchar(100) NOT NULL, 
	use_landscape_format integer DEFAULT 1, 
	default_doc integer DEFAULT 1, 
	status integer DEFAULT 1 NOT NULL, 
	header text, 
	footer text, 
	use_custom_header integer NOT NULL, 
	use_custom_footer integer NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	date_creation datetime, 
	import_key varchar(100), 
	fk_user_creat integer,
	fk_user_modif integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
