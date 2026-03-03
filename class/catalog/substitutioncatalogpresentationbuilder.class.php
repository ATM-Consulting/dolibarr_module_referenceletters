<?php

/**
 * Build human-readable presentation metadata for substitution keys.
 */
class SubstitutionCatalogPresentationBuilder
{
	/**
	 * @var Translate
	 */
	protected $langs;

	/**
	 * @param Translate $langs Translation helper.
	 */
	public function __construct($langs)
	{
		$this->langs = $langs;
		$this->langs->load('refflettersubtitution@referenceletters');
	}

	/**
	 * Build a presentable catalog grouped by existing UI blocks.
	 *
	 * @param array $catalog Raw catalog grouped by block.
	 * @return array
	 */
	public function buildCatalogPresentation(array $catalog)
	{
		$presentation = array();
		$loopUsageMap = $this->buildLoopUsageMap($catalog);

		foreach ($catalog as $block => $entries) {
			$presentation[$block] = array();
			if (!is_array($entries)) {
				continue;
			}

			foreach ($entries as $tag => $sampleValue) {
				$entryType = $this->resolveEntryType($tag);
				$presentation[$block][$tag] = array(
					'description' => $this->formatDescription($tag, $sampleValue, $block),
					'format_hint' => $this->formatHint($tag, $sampleValue),
					'sample_value' => $sampleValue,
					'is_loop_tag' => ($entryType === 'loop'),
					'entry_type' => $entryType,
					'type_label' => $this->formatTypeLabel($entryType),
					'usage_hint' => $this->formatUsageHint($entryType, $tag, $loopUsageMap),
				);
			}
		}

		return $presentation;
	}

	/**
	 * Build a user-facing description for one tag.
	 *
	 * @param string $tag Tag name without braces.
	 * @param string $sampleValue Raw sample value from catalog.
	 * @param string $block Source UI block.
	 * @return string
	 */
	protected function formatDescription($tag, $sampleValue, $block = '')
	{
		$translationKey = 'reflettershortcode_' . $tag;
		if (!empty($this->langs->tab_translate[$translationKey])) {
			return $this->langs->trans($translationKey);
		}

		if ($this->shouldPreferCatalogDescription($tag, $sampleValue, $block)) {
			return trim((string) $sampleValue);
		}

		if (preg_match('/^cust_contactclient_([A-Z_]+)_([0-9]+)_(.+)$/', $tag, $matches)) {
			return $this->formatContactDescription($matches[1], $matches[2], $matches[3]);
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return $this->langs->trans('RefLtrCatalogTechnicalDescription', $this->humanizeToken(substr($tag, 3, -3)));
		}

		if (strpos($tag, 'cust_company_options_') === 0) {
			$fieldKey = substr($tag, strlen('cust_company_options_'));
			$fieldKey = preg_replace('/_(locale|rfc)$/', '', $fieldKey);
			return 'Champ complementaire tiers - ' . $this->humanizeToken($fieldKey);
		}

		if (strpos($tag, 'object_') === 0) {
			return 'Document - ' . $this->humanizeToken(substr($tag, 7));
		}

		if (strpos($tag, 'cust_company_') === 0) {
			return 'Tiers client - ' . $this->humanizeToken(substr($tag, 13));
		}

		if (strpos($tag, 'cust_contactclient') === 0) {
			return 'Contacts client - ' . $this->humanizeToken(substr($tag, 16));
		}

		if (strpos($tag, 'line_') === 0) {
			return $this->langs->trans('RefLtrCatalogLoopFieldDescription', $this->humanizeToken(substr($tag, 5)));
		}

		if (strpos($tag, 'referenceletters_') === 0) {
			return 'DocEdit - ' . $this->humanizeToken(substr($tag, 17));
		}

		if (strpos($tag, 'formation_') === 0) {
			return 'Formation - ' . $this->humanizeToken(substr($tag, 10));
		}

		if (strpos($tag, 'stagiaire_') === 0 || strpos($tag, 'time_stagiaire_') === 0) {
			return 'Stagiaire - ' . $this->humanizeToken(str_replace(array('time_stagiaire_', 'stagiaire_'), '', $tag));
		}

		if (strpos($tag, 'trainer_') === 0) {
			return 'Formateur - ' . $this->humanizeToken(substr($tag, 8));
		}

		if (strpos($tag, 'step_') === 0) {
			return 'Etape - ' . $this->humanizeToken(substr($tag, 5));
		}

		if (strpos($tag, 'current_') === 0) {
			return 'Contexte courant - ' . $this->humanizeToken(substr($tag, 8));
		}

		if (strpos($tag, 'mycompany_') === 0) {
			return 'Societe emettrice - ' . $this->humanizeToken(substr($tag, 10));
		}

		if (strpos($tag, 'myuser_') === 0) {
			return 'Utilisateur courant - ' . $this->humanizeToken(substr($tag, 7));
		}

		if (is_string($sampleValue) && $this->isMeaningfulTextSample($sampleValue)) {
			return $sampleValue;
		}

		return $this->humanizeToken($tag);
	}

