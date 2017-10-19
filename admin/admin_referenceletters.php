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
 * \file admin/referenceletters.php
 * \ingroup referenceletters
 * \brief This file is an example module setup page
 * Put some comments here
 */
// Dolibarr environment
$res = @include ("../../main.inc.php"); // From htdocs directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // From "custom" directory
		                                           
// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once "../lib/referenceletters.lib.php";
require_once "../class/referenceletters.class.php";
// Translations
$langs->load("referenceletters@referenceletters");
$langs->load("errors");
$langs->load("admin");

// Access control
if (! $user->admin)
	accessforbidden();
	
	// Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scandir', 'alpha');

/*
 * Actions
 */

if ($action == 'updateMask') {
	$maskconstrefleter = GETPOST('maskconstrefletter', 'alpha');
	$maskrefletter = GETPOST('maskrefletter', 'alpha');
	if ($maskconstrefleter)
		$res = dolibarr_set_const($db, $maskconstrefleter, $maskrefletter, 'chaine', 0, '', $conf->entity);
	
	if (! $res > 0)
		$error ++;
	
	if (! $error) {
		setEventMessage($langs->trans("SetupSaved"), 'mesgs');
	} else {
		setEventMessage($langs->trans("Error"), 'errors');
	}
} else if ($action == 'setmod') {
	dolibarr_set_const($db, "REF_LETTER_ADDON", $value, 'chaine', 0, '', $conf->entity);
} else if ($action == 'setvar') {
	dolibarr_set_const($db, "REF_LETTER_TYPEEVENTNAME", GETPOST('REF_LETTER_TYPEEVENTNAME', 'alpha'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "REF_LETTER_PREDEF_HEADER", GETPOST('REF_LETTER_PREDEF_HEADER'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "REF_LETTER_PREDEF_FOOTER", GETPOST('REF_LETTER_PREDEF_FOOTER'), 'chaine', 0, '', $conf->entity);
}

/*
 * View
 */
$page_name = "ReferenceLettersSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = referencelettersAdminPrepareHead();
dol_fiche_head($head, 'settings', $langs->trans("Module103258Name"), 0, "referenceletters@referenceletters");

if ($conf->use_javascript_ajax) {
	print ' <script type="text/javascript">';
	print 'window.fnDisplayFileCopyOption=function() {$( ".ifeventyes" ).show();};' . "\n";
	print 'window.fnHideFileCopyOption=function() {$( ".ifeventyes" ).hide();};' . "\n";
	print 'window.fnDisplayHeaderOption=function() {$( ".ifheadandfootyes" ).show();};' . "\n";
	print 'window.fnHideHeaderOption=function() {$( ".ifheadandfootyes" ).hide();};' . "\n";
	print ' </script>';
}

/*
 * Module numerotation
 */
print_titre($langs->trans($page_name));

$dirmodels = array_merge(array (
		'/' 
), ( array ) $conf->modules_parts['models']);

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . "</td>\n";
print '<td>' . $langs->trans("Description") . "</td>\n";
print '<td nowrap>' . $langs->trans("Example") . "</td>\n";
print '<td align="center" width="60">' . $langs->trans("Status") . '</td>';
print '<td align="center" width="16">' . $langs->trans("Infos") . '</td>';
print '</tr>' . "\n";

clearstatcache();

$form = new Form($db);

foreach ( $dirmodels as $reldir ) {
	$dir = dol_buildpath($reldir . "core/modules/referenceletters/");
	
	if (is_dir($dir)) {
		$handle = opendir($dir);
		if (is_resource($handle)) {
			$var = true;
			
			while ( ($file = readdir($handle)) !== false ) {
				
				if (preg_match('/mod_referenceletters_/', $file) && substr($file, dol_strlen($file) - 3, 3) == 'php') {
					$file = substr($file, 0, dol_strlen($file) - 4);
					require_once $dir . $file . '.php';
					
					$module = new $file();
					
					// Show modules according to features level
					if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2)
						continue;
					if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1)
						continue;
					
					if ($module->isEnabled()) {
						$var = ! $var;
						print '<tr ' . $bc[$var] . '><td>' . $module->nom . "</td><td>\n";
						print $module->info();
						print '</td>';
						
						// Show example of numbering module
						print '<td class="nowrap">';
						$tmp = $module->getExample();
						if (preg_match('/^Error/', $tmp))
							print '<div class="error">' . $langs->trans($tmp) . '</div>';
						elseif ($tmp == 'NotConfigured')
							print $langs->trans($tmp);
						else
							print $tmp;
						print '</td>' . "\n";
						
						print '<td align="center">';
						if ($conf->global->REF_LETTER_ADDON == "$file") {
							print img_picto($langs->trans("Activated"), 'switch_on');
						} else {
							print '<a href="' . $_SERVER["PHP_SELF"] . '?action=setmod&amp;value=' . $file . '">';
							print img_picto($langs->trans("Disabled"), 'switch_off');
							print '</a>';
						}
						print '</td>';
						
						$businesscase = new ReferenceLetters($db);
						$businesscase->initAsSpecimen();
						
						// Info
						$htmltooltip = '';
						$htmltooltip .= '' . $langs->trans("Version") . ': <b>' . $module->getVersion() . '</b><br>';
						$nextval = $module->getNextValue($user->id, 'contract', '', '');
						// Keep " on nextval
						if ("$nextval" != $langs->trans("NotAvailable")) {
							$htmltooltip .= '' . $langs->trans("NextValue") . ': ';
							if ($nextval) {
								$htmltooltip .= $nextval . '<br>';
							} else {
								$htmltooltip .= $langs->trans($module->error) . '<br>';
							}
						}
						
						print '<td align="center">';
						print $form->textwithpicto('', $htmltooltip, 1, 0);
						print '</td>';
						
						print "</tr>\n";
					}
				}
			}
			closedir($handle);
		}
	}
}
print "</table><br>\n";

