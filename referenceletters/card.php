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
 *	\file		index.php
*	\ingroup	referenceletters
*	\brief		index page
*/

// Load environment

require '../config.php';
require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$confirm = GETPOST('confirm', 'alpha');

$refltrtitle=GETPOST('refltrtitle', 'alpha');
$refltrelement_type=GETPOST('refltrelement_type', 'alpha');
$refltruse_landscape_format=GETPOST('refltruse_landscape_format', 'alpha');


// Access control
// Restrict access to users with invoice reading permissions
restrictedArea($user, 'referenceletters');

// Load translation files required by the page
$langs->load("referenceletters@referenceletters");

$object = new ReferenceLetters($db);
$object_chapters = new ReferenceLettersChapters($db);
if(!empty($id)) {
	$result=$object->fetch($id);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	}
	$result=$object_chapters->fetch_byrefltr($id);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	}
}


$extrafields = new ExtraFields($db);

$error = 0;


// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'referenceletterscard'
));


/*
 * Actions
*/

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

if ($action == "add") {
	$object->title = $refltrtitle;
	$object->element_type = $refltrelement_type;
	$object->use_landscape_format = $refltruse_landscape_format;

	$extrafields->setOptionalsFromPost($extralabels, $object);

	$result = $object->create($user);
	if ($result < 0) {
		$action = 'create';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->referenceletters->delete) {
	$result = $object->delete($user);
	if ($result < 0) {
		setEventMessage($object->errors, 'errors');
	} else {
		header('Location:' . dol_buildpath('/referenceletters/referenceletters/list.php', 1));
	}
} elseif ($action == "clone") {

	$object_clone = new ReferenceLetters($db);
	$result = $object_clone->createFromClone($object->id);
	if ($result < 0) {
		setEventMessage($object_clone->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $result);
	}
} elseif($action=='setrefltrtitle') {
	$object->title = $refltrtitle;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltrtitle';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
}elseif($action=='setrefltrelement') {
	$object->element_type = $refltrelement_type;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltrelement';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif($action=='setrefltruse_landscape_format' && isset($_REQUEST['modify'])) {

	$object->use_landscape_format = $refltruse_landscape_format;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltruse_landscape_format';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif($action=='addbreakpage') {
	$object_chapters_breakpage = new ReferenceLettersChapters($db);
	$object_chapters_breakpage->fk_referenceletters=$object->id;
	$object_chapters_breakpage->title ='';
	$object_chapters_breakpage->content_text = '@breakpage@';
	$object_chapters_breakpage->sort_order=$object_chapters_breakpage->findMaxSortOrder();
	$result = $object_chapters_breakpage->create($user);
	if ($result < 0) {
		$action = 'addbreakpage';
		setEventMessage($object_chapters_breakpage->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif ($action=='addbreakpagewithoutheader') {
	$object_chapters_breakpage = new ReferenceLettersChapters($db);
	$object_chapters_breakpage->fk_referenceletters=$object->id;
	$object_chapters_breakpage->title ='';
	$object_chapters_breakpage->content_text = '@breakpagenohead@';
	$object_chapters_breakpage->sort_order=$object_chapters_breakpage->findMaxSortOrder();
	$result = $object_chapters_breakpage->create($user);
	if ($result < 0) {
		$action = 'addbreakpagewithoutheader';
		setEventMessage($object_chapters_breakpage->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
}elseif ($action == "changestatus") {

	if (empty($object->status)) {
		$object->status=ReferenceLetters::STATUS_VALIDATED;
	} else {
		$object->status=ReferenceLetters::STATUS_DRAFT;
	}
	$result = $object->update($user);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
}

/*
 * VIEW
*/
$title = $langs->trans('Module103258Name');

llxHeader('',$title);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);

$now = dol_now();
// Add new proposal
if ($action == 'create' && $user->rights->referenceletters->write) {
	print_fiche_titre($langs->trans("RefLtrCreate"), '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	print '<form name="addreferenceletters" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';

	print '<table class="border" width="100%">';

	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrElement');
	print '</td>';
	print '<td>';
	print $formrefleter->selectElementType($refltrelement_type, 'refltrelement_type');
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrTitle');
	print '</td>';
	print '<td>';
	print '<input type="text" name="refltrtitle" size="20" value="' . $refltrtitle . '"/>';
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td width="20%">';
	print $langs->trans('RefLtrUseLandscapeFormat');
	print '</td>';
	print '<td>';
	print $form->selectyesno('refltruse_landscape_format', $refltruse_landscape_format, 1);
	print '</td>';
	print '</tr>';

	// Other attributes
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook

	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'edit');
	}

	print '</table>';

	print '<center>';
	print '<input type="submit" class="button" value="' . $langs->trans("Create") . '">';
	print '&nbsp;<input type="button" class="button" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</center>';

	print '</form>';
} else {

	/*
	 * Show object in view mode
	*/
	$head = referenceletterPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans('Module103258Name'), 0, dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	// Confirm form
	$formconfirm = '';
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('RefLtrDelete'), $langs->trans('RefLtrConfirmDelete'), 'confirm_delete', '', 0, 1);
	}

	if (empty($formconfirm)) {
		$parameters = array();
		$formconfirm = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	}
	if (!empty($formconfirm)) {
		print $formconfirm;
	}

	$linkback = '<a href="' . dol_buildpath('/referenceletters/referenceletters/list.php', 1) . '">' . $langs->trans("BackToList") . '</a>';
	print $linkback;

	print '<div style="vertical-align: middle; margin-bottom: 10px">';
	print '<div class="pagination"><ul>';
	print '<li class="noborder litext">'.$linkback.'</li>';
	print '</ul></div>';
	print '<div class="inline-block floatleft valignmiddle refid refidpadding">';
	print $langs->trans('RefLtrElement').' : '. $form->editfieldval("RefLtrTitle",'refltrtitle',$object->title,$object,$user->rights->referenceletters->write);
	if ($action !== 'editrefltrtitle') print '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltrtitle&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>'.'<BR>';
	print '<div class="refidno">';
	if ($action=='editrefltrelement') {
		print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
		print '<input type="hidden" name="action" value="setrefltrelement">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';
		print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
		print '<tr><td>';
		print $langs->trans('RefLtrElement').$formrefleter->selectElementType($object->element_type, 'refltrelement_type');
		print '</td>';

		print '<td align="left">';
		print '<input type="submit" class="button'.(empty($notabletag)?'':' ').'" name="modify" value="'.$langs->trans("Modify").'">';
		print'<input type="submit" class="button'.(empty($notabletag)?'':' ').'" name="cancel" value="'.$langs->trans("Cancel").'">';
		print'</td>';

		print '</tr></table>'."\n";
		print '</form>'."\n";
		print '<br>'."\n";
	} else {
		print $langs->trans('RefLtrElement').' : '. $object->displayElement(). '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltrelement&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>'.'<BR>';
	}
	print $langs->trans('RefLtrUseLandscapeFormat') . ' : ';
	if ($action !== 'editrefltruse_landscape_format') print '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltruse_landscape_format&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>';
	print '&nbsp;' . $form->editfieldval("RefLtrUseLandscapeFormat",'refltruse_landscape_format',$object->use_landscape_format,$object,$user->rights->referenceletters->write, 'select;1:'.$langs->trans('Yes').',0:'.$langs->trans('No')) . '<bt>';

	// Other attributes
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook

	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields);
	}

	print '</div>';
	print '</div>';
	print '</div>';

	if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters)>0) {

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';
		print '<tr class="liste_titre"><td>'. img_picto('',dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 'class="valignmiddle" id="pictotitle"', 1) . ' ' . $langs->trans("RefLtrChapters");
		print '</td></tr>';
		foreach ($object_chapters->lines_chapters as $line_chapter) {
			if ($line_chapter->content_text=='@breakpage@') {
				print '<tr class="oddeven"><td><table class="border" width="100%">';
				print '<tr><td style="text-align:center;font-weight:bold">';
				print $langs->trans('RefLtrPageBreak');
				print '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
				print '</td></tr>';
				print '</table></td></tr>';
			} elseif ($line_chapter->content_text=='@breakpagenohead@') {
				print '<tr class="oddeven"><td><table class="border" width="100%">';
				print '<tr><td style="text-align:center;font-weight:bold">';
				print $langs->trans('RefLtrAddPageBreakWithoutHeader');
				print '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
				print '</td></tr>';
				print '</table></td><tr>';
			} else {
				print '<tr class="oddeven"><td><table class="border" width="100%">';

				if ($user->rights->referenceletters->write) {
					print '<tr><td rowspan="6" width="20px">';
					print '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=edit">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
					print '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
					print '</td></tr>';
				}

				if (! empty($conf->global->MAIN_MULTILANGS))
				{
					print '<tr>';
					print '<td  width="20%">';
					print $langs->trans('RefLtrLangue');
					print '</td>';
					print '<td>';
					$langs->load("languages");
					$labellang = ($line_chapter->lang?$langs->trans('Language_'.$line_chapter->lang):'');
					print $labellang;
					print '</td>';
					print '</tr>';
				}

				print '<tr>';
				print '<td  width="20%">';
				print $langs->trans('RefLtrTitle');
				print '</td>';
				print '<td>';
				print $line_chapter->title;
				print '</td>';
				print '</tr>';

				print '<tr>';
				print '<td  width="20%">';
				print $langs->trans('RefLtrText');
				print '</td>';
				print '<td>';
				print $line_chapter->content_text;
				print '</td>';
				print '</tr>';

				print '<tr>';
				print '<td  width="20%">';
				print $langs->trans('RefLtrOption');
				print '</td>';
				print '<td>';
				if (is_array($line_chapter->options_text) && count($line_chapter->options_text)>0) {
					foreach($line_chapter->options_text as $key=>$option_text) {
						print '<input type="checkbox" readonly="readonly" disabled="disabled" name="'.$key.'">'.$option_text.'<br>';
					}
				}
				print '</td>';
				print '</tr>';

				print '<tr>';
				print '<td width="20%">';
				print $langs->trans('RefLtrReadOnly');
				print '</td>';
				print '<td>';
				print '<input type="checkbox" name="refltrreadonly" size="20" disabled="disabled" '.(!empty($line_chapter->readonly)?'checked="checked"':'').' value="1"/>';
				print '</td>';
				print '</tr>';

				print '</table></td></tr>';
			}
		}
		print '</table>';
	}

	print "</div>\n";

	/*
	 * Barre d'actions
	*/
	print '<div class="tabsAction">';
	if ($user->rights->referenceletters->write) {
	    print '<div class="inline-block divButAction">';
	    print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters/card.php',1).'?action=addbreakpage&id='.$object->id.'">' . $langs->trans("RefLtrAddPageBreak") . '</a>';
	    print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters/card.php',1).'?action=addbreakpagewithoutheader&id='.$object->id.'">' . $langs->trans("RefLtrAddPageBreakWithoutHeader") . '</a>';
	    print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?action=create&idletter='.$object->id.'">' . $langs->trans("RefLtrNewChaters") . '</a>';
	    print "</div><br>";
		//print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit">' . $langs->trans("Edit") . "</a></div>\n";
		print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=clone">' . $langs->trans("Clone") . "</a></div>\n";
	} else {
		print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans("Edit") . "</font></div>";
	}

	// Activ/Unactiv
	if ($user->rights->referenceletters->write) {
		if (empty($object->status)) {
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=changestatus">' . $langs->trans("RefLtrActive") . "</a></div>\n";
		} else {
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=changestatus">' . $langs->trans("RefLtrUnactive") . "</a></div>\n";
		}
	}

	// Delete
	if ($user->rights->referenceletters->delete) {
		print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete">' . $langs->trans("Delete") . "</a></div>\n";
	} else {
		print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans("Delete") . "</font></div>";
	}
	print '</div>';

}



// Page end
llxFooter();
$db->close();