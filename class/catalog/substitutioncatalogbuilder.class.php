<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';
require_once __DIR__ . '/substitutioncataloggroupingpolicy.class.php';
require_once __DIR__ . '/substitutioncatalogvisibilitypolicy.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddprovider.class.php';
require_once __DIR__ . '/substitutioncatalogstandardprovider.class.php';

class SubstitutionCatalogBuilder
{
	/** @var DoliDB */
	protected $db;
	/** @var ReferenceLetters */
	protected $referenceletters;
	/** @var CommonDocGeneratorReferenceLetters */
	protected $docgen;
	/** @var Translate */
	protected $langs;
	/** @var SubstitutionCatalogGroupingPolicy */
	protected $groupingPolicy;
	/** @var SubstitutionCatalogVisibilityPolicy */
	protected $visibilityPolicy;
	/** @var SubstitutionCatalogProviderInterface[] */
	protected $providers = array();
	/** @var array<string,array> */
	protected $lastDetectedMetadata = array();

	/**
	 * @param DoliDB $db
	 * @param ReferenceLetters $referenceletters
	 * @param CommonDocGeneratorReferenceLetters $docgen
	 * @param Translate $langs
	 */
	public function __construct(DoliDB $db, ReferenceLetters $referenceletters, CommonDocGeneratorReferenceLetters $docgen, Translate $langs)
	{
		$this->db = $db;
		$this->referenceletters = $referenceletters;
		$this->docgen = $docgen;
		$this->langs = $langs;
		$this->groupingPolicy = new SubstitutionCatalogGroupingPolicy();
		$this->visibilityPolicy = new SubstitutionCatalogVisibilityPolicy();
		$this->providers = array(
			'standard' => new SubstitutionCatalogStandardProvider($db, $docgen, $langs),
			'agefodd' => new SubstitutionCatalogAgefoddProvider($db, $langs),
		);
	}

