<?php
/* References letters
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
 * \file lead/admin/lead_extrafields.php
 * \ingroup agenda
 * \brief Page to setup extra fields of lead
 */

// Dolibarr environment
$res = @include ("../../main.inc.php"); // From htdocs directory
if (! $res) {
	$res = @include ("../../../main.inc.php"); // From "custom" directory
}
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once "../lib/referenceletters.lib.php";

if (! $user->admin)
	accessforbidden();

$langs->load("admin");
$langs->load("other");
$langs->load("agenda");
$langs->load("referenceletters@referenceletters");

$extrafields = new ExtraFields($db);
$form = new Form($db);

// List of supported format
$tmptype2label = ExtraFields::$type2label;
$type2label = array(
	''
);
foreach ($tmptype2label as $key => $val)
	$type2label[$key] = $langs->trans($val);

$action = GETPOST('action', 'alpha');
$attrname = GETPOST('attrname', 'alpha');
$elementtype = 'referenceletters'; // Must be the $table_element of the class that manage extrafield

if (! $user->admin)
	accessforbidden();

	/*
 * Actions
 */
if (file_exists(DOL_DOCUMENT_ROOT . '/core/admin_extrafields.inc.php'))
	require_once DOL_DOCUMENT_ROOT . '/core/admin_extrafields.inc.php';

if (file_exists(DOL_DOCUMENT_ROOT . '/core/actions_extrafields.inc.php'))
	require_once DOL_DOCUMENT_ROOT . '/core/actions_extrafields.inc.php';

	/*
 * View
 */

$textobject = $langs->transnoentitiesnoconv("Module103258Name");

llxHeader('', $langs->trans("ReferenceLettersSetup"));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans("ReferenceLettersSetup"), $linkback, 'setup');


// Configuration header
$head = referencelettersAdminPrepareHead();
dol_fiche_head($head, 'attributes', $langs->trans("Module103258Name"), 0, "referenceletters@referenceletters");

print $langs->trans("DefineHereComplementaryAttributes", $langs->transnoentitiesnoconv("Module103258Name")) . '<br>' . "\n";
print '<br>';

// Load attribute_label
$extrafields->fetch_name_optionals_label($elementtype);

if(floatval(DOL_VERSION) >= 16) {
	$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attribute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
	if($extrafields->attributes[$elementtype]['loaded'] > 0) {
		$extrafields->attribute_type = $extrafields->attributes[$elementtype]['type'] ?? '';
		$extrafields->attribute_size = $extrafields->attributes[$elementtype]['size'] ?? '';
		$extrafields->attribute_unique = $extrafields->attributes[$elementtype]['unique'] ?? '';
		$extrafields->attribute_required = $extrafields->attributes[$elementtype]['required'] ?? '';
		$extrafields->attribute_label = $extrafields->attributes[$elementtype]['label'] ?? '';
		$extrafields->attribute_default = $extrafields->attributes[$elementtype]['default'] ?? '';
		$extrafields->attribute_computed = $extrafields->attributes[$elementtype]['computed'] ?? '';
		$extrafields->attribute_param = $extrafields->attributes[$elementtype]['param'] ?? '';
		$extrafields->attribute_perms = $extrafields->attributes[$elementtype]['perms'] ?? '';
		$extrafields->attribute_langfile = $extrafields->attributes[$elementtype]['langfile'] ?? '';
		$extrafields->attribute_list = $extrafields->attributes[$elementtype]['list'] ?? '';
		$extrafields->attribute_hidden = $extrafields->attributes[$elementtype]['hidden'] ?? '';
	}
}


print "<table summary=\"listofattributes\" class=\"noborder\" width=\"100%\">";

print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Label") . '</td>';
print '<td>' . $langs->trans("AttributeCode") . '</td>';
print '<td>' . $langs->trans("Type") . '</td>';
print '<td align="right">' . $langs->trans("Size") . '</td>';
print '<td align="center">' . $langs->trans("Unique") . '</td>';
print '<td align="center">' . $langs->trans("Required") . '</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";

$var = True;
$urlToken = '';
if (function_exists('newToken')) $urlToken = "&token=".newToken();
if (!empty($extrafields->attribute_type && is_array($extrafields->attribute_type))){
	foreach ($extrafields->attribute_type as $key => $value) {
		$var = ! $var;
		print "<tr " . $bc[$var] . ">";
		print "<td>" . $extrafields->attribute_label[$key] . "</td>\n";
		print "<td>" . $key . "</td>\n";
		print "<td>" . $type2label[$extrafields->attribute_type[$key]] . "</td>\n";
		print '<td align="right">' . $extrafields->attribute_size[$key] . "</td>\n";
		print '<td align="center">' . yn($extrafields->attribute_unique[$key]) . "</td>\n";
		print '<td align="center">' . yn($extrafields->attribute_required[$key]) . "</td>\n";
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit&attrname=' . $key . '">' . img_edit() . '</a>';
		print "&nbsp; <a href=\"" . $_SERVER["PHP_SELF"] . "?action=delete".$urlToken."&attrname=$key\">" . img_delete() . "</a></td>\n";
		print "</tr>";
	}
}

print "</table>";

dol_fiche_end();

// Buttons
if ($action != 'create' && $action != 'edit') {
	print '<div class="tabsAction">';
	print "<a class=\"butAction\" href=\"" . $_SERVER["PHP_SELF"] . "?action=create\">" . $langs->trans("NewAttribute") . "</a>";
	print "</div>";
}

/* ************************************************************************* */
/*                                                                            */
/* Creation d'un champ optionnel											  */
/*                                                                            */
/* ************************************************************************** */

if ($action == 'create') {
	print "<br>";
	print_titre($langs->trans('NewAttribute'));

	require DOL_DOCUMENT_ROOT . '/core/tpl/admin_extrafields_add.tpl.php';
}

/* ************************************************************************* */
/*                                                                            */
/* Edition d'un champ optionnel                                               */
/*                                                                            */
/* ************************************************************************** */
if ($action == 'edit' && ! empty($attrname)) {
	print "<br>";
	print_titre($langs->trans("FieldEdition", $attrname));

	require DOL_DOCUMENT_ROOT . '/core/tpl/admin_extrafields_edit.tpl.php';
}

llxFooter();

$db->close();
?>