	/**
	 * Build a user-facing format hint for one tag.
	 *
	 * @param string $tag Tag name without braces.
	 * @param string $sampleValue Raw sample value from catalog.
	 * @return string
	 */
	protected function formatHint($tag, $sampleValue)
	{
		if (preg_match('/_rfc$/', $tag)) {
			return 'Date RFC';
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return $this->langs->trans('RefLtrCatalogConfigConstant');
		}

		if (strpos($tag, 'line_') === 0) {
			return $this->langs->trans('RefLtrCatalogLoopOnly');
		}

		if (preg_match('/_locale$/', $tag)) {
			if ($this->isMoneyTag($tag)) {
				return 'Montant formate';
			}

			return 'Valeur formatee';
		}

		if ($this->isBooleanTag($tag, $sampleValue)) {
			return 'Booleen';
		}

		if ($this->isMoneyTag($tag)) {
			return 'Montant';
		}

		if ($this->isDateTag($tag)) {
			return 'Date';
		}

		if ($this->isCodeTag($tag)) {
			return 'Code';
		}

		if ($this->isEmailTag($tag)) {
			return 'Email';
		}

		if ($this->isPhoneTag($tag)) {
			return 'Telephone';
		}

		if ($this->isUrlTag($tag)) {
			return 'URL';
		}

		return 'Texte';
	}

	/**
	 * @param string $tag
	 * @return string
	 */
	protected function resolveEntryType($tag)
	{
		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return 'technical';
		}

		if (strpos($tag, 'line_') === 0) {
			return 'loop';
		}

