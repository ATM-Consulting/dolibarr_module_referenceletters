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

$linkback = '<a href="' . DOL_URL_ROOT . '/commande/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

$soc = $object->thirdparty;

// Ref
print '<tr><td width="20%">' . $langs->trans('Ref') . '</td>';
print '<td colspan="5">';
print $form->showrefnav($object, 'ref', $linkback ?? '', 1, 'ref', 'ref', $morehtmlref ?? '');
print '</td></tr>';

// Ref customer
print '<tr><td width="20%">';
print '<table class="nobordernopadding" width="100%"><tr><td>';
print $langs->trans('RefCustomer');
print '</td>';
print '</tr></table>';
print '</td>';
print '<td>';
print $object->ref_client;
print '</td></tr>';

// Third party
print '<tr><td>';
print '<table class="nobordernopadding" width="100%">';
print '<tr><td>' . $langs->trans('Company') . '</td>';
print '</td><td>';
print '</tr></table>';
print '</td><td>';
print ' &nbsp;' . $soc->getNomUrl(1, 'compta');
print ' &nbsp; ';
print '(<a href="' . DOL_URL_ROOT . '/commande/list.php?socid=' . $object->socid . '">' . $langs->trans('OtherOrders') . '</a>)';
print '</tr>';

// Date
print '<tr><td>';
print $langs->trans('Date');
print '</td></tr>';


// Statut
print '<tr><td>' . $langs->trans('Status') . '</td><td>' . $object->getLibStatut(4) . '</td></tr>';


print '</table><br>';
