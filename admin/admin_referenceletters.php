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
 * \file		admin/referenceletters.php
 * \ingroup	referenceletters
 * \brief		This file is an example module setup page
 * Put some comments here
 */
// Dolibarr environment
$res = @include ("../../main.inc.php"); // From htdocs directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // From "custom" directory
		                                          
// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once "../lib/referenceletters.lib.php";
require_once "../class/referenceletters.class.php";
// Translations
$langs->load("referenceletters@referenceletters");

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
	if ($maskconstlead)
		$res = dolibarr_set_const($db, $maskconstrefleter, $maskrefletter, 'chaine', 0, '', $conf->entity);
	
	if (! $res > 0)
		$error ++;
	
	if (! $error) {
		setEventMessage($langs->trans("SetupSaved"), 'mesgs');
	} else {
		setEventMessage($langs->trans("Error"), 'errors');
	}
} 
else if ($action == 'setmod') {
	dolibarr_set_const($db, "REF_LETTER_ADDON", $value, 'chaine', 0, '', $conf->entity);
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

/*
 * Module numerotation
 */
print_titre($langs->trans($page_name));

$dirmodels = array_merge(array(
	'/'
), (array) $conf->modules_parts['models']);

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

foreach ($dirmodels as $reldir) {
	$dir = dol_buildpath($reldir . "core/modules/referenceletters/");
	
	if (is_dir($dir)) {
		$handle = opendir($dir);
		if (is_resource($handle)) {
			$var = true;
			
			while (($file = readdir($handle)) !== false) {
				
				if (preg_match('/mod_referenceletters_/',$file) && substr($file, dol_strlen($file) - 3, 3) == 'php') {
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
						$nextval = $module->getNextValue($user->id, 'contract', $propal);
						if ("$nextval" != $langs->trans("NotAvailable")) 						// Keep " on nextval
						{
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



llxFooter();
$db->close();