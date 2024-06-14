<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
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
 * 	\file		core/triggers/interface_99_modReferenceLetters_ReferenceLetterstrigger.class.php
 * 	\ingroup	referenceletters
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_99_modReferenceLetters_ReferenceLetterstrigger.class.php
 * 					interface_99_modReferenceLetters_ReferenceLetterstrigger.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceMytrigger
 * 				- The constructor method must be named InterfaceMytrigger
 * 				- The name property name must be Mytrigger
 */

/**
 * Trigger class
 */
class InterfaceReferenceLetterstrigger
{

    private $db;

    /**
     * Constructor
     *
     * 	@param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'referenceletters@referenceletters';
    }

    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') {
            return $langs->trans("Development");
        } elseif ($this->version == 'experimental')

                return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else {
            return $langs->trans("Unknown");
        }
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
		dol_include_once('/referenceletters/class/referenceletters.class.php');

		// Objects having extrafields for doc generation
		$TTriggerObjects = array(
			'BILL_CREATE',
			'COMPANY_CREATE',
			'CONTACT_CREATE',
			'CONTRACT_CREATE',
			'PROPAL_CREATE',
			'ORDER_CREATE',
			'SHIPPING_CREATE',
			'FICHINTER_CREATE',
			'ORDER_SUPPLIER_CREATE',
			'PROPOSAL_SUPPLIER_CREATE'
		);

		if (in_array($action, $TTriggerObjects)){
			
			$refletter = new Referenceletters($this->db);
			$refletter->lines = array();
			
			$element = $object->element;
			if($element === 'facture') $element = 'invoice';
			if($element === 'commande') $element = 'order';
			if($element === 'contrat') $element = 'contract';
			
			$TFilters = array('t.element_type' => $element, 't.status' => 1, 't.default_doc' => 1);
			$result = $refletter->fetch_all('ASC', 't.rowid', 1, 0, $TFilters);
			
			if ($result < 0)
			{
				setEventMessages(null, $refletter->errors, 'errors');
				return -1;
			}
			
			if (empty($refletter->lines) || empty($result)) return 0;
			
			$id_model = $refletter->lines[0]->id;
			
			$object->array_options['options_rfltr_model_id'] = intval($id_model);
			$object->insertExtraFields();
		}
		
		return 0;
    }
}