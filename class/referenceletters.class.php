<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/referenceletters.class.php
 * \ingroup     referenceletters
 * \brief       This file is a CRUD class file for ReferenceLetters (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ReferenceLetters
 */
class ReferenceLetters extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'referenceletters';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'referenceletters';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'referenceletters_referenceletters';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for referenceletters. Must be the part after the 'object_' into object_referenceletters.png
	 */
	public $picto = 'referenceletters@referenceletters';


	const STATUS_DISABLED = 0;
	const STATUS_ACTIVATED = 1;

	/**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM' or '!empty($conf->multicurrency->enabled)' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'validate'=>'1', 'comment'=>"Reference of object"),
		'element_type' => array('type'=>'varchar(50)', 'label'=>'RefLtrElement', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1,),
		'title' => array('type'=>'varchar(100)', 'label'=>'Title', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1,),
		'use_landscape_format' => array('type'=>'integer', 'label'=>'RefLtrUseLandscapeFormat', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1, 'default'=>'0', 'arrayofkeyval'=>array('1'=>'Oui', '0'=>'Non'),),
		'default_doc' => array('type'=>'integer', 'label'=>'RefLtrDefaultDoc', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1, 'default'=>'0', 'arrayofkeyval'=>array('1'=>'Oui', '0'=>'Non'), 'validate'=>'1',),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>60, 'notnull'=>1, 'visible'=>0, 'default'=>'1', 'index'=>1, 'arrayofkeyval'=>array('1'=>'Activated', '0'=>'Disabled'), 'validate'=>'1',),
		'header' => array('type'=>'text', 'label'=>'Header', 'enabled'=>'1', 'position'=>70, 'notnull'=>0, 'visible'=>0,),
		'footer' => array('type'=>'text', 'label'=>'Footer', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>0, 'default'=>'0',),
		'use_custom_header' => array('type'=>'integer', 'label'=>'UseCustomHeader', 'enabled'=>'1', 'position'=>90, 'notnull'=>1, 'visible'=>0,),
		'use_custom_footer' => array('type'=>'integer', 'label'=>'UseCustomerFooter', 'enabled'=>'1', 'position'=>100, 'notnull'=>1, 'visible'=>0,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>0,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>120, 'notnull'=>0, 'visible'=>0, 'noteditable'=>'1', 'css'=>'left',),
		'import_key' => array('type'=>'varchar(100)', 'label'=>'ImportKey', 'enabled'=>'1', 'position'=>130, 'notnull'=>0, 'visible'=>0,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>140, 'notnull'=>0, 'visible'=>0, 'noteditable'=>'1',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserMod', 'enabled'=>'1', 'position'=>150, 'notnull'=>1, 'visible'=>0,),
	);
	public $rowid;
	public $ref;
	public $element_type;
	public $title;
	public $use_landscape_format;
	public $default_doc;
	public $status;
	public $header;
	public $footer;
	public $use_custom_header;
	public $use_custom_footer;
	public $tms;
	public $date_creation;
	public $import_key;
	public $fk_user_creat;
	public $fk_user_modif;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'referenceletters_referencelettersline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_referenceletters';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'ReferenceLettersline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('referenceletters_referencelettersdet');

	// /**
	//  * @var ReferenceLettersLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

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
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/contract/modules_contract.php',
			'listmodelclass' => 'ModelePDFContract',
			'document_dir' => $conf->contrat->dir_output
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
			'picto' => 'company',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/societe/modules_societe.php',
			'listmodelclass' => 'ModeleThirdPartyDoc',
			'document_dir' => $conf->societe->dir_output
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
			'substitution_method_line' => 'get_substitutionarray_lines',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php',
			'listmodelclass' => 'ModelePDFPropales',
			'document_dir' => $conf->propal->dir_output

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
			'substitution_method_line' => 'get_substitutionarray_lines',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php',
			'listmodelclass' => 'ModelePDFFactures',
			'document_dir' => $conf->facture->dir_output
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
			'substitution_method_line' => 'get_substitutionarray_lines',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php',
			'listmodelclass' => 'ModelePDFCommandes',
			'document_dir' => $conf->commande->dir_output
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
			'dir_output'=>DOL_DATA_ROOT.'/fournisseur/commande/',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/supplier_order/modules_commandefournisseur.php',
			'listmodelclass' => 'ModelePDFSuppliersOrders',
			'document_dir' => $conf->fournisseur->commande->dir_output
		);
		$this->element_type_list['supplier_proposal'] = array (
			'class' => 'supplier_proposal.class.php',
			'securityclass' => 'supplier_proposal',
			'securityfeature' => '',
			'objectclass' => 'SupplierProposal',
			'classpath' => DOL_DOCUMENT_ROOT . '/supplier_proposal/class/',
			'trans' => 'supplier_proposal',
			'title' => 'SupplierProposal',
			'menuloader_lib' => DOL_DOCUMENT_ROOT . '/core/lib/supplier_proposal.lib.php',
			'menuloader_function' => 'supplier_proposal_prepare_head',
			'card' => '/supplier_proposal/card.php',
			'substitution_method' => 'get_substitutionarray_object',
			'substitution_method_line' => 'get_substitutionarray_lines',
			'dir_output'=>DOL_DATA_ROOT.'/supplier_proposal/',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/supplier_proposal/modules_supplier_proposal.php',
			'listmodelclass' => 'ModelePDFSupplierProposal',
			'document_dir' => $conf->supplier_proposal->dir_output
		);
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
			'card' => '/exepedition/card.php',
			'substitution_method' => 'get_substitutionarray_object',
			'substitution_method_line' => 'get_substitutionarray_lines',
			'dir_output'=>DOL_DATA_ROOT.'/expedition/sending/',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php',
			'listmodelclass' => 'ModelePdfExpedition',
			'document_dir' => $conf->expedition->dir_output
		);
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
		$this->element_type_list['fichinter'] = array (
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
			'dir_output'=>DOL_DATA_ROOT.'/ficheinter/',
			'listmodelfile' =>	DOL_DOCUMENT_ROOT.'/core/modules/fichinter/modules_fichinter.php',
			'listmodelclass' => 'ModelePDFFicheinter',
			'document_dir' => $conf->ficheinter->dir_output
		);

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

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->referenceletters->referenceletters->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		return $resultcreate;
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) {
			$object->fetchLines();
		}

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) {
			$object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		}
		if (property_exists($object, 'label')) {
			$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		}
		if (property_exists($object, 'status')) {
			$object->status = self::STATUS_DISABLED;
		}
		if (property_exists($object, 'date_creation')) {
			$object->date_creation = dol_now();
		}
		if (property_exists($object, 'date_modification')) {
			$object->date_modification = null;
		}
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
					//var_dump($key);
					//var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error) {
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0) {
				$error++;
			}
		}
		if (!$error) {
			// copy external contacts if same company
			if (!empty($object->socid) && property_exists($this, 'fk_soc') && $this->fk_soc == $object->socid) {
				if ($this->copy_linked_contact($object, 'external') < 0) {
					$error++;
				}
			}
		}

		if (! $error) {
			// Clone Chapters
			require_once 'referenceletterschapters.class.php';
			$chapters = new ReferenceLettersChapters($this->db);
			$chaptersnew = new ReferenceLettersChapters($this->db);
			$TChapters=$chapters->fetchAll('', '', '', '', array('fk_referenceletters' => $this->id));

			if ($result < 0) {
				$this->errors[] = $object->error;
				$error ++;
			} else {
				if (is_array($TChapters) && count($TChapters) > 0) {
					foreach ($TChapters as $line ) {
						$chaptersnew = new ReferenceLettersChapters($this->db);
						$chaptersnew->fk_referenceletters = $object->id;
						$chaptersnew->lang = $line->lang;
						$chaptersnew->sort_order = $line->sort_order;
						$chaptersnew->title = $line->title;
						$chaptersnew->content_text = $line->content_text;
						$chaptersnew->options_text = $line->options_text;
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
		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->table_element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN ('".$this->db->sanitize($this->db->escape($value))."')";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$i] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false, $forceDeleteElements = false)
	{

		$error = 0;

		if ($forceDeleteElements) {
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
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "referenceletters_referenceletterschapters";
			$sql .= " WHERE fk_referenceletters=" . $this->id;

			dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}

		if (! $error) {
			return $this->deleteCommon($user, $notrigger);
		} else {
			return -1;
		}

		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_ACTIVATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->referenceletters->referenceletters->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->referenceletters->referenceletters->referenceletters_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_ACTIVATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".((int) $this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('REFERENCELETTERS_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'referenceletters/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'referenceletters/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++; $this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->referenceletters->dir_output.'/referenceletters/'.$oldref;
				$dirdest = $conf->referenceletters->dir_output.'/referenceletters/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->referenceletters->dir_output.'/referenceletters/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_ACTIVATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DISABLED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->referenceletters->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->referenceletters->referenceletters_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DISABLED, $notrigger, 'REFERENCELETTERS_UNVALIDATE');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("ReferenceLetters").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/referenceletters/referenceletters_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowReferenceLetters");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('referencelettersdao'));
		$parameters = array('id'=>$this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("referenceletters@referenceletters");
			$this->labelStatus[self::STATUS_DISABLED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatus[self::STATUS_ACTIVATED] = $langs->transnoentitiesnoconv('Activated');
			$this->labelStatusShort[self::STATUS_DISABLED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_ACTIVATED] = $langs->transnoentitiesnoconv('Activated');
		}

		$statusType = 'status'.$status;

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid, date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if (!empty($obj->fk_user_creat)) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_creat);
					$this->user_creation = $cuser;
				}

				if (!empty($obj->fk_user_modif)) {
					$muser = new User($this->db);
					$muser->fetch($obj->fk_user_modif);
					$this->user_modification = $cuser;
				}


				if (!empty($obj->fk_user_valid)) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if (!empty($obj->fk_user_cloture)) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		// Set here init that are not commonf fields
		// $this->property1 = ...
		// $this->property2 = ...

		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ReferenceLettersLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_referenceletters = '.((int) $this->id)));

		if (is_numeric($result)) {
			$this->error = $objectline->error;
			$this->errors = $objectline->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
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
			$conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON = 'mod_referenceletters_standard';
		}

		if (!empty($conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON)) {
			$mybool = false;

			$file = $conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON.".php";
			$classname = $conf->global->REFERENCELETTERS_REFERENCELETTERS_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/referenceletters/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}


			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
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
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 0;

		$langs->load("referenceletters@referenceletters");

		if (!dol_strlen($modele)) {
			$modele = 'standard_referenceletters';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->REFERENCELETTERS_ADDON_PDF)) {
				$modele = $conf->global->REFERENCELETTERS_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/referenceletters/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}



	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
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

		if(!empty($this->element_type_list[$this->element_type]['trans'])) $langs->load($this->element_type_list[$this->element_type]['trans']);

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
		$docgen = new CommonDocGeneratorReferenceLetters($this->db);
		$subst_array[$langs->trans('User')] = $docgen->get_substitutionarray_user($user, $langs);
		$subst_array[$langs->trans('MenuCompanySetup')] = $docgen->get_substitutionarray_mysoc($mysoc, $langs);
		$subst_array[$langs->trans('Other')] = $docgen->get_substitutionarray_other($langs);

		complete_substitutions_array($subst_array[$langs->trans('Other')], $langs);

		foreach ( $this->element_type_list as $type => $item ) {
			if ($this->element_type == $type) {

				$langs->load($item['trans']);
				//var_dump($item);exit;
				/** @var $testObj CommonObject */
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

						foreach ($array_first_thirdparty_object as $key => $value) {
							$array_second_thirdparty_object['cust_' . $key] = $value;
						}
						$subst_array[$langs->trans($item['title'])] = $array_second_thirdparty_object;
					}else {
						dol_syslog($item['substitution_method']);
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

		//Todo  : a faire seulement sur les object agefodd

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
		,'formation_date_debut_formated' => 'Date de début de la formation mise en forme'
		,'formation_date_fin' => 'Date de fin de la formation'
		,'formation_date_fin_formated' => 'Date de fin de la formation mise en forme'
		,'objvar_object_date_text'=>'Date de la session'
		,'formation_duree' => 'Durée de la formation'
		,'formation_duree_session' => 'Durée de la session'
		,'session_nb_days' => 'Nombre de jours dans le calendrier de la session'
		,'formation_commercial'=>'commercial en charge de la formation'
		,'formation_commercial_phone'=>'téléphone commercial en charge de la formation'
		,'formation_commercial_mail'=>'email commercial en charge de la formation'
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
		,'formation_Accessibility_Handicap_label'=>'Titre Accessibilité Handicap'
		,'formation_Accessibility_Handicap'=>'Accessible aux personnes handicapés'
		,'objvar_object_trainer_text'=>'Tous les foramteurs séparés par des virgules (Nom prenom)'
		,'objvar_object_trainer_text_invert'=>'Tous les foramteurs séparés par des virgules (Prenom nom)'
		,'objvar_object_id'=>'Id de la session'
		,'objvar_object_dthour_text'=>'Tous les horaires au format texte avec retour à la ligne'
		,'objvar_object_trainer_day_cost'=>'Cout formateur (cout/nb de creneaux)'
		,'AgfMentorList'=>'Liste des référents'
		,'Mentor_administrator'=>'Référent Administratif'
		,'Mentor_pedagogique'=>'Référent pédagogique'
		,'Mentor_handicap'	=>'Référent handicap'
		,'presta_lastname'	=>$langs->trans('PrestaLastname')
		,'presta_firstname'	=>$langs->trans('PrestaFirstname')
		,'presta_soc_name'	=>$langs->trans('PrestaSocName')
		,'presta_soc_id' 	=> $langs->trans('PrestaSocId')
		,'presta_soc_name_alias'	=> $langs->trans('PrestaSocNameAlias')
		,'presta_soc_code_client'	=> $langs->trans('PrestaSocCode')
		,'presta_soc_code_fournisseur'	=> $langs->trans('PrestaSocSupplier')
		,'presta_soc_email'	=> $langs->trans('PrestaSocEmail')
		,'presta_soc_phone'	=> $langs->trans('PrestaSocPhone')
		,'presta_soc_fax'	=> $langs->trans('PrestaSocFax')
		,'presta_soc_address'	=> $langs->trans('PrestaSocAddress')
		,'presta_soc_zip'	=> $langs->trans('PrestaSocZip')
		,'presta_soc_town'	=> $langs->trans('PrestaSocTown')
		,'presta_soc_country_id'	=> $langs->trans('PrestaSocCountryId')
		,'presta_soc_country_code'	=> $langs->trans('PrestaSocCountryCode')
		,'presta_soc_idprof1'	=> $langs->trans('PrestaSocIdprof1')
		,'presta_soc_idprof2'	=> $langs->trans('PrestaSocIdprof2')
		,'presta_soc_idprof3'	=> $langs->trans('PrestaSocIdprof3')
		,'presta_soc_idprof4'	=> $langs->trans('PrestaSocIdprof4')
		,'presta_soc_idprof5'	=> $langs->trans('PrestaSocIdprof5')
		,'presta_soc_idprof6'	=> $langs->trans('PrestaSocIdprof6')
		,'presta_soc_tvaintra'	=> $langs->trans('PrestaSocTvaIntra')
		,'presta_soc_note_public'	=> $langs->trans('PrestaSocNotePublic')
		,'presta_soc_note_private'	=> $langs->trans('PrestaSocNotePrivate')
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
		,'line_stagiaire_presence_total' => 'Temps de présence total stagiare'

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
		,'line_formateur_phone'=>'Téléphone du formateur'
		,'line_formateur_mail'=>'Adresse mail du formateur'
		,'line_formateur_statut'=>'Statut du formateur (Présent, Confirmé, etc...)'
		);

		$subst_array[$langs->trans('RefLtrSubstAgefoddStagiaire')] = array(
			'objvar_object_stagiaire_civilite'=>'Civilité du stagiaire'
		,'objvar_object_stagiaire_nom'=>'Nom du stagiaire'
		,'objvar_object_stagiaire_prenom'=>'Prénom du stagiaire'
		,'objvar_object_stagiaire_mail'=>'Email du stagiaire'
		,'stagiaire_presence_total' => 'Temps de présence total',
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
			'line_multicurrency_subprice'=>'Prix unitaire devisé (format numérique)',
			'line_up_locale'=>'Prix unitaire (format prix)',
			'line_multicurrency_subprice_locale'=>'Prix unitaire devisé (format prix)',
			'line_qty'=>'Qté ligne',
			'line_discount_percent'=>'Remise ligne',
			'line_price_ht'=>'Total HT ligne (format numérique)',
			'line_multicurrency_total_ht'=>'Total HT ligne devisé (format numérique)',
			'line_price_ttc'=>'Total TTC ligne (format numérique)',
			'line_multicurrency_total_ttc'=>'Total TTC ligne devisé (format numérique)',
			'line_price_ht_locale'=>'Total HT ligne (format prix)',
			'line_multicurrency_total_ht_locale'=>'Total HT ligne devisé (format prix)',
			'line_price_ttc_locale'=>'Total TTC ligne (format prix)',
			'line_multicurrency_total_ttc_locale'=>'Total TTC ligne devisé (format prix)',
			'line_price_vat'=>'Montant TVA (format numérique)',
			'line_price_vat_locale'=>'Montant TVA (format prix)',

			// Dates
			'line_date_start'=>'Date début service',
			'line_date_start_locale'=>'Date début service format 1',
			'line_date_start_rfc'=>'Date début service format 2',
			'line_date_end'=>'Date fin service',
			'line_date_end_locale'=>'Date fin service format 1',
			'line_date_end_rfc'=>'Date fin service format 2',
		);

		$subst_array[$langs->trans('RefLtrSubstConvention')]=array(
			'objvar_object_signataire_intra'=>'Nom du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_intra_poste'=>'Poste du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_intra_mail'=>'Mail du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_intra_phone'=>'Téléphone du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_inter'=>'Nom des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_signataire_inter_poste'=>'Poste des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_signataire_inter_mail'=>'Mail des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_signataire_inter_phone'=>'Téléphone des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_convention_notes'=>'commentaire de la convention',
			'objvar_object_convention_id'=>'identifiant unique de la convention',
			'objvar_object_signataire_intra_prof1'=>'siret du signataire',
			'objvar_object_signataire_intra_prof2'=>'siren du signataire',

		);

		$subst_array[$langs->trans('RefLtrTStagiairesSessionConvention')]=array(
			'line_civilite'=>'Civilité'
		,'line_nom'=>'Nom participant'
		,'line_prenom'=>'Prénom participant'
		,'line_nom_societe'=>'Société du participant'
		,'line_poste'=>'Poste occupé au sein de sa société'
		,'line_type'=>'Type de financement'
		);

		$subst_array[$langs->trans('RefLtrTrainerLetterMissions')]=array(
			'trainer_datehourtextline'=>'Horaire(s) calendrier formateur'
		,'trainer_datetextline'=>'Date(s) calendrier formateur'
		,'formation_agenda_ics' => 'Lien ICS de l\'agenda du formateur'
		,'formation_agenda_ics_url' => 'URL du lien ICS de l\'agenda du formateur'
		);

		$subst_array[$langs->trans('RefLtrTraineeDoc')]=array(
			'stagiaire_presence_total'=> 'Nombre d heure de présence par participants'
		,'stagiaire_presence_bloc'=> 'Présentation en bloc des heures de présences participants'
		,'stagiaire_temps_realise_total'=> 'Nombre d heure des sessions au statut "Réalisé"'
		,'stagiaire_temps_att_total'=> 'Nombre d heure des sessions au statut "Annulé trop tard"'
		,'stagiaire_temps_realise_att_total'=> 'Nombre d heure des sessions au statut "Réalisé" + "Annulé trop tard"'
		,'formation_agenda_ics' => 'Lien ICS de l\'agenda des participants'
		,'formation_agenda_ics_url' => 'URL du lien ICS de l\'agenda des participants'
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

}
