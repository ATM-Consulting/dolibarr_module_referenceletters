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
 * \file referenceletters/core/modules/referenceletters/supplier_order/doc/pdf_rfltr_dol_original_commande_fournisseur.modules.php
 * \ingroup referenceletters
 * \brief Class file to create PDF for letter's model on contract
 */
dol_include_once('/referenceletters/core/modules/referenceletters/pdf/pdf_rfltr_default.modules.php');
dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');
dol_include_once('/referenceletters/lib/referenceletters.lib.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

/**
 * Class to generate PDF ModelePDFReferenceLetters
 */
class pdf_rfltr_dol_original_commande_fournisseur extends pdf_rfltr_default
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
		$this->name = "docEdit commande fournisseur";
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
		$this->marge_gauche = floatval(getDolGlobalString('MAIN_PDF_MARGIN_LEFT', 10));
		$this->marge_droite = floatval(getDolGlobalString('MAIN_PDF_MARGIN_RIGHT', 10));
		$this->marge_haute = floatval(getDolGlobalString('MAIN_PDF_MARGIN_TOP', 10));
		$this->marge_basse = floatval(getDolGlobalString('MAIN_PDF_MARGIN_BOTTOM', 10));

		$this->option_logo = 1; // Affiche logo

		// Get source company
		$this->emetteur = $mysoc;
		if (empty($this->emetteur->country_code))
			$this->emetteur->country_code = substr($langs->defaultlang, - 2); // By default, if was not defined
	}
}
