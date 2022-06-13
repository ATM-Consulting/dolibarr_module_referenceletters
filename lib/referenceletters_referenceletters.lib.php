<?php
/* Copyright (C) 2022 SuperAdmin
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/referenceletters_referenceletters.lib.php
 * \ingroup referenceletters
 * \brief   Library files with common functions for ReferenceLetters
 */

/**
 * Prepare array of tabs for ReferenceLetters
 *
 * @param	ReferenceLetters	$object		ReferenceLetters
 * @return 	array					Array of tabs
 */
//function referencelettersPrepareHead($object)
//{
//	global $db, $langs, $conf;
//
//	$langs->load("referenceletters@referenceletters");
//
//	$showtabofpagecontact = 0;
//	$showtabofpagenote = 0;
//	$showtabofpagedocument = 0;
//	$showtabofpageagenda = 0;
//
//	$h = 0;
//	$head = array();
//
//	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters_card.php", 1).'?id='.$object->id;
//	$head[$h][1] = $langs->trans("Card");
//	$head[$h][2] = 'card';
//	$h++;
//
//	if ($showtabofpagecontact) {
//		$head[$h][0] = dol_buildpath("/referenceletters/referenceletters_contact.php", 1).'?id='.$object->id;
//		$head[$h][1] = $langs->trans("Contacts");
//		$head[$h][2] = 'contact';
//		$h++;
//	}
//
//	if ($showtabofpagenote) {
//		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
//			$nbNote = 0;
//			if (!empty($object->note_private)) {
//				$nbNote++;
//			}
//			if (!empty($object->note_public)) {
//				$nbNote++;
//			}
//			$head[$h][0] = dol_buildpath('/referenceletters/referenceletters_note.php', 1).'?id='.$object->id;
//			$head[$h][1] = $langs->trans('Notes');
//			if ($nbNote > 0) {
//				$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
//			}
//			$head[$h][2] = 'note';
//			$h++;
//		}
//	}
//
//	if ($showtabofpagedocument) {
//		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
//		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
//		$upload_dir = $conf->referenceletters->dir_output."/referenceletters/".dol_sanitizeFileName($object->ref);
//		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
//		$nbLinks = Link::count($db, $object->element, $object->id);
//		$head[$h][0] = dol_buildpath("/referenceletters/referenceletters_document.php", 1).'?id='.$object->id;
//		$head[$h][1] = $langs->trans('Documents');
//		if (($nbFiles + $nbLinks) > 0) {
//			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
//		}
//		$head[$h][2] = 'document';
//		$h++;
//	}
//
//	if ($showtabofpageagenda) {
//		$head[$h][0] = dol_buildpath("/referenceletters/referenceletters_agenda.php", 1).'?id='.$object->id;
//		$head[$h][1] = $langs->trans("Events");
//		$head[$h][2] = 'agenda';
//		$h++;
//	}
//
//	// Show more tabs from modules
//	// Entries must be declared in modules descriptor with line
//	//$this->tabs = array(
//	//	'entity:+tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
//	//); // to add new tab
//	//$this->tabs = array(
//	//	'entity:-tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
//	//); // to remove a tab
//	complete_head_from_modules($conf, $langs, $object, $head, $h, 'referenceletters@referenceletters');
//
//	complete_head_from_modules($conf, $langs, $object, $head, $h, 'referenceletters@referenceletters', 'remove');
//
//	return $head;
//}

/**
 * Return a Select Element
 *
 * @param strint $selected
 * @param string $htmlname
 * @return select HTML
 */
function selectElementType($selected='',$htmlname='element_type',$showempty=0, $in_array=array()) {
	global $langs, $db;

	$refletter = new Referenceletters($db);
	$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
	if (!empty($showempty)) {
		$select_elemnt .= '<option value=""></option>';
	}
	foreach($refletter->element_type_list as $element_type=>$array_data) {
		$langs->load($array_data['trans']);

		if(!empty($in_array)) {

			if(!in_array($element_type, $in_array)) continue;

		}

		if ($selected==$element_type) {
			$option_selected=' selected="selected" ';
		}else {
			$option_selected='';
		}

		$module = '';
		if(strpos($element_type, 'rfltr_agefodd_') !== false) $module = $langs->trans('Module103000Name') . ' - ';

		$select_elemnt .= '<option value="' . $element_type . '" '.$option_selected.'>' . $module . $langs->trans($array_data['title']) . '</option>';
	}

	$select_elemnt .= '</select>';
	return $select_elemnt;
}
