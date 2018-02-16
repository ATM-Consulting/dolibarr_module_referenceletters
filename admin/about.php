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
 * \file		admin/about.php
 * \ingroup	referenceletters
 * \brief		This file is an example about page
 * Put some comments here
 */
// Dolibarr environment
$res = @include ("../../main.inc.php"); // From htdocs directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // From "custom" directory

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once "../lib/referenceletters.lib.php";

dol_include_once('/referenceletters/lib/php-markdown/markdown.php');

// require_once "../class/myclass.class.php";
// Translations
$langs->load("referenceletters@referenceletters");

// Access control
if (! $user->admin)
	accessforbidden();

	// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "ReferenceLettersAbout";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = referencelettersAdminPrepareHead();
dol_fiche_head($head, 'about', $langs->trans("Module103258Name"), 0, "referenceletters@referenceletters");

// About page goes here
echo $langs->trans("ReferenceLettersAboutPage");


print '<BR><BR><BR><BR>--------------------------------';
print '<BR><a href="http://wiki.atm-consulting.fr/index.php/DocEdit_pour_Agefodd/Documentation_utilisateur" target="_blanck">Lien Documentation Utilisateur Français</a>';
print '<BR><a href="http://www.open-concept.pro/images/doc/Letters%20Templates%20-%20User%20guide.pdf" target="_blanck">Link english user guide</a>';
print '<BR><a href="http://www.open-concept.pro/images/doc/Plantillas%20de%20correos%20-%20Guia%20del%20usuario.pdf" target="_blanck">Link spanish Guía del usuario</a>';
print '<BR>';

echo '<br>';

$buffer = file_get_contents(dol_buildpath('/referenceletters/README.md', 0));
echo Markdown($buffer);

echo '<br>',
'<a href="' . dol_buildpath('/referenceletters/COPYING', 1) . '">',
'<img src="' . dol_buildpath('/referenceletters/img/gplv3.png', 1) . '"/>',
'</a>';


// Page end
dol_fiche_end();
llxFooter();
$db->close();