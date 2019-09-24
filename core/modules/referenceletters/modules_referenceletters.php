<?php
/*
 * Copyright (C) 2014 Florian HENRY <florian.henry@open-concept.pro>
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
 */

/**
 * \file referenceletters/core/modules/referenceletters/modules_referenceletters.php
 * \ingroup referenceletters
 * \brief referenceletters for numbering referenceletters
 */
dol_include_once('/referenceletters/class/commondocgeneratorreferenceletters.class.php');

/**
 * \class ModelePDFReferenceLetters
 * \brief Absctart class for ReferenceLetters module
 */
abstract class ModelePDFReferenceLetters extends CommonDocGeneratorReferenceLetters
{
	public $error = '';


	/**
	 * @var TCPDFRefletters
	 */
	public $pdf;
	public $instance_letter;
	public $outputlangs;

	/**
	 * Return list of active generation modules
	 *
	 * @param DoliDB $db handler
	 * @param string $maxfilenamelength length of value to show
	 * @return array of templates
	 */
	static function liste_modeles($db, $maxfilenamelength = 0) {

		$liste = array();

		$liste[] = 'referenceletters';

		return $liste;
	}

	/**
	 * Function to build pdf onto disk
	 *
	 * @param Object $object Object to generate
	 * @param Object $instance_letter Object to generate
	 * @param Translate $outputlangs Lang output object
	 * @param string $doctype DocType
	 * @param string $doctypedir DocType Directory
	 * @return int 1=OK, 0=KO
	 */
	public function write_file($object, $instance_letter, $outputlangs, $doctype='', $doctypedir='') {
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
			$dir = $conf->referenceletters->dir_output . "/".$doctypedir."/" . $objectref;
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
						$backup_forceDisableConcatPdf = !empty($object->forceDisableConcatPdf);
						$object->forceDisableConcatPdf = 1;
						$result= $object->generateDocument($documentModel, $this->outputlangs, $hidedetails, $hidedesc, $hideref, null);
						$object->forceDisableConcatPdf = $backup_forceDisableConcatPdf;
						if ($result <= 0)
						{
							setEventMessages($object->error, $object->errors, 'errors');
						} else {
							$this->pdf->setPrintHeader(false);
							$objectrefpdf = dol_sanitizeFileName($object->ref);

							if (strpos($doctype,'&')) {
								$TDoctypes=explode('&',$doctype);
								$doctype=$TDoctypes[0];
								$doctypeSubDir=$TDoctypes[1].'/';
							} else {
								$doctypeSubDir='';
							}

							$dirpdf = $conf->{$doctype}->multidir_output[$object->entity] . "/" . $doctypeSubDir . $objectrefpdf;

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

						// Pas trouvé d'autre moyen de remplacer les sauts de lignes généras par l'objet odf dans merge_array()...
						$chapter_text = strtr($chapter_text, array('<text:line-break/>'=>'<br />'));

						//If chapter is "Same Page" with try to fin if we need to add page
						if (!empty($line_chapter['same_page']))	{
							$this->pdf->startTransaction();
							$curent_page=$this->pdf->getPage();
							$test = $this->pdf->writeHTMLCell(0, 0, $posX, $posY, $this->outputlangs->convToOutputCharset($chapter_text), 0, 1, false, true);
							$next_page=$this->pdf->getPage();

							if ($next_page>$curent_page) {
								$this->pdf->rollbackTransaction(true);
								if (method_exists($this->pdf, 'AliasNbPages'))
									$this->pdf->AliasNbPages();

								$this->pdf->AddPage(empty($use_landscape_format) ? 'P' : 'L',$this->format, true);
								if (! empty($tplidx)) {
									$this->pdf->useTemplate($tplidx);
								}

								$this->pdf->setPrintFooter(true);

								$posX = $this->pdf->getX();
								$posY = $this->pdf->getY();
								$test = $this->pdf->writeHTMLCell(0, 0, $posX, $posY, $this->outputlangs->convToOutputCharset($chapter_text), 0, 1, false, true);
							} else {
								$this->pdf->commitTransaction();
							}
						} else {
							$test = $this->pdf->writeHTMLCell(0, 0, $posX, $posY, $this->outputlangs->convToOutputCharset($chapter_text), 0, 1, false, true);
						}
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
                // we delete the non-DocEdit PDF (it is included in the DocEdit PDF and it creates a useless dir)
                dol_delete_file($filepdf);
                dol_delete_dir(dirname($filepdf));

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
	 * Permet de gérer les données de types listes ou tableaux (données pour lesquelles il est nécessaire de boucler)
	 *
	 * @param $TElementArray : Tableau qui va contenir les différents éléments agefodd sur lesquels on peut boucler (lignes, participants, horaires)
	 * @return string
	 */
	function merge_array(&$object, $chapter_text, $TElementArray = array()) {
		global $hookmanager, $conf;

		require_once DOL_DOCUMENT_ROOT . '/core/lib/doc.lib.php';
		dol_include_once('/referenceletters/class/odf_rfltr.class.php');
		if ($conf->subtotal->enabled) {
			dol_include_once('/subtotal/class/subtotal.class.php');
		}
		if (! class_exists('Product')) {
			dol_include_once('/product/class/product.class.php'); // Pour le segment lignes, parfois la classe produit n'est pas chargée (pour les contrats par exemple)...
		}

		$odfHandler = new OdfRfltr('',
			array(
				'PATH_TO_TMP' => $conf->propal->dir_temp,
				'ZIP_PROXY' => 'PclZipProxy', // PhpZipProxy or PclZipProxy. Got "bad compression method" error when using PhpZipProxy.
				'DELIMITER_LEFT' => '{',
				'DELIMITER_RIGHT' => '}'
		), $chapter_text);

		if (! empty($TElementArray)) {

			foreach ( $TElementArray as $element_array ) {

				if (strpos($chapter_text, $element_array . ' ') === false && strpos($chapter_text, $element_array . '&nbsp;') === false) {
					continue;
				}

				$listlines = $odfHandler->setSegment($element_array);

				if (strpos($chapter_text, '[!-- BEGIN') !== false) {

					if (! empty($object->{$element_array})) {

						foreach ( $object->{$element_array} as $line ) {

							if (method_exists($this, 'get_substitutionarray_lines_agefodd')) {
								$tmparray = $this->get_substitutionarray_lines_agefodd($line, $this->outputlangs, false);
							} else {
								$tmparray = $this->get_substitutionarray_lines($line, $this->outputlangs, false);
							}
							complete_substitutions_array($tmparray, $this->outputlangs, $object, $line, "completesubstitutionarray_lines");
							// Call the ODTSubstitutionLine hook
							$parameters = array(
									'odfHandler' => &$odfHandler,
									'file' => '',
									'object' => $object,
									'outputlangs' => $this->outputlangs,
									'substitutionarray' => &$tmparray,
									'line' => $line,
									'context' => $object->element . 'card'
							);
							$action = "builddoc";
							$reshook = $hookmanager->executeHooks('ODTSubstitutionLine', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
							if ($conf->subtotal->enabled) {
								if (TSubtotal::isModSubtotalLine($line)) {
									$tmparray['line_up_locale'] = '';
									$tmparray['line_price_ht_locale'] = '';
								}
								if (TSubtotal::isSubtotal($line)) {
									$tmparray['line_price_ht_locale'] = price($tmparray['line_price_ht'], 0);
								}
							}
							$oldline = $listlines->xml;
							foreach ( $tmparray as $key => $val ) {
								try {
									$listlines->setVars($key, $val, false, 'UTF-8');
								} catch ( OdfException $e ) {
								} catch ( SegmentException $e ) {
								}
							}

							if (! empty($conf->subtotal->enabled))
							{
								if (TSubtotal::isTitle($line)) {
									$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'{line_fulldesc}' => '<strong><u>{line_fulldesc}</u></strong>'
											,'{line_product_label}' => '<strong><u>{line_product_label}</u></strong>'
											,'{line_desc}' => '<strong><u>{line_desc}</u></strong>'
									));
								} else if (TSubtotal::isSubtotal($line)) {
									$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'<tr' => '<tr bgcolor="#E6E6E6" align="right" '
									));
									$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'{line_fulldesc}' => '<strong><i>{line_fulldesc}</i></strong>'
											,'{line_product_label}' => '<strong><i>{line_product_label}</i></strong>'
											,'{line_desc}' => '<strong><i>{line_desc}</i></strong>'
									));
									$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'{line_price_ht_locale}' => '<strong>{line_price_ht_locale}</strong>'
									));
									// var_dump($listlines->xml);exit;
								}
							}

							$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
								'<tr' => '<tr nobr="true" '
							));

							$res = $listlines->merge();

							$listlines->xml = $listlines->savxml = $oldline;
						}
					}
					$res = $odfHandler->mergeSegment($listlines);
					$chapter_text = $odfHandler->getContentXml();
				}
			}
		}

		// Annule la modification de la méthode preOdfToOdf() de la class Odf (htdocs/includes/odtphp/odf.php) si on passe dans une boucle
		$chapter_text = str_replace("<text:line-break/>", "<br />", $chapter_text);

		return $chapter_text;
	}
	/**
	 *
	 * @param stdClass $object
	 * @return number
	 */
	function _pageheadCustom($object) {

		global $conf;

		// Conversion des tags
		$this->instance_letter->header = $this->setSubstitutions($object, $this->instance_letter->header);

		$default_font_size = pdf_getPDFFontSize($this->outputlangs); // Must be after pdf_getInstance
		$this->pdf->SetFont('', '', $default_font_size);
		$this->pdf->writeHTMLCell(0, 0, $this->marge_droite, 0, $this->outputlangs->convToOutputCharset($this->instance_letter->header), 0, 1);
		$end_y = $this->pdf->GetY();
		$height = $end_y;

		$nb=0;
		if(!empty($conf->global->REF_LETTER_PAGE_HEAD_ADJUST)) {	
			$tmp_array = explode(',', $conf->global->REF_LETTER_PAGE_HEAD_ADJUST);
			if(is_array($tmp_array) && !empty($tmp_array)) {
				foreach($tmp_array as $v) {
					list($element, $tmp_nb) = explode(':',$v);
					if($element == $object->element) {
						$nb = $tmp_nb;
						break;
					}
				}
			}
		}

		return $height + (float)$nb;
	}

	/**
	 *
	 * @param stdClass $object
	 * @param string $typeprint
	 */
	function _pagefootCustom($object, $typeprint = '', $usePageNumber=1) {

		global $conf;

		// Conversion des tags
		$this->instance_letter->footer = $this->setSubstitutions($object, $this->instance_letter->footer);

		$this->pdf->SetX($this->marge_gauche);
		$default_font_size = pdf_getPDFFontSize($this->outputlangs); // Must be after pdf_getInstance
		$this->pdf->SetFont('', '', $default_font_size);
		$dims = $this->pdf->getPageDimensions();

		if (! empty($typeprint)) {
			$this->pdf->writeHTMLCell(0, 0, $dims['lm'], $this->pdf->GetY(), $this->outputlangs->convToOutputCharset($this->instance_letter->footer), 0, 1);

		} else {
			$this->pdf->writeHTMLCell(0, 0, $dims['lm'], $dims['hk'] - $this->pdf->mybottommargin, $this->outputlangs->convToOutputCharset($this->instance_letter->footer), 0, 1);
		}

		// Show page nb only on iso languages (so default Helvetica font)
		if (strtolower(pdf_getPDFFont($this->outputlangs)) == 'helvetica' && empty($conf->global->MAIN_USE_FPDF) && !empty($usePageNumber))
		{
			$currenty=$this->pdf->GetY();
			$currentx=$this->pdf->GetX();
			$this->pdf->SetXY($dims['wk']-10,$dims['hk']-5);
			$this->pdf->SetFont('','',7);
			$this->pdf->MultiCell(13, 2, $this->outputlangs->convToOutputCharset($this->pdf->PageNo().'/'.$this->pdf->getAliasNbPages()), 0, 'R', 0);
			$this->pdf->SetXY($currentx,$currenty);
		}
	}

	/**
	 *
	 * @param stdClass $object
	 * @param string $txt
	 * @param Translate $outputlangs Translate langs
	 * @return mixed
	 */
	function setSubstitutions(&$object, $txt='', $outputlangs=null) {
		global $user, $mysoc, $conf;

		if (empty($outputlangs)) $outputlangs = $this->outputlangs;

		// User substitution value
		$tmparray = $this->get_substitutionarray_user($user, $outputlangs);
		$substitution_array = array();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}

		$tmparray = $this->get_substitutionarray_mysoc($mysoc, $outputlangs);
		$substitution_array = array();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}

		if (get_class($object) === 'Societe') {
			$socobject = $object;
		} else {
			if (! empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) && ! empty($object->contact)) {
				if (method_exists($object->contact, 'fetch_thirdparty')) {
					$object->contact->fetch_thirdparty();
					$socobject = $object->contact->thirdparty;
				} else {
					$socobject = $object->contact;
				}
			} else {
				$socobject = $object->thirdparty;
			}
		}

		$tmparray = $this->get_substitutionarray_thirdparty($socobject, $outputlangs);
		$substitution_array = array();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
			    if ($key == 'company_address') $value = nl2br($value);
				$substitution_array['{cust_' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}

		$tmparray = $this->get_substitutionarray_other($outputlangs, $object);
        complete_substitutions_array($tmparray, $outputlangs, $object);
		$substitution_array = array();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}


		// Réservé aux pièces de vente
		if (get_class($object) !== 'Societe' && get_class($object) !== 'Contact' && get_class($object) !== 'ModelePDFReferenceLetters' && get_class($object) !== 'TCPDFRefletters' && get_class($object) !== 'Agsession' ) {
			$tmparray = $this->get_substitutionarray_object($object, $outputlangs);
			$substitution_array = array();
			if (is_array($tmparray) && count($tmparray) > 0) {
				foreach ( $tmparray as $key => $value ) {
					$substitution_array['{' . $key . '}'] = $value;
				}
				$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
			}
		}

		// Get instance letter substitution
		$tmparray = $this->get_substitutionarray_refletter($this->instance_letter, $outputlangs);
		$substitution_array = array();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}

		if (get_class($object) === 'Contact') {
			$tmparray = $this->get_substitutionarray_contact($object, $outputlangs);
			$substitution_array = array();
			if (is_array($tmparray) && count($tmparray) > 0) {
				foreach ( $tmparray as $key => $value ) {
					$substitution_array['{' . $key . '}'] = $value;
				}
				if (!empty($object->civility_id)) {
					$substitution_array['{contact_civility}'] = $outputlangs->transnoentitiesnoconv('Civility'.$object->civility_id);
				}
				$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
			}
		}

		if (get_class($object) === 'Agsession') {
			$tmparray = $this->get_substitutionsarray_agefodd($object, $outputlangs);
			$substitution_array = array();
			if (is_array($tmparray) && count($tmparray) > 0) {
				foreach ( $tmparray as $key => $value ) {
					$substitution_array['{' . $key . '}'] = $value;
				}
				$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
			}
		}

		$tmparray = $this->get_substitutionarray_each_var_object($object, $outputlangs);
	$tmparray['object_incoterms']='';
        if($conf->incoterm->enabled){
            $sql = "SELECT code FROM llx_c_incoterms WHERE rowid='".$object->fk_incoterms."'";
            $resql=$this->db->query($sql);
            if ($resql) {
                $num = $this->db->num_rows($resql);
                if ($num) {
                    $obj = $this->db->fetch_object($resql);
                    if ($obj->code) {
                        $tmparray['object_code_incoterms'] = $obj->code;
			$tmparray['object_incoterms'] = 'Incoterm : '.$obj->code.' - '.$tmparray['object_location_incoterms'];
                    }
                }
            }
        }
		$substitution_array = array();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{objvar_' . $key . '}'] = $value;
			}

			// Traduction des conditions de règlement
			if(! empty($substitution_array['{objvar_object_cond_reglement_code}']))
			{
				$cond_reg_lib = $substitution_array['{objvar_object_cond_reglement_code}'];
				$outputlangs->load('bills');
				$translationKey = 'PaymentConditionShort' . strtoupper($cond_reg_lib);

				$label = $outputlangs->trans($translationKey);

				if($label == $translationKey)
				{
					$sql = 'SELECT libelle_facture
                            FROM ' . MAIN_DB_PREFIX . 'c_payment_term
                            WHERE code = "' . $object->db->escape($cond_reg_lib) . '"
                            LIMIT 1';

                    $resql = $object->db->query($sql);

                    if($resql && $object->db->num_rows($resql) > 0)
                    {
                        $obj = $object->db->fetch_object($resql);
	                    $label = $obj->libelle_facture;
                    }
				}

				$substitution_array['{objvar_object_cond_reglement_doc}'] = $label;
			}

            // Traduction des modes de règlement
            if(! empty($substitution_array['{objvar_object_mode_reglement_code}']))
            {
                $mod_reg_lib = $substitution_array['{objvar_object_mode_reglement_code}'];
                $outputlangs->load('bills');
                $translationKey = 'PaymentType' . strtoupper($mod_reg_lib);

	            $label = $outputlangs->trans($translationKey);

                if($label == $translationKey)
                {
                    $sql = 'SELECT libelle
                            FROM ' . MAIN_DB_PREFIX . 'c_paiement
                            WHERE code = "' . $object->db->escape($mod_reg_lib) . '"
                            LIMIT 1';

                    $resql = $object->db->query($sql);

                    if($resql && $object->db->num_rows($resql) > 0)
                    {
                        $obj = $object->db->fetch_object($resql);
                        $label = $obj->libelle;
                    }
                }

                $substitution_array['{objvar_object_mode_reglement}'] = $label;
            }

			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}

		// Les clés indexées pour les contacts ({cust_conctactclient_<CODE>_<INDICE>_fullname}) peuvent ne pas exister
		// si le nombre de contacts liés n'est pas suffisant => on nettoie celles qui restent
		$TContactTypes = $object->liste_type_contact('external', 'position', 1);

		foreach($TContactTypes as $code => $dummy)
		{
			$prefixKey = 'cust_contactclient_' . $code;

			$txt = preg_replace('/\{' . preg_quote($prefixKey, '/') . '_[0-9]+_[^\}]+\}/', '', $txt);
		}

		return $txt;
	}

	/**
	 * Caculate the real (in px) height of a printed string
	 *
	 * @param string $type type of calc head or foot
	 * @return int
	 */
	public function getRealHeightLine($type = '') {
		global $conf;

		// Determine if jump pages is needed
		$this->pdf->startTransaction();

		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
		$this->pdf->AddPage();

		// store starting values
		$start_y = $this->pdf->GetY();
		//print '$start_y='.$start_y.'<br>';

		$start_page = $this->pdf->getPage();
		//print '$start_page='.$start_page.'<br>';

		$height = 0;
		$bottom_margin = 0;

		// print content
		if ($type == 'head') {
			$use_custom_header = $this->instance_letter->use_custom_header;

			if (empty($use_custom_header)) {
				$height = $this->_pagehead($this->pdf->ref_object, 1, $this->outputlangs);
			} else {
				$height = $this->_pageheadCustom($this->pdf->ref_object);
			}
		} elseif ($type == 'foot') {
			$use_custom_footer = $this->instance_letter->use_custom_footer;

			if (empty($use_custom_footer)) {
				// HEre standard _pagefoot method return bottom margin
				$height = $this->_pagefoot($this->pdf->ref_object, $this->outputlangs);
			} else {
				$margins = $this->pdf->getMargins();
				$bottom_margin = $margins['bottom'];
				$this->_pagefootCustom($this->pdf->ref_object, 'custom');
			}
		}

		if (empty($height)) {
			// get the new Y

			$end_y = $this->pdf->GetY();
			$end_page = $this->pdf->getPage() - 1;
			// calculate height
			//print '$end_y='.$end_y.'<br>';
			//print '$end_page='.$end_page.'<br>';

			if (($end_page == $start_page || $end_page == 0) && $end_y > $start_y) {
				$height = $end_y - $start_y;
				//print 'aa$height='.$height.'<br>';
			} else {
				for($page = $start_page; $page <= $end_page; $page ++) {
					$this->pdf->setPage($page);
					// print '$page='.$page.'<br>';
					if ($page == $start_page) {
						// first page
						$height = $this->page_hauteur - $start_y - $this->marge_basse;
						// print '$height=$this->page_hauteur - $start_y - $this->marge_basse='.$this->page_hauteur .'-'. $start_y .'-'. $this->marge_basse.'='.$height.'<br>';
					} elseif ($page == $end_page) {
						// last page
						// print '$height='.$height.'<br>';
						$height += $end_y - $this->marge_haute;
						// print '$height += $end_y - $this->marge_haute='.$end_y.'-'. $this->marge_haute.'='.$height.'<br>';
					} else {
						// print '$height='.$height.'<br>';
						$height += $this->page_hauteur - $this->marge_haute - $this->marge_basse;
						// print '$height += $this->page_hauteur - $this->marge_haute - $this->marge_basse='.$this->page_hauteur .'-'. $this->marge_haute .'-'. $this->marge_basse.'='.$height.'<br>';
					}
				}
			}
		}
		$this->pdf->setPrintHeader(true);
		$this->pdf->setPrintFooter(true);

		// restore previous object
		$this->pdf = $this->pdf->rollbackTransaction();

		if (! empty($bottom_margin)) {
			if (get_class($this->pdf->ref_object) === 'Agsession' && ($bottom_margin / 2)<$height) {
				$height -= ($bottom_margin / 2);
			}
			$this->pdf->mybottommargin = $height;
		}

		return $height;
	}
}

