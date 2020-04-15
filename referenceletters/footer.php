<?php

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
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

$action = GETPOST('action');
$confirm = GETPOST('confirm');
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
		'referencelettersfooter'
));


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

if(empty($action)) $action = 'view';

if($action === 'save') {

	$object->footer = GETPOST('footer');
	$object->update($user);

} elseif($action === 'set_custom_footer') {
	$object->use_custom_footer = GETPOST('use_custom_footer');

	//TODO Check this !
	echo $object->update($user);
	exit;
} elseif($action === 'predeffooter'){
    $object->use_custom_footer = true;
    $object->footer = $conf->global->REF_LETTER_PREDEF_FOOTER;
    $object->update($user);
}

/*
 * View
 */

$title = $langs->trans('Module103258Name').'-'.$langs->trans('RefLtrFooterTab');

$arrayofcss = array('/referenceletters/css/view_documents.css?v='.time());
llxHeader('',$title, '', '', 0, 0, array(), $arrayofcss);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);

if(!empty($object->id)) {

	$head = referenceletterPrepareHead($object);
	dol_fiche_head($head, 'foot', $langs->trans('RefLtrFooterTab'), 0, dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	print '<form name="saveFooter" method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.GETPOST('id').'">';

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

	print '<td width="20%">';
	print $langs->trans('RefLtrTag');
	print '</td>';
	print '<td>';
	print $langs->trans("RefLtrDisplayTag").'<span class="docedit_shortcode classfortooltip" data-target="#footer"><span class="fa fa-code marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('DisplaySubtitutionTable').'" title="'.$langs->trans('DisplaySubtitutionTable').'"></span></span>';
	print $formrefleter->displaySubtitutionKeyAdvanced($user, $object);
	print '</td>';
	print '</tr>';

	print '<tr style="background-color:#CEF6CE;">';
	print '<td>'.$langs->trans('RefLtrUseCustomFooter');
	print '</td>';
	print '<td><input type="checkbox" name="use_custom_footer" id="use_custom_footer" value="1" '.(!empty($object->use_custom_footer) ? 'checked="checked"' : '').' />';
	if (!empty($conf->global->REF_LETTER_PREDEF_HEADER_AND_FOOTER) && !empty($conf->global->REF_LETTER_PREDEF_FOOTER)){
	    print '&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?id='.GETPOST('id').'&action=predeffooter" class="button">' . $langs->trans('Fill') . '</a>';
	}
	print '</td>';
	print '</tr>';

	print '<tr class="wysiwyg" '.(empty($object->use_custom_footer) ? 'style="display:none;background-color:#CEF6CE;"' : 'style="background-color:#CEF6CE;"').'>';
	print '<td>'.$langs->trans('RefLtrFooterContent');
	print '</td>';
	print '<td>';
	$doleditor=new DolEditor('footer', $object->footer, '', 150, 'dolibarr_notes_encoded', '', false, true, 1, $nbrows, 70);
	$doleditor->Create();
	print '</td>';
	print '</tr>';

	// Other attributes
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook

	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields);
	}

	print '</table>';

	print '<div class="wysiwyg" '.(empty($object->use_custom_footer) ? 'style="display:none;"' : '').'>';
	print '<input type="hidden" name="action" value="save" />';
	print '<center>';
	print '<input type="submit" class="button" value="' . $langs->trans('Save') . '">';
	print '&nbsp;<input type="button" class="button" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</center>';
	print '</div>';

	print '</form>';

}

?>

<script type="text/javascript">

	$('#use_custom_footer').click(function() {

		var is_checked = $(this).prop('checked');
		if(is_checked) {
			$('.wysiwyg').show();
		} else {
			$('.wysiwyg').hide();
		}

		$.ajax({

			url:"<?php echo dol_buildpath('/referenceletters/referenceletters/footer.php',1) ?>"
					,data:{
							id:<?php echo (int)$object->id ?>
							,action:"set_custom_footer"
							,use_custom_footer:+is_checked
						}

		});

	});

</script>

<?php

llxFooter();
$db->close();
