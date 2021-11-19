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
 * \file referenceletters/core/modules/referenceletters/supplier_order/doc/pdf_rfltr_dol_commande_fournisseur.modules.php
 * \ingroup referenceletters
 * \brief Class file to create PDF for letter's model on contract
 */
dol_include_once('referenceletters/core/modules/supplier_order/doc/pdf_rfltr_dol_original_commande_fournisseur.modules.php');

/**
 * Class to generate PDF ModelePDFReferenceLetters
 */
class pdf_rfltr_dol_commande_fournisseur extends pdf_rfltr_dol_original_commande_fournisseur
{
	// code has been moved to parent class because of a switch of modelpath between Dolibarr 11.0 & 12.0...
}
