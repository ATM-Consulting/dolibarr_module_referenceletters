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
class mybox extends ModeleBoxes {
	var $boxcode = "mybox";
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
	function mybox() {
		global $langs;
		$langs->load("boxes");
		
		$this->boxlabel = $langs->transnoentitiesnoconv("MyBox");
	}
	
	/**
	 * Load data into info_box_contents array to show array later.
	 *
	 * @param int $max of records to load
	 * @return void
	 */
	function loadBox($max = 5) {
		global $conf, $user, $langs, $db;
		
		$this->max = $max;
		
		// include_once DOL_DOCUMENT_ROOT . "/referenceletters/class/referenceletters.class.php";
		
		$text = $langs->trans("MyBoxDescription", $max);
		$this->info_box_head = array (
				'text' => $text,
				'limit' => dol_strlen($text) 
		);
		
		$this->info_box_contents[0][0] = array (
				'td' => 'align="left"',
				'text' => $langs->trans("MyBoxContent") 
		);
	}
	
	/**
	 * Method to show box
	 *
	 * @param array $head with properties of box title
	 * @param array $contents with properties of box lines
	 * @return void
	 */
	function showBox($head = null, $contents = null) {
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}

?>
