<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides DocEdit-specific `referenceletters_*` keys.
 */
class SubstitutionCatalogReferenceLetterProvider implements SubstitutionCatalogProviderInterface
{
	/** @var CommonDocGeneratorReferenceLetters */
	protected CommonDocGeneratorReferenceLetters $docgen;
	/** @var Translate */
	protected Translate $langs;

	/**
	 * @param CommonDocGeneratorReferenceLetters $docgen
	 * @param Translate $langs
	 */
	public function __construct(CommonDocGeneratorReferenceLetters $docgen, Translate $langs)
	{
		$this->docgen = $docgen;
		$this->langs = $langs;
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		$referenceletters = isset($context['referenceletters']) ? $context['referenceletters'] : null;
		if (!is_object($referenceletters)) {
			return;
		}

		$substArray[$this->langs->trans('Module103258Name')] = $this->docgen->get_substitutionarray_refletter($referenceletters, $this->langs);
	}
}
