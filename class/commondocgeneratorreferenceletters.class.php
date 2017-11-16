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
    
    function get_substitutionarray_other($outputlangs, $object='')
    {
    	global $conf;
    	
    	$outputlangs->load('main');
    	$array_other = parent::get_substitutionarray_other($outputlangs);
    	$array_other['current_date_fr'] = $outputlangs->trans('Day'.((int)date('w'))).' '.date('d').' '.$outputlangs->trans(date('F')).' '.date('Y');
    	if(!empty($object)) {
    		
    		// TVA
    		$TDetailTVA = self::get_detail_tva($object, $outputlangs);
    		if(!empty($TDetailTVA)) {
	    		$array_other['tva_detail_titres'] = implode('<br />', $TDetailTVA['TTitres']);
	    		$array_other['tva_detail_montants'] = implode('<br />', $TDetailTVA['TValues']);
    		}
    		
    		// Liste paiements
    		if(get_class($object) === 'Facture') {
	    		
    			$array_other['deja_paye']=$array_other['somme_avoirs']=price(0, 0, $outputlangs);
    			$total_ttc = ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? $object->multicurrency_total_ttc : $object->total_ttc;
	    		$array_other['liste_paiements'] = self::get_liste_reglements($object, $outputlangs);
	    		if(!empty($array_other['liste_paiements'])) {
	    			
	    			$deja_regle= $object->getSommePaiement(($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? 1 : 0);
	    			$creditnoteamount = $object->getSumCreditNotesUsed(($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? 1 : 0);
	    			$depositsamount = $object->getSumDepositsUsed(($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? 1 : 0);
	    			
	    			// Already paid + Deposits
	    			$array_other['deja_paye'] = price($deja_regle + $depositsamount, 0, $outputlangs);
	    			// Credit note
	    			$array_other['somme_avoirs'] = price($creditnoteamount, 0, $outputlangs);
	    		}
	    		
	    		// Reste à payer
	    		$resteapayer = price2num($total_ttc - $deja_regle - $creditnoteamount - $depositsamount, 'MT');
	    		$array_other['reste_a_payer'] = price($resteapayer, 0, $outputlangs);
	    		
    		}
    		
    		// Linked objects
			$array_other['objets_lies'] = self::getLinkedObjects($object, $outputlangs);
    		
    	}
    	//var_dump($array_other);exit;
    	return $array_other;
    }
    
    static function getLinkedObjects(&$object, &$outputlangs) {
    	
    	require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
    	$linkedobjects = pdf_getLinkedObjects($object,$outputlangs);
    	if (! empty($linkedobjects))
    	{
    		$TRefToShow=array();
    		foreach($linkedobjects as $linkedobject)
    		{
    			$reftoshow = $linkedobject["ref_title"].' : '.$linkedobject["ref_value"];
    			if (! empty($linkedobject["date_value"])) $reftoshow .= ' / '.$linkedobject["date_value"];
    			$TRefToShow[] = $reftoshow;
    		}
    	}
    	
    	if(empty($TRefToShow)) return '';
    	else return implode('<br />', $TRefToShow);
    	
    }
    
    static function get_detail_tva(&$object, &$outputlangs) {
    	
    	global $conf;
    	
    	if(!is_array($object->lines)) return 0;
    	
    	$TTva = array();
    	
    	$sign=1;
    	if (isset($object->type) && $object->type == 2 && ! empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE)) $sign=-1;
    	
    	foreach($object->lines as &$line) {
    		$vatrate=$line->tva_tx;
    		
    		// Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
    		if(get_class($object) === 'Facture') {
	    		$prev_progress = $line->get_prev_progress($object->id);
	    		if ($prev_progress > 0 && !empty($line->situation_percent)) // Compute progress from previous situation
	    		{
	    			if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) $tvaligne = $sign * $line->multicurrency_total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
	    			else $tvaligne = $sign * $line->total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
	    		} else {
	    			if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) $tvaligne= $sign * $line->multicurrency_total_tva;
	    			else $tvaligne= $sign * $line->total_tva;
	    		}
    		} else {
    			if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) $tvaligne=$line->multicurrency_total_tva;
    			else $tvaligne=$line->total_tva;
    		}
    		
    		if ($object->remise_percent) $tvaligne-=($tvaligne*$object->remise_percent)/100;
    		
    		if($tvaligne != 0) $TTva['Total TVA '.round($vatrate, 2).'%'] += $tvaligne;
    		
    	}
    	
    	// formatage sortie
    	foreach($TTva as $k=>&$v) $v = price($v);
    	
    	// Retour fonction
    	return array('TTitres'=>array_keys($TTva), 'TValues'=>$TTva);
    }
    
    static function get_liste_reglements(&$object, &$outputlangs) {
    	
    	global $db, $conf;
    	
    	$TPayments = array();
    	
    	// Loop on each deposits and credit notes included
    	$sql = "SELECT re.rowid, re.amount_ht, re.multicurrency_amount_ht, re.amount_tva, re.multicurrency_amount_tva,  re.amount_ttc, re.multicurrency_amount_ttc,";
    	$sql.= " re.description, re.fk_facture_source,";
    	$sql.= " f.type, f.datef";
    	$sql.= " FROM ".MAIN_DB_PREFIX ."societe_remise_except as re, ".MAIN_DB_PREFIX ."facture as f";
    	$sql.= " WHERE re.fk_facture_source = f.rowid AND re.fk_facture = ".$object->id;
    	$resql=$db->query($sql);
    	if ($resql)
    	{
    		$invoice=new Facture($db);
    		while ($obj = $db->fetch_object($resql))
    		{
    			$invoice->fetch($obj->fk_facture_source);
    			
    			if ($obj->type == 2) $text=$outputlangs->trans("CreditNote");
    			elseif ($obj->type == 3) $text=$outputlangs->trans("Deposit");
    			else $text=$outputlangs->trans("UnknownType");
    			
    			$date = dol_print_date($obj->datef,'day',false,$outputlangs,true);
    			$amount = price(($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? $obj->multicurrency_amount_ttc : $obj->amount_ttc, 0, $outputlangs);
    			$invoice_ref = $invoice->ref;
    			$TPayments[] = array($date, $amount, $text, $invoice->ref);
    		}
    	}
    	
    	// Loop on each payment
    	$sql = "SELECT p.datep as date, p.fk_paiement, p.num_paiement as num, pf.amount as amount, pf.multicurrency_amount,";
    	$sql.= " cp.code";
    	$sql.= " FROM ".MAIN_DB_PREFIX."paiement_facture as pf, ".MAIN_DB_PREFIX."paiement as p";
    	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_paiement as cp ON p.fk_paiement = cp.id AND cp.entity = " . getEntity('c_paiement');
    	$sql.= " WHERE pf.fk_paiement = p.rowid AND pf.fk_facture = ".$object->id;
    	$sql.= " ORDER BY p.datep";
    	
    	$resql=$db->query($sql);
    	if ($resql)
    	{
    		$sign=1;
    		if ($object->type == 2 && ! empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE)) $sign=-1;
    		while ($row = $db->fetch_object($resql)) {
    			
    			$date = dol_print_date($db->jdate($row->date),'day',false,$outputlangs,true);
    			$amount = price($sign * (($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? $row->multicurrency_amount : $row->amount), 0, $outputlangs);
    			$oper = $outputlangs->transnoentitiesnoconv("PaymentTypeShort" . $row->code);
    			$num = $row->num;
    			
    			$TPayments[] = array($date, $amount, $oper, $num);
    		}
    	}
    	
    	if(!empty($TPayments)) {
    		$res = '<font size="6">'.$outputlangs->trans('PaymentsAlreadyDone').'<hr />';
    		$res.= '<table style="font-weight:bold;"><tr><td>'.$outputlangs->trans('Payment')
    					.'</td><td>'.$outputlangs->trans('Amount')
    					.'</td><td>'.$outputlangs->trans('Type')
    					.'</td><td>'.$outputlangs->trans('Num').'</td></tr></table><hr />';
    		foreach($TPayments as $k=>$v) {
    			$res.= '<table><tr>';
    			foreach($v as $val) $res.= '<td>'.$val.'</td>';
    			$res.= '</tr></table>';
    			$res.= '<hr />';
    		}
    		return $res.'</font>';
    	} else return '';
    	
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
    			
    			if (! is_array($value) && ! is_object($value)) {
    				if(is_numeric($value) && strpos($key, 'zip') === false && strpos($key, 'phone') === false) $value = price($value);
    				$array_other['object_' . $sub_element_label . $key] = $value;
    			}
    			elseif ($recursive && !empty($value)) {
    				$sub = strtr('object_'.$sub_element_label.$key, array('object_'.$sub_element_label=>'')).'_';
    				$array_other = array_merge($array_other, $this->get_substitutionarray_each_var_object($value, $outputlangs, false, $sub));
    			}
    		}
    	}
    	
    	return $array_other;
    }
    
}