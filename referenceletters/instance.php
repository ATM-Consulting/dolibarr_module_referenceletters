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
 * \file instance.php
 * \ingroup referenceletters
 * \brief intance pages
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
require_once '../class/referenceletterselements.class.php';
require_once '../class/referenceletters_tools.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';
require_once '../core/modules/referenceletters/modules_referenceletters.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$idletter = GETPOST('idletter', 'int');
$confirm = GETPOST('confirm', 'alpha');
$element_type = GETPOST('element_type', 'alpha');
$refletterelemntid = GETPOST('refletterelemntid', 'int');
$justinformme = GETPOST('justinformme');

$sortfield=GETPOST('sortfield','alpha');
$sortorder=GETPOST('sortorder','alpha');

$object_chapters = new ReferencelettersChapters($db);
$object_element = new ReferenceLettersElements($db);
$object_refletter = new Referenceletters($db);
$object_refletter->fetch($idletter);

// Load translation files required by the page
$langs->load("referenceletters@referenceletters");

// Check if current view is setup in models letter class
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
restrictedArea($user, $object_refletter->element_type_list[$element_type]['securityclass'], $id, $object_refletter->element_type_list[$element_type]['securityfeature'], $element_type === 'order_supplier' ? 'commande' : '');

// Load translation files required by the page
$langs->load($object_refletter->element_type_list[$element_type]['trans']);

$error = 0;

$object = new $object_refletter->element_type_list[$element_type]['objectclass']($db);

$result = $object->fetch($id);
if ($result < 0)
	setEventMessage($object->error, 'errors');
if (method_exists($object, 'fetch_thirdparty')) {
	$result = $object->fetch_thirdparty();
	if ($result < 0)
		setEventMessage($object->error, 'errors');
}
//Needed for hook builddoc
$object_element->srcobject=$object;

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array (
		'referencelettersinstacecard'
));

/*
 * Actions
 */

$parameters = array ();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

