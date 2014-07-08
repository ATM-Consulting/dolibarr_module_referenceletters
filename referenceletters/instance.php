<?php
/* References letters
 * Copyright (C) 2014  HENRY Florian  florian.henry@open-concept.pro
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * \file		instance.php
 * \ingroup	refferenceletters
 * \brief		intance pages
 */

// Load environment
$res = 0;
if (! $res && file_exists("../main.inc.php")) {
	$res = @include ("../main.inc.php");
}
if (! $res && file_exists("../../main.inc.php")) {
	$res = @include ("../../main.inc.php");
}
if (! $res && file_exists("../../../main.inc.php")) {
	$res = @include ("../../../main.inc.php");
}
if (! $res) {
	die("Main include failed");
}

require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formadmin.class.php';

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$idletter = GETPOST('idletter', 'int');
$confirm = GETPOST('confirm', 'alpha');
$element_type = GETPOST('element_type', 'alpha');


$object_capters = new Referenceletterschapters($db);
$object_refletter = new Referenceletters($db);

//Check if current view is setup in models letter class
if (! is_array($object_refletter->element_type_list[$element_type])) {
	llxHeader('');
	setEventMessage($langs->trans('RefLtrNoModelReadyForThisObject', $element_type), 'errors');
	llxFooter();
	$db->close();
	exit();
}

// load menu according context (element_type)
require_once $object_refletter->element_type_list[$element_type]['classpath'] . $object_refletter->element_type_list[$element_type]['class'];
require_once $object_refletter->element_type_list[$element_type]['menuloader_lib'];

// Access control
restrictedArea($user, $object_refletter->element_type_list[$element_type]['securityclass'], $id);

// Load translation files required by the page
$langs->load("referenceletters@referenceletters");
$langs->load($object_refletter->element_type_list[$element_type]['trans']);

$error = 0;

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array (
		'referencelettersinstacecard' 
));

/*
 * Actions
*/

$parameters = array ();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks



/*
 * VIEW
*/

$title = $langs->trans($object_refletter->element_type_list[$element_type]['title']) . ' - ' . $langs->trans('Module103258Name');

llxHeader('', $title);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);
$formadmin = new FormAdmin($db);

$now = dol_now();

$object = new $object_refletter->element_type_list[$element_type]['objectclass']($db);
$result=$object->fetch($id);
if ($result < 0) setEventMessage($object->error, 'errors');
// load menu according context (element_type)
$head = call_user_func($object_refletter->element_type_list[$element_type]['menuloader_function'], $object);
dol_fiche_head($head, 'tabReferenceLetters', $object_refletter->element_type_list[$element_type]['title'], 0, $element_type);

//Include a template to display the object
include_once dol_buildpath('/referenceletters/tpl/'.$element_type.'.tpl.php');


print_fiche_titre($title, '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&element_type='.$element_type.'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="selectmodel">';

print '<table class="nobordernopadding">';
print '<tr>';
print '<td>';
print $formrefleter->selectReferenceletters($idletter,'idletter',$element_type);
print '</td>';
print '<td>';
print '<input type="submit" value="'.$langs->trans('RefLtrSelectModel').'" class="button" name="selectmodel">';
print '</td>';
print '</tr>';
print '</table>';

print '</form>';

if (!empty($idletter)) {
	if($action=='selectmodel') {
		
	}
}



// Page end
llxFooter();
$db->close();