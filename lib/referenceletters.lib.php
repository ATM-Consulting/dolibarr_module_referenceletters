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
 * \file    referenceletters/lib/referenceletters.lib.php
 * \ingroup referenceletters
 * \brief   Library files with common functions for Referenceletters
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function referencelettersAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("referenceletters@referenceletters");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/referenceletters/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;


	$head[$h][0] = dol_buildpath("/referenceletters/admin/referenceletters_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'referenceletters_extrafields';
	$h++;


	$head[$h][0] = dol_buildpath("/referenceletters/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@referenceletters:/referenceletters/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'referenceletters@referenceletters');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'referenceletters@referenceletters', 'remove');

	return $head;
}

function referencelettersPrepareHead($object) {
	global $langs, $conf;

	$langs->load("referenceletters@referenceletters");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/referenceletters/referenceletters_card.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/header.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("RefLtrHeaderTab");
	$head[$h][2] = 'head';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/footer.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("RefLtrFooterTab");
	$head[$h][2] = 'foot';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/background.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("RefLtrBackground");
	$head[$h][2] = 'background';
	$h ++;

	$head[$h][0] = dol_buildpath("/referenceletters/info.php", 1) . '?id=' . $object->id;
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
		print 'TCPDF Must be use for this module forget TCPDI or FPDF or other PDF class, please contact your admnistrator';
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

/**
 * Create a document onto disk according to template module.
 *
 * @param DoliDB $db Database handler
 * @param object $object Object proposal
 * @param object $instance_letter Instance letter
 * @param Translate $outputlangs Object langs to use for output
 * @param string $element_type element type
 * @return int 0 if KO, 1 if OK
 */
function referenceletters_pdf_create($db, $object, $instance_letter, $outputlangs, $element_type) {
	global $conf, $user, $langs;

	$error = 0;
	$filefound = 0;

	// Search template files
	$file = dol_buildpath('/referenceletters/core/modules/referenceletters/pdf/pdf_rfltr_' . $element_type . '.modules.php');
	if (file_exists($file)) {
		$filefound = 1;
	}

	$classname = 'pdf_rfltr_' . $element_type;
	// Charge le modele
	if ($filefound) {
		require_once $file;

		/** @var pdf_rfltr_propal|pdf_rfltr_order|pdf_rfltr_invoice|pdf_rfltr_contract|pdf_rfltr_thirdparty|pdf_rfltr_contact|pdf_rfltr_supplier_proposal|pdf_rfltr_order_supplier|pdf_rfltr_shipping $obj */
		$obj = new $classname($db);

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$res = $obj->write_file($object, $instance_letter, $outputlangs);
		if ($res > 0) {
			return 1;
		} else {
			setEventMessage('referenceletters_pdf_create Error: ' . $obj->error, 'errors');
			return - 1;
		}
	} else {
		setEventMessage($langs->trans("Error") . " " . $langs->trans("ErrorFileDoesNotExists", $file), 'errors');
		return - 1;
	}
}

/**
 *
 * @param object $pdf
 * @param int $id
 */
function importImageBackground(&$pdf, $id) {
	global $conf;
	if (empty($conf->global->MAIN_DISABLE_FPDI)) {

		require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

		// add doc from attached files of training
		$upload_dir = $conf->referenceletters->dir_output . "/referenceletters/" . $id;
		$filearray = dol_dir_list($upload_dir, "files", 0, '\.pdf$', '\.meta$', "name", SORT_ASC, 1);
		if (is_array($filearray) && count($filearray) > 0) {
			// Take first PDF file added
			$filedetail = reset($filearray);
			if (file_exists($filedetail['fullname'])) {
				$count = $pdf->setSourceFile($filedetail['fullname']);
				// import only first pages
				if ($count > 0) {
					$tplIdx = $pdf->importPage(1);
					if ($tplIdx !== false) {
						$pdf->useTemplate($tplIdx);
					} else {
						setEventMessages(null, array(
							$filedetail['fullname'] . ' cannot be added to current doc, probably Protected PDF'
						), 'warnings');
					}
				}
			}
		}
	}
}

function referenceletterMassPrepareHead() {
	global $langs, $conf;

	$langs->load("referenceletters@referenceletters");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/referenceletters/mass_gen.php', 1);
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
