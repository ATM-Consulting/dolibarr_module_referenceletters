<?php
/* References letters
 * Copyright (C) 2014  HENRY Florian  florian.henry@open-concept.pro
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file		core/boxes/mybox.php
 * \ingroup	referenceletters
 * \brief		This file is a sample box definition file
 * Put some comments here
 */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 */
class box_referenceletter_models_archive extends ModeleBoxes {
	var $boxcode = "referenceletter_models";
	var $boximg = "referenceletters@referenceletters";
	var $boxlabel;
	var $depends = array (
			"referenceletters"
	);
	var $db;
	var $param;
	var $info_box_head = array ();
	var $info_box_contents = array ();

	/**
	 * Constructor
	 */
	function __construct() {
		global $langs;
		$langs->load("boxes");

		$this->boxlabel = $langs->transnoentitiesnoconv("Module103258Name").'-'.$langs->transnoentitiesnoconv("RefLtrLettersUnactiveListbox",15);
	}

	/**
	 * Load data into info_box_contents array to show array later.
	 *
	 * @param int $max of records to load
	 * @return void
	 */
	function loadBox($max = 15) {
		global $conf, $user, $langs, $db;

		$this->max = $max;

		dol_include_once("/referenceletters/class/referenceletters.class.php");

		$text = $langs->trans("RefLtrLettersUnactiveListbox", $max);
		$this->info_box_head = array (
				'text' => $text,
				'limit' => dol_strlen($text)
		);


		$object=new ReferenceLetters($db);
		$result = $object->fetch_all('ASC','t.datec',5,0,array('t.status'=>0));
		if ($result<0) {
			setEventMessage($object->error,'errors');
		}

		if (is_array($object->lines) && count($object->lines)>0) {
			foreach($object->lines as $key=>$line) {
				$this->info_box_contents[$key][0] = array('td' => 'align="left" width="16"',
						'logo' => 'label',
						'url' => dol_buildpath('/referenceletters/referenceletters/card.php',1).'?id='.$line->id);
				$this->info_box_contents[$key][1] = array('td' => 'align="left" width="15"',
						'text' => $line->title,
						'url' => dol_buildpath('/referenceletters/referenceletters/card.php',1).'?id='.$line->id);
				$this->info_box_contents[$key][2] = array('td' => 'align="left" width="15"',
						'text' => dol_print_date($line->datec,'daytext'));
			}
		}



	}

	/**
	 * Method to show box
	 *
	 * @param array $head with properties of box title
	 * @param array $contents with properties of box lines
	 * @param integer $nooutput nooutput
	 * @return void
	 */
	function showBox($head = null, $contents = null, $nooutput=0) {
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}