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
INSERT INTO llx_c_actioncomm (id, code, type, libelle, module, active, todo, position) VALUES (1032581, 'AC_LTR_DOC', 'referenceletters', 'Documents', 'referenceletters', 1, NULL, 100);
ALTER TABLE llx_actioncomm MODIFY COLUMN elementtype varchar(255) DEFAULT NULL;