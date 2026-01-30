<?php
/* Copyright (C) 2025 ATM Consulting
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
print '<table class="border" width="100%">';

$linkback = '<a href="' . DOL_URL_ROOT . '/contact/list.php?restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>';

// Ref
print '<tr><td width="20%">' . $langs->trans("Ref") . '</td><td colspan="3">';
print $form->showrefnav($object, 'id', $linkback);
print '</td></tr>';

// Name
print '<tr><td width="20%">' . $langs->trans("Lastname") . ' / ' . $langs->trans("Label") . '</td><td width="30%">' . $object->lastname . '</td>';
print '<td width="20%">' . $langs->trans("Firstname") . '</td><td width="30%">' . $object->firstname . '</td></tr>';

// Company
if (!getDolGlobalString('SOCIETE_DISABLE_CONTACTS')) {
	if ($object->socid > 0) {
		$objsoc = new Societe($db);
		$objsoc->fetch($object->socid);

		print '<tr><td>' . $langs->trans("Company") . '</td><td colspan="3">' . $objsoc->getNomUrl(1) . '</td></tr>';
	}

	else {
		print '<tr><td>' . $langs->trans("Company") . '</td><td colspan="3">';
		print $langs->trans("ContactNotLinkedToCompany");
		print '</td></tr>';
	}
}

// Civility
print '<tr><td>' . $langs->trans("UserTitle") . '</td><td colspan="3">';
print $object->getCivilityLabel();
print '</td></tr>';
print '</table>';
