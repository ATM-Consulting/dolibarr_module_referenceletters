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
	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

    /**
     * @var string[]    Array of error strings
     */
    public $errors = array();

	/**
	 *
	 * @param ReferenceLetters $referenceletters reference letter
	 * @param Translate $outputlangs Translate instance
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
	public function get_substitutionarray_object($object, $outputlangs, $array_key = 'object')
	{
		global $db;
		$resarray = parent::get_substitutionarray_object($object, $outputlangs, $array_key);
		$resarray['object_date_validation_no_hour'] = (!empty($object->date_validation) ?dol_print_date($object->date_validation, 'day') : '');
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
					foreach ($agf_se->lines as $line)
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
		if (!empty($arrayidcontact)) {
			foreach ($arrayidcontact as $idsale) {
				$object->fetch_user($idsale);
				$resarray[$array_key . '_contactsale'] .= ($resarray[$array_key . '_contactsale'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->user->getFullName($outputlangs, 1)) . "\n";
			}
		}

		// contact tiers
		unset($arrayidcontact);
		$arrayidcontact = $object->getIdContact('external', 'CUSTOMER');

		$resarray['cust_contactclient'] = '';
		if (!empty($arrayidcontact)) {
			foreach ($arrayidcontact as $id) {
				$object->fetch_contact($id);
				$resarray['cust_contactclient'] .= ($resarray['cust_contactclient'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->getFullName($outputlangs, 1)) . "\n";
			}
		}

		// contact tiers facturation
		unset($arrayidcontact_inv);
		$arrayidcontact_inv = $object->getIdContact('external', 'BILLING');

		$resarray['cust_contactclientfact'] = '';
		$resarray['cust_contactclientfacttel'] = '';
		$resarray['cust_contactclientfactmail'] = '';
		if (!empty($arrayidcontact_inv)) {
			foreach ($arrayidcontact_inv as $id) {
				$object->fetch_contact($id);
				$resarray['cust_contactclientfact'] .= ($resarray['cust_contactclientfact'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->getFullName($outputlangs, 1)) . "\n";
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
		if (!empty($arrayidcontact_inv)) {
			foreach ($arrayidcontact_inv as $id) {
				$object->fetch_contact($id);
				$resarray['cust_contactclientlivr'] .= ($resarray['cust_contactclientlivr'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->getFullName($outputlangs, 1)) . "\n";
				$resarray['cust_contactclientlivrtel'] .= ($resarray['cust_contactclientlivrtel'] ? "\n" : '') . $outputlangs->convToOutputCharset(!empty($object->contact->phone_pro)?$object->contact->phone_pro:(!empty($object->contact->phone_mobile)?$object->contact->phone_mobile:
				'')) . "\n";
				$resarray['cust_contactclientlivrmail'] .= ($resarray['cust_contactclientlivrmail'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->email) . "\n";
				$resarray['cust_contactclientlivraddress'] .= ($resarray['cust_contactclientlivraddress'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->address) . "\n";
				$resarray['cust_contactclientlivrzip'] .= ($resarray['cust_contactclientlivrzip'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->zip) . "\n";
				$resarray['cust_contactclientlivrtown'] .= ($resarray['cust_contactclientlivrtown'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->town) . "\n";
				$resarray['cust_contactclientlivrcountry'] .= ($resarray['cust_contactclientlivrcountry'] ? "\n" : '') . $outputlangs->convToOutputCharset($object->contact->country) . "\n";
			}
		}

		// Contacts sélectionnés
		require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';

        if(!empty($object->id)) $linkedContacts = $object->liste_contact(); // External par défaut
        $TCounts = array();

        if(!empty($object->id)) $TContactTypes = $object->liste_type_contact('external', 'position', 1);
        $atLeastOneContact = false;

		$contactKey = 'cust_contactclient_';
		if(!empty($linkedContacts)) {
	        foreach ($linkedContacts as $TContactRef)
	        {
	            $code = $TContactRef['code']; // Code
	            if(empty($TCounts[$code])) // Index
	            {
	                $TCounts[$code] = 1; // On commence à 1 parce que l'utilisateur n'est pas formé aux tableaux zero-indexed :o)
	            }

        	    $object->fetch_contact($TContactRef['id']);

		        $contactPrefix = $contactKey . $code . '_' . $TCounts[$code];
	            if(!empty($object->contact->id)) $contactarray = parent::get_substitutionarray_contact($object->contact, $outputlangs, $contactPrefix);
	            $resarray = array_merge($resarray, $contactarray);

	            $atLeastOneContact = true;

        	    $TCounts[$code]++;
	        }
		}

        // Types de contacts non sélectionnés mais disponibles
        $i = 0;
		if(!empty($TContactTypes)) {
	        foreach($TContactTypes as $code => $label)
	        {
		        $contactPrefix = $contactKey . $code . '_1';

		        // S'il n'y a aucun contact associé, on détaille tous les champs disponibles. Sinon, ça a déjà été fait
		        // ci-dessus : on ne fait donc que préciser les codes des types de contacts qui n'ont pas de contact lié
		        if (empty($atLeastOneContact) && $i == 0)
		        {
			        $contactstatic = new Contact($db);
			        $contactstatic->id = 0; // On empêche une erreur SQL au chargement des extrafields
			        $contactstatic->statut = ''; // Champ prérempli par le constructeur
			        $contactarray = parent::get_substitutionarray_contact($contactstatic, $outputlangs, $contactPrefix);
			        $resarray = array_merge($resarray, $contactarray);
		        }
		        elseif (empty($TCounts[$code]))
		        {
		        	$resarray[$contactPrefix . '_[...]'] = '';
		        }

		        $i++;
	        }
		}

        // Multicurrency
		if(!empty($object->multicurrency_code)) $resarray['devise_label'] = currency_name($object->multicurrency_code);
		return $resarray;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see CommonDocGenerator::get_substitutionarray_other()
	 */
	public function get_substitutionarray_other($outputlangs, $object = '') {
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
				$total_ttc = (isModEnabled('multicurrency') && $object->multicurrency_tx != 1) ? $object->multicurrency_total_ttc : $object->total_ttc;
				$array_other['liste_paiements'] = self::get_liste_reglements($object, $outputlangs);
				if (! empty($array_other['liste_paiements'])) {

					$deja_regle = $object->getSommePaiement((isModEnabled('multicurrency') && $object->multicurrency_tx != 1) ? 1 : 0);
					$creditnoteamount = $object->getSumCreditNotesUsed((isModEnabled('multicurrency') && $object->multicurrency_tx != 1) ? 1 : 0);
					$depositsamount = $object->getSumDepositsUsed((isModEnabled('multicurrency') && $object->multicurrency_tx != 1) ? 1 : 0);

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
		return $array_other;
	}

	/**
	 *
	 * @param stdClass $object Object pointer
	 * @param Translate $outputlangs Translate instance
	 * @param stdClass $element Element
	 * @return string
	 */
	public static function getLinkedObjects(&$object, &$outputlangs, $element=null) {
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
	 * @param stdClass $object Object
	 * @param Translate $outputlangs Translate Instalce
	 * @return number|array[]|number[][]
	 */
	public static function get_detail_tva(&$object, &$outputlangs) {
		global $conf, $langs;

		$langs->load("referenceletters@referenceletters");

		if (! is_array($object->lines))
			return 0;

		$TTva = array();

		$sign = 1;
		if (isset($object->type) && $object->type == 2 && getDolGlobalString('INVOICE_POSITIVE_CREDIT_NOTE'))
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
						if (isModEnabled('multicurrency') && $object->multicurrency_tx != 1)
							$tvaligne = $sign * $line->multicurrency_total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
						else
							$tvaligne = $sign * $line->total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
					} else {
						if (isModEnabled('multicurrency') && $object->multicurrency_tx != 1)
							$tvaligne = $sign * $line->multicurrency_total_tva;
						else
							$tvaligne = $sign * $line->total_tva;
					}
				} else {
					if (isModEnabled('multicurrency') && $object->multicurrency_tx != 1)
						$tvaligne = $line->multicurrency_total_tva;
					else
						$tvaligne = $line->total_tva;
				}

				if ($object->remise_percent)
					$tvaligne -= ($tvaligne * $object->remise_percent) / 100;
				if(empty($TTva[$langs->trans('TotalVAT'). ' ' . round($vatrate, 2) . '%'])) $TTva[$langs->trans('TotalVAT'). ' ' . round($vatrate, 2) . '%'] = 0;
				$TTva[$langs->trans('TotalVAT'). " " . round($vatrate, 2) . '%'] += $tvaligne;
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
	 * @param stdClass $object Object
	 * @param Translate $outputlangs Translate instance
	 * @return number|array[]|number[][]
	 */
	public static function get_liste_reglements(&$object, &$outputlangs) {
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
				$amount = price((isModEnabled('multicurrency') && $object->multicurrency_tx != 1) ? $obj->multicurrency_amount_ttc : $obj->amount_ttc, 0, $outputlangs);
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
		$sql .= " AND cp.entity = " . getEntity('c_paiement'); // cp.entity apparaît en 7.0
		$sql .= " WHERE pf.fk_paiement = p.rowid AND pf.fk_facture = " . $object->id;
		$sql .= " ORDER BY p.datep";

		$resql = $db->query($sql);
		if ($resql) {
			$sign = 1;
			if ($object->type == 2 && getDolGlobalString('INVOICE_POSITIVE_CREDIT_NOTE'))
				$sign = - 1;
			while ( $row = $db->fetch_object($resql) ) {
				$date = dol_print_date($db->jdate($row->date), 'day', false, $outputlangs, true);
				$amount = price($sign * ((isModEnabled('multicurrency') && $object->multicurrency_tx != 1) ? $row->multicurrency_amount : $row->amount), 0, $outputlangs);
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
			foreach ($TPayments as $k => $v) {
				$res .= '<table><tr>';
				foreach ($v as $val)
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
	 * @param stdClass $object Object
	 * @param Translate $outputlangs Translate Instance
	 * @return number|array[]|number[][]
	 */
	public function get_substitutionarray_lines($line, $outputlangs, $linenumber = 0)
	{
		global $conf;

		$resarray = parent::get_substitutionarray_lines($line, $outputlangs);

		$resarray['line_product_ref_fourn'] = $line->ref_fourn; // for supplier doc lines
		$resarray['line_rang'] = $line->rang;
		$resarray['line_libelle'] = $line->libelle; // récupére le libellé du produit/service
		if(empty($resarray['line_product_label'])) $resarray['line_product_label'] = $line->label;

		if(empty($resarray['line_desc']) && isModEnabled('subtotal'))
		{
			dol_include_once('/subtotal/class/subtotal.class.php');

			if(TSubtotal::isTitle($line) && ! empty($line->label))
			{
				$resarray['line_desc'] = $line->label;
			}
		}

		// Vérification des propriétés avant de les utiliser

		if (isset($line->date_ouverture)) {
			$resarray['date_ouverture'] = dol_print_date($line->date_ouverture, 'day', 'tzuser');
		}

		if (isset($line->date_ouverture_prevue)) {
			$resarray['date_ouverture_prevue'] = dol_print_date($line->date_ouverture_prevue, 'day', 'tzuser');
		}

		if (isset($line->date_fin_validite)) {
			$resarray['date_fin_validite'] = dol_print_date($line->date_fin_validite, 'day', 'tzuser');
		}

		if (isset($line->qty_shipped) && empty($resarray['line_qty_shipped'])) {
			$resarray['line_qty_shipped'] = price2num($line->qty_shipped);
		}

		if (isset($line->qty_asked) && empty($resarray['line_qty_asked'])) {
			$resarray['line_qty_asked'] = price2num($line->qty_asked);
		}

		if (isset($line->weight) && empty($resarray['line_weight'])) {
			$resarray['line_weight'] = price2num($line->weight);
		}

		if (isset($line->volume) && empty($resarray['line_vol'])) {
			$resarray['line_vol'] = price2num($line->volume);
		}

		return $resarray;
	}

	/**
	 * Define array with couple substitution key => substitution value
	 *
	 * @param array $line Array of lines
	 * @param Translate $outputlangs Lang object to use for output
	 * @return array Return a substitution array
	 */
	public function get_substitutionarray_lines_agefodd(&$line, $outputlangs, $fetchoptionnals = true) {
		global $db, $conf, $langs;

		require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
        dol_include_once('/agefodd/class/agefodd_session_stagiaire.class.php');

        // Substitutions tableau de participants :
        $sessionStag = new Agefodd_session_stagiaire($this->db);
        $resarray = array();
        $resarray['line_poste'] = $line->poste;
        $resarray['line_civilite'] = $line->civilitel;
        $resarray['line_civilite_short'] = $line->civilite;
        $resarray['line_nom'] = $line->nom;
        $resarray['line_prenom'] = $line->prenom;
        $resarray['line_type'] = $line->type;
        $resarray['line_birthday'] = dol_print_date($line->date_birth);
		$resarray['line_statut'] = $sessionStag->LibStatut($line->status_in_session);
		$resarray['line_place_birth'] = $line->place_birth;
		$resarray['line_birthdayformated'] = $line->datebirthformated;
		$tel = $line->tel1;
		if (empty($tel) && !empty($line->tel2)) {
			$tel = $line->tel2;
		} else {
			$tel = $line->tel1.(!empty($line->tel2)?'/'.$line->tel2:"");
		}
		$resarray['line_phone'] = $tel;
		$resarray['line_phone_pro'] = $line->tel1;
		$resarray['line_phone_mobile'] = $line->tel2;
		$resarray['line_email'] = $line->email;
		if (isset($line->thirdparty) && !is_null($line->thirdparty)) {
			$resarray['line_siret'] = $line->thirdparty->idprof2 ?? '';
		}
		$resarray['line_birthplace'] = $line->place_birth;
		$resarray['line_code_societe'] = $line->soccode;
		$resarray['line_nom_societe'] = $line->socname;
		$resarray['line_financiers_trainee'] = Agefodd_session_stagiaire::getFinanciersByTrainee($line->stagerowid);
		$resarray['line_alternate_financier_trainee'] = Agefodd_session_stagiaire::getAlternateFinancierByTrainee($line->stagerowid);
		$resarray['line_stagiaire_presence_bloc'] = $line->stagiaire_presence_bloc;
		$resarray['line_stagiaire_presence_total'] = $line->stagiaire_presence_total;
		$resarray['line_time_stagiaire_temps_realise_total'] = $line->time_stagiaire_temps_realise_total;
		$resarray['line_stagiaire_temps_realise_total'] = $line->stagiaire_temps_realise_total;
		$resarray['line_time_stagiaire_temps_att_total'] = $line->time_stagiaire_temps_att_total;
		$resarray['line_stagiaire_temps_att_total'] = $line->stagiaire_temps_att_total;
		$resarray['line_time_stagiaire_temps_realise_att_total'] = $line->time_stagiaire_temps_realise_att_total;
		$resarray['line_stagiaire_temps_realise_att_total'] = $line->stagiaire_temps_realise_att_total;
		if(empty($line->agefodd_stagiaire->thirdparty)) { //Retro compat < 2.17
			$resarray['line_societe_address'] = $line->societe_address;
			$resarray['line_societe_zip'] = $line->societe_zip;
			$resarray['line_societe_town'] = $line->societe_town;
		}
		else {
			$resarray['line_societe_address'] = $line->agefodd_stagiaire->thirdparty->address;
			$resarray['line_societe_zip'] = $line->agefodd_stagiaire->thirdparty->zip;
			$resarray['line_societe_town'] = $line->agefodd_stagiaire->thirdparty->town;
			$resarray['line_societe_mail'] = $line->agefodd_stagiaire->thirdparty->email;
			$extrafields = new ExtraFields($this->db);
			$extrafields->fetch_name_optionals_label($line->agefodd_stagiaire->thirdparty->element, true);
			$resarray = $this->fill_substitutionarray_with_extrafields($line->agefodd_stagiaire->thirdparty, $resarray, $extrafields, 'line_societe', $langs);
		}
		$resarray['line_presence_bloc'] = '';
		$resarray['line_presence_total'] = '';

		if(isModEnabled('agefoddcertificat')) {
			// Certificats
			dol_include_once('/agefoddcertificat/class/agefoddcertificat.class.php');
			$agf_certif = new AgefoddCertificat($db);
			$TCertif = $agf_certif->fetchAll('','',0, 0,array('fk_trainee' => $line->id, 'fk_session' => $line->sessid, 'isDeleted' => 0));
			if(is_array($TCertif) && count($TCertif) > 0) {
				$agf_certif = array_shift($TCertif);
				$resarray['line_certif_code'] = $agf_certif->number;
				$resarray['line_certif_label'] = $agf_certif->label;
				$resarray['line_certif_date_debut'] = dol_print_date($agf_certif->date_start);
				$resarray['line_certif_date_fin'] = dol_print_date($agf_certif->date_end);
				$resarray['line_certif_date_alerte'] = dol_print_date($agf_certif->date_warning);
			}
		}

		// Display session stagiaire heure
		if(!empty($line->sessid) && !empty($line->id))
		{
		    dol_include_once('agefodd/class/agefodd_session_stagiaire_heures.class.php');
		    dol_include_once('agefodd/class/agefodd_session_calendrier.class.php');
		    if(class_exists('Agefoddsessionstagiaireheures') && class_exists('Agefodd_sesscalendar'))
		    {
    		    $agefoddsessionstagiaireheures = new Agefoddsessionstagiaireheures($db);
    		    $agefoddsessionstagiaireheures->fetch_all_by_session($line->sessid, $line->id);
    		    if(!empty($agefoddsessionstagiaireheures->lines)){
    		        $hPresenceTotal = 0;
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

    		                    $hPresenceTotal+= $heures->heures;

    		                    $resarray['line_presence_bloc'].= (!empty($resarray['line_presence_bloc'])?', ':'');
    		                    // return the time formatted HH:MM
    		                    $resarray['line_presence_bloc'].= dol_print_date($agefodd_sesscalendar->date_session, '%d/%m/%Y').'&nbsp;('.$hours."H".sprintf("%02u", $minutes).')';
    		                }
    		            }
    		        }

    		        // TOTAL DES HEURES PASSEES
    		        // start by converting to seconds
    		        $seconds = floor($hPresenceTotal * 3600);
    		        // we're given hours, so let's get those the easy way
    		        $hours = floor($hPresenceTotal);
    		        // since we've "calculated" hours, let's remove them from the seconds variable
    		        $seconds -= $hours * 3600;
    		        // calculate minutes left
    		        $minutes = floor($seconds / 60);
    		        $resarray['line_presence_total']= $hours."H".sprintf("%02u", $minutes);
    		    }
		    }
		}

		// Substitutions tableau d'horaires
		$resarray['line_date_session'] = property_exists($line, 'date_session') ? dol_print_date($line->date_session) : '';
		$resarray['line_heure_debut_session'] = property_exists($line, 'heured') ? dol_print_date($line->heured, 'hour') : '';
		$resarray['line_heure_fin_session'] = property_exists($line, 'heuref') ? dol_print_date($line->heuref, 'hour') : '';

		// Substitutions tableau des formateurs :
		$resarray['line_formateur_nom'] = property_exists($line, 'lastname') ? $line->lastname : '';
		$resarray['line_formateur_prenom'] = property_exists($line, 'firstname') ? $line->firstname : '';
		$resarray['line_formateur_phone'] = property_exists($line, 'phone') ? $line->phone : '';
		$resarray['line_formateur_phone_mobile'] = property_exists($line, 'phone_mobile') ? $line->phone_mobile : '';
		$resarray['line_formateur_phone_perso'] = property_exists($line, 'phone_perso') ? $line->phone_perso : '';
		$resarray['line_formateur_mail'] = property_exists($line, 'email') ? $line->email : '';
		$resarray['line_formateur_socname'] = property_exists($line, 'socname') ? $line->socname : '';
		$resarray['line_formateur_address'] = property_exists($line, 'address') ? $line->address : '';
		$resarray['line_formateur_town'] = property_exists($line, 'town') ? $line->town : '';
		$resarray['line_formateur_zip'] = property_exists($line, 'zip') ? $line->zip : '';
		$resarray['line_formateur_statut'] = (property_exists($line, 'labelstatut') && is_array($line->labelstatut) && property_exists($line, 'trainer_status'))
			? ($line->labelstatut[$line->trainer_status] ?? '')
			: '';

		// Substitutions tableau des élément financier :
//		$resarray['line_fin_desciption'] = str_replace('<br />', "\n", str_replace('<BR>', "\n", $line->description));

		//strip_tags permet de supprimer les balises HTML et PHP d'une chaine, la mise en forme faisait disparaître une partie du pdf de convention docedit
		// Description (avec strip_tags)
		$resarray['line_fin_desciption'] = property_exists($line, 'description')
			? strip_tags($line->description, "<br><p><ul><ol><li><span><div><tr><td><th><table>")
			: '';

	// Description light (commentée, donc pas activée)
	// $resarray['line_fin_desciption_light'] = property_exists($line, 'form_label') ? $line->form_label : '';

	// Description light short
		$resarray['line_fin_desciption_light_short'] = property_exists($line, 'form_label_short')
			? $line->form_label_short
			: '';

	// Quantité
		$resarray['line_fin_qty'] = property_exists($line, 'qty')
			? $line->qty
			: '';

	// Taux de TVA (attention: vatrate() peut nécessiter une valeur par défaut)
		$resarray['line_fin_tva_tx'] = property_exists($line, 'tva_tx')
			? vatrate($line->tva_tx, 1)
			: vatrate(0, 1); // ou vatrate('', 1) selon ta fonction

	// Montant HT (price() nécessite une valeur)
		$resarray['line_fin_amount_ht'] = property_exists($line, 'total_ht')
			? price($line->total_ht, 0, $outputlangs, 1, -1, 2)
			: price(0, 0, $outputlangs, 1, -1, 2);

	// Montant TTC (idem)
		$resarray['line_fin_amount_ttc'] = property_exists($line, 'total_ttc')
			? price($line->total_ttc, 0, $outputlangs, 1, -1, 2)
			: price(0, 0, $outputlangs, 1, -1, 2);

	// Remise (dol_print_reduction() nécessite une valeur)
		$resarray['line_fin_discount'] = property_exists($line, 'remise_percent')
			? dol_print_reduction($line->remise_percent, $outputlangs)
			: dol_print_reduction(0, $outputlangs);

	// Prix unitaire HT (price() nécessite une valeur)
		$resarray['line_fin_pu_ht'] = property_exists($line, 'price')
			? price($line->price, 0, $outputlangs, 1, -1, 2)
			: price(0, 0, $outputlangs, 1, -1, 2);

		// Retrieve extrafields
		$extrafieldkey = $line->element;
		$array_key = "line";
		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$extralabels = $extrafields->fetch_name_optionals_label($extrafieldkey, true);
		if(floatval(DOL_VERSION) >= 16) {
			$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attribute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
			if (isset($extrafields->attributes['agefodd_stagiaire']['loaded']) &&  $extrafields->attributes[$extrafieldkey]['loaded'] > 0) {
				$extrafields->attribute_type = isset($extrafields->attributes[$extrafieldkey]['type']) ? $extrafields->attributes[$extrafieldkey]['type'] : '';
				$extrafields->attribute_size = isset($extrafields->attributes[$extrafieldkey]['size']) ? $extrafields->attributes[$extrafieldkey]['size'] : '';
				$extrafields->attribute_unique = isset($extrafields->attributes[$extrafieldkey]['unique']) ? $extrafields->attributes[$extrafieldkey]['unique'] : '';
				$extrafields->attribute_required = isset($extrafields->attributes[$extrafieldkey]['required']) ? $extrafields->attributes[$extrafieldkey]['required'] : '';
				$extrafields->attribute_label = isset($extrafields->attributes[$extrafieldkey]['label']) ? $extrafields->attributes[$extrafieldkey]['label'] : '';
				$extrafields->attribute_default = isset($extrafields->attributes[$extrafieldkey]['default']) ? $extrafields->attributes[$extrafieldkey]['default'] : '';
				$extrafields->attribute_computed = isset($extrafields->attributes[$extrafieldkey]['computed']) ? $extrafields->attributes[$extrafieldkey]['computed'] : '';
				$extrafields->attribute_param = isset($extrafields->attributes[$extrafieldkey]['param']) ? $extrafields->attributes[$extrafieldkey]['param'] : '';
				$extrafields->attribute_perms = isset($extrafields->attributes[$extrafieldkey]['perms']) ? $extrafields->attributes[$extrafieldkey]['perms'] : '';
				$extrafields->attribute_langfile = isset($extrafields->attributes[$extrafieldkey]['langfile']) ? $extrafields->attributes[$extrafieldkey]['langfile'] : '';
				$extrafields->attribute_list = isset($extrafields->attributes[$extrafieldkey]['list']) ? $extrafields->attributes[$extrafieldkey]['list'] : '';
				$extrafields->attribute_hidden = isset($extrafields->attributes[$extrafieldkey]['hidden']) ? $extrafields->attributes[$extrafieldkey]['hidden'] : '';
			}
		}
		if ($fetchoptionnals) {
			$line->fetch_optionals($line->rowid, $extralabels);
		}

		if(getDolGlobalInt('AGF_USE_STEPS')){
			$resarray['line_step_label'] = $line->label;
			$resarray['line_step_date_start'] = dol_print_date($line->date_start, 'day');
			$resarray['line_step_date_end'] = dol_print_date($line->date_end, 'day');
			$resarray['line_step_duration'] = $line->duration != null ? $line->duration : '';
			// Lieu
			$resarray['line_step_lieu'] = strip_tags($line->place->ref_interne);
			$resarray['line_step_lieu_adresse'] = strip_tags($line->place->adresse);
			$resarray['line_step_lieu_cp'] = strip_tags($line->place->cp);
			$resarray['line_step_lieu_ville'] = strip_tags($line->place->ville);
			$resarray['line_step_lieu_acces'] = str_replace('&amp;', '&', $line->place->acces_site);
			$resarray['line_step_lieu_horaires'] = strip_tags($line->place->timeschedule);
			$resarray['line_step_lieu_divers'] = $line->place->note1;
		}

		if (property_exists($line, 'agefodd_stagiaire') && !empty($line->agefodd_stagiaire) && empty($line->array_options)) {
			$extrafields = new ExtraFields($this->db);
			$extralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);

			if(floatval(DOL_VERSION) >= 16) {
				$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attribute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
				if (isset($extrafields->attributes['agefodd_stagiaire']['loaded']) && $extrafields->attributes['agefodd_stagiaire']['loaded'] > 0) {
					$agefodd = $extrafields->attributes['agefodd_stagiaire'];
					$extrafields->attribute_type = isset($agefodd['type']) ? $agefodd['type'] : '';
					$extrafields->attribute_size = isset($agefodd['size']) ? $agefodd['size'] : '';
					$extrafields->attribute_unique = isset($agefodd['unique']) ? $agefodd['unique'] : '';
					$extrafields->attribute_required = isset($agefodd['required']) ? $agefodd['required'] : '';
					$extrafields->attribute_label = isset($agefodd['label']) ? $agefodd['label'] : '';
					$extrafields->attribute_default = isset($agefodd['default']) ? $agefodd['default'] : '';
					$extrafields->attribute_computed = isset($agefodd['computed']) ? $agefodd['computed'] : '';
					$extrafields->attribute_param = isset($agefodd['param']) ? $agefodd['param'] : '';
					$extrafields->attribute_perms = isset($agefodd['perms']) ? $agefodd['perms'] : '';
					$extrafields->attribute_langfile = isset($agefodd['langfile']) ? $agefodd['langfile'] : '';
					$extrafields->attribute_list = isset($agefodd['list']) ? $agefodd['list'] : '';
					$extrafields->attribute_hidden = isset($agefodd['hidden']) ? $agefodd['hidden'] : '';
				}

			}
			$line->agefodd_stagiaire->fetch_optionals();
			$line->array_options=$line->agefodd_stagiaire->array_options;
		}
		$resarray = $this->fill_substitutionarray_with_extrafields($line, $resarray, $extrafields, $array_key, $outputlangs);

		// Appel de la fonction parente pour les lignes des documents std dolibarr (propal, cmd, facture, contrat)
		$arrayTypeObj=array('PropaleLigne','OrderLine','FactureLigne','ContratLigne','CommandeFournisseurLigne','ExpeditionLigne');
		if (in_array(get_class($line),$arrayTypeObj)) {
			$resarray = parent::get_substitutionarray_lines($line, $outputlangs);
			$resarray['line_rang'] = $line->rang;
		}
		$resarray['line_unit'] = (method_exists($line, 'getLabelOfUnit')) ? $langs->trans($line->getLabelOfUnit('short')) : '';
		if (get_class($line)=='ExpeditionLigne') {
			$weighttxt = '';
			if ($line->fk_product_type == 0 && $line->weight)
			{
				$weighttxt = round($line->weight * $line->qty_shipped, 5).' '.measuringUnitString(0, "weight", $line->weight_units, 1);
			}
			$voltxt = '';
			if ($line->fk_product_type == 0 && $line->volume)
			{
				$voltxt = round($line->volume * $line->qty_shipped, 5).' '.measuringUnitString(0, "volume", $line->volume_units ? $line->volume_units : 0, 1);
			}
			$resarray['line_weight'] =$weighttxt;
			$resarray['line_vol'] =$voltxt;
			$resarray['line_qty_asked'] =$line->qty_asked;
			$resarray['line_qty_shipped'] =$line->qty_shipped;
		}

		// Spé pour les contrats
		$resarray['date_ouverture'] = property_exists($line, 'date_ouverture') ? dol_print_date($line->date_ouverture, 'day', 'tzuser') : '';
		$resarray['date_ouverture_prevue'] = property_exists($line, 'date_ouverture_prevue') ? dol_print_date($line->date_ouverture_prevue, 'day', 'tzuser') : '';
		$resarray['date_fin_validite'] = property_exists($line, 'date_fin_validite') ? dol_print_date($line->date_fin_validite, 'day', 'tzuser') : '';

		return $resarray;
	}

	/**
	 * executée depuis la fiche formation
	 * @param Formation $object
	 * @param string $outputlangs
	 * @return array
	 */
	public function get_substitutionsarray_agefodd_formation(Formation &$object,Translate  $outputlangs)
	{

		global $db,  $langs, $extrafields;
		$listRef = "";
		dol_include_once('/agefodd/class/html.formagefodd.class.php');
		dol_include_once('/product/class/product.class.php');
		$formAgefodd = new FormAgefodd($db);
		$resarray = array();

		$resarray['formation_nom']=$object->intitule;
		$resarray['formation_ref']=$object->ref_obj;
		$resarray['formation_statut']=$object->getLibStatut();
		$resarray['formation_duree' ]= $object->duree;
		$resarray['formation_but']=$object->but;
		$resarray['formation_methode']=$object->methode;
		$resarray['formation_nb_place_dispo']=$object->nb_place;
		$resarray['formation_nb_inscription_mini']= $object->nb_subscribe_min;
		$resarray['formation_category']=$object->category_lib;
		$resarray['formation_category_bpf']=$object->category_lib_bpf;
		$prod = new Product($db);
		$res = $prod->fetch($object->fk_product);
		if ($res)
		$resarray['formation_product']=$prod->label;
		$resarray['formation_type_public']=$object->public;
		$resarray['formation_methode_pedago']=$object->methode;
		$resarray['formation_documents']=$object->note1;
		$resarray['formation_equipements']=$object->note2;
		$resarray['formation_pre_requis']=$object->prerequis;
		$resarray['formation_moyens_peda']=$object->pedago_usage;
		$resarray['formation_sanction']=$object->sanction;
		$resarray['formation_nature']= $formAgefodd->select_formation_nature_action($object->fk_nature_action_code, '', '', '', '', 'view');
		$resarray['formation_Accessibility_Handicap']=$object->accessibility_handicap == 0 ? 'Non' : 'Oui';
		$arrpeda= explode(',', $object->formation_obj_peda);
		$tmp="";
		foreach ($arrpeda as $peda) {
			$tmp .= $peda . "<br>";
		}
		$resarray['formation_competences']=$tmp;

		$localuser = new User($db);
		$localuser->fetch(getDolGlobalInt('AGF_DEFAULT_MENTOR_ADMIN'));
		$resarray['Mentor_administrator']	=  $localuser->getFullName($langs); 'Référent Administratif';
		$listRef = $localuser->getFullName($langs);
		$localuser->fetch(getDolGlobalInt('AGF_DEFAULT_MENTOR_PEDAGO'));
		$resarray['Mentor_pedagogique']	=  $localuser->getFullName($langs);
		$listRef .= ', '.$localuser->getFullName($langs);
		$localuser->fetch(getDolGlobalInt('AGF_DEFAULT_MENTOR_HANDICAP'));
		$resarray['Mentor_handicap'	]	=  $localuser->getFullName($langs); 'Référent handicap';
		$listRef .= ', '.$localuser->getFullName($langs);
		$resarray['AgfMentorList']=$listRef;
   		// -----------

		$e = new ExtraFields($db);
		$e->fetch_name_optionals_label($object->table_element);
		if(floatval(DOL_VERSION) >= 16) {
			$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attribute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
			if($extrafields->attributes[$object->table_element]['loaded'] > 0) {
				$extrafields->attribute_type = $extrafields->attributes[$object->table_element]['type'] ?? array();
				$extrafields->attribute_size = $extrafields->attributes[$object->table_element]['size']?? array();
				$extrafields->attribute_unique = $extrafields->attributes[$object->table_element]['unique']?? array();
				$extrafields->attribute_required = $extrafields->attributes[$object->table_element]['required']?? array();
				$extrafields->attribute_label = $extrafields->attributes[$object->table_element]['label']?? array();
				$extrafields->attribute_default = $extrafields->attributes[$object->table_element]['default']?? array();
				$extrafields->attribute_computed = $extrafields->attributes[$object->table_element]['computed']?? array();
				$extrafields->attribute_param = $extrafields->attributes[$object->table_element]['param']?? array();
				$extrafields->attribute_perms = $extrafields->attributes[$object->table_element]['perms']?? array();
				$extrafields->attribute_langfile = $extrafields->attributes[$object->table_element]['langfile']?? array();
				$extrafields->attribute_list = $extrafields->attributes[$object->table_element]['list']?? array();
				$extrafields->attribute_hidden = $extrafields->attributes[$object->table_element]['hidden']?? array();
			}
		}
		$object->fetch_optionals();
		if( is_array($e->attributes[$object->table_element])
			&& array_key_exists('label',$e->attributes[$object->table_element])
			&& is_array($e->attributes[$object->table_element]['label'])){
			foreach($e->attributes[$object->table_element]['label'] as $key => $val) {
				$resarray['formation_options_'.$key] = strip_tags($e->showOutputField($key, $object->array_options['options_'.$key]));
			}
		}

		return $resarray;
	}
	/**
	 *
	 * @param CommonObject $object Object
	 * @param Translate $outputlangs Translate instance
	 * @return string[]|NULL[]|mixed[]|array[]
	 */
	public function get_substitutionsarray_agefodd(&$object, $outputlangs)
	{
		global $db, $langs;


		dol_include_once('/agefodd/class/html.formagefodd.class.php');
		dol_include_once('/societe/class/societe.class.php');

		$fk_step = intval(GETPOST('fk_step', 'int'));
		if($fk_step > 0) {
			$agfStep = new Agefodd_step($this->db);
			$agfStep->fetch($fk_step);
		}

		$formAgefodd = new FormAgefodd($db);

		$resarray = array();
		$resarray['formation_nom'] = $object->formintitule;
		$resarray['formation_nom_custo'] = $object->intitule_custo;
		$resarray['formation_date_debut'] = dol_print_date($object->dated,'day','tzserver',$outputlangs);
		$resarray['formation_date_debut_formated'] = dol_print_date($object->dated,'%A %d %B %Y','tzserver',$outputlangs);
		$resarray['formation_date_fin'] = dol_print_date($object->datef,'day','tzserver',$outputlangs);
		$resarray['formation_date_fin_formated'] = dol_print_date($object->datef,'%A %d %B %Y','tzserver',$outputlangs);
		$resarray['formation_ref'] = $object->formref;


		if(!empty($object->fk_product)) {
			$p = new Product($db);
			$p->fetch($object->fk_product);
			$resarray['formation_ref_produit'] = $p->ref;
		}


		// Substitution concernant le prestataire
		$TDefaultSub = array('presta_lastname', 'presta_firstname', 'presta_soc_name','presta_soc_id','presta_soc_name',
			'presta_soc_name_alias','presta_soc_code_client','presta_soc_code_fournisseur','presta_soc_email','presta_soc_phone',
			'presta_soc_fax','presta_soc_address','presta_soc_zip','presta_soc_town','presta_soc_country_id','presta_soc_country_code',
			'presta_soc_idprof1','presta_soc_idprof2','presta_soc_idprof3','presta_soc_idprof4','presta_soc_idprof5',
			'presta_soc_idprof6','presta_soc_tvaintra','presta_soc_note_public','presta_soc_note_private'
		);

		foreach ($TDefaultSub as $ksub){
			$resarray[$ksub] = '';// si pas de substitution remplacer par vide
		}

		if (!empty($object->fk_socpeople_presta)) {
			$presta = new Contact($db);
			$res = $presta->fetch($object->fk_socpeople_presta);
			if ($res > 0) {
				$resarray['presta_lastname'] = $presta->lastname;
				$resarray['presta_firstname'] = $presta->firstname;
			}

			$presta_soc = new Societe($db);
			$ressoc = $presta_soc->fetch($presta->socid);
			if ($ressoc > 0) {
				$resarray['presta_soc_name'] = $presta_soc->name;
				$resarray['presta_soc_id'] = $presta_soc->id;
				$resarray['presta_soc_name'] = $presta_soc->name;
				$resarray['presta_soc_name_alias'] = $presta_soc->name_alias;
				$resarray['presta_soc_code_client'] = $presta_soc->code_client;
				$resarray['presta_soc_code_fournisseur'] = $presta_soc->code_fournisseur;
				$resarray['presta_soc_email'] = $presta_soc->email;
				$resarray['presta_soc_phone'] = $presta_soc->phone;
				$resarray['presta_soc_fax'] = $presta_soc->fax;
				$resarray['presta_soc_address'] = $presta_soc->address;
				$resarray['presta_soc_zip'] = $presta_soc->zip;
				$resarray['presta_soc_town'] = $presta_soc->town;
				$resarray['presta_soc_country_id'] = $presta_soc->country_id;
				$resarray['presta_soc_country_code'] = $presta_soc->country_code;
				$resarray['presta_soc_idprof1'] = $presta_soc->idprof1;
				$resarray['presta_soc_idprof2'] = $presta_soc->idprof2;
				$resarray['presta_soc_idprof3'] = $presta_soc->idprof3;
				$resarray['presta_soc_idprof4'] = $presta_soc->idprof4;
				$resarray['presta_soc_idprof5'] = $presta_soc->idprof5;
				$resarray['presta_soc_idprof6'] = $presta_soc->idprof6;
				$resarray['presta_soc_tvaintra'] = $presta_soc->tva_intra;
				$resarray['presta_soc_note_public'] = dol_htmlentitiesbr($presta_soc->note_public);
				$resarray['presta_soc_note_private'] = dol_htmlentitiesbr($presta_soc->note_private);
			}
		}

		$resarray['formation_statut'] = $object->statuslib ?? '';
		$resarray['formation_id'] = $object->fk_formation_catalogue ?? '';
		$resarray['formation_duree'] = $object->duree?? '';
		$resarray['formation_duree_session'] = $object->duree_session?? '';
		$resarray['formation_commercial'] = $object->commercialname?? '';
		$resarray['formation_commercial_invert'] = $object->commercialname_invert?? '';
		$resarray['formation_commercial_phone'] = $object->commercialphone?? '';
		$resarray['formation_commercial_mobile_phone'] = $object->commercial_mobile_phone?? '';
		$resarray['formation_commercial_mail'] = $object->commercialemail?? '';
		$resarray['formation_societe'] = $object->thirdparty->nom?? '';
		$resarray['formation_commentaire'] = nl2br($object->notes ?? '') ;
		$resarray['formation_type'] = $formAgefodd->type_session_def[$object->type_session] ?? '';
		$resarray['formation_nb_stagiaire'] = $object->nb_stagiaire ?? '';
		$resarray['formation_nb_stagiaire_convention'] = $object->nb_stagiaire_convention?? '';
		$resarray['formation_stagiaire_convention'] = $object->stagiaire_convention?? '';
		$resarray['formation_prix'] = price($object->sell_price ?? '' );
		$resarray['formation_obj_peda'] = $object->formation_obj_peda?? '';
		$resarray['session_nb_days'] = $object->session_nb_days?? '';
		$resarray['trainer_datehourtextline'] = $object->trainer_datehourtextline?? '';
		$resarray['trainer_datetextline'] = $object->trainer_datetextline?? '';
		$resarray['stagiaire_presence_total'] = $object->stagiaire_presence_total?? '';
		$resarray['stagiaire_presence_bloc'] = $object->stagiaire_presence_bloc?? '';
		$resarray['time_stagiaire_temps_realise_total'] = $object->time_stagiaire_temps_realise_total?? '';
		$resarray['stagiaire_temps_realise_total'] = $object->stagiaire_temps_realise_total?? '';
		$resarray['time_stagiaire_temps_att_total'] = $object->time_stagiaire_temps_att_total?? '';
		$resarray['stagiaire_temps_att_total'] = $object->stagiaire_temps_att_total?? '';
		$resarray['time_stagiaire_temps_realise_att_total'] = $object->time_stagiaire_temps_realise_att_total?? '';
		$resarray['stagiaire_temps_realise_att_total'] = $object->stagiaire_temps_realise_att_total?? '';
 		$resarray['trainer_cost_planned'] = price($object->cost_trainer_planned ?? '');


		$resarray['AgfMentorList'] =  $langs->trans("AgfMentorList");
        if (getDolGlobalString('AGF_DEFAULT_MENTOR_ADMIN')){
			$u = new User($this->db);
			$res = $u->fetch(intval(getDolGlobalString('AGF_DEFAULT_MENTOR_ADMIN')));
			if ($res){
				$resarray['Mentor_administrator'] = ucfirst($langs->trans('MentorAdmin') ." : " . $u->civility_code .' '.  $u->firstname . " " . $u->lastname);
			}
        }

        if (getDolGlobalString('AGF_DEFAULT_MENTOR_PEDAGO')) {
			$u = new User($this->db);
			$res = $u->fetch(intval(getDolGlobalString('AGF_DEFAULT_MENTOR_PEDAGO')));
			if ($res) {
				$resarray['Mentor_pedagogique'] = ucfirst($langs->trans('MentorPedago') . " : " . $u->civility_code . ' ' . $u->firstname . " " . $u->lastname);
			}
		}


		if (getDolGlobalString('AGF_DEFAULT_MENTOR_HANDICAP')) {
			$u = new User($this->db);
			$res = $u->fetch(intval(getDolGlobalString('AGF_DEFAULT_MENTOR_HANDICAP')));
			if ($res) {
				$resarray['Mentor_handicap'] = ucfirst($langs->trans('MentorHandicap') . " : " . $u->civility_code . ' ' . $u->firstname . " " . $u->lastname);
			}
		}

		// cela devrait être toujours vrai ici
		if (! empty($object->fk_formation_catalogue)) {

			dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');


			// est ce que j'ai une copie de la formation de la formation dans session_catalogue ?
			// dit autrement est ce que j'ai modifié le receuil depuis l'onglet receuil de la formation ?
			//

			if (class_exists('Agefodd')) {
				$catalogue = new Agefodd($db);
			} elseif (class_exists('Formation')) {
				$catalogue = new Formation($db);
			}
			$sessionFormation = new SessionCatalogue($this->db);
			$res = $sessionFormation->fetchSessionCatalogue($object->id);

			if ($res > 0 ){
				$catalogue = $sessionFormation;
			}else{
				$catalogue->fetch($object->fk_formation_catalogue);
			}

			// ajouter les peda ici pour


			$resarray['formation_but'] = $catalogue->but;
			$resarray['formation_ref'] = $catalogue->ref_obj;
			$resarray['formation_refint'] = $catalogue->ref_interne;
			$resarray['formation_methode'] = $catalogue->methode;
			$resarray['formation_prerequis'] = $catalogue->prerequis;
			$resarray['formation_sanction'] = $catalogue->sanction;
			$resarray['formation_type_stagiaire'] = $catalogue->public;
			$resarray['formation_programme'] = $catalogue->programme;
			$resarray['formation_documents'] = $catalogue->note1;
			$resarray['formation_equipements'] = $catalogue->note2;
			$resarray['formation_nb_place'] = $catalogue->nb_place;
			$resarray['formation_type_public'] = $catalogue->category_lib;
			$resarray['formation_moyens_pedagogique'] = $catalogue->pedago_usage;
			$resarray['formation_sanction'] = $catalogue->sanction;
			$resarray['formation_Accessibility_Handicap_label'] = $langs->trans('RefLtrAccessHandicapTitle');

			$extrafields = new ExtraFields($db);
			$extrafields->fetch_name_optionals_label($catalogue->table_element);
			if(floatval(DOL_VERSION) >= 16) {
				if (isset($extrafields)){
					$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attribute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
					if($extrafields->attributes[$catalogue->table_element]['loaded'] > 0) {
					$extrafields->attribute_type = $extrafields->attributes[$catalogue->table_element]['type']?? array();
					$extrafields->attribute_size = $extrafields->attributes[$catalogue->table_element]['size']?? array() ;
					$extrafields->attribute_unique = $extrafields->attributes[$catalogue->table_element]['unique']?? array();
					$extrafields->attribute_required = $extrafields->attributes[$catalogue->table_element]['required']?? array();
					$extrafields->attribute_label = $extrafields->attributes[$catalogue->table_element]['label']?? array();
					$extrafields->attribute_default = $extrafields->attributes[$catalogue->table_element]['default']?? array();
					$extrafields->attribute_computed = $extrafields->attributes[$catalogue->table_element]['computed']?? array();
					$extrafields->attribute_param = $extrafields->attributes[$catalogue->table_element]['param']?? array();
					$extrafields->attribute_perms = $extrafields->attributes[$catalogue->table_element]['perms']?? array();
					$extrafields->attribute_langfile = $extrafields->attributes[$catalogue->table_element]['langfile']?? array();
					$extrafields->attribute_list = $extrafields->attributes[$catalogue->table_element]['list']?? array();
					$extrafields->attribute_hidden = $extrafields->attributes[$catalogue->table_element]['hidden']?? array();
				}
				}
			}

			if (isset($e->attributes[$catalogue->table_element]['label']) && is_array($e->attributes[$catalogue->table_element]['label'])){
				foreach($e->attributes[$catalogue->table_element]['label'] as $key => $val) {
					$resarray['formation_'.$key] = strip_tags($e->showOutputField($key, $catalogue->array_options['options_'.$key]));
				}
			}

			// surcharge pour le oui ou non à la place de 1 ou 0
			$resarray['formation_Accessibility_Handicap'] = $catalogue->accessibility_handicap == 1 ? 'oui':'non';


		}

		$fk_place = $object->placeid;
		if (!empty($agfStep->id)) { //Si on est sur une étape, on prend le lieu de l'étape
			$fk_place = $agfStep->fk_place;
		}

		if (isset($agfStep) && !is_null($agfStep)) {
			$resarray['step_label'] = property_exists($agfStep, 'label') ? $agfStep->label : '';
			$resarray['step_date_start'] = property_exists($agfStep, 'date_start') ? dol_print_date($agfStep->date_start, 'day') : '';
			$resarray['step_date_end'] = property_exists($agfStep, 'date_end') ? dol_print_date($agfStep->date_end, 'day') : '';
			$resarray['step_duration'] = property_exists($agfStep, 'duration') ? $agfStep->duration : '';
		} else {
			$resarray['step_label'] = '';
			$resarray['step_date_start'] = '';
			$resarray['step_date_end'] = '';
			$resarray['step_duration'] = '';
		}

		dol_include_once('/agefodd/class/agefodd_place.class.php');
		$agf_place = new Agefodd_place($db);
		if(! empty($fk_place)) $agf_place->fetch($fk_place);
		// Lieu
		$resarray['formation_lieu'] 				= strip_tags($agf_place->ref_interne);
		$resarray['formation_lieu_adresse'] 		= strip_tags($agf_place->adresse);
		$resarray['formation_lieu_cp'] 				= strip_tags($agf_place->cp);
		$resarray['formation_lieu_ville'] 			= strip_tags($agf_place->ville);
		// TODO si le str_replace est trop brutal, faire un preg_replace du style : src="(.*)\&amp;(.*)"
		// fix TK9760
		$resarray['formation_lieu_acces'] 			= str_replace('&amp;', '&', $agf_place->acces_site);
		$resarray['formation_lieu_phone'] 			= dol_print_phone($agf_place->tel, $agf_place->country_code);
		$resarray['formation_lieu_horaires'] 		= strip_tags($agf_place->timeschedule);
		$resarray['formation_lieu_notes'] 			= strip_tags($agf_place->notes);
		$resarray['formation_lieu_divers'] 			= $agf_place->note1;


		// Add ICS link replacement to mails
		$downloadIcsLink = dol_buildpath('public/agenda/agendaexport.php', 2) . '?format=ical&type=event';
		$documentLinkLabel = "ICS";

		if (!empty($object->trainer_session))
		{
			$url = $downloadIcsLink . '&amp;agftrainerid=' . $object->trainer_session->id;
			$url .= '&exportkey=' . md5(getDolGlobalString('MAIN_AGENDA_XCAL_EXPORTKEY') . 'agftrainerid' . $object->trainer_session->id);
			$resarray['formation_agenda_ics'] = '<a href="' . $url . '">' . $documentLinkLabel . '</a>';
			$resarray['formation_agenda_ics_url'] = $url;
		}
		elseif (!empty($object->stagiaire))
		{
			$url = $downloadIcsLink . '&amp;agftraineeid=' . $object->stagiaire->id;
			$url .='&exportkey=' . md5(getDolGlobalString('MAIN_AGENDA_XCAL_EXPORTKEY') . 'agftraineeid' . $object->stagiaire->id);
			$resarray['formation_agenda_ics'] = '<a href="' . $url . '">' . $documentLinkLabel . '</a>';
			$resarray['formation_agenda_ics_url'] = $url;
		}

		return $resarray;
	}

	/**
	 * Define array with couple subtitution key => subtitution value
	 *
	 * @param Object $object Dolibarr Object
	 * @param Translate $outputlangs Language object for output
	 * @param boolean $recursive Want to fetch child array or child object
	 * @param string $sub_element_label Object Element
	 * @return array Array of substitution key->code
	 */
	public function get_substitutionarray_each_var_object(&$object, $outputlangs, $recursive = true, $sub_element_label = '')
	{
		global $conf;

		$array_other = array();

		if (! empty($object)) {

			foreach ( $object as $key => $value ) {
				$isStagiaireSocExtrafields = strpos($key, 'stagiaire_soc_options')  !== false;
				if ($key == 'db') continue;
				else if ($key == 'array_options' && is_object($object) || $isStagiaireSocExtrafields)
				{
					// Inspiration depuis Dolibarr ( @see CommonDocGenerator::get_substitutionarray_object() )
					// à la différence que si l'objet n'a pas de ligne extrafield en BDD, le tag {objvar_object_array_options_options_XXX} affichera vide
					// au lieu de laisser la clé, ce qui est le cas avec les clés standards Dolibarr : {object_options_XXX}
					// Retrieve extrafields
					if (substr($object->element, 0, 7) === 'agefodd') $extrafieldkey=$object->table_element;
					else $extrafieldkey=$object->element;
					if($isStagiaireSocExtrafields) $extrafieldkey = 'societe';

					require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
					$extrafields = new ExtraFields($this->db);
					$extralabels = $extrafields->fetch_name_optionals_label($extrafieldkey, true);
					if(floatval(DOL_VERSION) >= 16) {
						if (isset($extrafields)) {
							$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attribute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
							if (isset($extrafields->attributes[$extrafieldkey]) &&
								isset($extrafields->attributes[$extrafieldkey]['loaded']) &&
								$extrafields->attributes[$extrafieldkey]['loaded'] > 0) {
								$extrafields->attribute_type = $extrafields->attributes[$extrafieldkey]['type'] ?? array();
								$extrafields->attribute_size = $extrafields->attributes[$extrafieldkey]['size']?? array();
								$extrafields->attribute_unique = $extrafields->attributes[$extrafieldkey]['unique']?? array();
								$extrafields->attribute_required = $extrafields->attributes[$extrafieldkey]['required']?? array();
								$extrafields->attribute_label = $extrafields->attributes[$extrafieldkey]['label']?? array();
								$extrafields->attribute_default = $extrafields->attributes[$extrafieldkey]['default']?? array();
								$extrafields->attribute_computed = $extrafields->attributes[$extrafieldkey]['computed']?? array();
								$extrafields->attribute_param = $extrafields->attributes[$extrafieldkey]['param']?? array();
								$extrafields->attribute_perms = $extrafields->attributes[$extrafieldkey]['perms']?? array();
								$extrafields->attribute_langfile = $extrafields->attributes[$extrafieldkey]['langfile']?? array();
								$extrafields->attribute_list = $extrafields->attributes[$extrafieldkey]['list']?? array();
								$extrafields->attribute_hidden = $extrafields->attributes[$extrafieldkey]['hidden']?? array();
							}
						}
					}
					if($isStagiaireSocExtrafields) {
						foreach ($extralabels as $key_opt => $label_opt) {
							$extraKey = str_replace('stagiaire_soc_options_', '', $key);
							if($key_opt === $extraKey) {
								$val = $this->showOutputFieldValue($extrafields, $key_opt, $value);
								$array_other['object_' . $sub_element_label . $key] = $val;
							}
						}
					} else {
						foreach ($extralabels as $key_opt => $label_opt) {
							$array_other['object_options_' . $key_opt] = '';
							$array_other['object_array_options_options_' . $key_opt] = ''; // backward compatibility
							// Attention, ce test est différent d'un isset()
							if (is_array($object->array_options) && count($object->array_options) > 0 && array_key_exists('options_' . $key_opt, $object->array_options)) {
								$val = $this->showOutputFieldValue($extrafields, $key_opt, $object->array_options['options_' . $key_opt], '', $object->table_element);
								$array_other['object_options_' . $key_opt] = $val;
								$array_other['object_array_options_options_' . $key_opt] = $val;
							}
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
                    if($key== 'date_birth' || $key == 'datec') {
                        $value = dol_print_date($value,'%d/%m/%Y','tzserver',$outputlangs);
                    }
                    if (is_numeric($value) && strpos($key, 'certif_code') === false && strpos($key, 'zip') === false && strpos($key, 'phone') === false && strpos($key, 'cp') === false && strpos($key, 'idprof') === false && $key !== 'id' && $key !== 'convention_id')
						$value = price($value);

					// Fix display vars according object
					// actually showPublicOutputField doesn't exist in Dolibarr but I will probably create then for Dolibarr 12
	 				// So param will probably have different param so I created referenceletter_showPublicOutputField to prevent conflict
					$methodVariable = array($object, 'referenceletter_showPublicOutputField');
					if (is_callable($methodVariable, false, $callable_name)){
						if (method_exists($object, 'referenceletter_showPublicOutputField')) {
							$value = $object->referenceletter_showPublicOutputField($key, $value);
						}else{
							$value  = '';
						}
					}


					$array_other['object_' . $sub_element_label . $key] = $value;
				} elseif ($recursive && ! empty($value)) {
					$sub = strtr('object_' . $sub_element_label . $key, array('object_' . $sub_element_label => '')) . '_';
					$array_other = array_merge($array_other, $this->get_substitutionarray_each_var_object($value, $outputlangs, false, $sub));
				}
			}
		}

		return $array_other;
	}

	/**
	 * Override de la fonction ExtraFields::showOutputField()
	 *
	 * @param ExtraFields	$extrafields Extrafields Object
	 * @param string		$key Key
	 * @param mixed			$value Value
	 * @param string		$moreparam moreparam
	 * @param string		$extrafieldsobjectkey Extrafields keys
	 * @return string
	 * @throws Exception
	 */
	public function showOutputFieldValue($extrafields, $key, $value, $moreparam='', $extrafieldsobjectkey='')
	{
		global $conf,$langs;

		//TODO, Dolibarr deal it with diffrent way in commondocgenerator : why ?
		if (!empty($extrafieldsobjectkey))
		{
			$attributes = $extrafields->attributes[$extrafieldsobjectkey];

			$elementtype = !empty($attributes['elementtype'][$key]) ? $attributes['elementtype'][$key] : null; // semble non utilisé
			$label = !empty($attributes['label'][$key]) ? $attributes['label'][$key] : null;
			$type = !empty($attributes['type'][$key]) ? $attributes['type'][$key] : null;
			$size = !empty($attributes['size'][$key]) ? $attributes['size'][$key] : null;
			$default = !empty($attributes['default'][$key]) ? $attributes['default'][$key] : null;
			$computed = !empty($attributes['computed'][$key]) ? $attributes['computed'][$key] : null;
			$unique = !empty($attributes['unique'][$key]) ? $attributes['unique'][$key] : null;
			$required = !empty($attributes['required'][$key]) ? $attributes['required'][$key] : null;
			$param = !empty($attributes['param'][$key]) ? $attributes['param'][$key] : null;
			$perms = !empty($attributes['perms'][$key]) ? $attributes['perms'][$key] : null;
			$langfile = !empty($attributes['langfile'][$key]) ? $attributes['langfile'][$key] : null;
			$list = !empty($attributes['list'][$key]) ? $attributes['list'][$key] : null;
			$ishidden = !empty($attributes['ishidden'][$key]) ? $attributes['ishidden'][$key] : null;
			$hidden=(($list == 0) ? 1 : 0);		// If zero, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)


		}
		else
		{
			$elementtype=$extrafields->attribute_elementtype[$key] ?? '';	// seems not used
			$label=$extrafields->attribute_label[$key] ?? '';
			$type=$extrafields->attribute_type[$key] ?? '';
			$size=$extrafields->attribute_size[$key] ?? '';
			$default=$extrafields->attribute_default[$key] ?? '';
			$computed=$extrafields->attribute_computed[$key] ?? '';
			$unique=$extrafields->attribute_unique[$key] ?? '';
			$required=$extrafields->attribute_required[$key] ?? '';
			$param=$extrafields->attribute_param[$key] ?? '';
			$perms=$extrafields->attribute_perms[$key] ?? '';
			$langfile=$extrafields->attribute_langfile[$key] ?? '';
			$list=$extrafields->attribute_list[$key] ?? '';
			$ishidden=$extrafields->attribute_hidden[$key] ?? '';
			$hidden=(($list == 0)  ? 1 : 0);		// If zero, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)

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
			if (strpos($InfoFieldList[4] ?? '', 'extra')!==false)
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
			$showsize=round(floatval($size));
			if ($showsize > 48) $showsize=48;
		}

		//print $type.'-'.$size;
		$out=$value;

		return $out;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Fill array with couple extrafield key => extrafield value
	 *
	 *	@param  Object			$object				Object with extrafields (must have $object->array_options filled)
	 *	@param  array			$array_to_fill      Substitution array
	 *  @param  Extrafields		$extrafields        Extrafields object
	 *  @param  string			$array_key	        Prefix for name of the keys into returned array
	 *  @param  Translate		$outputlangs        Lang object to use for output
	 *	@return	array								Substitution array
	 */
	public function fill_substitutionarray_with_extrafields($object, $array_to_fill, $extrafields, $array_key, $outputlangs)
	{

		//Duplication of code until https://github.com/Dolibarr/dolibarr/pull/11794 is merge

		//TODO when dolibarr 13 wil lbe out, delete this and mark this module only comatible with dolibarr 10.0
		if(floatval(DOL_VERSION) >= 16) {
			if(!empty($object->table_element)
			    && is_array($extrafields->attributes[$object->table_element])
				&& is_array($extrafields->attributes[$object->table_element]['loaded'])
				&&   $extrafields->attributes[$object->table_element]['loaded'] > 0) {
                		$extrafields->attribute_type = $extrafields->attribute_param = $extrafields->attribute_size = $extrafields->attrbute_unique = $extrafields->attribute_required = $extrafields->attribute_label = array();
                		$extrafields->attribute_type = $extrafields->attributes[$object->table_element]['type'] ?? array();
				$extrafields->attribute_size = $extrafields->attributes[$object->table_element]['size'] ?? array();
				$extrafields->attribute_unique = $extrafields->attributes[$object->table_element]['unique'] ?? array();
				$extrafields->attribute_required = $extrafields->attributes[$object->table_element]['required'] ?? array();
				$extrafields->attribute_label = $extrafields->attributes[$object->table_element]['label'] ?? array();
				$extrafields->attribute_default = $extrafields->attributes[$object->table_element]['default'] ?? array();
				$extrafields->attribute_computed = $extrafields->attributes[$object->table_element]['computed'] ?? array();
				$extrafields->attribute_param = $extrafields->attributes[$object->table_element]['param'] ?? array();
				$extrafields->attribute_perms = $extrafields->attributes[$object->table_element]['perms'] ?? array();
				$extrafields->attribute_langfile = $extrafields->attributes[$object->table_element]['langfile'] ?? array();
				$extrafields->attribute_list = $extrafields->attributes[$object->table_element]['list'] ?? array();
				$extrafields->attribute_hidden = $extrafields->attributes[$object->table_element]['hidden'] ?? array();
			}
		}
		// phpcs:enable
		global $conf;
		if (isset($extrafields->attribute_label) && !empty($extrafields->attribute_label)) {
			// La propriété est définie et n'est pas vide
			if (!empty($extrafields->attribute_label && is_array($extrafields->attribute_label))){
				foreach($extrafields->attribute_label as $key=>$label)
				{
					if($extrafields->attribute_type[$key] == 'price')
					{
						$object->array_options['options_'.$key] = price2num($object->array_options['options_'.$key]);
						$object->array_options['options_'.$key.'_currency'] = price($object->array_options['options_'.$key], 0, $outputlangs, 0, 0, -1, $conf->currency);
						//Add value to store price with currency
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_currency' => $object->array_options['options_'.$key.'_currency']));
					}
					elseif($extrafields->attribute_type[$key] == 'select')
					{
						$object->array_options['options_'.$key] = $extrafields->attribute_param[$key]['options'][$object->array_options['options_'.$key]];
					}
					elseif($extrafields->attribute_type[$key] == 'checkbox') {
						$valArray=explode(',', $object->array_options['options_'.$key]);
						$output=array();
						if (is_array($extrafields->attribute_param[$key]['options'])){
							foreach($extrafields->attribute_param[$key]['options'] as $keyopt=>$valopt) {
								if  (in_array($keyopt, $valArray)) {
									$output[]=$valopt;
								}
							}
						}

						$object->array_options['options_'.$key] = implode(', ', $output);
					}
					elseif($extrafields->attribute_type[$key] == 'date')
					{
						if (strlen($object->array_options['options_'.$key])>0)
						{
							$date = $object->array_options['options_'.$key];
							$object->array_options['options_'.$key] = dol_print_date($date, 'day');                                       // using company output language
							$object->array_options['options_'.$key.'_locale'] = dol_print_date($date, 'day', 'tzserver', $outputlangs);     // using output language format
							$object->array_options['options_'.$key.'_rfc'] = dol_print_date($date, 'dayrfc');                             // international format
						}
						else
						{
							$object->array_options['options_'.$key] = '';
							$object->array_options['options_'.$key.'_locale'] = '';
							$object->array_options['options_'.$key.'_rfc'] = '';
						}
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_locale' => $object->array_options['options_'.$key.'_locale']));
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_rfc' => $object->array_options['options_'.$key.'_rfc']));
					}
					elseif($extrafields->attribute_type[$key] == 'datetime')
					{
						$datetime = $object->array_options['options_'.$key];
						$object->array_options['options_'.$key] = ($datetime!="0000-00-00 00:00:00"?dol_print_date($object->array_options['options_'.$key], 'dayhour'):'');                            // using company output language
						$object->array_options['options_'.$key.'_locale'] = ($datetime!="0000-00-00 00:00:00"?dol_print_date($object->array_options['options_'.$key], 'dayhour', 'tzserver', $outputlangs):'');    // using output language format
						$object->array_options['options_'.$key.'_rfc'] = ($datetime!="0000-00-00 00:00:00"?dol_print_date($object->array_options['options_'.$key], 'dayhourrfc'):'');                             // international format
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_locale' => $object->array_options['options_'.$key.'_locale']));
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_rfc' => $object->array_options['options_'.$key.'_rfc']));
					}
					elseif($extrafields->attribute_type[$key] == 'link')
					{
						$id = $object->array_options['options_'.$key];
						if ($id != "")
						{
							$param = $extrafields->attribute_param[$key];
							if(!empty($param['options'])){
								$param_list=array_keys($param['options']);              // $param_list='ObjectName:classPath'
								$InfoFieldList = explode(":", $param_list[0]);
								$classname=$InfoFieldList[0];
								$classpath=$InfoFieldList[1];
								if (! empty($classpath))
								{
									dol_include_once($InfoFieldList[1]);
									if ($classname && class_exists($classname))
									{
										$tmpobject = new $classname($this->db);
										$tmpobject->fetch($id);
										// completely replace the id with the linked object name
										$object->array_options['options_'.$key] = $tmpobject->name;
									}
								}
							}

						}
					}
					elseif($extrafields->attribute_type[$key] == 'sellist') {
						$object->array_options['options_'.$key] = $this->showOutputFieldValue($extrafields, $key, $object->array_options['options_'.$key]);
					}
					elseif($extrafields->attribute_type[$key] == 'chkbxlst')
					{
						$object->array_options['options_'.$key] = $this->showOutputFieldValue($extrafields, $key, $object->array_options['options_'.$key]);
					}

					$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key => $object->array_options['options_'.$key]));
				}
			}
		}

		// Ajout des extrafields des object coeurs dans la selection des substitutions
		if ($object->table_element != null) {
			if (!empty($extrafields->attributes[$object->table_element])
				&& array_key_exists('label', $extrafields->attributes[$object->table_element])
				&& is_array($extrafields->attributes[$object->table_element]['label'])
				&& $extrafields->attributes[$object->table_element]['label'] !== null
				&& !empty($extrafields->attributes[$object->table_element]['label'])) {

				foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
					// Add value to store
					if (array_key_exists('options_'.$key, $object->array_options)) {
						$array_to_fill = array_merge($array_to_fill, array($array_key.'_options_'.$key => $object->array_options['options_'.$key]));
					}
				}
			}
		}
		return $array_to_fill;
	}
}