if ($action == 'buildoc') {

	// New letter
	if (empty($refletterelemntid)) {

		$ref_int = GETPOST('ref_int','alpha');
		if(empty($ref_int)) $ref_int = $object_element->getNextNumRef($object->thirdparty, $user->id, $element_type);

		// Save data
		$object_element->ref_int = $ref_int;
		$object_element->title = GETPOST('title_instance');
		$object_element->fk_element = $object->id;
		$object_element->element_type = $element_type;
		$object_element->fk_referenceletters = $idletter;
		$object_element->outputref = GETPOST('outputref','int');
		$object_element->use_custom_header = GETPOST('use_custom_header');
		$object_element->header = RfltrTools::setImgLinkToUrl(GETPOST('header'));
		$object_element->use_custom_footer = GETPOST('use_custom_footer');
		$object_element->footer = RfltrTools::setImgLinkToUrl(GETPOST('footer'));
		$object_element->use_landscape_format = GETPOST('use_landscape_format');

		if (empty($langs_chapter) && ! empty($conf->global->MAIN_MULTILANGS)) {
			$langs_chapter = $object->thirdparty->default_lang;
		}
		if (empty($langs_chapter)) {
			$langs_chapter = $langs->defaultlang;
		}

		$result = $object_chapters->fetch_byrefltr($idletter, $langs_chapter);
		if ($result < 0) {
			if($justinformme) echo $object_element->error;
			else setEventMessage($object_chapters->error, 'errors');
		}

		// Use a big array into class it is serialize
		$content_letter = array ();
		if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {
			foreach ( $object_chapters->lines_chapters as $key => $line_chapter ) {

				$options = array ();
				if (is_array($line_chapter->options_text) && count($line_chapter->options_text) > 0) {
					foreach ( $line_chapter->options_text as $key => $option_text ) {
						$options[$key] = array (
								'use_content_option' => GETPOST('use_content_option_' . $line_chapter->id . '_' . $key),
								'text_content_option' => GETPOST('text_content_option_' . $line_chapter->id . '_' . $key)
						);
					}
				}

				$content_letter[$line_chapter->id] = array (
						'content_text' => RfltrTools::setImgLinkToUrl(GETPOST('content_text_' . $line_chapter->id)),
						'options' => $options
				);
			}
		}
		elseif($justinformme){

			echo $langs->trans('NoContentChapterForLang', $langs_chapter);
			exit;
		}

		$object_element->content_letter = $content_letter;

		$result = $object_element->create($user);
		if ($result < 0) {
			if($justinformme) echo $object_element->error;
			else setEventMessage($object_element->error, 'errors');
		}

		$object_element->fetch($result);
		$refletterelemntid = $object_element->id;

	} else {
		// Edit letter
		$result = $object_element->fetch($refletterelemntid);
		if ($result < 0) {
			setEventMessage($object_element->error, 'errors');
		}

		$object_element->title = GETPOST('title_instance');
		$object_element->outputref = GETPOST('outputref','int');
		$object_element->use_custom_header = GETPOST('use_custom_header');
		$object_element->header = RfltrTools::setImgLinkToUrl(GETPOST('header'));
		$object_element->use_custom_footer = GETPOST('use_custom_footer');
		$object_element->footer = RfltrTools::setImgLinkToUrl(GETPOST('footer'));
		$object_element->use_landscape_format = GETPOST('use_landscape_format');


		if (! empty($conf->global->MAIN_MULTILANGS)) {
			$langs_chapter = $object->thirdparty->default_lang;
		}
		if (empty($langs_chapter))
			$langs_chapter = $langs->defaultlang;

		$result = $object_chapters->fetch_byrefltr($idletter, $langs_chapter);
		if ($result < 0)
			setEventMessage($object_chapters->error, 'errors');

			// Use a big array into class it is serialize
		$content_letter = array ();
		if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {
			foreach ( $object_chapters->lines_chapters as $key => $line_chapter ) {

				$options = array ();
				if (is_array($line_chapter->options_text) && count($line_chapter->options_text) > 0) {
					foreach ( $line_chapter->options_text as $key => $option_text ) {
						$options[$key] = array (
								'use_content_option' => GETPOST('use_content_option_' . $line_chapter->id . '_' . $key),
								'text_content_option' => GETPOST('text_content_option_' . $line_chapter->id . '_' . $key)
						);
					}
				}

				$content_letter[$line_chapter->id] = array (
						'content_text' => RfltrTools::setImgLinkToUrl(GETPOST('content_text_' . $line_chapter->id)),
						'options' => $options
				);
			}
		}

		$object_element->content_letter = $content_letter;

		$result = $object_element->update($user);
		if ($result < 0)
			setEventMessage($object_element->error, 'errors');
	}

	// Create document PDF

	// Define output language
	$outputlangs = $langs;
	if (! empty($conf->global->MAIN_MULTILANGS)) {
		$outputlangs = new Translate("", $conf);
		$newlang = $object->thridparty->default_lang;
		$outputlangs->setDefaultLang($newlang);
	}

	// Reload to get new records
	$ret = $object_element->fetch($refletterelemntid);
	$result = referenceletters_pdf_create($db, $object, $object_element, $outputlangs, $element_type);

	if ($result <= 0) {
		dol_print_error($db, $result);
		exit();
	} else {

		if($justinformme) {
			echo 1;
		}
		else{
			header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&element_type=' . $element_type);
		}

		exit();
	}
} elseif ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->referenceletters->delete) {
	$result = $object_element->fetch($refletterelemntid);
	if ($result < 0) {
		setEventMessage($object_element->error, 'errors');
	} else {
		$result = $object_element->delete($user);
		if ($result < 0) {
			setEventMessage($object_element->errors, 'errors');
		} else {
			header('Location:' . dol_buildpath('/referenceletters/referenceletters/instance.php', 1) . '?id=' . $object->id . '&element_type=' . $element_type);
		}
	}
}
/*
 * VIEW
 */

$title = $langs->trans($object_refletter->element_type_list[$element_type]['title']) . ' - ' . $langs->trans('Module103258Name');

llxHeader('', $title);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);
$formadmin = new FormAdmin($db);
$formfile = new FormFile($db);

$now = dol_now();

