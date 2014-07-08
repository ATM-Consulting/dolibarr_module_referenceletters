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
 * \file referenceletters/core/modules/referenceletters/mod_referenceletters_universal.php
 * \ingroup referenceletters
 * \brief Fichier contenant la classe du modele de numerotation de reference de referenceletters Universal
 */
dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');

/**
 * Classe du modele de numerotation de reference de projet Universal
 */
class mod_referenceletters_universal extends ModeleNumRefrReferenceLetters
{

	var $version = 'dolibarr'; // 'development', 'experimental', 'dolibarr'
	var $error = '';

	var $nom = 'Universal';

	/**
	 * Renvoi la description du modele de numerotation
	 *
	 * @return string Texte descripif
	 */
	function info()
	{
		global $conf, $langs;
		
		$langs->load("referenceletters@referenceletters");
		$langs->load("admin");
		
		$form = new Form($this->db);
		
		$texte = $langs->trans('GenericNumRefModelDesc') . "<br>\n";
		$texte .= '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
		$texte .= '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstrefletter" value="REF_LETTER_UNIVERSAL_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';
		
		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Module103258Name"), $langs->transnoentities("Module103258Name"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Module103258Name"), $langs->transnoentities("Module103258Name"));
		$tooltip .= $langs->trans("GenericMaskCodes5");
		
		// Parametrage du prefix
		$texte .= '<tr><td>' . $langs->trans("Mask") . ':</td>';
		$texte .= '<td align="right">' . $form->textwithpicto('<input type="text" class="flat" size="24" name="maskrefletter" value="' . $conf->global->REF_LETTER_UNIVERSAL_MASK . '">', $tooltip, 1, 1) . '</td>';
		
		$texte .= '<td align="left" rowspan="2">&nbsp; <input type="submit" class="button" value="' . $langs->trans("Modify") . '" name="Button"></td>';
		
		$texte .= '</tr>';
		
		$texte .= '</table>';
		$texte .= '</form>';
		
		return $texte;
	}

	/**
	 * Renvoi un exemple de numerotation
	 *
	 * @return string Example
	 */
	function getExample()
	{
		global $conf, $langs, $mysoc, $user;
		
		$old_code_client = $mysoc->code_client;
		// $mysoc->code_client='CCCCCCCCCC';
		$numExample = $this->getNextValue($user->id, $mysoc, '');
		// $mysoc->code_client=$old_code_client;
		
		if (! $numExample) {
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
	}

	/**
	 * Return next value
	 *
	 * @param int $fk_user
	 *        	user creating
	 * @param string $element_type
	 *        	element_type
	 * @param Lead $lead        	
	 * @return string Valeur
	 */
	function getNextValue($fk_user, $element_type, $objsoc, $referenceletters='')
	{
		global $db, $conf;
		
		require_once (DOL_DOCUMENT_ROOT . "/core/lib/functions2.lib.php");
		
		// On defini critere recherche compteur
		$mask = $conf->global->REF_LETTER_UNIVERSAL_MASK;
		
		if (! $mask) {
			$this->error = 'NotConfigured';
			return 0;
		}
		
		$numFinal = get_next_value($db, $mask, 'referenceletters_elements', 'ref_int', '', $objsoc->code_client, dol_now());
		
		return $numFinal;
	}
}

?>