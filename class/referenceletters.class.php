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
require_once DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/extrafields.class.php";
// require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");

/**
 * Put here description of your class
 */
class ReferenceLetters extends CommonObject
{
	public $db; // !< To store db handler
	public $error; // !< To return error code (or message)
	public $errors = array (); // !< To return several error codes (or messages)
	public $element = 'referenceletters'; // !< Id that identify managed objects
	public $table_element = 'referenceletters'; // !< Name of table without prefix where object is stored
	public $id;
	public $entity;
	public $title;
	public $element_type;
	public $use_landscape_format;
	public $status;
	public $default_doc;
	public $import_key;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
	public $element_type_list = array ();
	public $lines = array ();
	public $TStatus=array();

	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * DefaultDoc status
	 */
	const DEFAULTDOC_YES= 1;
	/**
	 * DefaultDoc status
	 */
	const DEFAULTDOC_NO = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	function __construct($db) {

		global $conf;

		$this->db = $db;
		$this->element_type_list['contract'] = array (
				'class' => 'contrat.class.php',
				'securityclass' => 'contrat',
				'securityfeature' => '',
				'objectclass' => 'Contrat',
				'classpath' => DOL_DOCUMENT_ROOT . '/contrat/class/',
				'trans' => 'contracts',
				'title' => 'Contract',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/contract.lib.php',
				'menuloader_function' => 'contract_prepare_head',
				'card' => '/contrat/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines',
		);
		$this->element_type_list['thirdparty'] = array (
				'class' => 'societe.class.php',
				'securityclass' => 'societe',
				'securityfeature' => '&societe',
				'objectclass' => 'Societe',
				'classpath' => DOL_DOCUMENT_ROOT . '/societe/class/',
				'trans' => 'companies',
				'title' => 'ThirdParties',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php',
				'menuloader_function' => 'societe_prepare_head',
				'card' => 'societe/soc.php',
				'substitution_method' => 'get_substitutionarray_thirdparty',
				'picto' => 'company'
		);
		$this->element_type_list['contact'] = array (
				'class' => 'contact.class.php',
				'securityclass' => (DOL_VERSION >=8)?'contact':'societe',
				'securityfeature' => 'socpeople&societe',
				'objectclass' => 'Contact',
				'classpath' => DOL_DOCUMENT_ROOT . '/contact/class/',
				'trans' => 'contact',
				'title' => 'Contact',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/contact.lib.php',
				'menuloader_function' => 'contact_prepare_head',
				'card' => 'contact/card.php',
				'substitution_method' => 'get_substitutionarray_contact'
		);
		$this->element_type_list['propal'] = array (
				'class' => 'propal.class.php',
				'securityclass' => 'propal',
				'securityfeature' => '',
				'objectclass' => 'Propal',
				'classpath' => DOL_DOCUMENT_ROOT . '/comm/propal/class/',
				'trans' => 'propal',
				'title' => 'Proposal',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/propal.lib.php',
				'menuloader_function' => 'propal_prepare_head',
				'card' => 'comm/propal/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines'
		);
		$this->element_type_list['invoice'] = array (
				'class' => 'facture.class.php',
				'securityclass' => 'facture',
				'securityfeature' => '',
				'objectclass' => 'Facture',
				'classpath' => DOL_DOCUMENT_ROOT . '/compta/facture/class/',
				'trans' => 'bills',
				'title' => 'Bill',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/invoice.lib.php',
				'menuloader_function' => 'facture_prepare_head',
				'card' => 'compta/facture/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines'
		);
		$this->element_type_list['order'] = array (
				'class' => 'commande.class.php',
				'securityclass' => 'commande',
				'securityfeature' => '',
				'objectclass' => 'Commande',
				'classpath' => DOL_DOCUMENT_ROOT . '/commande/class/',
				'trans' => 'orders',
				'title' => 'CustomerOrder',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/order.lib.php',
				'menuloader_function' => 'commande_prepare_head',
				'card' => 'commande/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines'
		);
		$this->element_type_list['order_supplier'] = array (
				'class' => 'fournisseur.commande.class.php',
				'securityclass' => 'fournisseur',
				'securityfeature' => 'commande_fournisseur',
				'objectclass' => 'CommandeFournisseur',
				'classpath' => DOL_DOCUMENT_ROOT . '/fourn/class/',
				'trans' => 'orders',
				'title' => 'SupplierOrder',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/fourn.lib.php',
				'menuloader_function' => 'ordersupplier_prepare_head',
				'card' => '/fourn/commande/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines',
				'dir_output'=>DOL_DATA_ROOT.'/fournisseur/commande/'
		);
		$this->element_type_list['supplier_proposal'] = array (
				'class' => 'supplier_proposal.class.php',
				'securityclass' => 'supplier_proposal',
				'securityfeature' => '',
				'objectclass' => 'SupplierProposal',
				'classpath' => DOL_DOCUMENT_ROOT . '/supplier_proposal/class/',
				'trans' => 'supplier_proposal',
				'title' => 'CommRequests',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/supplier_proposal.lib.php',
				'menuloader_function' => 'supplier_proposal_prepare_head',
				'card' => '/supplier_proposal/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines',
				'dir_output'=>DOL_DATA_ROOT.'/supplier_proposal/'
		);

		$this->TStatus[ReferenceLetters::STATUS_VALIDATED]='RefLtrAvailable';
		$this->TStatus[ReferenceLetters::STATUS_DRAFT]='RefLtrUnvailable';

		$this->TDefaultDoc[ReferenceLetters::DEFAULTDOC_YES]='Yes';
		$this->TDefaultDoc[ReferenceLetters::DEFAULTDOC_NO]='No';

		if(!empty($conf->agefodd->enabled)) {

			// Convention de formation
			$this->element_type_list['rfltr_agefodd_convention'] = array (
					'class' => 'agsession.class.php',
					'objectclass' => 'Agsession',
					'classpath' => dol_buildpath('/agefodd/class/'),
					'trans' => 'agefodd',
					'title' => 'AgfConvention',
					'card' => '/agefodd/session/card.php',
					'substitution_method' => 'get_substitutionarray_object',
					'substitution_method_line' => 'get_substitutionarray_lines_agefodd'
			);


			$Tab = array(
			    'fiche_pedago'=>'AgfFichePedagogique'
			    ,'fiche_pedago_modules'=>'AgfFichePedagogiqueModule'
			    ,'conseils'=>'AgfConseilsPratique'
			    ,'fiche_presence'=>'AgfFichePresence'
			    ,'fiche_presence_direct'=>'AgfFichePresenceDirect'
			    ,'fiche_presence_empty'=>'AgfFichePresenceEmpty'
			    ,'fiche_presence_trainee'=>'AgfFichePresenceTrainee'
			    ,'fiche_presence_trainee_direct'=>'AgfFichePresenceTraineeDirect'
			    ,'fiche_presence_landscape'=>'AgfFichePresenceTraineeLandscape'
			    ,'fiche_evaluation'=>'AgfFicheEval'
			    ,'fiche_remise_eval'=>'AgfRemiseEval'
			    ,'attestationendtraining_empty'=>'AgfAttestationEndTrainingEmpty'
			    ,'chevalet'=>'AgfChevalet'
			    ,'convocation'=>'AgfPDFConvocation'
			    ,'attestationendtraining'=>'AgfAttestationEndTraining'
			    ,'attestationpresencetraining'=>'AgfAttestationPresenceTraining'
			    ,'attestationpresencecollective'=>'AgfAttestationPresenceCollective'
			    ,'attestation'=>'AgfSendAttestation'
			    ,'certificateA4'=>'AgfPDFCertificateA4'
			    ,'certificatecard'=>'AgfPDFCertificateCard'
			    ,'contrat_presta'=>'AgfContratPrestation'
			    ,'mission_trainer'=>'AgfTrainerMissionLetter'
			    ,'contrat_trainer'=>'AgfContratTrainer'
			    ,'courrier'=>'Courrier'
			    ,'convocation_trainee'=>'Convocation Stagiaire'
			    ,'attestation_trainee'=>'Attestation stagiaire'
			    ,'attestationendtraining_trainee'=>'Attestation de fin de formation stagiaire'
			);

			foreach ($Tab as $key => $val){
			    $this->element_type_list['rfltr_agefodd_'.$key] = $this->element_type_list['rfltr_agefodd_convention'];
			    $this->element_type_list['rfltr_agefodd_'.$key]['title'] = $val;
			}

		}

		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user that creates
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;

		// Clean parameters

		if (empty($this->title)) {
			$this->errors[] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("RefLtrTitle"));
			$error ++;
		}

