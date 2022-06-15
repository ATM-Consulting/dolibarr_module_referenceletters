<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
 * Copyright (C) 2012      Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2014      Marcos García        <marcosgdf@gmail.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 *  \file			htdocs/core/modules/referenceletters/modules_referenceletterselement.php
 *  \ingroup		referenceletterselement
 *  \brief			File that contains parent class for referenceletterselement document models and parent class for referenceletterselement numbering models
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php'; // required for use by classes that inherit
dol_include_once('/referenceletters/class/commondocgeneratorreferenceletters.class.php');


/**
 *	Parent class for documents models
 */
abstract class ModelePDFReferenceLettersElement extends CommonDocGeneratorReferenceLetters
{

	/**
	 * Return list of active generation modules
	 *
	 * @param DoliDB $db handler
	 * @param int $maxfilenamelength length of value to show
	 * @return array of templates
	 */
	static function liste_modeles($db, $maxfilenamelength = 0) {

		$liste = array();

		$liste[] = 'referenceletterselement';

		return $liste;
	}

	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_pagefoot($pdf,$outputlangs,strtoupper($object->element).'_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,$showdetails,$hidefreetext);
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

		if(!empty($_REQUEST['lang_id']) && $this->outputlangs->defaultlang !== $_REQUEST['lang_id']) {
			$this->outputlangs = new Translate("", $conf);
			$this->outputlangs->setDefaultLang($_REQUEST['lang_id']);
			$this->outputlangs->load('main');
			$this->outputlangs->load('agefodd@agefodd');
		}

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

			$objectref = dol_sanitizeFileName($instance_letter->ref);
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
				$this->pdf->SetKeyWords($this->outputlangs->convToOutputCharset($instance_letter->ref) . " " . $this->outputlangs->transnoentities("Module103258Name"));
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
					$tabToMerge = array('lines');
					if (get_class($object) === 'Contrat') $tabToMerge[] = 'lines_active';

					$chapter_text = $this->merge_array($object, $chapter_text, $tabToMerge);

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
				if(!empty($filepdf)) {
					if (is_file($filepdf)) dol_delete_file($filepdf);
					if (is_dir(dirname($filepdf))) dol_delete_dir(dirname($filepdf));
				}

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

				if (strpos($chapter_text, '[!-- BEGIN') !== false) {

					$listlines = $odfHandler->setSegment($element_array);

					if (! empty($object->{$element_array})) {

						foreach ( $object->{$element_array} as $line ) {

							if (method_exists($this, 'get_substitutionarray_lines_agefodd') && strpos(get_class($this), 'agefodd') !== false) {
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
									$tmparray['line_price_ht_locale'] = $tmparray['line_price_ht'];
								}
							}
							if (!empty($listlines)) {
								$oldline = $listlines->xml;
								foreach ($tmparray as $key => $val) {
									try {
										$listlines->setVars($key, $val, false, 'UTF-8');
									} catch (OdfException $e) {
									} catch (SegmentException $e) {
									}
								}
							}

							if (! empty($conf->subtotal->enabled))
							{
								if (TSubtotal::isTitle($line)) {
									if (!empty($conf->global->SUBTOTAL_TITLE_STYLE))
									{
										$style_start = $style_end = '';
										if (strpos($conf->global->SUBTOTAL_TITLE_STYLE, 'B') !== false)
										{
											$style_start.= '<strong>';
											$style_end = '</strong>'.$style_end;
										}
										if (strpos($conf->global->SUBTOTAL_TITLE_STYLE, 'U') !== false)
										{
											$style_start.= '<u>';
											$style_end = '</u>'.$style_end;
										}
										if (strpos($conf->global->SUBTOTAL_TITLE_STYLE, 'I') !== false)
										{
											$style_start.= '<i>';
											$style_end = '</i>'.$style_end;
										}
									}
									else
									{
										$style_start = '<strong><u>';
										$style_end = '</u></strong>';
									}
									if (!empty($listlines)) {
										$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'{line_fulldesc}'        => $style_start . '{line_fulldesc}' . $style_end
										, '{line_product_label}' => $style_start . '{line_product_label}' . $style_end
										, '{line_desc}'          => '{line_desc}'
										));
									}
								} else if (TSubtotal::isSubtotal($line)) {
									if (!empty($conf->global->SUBTOTAL_SUBTOTAL_STYLE))
									{
										$style_start = $style_end = '';
										if (strpos($conf->global->SUBTOTAL_SUBTOTAL_STYLE, 'B') !== false)
										{
											$style_start.= '<strong>';
											$style_end = '</strong>'.$style_end;
										}
										if (strpos($conf->global->SUBTOTAL_SUBTOTAL_STYLE, 'U') !== false)
										{
											$style_start.= '<u>';
											$style_end = '</u>'.$style_end;
										}
										if (strpos($conf->global->SUBTOTAL_SUBTOTAL_STYLE, 'I') !== false)
										{
											$style_start.= '<i>';
											$style_end = '</i>'.$style_end;
										}
									}
									else
									{
										$style_start = '<strong><i>';
										$style_end = '</i></strong>';
									}
									if (!empty($listlines)) {
										$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'<tr' => '<tr bgcolor="#E6E6E6" align="right" '
										));
										$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'{line_fulldesc}'        => $style_start . '{line_fulldesc}' . $style_end
										, '{line_product_label}' => $style_start . '{line_product_label}' . $style_end
										, '{line_desc}'          => '{line_desc}'
										));
										$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
											'{line_price_ht_locale}' => $style_start . '{line_price_ht_locale}' . $style_end
										));
									}
									// var_dump($listlines->xml);exit;
								}
							}
							if (!empty($listlines)) {
								$listlines->xml = $listlines->savxml = strtr($listlines->xml, array(
									'<tr' => '<tr nobr="true" '
								));

								$res = $listlines->merge();

								$listlines->xml = $listlines->savxml = $oldline;
							}
						}
					}
					if (!empty($listlines)) {
						$res = $odfHandler->mergeSegment($listlines);
					}
					$chapter_text = $odfHandler->getContentXml();
				}
			}
		}

		// Annule la modification de la méthode preOdfToOdf() de la class Odf (htdocs/includes/odtphp/odf.php) si on passe dans une boucle
