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
require_once (DOL_DOCUMENT_ROOT . "/core/lib/company.lib.php");

/**
 *	\class      CommonDocGenerator
 *	\brief      Parent class for documents generators
 */
class CommonDocGeneratorReferenceLetters extends CommonDocGenerator
{
	var $error='';
	var $db;

    /**
     *
     * @param unknown $referenceletters
     * @param unknown $outputlangs
     * @return NULL[]
     */
    function get_substitutionarray_refletter($referenceletters,$outputlangs)
    {

    	return array(
    			'referenceletters_title'=>$referenceletters->title,
    			'referenceletters_ref_int'=>$referenceletters->ref_int,
    			'referenceletters_title_referenceletters'=>$referenceletters->title_referenceletters,
    	);
    }
    
    function get_substitutionarray_other($outputlangs)
    {
    	global $conf;
    	
    	$outputlangs->load('main');
    	
    	$array_other = parent::get_substitutionarray_other($outputlangs);
    	$array_other['current_date_fr'] = $outputlangs->trans('Day'.((int)date('w'))).' '.date('d').' '.$outputlangs->trans(date('F')).' '.date('Y');
    	
    	return $array_other;
    }
    
    /**
     *	Define array with couple substitution key => substitution value
     *
     *	@param  array			$line				Array of lines
     *	@param  Translate		$outputlangs        Lang object to use for output
     *  @return	array								Return a substitution array
     */
    function get_substitutionarray_lines_agefodd(&$line,$outputlangs,$fetchoptionnals=true)
    {
    	global $db, $conf;
    	
    	// Substitutions tableau de participants :
    	$resarray=array();
    	$resarray['line_poste'] = $line->poste;
    	$resarray['line_civilite'] = $line->civilitel;
    	$resarray['line_civilite_short'] = $line->civilite;
    	$resarray['line_nom'] = $line->nom;
    	$resarray['line_prenom'] = $line->prenom;
    	$resarray['line_type'] = $line->type;
    	$resarray['line_nom_societe'] = $line->soccode;
    	
    	// Substitutions tableau d'horaires
    	$resarray['line_date_session'] = date('d/m/Y', $line->date_session);
    	$resarray['line_heure_debut_session'] = date('H:i:s', $line->heured);
    	$resarray['line_heure_fin_session'] = date('H:i:s', $line->heuref);
    	
    	// Substitutions tableau des formateurs :
    	$resarray['line_formateur_nom'] = $line->lastname;
    	$resarray['line_formateur_prenom'] = $line->firstname;
    	$resarray['line_formateur_mail'] = $line->email;
    	$resarray['line_formateur_statut'] = $line->labelstatut[$line->trainer_status];
    	
    	
    	// Retrieve extrafields
    	$extrafieldkey=$line->element;
    	$array_key="line";
    	require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
    	$extrafields = new ExtraFields($this->db);
    	$extralabels = $extrafields->fetch_name_optionals_label($extrafieldkey,true);
    	if ($fetchoptionnals) $line->fetch_optionals($line->rowid,$extralabels);
    	
    	$resarray = $this->fill_substitutionarray_with_extrafields($line,$resarray,$extrafields,$array_key=$array_key,$outputlangs);
    	
    	return $resarray;
    }
    
    function get_substitutionsarray_agefodd(&$object, $outputlangs) {
    	
    	dol_include_once('/agefodd/class/html.formagefodd.class.php');
    	
    	$formAgefodd = new FormAgefodd($db);
    	
    	$resarray=array();
    	$resarray['formation_nom'] = $object->formintitule;
    	$resarray['formation_date_debut'] = date('d/m/Y', $object->dated);
    	$resarray['formation_date_fin'] = date('d/m/Y', $object->datef);
    	$resarray['formation_ref'] = $object->formref;
    	$resarray['formation_statut'] = $object->statuslib;
    	$resarray['formation_lieu'] = $object->placecode;
    	$resarray['formation_duree'] = $object->duree;
    	$resarray['formation_commercial'] = $object->commercialname;
    	$resarray['formation_societe'] = $object->thirdparty->nom;
    	$resarray['formation_commentaire'] = nl2br($object->notes);
    	$resarray['formation_type'] = $formAgefodd->type_session_def[$object->type_session];
    	
    	return $resarray;
    	
    }

    /**
     * Define array with couple subtitution key => subtitution value
     *
     * @param   Object	$object    	Dolibarr Object
     * @param   Translate $outputlangs    Language object for output
     * @param   boolean $recursive    	Want to fetch child array or child object
     * @return	array	Array of substitution key->code
     */
    function get_substitutionarray_each_var_object(&$object,$outputlangs,$recursive=true,$sub_element_label='') {
    	
    	$array_other = array();
    	
    	if(!empty($object)) {
    		
    		foreach($object as $key => $value) {
    			
    			// Test si attribut public pour les objets pour éviter un bug sure les attributs non publics
    			if(is_object($object)) {
    				$reflection = new ReflectionProperty($object, $key);
    				if(!$reflection->isPublic()) continue;
    			}
    			
    			if (! is_array($value) && ! is_object($value)) $array_other['object_' . $sub_element_label . $key] = $value;
    			elseif ($recursive && !empty($value)) {
    				$sub = strtr('object_'.$sub_element_label.$key, array('object_'.$sub_element_label=>'')).'_';
    				$array_other = array_merge($array_other, $this->get_substitutionarray_each_var_object($value, $outputlangs, false, $sub));
    			}
    		}
    	}
    	
    	return $array_other;
    }

    
}