		if (isset($this->entity))
			$this->entity = trim($this->entity);
		if (isset($this->title))
			$this->title = trim($this->title);
		if (isset($this->element_type))
			$this->element_type = trim($this->element_type);
		if (isset($this->status))
			$this->status = trim($this->status);
		if (isset($this->default_doc))
			$this->default_doc = trim($this->default_doc);
		if (isset($this->import_key))
			$this->import_key = trim($this->import_key);

			// Check parameters
			// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "referenceletters(";

		$sql .= "entity,";
		$sql .= "title,";
		$sql .= "element_type,";
		$sql .= "use_landscape_format,";
		$sql .= "use_custom_header,header,use_custom_footer,footer,";
		$sql .= "status,";
		$sql .= "default_doc,";
		$sql .= "import_key,";
		$sql .= "fk_user_author,";
		$sql .= "datec,";
		$sql .= "fk_user_mod";

		$sql .= ") VALUES (";

		$sql .= " " . $conf->entity . ",";
		$sql .= " " . (! isset($this->title) ? 'NULL' : "'" . $this->db->escape($this->title) . "'") . ",";
		$sql .= " " . (! isset($this->element_type) ? 'NULL' : "'" . $this->db->escape($this->element_type) . "'") . ",";
		$sql .= " " . (int)$this->use_landscape_format . ",";
		$sql .= " " . (int)$this->use_custom_header . ",";
		$sql .= " " . (! isset($this->header) ? 'NULL' : "'" . $this->header . "'") .",";
		$sql .= " " . (int)$this->use_custom_footer . ",";
		$sql .= " " . (! isset($this->footer) ? 'NULL' : "'" . $this->footer . "'") .",";
		$sql .= " " . (! isset($this->status) ? '1' : $this->status ) . ",";
		$sql .= " " . (! isset($this->default_doc) ? '0' : $this->default_doc ) . ",";
		$sql .= " " . (! isset($this->import_key) ? 'NULL' : "'" . $this->db->escape($this->import_key) . "'") . ",";
		$sql .= " " . $user->id . ",";
		$sql .= " '" . $this->db->idate(dol_now()) . "',";
		$sql .= " " . $user->id;

