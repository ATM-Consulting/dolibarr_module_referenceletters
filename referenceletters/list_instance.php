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
 * \file referenceletters/referenceletters/list_instance.php
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
require_once '../class/referenceletterselements.class.php';
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
$search_ref_int = GETPOST("search_ref_int");
$search_element_type = GETPOST("search_element_type");
$search_title = GETPOST("search_title");
$search_company = GETPOST("search_company");
$search_ref = GETPOST("search_ref");

$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;

if ($limit > 0 && $limit != $conf->liste_limit) $options.='&limit='.$limit;
// Do we click on purge search criteria ?
if (GETPOST("button_removefilter_x")) {
	$search_ref_int = '';
	$search_element_type = '';
}

$filter = array ();
if (! empty($search_ref_int)) {
	$filter['t.ref_int'] = $search_ref_int;
	$options .= '&amp;search_ref_int=' . $search_ref_int;
}
if (! empty($search_element_type)) {
	$filter['t.element_type'] = $search_element_type;
	$options .= '&amp;search_element_type=' . $search_element_type;
}
if (! empty($search_title)) {
	$filter['t.title'] = $search_title;
	$options .= '&amp;search_title=' . $search_title;
}
if (! empty($search_company)) {
	$filter['search_company'] = $search_company;
	$options .= '&amp;search_company=' . $search_company;
}
if (! empty($search_ref)) {
	$filter['search_ref'] = $search_ref;
	$options .= '&amp;search_ref=' . $search_ref;
}

if ($page == - 1) {
	$page = 0;
}

$offset = $limit* $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$form = new Form($db);
$object = new ReferenceLettersElements($db);
$object_ref = new ReferenceLetters($db);
$formrefleter = new FormReferenceLetters($db);

if (empty($sortorder)) {
	$sortorder = "ASC";
}
if (empty($sortfield)) {
	$sortfield = "t.datec";
}

$title = $langs->trans('RefLtrListInstance');

llxHeader('', $title);

$result = 

// Count total nb of records
$nbtotalofrecords = 0;

if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $object->fetchAll($sortorder, $sortfield, 0, 0, $filter);
}

$num = $object->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);

if ($num != - 1) {
	print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="search_form">' . "\n";
	
	print_barre_liste($title, $page, $_SERVEUR['PHP_SELF'], $options, $sortfield, $sortorder, '', $num, $nbtotalofrecords,'title_generic.png',0, '', '', $limit);
	
	if (! empty($sortfield))
		print '<input type="hidden" name="sortfield" value="' . $sortfield . '"/>';
	if (! empty($sortorder))
		print '<input type="hidden" name="sortorder" value="' . $sortorder . '"/>';
	if (! empty($page))
		print '<input type="hidden" name="page" value="' . $page . '"/>';
	
	$i = 0;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("RefLtrRef"), $_SERVEUR['PHP_SELF'], "t.ref_int", "", $options, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("RefLtrElement"), $_SERVEUR['PHP_SELF'], "t.element_type", "", $options, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("RefLtrTitle"), $_SERVEUR['PHP_SELF'], "t.title", "", $options, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Ref"), $_SERVEUR['PHP_SELF'], "t.fk_element", "", $options, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Company"), $_SERVEUR['PHP_SELF'], "", "", $options, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("RefLtrDatec"), $_SERVEUR['PHP_SELF'], "t.datec", "", $options, '', $sortfield, $sortorder);
	print "</tr>\n";
	
	print '<tr class="liste_titre">';
	
	print '<td><input type="text" class="flat" name="search_ref_int" value="' . $search_ref_int . '" size="10"></td>';
	
	print '<td>';
	print $formrefleter->selectElementType($search_element_type, 'search_element_type', 1);
	print '</td>';
	
	print '<td>';
	print '<input type="text" class="flat" name="search_title" value="' . $search_title . '" size="10">';
	print '</td>';
	
	print '<td><input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="10"></td>';
	
	print '<td>';
	print '<input type="text" class="flat" name="search_company" value="' . $search_company . '" size="10">';
	print '</td>';
	
	// edit button
	print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '&nbsp; ';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/searchclear.png" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
	print '</td>';
	
	print "</tr>\n";
	print '</form>';
	
	$var = true;
	
	foreach ( $object->lines as $line ) {
		
		// Affichage tableau des lead
		$var = ! $var;
		print "<tr $bc[$var]>";
		
		// Title
		print '<td><a href="' . dol_buildpath('referenceletters/referenceletters/instance.php', 1) . '?id=' . $line->fk_element . '&element_type=' . $line->element_type . '">' . $line->ref_int . '</a></td>';
		
		// Element
		require_once $object_ref->element_type_list[$line->element_type]['classpath'] . $object_ref->element_type_list[$line->element_type]['class'];
		$object_src = new $object_ref->element_type_list[$line->element_type]['objectclass']($db);
		
		$result = $object_src->fetch($line->fk_element);
		if ($result < 0) {
			setEventMessage($object_src->error, 'errors');
		}
		if (method_exists($object_src, 'fetch_thirdparty')) {
			$result = $object_src->fetch_thirdparty();
			if ($result < 0) {
				setEventMessage($object_src->error, 'errors');
			}
		}
		
		print '<td>' . $object_ref->displayElementElement(0, $line->element_type) . '</a></td>';
		
		print '<td>' . $line->title . '</a></td>';
		
		if ($object_ref->element_type_list[$line->element_type]['objectclass'] == 'Societe') {
			print '<td><a href="' . dol_buildpath('societe/soc.php', 1) . '?socid=' . $object_src->id . '">' . $object_src->getNomUrl() . '</a></td>';
		} else if($object_ref->element_type_list[$line->element_type]['objectclass'] == 'Contact'){
			print '<td><a href="' . dol_buildpath($object_ref->element_type_list[$line->element_type]['card'], 1) . '?id=' . $line->fk_element . '">' . $object_src->getNomUrl() . '</a></td>';
		}else {
			
			print '<td><a href="' . dol_buildpath($object_ref->element_type_list[$line->element_type]['card'], 1) . '?id=' . $line->fk_element . '">' . $object_src->ref . '</a></td>';
		}
		
		if ($object_ref->element_type_list[$line->element_type]['objectclass'] == 'Societe') {
			print '<td><a href="' . dol_buildpath('societe/soc.php', 1) . '?socid=' . $object_src->id . '">' .$object_src->getNomUrl() . '</a></td>';
		} else {
			print '<td><a href="' . dol_buildpath('societe/soc.php', 1) . '?socid=' . $object_src->thirdparty->id . '">' . $object_src->thirdparty->getNomUrl() . '</a></td>';
		}
		
		print '<td>' . dol_print_date($line->datec) . '</a></td>';
		
		print "</tr>\n";
	}
	
	print "</table>";
} else {
	setEventMessages($object->error, $object->errors, 'errors');
}

llxFooter();
$db->close();
