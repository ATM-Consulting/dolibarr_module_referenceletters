<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014  	   HENRY Florian  florian.henry@open-concept.pro
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
 * \file dev/skeletons/referenceletters.class.php
 * \ingroup referenceletters
 * \brief This file is a CRUD class file (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");
// require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
// require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");

/**
 * Put here description of your class
 */
class ReferenceLetters extends CommonObject {
	public $db; // !< To store db handler
	public $error; // !< To return error code (or message)
	public $errors = array (); // !< To return several error codes (or messages)
	public $element = 'referenceletters'; // !< Id that identify managed objects
	public $table_element = 'referenceletters'; // !< Name of table without prefix where object is stored
	public $id;
	public $entity;
	public $title;
	public $element_type;
	public $status;
	public $import_key;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
	public $element_type_list = array ();
	public $lines = array ();
	
	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	function __construct($db) {
		$this->db = $db;
		$this->element_type_list['contract'] = array (
				'class' => 'contrat.class.php',
				'securityclass' => 'contrat',
				'objectclass' => 'Contrat',
				'classpath' => DOL_DOCUMENT_ROOT . '/contrat/class/',
				'trans' => 'contracts',
				'title' => 'Contract',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/contract.lib.php',
				'menuloader_function' => 'contract_prepare_head',
				'card' => '/contrat/fiche.php',
				'substitution_method' => 'get_substitutionarray_object' 
		);
		$this->element_type_list['thirdparty'] = array (
				'class' => 'societe.class.php',
				'securityclass' => 'societe',
				'objectclass' => 'Societe',
				'classpath' => DOL_DOCUMENT_ROOT . '/societe/class/',
				'trans' => 'companies',
				'title' => 'Customer',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php',
				'menuloader_function' => 'societe_prepare_head',
				'card' => 'societe/soc.php',
				'substitution_method' => 'get_substitutionarray_thirdparty' 
		);
		$this->element_type_list['contact'] = array (
				'class' => 'contact.class.php',
				'securityclass' => 'societe',
				'objectclass' => 'Contact',
				'classpath' => DOL_DOCUMENT_ROOT . '/contact/class/',
				'trans' => 'contact',
				'title' => 'Contact',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/contact.lib.php',
				'menuloader_function' => 'contact_prepare_head',
				'card' => 'contact/fiche.php',
				'substitution_method' => 'get_substitutionarray_contact' 
		);
		$this->element_type_list['propal'] = array (
				'class' => 'propal.class.php',
				'securityclass' => 'propal',
				'objectclass' => 'Propal',
				'classpath' => DOL_DOCUMENT_ROOT . '/comm/propal/class/',
				'trans' => 'propal',
				'title' => 'Proposal',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php',
				'menuloader_function' => 'propal_prepare_head',
				'card' => 'comm/propal.php',
				'substitution_method' => 'get_substitutionarray_object' 
		);
		$this->element_type_list['invoice'] = array (
				'class' => 'facture.class.php',
				'securityclass' => 'facture',
				'objectclass' => 'Facture',
				'classpath' => DOL_DOCUMENT_ROOT . '/compta/facture/class/',
				'trans' => 'bills',
				'title' => 'Bill',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/invoice.lib.php',
				'menuloader_function' => 'facture_prepare_head',
				'card' => 'compta/facture.php',
				'substitution_method' => 'get_substitutionarray_object'
		);
		return 1;
	}
	
	/**
	 * Create object into database
	 *
	 * @param User $user that creates
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset($this->entity))
			$this->entity = trim($this->entity);
		if (isset($this->title))
			$this->title = trim($this->title);
		if (isset($this->element_type))
			$this->element_type = trim($this->element_type);
		if (isset($this->status))
			$this->status = trim($this->status);
		if (isset($this->import_key))
			$this->import_key = trim($this->import_key);
			
			// Check parameters
			// Put here code to add control on parameters values
			
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "referenceletters(";
		
		$sql .= "entity,";
		$sql .= "title,";
		$sql .= "element_type,";
		$sql .= "status,";
		$sql .= "import_key,";
		$sql .= "fk_user_author,";
		$sql .= "datec,";
		$sql .= "fk_user_mod";
		
		$sql .= ") VALUES (";
		
		$sql .= " " . $conf->entity . ",";
		$sql .= " " . (! isset($this->title) ? 'NULL' : "'" . $this->db->escape($this->title) . "'") . ",";
		$sql .= " " . (! isset($this->element_type) ? 'NULL' : "'" . $this->db->escape($this->element_type) . "'") . ",";
		$sql .= " " . (! isset($this->status) ? '1' : "'" . $this->status . "'") . ",";
		$sql .= " " . (! isset($this->import_key) ? 'NULL' : "'" . $this->db->escape($this->import_key) . "'") . ",";
		$sql .= " " . $user->id . ",";
		$sql .= " '" . $this->db->idate(dol_now()) . "',";
		$sql .= " " . $user->id;
		
		$sql .= ")";
		
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}
		
		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "referenceletters");
			
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
				
				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		if (! $error) {
			
			if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) {
				$result = $this->insertExtraFields();
				if ($result < 0) {
					$error ++;
				}
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	
	/**
	 * Load object in memory from the database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";
		
		$sql .= " t.entity,";
		$sql .= " t.title,";
		$sql .= " t.element_type,";
		$sql .= " t.status,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms";
		
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters as t";
		$sql .= " WHERE t.rowid = " . $id;
		
		dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				
				$this->id = $obj->rowid;
				
				$this->entity = $obj->entity;
				$this->title = $obj->title;
				$this->element_type = $obj->element_type;
				$this->status = $obj->status;
				$this->import_key = $obj->import_key;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);
				
				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($this->table_element, true);
				if (count($extralabels) > 0) {
					$this->fetch_optionals($this->id, $extralabels);
				}
			}
			$this->db->free($resql);
			
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * Load object in memory from the database
	 *
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all($sortorder, $sortfield, $limit, $offset, $filter = array()) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";
		
		$sql .= " t.entity,";
		$sql .= " t.title,";
		$sql .= " t.element_type,";
		$sql .= " t.status,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms";
		
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters as t";
		
		$sql .= " WHERE t.entity IN (" . getEntity('referenceletters') . ")";
		
		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 't.element_type') {
					$sql .= ' AND ' . $key . '=\'' . $this->db->escape($value) . '\'';
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		
		if (! empty($sortfield)) {
			$sql .= " ORDER BY " . $sortfield . ' ' . $sortorder;
		}
		
		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}
		
		dol_syslog(get_class($this) . "::fetch_all sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num > 0) {
				$this->lines = array ();
				while ( $obj = $this->db->fetch_object($resql) ) {
					
					$line = new ReferenceLettersLine();
					
					$line->id = $obj->rowid;
					
					$line->entity = $obj->entity;
					$line->title = $obj->title;
					$line->element_type = $obj->element_type;
					$line->status = $obj->status;
					$line->import_key = $obj->import_key;
					$line->fk_user_author = $obj->fk_user_author;
					$line->datec = $this->db->jdate($obj->datec);
					$line->fk_user_mod = $obj->fk_user_mod;
					$line->tms = $this->db->jdate($obj->tms);
					
					$this->lines[] = $line;
				}
			}
			$this->db->free($resql);
			
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_all " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * return translated label of element linked
	 *
	 * @param int $mode trans normal, 1 transnoentities
	 * @return string translated element label
	 *        
	 */
	public function displayElement($mode = 0) {
		global $langs;
		
		$langs->load($this->element_type_list[$this->element_type]['trans']);
		if (empty($mode)) {
			$label = $langs->trans($this->element_type_list[$this->element_type]['title']);
		} else {
			$label = $langs->transnoentities($this->element_type_list[$this->element_type]['title']);
		}
		return $label;
	}
	
	/**
	 * return translated label of element linked
	 *
	 * @param int $mode trans normal, 1 transnoentities
	 * @return string translated element label
	 *        
	 */
	public function getSubtitutionKey($user) {
		global $conf, $langs, $mysoc;
		
		require_once 'commondocgeneratorreferenceletters.class.php';
		$langs->load('admin');
		
		$subst_array = '';
		$docgen = new commondocgeneratorreferenceletters($this->db);
		$docgen->db = $this->db;
		$subst_array[$langs->trans('User')] = $docgen->get_substitutionarray_user($user, $langs);
		$subst_array[$langs->trans('MenuCompanySetup')] = $docgen->get_substitutionarray_mysoc($mysoc, $langs);
		$subst_array[$langs->trans('Other')] = $docgen->get_substitutionarray_other($langs);
		
		foreach ( $this->element_type_list as $type => $item ) {
			if ($this->element_type == $type) {
				
				$langs->load($item['trans']);
				
				require_once $item['classpath'] . $item['class'];
				$testObj = new $item['objectclass']($this->db);
				
				$sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . $testObj->table_element . ' WHERE entity IN (' . getEntity($conf->entity, 1) . ') ' . $this->db->plimit(1);
				dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) {
					$num = $this->db->num_rows($resql);
					if ($num > 0) {
						$obj = $this->db->fetch_object($resql);
					}
				}
				if (! empty($obj->rowid) && $num > 0) {
					$testObj->fetch($obj->rowid);
					
					if (method_exists($testObj, 'fetch_thirdparty')) {
						$testObj->fetch_thirdparty();
					}
					
					$subst_array[$langs->trans($item['title'])] = $docgen->$item['substitution_method']($testObj, $langs);
					$array_second_thirdparty_object=array();
					if (!empty($testObj->thirdparty->id)) {
						$array_first_thirdparty_object=$docgen->get_substitutionarray_thirdparty($testObj->thirdparty, $outputlangs);
						foreach($array_first_thirdparty_object as $key=>$value) {
							$array_second_thirdparty_object['cust_'.$key]=$value;
						}
					}
					//var_dump($array_second_thirdparty_object);
					$subst_array[$langs->trans($item['title'])]=array_merge($subst_array[$langs->trans($item['title'])], $array_second_thirdparty_object);
				} else {
					$subst_array[$langs->trans($item['title'])] = array ($langs->trans('RefLtrNoneExists',$langs->trans($item['title']))=>$langs->trans('RefLtrNoneExists',$langs->trans($item['title'])));
				}
			}
		}
		
		return $subst_array;
	}
	
	/**
	 * return translated label of element linked
	 *
	 * @param int $mode trans normal, 1 transnoentities
	 * @return string translated element label
	 *        
	 */
	public function displayElementElement($mode = 0, $element_type = '') {
		global $langs;
		
		$this->element_type = $element_type;
		return $this->displayElement($mode);
	}
	
	/**
	 * Update object into database
	 *
	 * @param User $user that modifies
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update($user = 0, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset($this->entity))
			$this->entity = trim($this->entity);
		if (isset($this->title))
			$this->title = trim($this->title);
		if (isset($this->element_type))
			$this->element_type = trim($this->element_type);
		if (isset($this->status))
			$this->status = trim($this->status);
		if (isset($this->import_key))
			$this->import_key = trim($this->import_key);
			
			// Check parameters
			// Put here code to add a control on parameters values
			
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "referenceletters SET";
		
		$sql .= " title=" . (isset($this->title) ? "'" . $this->db->escape($this->title) . "'" : "null") . ",";
		$sql .= " element_type=" . (isset($this->element_type) ? "'" . $this->db->escape($this->element_type) . "'" : "null") . ",";
		$sql .= " status=" . (isset($this->status) ? $this->status : "null") . ",";
		$sql .= " import_key=" . (isset($this->import_key) ? "'" . $this->db->escape($this->import_key) . "'" : "null") . ",";
		$sql .= " fk_user_mod=" . $user->id;
		
		$sql .= " WHERE rowid=" . $this->id;
		
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::update sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}
		
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
				
				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		if (! $error) {
			
			if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
{
				$result = $this->insertExtraFields();
				if ($result < 0) {
					$error ++;
				}
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}
	
	/**
	 * Delete object in database
	 *
	 * @param User $user that deletes
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		$this->db->begin();
		
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
				
				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		if (! $error) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters_chapters";
			$sql .= " WHERE fk_referenceletters=" . $this->id;
			
			dol_syslog(get_class($this) . "::delete sql=" . $sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}
		
		if (! $error) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters";
			$sql .= " WHERE rowid=" . $this->id;
			
			dol_syslog(get_class($this) . "::delete sql=" . $sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::delete " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}
	
	/**
	 * Load an object from its id and create a new one in database
	 *
	 * @param int $fromid of object to clone
	 * @return int id of clone
	 */
	function createFromClone($fromid) {
		global $user, $langs;
		
		$error = 0;
		
		$object = new Referenceletters($this->db);
		
		$this->db->begin();
		
		// Load source object
		$object->fetch($fromid);
		$object->title = $object->title . ' (Clone)';
		$clonedrefletterid = $object->create($user);
		
		// Other options
		if ($clonedrefletterid < 0) {
			$this->errors[] = $object->error;
			$error ++;
		}
		
		if (! $error) {
			// Clone Chapters
			require_once 'referenceletterschapters.class.php';
			$chapters = new ReferenceLettersChapters($this->db);
			$chaptersnew = new ReferenceLettersChapters($this->db);
			$result = $chapters->fetch_byrefltr($fromid);
			if ($result < 0) {
				$this->errors[] = $object->error;
				$error ++;
			} else {
				if (is_array($chapters->lines_chapters) && count($chapters->lines_chapters) > 0) {
					foreach ( $chapters->lines_chapters as $line ) {
						$chaptersnew = new ReferenceLettersChapters($this->db);
						$chaptersnew->entity = $line->entity;
						$chaptersnew->fk_referenceletters = $object->id;
						$chaptersnew->lang = $line->lang;
						$chaptersnew->sort_order = $line->sort_order;
						$chaptersnew->title = $line->title;
						$chaptersnew->content_text = $line->content_text;
						$chaptersnew->options_text = $line->options_text;
						$chaptersnew->status = $line->status;
						$result = $chaptersnew->create($user);
						if ($result < 0) {
							$this->errors[] = $object->error;
							$error ++;
						}
					}
				}
			}
		}
		
		// End
		if (! $error) {
			$this->db->commit();
			return $object->id;
		} else {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::delete " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1;
		}
	}
	
	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	function initAsSpecimen() {
		$this->id = 0;
		
		$this->entity = '';
		$this->title = '';
		$this->element_type = '';
		$this->status = '';
		$this->import_key = '';
		$this->fk_user_author = '';
		$this->datec = '';
		$this->fk_user_mod = '';
		$this->tms = '';
	}
	
	/**
	 * Give information on the object
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function info($id) {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " p.rowid, p.datec, p.tms, p.fk_user_mod, p.fk_user_author";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters as p";
		$sql .= " WHERE p.rowid = " . $id;
		
		dol_syslog(get_class($this) . "::info sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->tms);
				$this->user_modification = $obj->fk_user_mod;
				$this->user_creation = $obj->fk_user_author;
			}
			$this->db->free($resql);
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::info " . $this->error, LOG_ERR);
			return - 1;
		}
	}
}
class ReferenceLettersLine {
	public $id;
	public $entity;
	public $title;
	public $element_type;
	public $status;
	public $import_key;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
}
