<?php
/*
 * Copyright (C) 2016 Florian Henry <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file /referenceletters/referenceletters/background.php
 * \ingroup referenceletters
 */

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

$langs->load("companies");
$langs->load('other');

$action = GETPOST('action', 'none');
$confirm = GETPOST('confirm', 'none');
$id = GETPOST('id', 'int');

// Access control
// Restrict access to users with invoice reading permissions
restrictedArea($user, 'referenceletters');

// Load translation files required by the page
$langs->load("referenceletters@referenceletters");

$object = new ReferenceLetters($db);
if(!empty($id)) {
	$result=$object->fetch($id);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	}
}


$extrafields = new ExtraFields($db);

$error = 0;

$upload_dir=$conf->referenceletters->dir_output.'/referenceletters/'. $object->id;
$relativepathwithnofile="referenceletters/" . $object->id.'/';


// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'referencelettersbackground'
));


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks



/*
 * Actions
 */
$permissiontoadd = $user->rights->referenceletters->write;
include_once DOL_DOCUMENT_ROOT . '/core/actions_linkedfiles.inc.php';

/*
 * View
 */

$title = $langs->trans('Module103258Name').'-'.$langs->trans('RefLtrBackground');

llxHeader('',$title);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);

if ($object->id) {
	/*
	 * Affichage onglets
	 */
	if (! empty($conf->notification->enabled)) {
		$langs->load("mails");
	}

	$head = referenceletterPrepareHead($object);
	dol_fiche_head($head, 'background', $langs->trans('RefLtrBackground'), 0, dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);


	// Construit liste des fichiers
	$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
	$totalsize = 0;
	foreach ( $filearray as $key => $file ) {
		$totalsize += $file['size'];
	}

	$linkback = '<a href="' . dol_buildpath('/referenceletters/referenceletters/list.php', 1) . '">' . $langs->trans("BackToList") . '</a>';
	print $linkback;

	print '<table class="border" width="100%">';
	print '<tr>';
	print '<td  width="20%">';
	print $langs->trans("RefLtrTitle");
	print '</td><td>';
	print $object->title;
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td width="20%">';
	print $langs->trans('RefLtrElement');
	print '</td>';
	print '<td>';
	print $object->displayElement();
	print '</td>';
	print '</tr>';

	// Other attributes
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook

	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields);
	}

	print '</table>';

	//print
	print info_admin($langs->trans('RefLtrBackgroundHelp'));

	if (empty($conf->global->MAIN_DISABLE_FPDI)) {
		$modulepart = 'referenceletters';
		$permission = ($user->rights->referenceletters->write);
		$param = '&id=' . $object->id;
		include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
	}else {
		setEventMessages($langs->trans('MAIN_DISABLE_FPDI is on, this option cannot work, ask to your admnistrator'),null,'errors');
	}



} else {
	accessforbidden('', 0, 0);
}

llxFooter();
$db->close();
