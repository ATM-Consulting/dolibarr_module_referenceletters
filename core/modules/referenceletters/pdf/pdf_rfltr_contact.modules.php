<?php
/* References letters
 * Copyright (C) 2014  HENRY Florian  florian.henry@open-concept.pro
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 * \file refferenceletters/core/modules/refferenceletters/pdf_rfltr_contact.modules.php
 * \ingroup refferenceletters
 * \brief Class file to create PDF for letter's model on contract
 */
dol_include_once('/refferenceletters/core/modules/refferenceletters/modules_referenceletters.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

/**
 * Class to generate PDF ModelePDFReferenceLetters
 */
class pdf_rfltr_contact extends ModelePDFReferenceLetters
{
	var $db;
	var $name;
	var $description;
	var $type;
	var $version = 'dolibarr';
	var $page_largeur;
	var $page_hauteur;
	var $format;
	var $marge_gauche;
	var $marge_droite;
	var $marge_haute;
	var $marge_basse;
	var $emetteur; // Objet societe qui emet
	
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	function __construct($db) {
		global $conf, $langs, $mysoc;
		
		$langs->load("main");
		$langs->load("bills");
		$langs->load("referenceletters@referenceletters");
		
		$this->db = $db;
		$this->name = "referenceletter_contact";
		$this->description = $langs->trans('Module103258Name');
		
		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array (
				$this->page_largeur,
				$this->page_hauteur 
		);
		$this->marge_gauche = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
		$this->marge_droite = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
		$this->marge_haute = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
		$this->marge_basse = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;
		
		$this->option_logo = 1; // Affiche logo
		                        
		// Get source company
		$this->emetteur = $mysoc;
		if (empty($this->emetteur->country_code))
			$this->emetteur->country_code = substr($langs->defaultlang, - 2); // By default, if was not defined
	}
	
	/**
	 * Function to build pdf onto disk
	 *
	 * @param Object $object Object to generate
	 * @param Object $instance_letter Object to generate
	 * @param Translate $outputlangs Lang output object
	 * @return int 1=OK, 0=KO
	 */
	function write_file($object, $instance_letter, $outputlangs) {
		global $user, $langs, $conf, $mysoc, $hookmanager;
		
		if (! is_object($outputlangs))
			$outputlangs = $langs;
			// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF))
			$outputlangs->charset_output = 'ISO-8859-1';
		
		$outputlangs->load("main");
		$outputlangs->load("companies");
		$outputlangs->load("referenceletters@referenceletters");
		
		$nblignes = count($object->lines);
		
		// Loop on each lines to detect if there is at least one image to show
		$realpatharray = array ();
		
		if ($conf->referenceletters->dir_output) {
			$object->fetch_thirdparty();
			
			// $deja_regle = 0;
			
			$objectref = dol_sanitizeFileName($instance_letter->ref_int);
			$dir = $conf->referenceletters->dir_output . "/contact/" . $objectref;
			$file = $dir . '/' . $objectref . ".pdf";
			
			if (! file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
					return 0;
				}
			}
			
			if (file_exists($dir)) {
				// Create pdf instance
				$pdf = pdf_getInstance($this->format);
				$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
				$heightforinfotot = 50; // Height reserved to output the info and total part
				$heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5); // Height reserved to output the free text on last page
				$heightforfooter = $this->marge_basse + 8; // Height reserved to output the footer (value include bottom margin)
				$pdf->SetAutoPageBreak(1, 0);
				
				if (class_exists('TCPDF')) {
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output . '/' . $conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}
				
				$pdf->Open();
				$pagenb = 0;
				$pdf->SetDrawColor(128, 128, 128);
				
				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Module103258Name"));
				$pdf->SetCreator("Dolibarr " . DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($instance_letter->ref_int) . " " . $outputlangs->transnoentities("Module103258Name"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION))
					$pdf->SetCompression(false);
				
				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right
				                                                                                
				// New page
				$pdf->AddPage();
				if (! empty($tplidx))
					$pdf->useTemplate($tplidx);
				$pagenb ++;
				
				importImageBackground($pdf,$outputlangs,$instance_letter->fk_referenceletters);
				
				$this->_pagehead($pdf, $object, 1, $outputlangs, $instance_letter);
				
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->SetTextColor(0, 0, 0);
				
				$tab_top = 90;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 42 : 10);
				$tab_height = 130;
				$tab_height_newpage = 150;
				
				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;
				
				$posY = $nexY;
				$posX = $this->marge_gauche;
				
				foreach ( $instance_letter->content_letter as $key => $line_chapter ) {
					
					$pdf->SetXY($posX, $posY);
					
					$chapter_text = $line_chapter['content_text'];
					
					if ($chapter_text == '@breakpage@') {
						$this->_pagefoot($pdf, $object, $outputlangs);
						if (method_exists($pdf, 'AliasNbPages'))
							$pdf->AliasNbPages();
						$pdf->AddPage();
						if (! empty($tplidx))
							$pdf->useTemplate($tplidx);
						$pagenb ++;
						
						$this->_pagehead($pdf, $object, 1, $outputlangs, $instance_letter);
						
						$posX = $pdf->getX();
						$posY = $pdf->getY();
						
						continue;
					}
					
					if ($chapter_text == '@breakpagenohead@') {
						$this->_pagefoot($pdf, $object, $outputlangs);
						if (method_exists($pdf, 'AliasNbPages'))
							$pdf->AliasNbPages();
						$pdf->AddPage();
						if (! empty($tplidx))
							$pdf->useTemplate($tplidx);
						$pagenb ++;
						
						$posY = $this->marge_haute;
						$posX = $this->marge_gauche;
						$pdf->SetXY($posX, $posY);
						$pdf->SetTextColor(0, 0, 0);
						
						continue;
					}
					
					// User substitution value
					$tmparray = $this->get_substitutionarray_user($user, $outputlangs);
					$substitution_array = array ();
					if (is_array($tmparray) && count($tmparray) > 0) {
						foreach ( $tmparray as $key => $value ) {
							$substitution_array['{' . $key . '}'] = $value;
						}
						$chapter_text = str_replace(array_keys($substitution_array), array_values($substitution_array), $chapter_text);
					}
					
					$tmparray = $this->get_substitutionarray_mysoc($mysoc, $outputlangs);
					$substitution_array = array ();
					if (is_array($tmparray) && count($tmparray) > 0) {
						foreach ( $tmparray as $key => $value ) {
							$substitution_array['{' . $key . '}'] = $value;
						}
						$chapter_text = str_replace(array_keys($substitution_array), array_values($substitution_array), $chapter_text);
					}
					
					$tmparray = $this->get_substitutionarray_thirdparty($object->thirdparty, $outputlangs);
					$substitution_array = array ();
					if (is_array($tmparray) && count($tmparray) > 0) {
						foreach ( $tmparray as $key => $value ) {
							$substitution_array['{cust_' . $key . '}'] = $value;
						}
						$chapter_text = str_replace(array_keys($substitution_array), array_values($substitution_array), $chapter_text);
					}
					
					$tmparray = $this->get_substitutionarray_other($outputlangs);
					$substitution_array = array ();
					if (is_array($tmparray) && count($tmparray) > 0) {
						foreach ( $tmparray as $key => $value ) {
							$substitution_array['{' . $key . '}'] = $value;
						}
						$chapter_text = str_replace(array_keys($substitution_array), array_values($substitution_array), $chapter_text);
					}
					
					$tmparray = $this->get_substitutionarray_contact($object, $outputlangs);
					$substitution_array = array ();
					if (is_array($tmparray) && count($tmparray) > 0) {
						foreach ( $tmparray as $key => $value ) {
							$substitution_array['{' . $key . '}'] = $value;
						}
						$chapter_text = str_replace(array_keys($substitution_array), array_values($substitution_array), $chapter_text);
					}
					
					$test = $pdf->writeHTMLCell(0, 0, $posX, $posY, $outputlangs->convToOutputCharset($chapter_text), 0, 1, false, true);
					// var_dump($test);
					if (is_array($line_chapter['options']) && count($line_chapter['options']) > 0) {
						foreach ( $line_chapter['options'] as $keyoption => $option_detail ) {
							if (! empty($option_detail['use_content_option'])) {
								$posY = $pdf->GetY();
								$pdf->SetXY($posX, $posY);
								
								$pdf->writeHTMLCell(0, 0, $posX + 3, $posY, '<b>-</b> ' . $outputlangs->convToOutputCharset($option_detail['text_content_option']), 0, 1);
							}
						}
					}
					$posY = $pdf->GetY();
				}
				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages'))
					$pdf->AliasNbPages();
				
				$pdf->Close();
				
				$pdf->Output($file, 'F');
				
				// Add pdfgeneration hook
				$hookmanager->initHooks(array (
						'pdfgeneration' 
				));
				$parameters = array (
						'file' => $file,
						'object' => $object,
						'outputlangs' => $outputlangs,
						'instance_letter' => $instance_letter 
				);
				global $action;
				$reshook = $hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
				
				if (! empty($conf->global->MAIN_UMASK))
					@chmod($file, octdec($conf->global->MAIN_UMASK));
				
				return 1; // Pas d'erreur
			} else {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		} else {
			$this->error = $langs->trans("ErrorConstantNotDefined", "PROP_OUTPUTDIR");
			return 0;
		}
		
		$this->error = $langs->trans("ErrorUnknown");
		return 0; // Erreur par defaut
	}
	
	/**
	 * Show top header of page.
	 *
	 * @param PDF &$pdf Object PDF
	 * @param Object $object Object to show
	 * @param int $showaddress 0=no, 1=yes
	 * @param Translate $outputlangs Object lang for output
	 * @return void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $instance_letter) {
		global $conf, $langs;
		
		$outputlangs->load("main");
		$outputlangs->load("bills");
		$outputlangs->load("dict");
		$outputlangs->load("propal");
		$outputlangs->load("companies");
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		
		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);
		
		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);
		
		$posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - 100;
		
		$pdf->SetXY($this->marge_gauche, $posy);
		
		// Logo
		$logo = $conf->mycompany->dir_output . '/logos/' . $this->emetteur->logo;
		if ($this->emetteur->logo) {
			if (is_readable($logo)) {
				$height = pdf_getHeightForLogo($logo);
				$pdf->Image($logo, $this->marge_gauche, $posy, 0, $height); // width=0 (auto)
			} else {
				$pdf->SetTextColor(200, 0, 0);
				$pdf->SetFont('', 'B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		} else {
			$text = $this->emetteur->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}
		
		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$title = $outputlangs->convToOutputCharset($instance_letter->title);
		$pdf->MultiCell(100, 4, $title, '', 'R');
		
		$pdf->SetFont('', 'B', $default_font_size);
		
		$posy += 5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref") . " : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');
		
		$posy += 5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("RefLtrRef") . " : " . $outputlangs->convToOutputCharset($instance_letter->ref_int), '', 'R');
		
		$posy += 1;
		$pdf->SetFont('', '', $default_font_size - 1);
		
		if ($object->ref_client) {
			$posy += 5;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("RefCustomer") . " : " . $outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}
		
		$posy += 4;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("Date") . " : " . dol_print_date(dol_now(), "day", false, $outputlangs, true), '', 'R');
		
		if ($object->thirdparty->code_client) {
			$posy += 4;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("CustomerCode") . " : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}
		
		$posy += 2;
		
		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);
		
		if ($showaddress) {
			// Sender properties
			$carac_emetteur = '';
			// Add internal contact of proposal if defined
			/*$arrayidcontact=$object->getIdContact('internal','SALESREPFOLL');
			 if (count($arrayidcontact) > 0)
			 {
			 $object->fetch_user($arrayidcontact[0]);
			 $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->transnoentities("Name").": ".$outputlangs->convToOutputCharset($object->user->getFullName($outputlangs))."\n";
			 }*/
			
			$carac_emetteur .= pdf_build_address($outputlangs, $this->emetteur);
			
			// Show sender
			$posy = 42;
			$posx = $this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT))
				$posx = $this->page_largeur - $this->marge_droite - 80;
			$hautcadre = 45;
			
			// Show sender frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx, $posy - 5);
			$pdf->MultiCell(66, 5, $outputlangs->transnoentities("BillFrom") . ":", 0, 'L');
			$pdf->SetXY($posx, $posy);
			$pdf->SetFillColor(230, 230, 230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$pdf->SetTextColor(0, 0, 60);
			
			// Show sender name
			$pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy = $pdf->getY();
			
			// Show sender information
			$pdf->SetXY($posx + 2, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');
			
			// If CUSTOMER contact defined, we use it
			$usecontact = false;
			$arrayidcontact = $object->getIdContact('external', 'CUSTOMER');
			if (count($arrayidcontact) > 0) {
				$usecontact = true;
				$result = $object->fetch_contact($arrayidcontact[0]);
			}
			
			// Recipient name
			if (! empty($usecontact)) {
				// On peut utiliser le nom de la societe du contact
				if (! empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT))
					$socname = $object->contact->socname;
				else
					$socname = $object->client->nom;
				$carac_client_name = $outputlangs->convToOutputCharset($socname);
			} else {
				$carac_client_name = $outputlangs->convToOutputCharset($object->client->nom);
			}
			
			$carac_client = pdf_build_address($outputlangs, $this->emetteur, $object, ($usecontact ? $object : ''), $usecontact, 'target');
			
			// Show recipient
			$widthrecbox = 100;
			if ($this->page_largeur < 210)
				$widthrecbox = 84; // To work with US executive format
			$posy = 42;
			$posx = $this->page_largeur - $this->marge_droite - $widthrecbox;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT))
				$posx = $this->marge_gauche;
				
				// Show recipient frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx + 2, $posy - 5);
			$pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillTo") . ":", 0, 'L');
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);
			
			// Show recipient name
			$pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 4, $carac_client_name, 0, 'L');
			
			// Show recipient information
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->SetXY($posx + 2, $posy + 4 + (dol_nboflines_bis($carac_client_name, 50) * 4));
			$pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
		}
		
		$pdf->SetTextColor(0, 0, 0);
	}
	
	/**
	 * Show footer of page.
	 * Need this->emetteur object
	 *
	 * @param PDF &$pdf PDF
	 * @param Object $object Object to show
	 * @param Translate $outputlangs Object lang for output
	 * @param int $hidefreetext 1=Hide free text
	 * @return int Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0) {
		$pdf->SetX($this->marge_gauche);
		return pdf_pagefoot($pdf, $outputlangs, '', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, 0, $hidefreetext);
	}
}
