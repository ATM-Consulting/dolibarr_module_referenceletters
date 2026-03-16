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

	$linkback = '<a href="' . DOL_URL_ROOT . '/comm/propal/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$soc=$object->thirdparty;

	// Ref
	print '<tr><td>' . $langs->trans('Ref') . '</td><td colspan="5">';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
	print '</td></tr>';

	// Ref customer
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td class="nowrap">';
	print $langs->trans('RefCustomer') . '</td>';
	print '</td>';
	print '</tr></table>';
	print '</td><td colspan="5">';
	print $object->ref_client;
	print '</td>';
	print '</tr>';

	// Company
	print '<tr><td>' . $langs->trans('Company') . '</td><td colspan="5">' . $soc->getNomUrl(1) . '</td>';
	print '</tr>';

	// Statut
	print '<tr><td height="10">' . $langs->trans('Status') . '</td><td align="left" colspan="2">' . $object->getLibStatut(4) . '</td></tr>';

	print '</table><br>';
