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
require_once (DOL_DOCUMENT_ROOT . "/custom/referenceletters/core/modules/referenceletters/mod_referenceletters_standard.php");


/**
 * Put here description of your class
 */
class ReferenceLettersElements extends CommonObject
{
	public $db; // !< To store db handler
	public $error; // !< To return error code (or message)
	public $errors = array(); // !< To return several error codes (or messages)
	public $element = 'referenceletterselements'; // !< Id that identify managed objects
	public $table_element = 'referenceletters_elements'; // !< Name of table without prefix where object is stored
	public $id;
	public $entity;
	public $ref;
	public $fk_referenceletters;
	public $element_type;
	public $fk_element;
	public $content_letter;
	public $import_key;
	public $fk_user_creat;
	public $datec = '';
	public $fk_user_modif;
	public $tms = '';
	public $title;
	public $outputref;
	public $title_referenceletters;
	public $lines = array();

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

		if (isset($this->ref))
			$this->entity = trim($this->ref);
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
		} else if (is_string($this->content_letter)) {
			$content_letter = trim($this->content_letter);
		}

		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "referenceletters_elements(";

		$sql .= "entity,";
		$sql .= "ref,";
		$sql .= "title,";
		$sql .= "outputref,";
		$sql .= "fk_referenceletters,";
		$sql .= "element_type,";
		$sql .= "fk_element,";
		$sql .= "content_letter,";
		$sql .= "import_key,";
		$sql .= "fk_user_creat,";
		$sql .= "datec,";
		$sql .= "fk_user_modif,";
		$sql .= "use_custom_header,";
		$sql .= "header,";
		$sql .= "use_custom_footer,";
		$sql .= "footer,";
		$sql .= "use_landscape_format";

		$sql .= ") VALUES (";

		$sql .= " " . $conf->entity . ",";
		$sql .= " " . (! isset($this->ref) ? 'NULL' : "'" . $this->ref . "'") . ",";
		$sql .= " " . (! isset($this->title) ? 'NULL' : "'" . $this->title . "'") . ",";
		$sql .= " " . (empty($this->outputref) ? '0' : $this->outputref) . ",";
		$sql .= " " . (! isset($this->fk_referenceletters) ? 'NULL' : $this->fk_referenceletters) . ",";
		$sql .= " " . (! isset($this->element_type) ? 'NULL' : "'" . $this->db->escape($this->element_type) . "'") . ",";
		$sql .= " " . (! isset($this->fk_element) ? 'NULL' : $this->fk_element) . ",";
		$sql .= " " . (empty($content_letter) ? 'NULL' : "'" . $this->db->escape($content_letter) . "'") . ",";
		$sql .= " " . (! isset($this->import_key) ? 'NULL' : "'" . $this->db->escape($this->import_key) . "'") . ",";
		$sql .= " " . $user->id . ",";
		$sql .= " '" . $this->db->idate(dol_now()) . "',";
		$sql .= " " . $user->id . ",";
		$sql .= " " . ( int ) $this->use_custom_header . ",";
		$sql .= " " . (isset($this->header) ? "'" . $this->header . "'" : 'NULL') . ",";
		$sql .= " " . ( int ) $this->use_custom_footer . ",";
		$sql .= " " . (isset($this->footer) ? "'" . $this->footer . "'" : 'NULL') . ",";
		$sql .= " " . ( int ) $this->use_landscape_format;

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
		$sql .= " t.ref,";
		$sql .= " t.title,";
		$sql .= " t.outputref,";
		$sql .= " t.fk_referenceletters,";
		$sql .= " t.element_type,";
		$sql .= " t.fk_element,";
		$sql .= " t.content_letter,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_creat,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_modif,";
		$sql .= " t.tms,";
		$sql .= " t.use_custom_header,";
		$sql .= " t.header,";
		$sql .= " t.use_custom_footer,";
		$sql .= " t.footer,";
		$sql .= " t.use_landscape_format";
		$sql .= " ,p.title as title_referenceletters";

		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "referenceletters_referenceletters as p ON p.rowid=t.fk_referenceletters";
		$sql .= " WHERE t.rowid = " . $id;

		dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;

				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
				$this->fk_referenceletters = $obj->fk_referenceletters;
				$this->element_type = $obj->element_type;
				$this->fk_element = $obj->fk_element;
				$this->content_letter = unserialize($obj->content_letter);
				$this->import_key = $obj->import_key;
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_modif = $obj->fk_user_modif;
				$this->tms = $this->db->jdate($obj->tms);
				$this->title = $obj->title;
				$this->outputref = $obj->outputref;
				$this->title_referenceletters = $obj->title_referenceletters;
				$this->use_custom_header = $obj->use_custom_header;
				$this->header = $obj->header;
				$this->use_custom_footer = $obj->use_custom_footer;
				$this->footer = $obj->footer;
				$this->use_landscape_format = $obj->use_landscape_format;
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
		$sql .= " t.ref,";
		$sql .= " t.title,";
		$sql .= " t.outputref,";
		$sql .= " t.fk_referenceletters,";
		$sql .= " t.element_type,";
		$sql .= " t.fk_element,";
		$sql .= " t.content_letter,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_creat,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_modif,";
		$sql .= " t.tms";
		$sql .= " ,p.title as title_referenceletters";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "referenceletters_referenceletters as p ON p.rowid=t.fk_referenceletters";
		$sql .= " WHERE t.entity IN (".getEntity("referenceletters", 1).")";
		$sql .= " AND t.fk_element = " . $element_id;
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
				$this->lines = array();

				while ( $obj = $this->db->fetch_object($resql) ) {

					$line = new ReferenceLettersElementsLine();

					$line->id = $obj->rowid;

					$line->entity = $obj->entity;
					$line->ref = $obj->ref;
					$line->fk_referenceletters = $obj->fk_referenceletters;
					$line->outputref = $obj->outputref;
					$line->element_type = $obj->element_type;
					$line->fk_element = $obj->fk_element;
					$line->content_letter = unserialize($obj->content_letter);
					$line->import_key = $obj->import_key;
					$line->fk_user_creat = $obj->fk_user_creat;
					$line->datec = $this->db->jdate($obj->datec);
					$line->fk_user_modif = $obj->fk_user_modif;
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
		$sql .= " t.ref,";
		$sql .= " t.title,";
		$sql .= " t.outputref,";
		$sql .= " t.fk_referenceletters,";
		$sql .= " t.element_type,";
		$sql .= " t.fk_element,";
		$sql .= " t.content_letter,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_creat,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_modif,";
		$sql .= " t.tms";
		$sql .= " ,p.title as title_referenceletters";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters_elements as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "referenceletters_referenceletters as p ON p.rowid=t.fk_referenceletters";
		$sql .= " WHERE t.entity IN (" . getEntity('referenceletters') . ")";

		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 't.element_type') {
					$sql .= ' AND ' . $key . '=\'' . $this->db->escape($value) . '\'';
				} elseif ($key !== 'search_company' && $key !== 'search_ref') {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (! empty($sortfield)) {
			$sql .= " ORDER BY " . $sortfield . ' ' . $sortorder;
		}

		dol_syslog(get_class($this) . "::fetchAll sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			if ($num > 0) {
				$this->lines = array();
				$num = 0;
				while ( $obj = $this->db->fetch_object($resql) ) {

					$addline = true;
					// Search for company need to be calculated
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

						if ($object_ref->element_type_list[$obj->element_type]['objectclass'] == 'Societe') {

							if (strpos(mb_strtoupper($object_src->name, 'UTF-8'), mb_strtoupper($filter['search_company'], 'UTF-8')) !== false) {
								$addline = true;
							}
						} else {
							if (strpos(mb_strtoupper($object_src->thirdparty->name, 'UTF-8'), mb_strtoupper($filter['search_company'], 'UTF-8')) !== false) {
								$addline = true;
							}
						}
					} else {
						$addline = true;
					}
					if (array_key_exists('search_ref', $filter) && ! empty($filter['search_ref'])) {
						$object_ref = new ReferenceLetters($this->db);
						$element_type = $langs->trans($obj->element_type);
						include_once ($object_ref->element_type_list[$obj->element_type]['classpath'] . $object_ref->element_type_list[$obj->element_type]['class']);
						$class = $object_ref->element_type_list[$obj->element_type]['objectclass'];

						$object_src = new $class($this->db);
						$object_src->fetch($obj->fk_element);
						$addline = false;

						if (strpos(mb_strtoupper($object_src->ref, 'UTF-8'), mb_strtoupper($filter['search_ref'], 'UTF-8')) !== false) {
							$addline = true;
						}
						if ($object_ref->element_type_list[$obj->element_type]['objectclass'] == 'Societe') {
							if (strpos(mb_strtoupper($object_src->name, 'UTF-8'), mb_strtoupper($filter['search_ref'], 'UTF-8')) !== false) {
								$addline = true;
							}
						} else if ($object_ref->element_type_list[$obj->element_type]['objectclass'] == 'Contact') {

							if (strpos(mb_strtoupper($object_src->lastname, 'UTF-8'), mb_strtoupper($filter['search_ref'], 'UTF-8')) !== false || strpos(mb_strtoupper($object_src->firstname, 'UTF-8'), mb_strtoupper($filter['search_ref'], 'UTF-8')) !== false) {

								$addline = true;
							}
						}
					}

					if ($addline) {
						$num ++;
						$line = new ReferenceLettersElementsLine();

						$line->id = $obj->rowid;

						$line->entity = $obj->entity;
						$line->ref = $obj->ref;
						$line->fk_referenceletters = $obj->fk_referenceletters;
						$line->outputref = $obj->outputref;
						$line->element_type = $obj->element_type;
						$line->fk_element = $obj->fk_element;
						//Comment because out of memory
						// $line->content_letter = unserialize($obj->content_letter);
						$line->import_key = $obj->import_key;
						$line->fk_user_creat = $obj->fk_user_creat;
						$line->datec = $this->db->jdate($obj->datec);
						$line->fk_user_modif = $obj->fk_user_modif;
						$line->tms = $this->db->jdate($obj->tms);
						$line->title = $obj->title;
						$line->title_referenceletters = $obj->title_referenceletters;

						$this->lines[] = $line;
					}
				}
			}

			$this->lines = array_splice($this->lines, $offset, $limit);

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
		if (isset($this->ref))
			$this->entity = trim($this->ref);
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

		$sql .= " ref=" . (isset($this->ref) ? "'" . $this->db->escape($this->ref) . "'" : "null") . ",";
		$sql .= " title=" . (isset($this->title) ? "'" . $this->db->escape($this->title) . "'" : "null") . ",";
		$sql .= " fk_referenceletters=" . (isset($this->fk_referenceletters) ? $this->fk_referenceletters : "null") . ",";
		$sql .= " outputref=" . (! empty($this->outputref) ? $this->outputref : "0") . ",";
		$sql .= " element_type=" . (isset($this->element_type) ? "'" . $this->db->escape($this->element_type) . "'" : "null") . ",";
		$sql .= " fk_element=" . (isset($this->fk_element) ? $this->fk_element : "null") . ",";
		$sql .= " content_letter=" . (! empty($content_letter) ? "'" . $this->db->escape($content_letter) . "'" : "null") . ",";
		$sql .= " import_key=" . (isset($this->import_key) ? "'" . $this->db->escape($this->import_key) . "'" : "null") . ",";
		$sql .= " fk_user_modif=" . $user->id . ",";
		$sql .= " use_custom_header=" . ( int ) $this->use_custom_header . ",";
		$sql .= " use_custom_footer=" . ( int ) $this->use_custom_footer . ",";
		$sql .= " header=" . (isset($this->header) ? "'" . $this->header . "'" : "null") . ",";
		$sql .= " footer=" . (isset($this->footer) ? "'" . $this->footer . "'" : "null") . ",";
		$sql .= " use_landscape_format=" . ( int ) $this->use_landscape_format;

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
		$this->ref = 'LTR0001';
		$this->fk_referenceletters = '';
		$this->element_type = '';
		$this->fk_element = '';
		$this->content_letter = '';
		$this->import_key = '';
		$this->fk_user_creat = '';
		$this->datec = '';
		$this->fk_user_modif = '';
		$this->tms = '';
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("referenceletters@referenceletters");

		if (empty($conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON)) {
			$conf->global->SCRUMPROJECT_SCRUMCARD_ADDON = 'mod_referenceletters_standard';
		}

		if (!empty($conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON))
		{
			$mybool = false;

			$file = $conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON.".php";
			$classname = $conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/scrumproject/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
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

		$url = dol_buildpath('/referenceletters/instance.php', 1) . '?id=' . $this->fk_element . '&amp;element_type=' . $this->element_type;

		$result = '<a href="' . $url . '">' . ((! empty($withpicto)) ? img_pdf($this->ref) : '') . $this->ref . '</a>';

		return $result;
	}
}
class ReferenceLettersElementsLine
{
	public $id;
	public $entity;
	public $ref;
	public $fk_referenceletters;
	public $element_type;
	public $fk_element;
	public $content_letter;
	public $import_key;
	public $fk_user_creat;
	public $datec = '';
	public $fk_user_modif;
	public $tms = '';
	public $title;
	public $outputref;
	public $title_referenceletters;
}
