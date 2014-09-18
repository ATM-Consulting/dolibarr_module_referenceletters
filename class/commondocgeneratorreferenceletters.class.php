<?php
/* Reference Letters
 * Copyright (C) 2014 Florian HENRY <florian.henry@open-concept.pro>
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
 * or see http://www.gnu.org/
 */

/**
 *	    \file       class/commondocgeneratorreferenceletter.class.php
 *		\ingroup    referenceletter
 *		\brief      File of parent class for documents generators
 */

require_once (DOL_DOCUMENT_ROOT . "/core/class/commondocgenerator.class.php");

/**
 *	\class      CommonDocGenerator
 *	\brief      Parent class for documents generators
 */
class CommonDocGeneratorReferenceLetters extends CommonDocGenerator
{
	var $error='';


    /**
     * Define array with couple subtitution key => subtitution value
     *
     * @param   User		$contact        contact
     * @param   Translate	$outputlangs    Language object for output
     * @return	array						Array of substitution key->code
     */
    function get_substitutionarray_contact($contact,$outputlangs)
    {
        global $conf,$langs,$db;
        
        $langs->load("dict");
        
        $code=(! empty($contact->civilite_id)?$contact->civilite_id:(! empty($contact->civility_id)?$contact->civility_id:''));
        $civility=$langs->getLabelFromKey($db, "Civility".$code, "c_civilite", "code", "civilite", $code);
        
        return array(
            'contact_lastname'=>$contact->lastname,
            'contact_firstname'=>$contact->firstname,
            'contact_phone_pro'=>$contact->phone_pro,
            'contact_phone_perso'=>$contact->phone_perso,
            'contact_phone_mobile'=>$contact->phone_mobile,
       		'contact_address'=>$contact->address,
       		'contact_zip'=>$contact->zip,
       		'contact_town'=>$contact->town,
       		'contact_country'=>$contact->country,
        	'contact_country_code'=>$contact->country_code,
       		'contact_state'=>$contact->state,
        	'contact_state_code'=>$contact->state_code,
        	'contact_fax'=>$contact->fax,
            'contact_email'=>$contact->email,
        	'contact_job'=>$contact->poste,
        	'contact_civility_id'=>$contact->civilite_id,
        	'contact_civility'=>$civility
        );
    }
}

?>
