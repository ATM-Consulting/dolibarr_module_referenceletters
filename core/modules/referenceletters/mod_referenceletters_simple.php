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
 * \file lead/core/modules/lead/mod_lead_simple.php
 * \ingroup lead
 * \brief File with class to manage the numbering module Simple for lead references
 */
dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');

/**
 * Class to manage the numbering module Simple for lead references
 */
class mod_referenceletters_simple extends ModeleNumRefrReferenceLetters
{

	var $version = 'dolibarr'; // 'development', 'experimental', 'dolibarr'
	var $prefix = 'LTR-';

	var $error = '';

	var $nom = "Simple";

	/**
	 * Return description of numbering module
	 *
	 * @return string Text with description
	 */
	function info()
	{
		global $langs;
		return $langs->trans("RefLtrSimpleNumRefModelDesc", $this->prefix);
	}

	/**
	 * Return an example of numbering module values
	 *
	 * @return string Example
	 */
	function getExample()
	{
		return $this->prefix . "1402-0001";
	}

	/**
	 * Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 * de conflits qui empechera cette numerotation de fonctionner.
	 *
	 * @return boolean false si conflit, true si ok
	 */
	function canBeActivated()
	{
		global $conf, $langs;
		
		$coyymm = '';
		$max = '';
		
		$posindice = 8;
		$sql = "SELECT MAX(SUBSTRING(ref_int FROM " . $posindice . ")) as max";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements";
		$sql .= " WHERE ref_int LIKE '" . $this->prefix . "____-%'";
		// $sql.= " AND entity = ".$conf->entity;
		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$coyymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if (! $coyymm || preg_match('/' . $this->prefix . '[0-9][0-9][0-9][0-9]/i', $coyymm)) {
			return true;
		} else {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}
	}

	/**
	 * Return next value
	 *
	 * @param int $fk_user
	 *        	user creating
	 * @param string $element_type
	 *        	element_type
	 * @param Reference letters $referenceletters
	 * @return string Valeur
	 */
	function getNextValue($fk_user, $element_type, $referenceletters)
	{
		global $db, $conf;
		
		if (!empty($element_type)) {
			$this->prefix .= '-'.mb_strtoupper(substr($element_type,0,3));
			$posindice = 10 + count(mb_strtoupper(substr($element_type,0,3)))+1;
		} else {
			$posindice = 10;
		}
		
		// D'abord on recupere la valeur max
		
		$sql = "SELECT MAX(SUBSTRING(ref_int FROM " . $posindice . ")) as max";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements";
		$sql .= " WHERE ref_int like '" . $this->prefix . "____-%'";
		$sql .= " AND element_type='".$element_type."'";
		
		$resql = $db->query($sql);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			if ($obj)
				$max = intval($obj->max);
			else
				$max = 0;
		} else {
			dol_syslog("mod_referenceletters_simple::getNextValue sql=" . $sql);
			return - 1;
		}
		
		$date = empty($referenceletters->datec) ? dol_now() : $referenceletters->datec;
		
		// $yymm = strftime("%y%m",time());
		$yymm = strftime("%y%m", $date);
		$num = sprintf("%04s", $max + 1);
		
		dol_syslog("mod_referenceletters_simple::getNextValue return " . $this->prefix . $yymm . "-" . $num);
		return $this->prefix . $yymm . "-" . $num;
	}
}

?>