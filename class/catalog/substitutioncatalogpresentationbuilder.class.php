<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

/**
 * Build human-readable presentation metadata for substitution keys.
 */
class SubstitutionCatalogPresentationBuilder
{
	/**
	 * @var DoliDB|null
	 */
	protected $db;

	/**
	 * @var Translate
	 */
	protected $langs;

	/**
	 * @var string
	 */
	protected $currentElementType = '';

	/**
	 * @var object|null
	 */
	protected $currentCatalogObject = null;

	/**
	 * @var array<string,string>|null
	 */
	protected $currentObjectExtraLabels = null;

	/**
	 * @param DoliDB|null $db Database handle used to resolve extrafield labels.
	 * @param Translate $langs Translation helper.
	 */
	public function __construct($langs, $db = null)
	{
		$this->db = $db;
		$this->langs = $langs;
		$this->langs->load('refflettersubtitution@referenceletters');
	}

	/**
	 * Build a presentable catalog grouped by existing UI blocks.
	 *
	 * @param array $catalog Raw catalog grouped by block.
	 * @return array
	 */
	public function buildCatalogPresentation(array $catalog, $elementType = '', $catalogObject = null, array $loopCatalog = array())
	{
		$this->currentElementType = (string) $elementType;
		$this->currentCatalogObject = is_object($catalogObject) ? $catalogObject : null;
		$this->currentObjectExtraLabels = null;
		$presentation = array();
		$loopUsageMap = $this->buildLoopUsageMap($catalog, $loopCatalog);

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
		$translatedDescription = $this->resolveTranslatedDescription($tag);
		if ($translatedDescription !== '') {
			return $translatedDescription;
		}

		$structuredDescription = $this->resolveStructuredDescription($tag, false);
		if ($structuredDescription !== '') {
			return $structuredDescription;
		}

		$catalogDescription = $this->resolveCatalogDescription($tag, $sampleValue, $block);
		if ($catalogDescription !== '') {
			return $catalogDescription;
		}

		$genericStructuredDescription = $this->resolveStructuredDescription($tag, true);
		if ($genericStructuredDescription !== '') {
			return $genericStructuredDescription;
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
		$forcedHint = $this->resolveForcedFormatHint($tag);
		if ($forcedHint !== '') {
			return $forcedHint;
		}

		$detectedHint = $this->resolveDetectedFormatHint($tag, $sampleValue);
		if ($detectedHint !== '') {
			return $detectedHint;
		}

		return 'Texte';
	}

	/**
	 * Resolve a translated label when a specific wording already exists.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function resolveTranslatedDescription($tag)
	{
		$translationKey = 'reflettershortcode_' . $tag;
		if (!empty($this->langs->tab_translate[$translationKey])) {
			$translated = $this->langs->trans($translationKey);
			if (strpos($tag, 'object_') === 0) {
				return $this->getCurrentObjectLabel() . ' - ' . $translated;
			}

			return $translated;
		}

		return '';
	}

	/**
	 * Resolve a business wording already provided by the source catalog.
	 *
	 * @param string $tag
	 * @param string $sampleValue
	 * @param string $block
	 * @return string
	 */
	protected function resolveCatalogDescription($tag, $sampleValue, $block)
	{
		if ($this->shouldPreferCatalogDescription($tag, $sampleValue, $block)) {
			return trim((string) $sampleValue);
		}

		return '';
	}

	/**
	 * Resolve descriptions by tag family or explicit structure.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function resolveStructuredDescription($tag, $includeGenericPrefixes = true)
	{
		if (preg_match('/^cust_contactclient_([A-Z_]+)_([0-9]+)_(.+)$/', $tag, $matches)) {
			return $this->formatContactDescription($matches[1], $matches[2], $matches[3]);
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return $this->langs->trans('RefLtrCatalogTechnicalDescription', $this->humanizeToken(substr($tag, 3, -3)));
		}

		if (strpos($tag, 'cust_company_options_') === 0) {
			return $this->formatThirdpartyOptionDescription($tag);
		}

		if (strpos($tag, 'object_options_') === 0) {
			return $this->formatCurrentObjectOptionDescription($tag);
		}

		if (strpos($tag, 'objvar_object_contact_') === 0) {
			return 'Propriete technique contact - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_contact_')));
		}

		if (strpos($tag, 'objvar_object_thirdparty_') === 0) {
			return 'Propriete technique tiers - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_thirdparty_')));
		}

		if (strpos($tag, 'objvar_object_user_') === 0) {
			return 'Propriete technique utilisateur - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_user_')));
		}

		if (strpos($tag, 'objvar_object_formation_') === 0) {
			return 'Propriete technique formation - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_formation_')));
		}

		if (strpos($tag, 'objvar_object_array_options_options_') === 0) {
			return 'Options techniques de l\'objet - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_array_options_options_')));
		}

		if (strpos($tag, 'objvar_object_options_') === 0) {
			return 'Champs complementaires techniques - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_options_')));
		}

		if (strpos($tag, 'objvar_object_linkedObjectsFullLoaded_') === 0) {
			return 'Objet lie charge - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_linkedObjectsFullLoaded_')));
		}

		if (strpos($tag, 'objvar_object_') === 0) {
			return $this->getCurrentObjectLabel() . ' avance - ' . $this->humanizeToken(substr($tag, strlen('objvar_object_')));
		}

		if ($includeGenericPrefixes) {
			$prefixedDescriptions = array(
				'object_' => $this->getCurrentObjectLabel(),
				'cust_company_' => 'Tiers client',
				'cust_contactclient' => 'Contacts client',
				'referenceletters_' => 'DocEdit',
				'formation_' => 'Formation',
				'trainer_' => 'Formateur',
				'step_' => 'Etape',
				'current_' => 'Contexte courant',
				'mycompany_' => 'Societe emettrice',
				'myuser_' => 'Utilisateur courant',
			);

			foreach ($prefixedDescriptions as $prefix => $label) {
				$description = $this->formatPrefixedDescription($tag, $prefix, $label);
				if ($description !== '') {
					return $description;
				}
			}
		}

		if (strpos($tag, 'line_') === 0) {
			return $this->langs->trans('RefLtrCatalogLoopFieldDescription', $this->humanizeToken(substr($tag, 5)));
		}

		if (strpos($tag, 'stagiaire_') === 0 || strpos($tag, 'time_stagiaire_') === 0) {
			return 'Stagiaire - ' . $this->humanizeToken(str_replace(array('time_stagiaire_', 'stagiaire_'), '', $tag));
		}

		return '';
	}

	/**
	 * Return the best user-facing label for object_* tags.
	 *
	 * @return string
	 */
	protected function getCurrentObjectLabel()
	{
		$map = array(
			'contract' => 'Contrat',
			'thirdparty' => 'Tiers',
			'contact' => 'Contact',
			'propal' => 'Proposition commerciale',
			'invoice' => 'Facture',
			'order' => 'Commande client',
			'order_supplier' => 'Commande fournisseur',
			'supplier_proposal' => 'Proposition fournisseur',
			'expedition' => 'Expedition',
			'shipping' => 'Reception',
			'fichinter' => 'Intervention',
			'rfltr_agefodd_formation' => 'Formation catalogue',
		);

		if (isset($map[$this->currentElementType])) {
			return $map[$this->currentElementType];
		}

		if (strpos($this->currentElementType, 'rfltr_agefodd_') === 0) {
			if (preg_match('/_(trainee|trainer)$/', $this->currentElementType)) {
				return 'Document Agefodd participant';
			}

			return 'Session Agefodd';
		}

		return 'Objet courant';
	}

	/**
	 * Resolve hints that are enforced by tag suffixes or special families.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function resolveForcedFormatHint($tag)
	{
		if (preg_match('/_rfc$/', $tag)) {
			return 'Date RFC';
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return $this->langs->trans('RefLtrCatalogConfigConstant');
		}

		if (strpos($tag, 'line_') === 0 || $this->isNonPrefixedLoopTag($tag)) {
			return $this->langs->trans('RefLtrCatalogLoopOnly');
		}

		if (preg_match('/_locale$/', $tag)) {
			if ($this->isMoneyTag($tag)) {
				return 'Montant formate';
			}

			return 'Valeur formatee';
		}

		return '';
	}

	/**
	 * Resolve hints inferred from tag naming conventions.
	 *
	 * @param string $tag
	 * @param string $sampleValue
	 * @return string
	 */
	protected function resolveDetectedFormatHint($tag, $sampleValue)
	{
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

		return '';
	}

	/**
	 * Format one generic description based on a tag prefix.
	 *
	 * @param string $tag
	 * @param string $prefix
	 * @param string $label
	 * @return string
	 */
	protected function formatPrefixedDescription($tag, $prefix, $label)
	{
		if (strpos($tag, $prefix) !== 0) {
			return '';
		}

		return $label . ' - ' . $this->humanizeToken(substr($tag, strlen($prefix)));
	}

	/**
	 * Format one thirdparty extrafield description.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function formatThirdpartyOptionDescription($tag)
	{
		$fieldKey = substr($tag, strlen('cust_company_options_'));
		$fieldKey = preg_replace('/_(locale|rfc)$/', '', $fieldKey);

		return 'Champ complementaire tiers - ' . $this->humanizeToken($fieldKey);
	}

	/**
	 * Format one current-object extrafield description from its real label when available.
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function formatCurrentObjectOptionDescription($tag)
	{
		$fieldKey = substr($tag, strlen('object_options_'));
		$suffix = '';

		if (preg_match('/_(locale|rfc)$/', $fieldKey, $matches)) {
			$suffix = $matches[1];
			$fieldKey = substr($fieldKey, 0, -strlen($matches[0]));
		}

		$fieldLabel = $this->getCurrentObjectExtraLabel($fieldKey);
		if ($fieldLabel === '') {
			$fieldLabel = $this->humanizeToken($fieldKey);
		}

		$description = 'Champ complémentaire - ' . $fieldLabel;
		if ($suffix === 'locale') {
			$description .= ' formaté';
		} elseif ($suffix === 'rfc') {
			$description .= ' RFC';
		}

		return $description;
	}

	/**
	 * Resolve the extrafield label for the current object.
	 *
	 * @param string $fieldKey
	 * @return string
	 */
	protected function getCurrentObjectExtraLabel($fieldKey)
	{
		if ($fieldKey === '' || !is_string($fieldKey)) {
			return '';
		}

		if ($this->currentObjectExtraLabels === null) {
			$this->currentObjectExtraLabels = array();

			if (!is_object($this->currentCatalogObject) || empty($this->db) || empty($this->currentCatalogObject->table_element)) {
				return '';
			}

			$extrafields = new ExtraFields($this->db);
			$labels = $extrafields->fetch_name_optionals_label($this->currentCatalogObject->table_element, true);
			if (is_array($labels)) {
				$this->currentObjectExtraLabels = $labels;
			}
		}

		return !empty($this->currentObjectExtraLabels[$fieldKey]) ? (string) $this->currentObjectExtraLabels[$fieldKey] : '';
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

		if (strpos($tag, 'line_') === 0 || $this->isNonPrefixedLoopTag($tag)) {
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
				return $this->langs->trans('RefLtrCatalogUsageLoopAvailable', implode(', ', array_values(array_unique($loopUsageMap[$tag]))));
			}

			return $this->langs->trans('RefLtrCatalogUsageLoop');
		}
		if ($entryType === 'technical') {
			return $this->langs->trans('RefLtrCatalogUsageTechnical');
		}

		if (strpos($tag, 'objvar_object_contact_') === 0) {
			return $this->langs->trans('RefLtrCatalogUsageObjvarContact');
		}
		if (strpos($tag, 'objvar_object_thirdparty_') === 0) {
			return $this->langs->trans('RefLtrCatalogUsageObjvarThirdparty');
		}
		if (strpos($tag, 'objvar_object_user_') === 0) {
			return $this->langs->trans('RefLtrCatalogUsageObjvarUser');
		}
		if (strpos($tag, 'objvar_object_formation_') === 0) {
			return $this->langs->trans('RefLtrCatalogUsageObjvarFormation');
		}
		if (strpos($tag, 'objvar_object_') === 0) {
			return $this->langs->trans('RefLtrCatalogUsageObjvarObject');
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

		if ($this->isGenericDetectedDescription($sampleValue)) {
			return false;
		}

		if (strpos($tag, '__[') === 0 && substr($tag, -3) === ']__') {
			return false;
		}

		if (preg_match('/^(stagiaire_|time_stagiaire_|line_|formation_|trainer_|step_|presta_|objvar_object_|cust_company_options_)/', $tag)) {
			return true;
		}

		return (strpos((string) $block, 'Agefodd ') === 0);
	}

	/**
	 * Ignore generic detected wording so family-based wording can take over.
	 *
	 * @param string $sampleValue
	 * @return bool
	 */
	protected function isGenericDetectedDescription($sampleValue)
	{
		$value = trim((string) $sampleValue);
		if ($value === '') {
			return false;
		}

		return (
			strpos($value, 'Cle avancee detectee automatiquement : ') === 0
			|| strpos($value, 'Cle detectee automatiquement : ') === 0
		);
	}

	/**
	 * Build an index of line tags by compatible loop names.
	 *
	 * The mapping relies on tag families, not on translated UI group labels.
	 *
	 * @param array $catalog
	 * @return array<string,array<int,string>>
	 */
	protected function buildLoopUsageMap(array $catalog, array $loopCatalog = array())
	{
		$map = array();
		$groupLoopKeys = array();
		foreach ($loopCatalog as $loop) {
			if (!is_array($loop) || empty($loop['group_label']) || empty($loop['segment'])) {
				continue;
			}

			$groupLabel = (string) $loop['group_label'];
			if (empty($groupLoopKeys[$groupLabel])) {
				$groupLoopKeys[$groupLabel] = array();
			}

			$groupLoopKeys[$groupLabel][] = (string) $loop['segment'];
		}

		foreach ($catalog as $block => $entries) {
			if (!is_array($entries)) {
				continue;
			}

			$blockKey = (string) $block;
			if (empty($groupLoopKeys[$blockKey])) {
				continue;
			}

			foreach (array_keys($entries) as $tag) {
				if (!is_string($tag) || (strpos($tag, 'line_') !== 0 && !$this->isNonPrefixedLoopTag($tag))) {
					continue;
				}
				$map[$tag] = $groupLoopKeys[$blockKey];
			}
		}

		return $map;
	}

	/**
	 * Resolve a stable loop group key from a set of tags.
	 *
	 * @param array $entries
	 * @return string
	 */
	protected function resolveLoopGroupKey(array $entries)
	{
		$tags = array();
		foreach (array_keys($entries) as $tag) {
			if (!is_string($tag)) {
				continue;
			}
			$tags[] = $tag;
		}

		if (empty($tags)) {
			return '';
		}

		if ($this->hasTagPrefix($tags, 'line_step_')) {
			return 'steps';
		}
		if ($this->hasTagPrefix($tags, 'line_objpeda_')) {
			return 'pedagogic_objectives';
		}
		if ($this->hasTagPrefix($tags, 'line_fin_')) {
			return 'financial_lines';
		}
		if ($this->hasTagPrefix($tags, 'line_formateur_')) {
			foreach ($tags as $tag) {
				if (in_array($tag, array('line_date_session', 'line_heure_debut_session', 'line_heure_fin_session'), true)) {
					return 'trainer_calendar';
				}
			}

			return 'trainers';
		}
		if (in_array('line_date_session', $tags, true) || in_array('line_heure_debut_session', $tags, true) || in_array('line_heure_fin_session', $tags, true)) {
			return 'schedules';
		}
		if ($this->hasTagPrefix($tags, 'line_nom') || $this->hasTagPrefix($tags, 'line_prenom') || $this->hasTagPrefix($tags, 'line_statut') || $this->hasTagPrefix($tags, 'line_stagiaire_') || $this->hasTagPrefix($tags, 'line_time_') || $this->hasTagPrefix($tags, 'line_certif_')) {
			return 'participants';
		}
		if ($this->hasTagPrefix($tags, 'line_')) {
			return 'document_lines';
		}

		return '';
	}

	/**
	 * Check whether one of the provided tags starts with a prefix.
	 *
	 * @param array<int,string> $tags
	 * @param string $prefix
	 * @return bool
	 */
	protected function hasTagPrefix(array $tags, $prefix)
	{
		foreach ($tags as $tag) {
			if (strpos($tag, $prefix) === 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Tell whether one legacy tag is only available inside a repeated list.
	 *
	 * These historical tags were exposed before the line_* naming became consistent.
	 *
	 * @param string $tag
	 * @return bool
	 */
	protected function isNonPrefixedLoopTag($tag)
	{
		return in_array($tag, array(
			'date_ouverture',
			'date_ouverture_prevue',
			'date_fin_validite',
		), true);
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
			'user' => 'utilisateur',
			'username' => "nom d'utilisateur",
			'utilisateur' => 'utilisateur',
			'password' => 'mot de passe',
			'motdepasse' => 'mot de passe',
			'lien' => 'lien',
			'link' => 'lien',
			'paiement' => 'paiement',
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