//		$chapter_text = str_replace("<text:line-break/>", "<br />", $chapter_text);

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
		if (get_class($object) !== 'Societe' && get_class($object) !== 'Contact' && get_class($object) !== 'ModelePDFReferenceLettersElement' && get_class($object) !== 'TCPDFRefletters' && get_class($object) !== 'Agsession' ) {
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

		if (get_class($object)=='Expedition') {
			/** @var $object Expedition */
			// Tracking number for shipping
			if (isset($object->tracking_number) && !empty($object->tracking_number) && is_callable(array($object, 'getUrlTrackingStatus'))) {
				$object->getUrlTrackingStatus($object->tracking_number);
				if (!empty($object->tracking_url)) {
					if ($object->shipping_method_id > 0) {
						// Get code using getLabelFromKey
						$code = $outputlangs->getLabelFromKey($this->db, $object->shipping_method_id, 'c_shipment_mode', 'rowid', 'code');
						$label = '';
						if ($object->tracking_url != $object->tracking_number)
							$label .= $outputlangs->transnoentitiesnoconv("LinkToTrackYourPackage") . "<br>";
						$label .= $outputlangs->transnoentitiesnoconv("SendingMethod") . ": " . $outputlangs->transnoentitiesnoconv("SendingMethod" . strtoupper($code));
						//var_dump($object->tracking_url != $object->tracking_number);exit;
						if ($object->tracking_url != $object->tracking_number) {
							$label .= " : ";
							$label .= $object->tracking_url;
						}
						$substitution_array['{object_tracking_number}'] = $label;
					}
				}
			} else {
				$substitution_array['{object_tracking_number}']='';
			}
			if (is_callable(array($object, 'getTotalWeightVolume'))){

				// Set trueVolume and volume_units not currently stored into database
				if ($object->trueWidth && $object->trueHeight && $object->trueDepth)
				{
					$object->trueVolume = price(($object->trueWidth * $object->trueHeight * $object->trueDepth), 0, $outputlangs, 0, 0);
					$object->volume_units = $object->size_units * 3;
				}

				$tmparraytotal = $object->getTotalWeightVolume();
				$substitution_array['{object_total_weight}'] = $tmparraytotal['weight'];
				$substitution_array['{object_total_volume}'] = $tmparraytotal['volume'];
				if (function_exists('showDimensionInBestUnit')) {
					if ($substitution_array['{object_total_weight}'] != '') {
						$substitution_array['{object_total_weight}'] = showDimensionInBestUnit($substitution_array['{object_total_weight}'], 0, "weight", $outputlangs);
					}
					if ($substitution_array['{object_total_volume}'] != '') {
						$substitution_array['{object_total_volume}'] = showDimensionInBestUnit($substitution_array['{object_total_volume}'], 0, "volume", $outputlangs);
					}
					if ($object->trueWeight) $substitution_array['object_total_weight}'] = showDimensionInBestUnit($object->trueWeight, $object->weight_units, "weight", $outputlangs);
					if ($object->trueVolume) $substitution_array['object_total_volume}'] = showDimensionInBestUnit($object->trueVolume, $object->volume_units, "volume", $outputlangs);
				}
				$substitution_array['{object_total_qty_ordered}'] = $tmparraytotal['ordered'];
				$substitution_array['{object_total_qty_toship}'] = $tmparraytotal['toship'];
			}
			else {
				$substitution_array['{object_total_weight}']='';
				$substitution_array['{object_total_volume}']='';
				$substitution_array['{object_total_qty_ordered}']='';
				$substitution_array['{object_total_qty_toship}']='';
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}


		$tmparray = $this->get_substitutionarray_each_var_object($object, $outputlangs);
		$tmparray['object_incoterms']='';
		if($conf->incoterm->enabled && isset($object->fk_incoterms) && !empty($object->fk_incoterms)){
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
				$height = $this->_pagefoot($this->pdf,$this->pdf->ref_object, $this->outputlangs);
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
 *  Parent class to manage numbering of ReferenceLettersElement
 */
abstract class ModeleNumRefReferenceLettersElements
{
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 *	Return if a module can be used or not
	 *
	 *	@return		boolean     true if module can be used
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 *	Returns the default description of the numbering template
	 *
	 *	@return     string      Texte descripif
	 */
	public function info()
	{
		global $langs;
		$langs->load("referenceletters@referenceletters");
		return $langs->trans("NoDescription");
	}

	/**
	 *	Returns an example of numbering
	 *
	 *	@return     string      Example
	 */
	public function getExample()
	{
		global $langs;
		$langs->load("referenceletters@referenceletters");
		return $langs->trans("NoExample");
	}

	/**
	 *  Checks if the numbers already in the database do not
	 *  cause conflicts that would prevent this numbering working.
	 *
	 *	@param	Object		$object		Object we need next value for
	 *	@return boolean     			false if conflict, true if ok
	 */
	public function canBeActivated($object)
	{
		return true;
	}

	/**
	 *	Returns next assigned value
	 *
	 *	@param	Object		$object		Object we need next value for
	 *	@return	string      Valeur
	 */
	public function getNextValue($object)
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 *	Returns version of numbering module
	 *
	 *	@return     string      Valeur
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		if ($this->version) return $this->version;
		return $langs->trans("NotAvailable");
	}
}
