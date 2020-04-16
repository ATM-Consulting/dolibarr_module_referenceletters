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
dol_include_once('/referenceletters/core/modules/referenceletters/pdf/pdf_rfltr_invoice.modules.php');
require_once (DOL_DOCUMENT_ROOT . "/core/class/commondocgenerator.class.php");
dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');
dol_include_once('/referenceletters/lib/referenceletters.lib.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

/**
 * Class to generate PDF ModelePDFReferenceLetters
 */
class pdf_rfltr_default extends CommonDocGenerator
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
	 * @param DoliDB $db Database handler
	 */
	function __construct($db) {
		global $conf, $langs, $mysoc, $object;

		$langs->load("main");
		$langs->load("bills");
		$langs->load("referenceletters@referenceletters");

		$this->db = $db;
		$this->name = "";
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
	function write_file(&$object, $instance_letter, $outputlangs) {
		global $user, $langs, $conf, $db;

		$this->outputlangs=$outputlangs;
		$this->instance_letter = $instance_letter;

        $object->fetch_optionals();
		$id_model = $object->array_options['options_rfltr_model_id'];

		dol_include_once('/referenceletters/class/referenceletters_tools.class.php');
		$instances = RfltrTools::load_object_refletter($object->id, $id_model, $object, '', GETPOST('lang_id'));
		/** @var ReferenceLettersElements $instance_letter */
		$instance_letter = $instances[0];

		if(empty($instance_letter->ref_int)) $instance_letter->ref_int = $instance_letter->getNextNumRef($object->thirdparty, $user->id, $instance_letter->element_type);
		//$instance_letter->create($user);
		// Création du PDF
		$result = referenceletters_pdf_create($db, $object, $instance_letter, $outputlangs, $instance_letter->element_type);

		if($result > 0) {
		    // Renommage du fichier pour le mettre dans le bon répertoire pour qu'il apparaîsse dans la liste des fichiers joints sur la fiche de chaque élément
		    $objectref = dol_sanitizeFileName($instance_letter->ref_int);
		    $dir = $conf->referenceletters->dir_output . '/' .$instance_letter->element_type . '/' . $objectref;
		    $file = $dir . '/' . $objectref . ".pdf";

		    $objectref = dol_sanitizeFileName($object->ref);
		    $classname = get_class($object);
		    if($classname === 'CommandeFournisseur') $classname = 'supplier_order';
		    $dir_dest = $conf->{strtolower($classname)}->dir_output;
			if($classname === 'Expedition') $dir_dest .= '/sending';
		    if (empty($dir_dest)) {
		        dol_include_once('/referenceletters/class/referenceletters.class.php');
		        $refstatic = new ReferenceLetters($this->db);
		        if (array_key_exists('dir_output', $refstatic->element_type_list[$instance_letter->element_type])) {
		            $dir_dest = $refstatic->element_type_list[$instance_letter->element_type]['dir_output'];
		        }
		    }
		    if (empty($dir_dest)) {
		        setEventMessage($langs->trans('RefLtrCannotCopyFile'), 'errors');
		    } else {
                if (!empty($object->context['propale_history']['original_ref'])) {
                    $objectref = $object->context['propale_history']['original_ref'];
                }
                $dir_dest .= '/' . dol_sanitizeFileName($objectref);
                if (! file_exists($dir_dest))
		        {
		            dol_mkdir($dir_dest);
		        }
		        $file_dest = $dir_dest . '/' . $objectref . '.pdf';
		        $test=$conf->{strtolower(get_class($object))}->dir_output;

		        dol_copy($file, $file_dest);
		    }

// 		    // Header sur la même page pour annuler le traitement standard de génération de PDF
// 		    $field_id = 'id';
// 		    if(get_class($object) === 'Facture') $field_id = 'facid';
// 		    header('location: '.$_SERVER['PHP_SELF'].'?id='.GETPOST($field_id)); exit;
		    return 1;
		}
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
		global $conf, $langs;

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

		$posy += 2;

		// Show list of linked objects
		// $posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);

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