print '<form name="setvar" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="setvar">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td width="400px">' . $langs->trans("Value") . '</td>';
print '<td></td>';
print "</tr>\n";

print '<tr class="pair"><td>' . $langs->trans("RefLtrREF_LETTER_CREATEEVENT") . '</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax) {
	
	$input_array = array (
			'alert' => array (
					'set' => array (
							'content' => $langs->trans('RefLtrConfirmChangeState'),
							'title' => $langs->trans('RefLtrConfirmChangeState'),
							'method' => 'fnDisplayFileCopyOption',
							'yesButton' => $langs->trans('Yes'),
							'noButton' => $langs->trans('No') 
					),
					'del' => array (
							'content' => $langs->trans('RefLtrConfirmChangeState'),
							'title' => $langs->trans('RefLtrConfirmChangeState'),
							'method' => 'fnHideFileCopyOption',
							'yesButton' => $langs->trans('Yes'),
							'noButton' => $langs->trans('No') 
					) 
			) 
	);
	
	print ajax_constantonoff('REF_LETTER_CREATEEVENT', $input_array);
} else {
	$arrval = array (
			'0' => $langs->trans("No"),
			'1' => $langs->trans("Yes") 
	);
	print $form->selectarray("REF_LETTER_CREATEEVENT", $arrval, $conf->global->REF_LETTER_CREATEEVENT);
}
print '</td>';
print '<td align="center">';
print $form->textwithpicto('', $langs->trans("RefLtrHelpREF_LETTER_CREATEEVENT"), 1, 'help');
print '</td>';
print '</tr>';

if (! empty($conf->global->REF_LETTER_CREATEEVENT)) {
	print '<tr class="impair ifeventyes"><td style="padding-left:20px"> - ' . $langs->trans("RefLtrREF_LETTER_EVTCOPYFILE") . '</td>';
	print '<td align="left">';
	if ($conf->use_javascript_ajax) {
		print ajax_constantonoff('REF_LETTER_EVTCOPYFILE');
	} else {
		$arrval = array (
				'0' => $langs->trans("No"),
				'1' => $langs->trans("Yes") 
		);
		print $form->selectarray("REF_LETTER_EVTCOPYFILE", $arrval, $conf->global->REF_LETTER_EVTCOPYFILE);
	}
	print '</td>';
	print '<td align="center">';
	print $form->textwithpicto('', $langs->trans("RefLtrHelpREF_LETTER_EVTCOPYFILE"), 1, 'help');
	print '</td>';
	print '</tr>';
	
	print '<tr class="pair ifeventyes"><td style="padding-left:20px"> - ' . $langs->trans("RefLtrREF_LETTER_TYPEEVENTNAME") . '</td>';
	print '<td align="left">';
	$arrval = array (
			'normal' => $langs->trans("RefLtrREF_LETTER_TYPEEVENTNAME_normal"),
			'other' => $langs->trans("RefLtrREF_LETTER_TYPEEVENTNAME_other") 
	);
	print $form->selectarray("REF_LETTER_TYPEEVENTNAME", $arrval, $conf->global->REF_LETTER_TYPEEVENTNAME);
	print '</td>';
	print '<td align="center">';
	print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
	print '</td>';
	print '</tr>';
}

