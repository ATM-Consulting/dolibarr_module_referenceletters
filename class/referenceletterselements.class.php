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
 * \file class/referenceletterselements.class.php
 * \ingroup referenceletters
 * \brief This file is a CRUD class file (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");

/**
 * Put here description of your class
 */
class ReferenceLettersElements extends CommonObject
{
	public $db; // !< To store db handler
	public $error; // !< To return error code (or message)
	public $errors = array (); // !< To return several error codes (or messages)
	public $element = 'referenceletterselements'; // !< Id that identify managed objects
	public $table_element = 'referenceletters_elements'; // !< Name of table without prefix where object is stored
	public $id;
	public $entity;
	public $ref_int;
	public $fk_referenceletters;
	public $element_type;
	public $fk_element;
	public $content_letter;
	public $import_key;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
	public $title;
	public $outputref;
	public $title_referenceletters;
	public $lines = array ();
	
	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	function __construct($db) {
		$this->db = $db;
		return 1;
	}
	
	/**
	 * Create object into database
	 *
	 * @param User $user User that creates
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset($this->ref_int))
			$this->entity = trim($this->ref_int);
		if (isset($this->fk_referenceletters))
			$this->fk_referenceletters = trim($this->fk_referenceletters);
		if (isset($this->element_type))
			$this->element_type = trim($this->element_type);
		if (isset($this->fk_element))
			$this->fk_element = trim($this->fk_element);
		if (isset($this->import_key))
			$this->import_key = trim($this->import_key);
		if (isset($this->title))
			$this->title = trim($this->title);
		if (isset($this->outputref))
			$this->outputref = trim($this->outputref);
			
			// Check parameters
			// Put here code to add a control on parameters values
		if (is_array($this->content_letter) && count($this->content_letter) > 0) {
			$content_letter = serialize($this->content_letter);
		} else {
			$content_letter = trim($this->content_letter);
		}
		
		// Check parameters
		// Put here code to add control on parameters values
		
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "referenceletters_elements(";
		
		$sql .= "entity,";
		$sql .= "ref_int,";
		$sql .= "title,";
		$sql .= "outputref,";
		$sql .= "fk_referenceletters,";
		$sql .= "element_type,";
		$sql .= "fk_element,";
		$sql .= "content_letter,";
		$sql .= "import_key,";
		$sql .= "fk_user_author,";
		$sql .= "datec,";
		$sql .= "fk_user_mod";
		
		$sql .= ") VALUES (";
		
		$sql .= " " . $conf->entity . ",";
		$sql .= " " . (! isset($this->ref_int) ? 'NULL' : "'" . $this->ref_int . "'") . ",";
		$sql .= " " . (! isset($this->title) ? 'NULL' : "'" . $this->title . "'") . ",";
		$sql .= " " . (empty($this->outputref) ? '0' : $this->outputref) . ",";
		$sql .= " " . (! isset($this->fk_referenceletters) ? 'NULL' : $this->fk_referenceletters) . ",";
		$sql .= " " . (! isset($this->element_type) ? 'NULL' : "'" . $this->db->escape($this->element_type) . "'") . ",";
		$sql .= " " . (! isset($this->fk_element) ? 'NULL' : $this->fk_element) . ",";
		$sql .= " " . (empty($content_letter) ? 'NULL' : "'" . $this->db->escape($content_letter) . "'") . ",";
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
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "referenceletters_elements");
			
			if (! $notrigger) {
				
				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('REFLETTERINSTANCE_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
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
	 * @param int $id Id object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";
		
		$sql .= " t.entity,";
		$sql .= " t.ref_int,";
		$sql .= " t.title,";
		$sql .= " t.outputref,";
		$sql .= " t.fk_referenceletters,";
		$sql .= " t.element_type,";
		$sql .= " t.fk_element,";
		$sql .= " t.content_letter,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms";
		$sql .= " ,p.title as title_referenceletters";
		
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "referenceletters as p ON p.rowid=t.fk_referenceletters";
		$sql .= " WHERE t.rowid = " . $id;
		
		dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				
				$this->id = $obj->rowid;
				
				$this->entity = $obj->entity;
				$this->ref_int = $obj->ref_int;
				$this->fk_referenceletters = $obj->fk_referenceletters;
				$this->element_type = $obj->element_type;
				$this->fk_element = $obj->fk_element;
				$this->content_letter = unserialize($obj->content_letter);
				$this->import_key = $obj->import_key;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);
				$this->title = $obj->title;
				$this->outputref = $obj->outputref;
				$this->title_referenceletters = $obj->title_referenceletters;
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
	 * @param unknown $element_id
	 * @param unknown $element_type
	 * @param string $sortorder
	 * @param string $sortfield
	 * @param number $limit
	 * @param number $offset
	 * @return number
	 */
	public function fetchAllByElement($element_id, $element_type, $sortorder = '', $sortfield = '', $limit = 0, $offset = 0) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.entity,";
		$sql .= " t.ref_int,";
		$sql .= " t.title,";
		$sql .= " t.outputref,";
		$sql .= " t.fk_referenceletters,";
		$sql .= " t.element_type,";
		$sql .= " t.fk_element,";
		$sql .= " t.content_letter,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms";
		$sql .= " ,p.title as title_referenceletters";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "referenceletters as p ON p.rowid=t.fk_referenceletters";
		$sql .= " WHERE t.fk_element = " . $element_id;
		$sql .= " AND t.element_type = '" . $this->db->escape($element_type) . "'";
		
		if (! empty($sortfield)) {
			$sql .= " ORDER BY " . $sortfield . ' ' . $sortorder;
		}
		
		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}
		
		dol_syslog(get_class($this) . "::fetchAllByElement sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num > 0) {
				$this->lines = array ();
				
				while ( $obj = $this->db->fetch_object($resql) ) {
					
					$line = new ReferenceLettersElementsLine();
					
					$line->id = $obj->rowid;
					
					$line->entity = $obj->entity;
					$line->ref_int = $obj->ref_int;
					$line->fk_referenceletters = $obj->fk_referenceletters;
					$line->outputref = $obj->outputref;
					$line->element_type = $obj->element_type;
					$line->fk_element = $obj->fk_element;
					$line->content_letter = unserialize($obj->content_letter);
					$line->import_key = $obj->import_key;
					$line->fk_user_author = $obj->fk_user_author;
					$line->datec = $this->db->jdate($obj->datec);
					$line->fk_user_mod = $obj->fk_user_mod;
					$line->tms = $this->db->jdate($obj->tms);
					$line->title = $obj->title;
					$line->title_referenceletters = $obj->title_referenceletters;
					
					$this->lines[] = $line;
				}
			}
			$this->db->free($resql);
			
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetchAllByElement " . $this->error, LOG_ERR);
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
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array()) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.entity,";
		$sql .= " t.ref_int,";
		$sql .= " t.title,";
		$sql .= " t.outputref,";
		$sql .= " t.fk_referenceletters,";
		$sql .= " t.element_type,";
		$sql .= " t.fk_element,";
		$sql .= " t.content_letter,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms";
		$sql .= " ,p.title as title_referenceletters";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "referenceletters as p ON p.rowid=t.fk_referenceletters";
		$sql .= " WHERE t.entity IN (" . getEntity('referenceletters') . ")";
		
		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 't.element_type') {
					$sql .= ' AND ' . $key . '=\'' . $this->db->escape($value) . '\'';
				} elseif ($key !== 'search_company') {
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
		
		dol_syslog(get_class($this) . "::fetchAll sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num > 0) {
				$this->lines = array ();
				
				while ( $obj = $this->db->fetch_object($resql) ) {
					
					//Search for company need to be calculated
					if (array_key_exists('search_company', $filter) && ! empty($filter['search_company'])) {
						
						require_once 'referenceletters.class.php';
						$object_ref = new ReferenceLetters($this->db);
						
						$addline = false;
						require_once $object_ref->element_type_list[$obj->element_type]['classpath'] . $object_ref->element_type_list[$obj->element_type]['class'];
						$object_src = new $object_ref->element_type_list[$obj->element_type]['objectclass']($this->db);
						
						$result = $object_src->fetch($obj->fk_element);
						if ($result < 0) {
							$this->errors[] = $object_src->error;
							$error ++;
						}
						if (method_exists($object_src, 'fetch_thirdparty')) {
							$result = $object_src->fetch_thirdparty();
							if ($result < 0) {
								$error ++;
								$this->errors[] = $object_src->error;
							}
						}
						
						if ($this->element_type_list[$obj->element_type]['objectclass'] == 'Societe') {
							if (strpos(mb_strtoupper($object_src->name,'UTF-8'), mb_strtoupper($filter['search_company'],'UTF-8')) !== false) {
								$addline = true;
							}
						} else {
							if (strpos(mb_strtoupper($object_src->thirdparty->name,'UTF-8'), mb_strtoupper($filter['search_company'],'UTF-8')) !== false) {
								$addline = true;
							}
						}
					} else {
						$addline = true;
					}
					
					if ($addline) {
						$line = new ReferenceLettersElementsLine();
						
						$line->id = $obj->rowid;
						
						$line->entity = $obj->entity;
						$line->ref_int = $obj->ref_int;
						$line->fk_referenceletters = $obj->fk_referenceletters;
						$line->outputref = $obj->outputref;
						$line->element_type = $obj->element_type;
						$line->fk_element = $obj->fk_element;
						$line->content_letter = unserialize($obj->content_letter);
						$line->import_key = $obj->import_key;
						$line->fk_user_author = $obj->fk_user_author;
						$line->datec = $this->db->jdate($obj->datec);
						$line->fk_user_mod = $obj->fk_user_mod;
						$line->tms = $this->db->jdate($obj->tms);
						$line->title = $obj->title;
						$line->title_referenceletters = $obj->title_referenceletters;
						
						$this->lines[] = $line;
					}
				}
			}
			$this->db->free($resql);
			
			if (! empty($error)) {
				return - 1;
			}
			
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetchAll " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * Update object into database
	 *
	 * @param User $user User that modifies
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update($user = 0, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset($this->entity))
			$this->entity = trim($this->entity);
		if (isset($this->ref_int))
			$this->entity = trim($this->ref_int);
		if (isset($this->fk_referenceletters))
			$this->fk_referenceletters = trim($this->fk_referenceletters);
		if (isset($this->element_type))
			$this->element_type = trim($this->element_type);
		if (isset($this->fk_element))
			$this->fk_element = trim($this->fk_element);
		if (isset($this->import_key))
			$this->import_key = trim($this->import_key);
		if (isset($this->title))
			$this->title = trim($this->title);
		if (isset($this->outputref))
			$this->outputref = trim($this->outputref);
		
		if (is_array($this->content_letter) && count($this->content_letter) > 0) {
			$content_letter = serialize($this->content_letter);
		} else {
			$content_letter = trim($this->content_letter);
		}
		
		// Check parameters
		// Put here code to add a control on parameters values
		
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "referenceletters_elements SET";
		
		$sql .= " ref_int=" . (isset($this->ref_int) ? "'" . $this->db->escape($this->ref_int) . "'" : "null") . ",";
		$sql .= " title=" . (isset($this->title) ? "'" . $this->db->escape($this->title) . "'" : "null") . ",";
		$sql .= " fk_referenceletters=" . (isset($this->fk_referenceletters) ? $this->fk_referenceletters : "null") . ",";
		$sql .= " outputref=" . (!empty($this->outputref) ? $this->outputref : "0") . ",";
		$sql .= " element_type=" . (isset($this->element_type) ? "'" . $this->db->escape($this->element_type) . "'" : "null") . ",";
		$sql .= " fk_element=" . (isset($this->fk_element) ? $this->fk_element : "null") . ",";
		$sql .= " content_letter=" . (! empty($content_letter) ? "'" . $this->db->escape($content_letter) . "'" : "null") . ",";
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
	 * @param User $user User that deletes
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
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
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters_elements";
			$sql .= " WHERE rowid=" . $this->id;
			
			dol_syslog(get_class($this) . "::delete sql=" . $sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}
		
		if (! $error) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "actioncomm SET fk_element=NULL, elementtype=NULL";
			$sql .= " WHERE fk_element=" . $this->id;
			$sql .= " AND elementtype='referenceletters_referenceletterselements'";
			
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
	 * @param int $fromid Id of object to clone
	 * @return int New id of clone
	 */
	function createFromClone($fromid) {
		global $user, $langs;
		
		$error = 0;
		
		$object = new Referenceletterselements($this->db);
		
		$this->db->begin();
		
		// Load source object
		$object->fetch($fromid);
		$object->id = 0;
		$object->statut = 0;
		
		// Clear fields
		// ...
		
		// Create clone
		$result = $object->create($user);
		
		// Other options
		if ($result < 0) {
			$this->error = $object->error;
			$error ++;
		}
		
		if (! $error) {
		}
		
		// End
		if (! $error) {
			$this->db->commit();
			return $object->id;
		} else {
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
		$this->ref_int = 'LTR0001';
		$this->fk_referenceletters = '';
		$this->element_type = '';
		$this->fk_element = '';
		$this->content_letter = '';
		$this->import_key = '';
		$this->fk_user_author = '';
		$this->datec = '';
		$this->fk_user_mod = '';
		$this->tms = '';
	}
	
	/**
	 * Returns the reference to the following non used model letters used depending on the active numbering module
	 * defined into REF_LETTER_ADDON
	 *
	 * @param int $fk_user Id
	 * @param societe $objsoc Object
	 * @return string Reference libre pour la lead
	 */
	function getNextNumRef($objsoc, $fk_user = '', $element_type = '') {
		global $conf, $langs;
		$langs->load("referenceletters@referenceletters");
		
		$dirmodels = array_merge(array (
				'/' 
		), ( array ) $conf->modules_parts['models']);
		
		if (! empty($conf->global->REF_LETTER_ADDON)) {
			foreach ( $dirmodels as $reldir ) {
				$dir = dol_buildpath($reldir . "core/modules/referenceletters/");
				if (is_dir($dir)) {
					$handle = opendir($dir);
					if (is_resource($handle)) {
						$var = true;
						
						while ( ($file = readdir($handle)) !== false ) {
							if ($file == $conf->global->REF_LETTER_ADDON . '.php') {
								$file = substr($file, 0, dol_strlen($file) - 4);
								require_once $dir . $file . '.php';
								
								$module = new $file();
								
								// Chargement de la classe de numerotation
								$classname = $conf->global->REF_LETTER_ADDON;
								
								$obj = new $classname();
								
								$numref = "";
								$numref = $obj->getNextValue($fk_user, $element_type, $objsoc, $this);
								
								if ($numref != "") {
									return $numref;
								} else {
									$this->error = $obj->error;
									return "";
								}
							}
						}
					}
				}
			}
		} else {
			$langs->load("errors");
			print $langs->trans("Error") . " " . $langs->trans("ErrorModuleSetupNotComplete");
			return "";
		}
	}
	
	/**
	 * getNomUrl
	 *
	 * @param number $withpicto
	 * @param string $option
	 * @return string
	 */
	public function getNomUrl($withpicto = 0, $option = '') {
		global $langs;
		
		$result = '';
		
		$url = dol_buildpath('/referenceletters/referenceletters/instance.php', 1) . '?id=' . $this->fk_element . '&amp;element_type=' . $this->element_type;
		
		$result = '<a href="' . $url . '">' . ((! empty($withpicto)) ? img_pdf($this->ref_int) : '') . $this->ref_int . '</a>';
		
		return $result;
	}
}
class ReferenceLettersElementsLine
{
	public $id;
	public $entity;
	public $ref_int;
	public $fk_referenceletters;
	public $element_type;
	public $fk_element;
	public $content_letter;
	public $import_key;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
	public $title;
	public $outputref;
	public $title_referenceletters;
}