		$sql .= ")";

		$this->db->begin();

		dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
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
				dol_syslog(get_class($this) . "::".__METHOD__. ' ' . $errmsg, LOG_ERR);
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
	public function fetch($id, $title='') {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";

		$sql .= " t.entity,";
		$sql .= " t.title,";
		$sql .= " t.element_type,";
		$sql .= " t.status,";
		$sql .= " t.default_doc,";
		$sql .= " t.import_key,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms,";
		$sql .= " t.use_custom_header,";
		$sql .= " t.header,";
		$sql .= " t.use_custom_footer,";
		$sql .= " t.footer,";
		$sql .= " t.use_landscape_format";

		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters as t";
		$sql .= " WHERE 1 ";
		if(!empty($id)) $sql .= " AND t.rowid = " . $id;
		if(!empty($title)) $sql .= " AND t.title = '".$this->db->escape($title)."'";
		$sql.= ' AND entity IN (' . getEntity('referenceletters') . ')';

		dol_syslog(get_class($this) . "::".__METHOD__. ' ', LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;

				$this->entity = $obj->entity;
				$this->title = $obj->title;
				$this->element_type = $obj->element_type;
				$this->status = $obj->status;
				$this->default_doc = $obj->default_doc;
				$this->import_key = $obj->import_key;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);
				$this->header = $obj->header;
				$this->use_custom_header = $obj->use_custom_header;
				$this->footer = $obj->footer;
				$this->use_custom_footer= $obj->use_custom_footer;
				$this->use_landscape_format = $obj->use_landscape_format;

				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($this->table_element, true);
				if (count($extralabels) > 0) {
					$this->fetch_optionals($this->id, $extralabels);
				}
				$this->db->free($resql);

				return $this->id;
			} else {
				return 0;
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::".__METHOD__ . $this->error, LOG_ERR);
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
	public function fetch_all($sortorder, $sortfield, $limit, $offset, $filter = array()) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";

		$sql .= " t.entity,";
		$sql .= " t.title,";
		$sql .= " t.element_type,";
		$sql .= " t.status,";
		$sql .= " t.default_doc,";
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
				}if ($key == 't.status' || $key == 't.default_doc') {
					$sql .= ' AND ' . $key . '=' . $this->db->escape($value);
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}

