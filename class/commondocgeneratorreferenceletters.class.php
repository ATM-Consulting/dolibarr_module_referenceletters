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
    
}