<?php
dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
dol_include_once('/referenceletters/lib/referenceletters.lib.php');
class pdf_rfltr_agefodd extends ModelePDFReferenceLetters
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
		$this->name = "referenceletter_agefodd_convention";
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
	 * @param Object $object generate
	 * @param Object $instance_letter generate
	 * @param Translate $this->outputlangs object
	 * @return int 1=OK, 0=KO
	 */
	function write_file($id_object, $id_model, $outputlangs, $file, $obj_agefodd_convention = '', $socid = '') {
		global $db, $user, $langs, $conf, $mysoc, $hookmanager;

		dol_include_once('/referenceletters/class/referenceletters_tools.class.php');

		// Chargement du modèle utilisé
		list ( $instance_letter, $object ) = RfltrTools::load_object_refletter($id_object, $id_model, $obj_agefodd_convention, $socid, $outputlangs->defaultlang);
		$this->instance_letter = $instance_letter;

		$use_landscape_format = ( int ) $instance_letter->use_landscape_format;

		if (! is_object($this->outputlangs)) {
			if (! is_object($outputlangs) && get_class($outputlangs)=='Translate') {
				$this->outputlangs = &$langs;
			} else {
				$this->outputlangs=$outputlangs;
			}
		}


		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF))
			$this->outputlangs->charset_output = 'ISO-8859-1';

		$this->outputlangs->load("main");
		$this->outputlangs->load("companies");
		$this->outputlangs->load("referenceletters@referenceletters");

		// Loop on each lines to detect if there is at least one image to show
		$realpatharray = array();

		if ($conf->agefodd->dir_output) {

			// $deja_regle = 0;
			// var_dump($file);exit;
			// $objectref = dol_sanitizeFileName($instance_letter->ref_int);

			$dir = $conf->agefodd->dir_output;

			$file = $dir . '/' . $file;

			if (! file_exists($dir)) {
				if (dol_mkdir($dir) < 0) {
					$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
					return 0;
				}
			}

			if (file_exists($dir)) {
				// Create pdf instance
				// $this->pdf = pdf_getInstance($this->format);
				$this->pdf = pdf_getInstance_refletters($object, $instance_letter, $this, $this->format);
				$default_font_size = pdf_getPDFFontSize($this->outputlangs); // Must be after pdf_getInstance
				                                                             // Set calculation of header and footer high line
				                                                             // footer high
				$this->height_foot = $this->getRealHeightLine('foot');

				$this->pdf->SetAutoPageBreak(1, $this->height_foot);

				$this->pdf->setPrintHeader(true);
				$this->pdf->setPrintFooter(true);

				$this->pdf->SetFont(pdf_getPDFFont($this->outputlangs));
				// Set path to the background PDF File
				if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
					$pagecount = $this->pdf->setSourceFile($conf->mycompany->dir_output . '/' . $conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $this->pdf->importPage(1);
				}

				$this->pdf->Open();
				$this->pdf->SetDrawColor(128, 128, 128);

				$this->pdf->SetTitle($this->outputlangs->convToOutputCharset($object->ref));
				$this->pdf->SetSubject($this->outputlangs->transnoentities("Module103258Name"));
				$this->pdf->SetCreator("Dolibarr " . DOL_VERSION);
				$this->pdf->SetAuthor($this->outputlangs->convToOutputCharset($user->getFullName($this->outputlangs)));
				$this->pdf->SetKeyWords($this->outputlangs->convToOutputCharset($instance_letter->ref_int) . " " . $this->outputlangs->transnoentities("Module103258Name"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION))
					$this->pdf->SetCompression(false);

				// Set calculation of header and footer high line
				// Header high
				$this->height_head = $this->getRealHeightLine('head');
				// Left, Top, Right
				$this->pdf->SetMargins($this->marge_gauche, $this->height_head, $this->marge_droite, 1);

				// New page
				$this->pdf->AddPage(empty($use_landscape_format) ? 'P' : 'L');
				if (! empty($tplidx))
					$this->pdf->useTemplate($tplidx);

				$this->pdf->SetFont('', '', $default_font_size - 1);
				$this->pdf->SetTextColor(0, 0, 0);

				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 42 : 10);
				$tab_height = 130;
				$tab_height_newpage = 150;

				$posY = $this->pdf->getY();
				$posX = $this->marge_gauche;

				foreach ( $instance_letter->content_letter as $key => $line_chapter ) {

					$this->pdf->SetXY($posX, $posY);

					$chapter_text = $line_chapter['content_text'];

					if ($chapter_text == '@breakpage@') {
						if (method_exists($this->pdf, 'AliasNbPages')) {
							$this->pdf->AliasNbPages();
						}
						$this->pdf->AddPage(empty($use_landscape_format) ? 'P' : 'L');
						if (! empty($tplidx)) {
							$this->pdf->useTemplate($tplidx);
						}

						$posX = $this->pdf->getX();
						$posY = $this->pdf->getY();

						continue;
					}

					if ($chapter_text == '@breakpagenohead@') {
						if (method_exists($this->pdf, 'AliasNbPages')) {
							$this->pdf->AliasNbPages();
						}

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

					// Remplacement des tags par les bonnes valeurs
					$chapter_text = $this->setSubstitutions($object, $chapter_text);

					// merge agefodd arrays
					$chapter_text = $this->merge_array($object, $chapter_text, array(
							'THorairesSession',
							'TFormationObjPeda',
							'TStagiairesSession',
							'TStagiairesSessionPresent',
							'TStagiairesSessionSoc',
							'TStagiairesSessionSocMore',
							'TStagiairesSessionConvention',
							'TFormateursSession',
							'TConventionFinancialLine',
							'TFormateursSessionCal'
					));

					// correction de problème de décalage de texte
					if (preg_match('/<strong>/', $chapter_text)) {
						$position = 0;

						while ( preg_match('/<strong>/', substr($chapter_text, $position)) ) {
							$position = strpos($chapter_text, '<strong>', $position);
							$startStrong = $position;
							$endStrong = strpos($chapter_text, '</strong>', $position);
							$strong = substr($chapter_text, $startStrong + 8, $endStrong - $position - 8);
							$style = 'font-weight:bold;';
							$i = 0;
							while ( @strpos($strong, '<span style=', $i) !== false ) {
								$len = strpos(substr($strong, strpos($strong, '<span style="', $i) + 13), '">', $i) - strpos($strong, '<span style="', $i);
								$style .= substr($strong, strpos($strong, '<span style="', $i) + 13, $len) . ';';
								$styleposition = strpos($strong, '<span style=', $i);
								if (empty($styleposition)) {
									$l = strripos($strong, '</span>', $i) - strpos($strong, '>', $i) - 1;
									$strong = substr($strong, strpos($strong, '>', $o) + 1, $l);
								} else {
									$l = strripos($strong, '</span>', $i) - strpos($strong, '>', $i) - 1;
									$strong = substr($strong, 0, strpos($strong, '<span')) . substr($strong, strpos($strong, '>') + 1, $l) . substr($strong, strripos($strong, '</span>') + 7);
								}
								$i += $len;
							}
							$chapter_text = substr($chapter_text, 0, $startStrong) . '<span style="' . $style . '">' . $strong . '</span>' . substr($chapter_text, $endStrong + 9);
							$position = $endStrong;
						}
					}

					$test_array = explode('@breakpage@', $chapter_text);
					foreach ($test_array as $chapter_text){
    					$test = $this->pdf->writeHTMLCell(0, 0, $posX, $posY, $this->outputlangs->convToOutputCharset($chapter_text), 0, 1, false, true);

    					if (is_array($line_chapter['options']) && count($line_chapter['options']) > 0) {
    						foreach ( $line_chapter['options'] as $keyoption => $option_detail ) {
    							if (! empty($option_detail['use_content_option'])) {
    								$posY = $this->pdf->GetY();
    								$this->pdf->SetXY($posX, $posY);

    								$this->pdf->writeHTMLCell(0, 0, $posX + 3, $posY, '<b>-</b> ' . $this->outputlangs->convToOutputCharset($option_detail['text_content_option']), 0, 1);
    							}
    						}
    					}

    					$posY = $this->page_hauteur -5; // force le saut de page en se rendant dans le pied de page

					}

					if(count($test_array) > 1) {
						//comment because seems to not be need. Actually remove the last attestaion page en loop on pages
						if(! empty($conf->global->REF_LETTER_DELETE_LAST_BREAKPAGE_FROM_LOOP)) $this->pdf->deletePage($this->pdf->getPage());
					}
				}

				if (!empty($conf->global->AGF_ADD_PROGRAM_TO_CONV) && ! empty($obj_agefodd_convention) && $obj_agefodd_convention->id > 0) {
				    if(class_exists('Agefodd')){
				        $agfTraining = new Agefodd($db);
				    } elseif (class_exists('Formation')) {
				        $agfTraining = new Formation($db);
				    }

					$agfTraining->fetch($object->fk_formation_catalogue);
					$agfTraining->generatePDAByLink();
					$infile = $conf->agefodd->dir_output . '/fiche_pedago_' . $object->fk_formation_catalogue . '.pdf';
					if (is_file($infile)) {
						$count = $this->pdf->setSourceFile($infile);
						if (count($count)>0) {
							// Add footer manully beacuse auto footer won't work cause of setPrintFooter=false set just after
							$this->pdf->SetAutoPageBreak(0);
							if(empty($instance_letter->use_custom_footer)) {
							 	$this->_pagefoot($object, $this->outputlangs);
							 } else {
							 	$this->_pagefootCustom($object);
							 }
						}


						// import all page
						for($p = 1; $p <= $count; $p ++) {
							$this->pdf->setPrintHeader(false);
							$this->pdf->setPrintFooter(false);
							$this->pdf->AddPage();

							$tplIdx = $this->pdf->importPage($p);
							$this->pdf->useTemplate($tplIdx, 0, 0, $this->page_largeur);
						}
					}
				}

				if (method_exists($this->pdf, 'AliasNbPages'))
					$this->pdf->AliasNbPages();

				$this->pdf->Close();

				$this->pdf->Output($file, 'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array(
						'pdfgeneration'
				));
				$parameters = array(
						'file' => $file,
						'object' => $object,
						'outputlangs' => $this->outputlangs,
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
	 * @param PDF &$this->pdf Object PDF
	 * @param Object $object to show
	 * @param int $showaddress 0=no, 1=yes
	 * @param Translate $this->outputlangs for output
	 * @return void
	 */
	function _pagehead($object, $showaddress, $instance_letter) {
		global $conf, $langs;

		$max_y=0;

		$this->outputlangs->load("main");
		$this->outputlangs->load("bills");
		$this->outputlangs->load("propal");
		$this->outputlangs->load("companies");

		$default_font_size = pdf_getPDFFontSize($this->outputlangs);

		pdf_pagehead($this->pdf, $this->outputlangs, $this->page_hauteur);

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
				$this->pdf->MultiCell(100, 3, $this->outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
				$this->pdf->MultiCell(100, 3, $this->outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		} else {
			$text = $this->emetteur->name;
			$this->pdf->MultiCell(100, 4, $this->outputlangs->convToOutputCharset($text), 0, 'L');
		}

		if (! empty($instance_letter->outputref)) {
			$this->pdf->SetFont('', 'B', $default_font_size + 3);
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$title = $this->outputlangs->convToOutputCharset($instance_letter->title_referenceletters);
			$this->pdf->MultiCell(100, 4, $title, '', 'R');
			$posy += 5;
		}

		$this->pdf->SetFont('', 'B', $default_font_size);

		$this->pdf->SetXY($posx, $posy);
		$this->pdf->SetTextColor(0, 0, 60);
		$this->pdf->MultiCell(100, 4, $this->outputlangs->transnoentities("Ref") . " : " . $this->outputlangs->convToOutputCharset($object->ref), '', 'R');

		if (! empty($instance_letter->outputref)) {
			$posy += 5;
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$this->pdf->MultiCell(100, 4, $this->outputlangs->transnoentities("RefLtrRef") . " : " . $this->outputlangs->convToOutputCharset($instance_letter->ref_int), '', 'R');
		}

		$posy += 1;
		$this->pdf->SetFont('', '', $default_font_size - 1);

		if ($object->ref_client) {
			$posy += 5;
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$this->pdf->MultiCell(100, 3, $this->outputlangs->transnoentities("RefCustomer") . " : " . $this->outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}

		if ($object->thirdparty->code_client) {
			$posy += 4;
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetTextColor(0, 0, 60);
			$this->pdf->MultiCell(100, 3, $this->outputlangs->transnoentities("CustomerCode") . " : " . $this->outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		$posy += 2;

		// Show list of linked objects
		// $posy = pdf_writeLinkedObjects($this->pdf, $object, $this->outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);

		if ($showaddress) {
			// Sender properties
			$carac_emetteur = '';
			// Add internal contact of proposal if defined
			/*$arrayidcontact=$object->getIdContact('internal','SALESREPFOLL');
			 if (count($arrayidcontact) > 0)
			 {
			 $object->fetch_user($arrayidcontact[0]);
			 $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$this->outputlangs->transnoentities("Name").": ".$this->outputlangs->convToOutputCharset($object->user->getFullName($this->outputlangs))."\n";
			 }*/

			$carac_emetteur .= pdf_build_address($this->outputlangs, $this->emetteur, $object->thirdparty);

			// Show sender
			$posy = 42;
			$posx = $this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT))
				$posx = $this->page_largeur - $this->marge_droite - 80;
			$hautcadre = 45;
			$max_y=$posy+$hautcadre;

			// Show sender frame
			$this->pdf->SetTextColor(0, 0, 0);
			$this->pdf->SetFont('', '', $default_font_size - 2);
			$this->pdf->SetXY($posx, $posy - 5);
			$this->pdf->MultiCell(66, 5, $this->outputlangs->transnoentities("BillFrom") . ":", 0, 'L');
			$this->pdf->SetXY($posx, $posy);
			$this->pdf->SetFillColor(230, 230, 230);
			$this->pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$this->pdf->SetTextColor(0, 0, 60);

			// Show sender name
			$this->pdf->SetXY($posx + 2, $posy + 3);
			$this->pdf->SetFont('', 'B', $default_font_size);
			$this->pdf->MultiCell(80, 4, $this->outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy = $this->pdf->getY();

			// Show sender information
			$this->pdf->SetXY($posx + 2, $posy);
			$this->pdf->SetFont('', '', $default_font_size - 1);
			$this->pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');

			// If CUSTOMER contact defined, we use it
			/*$usecontact = false;
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
			 $socname = $object->thirdparty->nom;
			 $carac_client_name = $this->outputlangs->convToOutputCharset($socname);
			 } else {
			 $carac_client_name = $this->outputlangs->convToOutputCharset($object->thirdparty->nom);
			 }*/

			$carac_client_name = $this->outputlangs->convToOutputCharset($object->thirdparty->nom);
			$carac_client = pdf_build_address($this->outputlangs, $this->emetteur, $object->thirdparty, ($usecontact ? $object->contact : ''), $usecontact, 'target');

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
			$this->pdf->MultiCell($widthrecbox, 5, $this->outputlangs->transnoentities("BillTo") . ":", 0, 'L');
			$this->pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$this->pdf->SetXY($posx + 2, $posy + 3);
			$this->pdf->SetFont('', 'B', $default_font_size);
			$this->pdf->MultiCell($widthrecbox, 4, $carac_client_name, 0, 'L');

			// Show recipient information
			$this->pdf->SetFont('', '', $default_font_size - 1);
			$this->pdf->SetXY($posx + 2, $posy + 4 + (dol_nboflines_bis($carac_client_name, 50) * 4));
			$this->pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
		}

		$this->pdf->SetTextColor(0, 0, 0);
		return $max_y + 5;
	}

	/**
	 * Show footer of page.
	 * Need this->emetteur object
	 *
	 * @param PDF &$this->pdf PDF
	 * @param Object $object show
	 * @param Translate $this->outputlangs for output
	 * @param int $hidefreetext text
	 * @return int height of bottom margin including footer text
	 */
	function _pagefoot($object, $hidefreetext = 0) {
		$this->pdf->SetX($this->marge_gauche);
		return pdf_pagefoot($this->pdf, $this->outputlangs, '', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, 0, $hidefreetext);
	}
}
