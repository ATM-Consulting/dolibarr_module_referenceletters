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
require_once __DIR__ . '/catalog/substitutioncatalogpresentationbuilder.class.php';
// require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");

/**
 * Classe permettant de gérer les modèles de PDF DocEdit.
 *
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
	public $use_custom_header;
	public $use_custom_footer;
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
	public $TDefaultDoc = array();
	public $header;
	public $footer;

	/**
	 * Last sample object used to build the UI catalog for the current element type.
	 *
	 * @var object|null
	 */
	protected $lastCatalogUiObject = null;

	/**
	 * Optional object forced into the UI catalog builder for the current element type.
	 *
	 * @var object|null
	 */
	protected $forcedCatalogUiObject = null;

	/**
	 * Resolve the sample object used to enrich the UI catalog.
	 *
	 * The UI catalog must not depend on an opportunistic fetch against production
	 * tables. We only reuse an object explicitly injected by the caller.
	 *
	 * @param object $testObj
	 * @return object|null
	 */
	protected function resolveForcedCatalogObject(object $testObj): ?object
	{
		if (
			is_object($this->forcedCatalogUiObject)
			&& get_class($this->forcedCatalogUiObject) === get_class($testObj)
			&& !empty($this->forcedCatalogUiObject->id)
		) {
			return $this->forcedCatalogUiObject;
		}

		return null;
	}

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

		global $conf, $hookmanager;

		$hookmanager->initHooks(array('referenceletters'));

		$this->db = $db;
		if (isset($conf->contract) && !empty($conf->contract->enabled)) {
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
				'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/contract/modules_contract.php',
				'listmodelclass' => 'ModelePDFContract',
                'document_dir' => $conf->contract->dir_output
		);
		}
		if (isset($conf->societe) && !empty($conf->societe->enabled)) {
			$this->element_type_list['thirdparty'] = array(
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
				'picto' => 'company',
				'listmodelfile' => DOL_DOCUMENT_ROOT . '/core/modules/societe/modules_societe.php',
				'listmodelclass' => 'ModeleThirdPartyDoc',
				'document_dir' => $conf->societe->dir_output
			);
		}
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
		if (isset($conf->propal) && !empty($conf->propal->enabled)){
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
				'substitution_method_line' => 'get_substitutionarray_lines',
				'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php',
				'listmodelclass' => 'ModelePDFPropales',
                'document_dir' => $conf->propal->dir_output

		);
		}

		if (property_exists($conf, 'facture') && !empty($conf->facture->enabled) || property_exists($conf, 'invoice') && !empty($conf->invoice->enabled) ){
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
				'substitution_method_line' => 'get_substitutionarray_lines',
				'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php',
				'listmodelclass' => 'ModelePDFFactures',
                'document_dir' => $conf->invoice->dir_output
		);
		}
		if (property_exists($conf, 'commande') && !empty($conf->commande->enabled) || property_exists($conf, 'order') && !empty($conf->order->enabled) ){

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
					'substitution_method_line' => 'get_substitutionarray_lines',
					'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php',
					'listmodelclass' => 'ModelePDFCommandes',
					'document_dir' => $conf->commande->dir_output
			);
		}
		if (isset($conf->fournisseur) && !empty($conf->fournisseur->enabled)) {
			$this->element_type_list['order_supplier'] = array(
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
				'dir_output' => DOL_DATA_ROOT . '/fournisseur/commande/',
				'listmodelfile' => DOL_DOCUMENT_ROOT . '/core/modules/supplier_order/modules_commandefournisseur.php',
				'listmodelclass' => 'ModelePDFSuppliersOrders',
				'document_dir' => $conf->fournisseur->commande->dir_output
			);
		}
		if ( isset($conf->supplier_proposal) && !empty($conf->supplier_proposal->enabled) ) {
			$this->element_type_list['supplier_proposal'] = array(
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
				'dir_output' => DOL_DATA_ROOT . '/supplier_proposal/',
				'listmodelfile' => DOL_DOCUMENT_ROOT . '/core/modules/supplier_proposal/modules_supplier_proposal.php',
				'listmodelclass' => 'ModelePDFSupplierProposal',
				'document_dir' => $conf->supplier_proposal->dir_output
			);
		}
		if (isset($conf->expedition) && !empty($conf->expedition->enabled)) {
			$this->element_type_list['expedition'] = array (
				'class' => 'expedition.class.php',
				'securityclass' => 'expedition',
				'securityfeature' => '',
				'objectclass' => 'Expedition',
				'classpath' => DOL_DOCUMENT_ROOT . '/expedition/class/',
				'trans' => 'sendings',
				'title' => 'Shipment',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/sendings.lib.php',
				'menuloader_function' => 'shipping_prepare_head',
				'card' => '/expedition/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines',
				'dir_output'=>DOL_DATA_ROOT.'/expedition/sending/',
				'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php',
				'listmodelclass' => 'ModelePdfExpedition',
				'document_dir' => $conf->expedition->dir_output
			);
		}
		if (isset($conf->expedition) && !empty($conf->expedition->enabled)) {
			$this->element_type_list['shipping'] = array (
				'class' => 'expedition.class.php',
				'securityclass' => 'expedition',
				'securityfeature' => '',
				'objectclass' => 'Expedition',
				'classpath' => DOL_DOCUMENT_ROOT . '/expedition/class/',
				'trans' => 'sending',
				'title' => 'SendingSheet',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/expedition.lib.php',
				'menuloader_function' => 'expedition_prepare_head',
				'card' => 'expedition/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines'
		);
		}
		if (isset($conf->ficheinter) && !empty($conf->ficheinter->enabled)) {
			$this->element_type_list['fichinter'] = array(
				'class' => 'fichinter.class.php',
				'securityclass' => 'fichinter',
				'securityfeature' => '',
				'objectclass' => 'Fichinter',
				'classpath' => DOL_DOCUMENT_ROOT . '/fichinter/class/',
				'trans' => 'fichinter',
				'title' => 'Intervention',
				'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/fichinter.lib.php',
				'menuloader_function' => 'fichinter_prepare_head',
				'card' => '/fichinter/card.php',
				'substitution_method' => 'get_substitutionarray_object',
				'substitution_method_line' => 'get_substitutionarray_lines',
				'dir_output' => DOL_DATA_ROOT . '/ficheinter/',
				'listmodelfile' => DOL_DOCUMENT_ROOT . '/core/modules/fichinter/modules_fichinter.php',
				'listmodelclass' => 'ModelePDFFicheinter',
				'document_dir' => $conf->ficheinter->dir_output
			);
		}
		$this->TStatus[ReferenceLetters::STATUS_VALIDATED]='RefLtrAvailable';
		$this->TStatus[ReferenceLetters::STATUS_DRAFT]='RefLtrUnvailable';

		$this->TDefaultDoc[ReferenceLetters::DEFAULTDOC_YES]='Yes';
		$this->TDefaultDoc[ReferenceLetters::DEFAULTDOC_NO]='No';

		if(!empty($conf->agefodd->enabled)) {

			// Training convention.
			$this->element_type_list['rfltr_agefodd_convention'] = array (
					'class' => 'agsession.class.php',
					'objectclass' => 'Agsession',
					'classpath' => dol_buildpath('/agefodd/class/'),
					'trans' => 'agefodd',
					'title' => 'AgfConvention',
					'card' => '/agefodd/session/card.php',
					'substitution_method' => 'get_substitutionarray_agefodd',
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
			    ,'contrat_presta'=>'AgfContratPrestation'
			    ,'mission_trainer'=>'AgfTrainerMissionLetter'
			    ,'contrat_trainer'=>'AgfContratTrainer'
			    ,'courrier'=>'Courrier'
			    ,'convocation_trainee'=>'Convocation Stagiaire'
			    ,'attestation_trainee'=>'Attestation stagiaire'
			    ,'attestationendtraining_trainee'=>'AgfendTrainingTrainee'
				,'linked_certificate_completion_trainee'=>'AgfLinkedDocCertificatAchievment'
				,'certificate_completion_trainee'=>'AgfTraineeDocCertificatAchievment'
			);

			if(!empty($conf->agefoddcertificat->enabled)) {
				$Tab['certificateA4']='CertifTemplateA4';
				$Tab['certificatecard']='CertifTemplateCredit';
				$Tab['certificateA4_trainee']='CertifTemplateA4ByTrainee';
				$Tab['certificatecard_trainee']='CertifTemplateCreditByTrainee';
			}

			foreach ($Tab as $key => $val){
			    $this->element_type_list['rfltr_agefodd_'.$key] = $this->element_type_list['rfltr_agefodd_convention'];
			    $this->element_type_list['rfltr_agefodd_'.$key]['title'] = $val;
			}


			// Initial training program.
			$this->element_type_list['rfltr_agefodd_formation'] = array (
				'class' => 'agefodd_formation_catalogue.class.php',
				'objectclass' => 'Formation',
				'classpath' => dol_buildpath('/agefodd/class/'),
				'trans' => 'agefodd',
				'title' => 'AgfFormationInitiale',
				'card' => '/agefodd/training/card.php',
				'substitution_method' => 'get_substitutionarray_agefodd_formation',
				'substitution_method_line' => 'get_substitutionarray_lines_agefodd'
			);

			foreach (array(
				'rfltr_agefodd_fiche_pedago' => 'AgfFichePedagogique',
				'rfltr_agefodd_fiche_pedago_modules' => 'AgfFichePedagogiqueModule',
			) as $formationType => $formationTitle) {
				$this->element_type_list[$formationType] = $this->element_type_list['rfltr_agefodd_formation'];
				$this->element_type_list[$formationType]['title'] = $formationTitle;
			}
		}

		// Hook allowing other modules to register additional document types.
		// In the long term, Agefodd could rely on this hook as well and remove
		// direct Agefodd-specific knowledge from DocEdit.
		$parameters = array('element_type_list' => &$this->element_type_list);
		$hookmanager->executeHooks('referencelettersConstruct', $parameters, $this);

		return 1;
	}

	/**
	 * Force the object used to build the popup catalog for the current element type.
	 *
	 * @param object|null $object
	 * @return void
	 */
	public function setForcedCatalogUiObject($object)
	{
		$this->forcedCatalogUiObject = is_object($object) ? $object : null;
	}

	/**
	 * Clear any forced popup catalog object.
	 *
	 * @return void
	 */
	public function clearForcedCatalogUiObject()
	{
		$this->forcedCatalogUiObject = null;
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
		$sql .= " " . (int) $this->use_landscape_format . ",";
		$sql .= " " . (int) $this->use_custom_header . ",";
		$sql .= " " . (! isset($this->header) ? 'NULL' : "'" . $this->header . "'") .",";
		$sql .= " " . (int) $this->use_custom_footer . ",";
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

			if (!getDolGlobalString('MAIN_EXTRAFIELDS_DISABLED')) {
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


		if(empty($id) && empty($title)) return 0;

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
					$this->fetch_optionals($this->id);
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
		$label = '';
		if(!empty($this->element_type_list[$this->element_type]['trans'])) $langs->load($this->element_type_list[$this->element_type]['trans']);
		if(!empty($this->element_type_list[$this->element_type]['title'])) {
			if(empty($mode)) {
				$label = $langs->trans($this->element_type_list[$this->element_type]['title']);
			}
			else {
				$label = $langs->transnoentities($this->element_type_list[$this->element_type]['title']);
			}
		}
		return $label;
	}

	/**
	 * Fonction mal nommée car elle ne retourne pas une clé de substitution.
	 * Elle retourne un tableau associant des clés de substitution aux valeurs par lesquelles on doit remplacer les
	 * clés.
	 *
	 * @param User $user
	 * @return array
	 *
	 */
	public function getSubstitutionKey($user): array {
		global $conf, $langs, $mysoc, $hookmanager;

		require_once 'commondocgeneratorreferenceletters.class.php';
		require_once __DIR__ . '/catalog/substitutioncatalogbuilder.class.php';
		$langs->load('admin');

		$mailOnlyTags = array(
			'__AGENDATOKEN__',
			'__FORMDATESESSION__',
			'__FORMINTITULE__',
			'__TRAINER_1_EXTRAFIELD_XXXX__',
			'__CERTIFICAT_NUMBER__',
			'__CERTIFICAT_STAGIAIRE_LASTNAME__',
			'__CERTIFICAT_STAGIAIRE_FIRSTNAME__',
			'__CERTIFICAT_DATE_START__',
			'__CERTIFICAT_DATE_END__',
		);

		$subst_array = array();
		$docgen = new CommonDocGeneratorReferenceLetters($this->db);
		$catalogBuilder = new SubstitutionCatalogBuilder($this->db, $this, $docgen, $langs);
		$currentCatalogObject = null;
		$this->lastCatalogUiObject = null;
		$subst_array[$langs->trans('User')] = $docgen->get_substitutionarray_user($user, $langs);
		$subst_array[$langs->trans('MenuCompanySetup')] = $docgen->get_substitutionarray_mysoc($mysoc, $langs);
		$subst_array[$langs->trans('Other')] = $docgen->get_substitutionarray_other($langs);
		complete_substitutions_array($subst_array[$langs->trans('Other')], $langs);
		$catalogBuilder->sanitizeGlobalCatalogKeys($subst_array[$langs->trans('Other')], $mailOnlyTags);

		foreach ($this->element_type_list as $type => $item) {
			if ($this->element_type == $type) {
				$langs->load($item['trans']);
				/** @var $testObj CommonObject */
				require_once $item['classpath'] . $item['class'];
				$testObj = new $item['objectclass']($this->db);

				$forcedCatalogObject = $this->resolveForcedCatalogObject($testObj);
				if (is_object($forcedCatalogObject)) {
					$testObj = $forcedCatalogObject;
					$currentCatalogObject = $testObj;

					if (method_exists($testObj, 'fetch_thirdparty') && (!isset($testObj->thirdparty) || !is_object($testObj->thirdparty))) {
						$testObj->fetch_thirdparty();
					}
					if (!empty($testObj->thirdparty) && is_object($testObj->thirdparty) && method_exists($testObj->thirdparty, 'fetch_optionals')) {
						$testObj->thirdparty->fetch_optionals();
					}
					if (method_exists($testObj, 'fetch_lines') && (!isset($testObj->lines) || !is_array($testObj->lines))) {
						$testObj->fetch_lines();
					}

					$arraySecondThirdpartyObject = array();

					if ($testObj->element == 'societe') {
						$arrayFirstThirdpartyObject = $docgen->get_substitutionarray_thirdparty($testObj, $langs);

						foreach ($arrayFirstThirdpartyObject as $key => $value) {
							$arraySecondThirdpartyObject['cust_' . $key] = $value;
						}
						$subst_array[$langs->trans($item['title'])] = $arraySecondThirdpartyObject;
					} else {
						dol_syslog($item['substitution_method']);
						$subst_array[$langs->trans($item['title'])] = $docgen->{$item['substitution_method']}($testObj, $langs);
						$catalogBuilder->appendExternalContactCatalogKeys($subst_array[$langs->trans($item['title'])], $testObj);
						if ($item['substitution_method'] === 'get_substitutionarray_object') {
							$catalogBuilder->appendStandardCatalogKeys($subst_array[$langs->trans($item['title'])], $type);
						}
					}

					if (! empty($testObj->thirdparty->id)) {
						$arrayFirstThirdpartyObject = $docgen->get_substitutionarray_thirdparty($testObj->thirdparty, $langs);
						foreach ($arrayFirstThirdpartyObject as $key => $value) {
							$arraySecondThirdpartyObject['cust_' . $key] = $value;
						}
					}

					$subst_array[$langs->trans($item['title'])] = array_merge($subst_array[$langs->trans($item['title'])], $arraySecondThirdpartyObject);
					$catalogBuilder->appendThirdpartyCatalogKeys($subst_array[$langs->trans($item['title'])], !empty($testObj->thirdparty) ? $testObj->thirdparty : null);

					$contextOther = $docgen->get_substitutionarray_other($langs, $testObj);
					complete_substitutions_array($contextOther, $langs, $testObj);
					$catalogBuilder->sanitizeGlobalCatalogKeys($contextOther, $mailOnlyTags);
					$subst_array[$langs->trans('Other')] = array_merge($subst_array[$langs->trans('Other')], $contextOther);
				} else {
					$arraySecondThirdpartyObject = array();
					$currentCatalogObject = $testObj;
					if ($testObj->element == 'societe') {
						$arrayFirstThirdpartyObject = $docgen->get_substitutionarray_thirdparty($testObj, $langs);

						foreach ($arrayFirstThirdpartyObject as $key => $value) {
							$arraySecondThirdpartyObject['cust_' . $key] = $value;
						}
						$subst_array[$langs->trans($item['title'])] = $arraySecondThirdpartyObject;
					} else {
						$subst_array[$langs->trans($item['title'])] = $docgen->{$item['substitution_method']}($testObj, $langs);
						$catalogBuilder->appendExternalContactCatalogKeys($subst_array[$langs->trans($item['title'])], $testObj);
						if ($item['substitution_method'] === 'get_substitutionarray_object') {
							$catalogBuilder->appendStandardCatalogKeys($subst_array[$langs->trans($item['title'])], $type);
						}

						$thirdpartyStatic = new Societe($this->db);
						$arrayFirstThirdpartyObject = $docgen->get_substitutionarray_thirdparty($thirdpartyStatic, $langs);
						foreach ($arrayFirstThirdpartyObject as $key => $value) {
							$arraySecondThirdpartyObject['cust_' . $key] = $value;
						}
						$subst_array[$langs->trans($item['title'])] = array_merge($subst_array[$langs->trans($item['title'])], $arraySecondThirdpartyObject);
						$catalogBuilder->appendThirdpartyCatalogKeys($subst_array[$langs->trans($item['title'])], $thirdpartyStatic);
					}

					$contextOther = $docgen->get_substitutionarray_other($langs, $testObj);
					complete_substitutions_array($contextOther, $langs, $testObj);
					$catalogBuilder->sanitizeGlobalCatalogKeys($contextOther, $mailOnlyTags);
					$subst_array[$langs->trans('Other')] = array_merge($subst_array[$langs->trans('Other')], $contextOther);
				}
			}
		}

		require_once 'referenceletterselements.class.php';
		$testObj = new ReferenceLettersElements($this->db);
		$catalogBuilder->appendReferenceLetterCatalogKeys($subst_array, $testObj);

		$catalogBuilder->appendDocumentLineCatalogKeys(
			$subst_array,
			!empty($this->element_type_list[$this->element_type]['substitution_method_line'])
		);

		// Agefodd UI groups must only be exposed on Agefodd documents.
		if (isModEnabled('agefodd') && $this->isAgefoddElementType($this->element_type)) {
			$this->completeSubstitutionKeyArrayWithAgefoddData($subst_array);
		}

		// Generic fallback: expose newly detected keys in advanced groups
		// until they are promoted into explicit UI groups.
		$catalogBuilder->appendDetectedCatalogKeys($subst_array, (string) $this->element_type, $currentCatalogObject, array(
			'is_agefodd' => $this->isAgefoddElementType($this->element_type),
			'is_agefodd_formation' => $this->isAgefoddFormationElementType($this->element_type),
		));

		$catalogBuilder->relocateTechnicalGlobalCatalogKeys(
			$subst_array,
			$langs->trans('Other'),
			$langs->trans('RefLtrTechnicalConstantsTitle'),
			$mailOnlyTags
		);

		$parameters = array('subst_array' => &$subst_array);
		$hookmanager->executeHooks('referencelettersCompleteSubstitutionArray', $parameters, $this);
		$this->lastCatalogUiObject = $currentCatalogObject;
		return $subst_array;
	}

	/**
	 * Build the presentation catalog shown in DocEdit UI.
	 *
	 * @param User $user Current user.
	 * @return array
	 */
	public function getSubstitutionKeyPresentation($user): array
	{
		global $langs;

		$builder = new SubstitutionCatalogPresentationBuilder($langs, $this->db);
		return $builder->buildCatalogPresentation(
			$this->getSubstitutionKey($user),
			(string) $this->element_type,
			$this->lastCatalogUiObject,
			$this->getLoopCatalogPresentation()
		);
	}

	/**
	 * Build the full DocEdit UI catalog with scalar tags and loop metadata.
	 *
	 * @param User $user Current user.
	 * @return array<string,mixed>
	 */
	public function getSubstitutionKeyUiData($user): array
	{
		return array(
			'tags' => $this->getSubstitutionKeyPresentation($user),
			'loops' => $this->getLoopCatalogPresentation(),
		);
	}

	/**
	 * Return the loops available for the current document type.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function getLoopCatalogPresentation(): array
	{
		global $conf, $langs;

		$loops = array();

		if (!$this->isAgefoddElementType($this->element_type)) {
			$item = isset($this->element_type_list[$this->element_type]) ? $this->element_type_list[$this->element_type] : array();
			if (!empty($item['substitution_method_line'])) {
				$loops[] = $this->buildLoopDefinition(
					'lines',
					$langs->trans('RefLtrLines'),
					'Boucle des lignes standard du document courant.',
					array('line_fulldesc', 'line_product_ref', 'line_qty', 'line_price_ht_locale'),
					$langs->trans('RefLtrLines')
				);

				if ($this->element_type === 'contract') {
					$loops[] = $this->buildLoopDefinition(
						'lines_active',
						'Active contract lines',
						'Loop limited to active contract lines.',
						array('line_fulldesc', 'line_date_start_locale', 'line_date_end_locale', 'line_price_ht_locale'),
						$langs->trans('RefLtrLines')
					);
				}
			}

			return $loops;
		}

		if ($this->isAgefoddFormationElementType($this->element_type)) {
			$loops[] = $this->buildLoopDefinition(
				'TFormationObjPeda',
				'Pedagogic objectives',
				'Loop over formation pedagogic objectives.',
				array('line_objpeda_rang', 'line_objpeda_description'),
				'Agefodd Liste des objectifs pedagogiques'
			);
			$loops[] = $this->buildLoopDefinition(
				'TFormationModules',
				'Training modules',
				'Loop over formation modules.',
				array('line_module_title', 'line_module_duration', 'line_module_obj_peda', 'line_module_content_text'),
				'Agefodd Modules formation'
			);
			return $loops;
		}

		if (!$this->isAgefoddSessionElementType($this->element_type)) {
			return $loops;
		}

		$loops[] = $this->buildLoopDefinition(
			'THorairesSession',
			'Session schedules',
			'Loop over session schedules.',
			array('line_date_session', 'line_heure_debut_session', 'line_heure_fin_session'),
			'Agefodd Liste des horaires'
		);
		$loops[] = $this->buildLoopDefinition(
			'TFormationObjPeda',
			'Pedagogic objectives',
			'Loop over session pedagogic objectives.',
			array('line_objpeda_rang', 'line_objpeda_description'),
			'Agefodd Liste des objectifs pedagogiques'
		);
		$loops[] = $this->buildLoopDefinition(
			'TFormationModules',
			'Training modules',
			'Loop over training modules.',
			array('line_module_title', 'line_module_duration', 'line_module_obj_peda', 'line_module_content_text'),
			'Agefodd Modules formation'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSession',
			'Participants',
			'Loop over the full participant list.',
			array('line_nom', 'line_prenom', 'line_email', 'line_statut'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSessionPresent',
			'Present participants',
			'Loop over participants marked as present in the session.',
			array('line_nom', 'line_prenom', 'line_statut', 'line_stagiaire_presence_total'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSessionSoc',
			'Participants by thirdparty',
			'Loop over participants grouped by thirdparty.',
			array('line_nom_societe', 'line_nom', 'line_prenom', 'line_email'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSessionSocPresent',
			'Present participants by thirdparty',
			'Loop over present participants grouped by thirdparty.',
			array('line_nom_societe', 'line_nom', 'line_prenom', 'line_statut'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSessionSocConfirm',
			'Confirmed participants by thirdparty',
			'Loop over confirmed participants grouped by thirdparty.',
			array('line_nom_societe', 'line_nom', 'line_prenom', 'line_email'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSessionSocMore',
			'Participants by thirdparty (extended details)',
			'Additional participant loop grouped by thirdparty.',
			array('line_nom_societe', 'line_nom', 'line_prenom', 'line_type'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStagiairesSessionConvention',
			'Convention participants',
			'Loop limited to convention participants.',
			array('line_nom', 'line_prenom', 'line_email', 'line_financiers_trainee'),
			'Agefodd Liste des participants'
		);
		$loops[] = $this->buildLoopDefinition(
			'TFormateursSession',
			'Trainers',
			'Loop over trainers attached to the session.',
			array('line_formateur_nom', 'line_formateur_prenom', 'line_formateur_mail', 'line_formateur_phone'),
			'Agefodd Liste des formateurs'
		);
		$loops[] = $this->buildLoopDefinition(
			'TConventionFinancialLine',
			'Convention financial lines',
			'Loop over convention financial lines.',
			array('line_fin_desciption', 'line_fin_qty', 'line_fin_amount_ht', 'line_fin_amount_ttc'),
			'Agefodd Lignes financieres session'
		);
		$loops[] = $this->buildLoopDefinition(
			'TFormateursSessionCal',
			'Trainer schedule',
			'Loop over the trainer schedule.',
			array('line_formateur_nom', 'line_date_session', 'line_heure_debut_session', 'line_heure_fin_session'),
			'Agefodd Agenda formateur'
		);
		$loops[] = $this->buildLoopDefinition(
			'TSteps',
			'Steps',
			'Loop over all session steps.',
			array('line_step_label', 'line_step_date_start', 'line_step_date_end', 'line_step_lieu'),
			'Agefodd Liste des etapes'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStepsDistanciel',
			'Remote steps',
			'Loop over remote steps.',
			array('line_step_label', 'line_step_date_start', 'line_step_date_end', 'line_step_duration'),
			'Agefodd Liste des etapes'
		);
		$loops[] = $this->buildLoopDefinition(
			'TStepsPresentiel',
			'On-site steps',
			'Loop over on-site steps.',
			array('line_step_label', 'line_step_date_start', 'line_step_date_end', 'line_step_lieu'),
			'Agefodd Liste des etapes'
		);

		if (!empty($conf->agefoddcertificat->enabled)) {
			$loops[] = $this->buildLoopDefinition(
				'TSessionStagiairesCertif',
				'Trainee certificates',
				'Loop over trainee certificates.',
				array('line_certif_code', 'line_certif_label', 'line_certif_date_debut', 'line_certif_date_fin'),
				'Agefodd Liste des participants'
			);
			$loops[] = $this->buildLoopDefinition(
				'TSessionStagiairesCertifSoc',
				'Trainee certificates by thirdparty',
				'Loop over trainee certificates grouped by thirdparty.',
				array('line_nom_societe', 'line_certif_code', 'line_certif_label', 'line_certif_date_fin'),
				'Agefodd Liste des participants'
			);
		}

		return $loops;
	}

	/**
	 * Build one UI descriptor for a BEGIN/END loop.
	 *
	 * @param string $segment Segment name.
	 * @param string $label User-facing label.
	 * @param string $description Loop description.
	 * @param array<int,string> $sampleTags Representative tags available inside the loop.
	 * @param string $groupLabel UI group label containing the related fields.
	 * @return array<string,mixed>
	 */
	protected function buildLoopDefinition(string $segment, string $label, string $description, array $sampleTags, string $groupLabel = ''): array
	{
		global $langs;

		$exampleTag = !empty($sampleTags) ? $sampleTags[0] : '';
		$example = '[!-- BEGIN ' . $segment . ' --]' . "\n";
		if ($exampleTag !== '') {
			$example .= '{' . $exampleTag . '}' . "\n";
		}
		$example .= '[!-- END ' . $segment . ' --]';

		$groupUsageLabel = '';
		if ($groupLabel !== '') {
			$groupUsageLabel = is_object($langs) ? $langs->trans('RefLtrLoopGroupUsage', $groupLabel) : 'Voir les champs dans le bloc : ' . $groupLabel;
		}

		return array(
			'segment' => $segment,
			'label' => $label,
			'description' => $description,
			'sample_tags' => $sampleTags,
			'group_label' => $groupLabel,
			'group_usage_label' => $groupUsageLabel,
			'syntax' => $example,
		);
	}

	/**
	 * @param array $subst_array
	 * @return void
	 */
	public function completeSubstitutionKeyArrayWithAgefoddData(array &$subst_array): void {

		global $langs, $conf;

		$isFormationDoc = $this->isAgefoddFormationElementType($this->element_type);
		$isTrainerDoc = $this->isAgefoddTrainerElementType($this->element_type);
		$isTraineeDoc = $this->isAgefoddTraineeElementType($this->element_type);
		$isConventionDoc = $this->isAgefoddConventionElementType($this->element_type);
		$isSessionDoc = $this->isAgefoddSessionElementType($this->element_type);

		$groupLabels = array(
			'formation_catalogue' => $langs->trans('RefLtrGroupAgefoddFormationCatalogue'),
			'trainer_mission' => $langs->trans('RefLtrGroupAgefoddTrainerMission'),
			'session' => $langs->trans('RefLtrGroupAgefoddCurrentSession'),
			'training' => $langs->trans('RefLtrGroupAgefoddTraining'),
			'organization' => $langs->trans('RefLtrGroupAgefoddSessionContext'),
			'training_modules' => $langs->trans('RefLtrGroupAgefoddTrainingModules'),
			'participants' => $langs->trans('RefLtrGroupAgefoddParticipants'),
			'steps' => $langs->trans('RefLtrGroupAgefoddSteps'),
			'step' => $langs->trans('RefLtrGroupAgefoddCurrentStep'),
			'horaires' => $langs->trans('RefLtrGroupAgefoddSchedules'),
			'formateurs' => $langs->trans('RefLtrGroupAgefoddTrainers'),
			'financial_lines' => $langs->trans('RefLtrGroupAgefoddFinancialLines'),
			'pedagogic_objectives' => $langs->trans('RefLtrGroupAgefoddPedagogicObjectives'),
			'trainee' => $langs->trans('RefLtrGroupAgefoddCurrentTrainee'),
			'convention' => $langs->trans('RefLtrGroupAgefoddConvention'),
			'trainer_times' => $langs->trans('RefLtrGroupAgefoddTrainerSchedule'),
		);

			$catalogBuilder = new SubstitutionCatalogBuilder($this->db, $this, new CommonDocGeneratorReferenceLetters($this->db), $langs);

			// Drop the raw legacy Agefodd block and the old Agsession group
			// to keep only the curated business-oriented groups.
			$legacyTitle = '';
			if (!empty($this->element_type_list[$this->element_type]['title'])) {
				$legacyTitle = $langs->trans($this->element_type_list[$this->element_type]['title']);
			}
			if ($legacyTitle !== '' && isset($subst_array[$legacyTitle])) {
				unset($subst_array[$legacyTitle]);
			}
			if(isset($subst_array['Agsession'])) unset($subst_array['Agsession']);

		$subst_array[$groupLabels['session']] = array(

		);
		$subst_array[$groupLabels['training']] = array(

		);
		$subst_array[$groupLabels['organization']] = array(

		);

		$catalogBuilder->appendScopedAgefoddCatalogKeys($subst_array, $groupLabels, array(
			'is_agefodd' => true,
			'is_formation_doc' => $isFormationDoc,
			'is_session_doc' => $isSessionDoc,
			'is_convention_doc' => $isConventionDoc,
			'is_trainee_doc' => $isTraineeDoc,
			'is_trainer_doc' => $isTrainerDoc,
		));

		if ($isFormationDoc) {
			unset($subst_array[$groupLabels['training']]);
			unset($subst_array[$groupLabels['trainer_mission']]);
			unset($subst_array[$groupLabels['session']]);
			unset($subst_array[$groupLabels['organization']]);
			unset($subst_array[$groupLabels['participants']]);
			unset($subst_array[$groupLabels['steps']]);
			unset($subst_array[$groupLabels['step']]);
			unset($subst_array[$groupLabels['horaires']]);
			unset($subst_array[$groupLabels['formateurs']]);
			unset($subst_array[$groupLabels['financial_lines']]);
			unset($subst_array[$groupLabels['trainee']]);
			unset($subst_array[$groupLabels['convention']]);
			unset($subst_array[$groupLabels['trainer_times']]);
		} elseif ($isSessionDoc) {
			if (!$isTrainerDoc) {
				unset($subst_array[$groupLabels['trainer_mission']]);
				unset($subst_array[$groupLabels['trainer_times']]);
			}

			if (!$isTraineeDoc) {
				unset($subst_array[$groupLabels['trainee']]);
			}

			if (!$isConventionDoc) {
				unset($subst_array[$groupLabels['convention']]);
			}

			unset($subst_array[$langs->trans('RefLtrLines')]);
		}


		// Reserved for contract lines only.
		$subst_array[$langs->trans('RefLtrLines')]['date_ouverture'] = 'Date démarrage réelle (réservé aux contrats)';
		$subst_array[$langs->trans('RefLtrLines')]['date_ouverture_prevue'] = 'Date prévue de démarrage (réservé aux contrats)';
		$subst_array[$langs->trans('RefLtrLines')]['date_fin_validite'] = 'Date fin réelle (réservé aux contrats)';

		if ($this->isAgefoddElementType($this->element_type)) {
			unset($subst_array[$langs->trans('RefLtrLines')]);
		}


	}

	/**
	 * @deprecated Use getSubstitutionKey() instead.
	 *
	 * @param User $user
	 * @return array
	 */
	public function getSubtitutionKey($user): array {
		return $this->getSubstitutionKey($user);
	}

	/**
	 * @deprecated Use getSubstitutionKeyPresentation() instead.
	 *
	 * @param User $user
	 * @return array
	 */
	public function getSubtitutionKeyPresentation($user): array
	{
		return $this->getSubstitutionKeyPresentation($user);
	}

	/**
	 * @deprecated Use getSubstitutionKeyUiData() instead.
	 *
	 * @param User $user
	 * @return array<string,mixed>
	 */
	public function getSubtitutionKeyUiData($user): array
	{
		return $this->getSubstitutionKeyUiData($user);
	}

	/**
	 * @deprecated Use completeSubstitutionKeyArrayWithAgefoddData() instead.
	 *
	 * @param array<string,mixed> $subst_array
	 * @return void
	 */
	public function completeSubtitutionKeyArrayWithAgefoddData(array &$subst_array): void {
		$this->completeSubstitutionKeyArrayWithAgefoddData($subst_array);
	}

	protected function isAgefoddElementType(string $elementType): bool
	{
		return is_string($elementType) && strpos($elementType, 'rfltr_agefodd_') === 0;
	}

	protected function isAgefoddFormationElementType(string $elementType): bool
	{
		return in_array($elementType, array(
			'rfltr_agefodd_formation',
			'rfltr_agefodd_fiche_pedago',
			'rfltr_agefodd_fiche_pedago_modules',
		), true);
	}

	protected function isAgefoddSessionElementType(string $elementType): bool
	{
		return $this->isAgefoddElementType($elementType) && !$this->isAgefoddFormationElementType($elementType);
	}

	protected function isAgefoddTrainerElementType(string $elementType): bool
	{
		return in_array($elementType, array('rfltr_agefodd_mission_trainer', 'rfltr_agefodd_contrat_trainer'), true);
	}

	protected function isAgefoddTraineeElementType(string $elementType): bool
	{
		return is_string($elementType) && preg_match('/_trainee$/', $elementType);
	}

	protected function isAgefoddConventionElementType(string $elementType): bool
	{
		return $elementType === 'rfltr_agefodd_convention';
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

			if (!getDolGlobalString('MAIN_EXTRAFIELDS_DISABLED')) // For avoid conflicts if trigger used
{
				$result = $this->insertExtraFields();
				if ($result < 0) {
					$error ++;
				}
			}
		}

		// Commit or rollback
		if ($error) {
			if(!empty($this->errors)) {
				foreach($this->errors as $errmsg) {
					dol_syslog(get_class($this)."::".__METHOD__.' '.$errmsg, LOG_ERR);
					$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
				}
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
	 * @param bool $forceDeleteElements Force delete element generated with this model
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger = 0, $forceDeleteElements = false)
	{
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

		if (! $error && $forceDeleteElements) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters_elements";
			$sql .= " WHERE fk_referenceletters=" . $this->id;

			dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
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
			foreach ($this->errors as $errmsg) {
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
			// Clone chapters.
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
				if(property_exists($this, 'user_modification')) $this->user_modification = $obj->fk_user_mod;
				if(property_exists($this, 'user_modification_id')) $this->user_modification_id = $obj->fk_user_mod;
				if(property_exists($this, 'user_creation_id')) $this->user_creation_id = $obj->fk_user_author;
				if(property_exists($this, 'user_creation')) $this->user_creation = $obj->fk_user_author; // deprecated v19
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
