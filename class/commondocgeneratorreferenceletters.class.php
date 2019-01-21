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
 * \file class/commondocgeneratorreferenceletter.class.php
 * \ingroup referenceletter
 * \brief File of parent class for documents generators
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commondocgenerator.class.php");
require_once (DOL_DOCUMENT_ROOT . "/core/lib/company.lib.php");
require_once (DOL_DOCUMENT_ROOT . "/core/lib/functions2.lib.php");

/**
 * \class CommonDocGenerator
 * \brief Parent class for documents generators
 */
class CommonDocGeneratorReferenceLetters extends CommonDocGenerator
{
	var $error = '';
	var $db;

	/**
	 *
	 * @param stdClass $referenceletters
	 * @param stdClass $outputlangs
	 * @return NULL[]
	 */
	function get_substitutionarray_refletter($referenceletters, $outputlangs) {
		return array(
				'referenceletters_title' => $referenceletters->title,
				'referenceletters_ref_int' => $referenceletters->ref_int,
				'referenceletters_title_referenceletters' => $referenceletters->title_referenceletters
		);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see CommonDocGenerator::get_substitutionarray_object()
	 */
	function get_substitutionarray_object($object, $outputlangs, $array_key = 'object') {
		global $db;
		$resarray = parent::get_substitutionarray_object($object, $outputlangs, $array_key);
		if ($object->element == 'facture' || $object->element == 'propal') {
			dol_include_once('/agefodd/class/agefodd_session_element.class.php');
			if (class_exists('Agefodd_session_element')) {
				$agf_se = new Agefodd_session_element($db);
				if ($object->element == 'facture') {
					$agf_se->fetch_element_by_id($object->id, 'invoice');
				} else {
					$agf_se->fetch_element_by_id($object->id, $object->element);
				}

				if (count($agf_se->lines) > 1) {
					$TSessions = array();
					foreach ( $agf_se->lines as $line )
						$TSessions[] = $line->fk_session_agefodd;
					$resarray['object_references'] = implode(', ', $TSessions);
				} elseif (! empty($agf_se->lines)) {
					$resarray['object_references'] = $agf_se->lines[0]->fk_session_agefodd;
				} else
					$resarray['object_references'] = '';
			} else {
				$resarray['object_references'] = '';
			}
		}
		// contact emetteur
		$arrayidcontact = $object->getIdContact('internal', 'SALESREPFOLL');
		$resarray[$array_key . '_contactsale'] = '';
		if (count($arrayidcontact) > 0) {
			foreach ( $arrayidcontact as $idsale ) {
				$object->fetch_user($idsale);
				$resarray[$array_key . '_contactsale'] .= ($resarray[$array_key . '_contactsale'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->user->getFullName($outputlangs)) . "\n";
			}
		}

		// contact tiers
		unset($arrayidcontact);
		$arrayidcontact = $object->getIdContact('external', 'CUSTOMER');

		$resarray['cust_contactclient'] = '';
		if (count($arrayidcontact) > 0) {
			foreach ( $arrayidcontact as $id ) {
				$object->fetch_contact($id);
				$resarray['cust_contactclient'] .= ($resarray['cust_contactclient'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->getFullName($outputlangs)) . "\n";
			}
		}

		// contact tiers facturation
		unset($arrayidcontact_inv);
		$arrayidcontact_inv = $object->getIdContact('external', 'BILLING');

		$resarray['cust_contactclientfact'] = '';
		if (count($arrayidcontact_inv) > 0) {
			foreach ( $arrayidcontact_inv as $id ) {
				$object->fetch_contact($id);
				$resarray['cust_contactclientfact'] .= ($resarray['cust_contactclientfact'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->getFullName($outputlangs)) . "\n";
				$resarray['cust_contactclientfacttel'] .= ($resarray['cust_contactclientfacttel'] ? "\n" : '') . $outputlangs->convToOutputCharset(!empty($object->contact->phone_pro)?$object->contact->phone_pro:(!empty($object->contact->phone_mobile)?$object->contact->phone_mobile:
				'')) . "\n";
				$resarray['cust_contactclientfactmail'] .= ($resarray['cust_contactclientfactmail'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->email) . "\n";
			}
		}

		// contact tiers livraison
		unset($arrayidcontact_inv);
		$arrayidcontact_inv = $object->getIdContact('external', 'SHIPPING');

		$resarray['cust_contactclientlivr'] = '';
		$resarray['cust_contactclientlivrtel'] = '';
		$resarray['cust_contactclientlivrmail'] = '';
		$resarray['cust_contactclientlivraddress'] = '';
		$resarray['cust_contactclientlivrzip'] = '';
		$resarray['cust_contactclientlivrtown'] = '';
		$resarray['cust_contactclientlivrcountry'] = '';
		if (count($arrayidcontact_inv) > 0) {
			foreach ( $arrayidcontact_inv as $id ) {
				$object->fetch_contact($id);
				$resarray['cust_contactclientlivr'] .= ($resarray['cust_contactclientlivr'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->getFullName($outputlangs)) . "\n";
				$resarray['cust_contactclientlivrtel'] .= ($resarray['cust_contactclientlivrtel'] ? "\n" : '') . $outputlangs->convToOutputCharset(!empty($object->contact->phone_pro)?$object->contact->phone_pro:(!empty($object->contact->phone_mobile)?$object->contact->phone_mobile:
				'')) . "\n";
				$resarray['cust_contactclientlivrmail'] .= ($resarray['cust_contactclientlivrmail'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->email) . "\n";
				$resarray['cust_contactclientlivraddress'] .= ($resarray['cust_contactclientlivraddress'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->address) . "\n";
				$resarray['cust_contactclientlivrzip'] .= ($resarray['cust_contactclientlivrzip'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->zip) . "\n";
				$resarray['cust_contactclientlivrtown'] .= ($resarray['cust_contactclientlivrtown'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->town) . "\n";
				$resarray['cust_contactclientlivrcountry'] .= ($resarray['cust_contactclientlivrcountry'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->country) . "\n";
			}
		}

		if(!empty($object->multicurrency_code)) $resarray['devise_label'] = currency_name($object->multicurrency_code);

		return $resarray;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see CommonDocGenerator::get_substitutionarray_other()
	 */
	function get_substitutionarray_other($outputlangs, $object = '') {
		global $conf;

		$outputlangs->load('main');
		$array_other = parent::get_substitutionarray_other($outputlangs);
		$array_other['current_date_fr'] = $outputlangs->trans('Day' . (( int ) date('w'))) . ' ' . date('d') . ' ' . $outputlangs->trans(date('F')) . ' ' . date('Y');
		$array_other['current_date_fr_formated'] = date('d') . ' ' . ucfirst($outputlangs->trans(date('F'))) . ' ' . date('Y');
		if (! empty($object)) {

			// TVA
			$TDetailTVA = self::get_detail_tva($object, $outputlangs);
			if (! empty($TDetailTVA)) {
				$array_other['tva_detail_titres'] = implode('<br />', $TDetailTVA['TTitres']);
				$array_other['tva_detail_montants'] = implode('<br />', $TDetailTVA['TValues']);
			}

			// Liste paiements
			if (get_class($object) === 'Facture') {

				$array_other['deja_paye'] = $array_other['somme_avoirs'] = price(0, 0, $outputlangs);
				$total_ttc = ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? $object->multicurrency_total_ttc : $object->total_ttc;
				$array_other['liste_paiements'] = self::get_liste_reglements($object, $outputlangs);
				if (! empty($array_other['liste_paiements'])) {

					$deja_regle = $object->getSommePaiement(($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? 1 : 0);
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

			// @see function pdf_getLinkedObjects() in pdf.lib.php
			$array_other['objets_lies;element=facture'] = '';
			$array_other['objets_lies;element=invoice_supplier'] = ''; // Non utilisable pour le moment
			$array_other['objets_lies;element=propal'] = '';
			$array_other['objets_lies;element=supplier_proposal'] = '';
			$array_other['objets_lies;element=commande'] = '';
			$array_other['objets_lies;element=supplier_order'] = '';
			$array_other['objets_lies;element=contrat'] = '';
			$array_other['objets_lies;element=shipping'] = '';
			if (!empty($object->linkedObjects))
			{
				foreach($object->linkedObjects as $objecttype => $objects)
				{
					$array_other['objets_lies;element='.$objecttype] = self::getLinkedObjects($object, $outputlangs, $objecttype);
				}
			}
		}
		// var_dump($array_other);exit;
		return $array_other;
	}

	/**
	 *
	 * @param stdClass $object
	 * @param stdClass $outputlangs
	 * @return string
	 */
	static function getLinkedObjects(&$object, &$outputlangs, $element=null) {
		global $linkedobjects;

		if (empty($linkedobjects))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
			$linkedobjects = pdf_getLinkedObjects($object, $outputlangs);
		}

		if (! empty($linkedobjects)) {
			$TRefToShow = array();
			foreach ( $linkedobjects as $elementtype => $linkedobject )
			{
				if ($element !== null && $elementtype != $element) continue;

				$reftoshow = $linkedobject["ref_title"] . ' : ' . $linkedobject["ref_value"];
				if (! empty($linkedobject["date_value"]))
					$reftoshow .= ' / ' . $linkedobject["date_value"];
				$TRefToShow[] = $reftoshow;
			}
		}

		if (empty($TRefToShow))
			return '';
		else
			return implode('<br />', $TRefToShow);
	}

	/**
	 *
	 * @param stdClass $object
	 * @param stdClass $outputlangs
	 * @return number|array[]|number[][]
	 */
	static function get_detail_tva(&$object, &$outputlangs) {
		global $conf;

		if (! is_array($object->lines))
			return 0;

		$TTva = array();

		$sign = 1;
		if (isset($object->type) && $object->type == 2 && ! empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE))
			$sign = - 1;

		foreach ( $object->lines as &$line ) {
			// Do not calc VAT on text or subtotal line
			if ($line->product_type != 9) {
				$vatrate = $line->tva_tx;

				// Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
				if (get_class($object) === 'Facture') {
					$prev_progress = $line->get_prev_progress($object->id);
					if ($prev_progress > 0 && ! empty($line->situation_percent)) // Compute progress from previous situation
					{
						if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1)
							$tvaligne = $sign * $line->multicurrency_total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
						else
							$tvaligne = $sign * $line->total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
					} else {
						if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1)
							$tvaligne = $sign * $line->multicurrency_total_tva;
						else
							$tvaligne = $sign * $line->total_tva;
					}
				} else {
					if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1)
						$tvaligne = $line->multicurrency_total_tva;
					else
						$tvaligne = $line->total_tva;
				}

				if ($object->remise_percent)
					$tvaligne -= ($tvaligne * $object->remise_percent) / 100;

				$TTva['Total TVA ' . round($vatrate, 2) . '%'] += $tvaligne;
			}
		}

		// formatage sortie
		foreach ( $TTva as $k => &$v )
			$v = price($v);

		// Retour fonction
		return array(
				'TTitres' => array_keys($TTva),
				'TValues' => $TTva
		);
	}

	/**
	 *
	 * @param stdClass $object
	 * @param stdClass $outputlangs
	 * @return number|array[]|number[][]
	 */
	static function get_liste_reglements(&$object, &$outputlangs) {
		global $db, $conf;

		$TPayments = array();

		// Loop on each deposits and credit notes included
		$sql = "SELECT re.rowid, re.amount_ht, re.multicurrency_amount_ht, re.amount_tva, re.multicurrency_amount_tva,  re.amount_ttc, re.multicurrency_amount_ttc,";
		$sql .= " re.description, re.fk_facture_source,";
		$sql .= " f.type, f.datef";
		$sql .= " FROM " . MAIN_DB_PREFIX . "societe_remise_except as re, " . MAIN_DB_PREFIX . "facture as f";
		$sql .= " WHERE re.fk_facture_source = f.rowid AND re.fk_facture = " . $object->id;
		$resql = $db->query($sql);
		if ($resql) {
			$invoice = new Facture($db);
			while ( $obj = $db->fetch_object($resql) ) {
				$invoice->fetch($obj->fk_facture_source);

				if ($obj->type == 2)
					$text = $outputlangs->trans("CreditNote");
				elseif ($obj->type == 3)
					$text = $outputlangs->trans("Deposit");
				else
					$text = $outputlangs->trans("UnknownType");

				$date = dol_print_date($obj->datef, 'day', false, $outputlangs, true);
				$amount = price(($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? $obj->multicurrency_amount_ttc : $obj->amount_ttc, 0, $outputlangs);
				$invoice_ref = $invoice->ref;
				$TPayments[] = array(
						$date,
						$amount,
						$text,
						$invoice->ref
				);
			}
		}

		// Loop on each payment
		$sql = "SELECT p.datep as date, p.fk_paiement, p.num_paiement as num, pf.amount as amount, pf.multicurrency_amount,";
		$sql .= " cp.code";
		$sql .= " FROM " . MAIN_DB_PREFIX . "paiement_facture as pf, " . MAIN_DB_PREFIX . "paiement as p";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_paiement as cp ON p.fk_paiement = cp.id ";
		if (( float ) DOL_VERSION > 6)
			$sql .= " AND cp.entity = " . getEntity('c_paiement'); // cp.entity apparaît en 7.0
		$sql .= " WHERE pf.fk_paiement = p.rowid AND pf.fk_facture = " . $object->id;
		$sql .= " ORDER BY p.datep";

		$resql = $db->query($sql);
		if ($resql) {
			$sign = 1;
			if ($object->type == 2 && ! empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE))
				$sign = - 1;
			while ( $row = $db->fetch_object($resql) ) {

				$date = dol_print_date($db->jdate($row->date), 'day', false, $outputlangs, true);
				$amount = price($sign * (($conf->multicurrency->enabled && $object->multicurrency_tx != 1) ? $row->multicurrency_amount : $row->amount), 0, $outputlangs);
				$oper = $outputlangs->transnoentitiesnoconv("PaymentTypeShort" . $row->code);
				$num = $row->num;

				$TPayments[] = array(
						$date,
						$amount,
						$oper,
						$num
				);
			}
		}

		if (! empty($TPayments)) {
			$res = '<font size="6">' . $outputlangs->trans('PaymentsAlreadyDone') . '<hr />';
			$res .= '<table style="font-weight:bold;"><tr><td>' . $outputlangs->trans('Payment') . '</td><td>' . $outputlangs->trans('Amount') . '</td><td>' . $outputlangs->trans('Type') . '</td><td>' . $outputlangs->trans('Num') . '</td></tr></table><hr />';
			foreach ( $TPayments as $k => $v ) {
				$res .= '<table><tr>';
				foreach ( $v as $val )
					$res .= '<td>' . $val . '</td>';
				$res .= '</tr></table>';
				$res .= '<hr />';
			}
			return $res . '</font>';
		} else
			return '';
	}

	/**
	 *
	 * @param stdClass $object
	 * @param stdClass $outputlangs
	 * @return number|array[]|number[][]
	 */
	function get_substitutionarray_lines($line, $outputlangs) {
		$resarray = parent::get_substitutionarray_lines($line, $outputlangs);
		$resarray['line_product_ref_fourn'] = $line->ref_fourn; // for supplier doc lines
		if(empty($resarray['line_product_label'])) $resarray['line_product_label'] = $line->label;
		$resarray['date_ouverture'] = dol_print_date($line->date_ouverture, 'day', 'tzuser');
		$resarray['date_ouverture_prevue'] = dol_print_date($line->date_ouverture_prevue, 'day', 'tzuser');
		$resarray['date_fin_validite'] = dol_print_date($line->date_fin_validite, 'day', 'tzuser');
		return $resarray;
	}

	/**
	 * Define array with couple substitution key => substitution value
	 *
	 * @param array $line Array of lines
	 * @param Translate $outputlangs Lang object to use for output
	 * @return array Return a substitution array
	 */
	function get_substitutionarray_lines_agefodd(&$line, $outputlangs, $fetchoptionnals = true) {
		global $db, $conf, $langs;

		require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

		// Substitutions tableau de participants :
		$resarray = array();
		$resarray['line_poste'] = $line->poste;
		$resarray['line_civilite'] = $line->civilitel;
		$resarray['line_civilite_short'] = $line->civilite;
		$resarray['line_nom'] = $line->nom;
		$resarray['line_prenom'] = $line->prenom;
		$resarray['line_type'] = $line->type;
		$resarray['line_birthday'] = dol_print_date($line->date_birth);
		$resarray['line_mail'] = $line->mail;
		$resarray['line_siret'] = $line->thirdparty->idprof2;
		$resarray['line_birthplace'] = $line->place_birth;
		$resarray['line_code_societe'] = $line->soccode;
		$resarray['line_nom_societe'] = $line->socname;

		$resarray['line_societe_address'] = $line->societe_address;
		$resarray['line_societe_zip'] = $line->societe_zip;
		$resarray['line_societe_town'] = $line->societe_town;
		$resarray['line_presence_bloc'] = '';

		// Display session stagiaire heure
		if(!empty($line->sessid) && !empty($line->stagerowid))
		{
		    dol_include_once('agefodd/class/agefodd_session_stagiaire_heures.class.php');
		    dol_include_once('agefodd/class/agefodd_session_calendrier.class.php');
		    if(class_exists('Agefoddsessionstagiaireheures') && class_exists('Agefodd_sesscalendar'))
		    {
    		    $agefoddsessionstagiaireheures = new Agefoddsessionstagiaireheures($db);
    		    $agefoddsessionstagiaireheures->fetch_all_by_session($line->sessid, $line->stagerowid);
    		    if(!empty($agefoddsessionstagiaireheures->lines)){
    		        foreach ($agefoddsessionstagiaireheures->lines as $heures)
    		        {
    		            $agefodd_sesscalendar = new Agefodd_sesscalendar($db);
    		            if($agefodd_sesscalendar->fetch($heures->fk_calendrier)>0)
    		            {
    		                if(!empty($heures->heures)){
    		                    // start by converting to seconds
    		                    $seconds = floor($heures->heures * 3600);
    		                    // we're given hours, so let's get those the easy way
    		                    $hours = floor($heures->heures);
    		                    // since we've "calculated" hours, let's remove them from the seconds variable
    		                    $seconds -= $hours * 3600;
    		                    // calculate minutes left
    		                    $minutes = floor($seconds / 60);

    		                    $resarray['line_presence_bloc'].= (!empty($resarray['line_presence_bloc'])?', ':'');
    		                    // return the time formatted HH:MM
    		                    $resarray['line_presence_bloc'].= dol_print_date($agefodd_sesscalendar->date_session, '%d/%m/%Y').'&nbsp;('.$hours."H".sprintf("%02u",$minutes).')';
    		                }
    		            }
    		        }
    		    }
		    }

		}

		// Substitutions tableau d'horaires
		$resarray['line_date_session'] = dol_print_date($line->date_session);
		$resarray['line_heure_debut_session'] = dol_print_date($line->heured, 'hour');
		$resarray['line_heure_fin_session'] = dol_print_date($line->heuref, 'hour');

		// Substitutions tableau des formateurs :
		$resarray['line_formateur_nom'] = $line->lastname;
		$resarray['line_formateur_prenom'] = $line->firstname;
		$resarray['line_formateur_mail'] = $line->email;
		$resarray['line_formateur_statut'] = $line->labelstatut[$line->trainer_status];

		// Substitutions tableau des objectif :
		$resarray['line_objpeda_rang'] = $line->priorite;
		$resarray['line_objpeda_description'] = $line->intitule;

		// Substitutions tableau des élément financier :
		$resarray['line_fin_desciption'] = str_replace('<br />', "\n", str_replace('<BR>', "\n", $line->description));
		$resarray['line_fin_desciption_light'] = $line->form_label;
		$resarray['line_fin_desciption_light_short'] = $line->form_label_short;
		$resarray['line_fin_qty'] = $line->qty;
		$resarray['line_fin_tva_tx'] = vatrate($line->tva_tx, 1);
		$resarray['line_fin_amount_ht'] = price($line->total_ht, 0, $outputlangs, 1, - 1, 2);
		$resarray['line_fin_amount_ttc'] = price($line->total_ttc, 0, $outputlangs, 1, - 1, 2);
		$resarray['line_fin_discount'] = dol_print_reduction($line->remise_percent, $outputlangs);
		$resarray['line_fin_pu_ht'] = price($line->price, 0, $outputlangs, 1, - 1, 2);

		// Retrieve extrafields
		$extrafieldkey = $line->element;
		$array_key = "line";
		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$extralabels = $extrafields->fetch_name_optionals_label($extrafieldkey, true);
		if ($fetchoptionnals) {
			$line->fetch_optionals($line->rowid, $extralabels);
		}

		if (property_exists($line, 'agefodd_stagiaire') && !empty($line->agefodd_stagiaire) && empty($line->array_options)) {
			$extrafields = new ExtraFields($this->db);
			$extralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);
			$line->array_options=$line->agefodd_stagiaire->array_options;
		}

		$resarray = $this->fill_substitutionarray_with_extrafields($line, $resarray, $extrafields, $array_key, $outputlangs);

		// Appel de la fonction parente pour les lignes des documents std dolibarr (propal, cmd, facture, contrat)
		if (get_class($line) === 'PropaleLigne' || get_class($line) === 'OrderLine' || get_class($line) === 'FactureLigne' || get_class($line) === 'ContratLigne' || get_class($line) === 'CommandeFournisseurLigne')
			$resarray = parent::get_substitutionarray_lines($line, $outputlangs);
		$resarray['line_unit'] = (method_exists($line, 'getLabelOfUnit')) ? $langs->trans($line->getLabelOfUnit('short')) : '';
		// Spé pour les contrats
		$resarray['date_ouverture'] = dol_print_date($line->date_ouverture, 'day', 'tzuser');
		$resarray['date_ouverture_prevue'] = dol_print_date($line->date_ouverture_prevue, 'day', 'tzuser');
		$resarray['date_fin_validite'] = dol_print_date($line->date_fin_validite, 'day', 'tzuser');

		return $resarray;
	}

	/**
	 *
	 * @param CommonObject $object
	 * @param LangTest $outputlangs
	 * @return string[]|NULL[]|mixed[]|array[]
	 */
	function get_substitutionsarray_agefodd(&$object, $outputlangs) {
		global $db;

		dol_include_once('/agefodd/class/html.formagefodd.class.php');

		$formAgefodd = new FormAgefodd($db);

		$resarray = array();
		$resarray['formation_nom'] = $object->formintitule;
		$resarray['formation_nom_custo'] = $object->intitule_custo;
		$resarray['formation_date_debut'] = dol_print_date($object->dated,'day','tzuser',$langs);
		$resarray['formation_date_debut_formated'] = dol_print_date($object->dated,'daytextshort','tzuser',$langs);
		$resarray['formation_date_fin'] = dol_print_date($object->datef,'day','tzuser',$langs);
		$resarray['formation_date_fin_formated'] = dol_print_date($object->datef,'daytextshort','tzuser',$langs);
		$resarray['formation_ref'] = $object->formref;
		$resarray['formation_statut'] = $object->statuslib;
		$resarray['formation_duree'] = $object->duree;
		$resarray['formation_duree_session'] = $object->duree_session;
		$resarray['formation_commercial'] = $object->commercialname;
		$resarray['formation_societe'] = $object->thirdparty->nom;
		$resarray['formation_commentaire'] = nl2br($object->notes);
		$resarray['formation_type'] = $formAgefodd->type_session_def[$object->type_session];
		$resarray['formation_nb_stagiaire'] = $object->nb_stagiaire;
		$resarray['formation_nb_stagiaire_convention'] = $object->nb_stagiaire_convention;
		$resarray['formation_stagiaire_convention'] = $object->stagiaire_convention;
		$resarray['formation_prix'] = price($object->sell_price);
		$resarray['formation_obj_peda'] = $object->formation_obj_peda;
		$resarray['session_nb_days'] = $object->session_nb_days;
		$resarray['trainer_datehourtextline'] = $object->trainer_datehourtextline;
		$resarray['trainer_datetextline'] = $object->trainer_datetextline;

		if (! empty($object->fk_formation_catalogue)) {

			dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');

			if (class_exists('Agefodd')) {
				$catalogue = new Agefodd($db);
			} elseif (class_exists('Formation')) {
				$catalogue = new Formation($db);
			}

			$catalogue->fetch($object->fk_formation_catalogue);
			$resarray['formation_but'] = strip_tags($catalogue->but);
			$resarray['formation_ref'] = strip_tags($catalogue->ref_obj);
			$resarray['formation_refint'] = strip_tags($catalogue->ref_interne);
			$resarray['formation_methode'] = strip_tags($catalogue->methode);
			$resarray['formation_prerequis'] = strip_tags($catalogue->prerequis);
			$resarray['formation_sanction'] = strip_tags($catalogue->sanction);
			$resarray['formation_type_stagiaire'] = strip_tags($catalogue->public);
			$resarray['formation_programme'] = $catalogue->programme;
			$resarray['formation_documents'] = $catalogue->note1;
			$resarray['formation_equipements'] = $catalogue->note2;

			foreach($catalogue->array_options as $key=>$val) {
				$resarray['formation_'.$key]=$val;
			}

		}

		if (! empty($object->placeid)) {
			dol_include_once('/agefodd/class/agefodd_place.class.php');
			$agf_place = new Agefodd_place($db);
			$agf_place->fetch($object->placeid);

			$resarray['formation_lieu'] = $object->placecode;
			$resarray['formation_lieu_adresse'] = strip_tags($agf_place->adresse);
			$resarray['formation_lieu_cp'] = strip_tags($agf_place->cp);
			$resarray['formation_lieu_ville'] = strip_tags($agf_place->ville);
			$resarray['formation_lieu_acces'] = $agf_place->acces_site;
			$resarray['formation_lieu_horaires'] = strip_tags($agf_place->timeschedule);
			$resarray['formation_lieu_notes'] = strip_tags($agf_place->notes);
			$resarray['formation_lieu_divers'] = $agf_place->note1;
		}

		return $resarray;
	}

	/**
	 * Define array with couple subtitution key => subtitution value
	 *
	 * @param Object $object Dolibarr Object
	 * @param Translate $outputlangs Language object for output
	 * @param boolean $recursive Want to fetch child array or child object
	 * @return array Array of substitution key->code
	 */
	function get_substitutionarray_each_var_object(&$object, $outputlangs, $recursive = true, $sub_element_label = '')
	{
		global $conf;

		$array_other = array();

		if (! empty($object)) {



			foreach ( $object as $key => $value ) {

				if ($key == 'db') continue;
				else if ($key == 'array_options' && is_object($object))
				{
					// Inspiration depuis Dolibarr ( @see CommonDocGenerator::get_substitutionarray_object() )
					// à la différence que si l'objet n'a pas de ligne extrafield en BDD, le tag {objvar_object_array_options_options_XXX} affichera vide
					// au lieu de laisser la clé, ce qui est le cas avec les clés standards Dolibarr : {object_options_XXX}
					// Retrieve extrafields
					if (substr($object->element, 0, 7) === 'agefodd') $extrafieldkey=$object->table_element;
					else $extrafieldkey=$object->element;

					require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
					$extrafields = new ExtraFields($this->db);
					$extralabels = $extrafields->fetch_name_optionals_label($extrafieldkey,true);

					foreach ($extralabels as $key_opt => $label_opt)
					{
						$array_other['object_options_'.$key_opt] =  '';
						$array_other['object_array_options_options_'.$key_opt] =  ''; // backward compatibility
						// Attention, ce test est différent d'un isset()
						if (is_array($object->array_options) && count($object->array_options)>0 && array_key_exists('options_'.$key_opt, $object->array_options))
						{
							$val = $this->showOutputFieldValue($extrafields, $key_opt, $object->array_options['options_'.$key_opt]);

							$array_other['object_options_'.$key_opt] = $val;
							$array_other['object_array_options_options_'.$key_opt] = $val;
						}
					}

					// Si les clés des extrafields ne sont pas remplacé, c'est que fetch_name_optionals_label() un poil plus haut retour vide (pas la bonne valeur passé en param)
					continue;
				}

				// Test si attribut public pour les objets pour éviter un bug sure les attributs non publics
				if (is_object($object)) {
					$reflection = new ReflectionProperty($object, $key);
					if (! $reflection->isPublic())
						continue;
				}

				if (! is_array($value) && ! is_object($value)) {
					if (is_numeric($value) && strpos($key, 'zip') === false && strpos($key, 'phone') === false && strpos($key, 'cp') === false && strpos($key, 'idprof') === false && $key !== 'id')
						$value = price($value);

					$array_other['object_' . $sub_element_label . $key] = $value;
				} elseif ($recursive && ! empty($value)) {
					$sub = strtr('object_' . $sub_element_label . $key, array(
							'object_' . $sub_element_label => ''
					)) . '_';
					$array_other = array_merge($array_other, $this->get_substitutionarray_each_var_object($value, $outputlangs, false, $sub));
				}
			}
		}

		return $array_other;
	}

	/**
	 * Override de la fonction ExtraFields::showOutputField()
	 *
	 * @param ExtraFields	$extrafields
	 * @param string		$key
	 * @param mixed			$value
	 * @param string		$moreparam
	 * @param string		$extrafieldsobjectkey
	 * @return string
	 * @throws Exception
	 */
	public function showOutputFieldValue($extrafields, $key, $value, $moreparam='', $extrafieldsobjectkey='')
	{
		global $conf,$langs;

		if (! empty($extrafieldsobjectkey))
		{
			$elementtype=$extrafields->attributes[$extrafieldsobjectkey]['elementtype'][$key];	// seems not used
			$label=$extrafields->attributes[$extrafieldsobjectkey]['label'][$key];
			$type=$extrafields->attributes[$extrafieldsobjectkey]['type'][$key];
			$size=$extrafields->attributes[$extrafieldsobjectkey]['size'][$key];
			$default=$extrafields->attributes[$extrafieldsobjectkey]['default'][$key];
			$computed=$extrafields->attributes[$extrafieldsobjectkey]['computed'][$key];
			$unique=$extrafields->attributes[$extrafieldsobjectkey]['unique'][$key];
			$required=$extrafields->attributes[$extrafieldsobjectkey]['required'][$key];
			$param=$extrafields->attributes[$extrafieldsobjectkey]['param'][$key];
			$perms=$extrafields->attributes[$extrafieldsobjectkey]['perms'][$key];
			$langfile=$extrafields->attributes[$extrafieldsobjectkey]['langfile'][$key];
			$list=$extrafields->attributes[$extrafieldsobjectkey]['list'][$key];
			$ishidden=$extrafields->attributes[$extrafieldsobjectkey]['ishidden'][$key];
			
			if( (float)DOL_VERSION < 7 ){
			    $hidden= ($ishidden == 1 ?  1 : 0);
			}
			else{
			    $hidden=(($list == 0) ? 1 : 0);		// If zero, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)
			}
			
		}
		else
		{
			$elementtype=$extrafields->attribute_elementtype[$key];	// seems not used
			$label=$extrafields->attribute_label[$key];
			$type=$extrafields->attribute_type[$key];
			$size=$extrafields->attribute_size[$key];
			$default=$extrafields->attribute_default[$key];
			$computed=$extrafields->attribute_computed[$key];
			$unique=$extrafields->attribute_unique[$key];
			$required=$extrafields->attribute_required[$key];
			$param=$extrafields->attribute_param[$key];
			$perms=$extrafields->attribute_perms[$key];
			$langfile=$extrafields->attribute_langfile[$key];
			$list=$extrafields->attribute_list[$key];
			$ishidden=$extrafields->attribute_hidden[$key];
			
			if( (float)DOL_VERSION < 7 ){
			    $hidden= ($ishidden == 1 ?  1 : 0);
			}
			else{
			    $hidden=(($list == 0) ? 1 : 0);		// If zero, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)
			}
		}

		if ($hidden) return '';		// This is a protection. If field is hidden, we should just not call this method.

		// If field is a computed field, value must become result of compute
		if ($computed)
		{
			// Make the eval of compute string
			//var_dump($computed);
			$value = dol_eval($computed, 1, 0);
		}

		$showsize=0;
		if ($type == 'date')
		{
			$showsize=10;
			$value=dol_print_date($value, 'day');
		}
		elseif ($type == 'datetime')
		{
			$showsize=19;
			$value=dol_print_date($value, 'dayhour');
		}
		elseif ($type == 'int')
		{
			$showsize=10;
		}
		elseif ($type == 'double')
		{
			if (!empty($value)) {
				$value=price($value);
			}
		}
		elseif ($type == 'boolean')
		{
			$value = yn($value, 1);
		}
		elseif ($type == 'mail')
		{
			$value=dol_print_email($value, 0, 0, 0, 64, 1, 1);
		}
		elseif ($type == 'url')
		{
			$value=dol_print_url($value,'_blank',32,1);
		}
		elseif ($type == 'phone')
		{
			$value=dol_print_phone($value, '', 0, 0, '', '&nbsp;', 1);
		}
		elseif ($type == 'price')
		{
			$value=price($value, 0, $langs, 0, 0, -1, $conf->currency);
		}
		elseif ($type == 'select')
		{
			$value=$param['options'][$value];
		}
		elseif ($type == 'sellist')
		{
			$param_list=array_keys($param['options']);
			$InfoFieldList = explode(":", $param_list[0]);

			$selectkey="rowid";
			$keyList='rowid';

			if (count($InfoFieldList)>=3)
			{
				$selectkey = $InfoFieldList[2];
				$keyList=$InfoFieldList[2].' as rowid';
			}

			$fields_label = explode('|',$InfoFieldList[1]);
			if(is_array($fields_label)) {
				$keyList .=', ';
				$keyList .= implode(', ', $fields_label);
			}

			$sql = 'SELECT '.$keyList;
			$sql.= ' FROM '.MAIN_DB_PREFIX .$InfoFieldList[0];
			if (strpos($InfoFieldList[4], 'extra')!==false)
			{
				$sql.= ' as main';
			}
			if ($selectkey=='rowid' && empty($value)) {
				$sql.= " WHERE ".$selectkey."=0";
			} elseif ($selectkey=='rowid') {
				$sql.= " WHERE ".$selectkey."=".$this->db->escape($value);
			}else {
				$sql.= " WHERE ".$selectkey."='".$this->db->escape($value)."'";
			}

			//$sql.= ' AND entity = '.$conf->entity;

			dol_syslog(get_class($this).':showOutputField:$type=sellist', LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql)
			{
				$value='';	// value was used, so now we reste it to use it to build final output

				$obj = $this->db->fetch_object($resql);

				// Several field into label (eq table:code|libelle:rowid)
				$fields_label = explode('|',$InfoFieldList[1]);

				if(is_array($fields_label) && count($fields_label)>1)
				{
					foreach ($fields_label as $field_toshow)
					{
						$translabel='';
						if (!empty($obj->$field_toshow)) {
							$translabel=$langs->trans($obj->$field_toshow);
						}
						if ($translabel!=$field_toshow) {
							$value.=dol_trunc($translabel,18).' ';
						}else {
							$value.=$obj->$field_toshow.' ';
						}
					}
				}
				else
				{
					$translabel='';
					if (!empty($obj->{$InfoFieldList[1]})) {
						$translabel=$langs->trans($obj->{$InfoFieldList[1]});
					}
					if ($translabel!=$obj->{$InfoFieldList[1]}) {
						$value=dol_trunc($translabel,18);
					}else {
						$value=$obj->{$InfoFieldList[1]};
					}
				}
			}
			else dol_syslog(get_class($this).'::showOutputField error '.$this->db->lasterror(), LOG_WARNING);
		}
		elseif ($type == 'radio')
		{
			$value=$param['options'][$value];
		}
		elseif ($type == 'checkbox')
		{
			// mise en commentaire pour afficher directement $value
//			$value_arr=explode(',',$value);
//			$value='';
//			$toprint=array();
//			if (is_array($value_arr))
//			{
//				foreach ($value_arr as $keyval=>$valueval) {
//					$toprint[]=$param['options'][$valueval];
//				}
//			}
//			$value=implode(' ', $toprint);
		}
		elseif ($type == 'chkbxlst')
		{
			$value_arr = explode(',', $value);

			$param_list = array_keys($param['options']);
			$InfoFieldList = explode(":", $param_list[0]);

			$selectkey = "rowid";
			$keyList = 'rowid';

			if (count($InfoFieldList) >= 3) {
				$selectkey = $InfoFieldList[2];
				$keyList = $InfoFieldList[2] . ' as rowid';
			}

			$fields_label = explode('|', $InfoFieldList[1]);
			if (is_array($fields_label)) {
				$keyList .= ', ';
				$keyList .= implode(', ', $fields_label);
			}

			$sql = 'SELECT ' . $keyList;
			$sql .= ' FROM ' . MAIN_DB_PREFIX . $InfoFieldList[0];
			if (strpos($InfoFieldList[4], 'extra') !== false) {
				$sql .= ' as main';
			}
			// $sql.= " WHERE ".$selectkey."='".$this->db->escape($value)."'";
			// $sql.= ' AND entity = '.$conf->entity;

			dol_syslog(get_class($this) . ':showOutputField:$type=chkbxlst',LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql) {
				$value = ''; // value was used, so now we reste it to use it to build final output
				$toprint=array();
				while ( $obj = $this->db->fetch_object($resql) ) {

					// Several field into label (eq table:code|libelle:rowid)
					$fields_label = explode('|', $InfoFieldList[1]);
					if (is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
						if (is_array($fields_label) && count($fields_label) > 1) {
							foreach ( $fields_label as $field_toshow ) {
								$translabel = '';
								if (! empty($obj->$field_toshow)) {
									$translabel = $langs->trans($obj->$field_toshow);
								}
								if ($translabel != $field_toshow) {
									$toprint[]=dol_trunc($translabel, 18);
								} else {
									$toprint[]=$obj->$field_toshow;
								}
							}
						} else {
							$translabel = '';
							if (! empty($obj->{$InfoFieldList[1]})) {
								$translabel = $langs->trans($obj->{$InfoFieldList[1]});
							}
							if ($translabel != $obj->{$InfoFieldList[1]}) {
								$toprint[]=dol_trunc($translabel, 18);
							} else {
								$toprint[]=$obj->{$InfoFieldList[1]};
							}
						}
					}
				}
				$value=implode(', ', $toprint);

			} else {
				dol_syslog(get_class($this) . '::showOutputField error ' . $this->db->lasterror(), LOG_WARNING);
			}
		}
		elseif ($type == 'link')
		{
			$out='';

			// Only if something to display (perf)
			if ($value)		// If we have -1 here, pb is into sert, not into ouptu
			{
				$param_list=array_keys($param['options']);				// $param_list='ObjectName:classPath'

				$InfoFieldList = explode(":", $param_list[0]);
				$classname=$InfoFieldList[0];
				$classpath=$InfoFieldList[1];
				if (! empty($classpath))
				{
					dol_include_once($InfoFieldList[1]);
					if ($classname && class_exists($classname))
					{
						$object = new $classname($this->db);
						$object->fetch($value);
						$value=$object->getNomUrl(3);
					}
				}
				else
				{
					dol_syslog('Error bad setup of extrafield', LOG_WARNING);
					return 'Error bad setup of extrafield';
				}
			}
		}
		elseif ($type == 'text')
		{
			$value=dol_htmlentitiesbr($value);
		}
		elseif ($type == 'password')
		{
			$value=preg_replace('/./i','*',$value);
		}
		else
		{
			$showsize=round($size);
			if ($showsize > 48) $showsize=48;
		}

		//print $type.'-'.$size;
		$out=$value;

		return $out;
	}
}

