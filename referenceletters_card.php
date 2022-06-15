<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
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

/**
 *   	\file       referenceletters_card.php
 *		\ingroup    referenceletters
 *		\brief      Page to create/edit/view referenceletters
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/referenceletters/class/referenceletters.class.php');
dol_include_once('/referenceletters/class/referenceletterschapters.class.php');
dol_include_once('/referenceletters/class/referenceletters_tools.class.php');
dol_include_once('/referenceletters/lib/referenceletters.lib.php');
dol_include_once('/referenceletters/lib/referenceletters_referenceletters.lib.php');


// Load translation files required by the page
$langs->loadLangs(array("referenceletters@referenceletters", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'referenceletterscard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

$refltrdefault_doc=GETPOST('refltrdefault_doc', 'alpha');
$refltrdoc_template=GETPOST('refltrdoc_template', 'alpha');

// Initialize technical objects
$object = new ReferenceLetters($db);
$object_chapters = new ReferenceLettersChapters($db);
$object_tools = new RfltrTools($db);
if(!empty($id)) {
	$result=$object->fetch($id);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	}
	$TChaptersLines=$object_chapters->fetchAll('', '', '', '', array('fk_referenceletters' => $object->id));
	if ($TChaptersLines < 0) {
		setEventMessage($object->error, 'errors');
	}
}
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->referenceletters->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('referenceletterscard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->referenceletters->referenceletters->read;
	$permissiontoadd = $user->rights->referenceletters->referenceletters->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->referenceletters->referenceletters->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DISABLED);
	$permissionnote = $user->rights->referenceletters->referenceletters->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->referenceletters->referenceletters->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->referenceletters->multidir_output[isset($object->entity) ? $object->entity : 1].'/referenceletters';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DISABLED) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->referenceletters->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/referenceletters/referenceletters_list.php', 1);

	if($action=='addbreakpage' ) {
		$object_chapters_breakpage = new ReferenceLettersChapters($db);
		$object_chapters_breakpage->fk_referenceletters=$object->id;
		$object_chapters_breakpage->title ='breakpage';
		$object_chapters_breakpage->content_text = '@breakpage@';
		$object_chapters_breakpage->sort_order=$object_chapters_breakpage->findMaxSortOrder();
		$object_chapters_breakpage->lang=$object_chapters_breakpage->findPreviewsLanguage();
		$result = $object_chapters_breakpage->create($user);
		if ($result < 0) {
			$action = 'addbreakpage';
			setEventMessage($object_chapters_breakpage->error, 'errors');
		} else {
			header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
		}
	}

	if ($action=='addbreakpagewithoutheader') {
		$object_chapters_breakpage = new ReferenceLettersChapters($db);
		$object_chapters_breakpage->fk_referenceletters=$object->id;
		$object_chapters_breakpage->title ='';
		$object_chapters_breakpage->content_text = '@breakpagenohead@';
		$object_chapters_breakpage->sort_order=$object_chapters_breakpage->findMaxSortOrder();
		$object_chapters_breakpage->lang=$object_chapters_breakpage->findPreviewsLanguage();
		$result = $object_chapters_breakpage->create($user);
		if ($result < 0) {
			$action = 'addbreakpagewithoutheader';
			setEventMessage($object_chapters_breakpage->error, 'errors');
		} else {
			header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
		}
	}

	if($action=='adddocpdf' && !empty($refltrdoc_template)) {
		$modellist=array();
		if (array_key_exists('listmodelfile', $object->element_type_list[$object->element_type])) {
			if (file_exists($object->element_type_list[$object->element_type]['listmodelfile'])) {
				include_once $object->element_type_list[$object->element_type]['listmodelfile'];
				$modellist = call_user_func($object->element_type_list[$object->element_type]['listmodelclass'].'::liste_modeles', $db);
			}
		}
		if (empty($modellist)) {
			$action='adddocpdf_confirm';
		}
	}

	if ($action=='adddocpdf_confirm' && !empty($refltrdoc_template)) {

		$object_chapters_pdfdoc = new ReferenceLettersChapters($db);
		$object_chapters_pdfdoc->fk_referenceletters=$object->id;
		$object_chapters_pdfdoc->title ='';
		$object_chapters_pdfdoc->content_text = '@pdfdoc_'.$refltrdoc_template.'@';
		$object_chapters_pdfdoc->sort_order=$object_chapters_pdfdoc->findMaxSortOrder();
		$object_chapters_pdfdoc->lang=$object_chapters_pdfdoc->findPreviewsLanguage();
		$result = $object_chapters_pdfdoc->create($user);
		if ($result < 0) {
			$action = 'adddocpdf';
			setEventMessage($object_chapters_pdfdoc->error, 'errors');
		} else {
			header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
		}
	}

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/referenceletters/referenceletters_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'REFERENCELETTERS_REFERENCELETTERS_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);
$arrayofcss = array('/referenceletters/css/view_documents.css?v='.time());

$title = $langs->trans("ReferenceLetters");
$help_url = '';
llxHeader('', $title, $help_url, '', 0, 0, array(), $arrayofcss);

if($action == 'create' || $action == 'edit') {
	$selectElementType = selectElementType();
	print '<script type="text/javascript">
	 jQuery(document).ready(function() {
		function init_myfunc()
		{
			var element = "' .addslashes($selectElementType). '";
			$(element).replaceAll("#element_type");
		}
		init_myfunc();
		jQuery("#mybutton").click(function() {
			init_myfunc();
		});
	 });
	 </script>';
}



// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("ReferenceLetters")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("ReferenceLetters"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = referencelettersPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("ReferenceLetters"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteReferenceLetters'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	if ($action=='adddocpdf') {
		if (array_key_exists('listmodelfile', $object->element_type_list[$object->element_type])) {
			if (file_exists($object->element_type_list[$object->element_type]['listmodelfile'])) {
				include_once $object->element_type_list[$object->element_type]['listmodelfile'];
				$modellist = call_user_func($object->element_type_list[$object->element_type]['listmodelclass'].'::liste_modeles', $db);
			}

			$formquestion = array(
				array(
					'label' => $langs->trans('Model'),
					'name'  => 'refltrdoc_template',
					'type' => 'select',
					'values' => $modellist
				)
			);

			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('RefLtrAddPDFDoc'), $langs->trans('RefLtrAddPDFDoc'), 'adddocpdf_confirm', $formquestion, 0, 1);
		}
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionReferenceLetters', $object->ref);
		/*if (! empty($conf->notification->enabled))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('REFERENCELETTERS_CLOSE', $object->socid, $object);
		}*/

		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/referenceletters/referenceletters_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled)) {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd) {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref .= '<input type="hidden" name="action" value="classin">';
	 $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref .= '</form>';
	 } else {
	 $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	 }
	 } else {
	 if (! empty($object->fk_project)) {
	 $proj = new Project($db);
	 $proj->fetch($object->fk_project);
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';


	if (is_array($TChaptersLines) && count($TChaptersLines)>0) {
		$pageCurrentNum = 1;

		print '<div class="underbanner clearboth"></div>';
		print '<div  id="sortablezone" class="docedit_docboard">';

		print '<div class="info">'.$langs->trans('doceditinfo_viewlimit').'</div>';
		$classOrientation = "portrait";
		if(!empty($object->use_landscape_format))
		{
			$classOrientation = "landscape";
		}

		print '<div id="page_'.$pageCurrentNum.'"  class="docedit_document '.$classOrientation.'" data-page="'.$pageCurrentNum.'" >';

		$object_tools::_print_docedit_header($object);
		$nbChapterInPage = 0;
		$nofooternext=false;
		foreach ($TChaptersLines as $line_chapter) {
			$TIsSprecialChapter=$line_chapter->isSpecialChapters();
			if (count($TIsSprecialChapter)>1) {
				// reset nb chapters in page
				$nbChapterInPage = 0;

				// first close page
				if (!$nofooternext) {
					$object_tools::_print_docedit_footer($object);

				} else {
					$nofooternext=false;
				}
				print '</div><!-- END docedit_document -->';

				// add break page element
				print $object_tools::renderChapterHTML($line_chapter,'view');
				$norepeat=$line_chapter->isNoRepeat();

				// start new page
				$pageCurrentNum++;
				print '<div id="page_'.$pageCurrentNum.'"  class="docedit_document '.$classOrientation.'" data-page="'.$pageCurrentNum.'" >';
				if (! array_key_exists('nohead',$TIsSprecialChapter)){
					$object_tools::_print_docedit_header($object, $norepeat);
				} else {
					$nofooternext=true;
				}

			} else {
				$nbChapterInPage++;
				$urlToken = '';
				if (function_exists('newToken')) $urlToken = "&token=".newToken();

				print '<div id="chapter_'.$line_chapter->id.'" class="sortable docedit_document_body docedit_document_bloc" data-sortable-chapter="'.$line_chapter->id.'">';

				// Button and infos
				print '<div class="docedit_infos docedit_infos_left"><div class="docedit_sticky">';

				if ($user->rights->referenceletters->write) {
					if(!empty($conf->global->DOCEDIT_CHAPTERS_SORTABLE)){
						print '<span class="docedit_infos_icon handle classfortooltip" ><span class="fa fa-th marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('MoveChapter').'" title="'.$langs->trans('MoveChapter').'"></span></span>';
					}

					if(!empty($conf->global->DOCEDIT_CHAPTERS_INLINE_EDITION)){
						print '<span class="docedit_infos_icon docedit_save classfortooltip" data-target="#chapter_body_text_'.$line_chapter->id.'"  ><span class="fa fa-save marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('Save').'" title="'.$langs->trans('Save').'"></span></span>';

						print '<span class="docedit_infos_icon docedit_shortcode classfortooltip" data-target="#chapter_body_text_'.$line_chapter->id.'"  ><span class="fa fa-code marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('DisplaySubtitutionTable').'" title="'.$langs->trans('DisplaySubtitutionTable').'"></span></span>';

						print '<span class="docedit_infos_icon docedit_setbool classfortooltip" data-field="readonly" data-id="'.$line_chapter->id.'" data-valtoset="'.(!$line_chapter->readonly).'" ><span class="fa '.(empty($line_chapter->readonly)?'fa-toggle-off':'fa-toggle-on').' marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('RefLtrReadOnly').'" title="'.$langs->trans('RefLtrReadOnly').'"></span></span>';
						print '<span class="docedit_infos_icon docedit_setbool classfortooltip" data-field="same_page" data-id="'.$line_chapter->id.'" data-valtoset="'.(!$line_chapter->same_page).'"  ><span class="fa '.(empty($line_chapter->same_page)?'fa-toggle-off':'fa-toggle-on').' marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('RefLtrUnsecable').'" title="'.$langs->trans('RefLtrUnsecable').'"></span></span>';
					}

					print '<a  href="'.dol_buildpath('/referenceletters/referenceletterschapters_card.php', 1).'?id=' . $line_chapter->id . '&action=edit&fk_referenceletters='.$object->id.'">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
					print '<a class="docedit_infos_icon classfortooltip" href="'.dol_buildpath('/referenceletters/referenceletterschapters_card.php', 1).'?id=' . $line_chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';

				}

				print '</div></div><!-- END docedit_infos -->';

				print '<div class="docedit_infos docedit_infos_top">';
				print '<span class="docedit_title_type" >';
				print $langs->trans('RefLtrTitle');
				if (! empty($conf->global->MAIN_MULTILANGS))
				{
					$s=picto_from_langcode($line_chapter->lang);
					print ($s?' '.$s:'');
				}
				print ' : </span>';
				//print $langs->trans('RefLtrTitle');
				print '<span class="docedit_title" >'. $line_chapter->title.'</span>';

				print '</div><!-- END docedit_infos_top -->';

				//print $langs->trans('RefLtrText');
				$editInline = '';
				if(!empty($conf->global->DOCEDIT_CHAPTERS_INLINE_EDITION)  && $user->rights->referenceletters->write ){ $editInline = ' contenteditable="true" '; }

				print '<div class="docedit_document_body_text" '.$editInline.' id="chapter_body_text_'.$line_chapter->id.'" data-id="'.$line_chapter->id.'"  data-type="chapter_text" >';
				print $line_chapter->content_text;
				print '</div><!-- END docedit_document_body_text -->';

				if (is_array($line_chapter->options_text) && count($line_chapter->options_text)>0) {

					print '<div class="docedit_document_option">';

					print $langs->trans('RefLtrOption');

					if(!empty($line_chapter->readonly))
					{
						print ' <span class="docedit_document_option_read_only" >'.$langs->trans('RefLtrReadOnly').'</span>';
					}

					foreach($line_chapter->options_text as $key=>$option_text) {
						print '<label class="docedit_label" ><input type="checkbox" readonly="readonly" disabled="disabled" name="'.$key.'"> '.$option_text.'</label>';
					}
					print '</div><!-- END docedit_document_option -->';
				}

				print '</div><!-- end docedit_document_body -->';
			}
		}

		if (!$nofooternext) {
			$object_tools::_print_docedit_footer($object);
		}

		print '</div><!-- END docedit_document -->';

		print '</div><!-- end docedit_docboard -->';

		if(!empty($conf->global->DOCEDIT_CHAPTERS_SORTABLE) && $user->rights->referenceletters->write)
		{
			print '
	        <script>$( function() {
	        $( ".docedit_document" ).sortable({
                cursor: "move",
	            placeholder: "ui-state-highlight",
	            connectWith: ".docedit_document",
	            items: ".sortable:not(.sortabledisable)",
	            handle: ".handle",
                stop: function (event, ui) {
						$(".slide-placeholder-animator").remove();

						console.log("onstop");
						console.log(getOrder());

						$.ajax({
		    	            data: {
								object_id: '.$object->id.',
						    	roworder: getOrder(),
                                set: "sortChapter"
							},
		    	            type: "POST",
                            dataType: "json",
		    	            url: "'.dol_buildpath('referenceletters/script/interface.php',1).'",
		    	            success: function(data) {
               	                console.log(data);
                                if(data.saved > 0){
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Saved')).'");
                                }else{
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Error')).' : " + data.message, "error", 3000);
                                }
		    	            }
		    	        });
		    	  },

                revert: 150,
                start: function(e, ui){

                    placeholderHeight = ui.item.outerHeight();
                    ui.placeholder.height(placeholderHeight + 15);
                    $(\'<div class="slide-placeholder-animator" data-height="\' + placeholderHeight + \'"></div>\').insertAfter(ui.placeholder);

                },
                change: function(event, ui) {

                    ui.placeholder.stop().height(0).animate({
                        height: ui.item.outerHeight() + 15
                    }, 300);

                    placeholderAnimatorHeight = parseInt($(".slide-placeholder-animator").attr("data-height"));

                    $(".slide-placeholder-animator").stop().height(placeholderAnimatorHeight + 15).animate({
                        height: 0
                    }, 300, function() {
                        $(this).remove();
                        placeholderHeight = ui.item.outerHeight();
                        $(\'<div class="slide-placeholder-animator" data-height="\' + placeholderHeight + \'"></div>\').insertAfter(ui.placeholder);
                    });

                },

	          });

                function getOrder() {
                    var data = "";

                    $("[data-sortable-chapter]").each(function(){
                       if(data.length>0){
                            data += ",";
                       }
                       data += $(this).attr("data-sortable-chapter");
                    });
                   return data;
                }
            ';



			print '} );</script>';
		}

		if(!empty($conf->global->DOCEDIT_CHAPTERS_INLINE_EDITION) && $user->rights->referenceletters->write)
		{

			print '<script>'."\n";
			// The "instanceCreated" event is fired for every editor instance created.
			print ' CKEDITOR.on( \'instanceCreated\', function ( event ) {

		                var editor = event.editor, element = editor.element;

			            // Customize the editor configuration on "configLoaded" event,
			            // which is fired after the configuration file loading and execution.
			            // This makes it possible to change the configuration before the editor initialization takes place.
			            editor.on( \'configLoaded\', function () {

		                // Remove redundant plugins to make the editor simpler.
				        editor.config.removePlugins = \'flash,forms,iframe,newpage,smiley,specialchar,templates\';

		                editor.config.customConfig = ckeditorConfig;
		                editor.config.readOnly = false;
		                editor.config.htmlEncodeOutput =false;
		                editor.config.allowedContent =false;
		                editor.config.extraAllowedContent = \'\';
		                editor.config.fullPage = false;
		                editor.config.toolbarStartupExpanded=false;
		                editor.config.language= \''.$langs->defaultlang.'\';
		                editor.config.textDirection= \''.$langs->trans("DIRECTION").'\';
                        width: element.offsetWidth,
                        editor.config.filebrowserBrowseUrl = ckeditorFilebrowserBrowseUrl;
                        editor.config.filebrowserImageBrowseUrl = ckeditorFilebrowserImageBrowseUrl;
                        editor.config.filebrowserWindowWidth = \'900\';
                        editor.config.filebrowserWindowHeight = \'500\';
                        editor.config.filebrowserImageWindowWidth = \'900\';
                        editor.config.filebrowserImageWindowHeight = \'500\';

                    	// Used for notes fields
                    	editor.config.toolbar_dolibarr_inline_notes =
                    	[
                    	 	[\'SpellChecker\', \'Scayt\'],// \'Cut\',\'Copy\',\'Paste\',\'-\', are useless, can be done with right click, even on smarpthone
                    	 	[\'Undo\',\'Redo\',\'-\',\'Find\',\'Replace\'],
                    	    [\'Format\',\'Font\',\'FontSize\'],
                    	 	[\'Bold\',\'Italic\',\'Underline\',\'Strike\',\'Superscript\',\'-\',\'TextColor\',\'BGColor\',\'RemoveFormat\'],
                    	 	[\'NumberedList\',\'BulletedList\',\'Outdent\',\'Indent\'],
                    	 	[\'JustifyLeft\',\'JustifyCenter\',\'JustifyRight\',\'JustifyBlock\'],
                    	    [\'Link\',\'Unlink\',\'Image\',\'Table\',\'HorizontalRule\',\'SpecialChar\'],
                    	 	[\'Source\']
                    	];

		                editor.config.toolbar = editor.config.toolbar_dolibarr_inline_notes;

		            } );
		    } );
		    ';


			print ' $( function() { ';
			print '
                   $(".docedit_save").click(function(btnsave) {

                        var saveTarget = $($(this).data("target"));

                        if(CKEDITOR.instances[saveTarget.attr("id")] != undefined)
                        {
                            var evt = CKEDITOR.instances[saveTarget.attr("id")];
                            // getData() returns CKEditor\'s HTML content.
                            console.log( evt ); //evt.editor.getData().length


                            $.ajax({
                              method: "POST",
                              url: "'.dol_buildpath('referenceletters/script/interface.php',1).'",
                              dataType: "json",
                              data: { set: "content" , id: saveTarget.data("id") , type: saveTarget.data("type"), content: evt.getData() }
                            })
                            .done(function( data ) {
                                if(data.status){
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Saved')).'");
                                }else{
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Error')).' : " + data.message, "error", 3000);
                                }
                            });

                        } else{
                            console.log("Target not found");
                        }

                   });
            ';

			print '} );</script>';

			$html = $object_tools::displaySubtitutionKeyAdvanced($user, $object);
			print $html;
		}
	}
	print '<style>.ui-state-highlight::before { content: "'.$langs->trans('PlaceHere').'"; }</style>';
	print "</div>\n";

	print dol_get_fiche_end();

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			if ($user->rights->referenceletters->write) {
				print '<div class="inline-block divButAction">';
				print '<a class="butAction" href="' . dol_buildpath('/referenceletters/referenceletters_card.php', 1) . '?action=addbreakpage&id=' . $object->id . '">' . $langs->trans("RefLtrAddPageBreak") . '</a>';
				if (strpos('rfltr_agefodd_', $object->element_type) == false && (array_key_exists('listmodelfile',$object->element_type_list[$object->element_type]))) {
					print '<a class="butAction" href="' . dol_buildpath('/referenceletters/referenceletters_card.php', 1) . '?action=adddocpdf&id=' . $object->id . '">' . $langs->trans("RefLtrAddPDFDoc") . '</a>';
				}
				print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters_card.php',1).'?action=addbreakpagewithoutheader&id='.$object->id.'">' . $langs->trans("RefLtrAddPageBreakWithoutHeader") . '</a>';
				print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletterschapters_card.php',1).'?action=create&fk_referenceletters='.$object->id.'">' . $langs->trans("RefLtrNewChaters") . '</a>';
				print "</div><br>";
				//print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit">' . $langs->trans("Edit") . "</a></div>\n";
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=clone">' . $langs->trans("Clone") . "</a></div>\n";
			}

			// Back to draft
			if ($object->status == $object::STATUS_ACTIVATED) {
				print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Validate
			if ($object->status == $object::STATUS_DISABLED) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction($langs->trans('Activate'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Activate"), 'default', '#', '', 0);
				}
			}

			// Clone
//			print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid)?'&socid='.$object->socid:'').'&action=clone&token='.newToken(), '', $permissiontoadd);

			/*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Enable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			/* if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ACTIVATED) {
					print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Re-Open'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DISABLED && $permissiontoadd));
		}
		print '</div>'."\n";
	}

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->referenceletters->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('referenceletters:ReferenceLetters', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		print '</div></div>';
	}
}

// End of page
llxFooter();
$db->close();

