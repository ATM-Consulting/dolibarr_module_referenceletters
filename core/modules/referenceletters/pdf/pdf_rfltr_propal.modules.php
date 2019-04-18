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
 * \file referenceletters/core/modules/referenceletters/pdf_rfltr_contract.modules.php
 * \ingroup referenceletters
 * \brief Class file to create PDF for letter's model on contract
 */
dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');
dol_include_once('/referenceletters/lib/referenceletters.lib.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

/**
 * Class to generate PDF ModelePDFReferenceLetters
 */
class pdf_rfltr_propal extends ModelePDFReferenceLetters
{
	public $db;
	public $name;
	public $description;
	public $type;
	public $version = 'dolibarr';
	public $page_largeur;
	public $page_hauteur;
	public $format;
	public $marge_gauche;
	public $marge_droite;
	public $marge_haute;
	public $marge_basse;
	public $emetteur; // Objet societe qui emet

	/**
	 * Constructor
	 *
	 * @param DoliDB $db handler
	 */
	function __construct($db) {
		global $conf, $langs, $mysoc;

		$langs->load("main");
		$langs->load("bills");
		$langs->load("referenceletters@referenceletters");

		$this->db = $db;
		$this->name = "referenceletter_propal";
		$this->description = $langs->trans('Module103258Name');

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array(
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
		global $user, $langs, $conf, $hookmanager;

		$this->outputlangs=$outputlangs;
		$this->instance_letter = $instance_letter;

		$use_landscape_format = (int)$instance_letter->use_landscape_format;

		if (! is_object($this->outputlangs))
			$this->outputlangs = $langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF))
			$this->outputlangs->charset_output = 'ISO-8859-1';

		$this->outputlangs->load("main");
		$this->outputlangs->load("companies");
		$this->outputlangs->load("referenceletters@referenceletters");

		// Loop on each lines to detect if there is at least one image to show

		if ($conf->referenceletters->dir_output) {
			$object->fetch_thirdparty();
			if (!empty($object->thirdparty->country_code))
			{
				$this->outputlangs->load("dict");
				$object->thirdparty->country=$this->outputlangs->transnoentitiesnoconv("Country".$object->thirdparty->country_code);
			}

			$objectref = dol_sanitizeFileName($instance_letter->ref_int);
			$dir = $conf->referenceletters->dir_output . "/propal/" . $objectref;
			$file = $dir . '/' . $objectref . ".pdf";

			if (! file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
					return 0;
				}
			}

			if (file_exists($dir)) {
				// Create pdf instance
				$this->pdf = pdf_getInstance_refletters($object, $instance_letter, $this, $this->format);

				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$this->outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);

				$default_font_size = pdf_getPDFFontSize($this->outputlangs); // Must be after pdf_getInstance

				// Set calculation of header and footer high line
				// footer high
				$height = $this->getRealHeightLine('foot');
				$this->pdf->SetAutoPageBreak(1, $height);

				$this->pdf->setPrintHeader(true);
				$this->pdf->setPrintFooter(true);

				$this->pdf->SetFont(pdf_getPDFFont($this->outputlangs));
				// Set path to the background PDF File
				if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
					$pagecount = $this->pdf->setSourceFile($conf->mycompany->dir_output . '/' . $conf->global->MAIN_ADD_PDF_BACKGROUND);
					if ($pagecount>0) {
						$tplidx = $this->pdf->importPage(1);
					}
				}

				$this->pdf->Open();
				$this->pdf->SetDrawColor(128, 128, 128);

				$this->pdf->SetTitle($this->outputlangs->convToOutputCharset($object->ref));
				$this->pdf->SetSubject($this->outputlangs->transnoentities("Module103258Name"));
				$this->pdf->SetCreator("Dolibarr " . DOL_VERSION);
				$this->pdf->SetAuthor($this->outputlangs->convToOutputCharset($user->getFullName($this->outputlangs)));
				$this->pdf->SetKeyWords($this->outputlangs->convToOutputCharset($instance_letter->ref_int) . " " . $this->outputlangs->transnoentities("Module103258Name"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) {
					$this->pdf->SetCompression(false);
				}

				// Set calculation of header and footer high line
				// Header high
				$height = $this->getRealHeightLine('head');
				if (!empty($conf->global->REF_LETTER_PREDEF_HIGHT) && !empty($instance_letter->use_custom_header)) {
					$height=$height+$conf->global->REF_LETTER_PREDEF_HIGHT;
				} else {
					$height=$height+10;
				}
				// Left, Top, Right
				$this->pdf->SetMargins($this->marge_gauche, $height, $this->marge_droite, 1);

				// New page
				$this->pdf->AddPage(empty($use_landscape_format) ? 'P' : 'L', $this->format, true);
				if (! empty($tplidx)) {
					$this->pdf->useTemplate($tplidx);
				}

				$this->pdf->SetFont('', '', $default_font_size - 1);
				$this->pdf->SetTextColor(0, 0, 0);

				$posY = $this->pdf->getY();
				$posX = $this->marge_gauche;

				foreach ( $instance_letter->content_letter as $key => $line_chapter ) {

					$this->pdf->SetXY($posX, $posY);

					$chapter_text = $line_chapter['content_text'];

					if ($chapter_text == '@breakpage@') {
						if (method_exists($this->pdf, 'AliasNbPages'))
							$this->pdf->AliasNbPages();

						$this->pdf->AddPage(empty($use_landscape_format) ? 'P' : 'L');
						if (! empty($tplidx)) {
							$this->pdf->useTemplate($tplidx);
						}

						$this->pdf->setPrintFooter(true);

						$posX = $this->pdf->getX();
						$posY = $this->pdf->getY();

						continue;
					}

					if ($chapter_text == '@breakpagenohead@') {
						if (method_exists($this->pdf, 'AliasNbPages')) {
							$this->pdf->AliasNbPages();
						}

						$this->pdf->setPrintFooter(true);

						$this->pdf->setPrintHeader(false);

						$this->pdf->AddPage(empty($use_landscape_format) ? 'P' : 'L');
						if (! empty($tplidx)) {
							$this->pdf->useTemplate($tplidx);
						}

						$posY = $this->marge_haute;
						$posX = $this->marge_gauche;
						$this->pdf->SetXY($posX, $posY);
						$this->pdf->SetTextColor(0, 0, 0);

						$this->pdf->setPrintHeader(true);

						continue;
					}

                    if (strpos($chapter_text,'@pdfdoc')===0) {

	                    $documentModel=str_replace('@','',str_replace('pdfdoc_','',$chapter_text));

	                    $hidedetails = (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0);
	                    $hidedesc =(! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0);
	                    $hideref = (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0);

	                    $result= $object->generateDocument($documentModel, $this->outputlangs, $hidedetails, $hidedesc, $hideref, null);
						if ($result <= 0)
						{
							setEventMessages($object->error, $object->errors, 'errors');
						} else {
							$this->pdf->setPrintHeader(false);
							$objectrefpdf = dol_sanitizeFileName($object->ref);
							$dirpdf = $conf->propal->multidir_output[$object->entity] . "/" . $objectrefpdf;
							$filepdf = $dirpdf . "/" . $objectrefpdf . ".pdf";
							$pagecounttmp = $this->pdf->setSourceFile($filepdf);
							if ($pagecounttmp>=1) {

								for ($idocpdf = 1; $idocpdf <= $pagecounttmp; $idocpdf++) {
									$tplidxdoc = $this->pdf->ImportPage($idocpdf);
									if (!empty($tplidxdoc)) {
										$s = $this->pdf->getTemplatesize($tplidxdoc);
										$this->pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
										$this->pdf->setPrintFooter(false);
										$this->pdf->useTemplate($tplidxdoc);
									}
								}
							} else {
								dol_syslog("Error: Can't read PDF content with setSourceFile, for file " . $file, LOG_ERR);
							}
							$this->pdf->setPrintHeader(true);

						}

	                    $posY = $this->marge_haute;
	                    $posX = $this->marge_gauche;
	                    $this->pdf->SetXY($posX, $posY);
	                    $this->pdf->SetTextColor(0, 0, 0);

                        continue;
                    }

					// Remplacement des tags par les bonnes valeurs
					$chapter_text = $this->setSubstitutions($object, $chapter_text, $this->outputlangs);

					// Merge arrays
					$chapter_text = $this->merge_array($object, $chapter_text, array(
							'lines'
					));

					$test_array = explode('@breakpage@', $chapter_text);
					foreach ($test_array as $chapter_text){
						$chapter_text = strtr($chapter_text, array('<text:line-break/>'=>'<br />')); // Pas trouvé d'autre moyen de remplacer les sauts de lignes généras par l'objet odf dans merge_array()...
						$test = $this->pdf->writeHTMLCell(0, 0, $posX, $posY, $this->outputlangs->convToOutputCharset($chapter_text), 0, 1, false, true);
						// var_dump($test);
						if (is_array($line_chapter['options']) && count($line_chapter['options']) > 0) {
							foreach ( $line_chapter['options'] as $keyoption => $option_detail ) {
								if (! empty($option_detail['use_content_option'])) {
									$posY = $this->pdf->GetY();
									$this->pdf->SetXY($posX, $posY);

									$this->pdf->writeHTMLCell(0, 0, $posX + 3, $posY, '<b>-</b> ' . $this->outputlangs->convToOutputCharset($option_detail['text_content_option']), 0, 1);
								}
							}
						}
						if (count($test_array)>1) {
							$posY = $this->page_hauteur -5; // force le saut de page en se rendant dans le pied de page
						} else {
							$posY = $this->pdf->GetY();
						}

					}

					if(count($test_array) > 1) {
						//comment because seems to not be need. Actually remove the last attestaion page en loop on pages
						if(! empty($conf->global->REF_LETTER_DELETE_LAST_BREAKPAGE_FROM_LOOP)) $this->pdf->deletePage($this->pdf->getPage());
					}
				}
				// Pied de page
				if (method_exists($this->pdf, 'AliasNbPages'))
					$this->pdf->AliasNbPages();

				$this->pdf->Close();

				$this->pdf->Output($file, 'F');

				$parameters = array(
						'file' => $file,
						'object' => $object,
						'outputlangs' => $this->outputlangs,
						'instance_letter' => $instance_letter
				);
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
	 * @param Object $object to show
	 * @param int $showaddress 0=no, 1=yes
	 * @param Translate $outputlangs Object lang for output
	 * @return void
	 */
	function _pagehead($object, $showaddress, $outputlangs) {
		global $conf;

		$outputlangs->load("main");
		$outputlangs->load("bills");
		$outputlangs->load("propal");
		$outputlangs->load("companies");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($this->pdf, $outputlangs, $this->page_hauteur);

		$this->pdf->SetTextColor(0, 0, 60);
		$this->pdf->SetFont('', 'B', $default_font_size + 3);

		$posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - 100;

		$this->pdf->SetXY($this->marge_gauche, $posy);

		// Logo
		$logo = $conf->mycompany->dir_output . '/logos/' . $this->emetteur->logo;
		if ($this->emetteur->logo) {
			if (is_readable($logo)) {
				$height = pdf_getHeightForLogo($logo);
				$this->pdf->Image($logo, $this->marge_gauche, $posy, 0, $height); // width=0 (auto)
			} else {
				$this->pdf->SetTextColor(200, 0, 0);
				$this->pdf->SetFont('', 'B', $default_font_size - 2);
				$this->pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
				$this->pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		} else {
			$text = $this->emetteur->name;
			$this->pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

		if (! empty($this->instance_letter->outputref)) {
			$this->pdf->SetFont('', 'B', $default_font_size + 3);
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$title = $outputlangs->convToOutputCharset($this->instance_letter->title_referenceletters);
			$this->pdf->MultiCell(100, 4, $title, '', 'R');
			$posy += 5;
		}

		$this->pdf->SetFont('', 'B', $default_font_size);

		$this->pdf->SetXY($posx, $posy);
		$this->pdf->SetTextColor(0, 0, 60);
		$this->pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref") . " : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');

		if (! empty($this->instance_letter->outputref)) {
			$posy += 5;
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$this->pdf->MultiCell(100, 4, $outputlangs->transnoentities("RefLtrRef") . " : " . $outputlangs->convToOutputCharset($this->instance_letter->ref_int), '', 'R');
		}

		$posy += 1;
		$this->pdf->SetFont('', '', $default_font_size - 1);

		if ($object->ref_client) {
			$posy += 5;
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$this->pdf->MultiCell(100, 3, $outputlangs->transnoentities("RefCustomer") . " : " . $outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}

		if ($object->thirdparty->code_client) {
			$posy += 4;
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$this->pdf->MultiCell(100, 3, $outputlangs->transnoentities("CustomerCode") . " : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		// Show list of linked objects
		// $posy = pdf_writeLinkedObjects($this->pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);

		if ($showaddress) {
			// Sender properties
			$carac_emetteur = '';

			$carac_emetteur .= pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty);

			// Show sender
			$posy = 42;
			$posx = $this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT))
				$posx = $this->page_largeur - $this->marge_droite - 80;
			$hautcadre = 45;

			// Show sender frame
			$this->pdf->SetTextColor(0, 0, 0);
			$this->pdf->SetFont('', '', $default_font_size - 2);
			$this->pdf->SetXY($posx, $posy - 5);
			$this->pdf->MultiCell(66, 5, $outputlangs->transnoentities("BillFrom") . ":", 0, 'L');
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetFillColor(230, 230, 230);
			$this->pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$this->pdf->SetTextColor(0, 0, 60);

			// Show sender name
			$this->pdf->SetXY($posx + 2, $posy + 3);
			$this->pdf->SetFont('', 'B', $default_font_size);
			$this->pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy = $this->pdf->getY();

			// Show sender information
			$this->pdf->SetXY($posx + 2, $posy);
			$this->pdf->SetFont('', '', $default_font_size - 1);
			$this->pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');

			// If CUSTOMER contact defined, we use it
			$usecontact = false;
			$arrayidcontact = $object->getIdContact('external', 'CUSTOMER');
			if (count($arrayidcontact) > 0) {
				$usecontact = true;
				$result = $object->fetch_contact($arrayidcontact[0]);
				if ($result<0) {
					setEventMessage($object->contact->error,'errors');
				}
			}

			// Recipient name
			if (! empty($usecontact)) {
				// On peut utiliser le nom de la societe du contact
				if (! empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT))
					$socname = $object->contact->socname;
				else
					$socname = $object->thirdparty->nom;
				$carac_client_name = $outputlangs->convToOutputCharset($socname);
			} else {
				$carac_client_name = $outputlangs->convToOutputCharset($object->thirdparty->nom);
			}

			$carac_client = pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, ($usecontact ? $object->contact : ''), $usecontact, 'target');

			// Show recipient
			$widthrecbox = 100;
			if ($this->page_largeur < 210)
				$widthrecbox = 84; // To work with US executive format
			$posy = 42;
			$posx = $this->page_largeur - $this->marge_droite - $widthrecbox;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT))
				$posx = $this->marge_gauche;

			// Show recipient frame
			$this->pdf->SetTextColor(0, 0, 0);
			$this->pdf->SetFont('', '', $default_font_size - 2);
			$this->pdf->SetXY($posx + 2, $posy - 5);
			$this->pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillTo") . ":", 0, 'L');
			$this->pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$this->pdf->SetXY($posx + 2, $posy + 3);
			$this->pdf->SetFont('', 'B', $default_font_size);
			$this->pdf->MultiCell($widthrecbox, 4, $carac_client_name, 0, 'L');

			// Show recipient information
			$this->pdf->SetFont('', '', $default_font_size - 1);
			$this->pdf->SetXY($posx + 2, $this->pdf->GetY());
			$this->pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');

			$this->pdf->SetY(42+$hautcadre);
		}

		$this->pdf->SetTextColor(0, 0, 0);
	}

	/**
	 * Show footer of page.
	 * Need this->emetteur object
	 *
	 * @param Object $object show
	 * @param int $hidefreetext text
	 * @return int height of bottom margin including footer text
	 */
	function _pagefoot($object, $hidefreetext = 0) {
		$this->pdf->SetX($this->marge_gauche);
		return pdf_pagefoot($this->pdf, $this->outputlangs, '', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, 0, $hidefreetext);
	}
}