	/**
	 * Keep legacy wrappers while the historical `ReferenceLetters` class is being simplified.
	 *
	 * @param array<string,mixed> $substArray
	 * @param string $elementType
	 * @return void
	 */
	public function appendStandardCatalogKeys(array &$substArray, string $elementType): void
	{
		$this->getProvider('standard')->appendStandardCatalogKeys($substArray, $elementType);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @return void
	 */
	public function appendDocumentLineCatalogKeys(array &$substArray, bool $hasDocumentLines = false): void
	{
		$this->getProvider('standard')->appendDocumentLineCatalogKeys($substArray, $hasDocumentLines);
	}

	/**
	 * Remove placeholders that must never be exposed in DocEdit.
	 *
	 * @param array<string,mixed> $substArray
	 * @param array<int,string> $excludedTags
	 * @return void
	 */
	public function sanitizeGlobalCatalogKeys(array &$substArray, array $excludedTags = array()): void
	{
		if (!is_array($substArray)) {
			return;
		}

		foreach ($excludedTags as $excludedTag) {
			if (isset($substArray[$excludedTag])) {
				unset($substArray[$excludedTag]);
			}
		}
	}

	/**
	 * Move technical global constants into a dedicated UI group.
	 *
	 * @param array<string,mixed> $catalog
	 * @param string $sourceGroupLabel
	 * @param string $targetGroupLabel
	 * @param array<int,string> $excludedTags
	 * @return void
	 */
	public function relocateTechnicalGlobalCatalogKeys(array &$catalog, string $sourceGroupLabel, string $targetGroupLabel, array $excludedTags = array()): void
	{
		if (
			!isset($catalog[$sourceGroupLabel])
			|| !is_array($catalog[$sourceGroupLabel])
		) {
			return;
		}

		if (!isset($catalog[$targetGroupLabel]) || !is_array($catalog[$targetGroupLabel])) {
			$catalog[$targetGroupLabel] = array();
		}

		foreach (array_keys($catalog[$sourceGroupLabel]) as $tag) {
			if (!is_string($tag) || !$this->isGlobalConstTag($tag)) {
				continue;
			}
			if (in_array($tag, $excludedTags, true)) {
				unset($catalog[$sourceGroupLabel][$tag]);
				continue;
			}

			$catalog[$targetGroupLabel][$tag] = $catalog[$sourceGroupLabel][$tag];
			unset($catalog[$sourceGroupLabel][$tag]);
		}
	}

	/**
	 * @param string $tag
	 * @return bool
	 */
	protected function isGlobalConstTag(string $tag): bool
	{
		return strpos($tag, '__[') === 0 && substr($tag, -3) === ']__';
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param object|null $object
	 * @return void
	 */
	public function appendExternalContactCatalogKeys(array &$substArray, ?object $object): void
	{
		$this->getProvider('standard')->appendExternalContactCatalogKeys($substArray, $object);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param object|null $thirdparty
	 * @return void
	 */
	public function appendThirdpartyCatalogKeys(array &$substArray, ?object $thirdparty = null): void
	{
		$this->getProvider('standard')->appendThirdpartyCatalogKeys($substArray, $thirdparty);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param object|null $referenceletters
	 * @return void
	 */
	public function appendReferenceLetterCatalogKeys(array &$substArray, ?object $referenceletters): void
	{
		$this->getProvider('standard')->appendReferenceLetterCatalogKeys($substArray, $referenceletters);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,string> $groupLabels
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendScopedAgefoddCatalogKeys(array &$substArray, array $groupLabels, array $context = array()): void
	{
		$context['group_labels'] = $groupLabels;
		$this->getProvider('agefodd')->appendCatalogKeys($substArray, $context);
	}

	/**
	 * Append automatically detected keys that are not already listed in UI groups.
	 *
	 * @param array<string,mixed> $substArray
	 * @param string|null $elementType
	 * @param object|null $object
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendDetectedCatalogKeys(array &$substArray, $elementType, ?object $object = null, array $context = array()): void
	{
		if (!is_object($object)) {
			return;
		}

		$elementType = (string) $elementType;

		$isAgefodd = !empty($context['is_agefodd']);
		$isAgefoddFormation = !empty($context['is_agefodd_formation']);
		$detectedTags = $this->collectAvailableTags($elementType, $object, $isAgefodd, $isAgefoddFormation);

		$this->lastDetectedMetadata = array();

		foreach ($detectedTags as $tag => $meta) {
			$descriptor = $this->buildDetectedTagDescriptor($tag, $meta, $elementType, $isAgefodd);
			$this->lastDetectedMetadata[$tag] = $descriptor;

			if ($this->hasTag($substArray, $tag)) {
				$this->lastDetectedMetadata[$tag]['already_visible'] = true;
				continue;
			}

			if ($descriptor['visibility'] === 'hidden') {
				continue;
			}

			$groupLabel = $descriptor['group_label'];
			if (!isset($substArray[$groupLabel]) || !is_array($substArray[$groupLabel])) {
				$substArray[$groupLabel] = array();
			}

			$substArray[$groupLabel][$tag] = $descriptor['description'];
		}
	}

	/**
	 * Build metadata for all detected keys, including keys already visible in the UI.
	 *
	 * @param string|null $elementType
	 * @param object|null $object
	 * @param array<string,mixed> $context
	 * @return array<string,array<string,mixed>>
	 */
	public function buildDetectedCatalogMetadata($elementType, ?object $object = null, array $context = array()): array
	{
		if (!is_object($object)) {
			return array();
		}

		$elementType = (string) $elementType;

		$isAgefodd = !empty($context['is_agefodd']);
		$isAgefoddFormation = !empty($context['is_agefodd_formation']);
		$detectedTags = $this->collectAvailableTags($elementType, $object, $isAgefodd, $isAgefoddFormation);
		$rows = array();

		foreach ($detectedTags as $tag => $meta) {
			$rows[$tag] = $this->buildDetectedTagDescriptor($tag, $meta, $elementType, $isAgefodd);
		}

		return $rows;
	}

	/**
	 * Collect keys exposed by the current runtime object and renderer.
	 *
	 * @param string $elementType
	 * @param object $object
	 * @param bool $isAgefodd
	 * @param bool $isAgefoddFormation
	 * @return array<string,array<string,string>>
	 */
	protected function collectAvailableTags(string $elementType, object $object, bool $isAgefodd, bool $isAgefoddFormation): array
	{
		$tags = array();

		$this->mergeDetectedTags($tags, $this->prefixKeys($this->docgen->get_substitutionarray_each_var_object($object, $this->langs), 'objvar_'), 'dynamic_object');

		$dynamicMap = $this->docgen->get_substitutionarray_each_var_object($object, $this->langs);
		if (is_array($dynamicMap)) {
			if (!empty($dynamicMap['object_cond_reglement_code'])) {
				$tags['objvar_object_cond_reglement_doc'] = array('source' => 'dynamic_object');
			}
			if (!empty($dynamicMap['object_mode_reglement_code'])) {
				$tags['objvar_object_mode_reglement'] = array('source' => 'dynamic_object');
			}
		}

		if (method_exists($this->docgen, 'get_substitutionarray_object') && $this->shouldCollectObjectSubstitutions($object, $isAgefodd)) {
			$this->mergeDetectedTags($tags, $this->docgen->get_substitutionarray_object($object, $this->langs), 'object');
		}

		if (method_exists($object, 'fetch_thirdparty') && !empty($object->thirdparty) && is_object($object->thirdparty)) {
			$this->mergeDetectedTags($tags, $this->prefixKeys($this->docgen->get_substitutionarray_thirdparty($object->thirdparty, $this->langs), 'cust_'), 'thirdparty');
		}

		if ($isAgefodd) {
			if ($isAgefoddFormation && method_exists($this->docgen, 'get_substitutionsarray_agefodd_formation')) {
				$this->mergeDetectedTags($tags, $this->docgen->get_substitutionsarray_agefodd_formation($object, $this->langs), 'agefodd');
			} elseif (method_exists($this->docgen, 'get_substitutionsarray_agefodd')) {
				$this->mergeDetectedTags($tags, $this->docgen->get_substitutionsarray_agefodd($object, $this->langs), 'agefodd');
			}
		}

		if (!empty($object->lines) && is_array($object->lines)) {
			require_once DOL_DOCUMENT_ROOT . '/core/lib/doc.lib.php';

			foreach ($object->lines as $line) {
				if (!is_object($line)) {
					continue;
				}
				$this->mergeDetectedTags($tags, $this->docgen->get_substitutionarray_lines($line, $this->langs, 0), 'line');
				break;
			}
		}

		ksort($tags);
		return $tags;
	}

	/**
	 * Keep detected object_* keys aligned with runtime exposure rules.
	 *
	 * @param object $object
	 * @param bool $isAgefodd
	 * @return bool
	 */
	protected function shouldCollectObjectSubstitutions(object $object, bool $isAgefodd): bool
	{
		if ($isAgefodd) {
			return false;
		}

		$excludedClasses = array(
			'Societe',
			'Contact',
			'ModelePDFReferenceLetters',
			'TCPDFRefletters',
			'Agsession',
			'Formation',
		);

		return !in_array(get_class($object), $excludedClasses, true);
	}

	/**
	 * @param array<string,array<string,string>> $target
	 * @param array<string,mixed>|null $tagArray
	 * @param string $source
	 * @return void
	 */
	protected function mergeDetectedTags(array &$target, $tagArray, string $source): void
	{
		if (!is_array($tagArray)) {
			return;
		}

		foreach ($tagArray as $tag => $value) {
			if (!is_string($tag) || $tag === '') {
				continue;
			}
			if (!isset($target[$tag])) {
				$target[$tag] = array('source' => $source);
			}
		}
	}

	/**
	 * @param array<string,mixed>|null $tagArray
	 * @param string $prefix
	 * @return array<string,mixed>
	 */
	protected function prefixKeys($tagArray, string $prefix): array
	{
		$result = array();
		if (!is_array($tagArray)) {
			return $result;
		}

		foreach ($tagArray as $tag => $value) {
			if (!is_string($tag) || $tag === '') {
				continue;
			}
			$result[$prefix . $tag] = $value;
		}

		return $result;
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param string $tag
	 * @return bool
	 */
	protected function hasTag(array $substArray, string $tag): bool
	{
		foreach ($substArray as $groupValues) {
			if (!is_array($groupValues)) {
				continue;
			}
			if (array_key_exists($tag, $groupValues)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $tag
	 * @param string $visibility
	 * @return string
	 */
	protected function buildDescription(string $tag, string $visibility): string
	{
		$prefix = $visibility === 'advanced' ? 'Cle avancee detectee automatiquement : ' : 'Cle detectee automatiquement : ';
		return $prefix . $tag;
	}

	/**
	 * @param string $tag
	 * @param array<string,string> $meta
	 * @param string $elementType
	 * @param bool $isAgefodd
	 * @return array<string,mixed>
	 */
	protected function buildDetectedTagDescriptor(string $tag, array $meta, string $elementType, bool $isAgefodd): array
	{
		$source = isset($meta['source']) ? $meta['source'] : 'unknown';
		$visibility = $this->visibilityPolicy->getVisibility($tag, $source);
		$groupLabel = $this->groupingPolicy->resolveGroupLabel($tag, $elementType, $isAgefodd);

		return array(
			'tag' => $tag,
			'source' => $source,
			'classification' => $this->classifyDetectedTag($tag, $source),
			'visibility' => $visibility,
			'group_label' => $groupLabel,
			'description' => $this->buildDescription($tag, $visibility),
			'already_visible' => false,
		);
	}

	/**
	 * @param string $tag
	 * @param string $source
	 * @return string
	 */
	protected function classifyDetectedTag(string $tag, string $source): string
	{
		if (strpos($tag, 'line_') === 0) {
			return 'segment';
		}
		if (strpos($tag, 'objvar_') === 0) {
			return 'dynamic_object';
		}
		if (strpos($tag, 'cust_') === 0) {
			return 'thirdparty';
		}
		if ($source === 'agefodd') {
			return 'agefodd_scalar';
		}
		if ($source === 'object') {
			return 'object_scalar';
		}
		return 'scalar';
	}

	/**
	 * @param string $name
	 * @return SubstitutionCatalogProviderInterface|null
	 */
	protected function getProvider(string $name): ?SubstitutionCatalogProviderInterface
	{
		return isset($this->providers[$name]) ? $this->providers[$name] : null;
	}
}
