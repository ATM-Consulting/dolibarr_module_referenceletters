-- References letters
-- Copyright (C) 2014  HENRY Florian  florian.henry@open-concept.pro
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
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

CREATE TABLE IF NOT EXISTS llx_referenceletters (
rowid integer NOT NULL auto_increment PRIMARY KEY,
entity integer NOT NULL DEFAULT 1,
title varchar(100) NOT NULL,
element_type varchar(50) NOT NULL,
use_custom_header integer NOT NULL DEFAULT 0,
header text,
use_custom_footer integer NOT NULL DEFAULT 0,
footer text,
status integer NOT NULL DEFAULT 1,
import_key varchar(100) NULL,
fk_user_author	integer	NOT NULL,
datec	datetime  NOT NULL,
fk_user_mod integer NOT NULL,
tms timestamp NOT NULL
)ENGINE=InnoDB;