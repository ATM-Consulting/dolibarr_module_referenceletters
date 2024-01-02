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
require_once  __DIR__. '/../../lib/referenceletters.lib.php';
/**
 * Class to manage the box
 */
class box_referenceletter_elements extends ModeleBoxes {
	var $boxcode = "referenceletter_elements";
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
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	function __construct(DoliDB $db, $param = '') {
		global $langs, $user;
		parent::__construct($db, $param);

		$langs->load("boxes");
		$this->boxlabel = $langs->transnoentitiesnoconv("Module103258Name").'-'.$langs->transnoentitiesnoconv("RefLtrExistingLetters",10);

		$this->hidden=! ( rl_userHasRight($user,'referenceletters', 'read'));
	}

	/**
	 * Load data into info_box_contents array to show array later.
	 *
	 * @param int $max of records to load
	 * @return void
	 */
	function loadBox($max = 10) {
		global $conf, $user, $langs, $db;

		$this->max = $max;

		dol_include_once("/referenceletters/class/referenceletterselements.class.php");
		dol_include_once("/referenceletters/class/referenceletters.class.php");

		$text = $langs->trans("RefLtrExistingLetters", $max);
		$this->info_box_head = array (
				'text' => $text,
				'limit' => dol_strlen($text)
		);

		$object_ref=new ReferenceLetters($db);

		$object=new ReferenceLettersElements($db);
		$result = $object->fetchAll('DESC','t.datec',5,0);
		if ($result<0) {
			setEventMessage($object->error,'errors');
		}

		if (is_array($object->lines) && count($object->lines)>0) {
			foreach($object->lines as $key=>$line) {

				// Check if current view is setup in models letter class
				if (! is_array($object_ref->element_type_list[$line->element_type])) {
					$this->info_box_contents[$key][0] = array('td' => 'align="left" width="16"',
							'logo' => 'label');
					$this->info_box_contents[$key][1] = array('td' => 'align="left" width="15"',
							'text' => $langs->trans('RefLtrNoModelReadyForThisObject', $line->element_type));
					$this->info_box_contents[$key][2] = array('td' => 'align="left" width="15"',
							'text' => '');
					$this->info_box_contents[$key][3] = array('td' => 'align="left" width="15"',
							'text' => '');
					continue;
				}



				// load class according
				require_once $object_ref->element_type_list[$line->element_type]['classpath'] . $object_ref->element_type_list[$line->element_type]['class'];
				$object = new $object_ref->element_type_list[$line->element_type]['objectclass']($db);

				$result = $object->fetch($line->fk_element);
				if ($result < 0)
					setEventMessage($object->error, 'errors');
				if (method_exists($object, 'fetch_thirdparty')) {
					$result = $object->fetch_thirdparty();
					if ($result < 0)
						setEventMessage($object->error, 'errors');
				}

				$this->info_box_contents[$key][0] = array('td' => 'align="left" width="16"',
						'logo' => 'label',
						'url' => dol_buildpath('referenceletters/referenceletters/instance.php',1).'?id='.$line->fk_element.'&element_type='.$line->element_type);
				$this->info_box_contents[$key][1] = array('td' => 'align="left" width="15"',
						'text' => $line->ref_int,
						'url' => dol_buildpath('/referenceletters/referenceletters/instance.php',1).'?id='.$line->fk_element.'&element_type='.$line->element_type);
				$this->info_box_contents[$key][2] = array('td' => 'align="left" width="15"',
						'text' => $object_ref->displayElementElement(0,$line->element_type));

				if ($object_ref->element_type_list[$line->element_type]['objectclass']=='Societe') {
					$this->info_box_contents[$key][3] = array('td' => 'align="left" width="15"',
							'text' => $object->name,
							'url' => dol_buildpath('societe/card.php',1).'?socid='.$object->id);
				} else {
					$this->info_box_contents[$key][3] = array('td' => 'align="left" width="15"',
						'text' => $object->ref,
						'url' => dol_buildpath($object_ref->element_type_list[$line->element_type]['card'],1).'?id='.$line->fk_element);
				}

				if ($object_ref->element_type_list[$line->element_type]['objectclass']=='Societe') {
					$this->info_box_contents[$key][4] = array('td' => 'align="left" width="15"',
							'text' => $object->name,
							'url' => dol_buildpath('societe/card.php',1).'?socid='.$object->id);
				} else {
					$this->info_box_contents[$key][4] = array('td' => 'align="left" width="15"',
							'text' => $object->thirdparty->name,
							'url' => dol_buildpath('societe/card.php',1).'?socid='.$object->thirdparty->id);
				}
				$this->info_box_contents[$key][5] = array('td' => 'align="left" width="15"',
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
	 * @return string
	 */
	function showBox($head = null, $contents = null, $nooutput = 0) {
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
