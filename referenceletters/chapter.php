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
 *	\file		referenceletters/referenceletters/chapter.php
*	\ingroup	referenceletters
*	\brief		chapter pages
*/

// Load environment
$res = 0;
if (! $res && file_exists("../main.inc.php")) {
	$res = @include("../main.inc.php");
}
if (! $res && file_exists("../../main.inc.php")) {
	$res = @include("../../main.inc.php");
}
if (! $res && file_exists("../../../main.inc.php")) {
	$res = @include("../../../main.inc.php");
}
if (! $res) {
	die("Main include failed");
}

require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';




$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$idletter = GETPOST('idletter', 'int');
$confirm = GETPOST('confirm', 'alpha');

$refltrtitle=GETPOST('refltrtitle','alpha');
$refltrelement_type=GETPOST('refltrelement_type','alpha');

// Access control
// Restrict access to users with invoice reading permissions
restrictedArea($user, 'referenceletters');

// Load translation files required by the page
$langs->load("referenceletters@referenceletters");

$object = new ReferenceLettersChapters($db);
$object_refletter = new Referenceletters($db);


$error = 0;

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'referenceletterschaptercard'
));


/*
 * Actions
*/

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

if ($action == "add") {

	$object->fk_referenceletters=$idletter;
	$object->title = GETPOST('refltrtitle');
	$object->content_text = GETPOST('content_text');
	$object->sort_order=GETPOST('sort_order');
	$object->readonly=GETPOST('refltrreadonly','int');
	$chapter_lang=GETPOST('chapter_lang');
	if (empty($chapter_lang)) {
		$chapter_lang=$langs->defaultlang;
	}
	$object->lang=$chapter_lang;

	$options = GETPOST('option_text');
	if (!empty($options)) {
		 $option_array = explode("\r\n",$options);
	}
	$object->options_text = $option_array;

	$result = $object->create($user);
	if ($result < 0) {
		$action = 'create';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . dol_buildpath('/referenceletters/referenceletters/card.php', 1).'?id='.$object->fk_referenceletters);
	}
} elseif ($action == "update") {
	$result = $object->fetch($id);
	if ($result < 0) {
		$action = 'edit';
		setEventMessage($object->error, 'errors');
	}

	$object->title = GETPOST('refltrtitle');
	$object->content_text = GETPOST('content_text');
	$object->sort_order=GETPOST('sort_order');
	$object->readonly=GETPOST('refltrreadonly','int');
	$chapter_lang=GETPOST('chapter_lang');
	if (empty($chapter_lang)) {
		$chapter_lang=$langs->defaultlang;
	}
	$object->lang=$chapter_lang;

	$options = GETPOST('option_text');
	if (!empty($options)) {
		$option_array = explode("\r\n",$options);
	}
	$object->options_text = $option_array;

	$result = $object->update($user);
	if ($result < 0) {
		$action = 'edit';
		setEventMessage($object->error, 'errors');
	} else {
		$saveandstay=GETPOST('saveandstay');
		if (! empty($saveandstay)) {
			header('Location:' . dol_buildpath('/referenceletters/referenceletters/chapter.php', 1).'?id='.$id.'&action=edit');
		} else {
			header('Location:' . dol_buildpath('/referenceletters/referenceletters/card.php', 1).'?id='.$object->fk_referenceletters);
		}
	}
} elseif ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->referenceletters->delete) {
	$result = $object->fetch($id);
	if ($result < 0) {
		$action = 'delete';
		setEventMessage($object->error, 'errors');
	}
	$result = $object->delete($user);
	if ($result < 0) {
		setEventMessage($object->errors, 'errors');
	} else {
		header('Location:' . dol_buildpath('/referenceletters/referenceletters/card.php', 1).'?id='.$object->fk_referenceletters);
	}
}

/*
 * VIEW
*/

$title = $langs->trans('Module103258Name');
if ($action=='create') {

	if (!empty($idletter)) {
		$result=$object_refletter->fetch($idletter);
		if ($result < 0) {
			setEventMessage($object->error, 'errors');
		}
		$object->fk_referenceletters=$idletter;
	} else {
		setEventMessage('Page call wtih wrong argument', 'errors');
	}


	$subtitle=$langs->trans("RefLtrNewChaters").' - '.$object_refletter->title;
	$button_text='Create';
	$action_next='add';
	$button_text_stay='';


} elseif ($action=='edit' || $action == 'delete') {

	if(!empty($id)) {
		$result=$object->fetch($id);
		if ($result < 0) {
			setEventMessage($object->error, 'errors');
		}
		$result=$object_refletter->fetch($object->fk_referenceletters);
		if ($result < 0) {
			setEventMessage($object->error, 'errors');
		}
	}



	$subtitle=$langs->trans("RefLtrChapters").' - '.$object_refletter->title;

	$button_text='Modify';
	$button_text_stay='RefLtrModifyAndStay';
	$action_next='update';
}

llxHeader('',$title . ' - ' . $subtitle);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);
$formadmin = new FormAdmin($db);