// load menu according context (element_type)
$head = call_user_func($object_refletter->element_type_list[$element_type]['menuloader_function'], $object);
if (array_key_exists('picto', $object_refletter->element_type_list[$element_type])) {
	$picto=$object_refletter->element_type_list[$element_type]['picto'];
} else {
	$picto=$element_type;
}
dol_fiche_head($head, 'tabReferenceLetters', $langs->trans($object_refletter->element_type_list[$element_type]['title']), -1, $picto);

// Include a template to display the object
include_once dol_buildpath('/referenceletters/tpl/' . $element_type . '.tpl.php');

// Display existing letter already created
print load_fiche_titre($langs->trans('RefLtrExistingLetters'), '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

// Confirm form
$formconfirm = '';
if ($action == 'delete') {
	$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&element_type=' . $element_type . '&refletterelemntid=' . $refletterelemntid, $langs->trans('RefLtrDeleteLetter'), $langs->trans('RefLtrConfirmDeleteLetter'), 'confirm_delete', '', 0, 1);
}

if (empty($formconfirm)) {
	$parameters = array ();
	$formconfirm = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
}
if (!empty($formconfirm)) {
	print $formconfirm;
}

$options='&amp;element_type='.$element_type.'&amp;id='.$id;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print_liste_field_titre($langs->trans("RefLtrRef"), $_SERVEUR['PHP_SELF'], "t.ref_int", "", $options, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("RefLtrTitle"), $_SERVEUR['PHP_SELF'], "t.title", "", $options, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("RefLtrTitleModel"), $_SERVEUR['PHP_SELF'], "p.title", "", $options, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("RefLtrDatec"), $_SERVEUR['PHP_SELF'], "t.element_type", "", $options, '', $sortfield, $sortorder);
print '<th></th>';
print '<th></th>';

$result = $object_element->fetchAllByElement($id, $element_type, $sortorder, $sortfield);
if ($result < 0)
	setEventMessage($object_element->error, 'errors');

if (is_array($object_element->lines) && count($object_element->lines) > 0) {
	foreach ( $object_element->lines as $line ) {

		// Affichage tableau des lead
		$var = ! $var;
		print "<tr $bc[$var]>";

		// Ref int
		print '<td><a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&element_type=' . $element_type . '&refletterelemntid=' . $line->id . '&action=edit">' . $line->ref_int . '</a></td>';

		// title
		print '<td>' . $line->title . '</td>';

		// title
		print '<td>' . $line->title_referenceletters . '</td>';

		print '<td>' . dol_print_date($line->datec, 'daytext') . '</td>';

		// File
		print '<td>';
		$filename = dol_sanitizeFileName($line->ref_int);
		$filedir = $conf->referenceletters->dir_output . "/" . $element_type . "/" . $line->ref_int;
		$linkeddoc = $formfile->getDocumentsLink('referenceletters', $filename, $filedir);
		$linkeddoc = preg_replace('/file=/', 'file=' . $element_type . '%2F', $linkeddoc);
		// var_dump($linkeddoc);
		print $linkeddoc;
		print '</td>';

		print '<td>';
		print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&element_type=' . $element_type . '&refletterelemntid=' . $line->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
		print '</td>';
		print "</tr>\n";
	}
}

print '</table>';

print_fiche_titre($langs->trans('RefLtrNewLetters'), '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&element_type=' . $element_type . '" method="POST">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="selectmodel">';

print '<table class="nobordernopadding">';
print '<tr>';
print '<td>';
print $formrefleter->selectReferenceletters($idletter, 'idletter', $element_type);
print '</td>';
print '<td>';
print '<input type="submit" value="' . $langs->trans('RefLtrSelectModel') . '" class="button" name="selectmodel">';
print '</td>';
print '</tr>';
print '</table>';

print '</form>';


// New letter
if (! empty($idletter)) {
	if ($action == 'selectmodel') {
		if (! empty($conf->global->MAIN_MULTILANGS)) {
			$langs_chapter = $object->thirdparty->default_lang;
		}
		if (empty($langs_chapter))
			$langs_chapter = $langs->defaultlang;

		$result = $object_chapters->fetch_byrefltr($idletter, $langs_chapter);
		if ($result < 0)
			setEventMessage($object_chapters->error, 'errors');

		print_fiche_titre($langs->trans("RefLtrChapters"), '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

		print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&element_type=' . $element_type . '" method="POST">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="buildoc">';
		print '<input type="hidden" name="idletter" value="' . $idletter . '">';

		print '<table class="border" width="100%">';
		if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrRef');
			print '</td>';
			print '<td>';
			$ref_int = $object_element->getNextNumRef($object->thirdparty, $user->id, $element_type);
			print $ref_int;
			print '<input type="hidden" name="ref_int" value="' . $ref_int . '">';
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrTitle');
			print '</td>';
			print '<td>';
			print '<input type="text" class="flat" name="title_instance" id="title_instance" size="30" value="' . GETPOST('title_instance') . '">';
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrUseLandscapeFormat');
			print '</td>';
			print '<td>';
			print $form->selectyesno('use_landscape_format', $object_refletter->use_landscape_format, 1);
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrREF_LETTER_OUTPUTREFLET');
			print '</td>';
			print '<td>';
			print '<input type="checkbox" class="flat" name="outputref" '.(!empty($conf->global->REF_LETTER_OUTPUTREFLET)?'checked="checked"':'').' id="outputref" value="1">';
			print '</td>';
			print '</tr>';

			print '<tr style="background-color:#CEECF5;">';
			print '<td>';
			print $langs->trans('RefLtrUseCustomHeader');
			print '</td>';
			print '<td><input type="checkbox" name="use_custom_header" id="use_custom_header" value="1" '.(!empty($object_refletter->use_custom_header) ? 'checked="checked"' : '').' />';
			print '</td>';
			print '</tr>';

			print '<tr class="wysiwyg_header" '.(empty($object_refletter->use_custom_header) ? 'style="display:none;background-color:#CEECF5;"' : 'style="background-color:#CEECF5;"').'>';
			print '<td>'.$langs->trans('RefLtrHeaderContent');
			print '</td>';
			print '<td>';
			$doleditor=new DolEditor('header', $object_refletter->header, '', 150, 'dolibarr_notes_encoded', '', false, true, 1, $nbrows, 70);
			$doleditor->Create();
			print '</td>';
			print '</tr>';

			foreach ( $object_chapters->lines_chapters as $key => $line_chapter ) {
				if ($line_chapter->content_text == '@breakpage@') {
					print '<tr><td colspan="2" style="text-align:center;font-weight:bold">';
					print '<input type="hidden" name="content_text_' . $line_chapter->id . '" value="' . $line_chapter->content_text . '"/>';
					print $langs->trans('RefLtrPageBreak');
					print '</td></tr>';
				}elseif ($line_chapter->content_text == '@breakpagenohead@') {
					print '<tr><td colspan="2" style="text-align:center;font-weight:bold">';
					print '<input type="hidden" name="content_text_' . $line_chapter->id . '" value="' . $line_chapter->content_text . '"/>';
					print $langs->trans('RefLtrAddPageBreakWithoutHeader');
					print '</td></tr>';
				} else {
					print '<tr style="'.(!empty($line_chapter->readonly)?'display:none':'').'">';
					print '<td  width="20%">';
					print $langs->trans('RefLtrText');
					print '</td>';
					print '<td>';

					$nbrows = ROWS_2;
					if (! empty($conf->global->MAIN_INPUT_DESC_HEIGHT))
						$nbrows = $conf->global->MAIN_INPUT_DESC_HEIGHT;
					$enable = (isset($conf->global->FCKEDITOR_ENABLE_SOCIETE) ? $conf->global->FCKEDITOR_ENABLE_SOCIETE : 0);
					$doleditor = new DolEditor('content_text_' . $line_chapter->id, $line_chapter->content_text, '', 150, 'dolibarr_notes_encoded', '', false, true, $enable, $nbrows, 70);
					$doleditor->Create();
					print '</td>';
					print '</tr>';

					print '<tr style="'.(!empty($line_chapter->readonly)?'display:none':'').'">';
					print '<td  width="20%">';
					print $langs->trans('RefLtrOption');
					print '</td>';
					print '<td>';
					if (is_array($line_chapter->options_text) && count($line_chapter->options_text) > 0) {
						foreach ( $line_chapter->options_text as $key => $option_text ) {
							if (! empty($option_text)) {
								print '<input type="checkbox" checked="checked" name="use_content_option_' . $line_chapter->id . '_' . $key . '" value="1"><input type="texte class="flat" size="20" name="text_content_option_' . $line_chapter->id . '_' . $key . '" value="' . $option_text . '" ><br>';
							}
						}
					}
					print '</td>';
					print '</tr>';
				}
			}



			print '<tr style="background-color:#CEF6CE;">';
			print '<td>';
			print $langs->trans('RefLtrUseCustomFooter');
			print '</td>';
			print '<td><input type="checkbox" name="use_custom_footer" id="use_custom_footer" value="1" '.(!empty($object_refletter->use_custom_footer) ? 'checked="checked"' : '').' />';
			print '</td>';
			print '</tr>';

			print '<tr class="wysiwyg_footer" '.(empty($object_refletter->use_custom_footer) ? 'style="display:none;background-color:#CEF6CE;"' : 'style="background-color:#CEF6CE;"').'>';
			print '<td>'.$langs->trans('RefLtrFooterContent');
			print '</td>';
			print '<td>';
			$doleditor=new DolEditor('footer', $object_refletter->footer, '', 150, 'dolibarr_notes_encoded', '', false, true, 1, $nbrows, 70);
			$doleditor->Create();
			print '</td>';
			print '</tr>';

			print '<td colspan="2" align="center">';
			print '<input type="submit" value="' . $langs->trans('RefLtrCreateDoc') . '" class="button" name="createdoc">';
			print '</td>';
		}
		print '</table>';

		print '</form>';
	}
}
// Edit existing letter
if (! empty($refletterelemntid)) {
	if ($action == 'edit') {
		$result = $object_element->fetch($refletterelemntid);
		if ($result < 0) {
			setEventMessage($object_element->error, 'errors');
		} else {

			// Edit a existing letter
			print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&element_type=' . $element_type . '" method="POST">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="buildoc">';
			print '<input type="hidden" name="idletter" value="' . $object_element->fk_referenceletters . '">';
			print '<input type="hidden" name="refletterelemntid" value="' . $object_element->id . '">';

			print '<table class="border" width="100%">';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrRef');
			print '</td>';
			print '<td>';
			print $object_element->ref_int;
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrTitle');
			print '</td>';
			print '<td>';
			print '<input type="text" class="flat" name="title_instance" id="title_instance" size="30" value="' . $object_element->title . '">';
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrUseLandscapeFormat');
			print '</td>';
			print '<td>';
			print $form->selectyesno('use_landscape_format', $object_element->use_landscape_format, 1);
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td  width="20%">';
			print $langs->trans('RefLtrREF_LETTER_OUTPUTREFLET');
			print '</td>';
			print '<td>';
			print '<input type="checkbox" class="flat" name="outputref" '.(!empty($object_element->outputref)?'checked="checked"':'').' id="outputref" value="1">';
			print '</td>';
			print '</tr>';

			print '<tr style="background-color:#CEECF5;">';
			print '<td>';
			print $langs->trans('RefLtrUseCustomHeader');
			print '</td>';
			print '<td><input type="checkbox" name="use_custom_header" id="use_custom_header" value="1" '.(!empty($object_element->use_custom_header) ? 'checked="checked"' : '').' />';
			print '</td>';
			print '</tr>';

			print '<tr class="wysiwyg_header" '.(empty($object_element->use_custom_header) ? 'style="display:none;background-color:#CEECF5;"' : 'style="background-color:#CEECF5;"').'>';
			print '<td>'.$langs->trans('RefLtrHeaderContent');
			print '</td>';
			print '<td>';
			$doleditor=new DolEditor('header', $object_element->header, '', 150, 'dolibarr_notes_encoded', '', false, true, 1, $nbrows, 70);
			$doleditor->Create();
			print '</td>';
			print '</tr>';

			foreach ( $object_element->content_letter as $key => $line_chapter ) {
				if ($line_chapter['content_text'] == '@breakpage@') {
					print '<tr><td colspan="2" style="text-align:center;font-weight:bold">';
					print '<input type="hidden" name="content_text_' . $key . '" value="' . $line_chapter['content_text'] . '"/>';
					print $langs->trans('RefLtrPageBreak');
					print '</td></tr>';
				} elseif ($line_chapter['content_text'] == '@breakpagenohead@') {
					print '<tr><td colspan="2" style="text-align:center;font-weight:bold">';
					print '<input type="hidden" name="content_text_' . $key . '" value="' . $line_chapter['content_text'] . '"/>';
					print $langs->trans('RefLtrAddPageBreakWithoutHeader');
					print '</td></tr>';
				}else {
					print '<tr>';
					print '<td  width="20%">';
					print $langs->trans('RefLtrText');
					print '</td>';
					print '<td>';

					require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
					$nbrows = ROWS_2;
					if (! empty($conf->global->MAIN_INPUT_DESC_HEIGHT))
						$nbrows = $conf->global->MAIN_INPUT_DESC_HEIGHT;
					$enable = (isset($conf->global->FCKEDITOR_ENABLE_SOCIETE) ? $conf->global->FCKEDITOR_ENABLE_SOCIETE : 0);
					$doleditor = new DolEditor('content_text_' . $key, $line_chapter['content_text'], '', 150, 'dolibarr_notes_encoded', '', false, true, $enable, $nbrows, 70);
					$doleditor->Create();
					print '</td>';
					print '</tr>';

					print '<tr>';
					print '<td  width="20%">';
					print $langs->trans('RefLtrOption');
					print '</td>';
					print '<td>';
					if (is_array($line_chapter['options']) && count($line_chapter['options']) > 0) {
						foreach ( $line_chapter['options'] as $keyoption => $option_detail ) {
							if (! empty($option_detail['text_content_option'])) {
								if (! empty($option_detail['use_content_option'])) {
									$checked = ' checked="checked" ';
								} else {
									$checked = '';
								}
								print '<input type="checkbox" ' . $checked . ' name="use_content_option_' . $key . '_' . $keyoption . '" value="1"><input type="texte class="flat" size="20" name="text_content_option_' . $key . '_' . $keyoption . '" value="' . $option_detail['text_content_option'] . '" ><br>';
							}
						}
					}
					print '</td>';
					print '</tr>';
				}
			}


			print '<tr style="background-color:#CEF6CE;">';
			print '<td>';
			print $langs->trans('RefLtrUseCustomFooter');
			print '</td>';
			print '<td><input type="checkbox" name="use_custom_footer" id="use_custom_footer" value="1" '.(!empty($object_element->use_custom_footer) ? 'checked="checked"' : '').' />';
			print '</td>';
			print '</tr>';

			print '<tr class="wysiwyg_footer" '.(empty($object_element->use_custom_footer) ? 'style="display:none;background-color:#CEF6CE;"' : 'style="background-color:#CEF6CE;"').'>';
			print '<td>'.$langs->trans('RefLtrFooterContent');
			print '</td>';
			print '<td>';
			$doleditor=new DolEditor('footer', $object_element->footer, '', 150, 'dolibarr_notes_encoded', '', false, true, 1, $nbrows, 70);
			$doleditor->Create();
			print '</td>';
			print '</tr>';

			print '<tr>';
			print '<td colspan="2" align="center">';
			print '<input type="submit" value="' . $langs->trans('RefLtrCreateDoc') . '" class="button" name="createdoc">';
			print '</td>';
			print '</tr>';

			print '</table>';
		}
	}
}

?>

<script type="text/javascript">

	$('[name*=use_custom]').click(function() {

		var is_checked = $(this).prop('checked');
		var name_checkbox = $(this).attr('name');
		var type_checkbox = name_checkbox.replace('use_custom_', '');

		if(is_checked) {
			$('.wysiwyg_' + type_checkbox).show();
		} else {
			$('.wysiwyg_' + type_checkbox).hide();
		}

	});

</script>

<?php
// Page end
llxFooter();
$db->close();
