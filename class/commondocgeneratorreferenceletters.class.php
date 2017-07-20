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
    
    /**
     *	Define array with couple substitution key => substitution value
     *
     *	@param  array			$line				Array of lines
     *	@param  Translate		$outputlangs        Lang object to use for output
     *  @return	array								Return a substitution array
     */
    function get_substitutionarray_lines_agefodd($line,$outputlangs,$fetchoptionnals=true)
    {
    	global $conf;
    	
    	// Substitutions tableau de participants :
    	$resarray=array();
    	$resarray['line_poste'] = $line->poste;
    	$resarray['line_civilite'] = $line->civilitel;
    	$resarray['line_civilite_short'] = $line->civilite;
    	$resarray['line_nom'] = $line->nom;
    	$resarray['line_prenom'] = $line->prenom;
    	
    	/*$resarray= array(
    			'line_fulldesc'=>doc_getlinedesc($line,$outputlangs),
    			'line_product_ref'=>$line->product_ref,
    			'line_product_label'=>$line->product_label,
    			'line_product_type'=>$line->product_type,
    			'line_desc'=>$line->desc,
    			'line_vatrate'=>vatrate($line->tva_tx,true,$line->info_bits),
    			'line_up'=>price2num($line->subprice),
    			'line_up_locale'=>price($line->subprice, 0, $outputlangs),
    			'line_qty'=>$line->qty,
    			'line_discount_percent'=>($line->remise_percent?$line->remise_percent.'%':''),
    			'line_price_ht'=>price2num($line->total_ht),
    			'line_price_ttc'=>price2num($line->total_ttc),
    			'line_price_vat'=>price2num($line->total_tva),
    			'line_price_ht_locale'=>price($line->total_ht, 0, $outputlangs),
    			'line_price_ttc_locale'=>price($line->total_ttc, 0, $outputlangs),
    			'line_price_vat_locale'=>price($line->total_tva, 0, $outputlangs),
    			// Dates
    			'line_date_start'=>dol_print_date($line->date_start, 'day', 'tzuser'),
    			'line_date_start_locale'=>dol_print_date($line->date_start, 'day', 'tzuser', $outputlangs),
    			'line_date_start_rfc'=>dol_print_date($line->date_start, 'dayrfc', 'tzuser'),
    			'line_date_end'=>dol_print_date($line->date_end, 'day', 'tzuser'),
    			'line_date_end_locale'=>dol_print_date($line->date_end, 'day', 'tzuser', $outputlangs),
    			'line_date_end_rfc'=>dol_print_date($line->date_end, 'dayrfc', 'tzuser'),
    	);*/
    	
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
    
}