$now = dol_now();
// Add new proposal
if (($action == 'create' || $action=='edit' || $action=='delete') && $user->rights->referenceletters->write) {

	print '<script>';
	print 'function DivStatus( tbl_){' . "\n";
	print '	var Obj = document.getElementById( tbl_);' . "\n";
	print '	if( Obj.style.display=="none"){' . "\n";
	print '		Obj.style.display ="block";' . "\n";
	print '	}' . "\n";
	print '	else{' . "\n";
	print '		Obj.style.display="none";' . "\n";
	print '	}' . "\n";
	print '}' . "\n";
	print '</script>';


	// Confirm form
	$formconfirm = '';
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('RefLtrDeleteChapter'), $langs->trans('RefLtrConfirmDeleteChapter'), 'confirm_delete', '', 0, 1);
	}

	if (empty($formconfirm)) {
		$parameters = array();
		$formconfirm = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	}
	if (!empty($formconfirm)) {
		print $formconfirm;
	}


	print_fiche_titre($subtitle, '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	$linkback = '<a href="' . dol_buildpath('/referenceletters/referenceletters/card.php', 1) . '?id='.$object->fk_referenceletters.'">' . $langs->trans("RefLtrBackToRefLtr") . '</a>';
	print $linkback;

	print '<form name="addreferenceletters" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="idletter" value="'.$idletter.'">';
	print '<input type="hidden" name="action" value="'.$action_next.'">';
	print '<input type="hidden" name="id" value="'.$id.'">';

	print '<table class="border" width="100%">';
	print '<tr>';

	if (! empty($conf->global->MAIN_MULTILANGS))
	{
		print '<td class="fieldrequired"  width="20%">';
		print $langs->trans('RefLtrLangue');
		print '</td>';
		print '<td>';
		if (empty($object->lang)) {
			$object->lang=$langs->defaultlang;
		}
		print $formadmin->select_language($object->lang,'chapter_lang');
		print '</td>';
		print '</tr>';
	}


	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrPosition');
	print '</td>';
	print '<td>';
	if (empty($object->sort_order)) {
		$result=$object->findMaxSortOrder();
		if ($result < 0) {
			setEventMessage($object->error, 'errors');
		} else {
			$object->sort_order=$result;
		}
	}
	print '<input type="text" name="sort_order" size="2" value="' . $object->sort_order . '"/>';
	print '</td>';
	print '</tr>';

	print '<td width="20%">';
	print $langs->trans('RefLtrTag');
	print '</td>';
	print '<td>';
	print '<a href="javascript:DivStatus(\'refltertags\');" title="'.$langs->trans('RefLtrDisplayTag').'" style="font-size:14px;">+</a>';
	print $formrefleter->displaySubtitutionKey($user,$object_refletter);
	print '</td>';
	print '</tr>';

	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrTitle');
	print '</td>';
	print '<td>';
	print '<input type="text" name="refltrtitle" size="20" value="' . $object->title . '"/>';
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrText');
	print '</td>';
	print '<td>';
	require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
	$nbrows=ROWS_2;
	if (! empty($conf->global->MAIN_INPUT_DESC_HEIGHT)) $nbrows=$conf->global->MAIN_INPUT_DESC_HEIGHT;
	$enable=(isset($conf->global->FCKEDITOR_ENABLE_SOCIETE)?$conf->global->FCKEDITOR_ENABLE_SOCIETE:0);
	$doleditor=new DolEditor('content_text', $object->content_text, '', 700, 'dolibarr_notes_encoded', '', false, true, $enable, $nbrows, 70);
	$doleditor->Create();
	print '</td>';
	print '</tr>';

	print '<td width="20%">';
	print $langs->trans('RefLtrOption');
	print '</td>';
	print '<td>';
	print '<table class="nobordernopadding"><tr><td>';
	print '<textarea name="option_text" id="option_text" rows="4" cols="50">';
	if (is_array($object->options_text) && count($object->options_text)>0) {
		foreach($object->options_text as $key=>$option_text) {
			print $option_text."\n";
		}
	}
	print '</textarea>';
	print '</td><td>';
	print $form->textwithpicto('', $langs->trans("RefLtrOptionHelp".$type),1,0);
	print '</td></tr></table>';
	print '</td>';
	print '</tr>';

	print '<td width="20%">';
	print $langs->trans('RefLtrReadOnly');
	print '</td>';
	print '<td>';
	print '<input type="checkbox" name="refltrreadonly" size="20"  '.(!empty($object->readonly)?'checked="checked"':'').' value="1"/>';
	print '</td>';
	print '</tr>';

	print '</table>';

	print '<center>';
	print '<input type="submit" class="butAction" value="' . $langs->trans($button_text) . '">';
	if (!empty($button_text_stay)) {
		print '<input type="submit" class="butAction" name="saveandstay" value="' . $langs->trans($button_text_stay) . '">';
	}
	print '&nbsp;<input type="button" class="butAction" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</center>';

	print '</form>';
}

// Page end
llxFooter();
$db->close();