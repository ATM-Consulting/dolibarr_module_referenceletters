<?php
/*
 * Copyright (C) 2014 Florian HENRY <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file referenceletters/referenceletters/info.php
 * \ingroup referenceletters
 * \brief info of referenceletters
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/referenceletters.class.php');
require_once ('../lib/referenceletters.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php');

// Security check
if (! rl_userHasRight($user, 'referenceletters', 'read')) accessforbidden();

$id = GETPOST('id', 'int');

/*
 * View
 */

llxHeader('', $langs->trans("Module103258Name"));

$object = new ReferenceLetters($db);
$object->info($id);

$head = referenceletterPrepareHead($object);

dol_fiche_head($head, 'info', $langs->trans("Module103258Name"), 0, 'bill');

print '<table width="100%"><tr><td>';
dol_print_object_info($object);
print '</td></tr></table>';
print '</div>';

llxFooter();
$db->close();
