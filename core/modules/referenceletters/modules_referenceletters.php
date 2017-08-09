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
	var $error = '';

	/**
	 * Return list of active generation modules
	 *
	 * @param DoliDB $db handler
	 * @param string $maxfilenamelength length of value to show
	 * @return array of templates
	 */
	static function liste_modeles($db, $maxfilenamelength = 0) {
		global $conf;

		$type = 'referenceletters';
		$liste = array ();

		$liste[] = 'referenceletters';

		return $liste;
	}
	
	/**
	 * Permet de gérer les données de types listes ou tableaux (données pour lesquelles il est nécessaire de boucler)
	 * @param $TElementArray : Tableau qui va contenir les différents éléments agefodd sur lesquels on peut boucler (lignes, participants, horaires)
	 */
	function merge_array(&$object, $chapter_text, $TElementArray=array()) {
		
		global $hookmanager;
		
		require_once DOL_DOCUMENT_ROOT.'/core/lib/doc.lib.php';
		dol_include_once('/referenceletters/class/odf_rfltr.class.php');
		if(!class_exists('Product')) dol_include_once('/product/class/product.class.php'); // Pour le segment lignes, parfois la classe produit n'est pas chargée (pour les contrats par exemple)...
		
		$odfHandler = new OdfRfltr(
				$srctemplatepath,
				array(
						'PATH_TO_TMP'	  => $conf->propal->dir_temp,
						'ZIP_PROXY'		  => 'PclZipProxy',	// PhpZipProxy or PclZipProxy. Got "bad compression method" error when using PhpZipProxy.
						'DELIMITER_LEFT'  => '{',
						'DELIMITER_RIGHT' => '}'
				),
				$chapter_text
				);
		
		if(!empty($TElementArray)) {
			
			foreach($TElementArray as $element_array) {
				
				if(strpos($chapter_text, $element_array) === false) continue;
				
				$listlines = $odfHandler->setSegment($element_array);
				
				if(strpos($chapter_text, '[!-- BEGIN') !== false) {
					
					foreach ($object->{$element_array} as $line) {
						
						$tmparray=$this->get_substitutionarray_lines_agefodd($line, $outputlangs, false);
						complete_substitutions_array($tmparray, $outputlangs, $object, $line, "completesubstitutionarray_lines");
						// Call the ODTSubstitutionLine hook
						$parameters=array('odfHandler'=>&$odfHandler,'file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs,'substitutionarray'=>&$tmparray,'line'=>$line);
						$reshook=$hookmanager->executeHooks('ODTSubstitutionLine',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
						
						foreach($tmparray as $key => $val)
						{
							try {$listlines->setVars($key, $val, true, 'UTF-8');}
							catch(OdfException $e) {}
							catch(SegmentException $e) {}
							
						}
						
						$res = $listlines->merge();
					}
					
					$res=$odfHandler->mergeSegment($listlines);
					$chapter_text = $odfHandler->getContentXml();
					
				}
			}
		
		}
		
		return $chapter_text;
		
	}

	function _pageheadCustom(&$pdf, $object, $showadress, $outputlangs, $instance_letter) {
		
		// Conversion des tags
		$instance_letter->header = $this->setSubstitutions($object, $instance_letter, $instance_letter->header, $outputlangs);
		
		$posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - 100;
		$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
		$pdf->SetFont('', '', $default_font_size);
		$pdf->writeHTMLCell(0, 0, $posX + 3, $posY, $outputlangs->convToOutputCharset($instance_letter->header), 0, 1);
		
	}
	
	function _pagefootCustom(&$pdf, $object, $outputlangs, $hidefreetext = 0, $instance_letter) {
		
		// Conversion des tags
		$instance_letter->footer = $this->setSubstitutions($object, $instance_letter, $instance_letter->footer, $outputlangs);
		
		$pdf->SetX($this->marge_gauche);
		$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
		$pdf->SetFont('', '', $default_font_size);
		$dims=$pdf->getPageDimensions();
		$pdf->writeHTMLCell($pdf->page_largeur - $pdf->margin_left - $pdf->margin_right, 0, $dims['lm'], $dims['hk']-45, $instance_letter->footer);
		
		// TODO pagination marche pas
		/*if (empty($conf->global->MAIN_USE_FPDF)) $pdf->MultiCell(13, 2, $pdf->PageNo().'/'.$pdf->getAliasNbPages(), 0, 'R', 0);
		else $pdf->MultiCell(13, 2, $pdf->PageNo().'/{nb}', 0, 'R', 0);*/
	}
	
	function setSubstitutions(&$object, &$instance_letter, $txt, $outputlangs) {
		
		global $user, $mysoc;
		
		// User substitution value
		$tmparray = $this->get_substitutionarray_user($user, $outputlangs);
		$substitution_array = array ();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}
		
		$tmparray = $this->get_substitutionarray_mysoc($mysoc, $outputlangs);
		$substitution_array = array ();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}
		
		if(get_class($object) === 'Societe') $socobject = $object;
		if (! empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) && ! empty($object->contact)) $socobject = $object->contact;
		else $socobject = $object->thirdparty;
		
		$tmparray = $this->get_substitutionarray_thirdparty($socobject, $outputlangs);
		$substitution_array = array ();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}
		
		$tmparray = $this->get_substitutionarray_other($outputlangs);
		$substitution_array = array ();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}
		
		if(get_class($object) !== 'Societe' && get_class($object) !== 'Contact') { // Réservé aux pièces de vente
			$tmparray = $this->get_substitutionarray_object($object, $outputlangs);
			$substitution_array = array ();
			if (is_array($tmparray) && count($tmparray) > 0) {
				foreach ( $tmparray as $key => $value ) {
					$substitution_array['{' . $key . '}'] = $value;
				}
				$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
			}
		}
		
		// Get instance letter substitution
		$tmparray = $this->get_substitutionarray_refletter($instance_letter, $outputlangs);
		$substitution_array = array ();
		if (is_array($tmparray) && count($tmparray) > 0) {
			foreach ( $tmparray as $key => $value ) {
				$substitution_array['{' . $key . '}'] = $value;
			}
			$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
		}
		
		if(get_class($object) === 'Contact') {
			$tmparray = $this->get_substitutionarray_contact($object, $outputlangs);
			$substitution_array = array ();
			if (is_array($tmparray) && count($tmparray) > 0) {
				foreach ( $tmparray as $key => $value ) {
					$substitution_array['{' . $key . '}'] = $value;
				}
				$txt = str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
			}
		}
		
		if(get_class($object) === 'Agsession') {
			$tmparray = $this->get_substitutionsarray_agefodd($object, $outputlangs);
			$substitution_array = array ();
			if (is_array($tmparray) && count($tmparray) > 0) {
				foreach ( $tmparray as $key => $value ) {
					$substitution_array['{' . $key . '}'] = $value;
				}
				$txt= str_replace(array_keys($substitution_array), array_values($substitution_array), $txt);
			}
		}
		
		return $txt;
		
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
	 * Renvoi prochaine valeur attribuee
	 *
	 * @param int $fk_user user creating
	 * @param Societe $objsoc party
	 * @param Lead $lead
	 * @return string Valeur
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
 * @param unknown $pdf
 * @param unknown $outputlangs
 * @param unknown $id
 */
function importImageBackground(&$pdf, $outputlangs, $id) {
	global $conf;
	if (empty($conf->global->MAIN_DISABLE_FPDI)) {

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		// add doc from attached files of training
		$upload_dir = $conf->referenceletters->dir_output . "/referenceletters/" . $id;
		$filearray = dol_dir_list($upload_dir, "files", 0, '\.pdf$', '\.meta$', "name", SORT_ASC, 1);
		if (is_array($filearray) && count($filearray) > 0) {
			//Take first PDF file added
			$filedetail = reset($filearray);
			if (file_exists($filedetail['fullname'])) {
				$count = $pdf->setSourceFile($filedetail['fullname']);
				// import only first pages
				if ($count>0) {
					$tplIdx = $pdf->importPage(1);
					if ($tplIdx!==false) {
						$pdf->useTemplate($tplIdx);
					} else {
						setEventMessages(null, array($filedetail['fullname'].' cannot be added to current doc, probably Protected PDF'),'warnings');
					}
				}
			}
		}
	}
}