		if (! empty($sortfield)) {
			$sql .= $this->db->order($sortfield,$sortorder);
		}

		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
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
					$line->default_doc = $obj->default_doc;
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
			$this->errors[] = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::".__METHOD__ .' '.$this->error, LOG_ERR);
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

		$subst_array = array();
		$docgen = new commondocgeneratorreferenceletters($this->db);
		$docgen->db = $this->db;
		$subst_array[$langs->trans('User')] = $docgen->get_substitutionarray_user($user, $langs);
		$subst_array[$langs->trans('MenuCompanySetup')] = $docgen->get_substitutionarray_mysoc($mysoc, $langs);
		$subst_array[$langs->trans('Other')] = $docgen->get_substitutionarray_other($langs);

		foreach ( $this->element_type_list as $type => $item ) {
			if ($this->element_type == $type) {

				$langs->load($item['trans']);
				//var_dump($item);exit;
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

					$array_second_thirdparty_object = array ();

					if($testObj->element == 'societe'){
						$array_first_thirdparty_object = $docgen->get_substitutionarray_thirdparty($testObj, $langs);

						foreach ( $array_first_thirdparty_object as $key => $value ) {
							$array_second_thirdparty_object['cust_' . $key] = $value;
						}
						$subst_array[$langs->trans($item['title'])] =  $array_second_thirdparty_object;
					}else {
						$subst_array[$langs->trans($item['title'])] = $docgen->{$item['substitution_method']}($testObj, $langs);
					}

					if (! empty($testObj->thirdparty->id)) {

						$array_first_thirdparty_object = $docgen->get_substitutionarray_thirdparty($testObj->thirdparty, $langs);
						foreach ( $array_first_thirdparty_object as $key => $value ) {
							$array_second_thirdparty_object['cust_' . $key] = $value;
						}

					}


					$subst_array[$langs->trans($item['title'])] = array_merge($subst_array[$langs->trans($item['title'])], $array_second_thirdparty_object);
				} else {
					$subst_array[$langs->trans($item['title'])] = array (
							$langs->trans('RefLtrNoneExists', $langs->trans($item['title'])) => $langs->trans('RefLtrNoneExists', $langs->trans($item['title']))
					);
				}
				//TODO : add line replacement
			}
		}

		require_once 'referenceletterselements.class.php';
		$testObj = new ReferenceLettersElements($this->db);
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

			$subst_array[$langs->trans('Module103258Name')] = $docgen->get_substitutionarray_refletter($testObj, $langs);
		} else {
			$subst_array[$langs->trans('Module103258Name')] = array (
					$langs->trans('RefLtrNoneExists', $langs->trans($langs->trans('Module103258Name'))) => $langs->trans('RefLtrNoneExists', $langs->trans($langs->trans('Module103258Name')))
			);
		}

		if(!empty($conf->agefodd->enabled)) $this->completeSubtitutionKeyArrayWithAgefoddData($subst_array);

		return $subst_array;
	}

	public function completeSubtitutionKeyArrayWithAgefoddData(&$subst_array) {

		global $langs;

		// On supprime les clefs que propose automatiquement le module car presque inutiles et on les refait à la main
		if(isset($subst_array['Agsession'])) unset($subst_array['Agsession']);

		$subst_array[$langs->trans('AgfTrainerMissionLetter')]['objvar_object_formateur_session_lastname'] = 'Nom du formateur';
		$subst_array[$langs->trans('AgfTrainerMissionLetter')]['objvar_object_formateur_session_firstname'] = 'Prénom du formateur';

		$subst_array[$langs->trans('RefLtrSubstAgefodd')] = array(
				'formation_nom'=>'Intitulé de la formation'
				,'formation_nom_custo'=>'Intitulé formation (pour les documents PDF)'
				,'formation_ref'=>'Référence de la formation'
				,'formation_statut'=>'Statut de la formation'
		        ,'formation_date_debut' => 'Date de début de la formation'
		        ,'formation_date_fin' => 'Date de fin de la formation'
				,'objvar_object_date_text'=>'Date de la session'
		        ,'formation_duree' => 'Durée de la formation'
				,'formation_commercial'=>'commercial en charge de la formation'
				,'formation_societe'=>'Société concernée'
		        ,'formation_but'=>'But de la formation'
		        ,'formation_methode'=>'Methode de formation'
		        ,'formation_nb_stagiaire'=>'Nombre de stagiaire de la formation'
		        ,'formation_type_stagiaire'=>'Caractéristiques des stagiaires'
		        ,'formation_documents'=>'Documents nécessaires à la formation'
		        ,'formation_equipements'=>'Equipements nécessaires à la formation'
		        ,'formation_lieu'=>'Lieu de la formation'
		        ,'formation_lieu_adresse'=>'Adresse du lieu de formation'
		        ,'formation_lieu_cp'=>'Code postal du lieu de formation'
		        ,'formation_lieu_ville'=>'Ville du lieu de formation'
		        ,'formation_lieu_acces'=>'Instruction d\'accès au lieu lieu de formation'
		        ,'formation_lieu_horaires'=>'Horaires du lieu de formation'
		        ,'formation_lieu_notes'=>'Commentaire du lieu de formation'
		        ,'formation_lieu_divers'=>'Infos Repas, Hébergements, divers'
		        ,'objvar_object_trainer_text'=>'Tous les foramteurs séparés par des virgules'
		        ,'objvar_object_id'=>'Id de la session'
		        ,'objvar_object_dthour_text'=>'Tous les horaires au format texte avec retour à la ligne'
		        ,'objvar_object_trainer_day_cost'=>'Cout formateur (cout/nb de creneaux)'
		);

		// Liste de données - Participants
		$subst_array[$langs->trans('RefLtrSubstAgefoddListParticipants')] = array(
				'line_civilite'=>'Civilité'
				,'line_nom'=>'Nom participant'
				,'line_prenom'=>'Prénom participant'
				,'line_nom_societe'=>'Société du participant'
				,'line_poste'=>'Poste occupé au sein de sa société'
				,'line_mail' => 'Email du participant'
				,'line_siret' => 'SIRET de la société du participant'
				,'line_birthday' => 'Date de naissance du participant'
				,'line_birthplace'=>'Lieu de naissance du participant'
				,'line_code_societe'=> 'Code de la société du participant'
				,'line_nom_societe'=> 'Nom du client du participant'
		);

		// Liste de données - Horaires
		$subst_array[$langs->trans('RefLtrSubstAgefoddListHoraires')] = array(
				'line_date_session'=>'Date de la session'
				,'line_heure_debut_session'=>'Heure début session'
				,'line_heure_fin_session'=>'Heure fin session'
		);

		// Liste de données - Formateurs
		$subst_array[$langs->trans('RefLtrSubstAgefoddListFormateurs')] = array(
				'line_formateur_nom'=>'Nom du formateur'
				,'line_formateur_prenom'=>'Prénom du formateur'
				,'line_formateur_mail'=>'Adresse mail du formateur'
				,'line_formateur_statut'=>'Statut du formateur (Présent, Confirmé, etc...)'
		);

		$subst_array['RefLtrSubstAgefoddStagiaire'] = array(
		    'objvar_object_stagiaire_civilite'=>'Civilité du stagiaire'
		    ,'objvar_object_stagiaire_nom'=>'Nom du stagiaire'
		    ,'objvar_object_stagiaire_prenom'=>'Prénom du stagiaire'
		    ,'objvar_object_stagiaire_mail'=>'Email du stagiaire'
		);

		// Tags des lignes
		$subst_array[$langs->trans('RefLtrLines')] = array(
				'line_fulldesc'=>'Description complète',
				'line_product_ref'=>'Référence produit',
				'line_product_ref_fourn'=>'Référence produit fournisseur (pour les documents fournisseurs)',
				'line_product_label'=>'Libellé produit',
				'line_product_type'=>'Type produit',
				'line_desc'=>'Description',
				'line_vatrate'=>'Taux de TVA',
				'line_up'=>'Prix unitaire (format numérique)',
				'line_up_locale'=>'Prix unitaire (format prix)',
				'line_qty'=>'Qté ligne',
				'line_discount_percent'=>'Remise ligne',
				'line_price_ht'=>'Total HT ligne (format numérique)',
				'line_price_ttc'=>'Total TTC ligne (format numérique)',
				'line_price_vat'=>'Montant TVA (format numérique)',
				'line_price_ht_locale'=>'Total HT ligne (format prix)',
				'line_price_ttc_locale'=>'Total TTC ligne (format prix)',
				'line_price_vat_locale'=>'Montant TVA (format prix)',
				// Dates
				'line_date_start'=>'Date début service',
				'line_date_start_locale'=>'Date début service format 1',
				'line_date_start_rfc'=>'Date début service format 2',
				'line_date_end'=>'Date fin service',
				'line_date_end_locale'=>'Date fin service format 1',
				'line_date_end_rfc'=>'Date fin service format 2',
		);

		// Réservé aux lignes de contrats
		$subst_array[$langs->trans('RefLtrLines')]['date_ouverture'] = 'Date démarrage réelle (réservé aux contrats)';
		$subst_array[$langs->trans('RefLtrLines')]['date_ouverture_prevue'] = 'Date prévue de démarrage (réservé aux contrats)';
		$subst_array[$langs->trans('RefLtrLines')]['date_fin_validite'] = 'Date fin réelle (réservé aux contrats)';

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
	public function update($user = 0, $notrigger = 0) {
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
		if (isset($this->default_doc))
			$this->default_doc = trim($this->default_doc);
		if (isset($this->import_key))
			$this->import_key = trim($this->import_key);

			// Check parameters
			// Put here code to add a control on parameters values

		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "referenceletters SET";

		$sql .= " title=" . (isset($this->title) ? "'" . $this->db->escape($this->title) . "'" : "null") . ",";
		$sql .= " element_type=" . (isset($this->element_type) ? "'" . $this->db->escape($this->element_type) . "'" : "null") . ",";
		$sql .= " status=" . (isset($this->status) ? $this->status : "0") . ",";
		$sql .= " default_doc=" . (isset($this->default_doc) ? $this->default_doc : "0") . ",";
		$sql .= " import_key=" . (isset($this->import_key) ? "'" . $this->db->escape($this->import_key) . "'" : "null") . ",";
		$sql .= " header=" . (isset($this->header) ? "'" . $this->header . "'" : "null") . ",";
		$sql .= " footer=" . (isset($this->footer) ? "'" . $this->footer. "'" : "null") . ",";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " use_custom_header=" . $this->use_custom_header . ",";
		$sql .= " use_custom_footer=" . $this->use_custom_footer . ",";
		$sql .= " use_landscape_format=" . (int)$this->use_landscape_format;

		$sql .= " WHERE rowid=" . $this->id;

		$this->db->begin();

		dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
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
				dol_syslog(get_class($this) . "::".__METHOD__. ' ' . $errmsg, LOG_ERR);
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
	public function delete($user, $notrigger = 0) {
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

			dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}

		if (! $error) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters";
			$sql .= " WHERE rowid=" . $this->id;

			dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}

		if (! $error) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters_extrafields";
			$sql .= " WHERE fk_object=" . $this->id;

			dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::".__METHOD__. ' ' . $errmsg, LOG_ERR);
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
	public function createFromClone($fromid) {
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
						$chaptersnew->default_doc = $line->default_doc;
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
				dol_syslog(get_class($this) . "::".__METHOD__ . $errmsg, LOG_ERR);
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
	public function initAsSpecimen() {
		$this->id = 0;

		$this->entity = '';
		$this->title = '';
		$this->element_type = '';
		$this->status = '';
		$this->default_doc = '';
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
	public function info($id) {
		global $langs;

		$sql = "SELECT";
		$sql .= " p.rowid, p.datec, p.tms, p.fk_user_mod, p.fk_user_author";
		$sql .= " FROM " . MAIN_DB_PREFIX . "referenceletters as p";
		$sql .= " WHERE p.rowid = " . $id;

		dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
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
			dol_syslog(get_class($this) . "::".__METHOD__. " " . $this->error, LOG_ERR);
			return - 1;
		}
	}
}
class ReferenceLettersLine
{
	public $id;
	public $entity;
	public $title;
	public $element_type;
	public $status;
	public $default_doc;
	public $import_key;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
}
