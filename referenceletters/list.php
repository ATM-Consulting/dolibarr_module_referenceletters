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
 * \file referenceletters/referenceletters/list.php
 * \ingroup referenceletters
 * \brief list of referenceletters
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once '../class/referenceletters.class.php';
require_once '../lib/referenceletters.lib.php';
require_once '../class/html.formreferenceletters.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

// Security check
if (! $user->rights->referenceletters->read)
	accessforbidden();

$langs->load("referenceletters@referenceletters");


$sortorder = GETPOST('sortorder', 'alpha');
$sortfield = GETPOST('sortfield', 'alpha');
$page = GETPOST('page', 'int');


// Search criteria
$search_title = GETPOST("search_title");
$search_element_type = GETPOST("search_element_type");
$search_status = GETPOST("search_status",'int');

// Do we click on purge search criteria ?
if (GETPOST("button_removefilter_x")) {
	$search_title = '';
	$search_element_type = '';
	$search_status=-1;
}

$form = new Form($db);
$object = new Referenceletters($db);
$formrefleter = new FormReferenceLetters($db);

$filter = array();
$option='';
if (! empty($search_title)) {
	$filter['t.title'] = $search_title;
	$option .= '&search_title=' . $search_title;
}
if (! empty($search_element_type)) {
	$filter['t.element_type'] = $search_element_type;
	$option .= '&search_element_type=' . $search_element_type;
}
if (array_key_exists($search_status,$object->TStatus)) {
	$filter['t.status'] = $search_status;
	$option .= '&search_status=' . $search_status;
}
if ($page == - 1) {
	$page = 0;
}

$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (empty($sortorder))
	$sortorder = "ASC";
if (empty($sortfield))
	$sortfield = "t.title";

$title = $langs->trans('RefLtrList');

llxHeader('', $title);

// Count total nb of records
$nbtotalofrecords = 0;

if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $object->fetch_all($sortorder, $sortfield, 0, 0, $filter);
}
$resql = $object->fetch_all($sortorder, $sortfield, $conf->liste_limit, $offset, $filter);

if ($resql != - 1) {
	$num = $resql;

	print_barre_liste($title, $page, $_SERVEUR['PHP_SELF'], $option, $sortfield, $sortorder, '', $num, $nbtotalofrecords);

	print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="search_form">' . "\n";

	if (! empty($sortfield))
		print '<input type="hidden" name="sortfield" value="' . $sortfield . '"/>';
	if (! empty($sortorder))
		print '<input type="hidden" name="sortorder" value="' . $sortorder . '"/>';
	if (! empty($page))
		print '<input type="hidden" name="page" value="' . $page . '"/>';

	$i = 0;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("RefLtrTitle"), $_SERVEUR['PHP_SELF'], "t.title", "", $option, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("RefLtrElement"), $_SERVEUR['PHP_SELF'], "t.element_type", "", $option, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Status"), $_SERVEUR['PHP_SELF'], "t.status", "", $option, '', $sortfield, $sortorder);
	print '<td align="center"></td>';

	print "</tr>\n";

	print '<tr class="liste_titre">';

	print '<td><input type="text" class="flat" name="search_title" value="' . $search_title . '" size="10"></td>';

	print '<td>';
	print $formrefleter->selectElementType($search_element_type, 'search_element_type',1);
	print '</td>';

	print '<td>';
	print $formrefleter->selectStatus($search_status, 'search_status',1);
	print '</td>';

	// edit button
	print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '&nbsp; ';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/searchclear.png" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
	print '</td>';

	print "</tr>\n";
	print '</form>';

	$var = true;

	foreach ($object->lines as $line) {

		// Affichage tableau des lead
		$var = ! $var;
		print "<tr $bc[$var]>";

		// Title
		print '<td><a href="card.php?id=' . $line->id . '">' . $line->title . '</a></td>';

		//Element
		print '<td>' . $object->displayElementElement(0,$line->element_type) . '</td>';

		//Status
		print '<td>' . $langs->trans($object->TStatus[$line->status]) . '</td>';

		print '<td align="center"><a href="card.php?id=' . $line->id . '&action=edit">' . img_picto($langs->trans('Edit'), 'edit') . '</a></td>';

		print "</tr>\n";

	}

	print "</table>";

} else {
	setEventMessage($object->error, 'errors');
}

llxFooter();
$db->close();
