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


ALTER TABLE llx_referenceletters_elements ADD INDEX idx_referenceletters_elements_fk_referenceletters (fk_referenceletters);
ALTER TABLE llx_referenceletters_elements ADD CONSTRAINT ibfk_referenceletters_elements_fk_referenceletters FOREIGN KEY (fk_referenceletters) REFERENCES llx_referenceletters (rowid);