/**
 * Classe mere des modeles de numerotation des references de lead
 */
abstract class ModeleNumRefrReferenceLetters
{
	var $error = '';

	/**
	 * Return if a module can be used or not
	 *
	 * @return boolean true if module can be used
	 */
	function isEnabled() {
		return true;
	}

	/**
	 * Renvoi la description par defaut du modele de numerotation
	 *
	 * @return string Texte descripif
	 */
	function info() {
		global $langs;
		$langs->load("referenceletters@referenceletters");
		return $langs->trans("NoDescription");
	}

	/**
	 * Renvoi un exemple de numerotation
	 *
	 * @return string Example
	 */
	function getExample() {
		global $langs;
		$langs->load("referenceletters");
		return $langs->trans("NoExample");
	}

	/**
	 * Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 * de conflits qui empechera cette numerotation de fonctionner.
	 *
	 * @return boolean false si conflit, true si ok
	 */
	function canBeActivated() {
		return true;
	}

	/**
	 *
	 * Renvoi prochaine valeur attribuee
	 *
	 * @param int $fk_user
	 * @param string $element_type
	 * @param Societe $objsoc
	 * @param reference letter $referenceletters_element
	 * @return string
	 */
	function getNextValue($fk_user, $element_type, $objsoc, $referenceletters_element) {
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 * Renvoi version du module numerotation
	 *
	 * @return string Valeur
	 */
	function getVersion() {
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development')
			return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental')
			return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr')
			return DOL_VERSION;
		return $langs->trans("NotAvailable");
	}
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

		/** @var pdf_rfltr_propal|pdf_rfltr_order|pdf_rfltr_invoice|pdf_rfltr_contract|pdf_rfltr_thirdparty|pdf_rfltr_contact|pdf_rfltr_supplier_proposal|pdf_rfltr_order_supplier $obj */
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