		return 'scalar';
	}

	/**
	 * @param string $entryType
	 * @return string
	 */
	protected function formatTypeLabel($entryType)
	{
		if ($entryType === 'loop') {
			return $this->langs->trans('RefLtrCatalogTypeLoop');
		}
		if ($entryType === 'technical') {
			return $this->langs->trans('RefLtrCatalogTypeTechnical');
		}

		return $this->langs->trans('RefLtrCatalogTypeDirect');
	}

	/**
	 * @param string $entryType
	 * @return string
	 */
	protected function formatUsageHint($entryType, $tag = '', array $loopUsageMap = array())
	{
		if ($entryType === 'loop') {
			if ($tag !== '' && isset($loopUsageMap[$tag]) && !empty($loopUsageMap[$tag])) {
				return $this->langs->trans('RefLtrCatalogUsageLoopAvailable', implode(', ', $loopUsageMap[$tag]));
			}

			return $this->langs->trans('RefLtrCatalogUsageLoop');
		}
		if ($entryType === 'technical') {
			return $this->langs->trans('RefLtrCatalogUsageTechnical');
		}

		return $this->langs->trans('RefLtrCatalogUsageDirect');
	}

	/**
	 * Keep provider-defined wording when the catalog already carries a real business label.
	 *
	 * @param string $tag
	 * @param string $sampleValue
	 * @param string $block
	 * @return bool
	 */
	protected function shouldPreferCatalogDescription($tag, $sampleValue, $block)
	{
		if (!is_string($sampleValue) || !$this->isMeaningfulTextSample($sampleValue)) {
			return false;
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return false;
		}

		if (preg_match('/^(stagiaire_|time_stagiaire_|line_|formation_|trainer_|step_|presta_|objvar_object_)/', $tag)) {
			return true;
		}

		return (strpos((string) $block, 'Agefodd ') === 0);
	}

	/**
	 * Build an index of line tags by loop block label.
	 *
	 * @param array $catalog
	 * @return array<string,array<int,string>>
	 */
	protected function buildLoopUsageMap(array $catalog)
	{
		$map = array();
		$groupLoopLabels = array(
			'Agefodd Liste des participants' => array(
				'TStagiairesSession',
				'TStagiairesSessionPresent',
				'TStagiairesSessionSoc',
				'TStagiairesSessionSocPresent',
				'TStagiairesSessionSocConfirm',
				'TStagiairesSessionSocMore',
				'TStagiairesSessionConvention',
				'TSessionStagiairesCertif',
				'TSessionStagiairesCertifSoc',
			),
			'Agefodd Liste des etapes' => array(
				'TSteps',
				'TStepsDistanciel',
				'TStepsPresentiel',
			),
			'Agefodd Liste des horaires' => array(
				'THorairesSession',
			),
			'Agefodd Liste des formateurs' => array(
				'TFormateursSession',
			),
			'Agefodd Agenda formateur' => array(
				'TFormateursSessionCal',
			),
			'Agefodd Lignes financieres session' => array(
				'TConventionFinancialLine',
			),
			'Agefodd Liste des objectifs pedagogiques' => array(
				'TFormationObjPeda',
			),
			'Lignes de documents' => array(
				'lines',
				'lines_active',
			),
		);

		foreach ($groupLoopLabels as $groupLabel => $loops) {
			if (!isset($catalog[$groupLabel]) || !is_array($catalog[$groupLabel])) {
				continue;
			}

			foreach (array_keys($catalog[$groupLabel]) as $tag) {
				if (!is_string($tag) || strpos($tag, 'line_') !== 0) {
					continue;
				}
				$map[$tag] = $loops;
			}
		}

		return $map;
	}

	/**
	 * Build a readable contact description from contact role fragments.
	 *
	 * @param string $role Contact role code.
	 * @param string $index Contact index.
	 * @param string $field Field name.
	 * @return string
	 */
	protected function formatContactDescription($role, $index, $field)
	{
		$roleMap = array(
			'BILLING' => 'Contact facturation',
			'SHIPPING' => 'Contact livraison',
			'SERVICE' => 'Contact service',
			'CUSTOMER' => 'Contact client',
		);

		$roleLabel = isset($roleMap[$role]) ? $roleMap[$role] : 'Contact ' . $this->humanizeToken(strtolower($role));
		return $roleLabel . ' ' . $index . ' - ' . $this->humanizeToken($field);
	}

	/**
	 * Turn an internal token into a readable label.
	 *
	 * @param string $token Raw token.
	 * @return string
	 */
	protected function humanizeToken($token)
	{
		$map = array(
			'ht' => 'HT',
			'ttc' => 'TTC',
			'tva' => 'TVA',
			'vat' => 'TVA',
			'bic' => 'BIC',
			'iban' => 'IBAN',
			'rfc' => 'RFC',
			'qty' => 'quantite',
			'ref' => 'reference',
			'refint' => 'reference interne',
			'locale' => 'formate',
			'multicurrency' => 'multidevise',
			'already' => 'deja',
			'payed' => 'regle',
			'deposit' => 'acompte',
			'creditnote' => 'avoir',
			'remain' => 'reste',
			'pay' => 'payer',
			'payment' => 'reglement',
			'term' => 'echeance',
			'mode' => 'mode',
			'juridicalstatus' => 'forme juridique',
			'fullname' => 'nom complet',
			'lastname' => 'nom',
			'firstname' => 'prenom',
			'birthday' => 'date de naissance',
			'civility' => 'civilite',
			'socid' => 'id tiers',
			'idprof1' => 'identifiant 1',
			'idprof2' => 'identifiant 2',
			'idprof3' => 'identifiant 3',
			'idprof4' => 'identifiant 4',
			'idprof5' => 'identifiant 5',
			'idprof6' => 'identifiant 6',
		);

		$parts = preg_split('/[_]+/', trim((string) $token, '_'));
		$labels = array();

		foreach ($parts as $part) {
			if ($part === '') {
				continue;
			}

			$lower = strtolower($part);
			if (isset($map[$lower])) {
				$labels[] = $map[$lower];
			} elseif ($lower === 'to') {
				continue;
			} elseif (ctype_digit($part)) {
				$labels[] = $part;
			} else {
				$labels[] = ucfirst(str_replace('-', ' ', $lower));
			}
		}

		return implode(' ', $labels);
	}

	/**
	 * Tell if a sample text is useful as description fallback.
	 *
	 * @param string $sampleValue Sample value.
	 * @return bool
	 */
	protected function isMeaningfulTextSample($sampleValue)
	{
		$value = trim((string) $sampleValue);
		if ($value === '' || $this->isNumericLike($value)) {
			return false;
		}

		return strlen($value) > 3;
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isMoneyTag($tag)
	{
		return (bool) preg_match('/(^|_)(total|amount|price|discount|capital|limit|payed|deposit|creditnote|remain)(_|$)/', $tag);
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isDateTag($tag)
	{
		return (bool) preg_match('/(^|_)(date|birthday|hour|heured|heuref)(_|$)/', $tag);
	}

	/**
	 * @param string $tag
	 * @param string $sampleValue
	 * @return bool
	 */
	protected function isBooleanTag($tag, $sampleValue)
	{
		if (preg_match('/(^|_)(enabled|disable|active|confirm|certifying|required)(_|$)/', $tag)) {
			return true;
		}

		return in_array($tag, array(
			'object_already_payed_all',
		), true);
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isCodeTag($tag)
	{
		return (bool) preg_match('/(_code$|^code_|_id$|^id_)/', $tag);
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isEmailTag($tag)
	{
		return strpos($tag, 'email') !== false || strpos($tag, 'mail') !== false;
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isPhoneTag($tag)
	{
		return strpos($tag, 'phone') !== false || strpos($tag, 'fax') !== false || strpos($tag, 'tel') !== false;
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isUrlTag($tag)
	{
		return strpos($tag, 'url') !== false || strpos($tag, 'web') !== false;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	protected function isIntegerSample($value)
	{
		return preg_match('/^-?[0-9]+$/', trim((string) $value)) === 1;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	protected function isNumericLike($value)
	{
		$normalized = str_replace(array(' ', "\xc2\xa0"), '', trim((string) $value));
		$normalized = str_replace(',', '.', $normalized);

		return is_numeric($normalized);
	}
}
