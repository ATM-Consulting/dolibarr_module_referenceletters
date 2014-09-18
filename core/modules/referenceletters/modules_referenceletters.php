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

require_once '../class/commondocgeneratorreferenceletters.class.php';

/**
 * \class ModelePDFReferenceLetters
 * \brief Absctart class for ReferenceLetters module
 */
abstract class ModelePDFReferenceLetters extends CommonDocGeneratorReferenceLetters {
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

		$liste [] = 'referenceletters';

		return $liste;
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
	function isEnabled()
	{
		return true;
	}

	/**
	 * Renvoi la description par defaut du modele de numerotation
	 *
	 * @return string Texte descripif
	 */
	function info()
	{
		global $langs;
		$langs->load("referenceletters@referenceletters");
		return $langs->trans("NoDescription");
	}

	/**
	 * Renvoi un exemple de numerotation
	 *
	 * @return string Example
	 */
	function getExample()
	{
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
	function canBeActivated()
	{
		return true;
	}

	/**
	 * Renvoi prochaine valeur attribuee
	 *
	 * @param int $fk_user
	 *        	user creating
	 * @param Societe $objsoc
	 *        	party
	 * @param Lead $lead        	
	 * @return string Valeur
	 */
	function getNextValue($fk_user, $objsoc, $lead)
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 * Renvoi version du module numerotation
	 *
	 * @return string Valeur
	 */
	function getVersion()
	{
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
 *  Create a document onto disk according to template module.
 *
 * 	@param	    DoliDB		$db  			 Database handler
 * 	@param	    object		$object			 Object proposal
 * 	@param	    object		$instance_letter Instance letter
 * 	@param		Translate	$outputlangs	 Object langs to use for output
 *  @param      string		$element_type    element type
 * 	@return     int         				0 if KO, 1 if OK
 */
function referenceletters_pdf_create($db, $object, $instance_letter, $outputlangs, $element_type)
{
	global $conf,$user,$langs;

	$error=0;
	$filefound=0;
	// Search template files
	$file=dol_buildpath('/referenceletters/core/modules/referenceletters/pdf/pdf_rfltr_'.$element_type.'.modules.php');
	if (file_exists($file)) {
		$filefound=1;
	}

	$classname='pdf_rfltr_'.$element_type; 

	// Charge le modele
	if ($filefound)
	{
		require_once $file;

		$obj = new $classname($db);

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		if ($obj->write_file($object, $instance_letter, $outputlangs) > 0)
		{
			return 1;
		}
		else
		{
			setEventMessage('referenceletters_pdf_create Error: '.$obj->error, 'errors');
			return -1;
		}

	}
	else
	{
		setEventMessage($langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$file), 'errors');
		return -1;
	}
}