print '<tr class="pair ifeventyes"><td>' . $langs->trans("RefLtrREF_LETTER_OUTPUTREFLET") . '</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('REF_LETTER_OUTPUTREFLET');
} else {
	$arrval = array (
			'0' => $langs->trans("No"),
			'1' => $langs->trans("Yes") 
	);
	print $form->selectarray("REF_LETTER_OUTPUTREFLET", $arrval, $conf->global->REF_LETTER_OUTPUTREFLET);
}
print '</td>';
print '<td align="center">';
// print $form->textwithpicto('', $langs->trans("RefLtrHelpREF_LETTER_TYPEEVENTNAME"), 1, 'help');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>' . $langs->trans("RefLtrREF_LETTER_PREDEF_HEADER_AND_FOOTER") . '</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax) {
    $input_array = array (
        'alert' => array (
            'set' => array (
                'content' => $langs->trans('RefLtrConfirmChangeState'),
                'title' => $langs->trans('RefLtrConfirmChangeState'),
                'method' => 'fnDisplayHeaderOption',
                'yesButton' => $langs->trans('Yes'),
                'noButton' => $langs->trans('No')
            ),
            'del' => array (
                'content' => $langs->trans('RefLtrConfirmChangeState'),
                'title' => $langs->trans('RefLtrConfirmChangeState'),
                'method' => 'fnHideHeaderOption',
                'yesButton' => $langs->trans('Yes'),
                'noButton' => $langs->trans('No')
            )
        )
    );
    
    print ajax_constantonoff('REF_LETTER_PREDEF_HEADER_AND_FOOTER', $input_array);
    
} else {
    $arrval = array (
        '0' => $langs->trans("No"),
        '1' => $langs->trans("Yes")
    );
    print $form->selectarray("REF_LETTER_PREDEF_HEADER_AND_FOOTER", $arrval, $conf->global->REF_LETTER_PREDEF_HEADER_AND_FOOTER);
}
print '</td>';
print '<td align="center">';
print $form->textwithpicto('', $langs->trans("RefLtrHelpREF_LETTER_PREDEF_HEADER_AND_FOOTER"), 1, 'help');
print '</td>';
print '</tr>';

if (! empty($conf->global->REF_LETTER_PREDEF_HEADER_AND_FOOTER)) {
    
    print '<tr class="impair ifheadandfootyes"><td colspan="2">'.$langs->trans('RefLtrHeaderContent').' <br><br>';
    $doleditor=new DolEditor('REF_LETTER_PREDEF_HEADER', $conf->global->REF_LETTER_PREDEF_HEADER, '', 200, 'dolibarr_notes_encoded', '', false, true, 1, '', 70);
    $doleditor->Create();
    print '</td><td align="center">';
    print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
    print '</td>';
    print '</tr>';
    
    print '<tr class="pair ifheadandfootyes"><td colspan="2">'.$langs->trans('RefLtrFooterContent') . '<br><br>';
    $doleditor=new DolEditor('REF_LETTER_PREDEF_FOOTER', $conf->global->REF_LETTER_PREDEF_FOOTER, '', 200, 'dolibarr_notes_encoded', '', false, true, 1, '', 70);
    $doleditor->Create();
    print '</td><td align="center">';
    print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
    print '</td>';
    print '</tr>';
}


print "</table><br>\n";
if (! $conf->use_javascript_ajax) {
	print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
}

print '</form>';

llxFooter();
$db->close();