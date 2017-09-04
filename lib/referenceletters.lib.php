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
 * \file lib/referenceletters.lib.php
 * \ingroup referenceletters
 * \brief This file is an example module library
 * Put some comments here
 */
function referencelettersAdminPrepareHead() {
	global $langs, $conf;

	$langs->load("referenceletters@referenceletters");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/referenceletters/admin/admin_referenceletters.php", 1);
	$head[$h][1] = $langs->trans("ReferenceLettersSettings");
	$head[$h][2] = 'settings';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/admin/referenceletters_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'attributes';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h ++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array(
	// 'entity:+tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	// ); // to add new tab
	// $this->tabs = array(
	// 'entity:-tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	// ); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'adminreferenceletters');

	return $head;
}
function referenceletterMassPrepareHead() {
	global $langs, $conf;

	$langs->load("referenceletters@referenceletters");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/referenceletters/referenceletters/mass_gen.php', 1);
	$head[$h][1] = $langs->trans("Module103258Name");
	$head[$h][2] = 'card';
	$h ++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array(
	// 'entity:+tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	// ); // to add new tab
	// $this->tabs = array(
	// 'entity:-tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	// ); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'referencelettersmass');

	return $head;
}
function referenceletterPrepareHead($object) {
	global $langs, $conf;

	$langs->load("referenceletters@referenceletters");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters/card.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Module103258Name");
	$head[$h][2] = 'card';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters/header.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("RefLtrHeaderTab");
	$head[$h][2] = 'head';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters/footer.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("RefLtrFooterTab");
	$head[$h][2] = 'foot';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters/background.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("RefLtrBackground");
	$head[$h][2] = 'background';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters/info.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$h ++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array(
	// 'entity:+tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	// ); // to add new tab
	// $this->tabs = array(
	// 'entity:-tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	// ); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'referenceletters');

	return $head;
}

/**
 * Return a PDF instance object.
 * We create a FPDI instance that instantiate TCPDF.
 *
 * @param string $object Object
 * @param string $instance_letter Instance letters
 * @param string $format Array(width,height). Keep empty to use default setup.
 * @param string $metric Unit of format ('mm')
 * @param string $pagetype 'P' or 'l'
 * @return TCPDF PDF object
 */
function pdf_getInstance_refletters($object, $instance_letter, &$model, $format = '', $metric = 'mm', $pagetype = 'P') {
	global $conf;

	dol_include_once('/referenceletters/class/TCPDFReferenceletters.class.php');

	if ((! file_exists(TCPDF_PATH . 'tcpdf.php') && ! class_exists('TCPDFRefletters')) && ! empty($conf->global->MAIN_USE_FPDF)) {
		print 'TCPDF Must be use for this module forget TCPDI or FPDF or other PDF class, plaese contact your admnistrator';
		exit();
	}

	// Define constant for TCPDF
	if (! defined('K_TCPDF_EXTERNAL_CONFIG')) {
		define('K_TCPDF_EXTERNAL_CONFIG', 1); // this avoid using tcpdf_config file
		define('K_PATH_CACHE', DOL_DATA_ROOT . '/admin/temp/');
		define('K_PATH_URL_CACHE', DOL_DATA_ROOT . '/admin/temp/');
		dol_mkdir(K_PATH_CACHE);
		define('K_BLANK_IMAGE', '_blank.png');
		define('PDF_PAGE_FORMAT', 'A4');
		define('PDF_PAGE_ORIENTATION', 'P');
		define('PDF_CREATOR', 'TCPDF');
		define('PDF_AUTHOR', 'TCPDF');
		define('PDF_HEADER_TITLE', 'TCPDF Example');
		define('PDF_HEADER_STRING', "by Dolibarr ERP CRM");
		define('PDF_UNIT', 'mm');
		define('PDF_MARGIN_HEADER', 5);
		define('PDF_MARGIN_FOOTER', 10);
		define('PDF_MARGIN_TOP', 27);
		define('PDF_MARGIN_BOTTOM', 25);
		define('PDF_MARGIN_LEFT', 15);
		define('PDF_MARGIN_RIGHT', 15);
		define('PDF_FONT_NAME_MAIN', 'helvetica');
		define('PDF_FONT_SIZE_MAIN', 10);
		define('PDF_FONT_NAME_DATA', 'helvetica');
		define('PDF_FONT_SIZE_DATA', 8);
		define('PDF_FONT_MONOSPACED', 'courier');
		define('PDF_IMAGE_SCALE_RATIO', 1.25);
		define('HEAD_MAGNIFICATION', 1.1);
		define('K_CELL_HEIGHT_RATIO', 1.25);
		define('K_TITLE_MAGNIFICATION', 1.3);
		define('K_SMALL_RATIO', 2 / 3);
		define('K_THAI_TOPCHARS', true);
		define('K_TCPDF_CALLS_IN_HTML', true);
		define('K_TCPDF_THROW_EXCEPTION_ERROR', false);
	}

	require_once TCPDF_PATH . 'tcpdf.php';

	$pdf = new TCPDFRefletters($pagetype, $metric, $format);
	$pdf->ref_object = $object;
	$pdf->instance_letter = $instance_letter;
	$pdf->model = $model;

	// We need to instantiate tcpdi or fpdi object (instead of tcpdf) to use merging features. But we can disable it (this will break all merge features).
	/*if (empty($conf->global->MAIN_DISABLE_TCPDI))
		require_once TCPDI_PATH . 'tcpdi.php';
	else if (empty($conf->global->MAIN_DISABLE_FPDI))
		require_once FPDI_PATH . 'fpdi.php';*/

	// $arrayformat=pdf_getFormat();
	// $format=array($arrayformat['width'],$arrayformat['height']);
	// $metric=$arrayformat['unit'];

	// Protection and encryption of pdf
	/*if (empty($conf->global->MAIN_USE_FPDF) && ! empty($conf->global->PDF_SECURITY_ENCRYPTION))
	 {
	 // Permission supported by TCPDF
	 // - print : Print the document;
	 // - modify : Modify the contents of the document by operations other than those controlled by 'fill-forms', 'extract' and 'assemble';
	 // - copy : Copy or otherwise extract text and graphics from the document;
	 // - annot-forms : Add or modify text annotations, fill in interactive form fields, and, if 'modify' is also set, create or modify interactive form fields (including signature fields);
	 // - fill-forms : Fill in existing interactive form fields (including signature fields), even if 'annot-forms' is not specified;
	 // - extract : Extract text and graphics (in support of accessibility to users with disabilities or for other purposes);
	 // - assemble : Assemble the document (insert, rotate, or delete pages and create bookmarks or thumbnail images), even if 'modify' is not set;
	 // - print-high : Print the document to a representation from which a faithful digital copy of the PDF content could be generated. When this is not set, printing is limited to a low-level representation of the appearance, possibly of degraded quality.
	 // - owner : (inverted logic - only for public-key) when set permits change of encryption and enables all other permissions.
	 //
	 if (class_exists('TCPDI')) $pdf = new TCPDI($pagetype,$metric,$format);
	 else if (class_exists('FPDI')) $pdf = new FPDI($pagetype,$metric,$format);
	 else $pdf = new TCPDF($pagetype,$metric,$format);
	 //$pdf->ref_object= $object;
	 //$pdf->instance_letter= $instance_letter;

	 // For TCPDF, we specify permission we want to block
	 $pdfrights = array('modify','copy');

	 $pdfuserpass = ''; // Password for the end user
	 $pdfownerpass = NULL; // Password of the owner, created randomly if not defined
	 $pdf->SetProtection($pdfrights,$pdfuserpass,$pdfownerpass);
	 }
	 else
	 {
	 if (class_exists('TCPDI')) $pdf = new TCPDI($pagetype,$metric,$format);
	 else if (class_exists('FPDI')) $pdf = new FPDI($pagetype,$metric,$format);
	 else $pdf = new TCPDF($pagetype,$metric,$format,true, 'UTF-8', false, false);
	 //$pdf->ref_object= $object;
	 $pdf->instance_letter= $instance_letter;
	 }*/

	return $pdf;
}