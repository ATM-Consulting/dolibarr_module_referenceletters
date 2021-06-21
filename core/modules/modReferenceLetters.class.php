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
 * \defgroup referenceletters ReferenceLetters module
 * \brief ReferenceLetters module descriptor.
 * \file core/modules/modReferenceLetters.class.php
 * \ingroup referenceletters
 * \brief Description and activation file for module ReferenceLetters
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module ReferenceLetters
 */
class modReferenceLetters extends DolibarrModules
{

	/**
	 * Constructor.
	 * Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db
	 */
	function __construct($db) {
		global $langs, $conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use a free id here
		// (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 103258;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'referenceletters';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = 'ATM Consulting – Autres';
		// Module label (no space allowed)
		// used if translation string 'ModuleXXXName' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description
		// used if translation string 'ModuleXXXDesc' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->description = "Description of module ReferenceLetters";
		// Possible values for version are: 'development', 'experimental' or version

		$this->version = '2.9.2';

		// Key used in llx_const table to save module status enabled/disabled
		// (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page
		// (0=common,1=interface,2=others,3=very specific)
		$this->special = 2;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png
		// use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png
		// use this->picto='pictovalue@module'
		$this->picto = 'referenceletters@referenceletters'; // mypicto@referenceletters
		                                                    // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		                                                    // for default path (eg: /referenceletters/core/xxxxx) (0=disable, 1=enable)
		                                                    // for specific path of parts (eg: /referenceletters/core/modules/barcode)
		                                                    // for specific css file (eg: /referenceletters/css/referenceletters.css.php)

		$this->editor_name = 'ATM Consulting';
		$this->editor_url = 'https://www.atm-consulting.fr';

		$this->module_parts = array (
				'models' => 1,
				'hooks' => array (
						'pdfgeneration'
						,'formfile'
						,'propalcard'
						,'ordercard'
						,'invoicecard'
						,'contractcard'
						,'supplier_proposalcard'
						,'ordersuppliercard'
						,'expeditioncard'
				)
		);
		// Set this to 1 if module has its own trigger directory
		// 'triggers' => 1,
		// Set this to 1 if module has its own login method directory
		// 'login' => 0,
		// Set this to 1 if module has its own substitution function file
		// 'substitutions' => 0,
		// Set this to 1 if module has its own menus handler directory
		// 'menus' => 0,
		// Set this to 1 if module has its own barcode directory
		// 'barcode' => 0,
		// Set this to 1 if module has its own models directory
		// 'models' => 0,
		// Set this to relative path of css if module has its own css file
		// 'css' => '/referenceletters/css/mycss.css.php',
		// Set here all hooks context managed by module
		// 'hooks' => array('hookcontext1','hookcontext2')
		// Set here all workflow context managed by module
		// 'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE'))

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/referenceletters/temp");
		$this->dirs = array (
				'/referenceletters',
				'/referenceletters/contract',
				'/referenceletters/contact',
				'/referenceletters/thirdparty',
				'/referenceletters/propal',
				'/referenceletters/invoice',
				'/referenceletters/order',
				'/referenceletters/supplier_proposal',
				'/referenceletters/order_supplier',
				'/referenceletters/expedition',
				'/referenceletters/referenceletters',
				'/referenceletters/shipping',
		);

		// Config pages. Put here list of php pages
		// stored into referenceletters/admin directory, used to setup module.
		$this->config_page_url = array (
				"admin_referenceletters.php@referenceletters"
		);

		// Dependencies
		// List of modules id that must be enabled if this module is enabled
		$this->depends = array ('modFckeditor');
		// List of modules id to disable if this one is disabled
		$this->requiredby = array ();
		// Minimum version of PHP required by module
		$this->phpmin = array (
				5,
				2
		);
		// Minimum version of Dolibarr required by module
		$this->need_dolibarr_version = array (
				4,
				0
		);
		$this->langfiles = array (
				"referenceletters@referenceletters"
		); // langfiles@referenceletters
		   // Constants
		   // List of particular constants to add when module is enabled
		   // (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		   // Example:
		$this->const[] = array (
				'REF_LETTER_ADDON',
				'chaine',
				'mod_referenceletters_simple',
				'Use simple mask for reference letters ref',
				0,
				'current',
				1
		);
		$this->const[] = array (
				'REF_LETTER_UNIVERSAL_MASK',
				'chaine',
				'',
				'Mask of reference letters reference',
				0,
				'current',
				1
		);
		$this->const[] = array (
				'FCKEDITOR_ENABLE_SOCIETE',
				'yesno',
				'1',
				'Enabled WYSYWYG on modules',
				0,
				'current',
				1
		);
		$this->const[] = array (
				'REF_LETTER_CREATEEVENT',
				'yesno',
				'1',
				'CreateEvent',
				0,
				'current',
				1
		);
		$this->const[] = array (
				'REF_LETTER_EVTCOPYFILE',
				'yesno',
				'1',
				'copy file event',
				0,
				'current',
				1
		);
		$this->const[] = array (
				'REF_LETTER_TYPEEVENTNAME',
				'chaine',
				'other',
				'Event name like <Letter Ref.  {ref_int}>(normal) or like <{model title} - {customer name}>(other)',
				0,
				'current',
				1
		);
		$this->const[] = array (
				'REF_LETTER_OUTPUTREFLET',
				'yesno',
				'1',
				'Output document ref',
				0,
				'current',
				1
		);

		// Array to add new pages in new tabs
		// Example:
		// $this->tabs = array();
		// // To add a new tab identified by code tabname1
		// 'objecttype:+tabname1:Title1:langfile@referenceletters:$user->rights->referenceletters->read:/referenceletters/mynewtab1.php?id=__ID__',
		// // To add another new tab identified by code tabname2
		// 'objecttype:+tabname2:Title2:langfile@referenceletters:$user->rights->othermodule->read:/referenceletters/mynewtab2.php?id=__ID__',
		// // To remove an existing tab identified by code tabname
		// 'objecttype:-tabname'

		$this->tabs = array (
				'contract:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=contract',
				'thirdparty:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=thirdparty',
				'contact:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=contact',
				'propal:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=propal',
				'invoice:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=invoice',
				'order:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=order',
				'supplier_proposal:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=supplier_proposal',
				'supplier_order:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=order_supplier',
				//'delivery:+tabReferenceLetters:RefLtrLetters:referenceletters@referenceletters:$user->rights->referenceletters->use:/referenceletters/referenceletters/instance.php?id=__ID__&element_type=expedition'
		);

		// where objecttype can be
		// 'thirdparty' to add a tab in third party view
		// 'intervention' to add a tab in intervention view
		// 'order_supplier' to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice' to add a tab in customer invoice view
		// 'order' to add a tab in customer order view
		// 'product' to add a tab in product view
		// 'stock' to add a tab in stock view
		// 'propal' to add a tab in propal view
		// 'member' to add a tab in fundation member view
		// 'contract' to add a tab in contract view
		// 'user' to add a tab in user view
		// 'group' to add a tab in group view
		// 'contact' to add a tab in contact view
		// 'categories_x' to add a tab in category view
		// (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// Dictionnaries
		if (! isset($conf->referenceletters->enabled)) {
			$conf->referenceletters = ( object ) array ();
			$conf->referenceletters->enabled = 0;
		}
		$this->dictionnaries = array ();


		// Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		$this->boxes = array (); // Boxes list
		$r = 0;
		$this->boxes[$r][1] = "box_referenceletter_models@referenceletters";
		$r ++;
		$this->boxes[$r][1] = "box_referenceletter_elements@referenceletters";
		$r ++;
		$this->boxes[$r][1] = "box_referenceletter_models_archive@referenceletters";
		// $r ++;
		/*
		 $this->boxes[$r][1] = "myboxb.php";
		 $r++;
		 */

		// Permissions
		$this->rights = array (); // Permission array used by this module
		$r = 0;

		// Add here list of permission defined by
		// an id, a label, a boolean and two constant strings.
		// Example:
		// // Permission id (must not be already used)
		$this->rights = array ();
		$r = 0;

		$this->rights[$r][0] = 1032581;
		$this->rights[$r][1] = 'See models letters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';
		$r ++;

		$this->rights[$r][0] = 1032582;
		$this->rights[$r][1] = 'Modify models letters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'write';
		$r ++;

		$this->rights[$r][0] = 1032583;
		$this->rights[$r][1] = 'Delete models letters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'delete';
		$r ++;

		$this->rights[$r][0] = 1032584;
		$this->rights[$r][1] = 'Use models letters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'use';
		$r ++;

		// Main menu entries
		$this->menus = array (); // List of menus to add
		$r = 0;
		$this->menu[$r] = array (
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'Module103258Name',
				'mainmenu' => 'referenceletters',
				'leftmenu' => '0',
				'url' => '/referenceletters/index.php',
				'langs' => 'referenceletters@referenceletters',
				'position' => 100,
				'enabled' => '$user->rights->referenceletters->read',
				'perms' => '$user->rights->referenceletters->read',
				'target' => '',
				'user' => 0
		);

		$r ++;
		$this->menu[$r] = array (
				'fk_menu' => 'fk_mainmenu=referenceletters',
				'type' => 'left',
				'titre' => 'Module103258Name',
				'leftmenu' => 'refletterlist',
				'url' => '/referenceletters/referenceletters/list.php',
				'langs' => 'referenceletters@referenceletters',
				'position' => 101,
				'enabled' => '$user->rights->referenceletters->read',
				'perms' => '$user->rights->referenceletters->read',
				'target' => '',
				'user' => 0
		);

		$r ++;
		$this->menu[$r] = array (
				'fk_menu' => 'fk_mainmenu=referenceletters,fk_leftmenu=refletterlist',
				'type' => 'left',
				'titre' => 'RefLtrList',
				'mainmenu' => 'referenceletters',
				'url' => '/referenceletters/referenceletters/list.php',
				'langs' => 'referenceletters@referenceletters',
				'position' => 102,
				'enabled' => '$user->rights->referenceletters->read',
				'perms' => '$user->rights->referenceletters->read',
				'target' => '',
				'user' => 0
		);

		$r ++;
		$this->menu[$r] = array (
				'fk_menu' => 'fk_mainmenu=referenceletters,fk_leftmenu=refletterlist',
				'type' => 'left',
				'titre' => 'RefLtrNew',
				'mainmenu' => 'referenceletters',
				'url' => '/referenceletters/referenceletters/card.php?action=create',
				'langs' => 'referenceletters@referenceletters',
				'position' => 103,
				'enabled' => '$user->rights->referenceletters->write',
				'perms' => '$user->rights->referenceletters->write',
				'target' => '',
				'user' => 0
		);

		$r ++;
		$this->menu[$r] = array (
				'fk_menu' => 'fk_mainmenu=referenceletters,fk_leftmenu=refletterlist',
				'type' => 'left',
				'titre' => 'RefLtrListInstance',
				'mainmenu' => 'referenceletters',
				'url' => '/referenceletters/referenceletters/list_instance.php',
				'langs' => 'referenceletters@referenceletters',
				'position' => 104,
				'enabled' => '$user->rights->referenceletters->read',
				'perms' => '$user->rights->referenceletters->read',
				'target' => '',
				'user' => 0
		);

		$r ++;
		$this->menu[$r] = array (
				'fk_menu' => 'fk_mainmenu=referenceletters,fk_leftmenu=refletterlist',
				'type' => 'left',
				'titre' => 'RefLtrListMassGen',
				'mainmenu' => 'referenceletters',
				'url' => '/referenceletters/referenceletters/mass_gen.php',
				'langs' => 'referenceletters@referenceletters',
				'position' => 104,
				'enabled' => '$user->rights->referenceletters->write',
				'perms' => '$user->rights->referenceletters->write',
				'target' => '',
				'user' => 0
		);
	}

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options enabling module ('', 'noboxes')
	 * @return int if OK, 0 if KO
	 */
	function init($options = '') {
	    global $db, $conf;

		$sql = array ();

		$result = $this->load_tables();

		define('INC_FROM_DOLIBARR', true);

		$ext = new ExtraFields($db);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'facture', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'thirdparty', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'propal', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'contrat', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'socpeople', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'commande', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'commande_fournisseur', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'supplier_proposal', 0, 0, '', '', 1, '', 0, 1);
		$ext->addExtraField('rfltr_model_id', 'model doc edit', 'int', 0, 10, 'expedition', 0, 0, '', '', 1, '', 0, 1);

		$reinstalltemplate=false;
		dol_include_once('/referenceletters/script/create-maj-base.php');
		if (empty($conf->global->REF_LETTER_MIGRATED))
		{
		    dolibarr_set_const($db, "REF_LETTER_MIGRATED", '1', 'chaine', 0, '', $conf->entity);
		    dol_include_once('/referenceletters/script/migrate_model_to_extrafields.php');
		}

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options enabling module ('', 'noboxes')
	 * @return int if OK, 0 if KO
	 */
	function remove($options = '') {
		$sql = array ();

		return $this->_remove($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /referenceletters/sql/
	 * This function is called by this->init
	 *
	 * @return int if KO, >0 if OK
	 */
	function load_tables() {
		return $this->_load_tables('/referenceletters/sql/');
	}
}
