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
dol_include_once('/agefodd/class/agefodd_place.class.php');

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
	 * Resolve a display label for a linked object loaded from an extrafield.
	 *
	 * @param object $object
	 * @return string
	 */
	protected function resolveLinkedExtraFieldObjectLabel(object $object): string
	{
		$properties = array('name', 'ref', 'label', 'title', 'lastname', 'firstname');
		foreach ($properties as $property) {
			if (property_exists($object, $property) && is_scalar($object->{$property}) && trim((string) $object->{$property}) !== '') {
				return trim((string) $object->{$property});
			}
		}

		if (
			property_exists($object, 'lastname')
			&& property_exists($object, 'firstname')
			&& (is_scalar($object->lastname) || is_scalar($object->firstname))
		) {
			$fullName = trim(((string) $object->firstname) . ' ' . ((string) $object->lastname));
			if ($fullName !== '') {
				return $fullName;
			}
		}

		return '';
	}

	/**
	 * Resolve configured Agefodd mentors once per request.
	 *
	 * @param Translate $langs
	 * @return array{list:string,items:array<string,string>}
	 */
	protected function getAgefoddMentorSubstitutions(Translate $langs): array
	{
		static $mentorUserCache = array();

		$mentorConfigMap = array(
			'Mentor_administrator' => array('const' => 'AGF_DEFAULT_MENTOR_ADMIN', 'label' => 'MentorAdmin'),
			'Mentor_pedagogique' => array('const' => 'AGF_DEFAULT_MENTOR_PEDAGO', 'label' => 'MentorPedago'),
			'Mentor_handicap' => array('const' => 'AGF_DEFAULT_MENTOR_HANDICAP', 'label' => 'MentorHandicap'),
		);

		$result = array(
			'list' => '',
			'items' => array(
				'Mentor_administrator' => '',
				'Mentor_pedagogique' => '',
				'Mentor_handicap' => '',
			),
		);

		$mentorList = array();
		foreach ($mentorConfigMap as $tag => $mentorConfig) {
			$mentorId = (int) getDolGlobalInt($mentorConfig['const']);
			if ($mentorId <= 0) {
				continue;
			}

			if (!array_key_exists($mentorId, $mentorUserCache)) {
				$mentorUserCache[$mentorId] = '';
				$localUser = new User($this->db);
				$fetchResult = $localUser->fetch($mentorId);
				if ($fetchResult > 0) {
					$civility = trim((string) $localUser->civility_code);
					$fullName = trim(($civility !== '' ? $civility . ' ' : '') . $localUser->firstname . ' ' . $localUser->lastname);
					if ($fullName !== '') {
						$mentorUserCache[$mentorId] = $fullName;
					}
				} else {
					dol_syslog(
						__METHOD__ . ' mentor constant ' . $mentorConfig['const'] . ' points to missing or unreadable user #' . $mentorId,
						LOG_WARNING
					);
				}
			}

			$fullName = $mentorUserCache[$mentorId];
			if ($fullName === '') {
				continue;
			}

			$result['items'][$tag] = ucfirst($langs->trans($mentorConfig['label']) . ' : ' . $fullName);
			$mentorList[] = $fullName;
		}

		$result['list'] = implode(', ', $mentorList);

		return $result;
	}

    /**
     * @var string[]    Array of error strings
     */
    public $errors = array();

	/**
	 * Return a scalar property value when available.
	 *
	 * @param object $object Source object.
	 * @param string $property Property name.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function getObjectPropertyValue(object $object, string $property, string $default = ''): string
	{
		if (!property_exists($object, $property) || $object->{$property} === null) {
			return $default;
		}

		return is_scalar($object->{$property}) ? (string) $object->{$property} : $default;
	}

	/**
	 * Return a nested property value when both levels exist.
	 *
	 * @param object $object Source object.
	 * @param string $property Parent property.
	 * @param string $nestedProperty Nested property name.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function getNestedObjectPropertyValue(object $object, string $property, string $nestedProperty, string $default = ''): string
	{
		if (!property_exists($object, $property) || !is_object($object->{$property})) {
			return $default;
		}

		return $this->getObjectPropertyValue($object->{$property}, $nestedProperty, $default);
	}

	/**
	 * Return a formatted date when the property exists.
	 *
	 * @param object $object Source object.
	 * @param string $property Date property.
	 * @param string $format Dolibarr date format key.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function getFormattedDatePropertyValue(object $object, string $property, string $format = 'day', string $default = ''): string
	{
		if (!property_exists($object, $property) || empty($object->{$property})) {
			return $default;
		}

		return dol_print_date($object->{$property}, $format);
	}

	/**
	 * Return a status label from a line dictionary.
	 *
	 * @param object $object Source object.
	 * @param string $statusProperty Status property name.
	 * @param string $labelsProperty Labels property name.
	 * @param string $default Default value.
	 * @return string
	 */
	protected function getStatusLabelValue(object $object, string $statusProperty, string $labelsProperty, string $default = ''): string
	{
		if (!property_exists($object, $statusProperty)
			|| !property_exists($object, $labelsProperty)
			|| !is_array($object->{$labelsProperty})) {
			return $default;
		}

		$status = $object->{$statusProperty};
		if ($status === null || !isset($object->{$labelsProperty}[$status])) {
			return $default;
		}

		return (string) $object->{$labelsProperty}[$status];
	}

	/**
	 * Keep an existing substitution value unless a non-empty override is provided.
	 *
	 * @param array $resarray Current substitutions array.
	 * @param string $key Target substitution key.
	 * @param mixed $candidate Preferred replacement value.
	 * @return array
	 */
	protected function setPreferredAgefoddValue(array $resarray, $key, $candidate)
	{
		if (!empty($candidate)) {
			$resarray[$key] = $candidate;
		} elseif (!isset($resarray[$key])) {
			$resarray[$key] = '';
		}

		return $resarray;
	}

	/**
	 * Apply the preferred Agefodd formation references with explicit precedence.
	 * Session-level references stay authoritative; catalogue references are fallbacks.
	 *
	 * @param array $resarray Current substitutions array.
	 * @param object|null $catalogue Formation/session catalogue clone when available.
	 * @return array
	 */
	protected function applyAgefoddFormationReferenceFallbacks(array $resarray, $catalogue = null)
	{
		if (is_object($catalogue)) {
			$resarray = $this->setPreferredAgefoddValue($resarray, 'formation_ref', $catalogue->ref_obj ?? '');
			$resarray = $this->setPreferredAgefoddValue($resarray, 'formation_refint', $catalogue->ref_interne ?? '');
		}

		if (!isset($resarray['formation_ref'])) {
			$resarray['formation_ref'] = '';
		}
		if (!isset($resarray['formation_refint'])) {
			$resarray['formation_refint'] = '';
		}

		return $resarray;
	}

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
	 * Override thirdparty substitutions to ensure thirdparty extrafields are always loaded
	 * on the ReferenceLetters runtime, without relying on a pre-populated global $extrafields.
	 *
	 * @param Societe $object
	 * @param Translate $outputlangs
	 * @param string $array_key
	 * @return array
	 */
	public function get_substitutionarray_thirdparty($object, $outputlangs, $array_key = 'company')
	{
		$arrayThirdparty = parent::get_substitutionarray_thirdparty($object, $outputlangs, $array_key);

		if (!is_object($object)) {
			return $arrayThirdparty;
		}

		if (!is_array($object->array_options)) {
			$object->array_options = array();
		}
		if (method_exists($object, 'fetch_optionals')) {
			$object->fetch_optionals();
		}

		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$extrafields->fetch_name_optionals_label($object->table_element, true);

		return $this->fill_substitutionarray_with_extrafields($object, $arrayThirdparty, $extrafields, $array_key, $outputlangs);
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
		$resarray['devise_label'] = '';
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
		$multicurrencyEnabled = !empty($conf->multicurrency) && !empty($conf->multicurrency->enabled);

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

				$useMulticurrency = $multicurrencyEnabled && !empty($object->multicurrency_tx) && $object->multicurrency_tx != 1;
				$deja_regle = 0;
				$creditnoteamount = 0;
				$depositsamount = 0;
				$array_other['deja_paye'] = $array_other['somme_avoirs'] = price(0, 0, $outputlangs);
				$total_ttc = $useMulticurrency ? $object->multicurrency_total_ttc : $object->total_ttc;
				$array_other['liste_paiements'] = self::get_liste_reglements($object, $outputlangs);
				if (! empty($array_other['liste_paiements'])) {

					$deja_regle = $object->getSommePaiement($useMulticurrency ? 1 : 0);
					$creditnoteamount = $object->getSumCreditNotesUsed($useMulticurrency ? 1 : 0);
					$depositsamount = $object->getSumDepositsUsed($useMulticurrency ? 1 : 0);

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
		$multicurrencyEnabled = !empty($conf->multicurrency) && !empty($conf->multicurrency->enabled);

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
					$vatrate = isset($line->tva_tx) ? $line->tva_tx : 0;

				// Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
				if (get_class($object) === 'Facture') {
					$prev_progress = $line->get_prev_progress($object->id);
					if ($prev_progress > 0 && ! empty($line->situation_percent)) // Compute progress from previous situation
					{
						if ($multicurrencyEnabled && $object->multicurrency_tx != 1)
							$tvaligne = $sign * $line->multicurrency_total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
						else
							$tvaligne = $sign * $line->total_tva * ($line->situation_percent - $prev_progress) / $line->situation_percent;
					} else {
						if ($multicurrencyEnabled && $object->multicurrency_tx != 1)
							$tvaligne = $sign * $line->multicurrency_total_tva;
						else
							$tvaligne = $sign * $line->total_tva;
					}
				} else {
					if ($multicurrencyEnabled && $object->multicurrency_tx != 1)
						$tvaligne = $line->multicurrency_total_tva;
					else
						$tvaligne = $line->total_tva;
				}

				if (!empty($object->remise_percent))
					$tvaligne -= ($tvaligne * $object->remise_percent) / 100;
				if(empty($TTva[$langs->trans('TotalVAT'). ' ' . round($vatrate, 2) . '%'])) $TTva[$langs->trans('TotalVAT'). ' ' . round($vatrate, 2) . '%'] = 0;
				$TTva[$langs->trans('TotalVAT'). " " . round($vatrate, 2) . '%'] += $tvaligne;
			}
		}
		unset($line);

		// formatage sortie
		foreach ( $TTva as $k => &$v )
			$v = price($v);
		unset($v);

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
		$multicurrencyEnabled = !empty($conf->multicurrency) && !empty($conf->multicurrency->enabled);

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
				$amount = price(($multicurrencyEnabled && $object->multicurrency_tx != 1) ? $obj->multicurrency_amount_ttc : $obj->amount_ttc, 0, $outputlangs);
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
			if ($object->type == 2 && getDolGlobalString('INVOICE_POSITIVE_CREDIT_NOTE'))
				$sign = - 1;
			while ( $row = $db->fetch_object($resql) ) {
				$date = dol_print_date($db->jdate($row->date), 'day', false, $outputlangs, true);
				$amount = price($sign * (($multicurrencyEnabled && $object->multicurrency_tx != 1) ? $row->multicurrency_amount : $row->amount), 0, $outputlangs);
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

		$resarray = parent::get_substitutionarray_lines($line, $outputlangs, 0);

		$resarray['line_product_ref_fourn'] = isset($line->ref_fourn) ? $line->ref_fourn : ''; // for supplier doc lines
		$resarray['line_rang'] = isset($line->rang) ? $line->rang : '';
		$resarray['line_libelle'] = isset($line->libelle) ? $line->libelle : ''; // récupére le libellé du produit/service
		if (empty($resarray['line_product_label']) && isset($line->label)) $resarray['line_product_label'] = $line->label;

		if(empty($resarray['line_desc']) && ! empty($conf->subtotal->enabled))
		{
			dol_include_once('/subtotal/class/subtotal.class.php');

			if(TSubtotal::isTitle($line) && ! empty($line->label))
			{
				$resarray['line_desc'] = $line->label;
			}
		}

		$resarray['date_ouverture'] = property_exists($line, 'date_ouverture') ? dol_print_date($line->date_ouverture, 'day', 'tzuser') : '';
		$resarray['date_ouverture_prevue'] = property_exists($line, 'date_ouverture_prevue') ? dol_print_date($line->date_ouverture_prevue, 'day', 'tzuser') : '';
		$resarray['date_fin_validite'] = property_exists($line, 'date_fin_validite') ? dol_print_date($line->date_fin_validite, 'day', 'tzuser') : '';
		$lineQtyShipped = property_exists($line, 'qty_shipped') ? $line->qty_shipped : '';
		$lineQtyAsked = property_exists($line, 'qty_asked') ? $line->qty_asked : '';
		if (empty($resarray['line_qty_shipped']) && $lineQtyShipped !== '') $resarray['line_qty_shipped'] = price2num($lineQtyShipped);
		if (empty($resarray['line_qty_asked']) && $lineQtyAsked !== '') $resarray['line_qty_asked'] = price2num($lineQtyAsked);
		if(empty($resarray['line_weight'])) $resarray['line_weight'] = price2num($line->weight);
		if(empty($resarray['line_vol'])) $resarray['line_vol'] = price2num($line->volume);

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
        $resarray['line_poste'] = $this->getObjectPropertyValue($line, 'poste');
        $resarray['line_civilite'] = $this->getObjectPropertyValue($line, 'civilitel');
        $resarray['line_civilite_short'] = $this->getObjectPropertyValue($line, 'civilite');
        $resarray['line_nom'] = $this->getObjectPropertyValue($line, 'nom');
        $resarray['line_prenom'] = $this->getObjectPropertyValue($line, 'prenom');
        $resarray['line_type'] = $this->getObjectPropertyValue($line, 'type');
        $resarray['line_birthday'] = $this->getFormattedDatePropertyValue($line, 'date_birth');
		$resarray['line_statut'] = property_exists($line, 'status_in_session') ? $sessionStag->LibStatut($line->status_in_session) : '';
		$resarray['line_place_birth'] = $this->getObjectPropertyValue($line, 'place_birth');
		$resarray['line_birthdayformated'] = $this->getObjectPropertyValue($line, 'datebirthformated');
		$linePhonePro = $this->getObjectPropertyValue($line, 'tel1');
		$linePhoneMobile = $this->getObjectPropertyValue($line, 'tel2');
		$tel = $linePhonePro;
		if (empty($tel) && !empty($linePhoneMobile)) {
			$tel = $linePhoneMobile;
		} else {
			$tel = $linePhonePro.(!empty($linePhoneMobile) ? '/'.$linePhoneMobile : "");
		}
		$resarray['line_phone'] = $tel;
		$resarray['line_phone_pro'] = $linePhonePro;
		$resarray['line_phone_mobile'] = $linePhoneMobile;
		$resarray['line_email'] = $this->getObjectPropertyValue($line, 'email');
		$resarray['line_siret'] = $this->getNestedObjectPropertyValue($line, 'thirdparty', 'idprof2');
		$resarray['line_birthplace'] = $this->getObjectPropertyValue($line, 'place_birth');
		$resarray['line_code_societe'] = $this->getObjectPropertyValue($line, 'soccode');
		$resarray['line_nom_societe'] = $this->getObjectPropertyValue($line, 'socname');
		$lineStageRowId = property_exists($line, 'stagerowid') ? (int) $line->stagerowid : 0;
		$resarray['line_financiers_trainee'] = $lineStageRowId > 0 ? Agefodd_session_stagiaire::getFinanciersByTrainee($lineStageRowId) : '';
		$resarray['line_alternate_financier_trainee'] = $lineStageRowId > 0 ? Agefodd_session_stagiaire::getAlternateFinancierByTrainee($lineStageRowId) : '';
		$resarray['line_stagiaire_presence_bloc'] = $this->getObjectPropertyValue($line, 'stagiaire_presence_bloc');
		$resarray['line_stagiaire_presence_total'] = $this->getObjectPropertyValue($line, 'stagiaire_presence_total');
		$resarray['line_time_stagiaire_temps_realise_total'] = $this->getObjectPropertyValue($line, 'time_stagiaire_temps_realise_total');
		$resarray['line_stagiaire_temps_realise_total'] = $this->getObjectPropertyValue($line, 'stagiaire_temps_realise_total');
		$resarray['line_time_stagiaire_temps_att_total'] = $this->getObjectPropertyValue($line, 'time_stagiaire_temps_att_total');
		$resarray['line_stagiaire_temps_att_total'] = $this->getObjectPropertyValue($line, 'stagiaire_temps_att_total');
		$resarray['line_time_stagiaire_temps_realise_att_total'] = $this->getObjectPropertyValue($line, 'time_stagiaire_temps_realise_att_total');
		$resarray['line_stagiaire_temps_realise_att_total'] = $this->getObjectPropertyValue($line, 'stagiaire_temps_realise_att_total');
		$hasTraineeThirdparty = is_object($line)
			&& property_exists($line, 'agefodd_stagiaire')
			&& is_object($line->agefodd_stagiaire)
			&& property_exists($line->agefodd_stagiaire, 'thirdparty')
			&& is_object($line->agefodd_stagiaire->thirdparty);
		if(!$hasTraineeThirdparty) { //Retro compat < 2.17
			$resarray['line_societe_address'] = $this->getObjectPropertyValue($line, 'societe_address');
			$resarray['line_societe_zip'] = $this->getObjectPropertyValue($line, 'societe_zip');
			$resarray['line_societe_town'] = $this->getObjectPropertyValue($line, 'societe_town');
		}
		else {
			$resarray['line_societe_address'] = $this->getNestedObjectPropertyValue($line->agefodd_stagiaire, 'thirdparty', 'address');
			$resarray['line_societe_zip'] = $this->getNestedObjectPropertyValue($line->agefodd_stagiaire, 'thirdparty', 'zip');
			$resarray['line_societe_town'] = $this->getNestedObjectPropertyValue($line->agefodd_stagiaire, 'thirdparty', 'town');
			$resarray['line_societe_mail'] = $this->getNestedObjectPropertyValue($line->agefodd_stagiaire, 'thirdparty', 'email');
			$extrafields = new ExtraFields($this->db);
			$extrafields->fetch_name_optionals_label($line->agefodd_stagiaire->thirdparty->element, true);
			$resarray = $this->fill_substitutionarray_with_extrafields($line->agefodd_stagiaire->thirdparty, $resarray, $extrafields, 'line_societe', $langs);
		}
		$resarray['line_presence_bloc'] = '';
		$resarray['line_presence_total'] = '';
		$resarray['line_certif_code'] = '';
		$resarray['line_certif_label'] = '';
		$resarray['line_certif_date_debut'] = '';
		$resarray['line_certif_date_fin'] = '';
		$resarray['line_certif_date_alerte'] = '';

		if($conf->agefoddcertificat->enabled) {
			// Certificats
			dol_include_once('/agefoddcertificat/class/agefoddcertificat.class.php');
			$agf_certif = new AgefoddCertificat($db);
			$lineSessionId = property_exists($line, 'sessid') ? (int) $line->sessid : 0;
			$TCertif = ($lineSessionId > 0 && !empty($line->id))
				? $agf_certif->fetchAll('', '', 0, 0, array('fk_trainee' => $line->id, 'fk_session' => $lineSessionId, 'isDeleted' => 0))
				: array();
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
		$resarray['line_date_session'] = $this->getFormattedDatePropertyValue($line, 'date_session');
		$resarray['line_heure_debut_session'] = $this->getFormattedDatePropertyValue($line, 'heured', 'hour');
		$resarray['line_heure_fin_session'] = $this->getFormattedDatePropertyValue($line, 'heuref', 'hour');

		// Substitutions tableau des formateurs :
		$resarray['line_formateur_nom'] = $this->getObjectPropertyValue($line, 'lastname');
		$resarray['line_formateur_prenom'] = $this->getObjectPropertyValue($line, 'firstname');
		$resarray['line_formateur_phone'] = $this->getObjectPropertyValue($line, 'phone');
		$resarray['line_formateur_phone_mobile'] = $this->getObjectPropertyValue($line, 'phone_mobile');
		$resarray['line_formateur_phone_perso'] = $this->getObjectPropertyValue($line, 'phone_perso');
		$resarray['line_formateur_mail'] = $this->getObjectPropertyValue($line, 'email');
		$resarray['line_formateur_socname'] =  $this->getObjectPropertyValue($line, 'socname');
		$resarray['line_formateur_address'] = $this->getObjectPropertyValue($line, 'address');
		$resarray['line_formateur_town'] = $this->getObjectPropertyValue($line, 'town');
		$resarray['line_formateur_zip'] = $this->getObjectPropertyValue($line, 'zip');
		$resarray['line_formateur_statut'] = $this->getStatusLabelValue($line, 'trainer_status', 'labelstatut');

		// Substitutions tableau des objectif :
		$resarray['line_objpeda_rang'] = $this->getObjectPropertyValue($line, 'priorite');
		$resarray['line_objpeda_description'] = $this->getObjectPropertyValue($line, 'intitule');

		// Substitutions modules de formation :
		$resarray['line_module_title'] = $this->getObjectPropertyValue($line, 'title');
		$resarray['line_module_duration'] = $this->getObjectPropertyValue($line, 'duration');
		$resarray['line_module_obj_peda'] = $this->getObjectPropertyValue($line, 'obj_peda');
		$resarray['line_module_content_text'] = $this->getObjectPropertyValue($line, 'content_text');

		// Substitutions tableau des élément financier :
//		$resarray['line_fin_desciption'] = str_replace('<br />', "\n", str_replace('<BR>', "\n", $line->description));

		//strip_tags permet de supprimer les balises HTML et PHP d'une chaine, la mise en forme faisait disparaître une partie du pdf de convention docedit
        $resarray['line_fin_desciption'] = strip_tags((string) $this->getObjectPropertyValue($line, 'description'), "<br><p><ul><ol><li><span><div><tr><td><th><table>");
//		$resarray['line_fin_desciption_light'] = $line->form_label;
		$resarray['line_fin_desciption_light_short'] = $this->getObjectPropertyValue($line, 'form_label_short');
		$resarray['line_fin_qty'] = $this->getObjectPropertyValue($line, 'qty');
		$resarray['line_fin_tva_tx'] = property_exists($line, 'tva_tx') ? vatrate($line->tva_tx, 1) : '';
		$resarray['line_fin_amount_ht'] = property_exists($line, 'total_ht') ? price($line->total_ht, 0, $outputlangs, 1, - 1, 2) : '';
		$resarray['line_fin_amount_ttc'] = property_exists($line, 'total_ttc') ? price($line->total_ttc, 0, $outputlangs, 1, - 1, 2) : '';
		$resarray['line_fin_discount'] = property_exists($line, 'remise_percent') ? dol_print_reduction($line->remise_percent, $outputlangs) : '';
		$resarray['line_fin_pu_ht'] = property_exists($line, 'price') ? price($line->price, 0, $outputlangs, 1, - 1, 2) : '';

		// Retrieve extrafields
		$extrafieldkey = $this->getObjectPropertyValue($line, 'element');
		$array_key = "line";
		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$extralabels = $extrafields->fetch_name_optionals_label($extrafieldkey, true);
		if ($fetchoptionnals) {
			$line->fetch_optionals($line->rowid);
		}

		if(getDolGlobalInt('AGF_USE_STEPS')){
			$resarray['line_step_label'] = $this->getObjectPropertyValue($line, 'label');
			$resarray['line_step_date_start'] = $this->getFormattedDatePropertyValue($line, 'date_start', 'day');
			$resarray['line_step_date_end'] = $this->getFormattedDatePropertyValue($line, 'date_end', 'day');
			$resarray['line_step_duration'] = property_exists($line, 'duration') && $line->duration !== null ? $line->duration : '';
			// Lieu
			$resarray['line_step_lieu'] = strip_tags((string) $this->getNestedObjectPropertyValue($line, 'place', 'ref_interne'));
			$resarray['line_step_lieu_adresse'] = strip_tags((string) $this->getNestedObjectPropertyValue($line, 'place', 'adresse'));
			$resarray['line_step_lieu_cp'] = strip_tags((string) $this->getNestedObjectPropertyValue($line, 'place', 'cp'));
			$resarray['line_step_lieu_ville'] = strip_tags((string) $this->getNestedObjectPropertyValue($line, 'place', 'ville'));
			$resarray['line_step_lieu_acces'] = str_replace('&amp;', '&', (string) $this->getNestedObjectPropertyValue($line, 'place', 'acces_site'));
			$resarray['line_step_lieu_horaires'] = strip_tags((string) $this->getNestedObjectPropertyValue($line, 'place', 'timeschedule'));
			$resarray['line_step_lieu_notes'] = strip_tags((string) $this->getNestedObjectPropertyValue($line, 'place', 'notes'));
			$resarray['line_step_lieu_divers'] = $this->getNestedObjectPropertyValue($line, 'place', 'note1');
		}


		if (property_exists($line, 'agefodd_stagiaire') && !empty($line->agefodd_stagiaire) && is_object($line->agefodd_stagiaire)) {
			$extrafields = new ExtraFields($this->db);
			$extralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);
			if (!is_array($line->array_options)) {
				$line->array_options = array();
			}
			if (is_array($line->agefodd_stagiaire->array_options)) {
				foreach ($line->agefodd_stagiaire->array_options as $keyOption => $valueOption) {
					if (!array_key_exists($keyOption, $line->array_options) || $line->array_options[$keyOption] === '') {
						$line->array_options[$keyOption] = $valueOption;
					}
				}
			}
		}

		$resarray = $this->fill_substitutionarray_with_extrafields($line, $resarray, $extrafields, $array_key, $outputlangs);

		// Appel de la fonction parente pour les lignes des documents std dolibarr (propal, cmd, facture, contrat)
		$arrayTypeObj=array('PropaleLigne','OrderLine','FactureLigne','ContratLigne','CommandeFournisseurLigne','ExpeditionLigne');
		if (in_array(get_class($line),$arrayTypeObj)) {
			$resarray = parent::get_substitutionarray_lines($line, $outputlangs, 0);
			$resarray['line_rang'] = $line->rang;
		}
		$resarray['line_unit'] = (method_exists($line, 'getLabelOfUnit')) ? $langs->trans($line->getLabelOfUnit('short')) : '';
		if (get_class($line)=='ExpeditionLigne') {
			$lineQtyShipped = property_exists($line, 'qty_shipped') ? (float) $line->qty_shipped : 0.0;
			$lineQtyAsked = property_exists($line, 'qty_asked') ? $line->qty_asked : '';
			$weighttxt = '';
			if ($line->fk_product_type == 0 && $line->weight)
			{
				$weighttxt = round($line->weight * $lineQtyShipped, 5).' '.measuringUnitString(0, "weight", $line->weight_units, 1);
			}
			$voltxt = '';
			if ($line->fk_product_type == 0 && $line->volume)
			{
				$voltxt = round($line->volume * $lineQtyShipped, 5).' '.measuringUnitString(0, "volume", $line->volume_units ? $line->volume_units : 0, 1);
			}
			$resarray['line_weight'] =$weighttxt;
			$resarray['line_vol'] =$voltxt;
			$resarray['line_qty_asked'] = $lineQtyAsked;
			$resarray['line_qty_shipped'] = $lineQtyShipped;
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
		$resarray['formation_id'] = $object->id ?? '';
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
		$resarray['formation_type_stagiaire']=$object->public;
		$resarray['formation_methode_pedago']=$object->methode;
		$resarray['formation_documents']=$object->note1;
		$resarray['formation_equipements']=$object->note2;
		$resarray['formation_pre_requis']=$object->prerequis;
		$resarray['formation_prerequis']=$object->prerequis;
		$resarray['formation_programme']=$object->programme;
		$resarray['formation_moyens_peda']=$object->pedago_usage;
		$resarray['formation_moyens_pedagogique']=$object->pedago_usage;
		$resarray['formation_sanction']=$object->sanction;
		$resarray['formation_nature']= $formAgefodd->select_formation_nature_action($object->fk_nature_action_code, '', '', '', '', 'view');
		$resarray['formation_Accessibility_Handicap']=$object->accessibility_handicap == 0 ? 'Non' : 'Oui';
		$arrpeda= explode(',', $object->formation_obj_peda ?? '');
		$tmp="";
		foreach ($arrpeda as $peda) {
			$tmp .= $peda . "<br>";
		}
		$resarray['formation_competences']=$tmp;

		$mentorSubstitutions = $this->getAgefoddMentorSubstitutions($langs);
		$resarray['Mentor_administrator'] = $mentorSubstitutions['items']['Mentor_administrator'];
		$resarray['Mentor_pedagogique'] = $mentorSubstitutions['items']['Mentor_pedagogique'];
		$resarray['Mentor_handicap'] = $mentorSubstitutions['items']['Mentor_handicap'];
		$resarray['AgfMentorList'] = $mentorSubstitutions['list'];
   		// -----------

			$e = new ExtraFields($db);
			$e->fetch_name_optionals_label($object->table_element);
			if(floatval(DOL_VERSION) >= 16) {
				$e->attribute_type = $e->attribute_param = $e->attribute_size = $e->attribute_unique = $e->attribute_required = $e->attribute_label = array();
				$attributeSource = is_array($e->attributes ?? null) && is_array($e->attributes[$object->table_element] ?? null)
					? $e->attributes[$object->table_element]
					: array();
				if (($attributeSource['loaded'] ?? 0) > 0) {
					$e->attribute_type = $attributeSource['type'] ?? array();
					$e->attribute_size = $attributeSource['size'] ?? array();
					$e->attribute_unique = $attributeSource['unique'] ?? array();
					$e->attribute_required = $attributeSource['required'] ?? array();
					$e->attribute_label = $attributeSource['label'] ?? array();
					$e->attribute_default = $attributeSource['default'] ?? array();
					$e->attribute_computed = $attributeSource['computed'] ?? array();
					$e->attribute_param = $attributeSource['param'] ?? array();
					$e->attribute_perms = $attributeSource['perms'] ?? array();
					$e->attribute_langfile = $attributeSource['langfile'] ?? array();
					$e->attribute_list = $attributeSource['list'] ?? array();
					$e->attribute_hidden = $attributeSource['hidden'] ?? array();
				}
			}
			$object->fetch_optionals();
			$resarray = $this->fill_substitutionarray_with_extrafields($object, $resarray, $e, 'formation', $langs);

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

		$agfStep = null;

		dol_include_once('/agefodd/class/html.formagefodd.class.php');
		dol_include_once('/societe/class/societe.class.php');

		$fk_step = intval(GETPOST('fk_step', 'int'));
		if ($fk_step <= 0) {
			$fk_step = (int) $this->getObjectPropertyValue($object, 'fk_step');
		}
		if($fk_step > 0) {
			$agfStep = new Agefodd_step($this->db);
			$agfStep->fetch($fk_step);
		}

		$formAgefodd = new FormAgefodd($db);

		$resarray = array();
		$formationName = $this->getObjectPropertyValue($object, 'formintitule');
		if ($formationName === '') {
			$formationName = $this->getObjectPropertyValue($object, 'intitule');
		}
		$formationCustomName = $this->getObjectPropertyValue($object, 'intitule_custo');
		if ($formationCustomName === '') {
			$formationCustomName = $formationName;
		}
		$formationRef = $this->getObjectPropertyValue($object, 'formref');
		$formationRefInterne = $this->getObjectPropertyValue($object, 'formrefint');
		if ($formationRef === '') {
			$formationRef = $this->getObjectPropertyValue($object, 'ref');
		}
		$resarray['formation_nom'] = $formationName;
		$resarray['formation_nom_custo'] = $formationCustomName;
		$resarray['formation_date_debut'] = $this->getFormattedDatePropertyValue($object, 'dated', 'day');
		$resarray['formation_date_debut_formated'] = $this->getFormattedDatePropertyValue($object, 'dated', '%A %d %B %Y');
		$resarray['formation_date_fin'] = $this->getFormattedDatePropertyValue($object, 'datef', 'day');
		$resarray['formation_date_fin_formated'] = $this->getFormattedDatePropertyValue($object, 'datef', '%A %d %B %Y');
		$resarray['formation_ref'] = $formationRef;
		$resarray['formation_refint'] = $formationRefInterne;
		$resarray['formation_ref_produit'] = '';


		if(!empty($object->fk_product)) {
			$p = new Product($db);
			$p->fetch($object->fk_product);
			$resarray['formation_ref_produit'] = $p->ref;
		}

		// Some Agefodd session flows do not hydrate formref reliably.
		// Fall back to the linked formation catalogue reference when available.
		if (empty($resarray['formation_ref']) && !empty($object->fk_formation_catalogue)) {
			if (class_exists('Agefodd')) {
				$formationCatalog = new Agefodd($db);
			} elseif (class_exists('Formation')) {
				$formationCatalog = new Formation($db);
			}
			if (!empty($formationCatalog) && $formationCatalog->fetch($object->fk_formation_catalogue) > 0) {
				$resarray['formation_ref'] = $formationCatalog->ref_obj;
				if (empty($resarray['formation_ref_produit']) && !empty($formationCatalog->fk_product)) {
					$p = new Product($db);
					if ($p->fetch($formationCatalog->fk_product) > 0) {
						$resarray['formation_ref_produit'] = $p->ref;
					}
				}
			}
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
		$formationTypeSession = property_exists($object, 'type_session') ? $object->type_session : null;
		$resarray['formation_type'] = $formationTypeSession !== null ? ($formAgefodd->type_session_def[$formationTypeSession] ?? '') : '';
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


		$mentorSubstitutions = $this->getAgefoddMentorSubstitutions($langs);
		$resarray['AgfMentorList'] = $mentorSubstitutions['list'];
		$resarray['Mentor_administrator'] = $mentorSubstitutions['items']['Mentor_administrator'];
		$resarray['Mentor_pedagogique'] = $mentorSubstitutions['items']['Mentor_pedagogique'];
		$resarray['Mentor_handicap'] = $mentorSubstitutions['items']['Mentor_handicap'];

		// cela devrait être toujours vrai ici
			if (! empty($object->fk_formation_catalogue)) {

				dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');
				dol_include_once('/agefodd/class/agefodd_session_catalogue.class.php');


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
			if (method_exists($catalogue, 'fetch_optionals') && !empty($catalogue->id)) {
				$catalogue->fetch_optionals($catalogue->id);
			}

			// Keep the full training/session clone object reachable from the runtime Agsession
			// so advanced dynamic tags stay exhaustive for the current generation context.
			$object->session_catalogue = $catalogue;
			$object->formation_catalogue = $catalogue;

			// ajouter les peda ici pour


			$resarray['formation_but'] = $catalogue->but;
			$resarray = $this->applyAgefoddFormationReferenceFallbacks($resarray, $catalogue);
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

			$resarray = $this->fill_substitutionarray_with_extrafields($catalogue, $resarray, $extrafields, 'formation', $langs);

			// surcharge pour le oui ou non à la place de 1 ou 0
			$resarray['formation_Accessibility_Handicap'] = $catalogue->accessibility_handicap == 1 ? 'oui':'non';


		}

		$fk_place = property_exists($object, 'placeid') ? $object->placeid : null;
		if (!empty($agfStep->id) && !empty($agfStep->fk_place)) { //Si l'étape porte un lieu, on le privilégie
			$fk_place = $agfStep->fk_place;
		}

		$resarray['step_label'] = !empty($agfStep->label) ? $agfStep->label : '';
		$resarray['step_date_start'] = !empty($agfStep->date_start) ? dol_print_date($agfStep->date_start, 'day') : '';
		$resarray['step_date_end'] = !empty($agfStep->date_end) ? dol_print_date($agfStep->date_end, 'day') : '';
		$resarray['step_duration'] = !empty($agfStep->duration) ? $agfStep->duration : '';

		dol_include_once('/agefodd/class/agefodd_place.class.php');
		$agf_place = new Agefodd_place($db);
		if(! empty($fk_place)) $agf_place->fetch($fk_place);
		// Lieu
		$resarray['formation_lieu'] 				= strip_tags((string) ($agf_place->ref_interne ?? ''));
		$resarray['formation_lieu_adresse'] 		= strip_tags((string) ($agf_place->adresse ?? ''));
		$resarray['formation_lieu_cp'] 				= strip_tags((string) ($agf_place->cp ?? ''));
		$resarray['formation_lieu_ville'] 			= strip_tags((string) ($agf_place->ville ?? ''));
		// fix TK9760
		$resarray['formation_lieu_acces'] 			= str_replace('&amp;', '&', (string) ($agf_place->acces_site ?? ''));
		$resarray['formation_lieu_phone'] 			= dol_print_phone($agf_place->tel, $agf_place->country_code);
		$resarray['formation_lieu_horaires'] 		= strip_tags((string) ($agf_place->timeschedule ?? ''));
		$resarray['formation_lieu_notes'] 			= strip_tags((string) ($agf_place->notes ?? ''));
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
								$val = $this->showOutputFieldValue($extrafields, $key_opt, $object->array_options['options_' . $key_opt]);
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
						$value = $object->referenceletter_showPublicOutputField($key,$value);
					}


					$array_other['object_' . $sub_element_label . $key] = $value;
				} elseif ($recursive && ! empty($value)) {
					$sub = strtr('object_' . $sub_element_label . $key, array('object_' . $sub_element_label => '')) . '_';
					$array_other = array_merge($array_other, $this->get_substitutionarray_each_var_object($value, $outputlangs, false, $sub));
				}
			}

			// Keep legacy trainee aliases resolvable even when no current trainee context exists.
			if (is_object($object) && get_class($object) === 'Agsession') {
				$traineeRpps = '';
				$traineeSocRpps = '';
				$traineeSocAdeli = '';

				if (!empty($object->stagiaire_soc_options_rpps)) {
					$traineeSocRpps = $object->stagiaire_soc_options_rpps;
				}
				if (!empty($object->stagiaire_soc_options_adeli)) {
					$traineeSocAdeli = $object->stagiaire_soc_options_adeli;
				}
				if (!empty($object->stagiaire) && is_object($object->stagiaire)) {
					if (!empty($object->stagiaire->thirdparty) && !empty($object->stagiaire->thirdparty->array_options) && is_array($object->stagiaire->thirdparty->array_options)) {
						if (!empty($object->stagiaire->thirdparty->array_options['options_rpps'])) {
							$traineeSocRpps = $object->stagiaire->thirdparty->array_options['options_rpps'];
						}
						if (!empty($object->stagiaire->thirdparty->array_options['options_adeli'])) {
							$traineeSocAdeli = $object->stagiaire->thirdparty->array_options['options_adeli'];
						}
					}
				}

				$traineeRpps = $traineeSocRpps;
				$array_other['object_stagiaire_rpps'] = $traineeRpps;
				$array_other['object_stagiaire_soc_options_rpps'] = $traineeSocRpps;
				$array_other['object_stagiaire_soc_options_adeli'] = $traineeSocAdeli;
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

		if (! empty($extrafieldsobjectkey))
		{
			$attributeSource = is_array($extrafields->attributes ?? null) && is_array($extrafields->attributes[$extrafieldsobjectkey] ?? null)
				? $extrafields->attributes[$extrafieldsobjectkey]
				: array();
			$elementtype = $attributeSource['elementtype'][$key] ?? '';	// seems not used
			$label = $attributeSource['label'][$key] ?? '';
			$type = $attributeSource['type'][$key] ?? '';
			$size = $attributeSource['size'][$key] ?? '';
			$default = $attributeSource['default'][$key] ?? '';
			$computed = $attributeSource['computed'][$key] ?? '';
			$unique = $attributeSource['unique'][$key] ?? '';
			$required = $attributeSource['required'][$key] ?? '';
			$param = is_array($attributeSource['param'][$key] ?? null) ? $attributeSource['param'][$key] : array();
			$perms = $attributeSource['perms'][$key] ?? '';
			$langfile = $attributeSource['langfile'][$key] ?? '';
			$list = $attributeSource['list'][$key] ?? '';
			$ishidden = $attributeSource['ishidden'][$key] ?? 0;

			if( (float) DOL_VERSION < 7 ) {
			    $hidden= ($ishidden == 0 ?  1 : 0);
			}
			else{
			    $hidden=((string) $list === '0' ? 1 : 0);		// If zero, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)
			}

		}
			else
			{
				$attributeSource = array();
				if (!empty($extrafields->attributes) && is_array($extrafields->attributes)) {
					foreach ($extrafields->attributes as $definition) {
						if (is_array($definition) && isset($definition['label'][$key])) {
							$attributeSource = $definition;
							break;
						}
					}
				}
				$elementtype=$attributeSource['elementtype'][$key] ?? '';	// seems not used
				$label=$attributeSource['label'][$key] ?? '';
				$type=$attributeSource['type'][$key] ?? '';
				$size=$attributeSource['size'][$key] ?? '';
				$default=$attributeSource['default'][$key] ?? '';
				$computed=$attributeSource['computed'][$key] ?? '';
				$unique=$attributeSource['unique'][$key] ?? '';
				$required=$attributeSource['required'][$key] ?? '';
				$param=is_array($attributeSource['param'][$key] ?? null) ? $attributeSource['param'][$key] : array();
				$perms=$attributeSource['perms'][$key] ?? '';
				$langfile=$attributeSource['langfile'][$key] ?? '';
				$list=$attributeSource['list'][$key] ?? 0;
				$ishidden=$attributeSource['hidden'][$key] ?? 0;

			if( (float) DOL_VERSION < 7 ){
			    $hidden= ($ishidden == 0 ?  1 : 0);
			}
			else{
			    $hidden=((string) $list === '0' ? 1 : 0);		// If zero, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)
			}
		}
		if ($hidden) return '';		// This is a protection. If field is hidden, we should just not call this method.

		// If field is a computed field, value must become result of compute
		if ($computed)
		{
			$value = dol_eval($computed, 1, 0);
		}

		$showsize=0;
		if ($type == 'date')
		{
			$showsize=10;
			$value = $this->formatExtrafieldDateValue($value, 'day');
		}
		elseif ($type == 'datetime')
		{
			$showsize=19;
			$value = $this->formatExtrafieldDateValue($value, 'dayhour');
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
			$value = isset($param['options'][$value]) ? $param['options'][$value] : '';
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
			$showsize=round(floatval($size));
			if ($showsize > 48) $showsize=48;
		}

		//print $type.'-'.$size;
		$out=$value;

		return $out;
	}

	/**
	 * Format date-like extrafield values without exploding on already formatted strings.
	 *
	 * @param mixed $value
	 * @param string $format
	 * @return string
	 */
	protected function formatExtrafieldDateValue($value, $format)
	{
		if ($value === null || $value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
			return '';
		}

		if (is_numeric($value)) {
			return dol_print_date((int) $value, $format);
		}

		if (is_string($value)) {
			$timestamp = strtotime($value);
			if ($timestamp !== false) {
				return dol_print_date($timestamp, $format);
			}
		}

		return (string) $value;
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

		// phpcs:enable
		global $conf;
		$attributeSource = array();
		if (!isset($object->array_options) || !is_array($object->array_options)) {
			$object->array_options = array();
		}
		if (!empty($extrafields) && is_object($extrafields) && is_array($extrafields->attributes ?? null) && !empty($object->element) && is_array($extrafields->attributes[$object->element] ?? null)) {
			$attributeSource = $extrafields->attributes[$object->element];
		} elseif (!empty($extrafields) && is_object($extrafields) && is_array($extrafields->attributes ?? null) && !empty($object->table_element) && is_array($extrafields->attributes[$object->table_element] ?? null)) {
			$attributeSource = $extrafields->attributes[$object->table_element];
		}
		$attributeLabels = is_array($attributeSource) && is_array($attributeSource['label'] ?? null) ? $attributeSource['label'] : array();
		$attributeTypes = is_array($attributeSource) && is_array($attributeSource['type'] ?? null) ? $attributeSource['type'] : array();
		$attributeParams = is_array($attributeSource) && is_array($attributeSource['param'] ?? null) ? $attributeSource['param'] : array();
		foreach($attributeLabels as $key=>$label)
		{
				$currentType = $attributeTypes[$key] ?? '';
				$currentParam = is_array($attributeParams[$key] ?? null) ? $attributeParams[$key] : array();
				$currentOptionKey = 'options_'.$key;
				$currentOptionValue = isset($object->array_options[$currentOptionKey]) ? $object->array_options[$currentOptionKey] : '';
				if($currentType == 'price')
				{
					$object->array_options[$currentOptionKey] = price2num($currentOptionValue);
					$object->array_options[$currentOptionKey.'_currency'] = price($object->array_options[$currentOptionKey], 0, $outputlangs, 0, 0, -1, $conf->currency);
					//Add value to store price with currency
					$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_currency' => $object->array_options[$currentOptionKey.'_currency']));
				}
				elseif($currentType == 'select')
				{
					if (isset($currentParam['options'][$currentOptionValue])) {
						$object->array_options[$currentOptionKey] = $currentParam['options'][$currentOptionValue];
					} else {
						$object->array_options[$currentOptionKey] = '';
					}
				}
				elseif($currentType == 'checkbox') {
					$valArray=($currentOptionValue === '' ? array() : explode(',', $currentOptionValue));
					$output=array();
					if (is_array($currentParam['options'] ?? null)){
						foreach($currentParam['options'] as $keyopt=>$valopt) {
						if  (in_array($keyopt, $valArray)) {
							$output[]=$valopt;
							}
						}
					}

					$object->array_options[$currentOptionKey] = implode(', ', $output);
				}
				elseif($currentType == 'date')
				{
					if (strlen($currentOptionValue)>0)
					{
						$date = $currentOptionValue;
						$object->array_options[$currentOptionKey] = dol_print_date($date, 'day');                                       // using company output language
						$object->array_options[$currentOptionKey.'_locale'] = dol_print_date($date, 'day', 'tzserver', $outputlangs);     // using output language format
						$object->array_options[$currentOptionKey.'_rfc'] = dol_print_date($date, 'dayrfc');                             // international format
					}
					else
					{
						$object->array_options[$currentOptionKey] = '';
						$object->array_options[$currentOptionKey.'_locale'] = '';
						$object->array_options[$currentOptionKey.'_rfc'] = '';
					}
					$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_locale' => $object->array_options[$currentOptionKey.'_locale']));
					$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_rfc' => $object->array_options[$currentOptionKey.'_rfc']));
					}
						elseif($currentType == 'datetime')
						{
							$datetime = $currentOptionValue;
							$object->array_options[$currentOptionKey] = ($datetime!="0000-00-00 00:00:00" && $datetime !== '' ? dol_print_date($datetime, 'dayhour') : '');                            // using company output language
							$object->array_options[$currentOptionKey.'_locale'] = ($datetime!="0000-00-00 00:00:00" && $datetime !== '' ? dol_print_date($datetime, 'dayhour', 'tzserver', $outputlangs) : '');    // using output language format
							$object->array_options[$currentOptionKey.'_rfc'] = ($datetime!="0000-00-00 00:00:00" && $datetime !== '' ? dol_print_date($datetime, 'dayhourrfc') : '');                             // international format
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_locale' => $object->array_options[$currentOptionKey.'_locale']));
						$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key.'_rfc' => $object->array_options[$currentOptionKey.'_rfc']));
					}
				elseif($currentType == 'link')
				{
					$id = $currentOptionValue;
					if ($id != "")
					{
					$param = $currentParam;
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
								if ($tmpobject instanceof CommonObject && method_exists($tmpobject, 'fetch') && $tmpobject->fetch((int) $id) > 0) {
									$resolvedLabel = $this->resolveLinkedExtraFieldObjectLabel($tmpobject);
									$object->array_options[$currentOptionKey] = ($resolvedLabel !== '' ? $resolvedLabel : (string) $id);
								}
							}
						}
						}

				}
			}
				elseif($currentType == 'sellist') {
					$object->array_options[$currentOptionKey] = $this->showOutputFieldValue($extrafields, $key, $currentOptionValue);
				}
				elseif($currentType == 'chkbxlst')
				{
					$object->array_options[$currentOptionKey] = $this->showOutputFieldValue($extrafields, $key, $currentOptionValue);
				}

				$array_to_fill=array_merge($array_to_fill, array($array_key.'_options_'.$key => (isset($object->array_options[$currentOptionKey]) ? $object->array_options[$currentOptionKey] : '')));
			}


		return $array_to_fill;
	}
}
