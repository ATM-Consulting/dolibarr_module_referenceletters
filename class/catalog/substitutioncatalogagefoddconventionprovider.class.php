<?php

require_once __DIR__ . '/substitutioncatalogagefoddabstractprovider.class.php';

/**
 * Provides Agefodd convention-specific catalog keys.
 */
class SubstitutionCatalogAgefoddConventionProvider extends SubstitutionCatalogAgefoddAbstractProvider
{
	/**
	 * @param Translate $langs
	 */
	public function __construct(Translate $langs)
	{
		parent::__construct(null, $langs);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['is_agefodd']) || empty($context['is_convention_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (empty($groupLabels['convention'])) {
			return;
		}

		$substArray[$groupLabels['convention']] = $this->translateTags(array(
			'objvar_object_signataire_intra', 'objvar_object_signataire_intra_poste',
			'objvar_object_signataire_intra_mail', 'objvar_object_signataire_intra_phone',
			'objvar_object_signataire_inter', 'objvar_object_signataire_inter_poste',
			'objvar_object_signataire_inter_mail', 'objvar_object_signataire_inter_phone',
			'objvar_object_convention_notes', 'objvar_object_convention_id',
			'objvar_object_convention_intro1', 'objvar_object_convention_intro2',
			'objvar_object_convention_art1', 'objvar_object_convention_art2',
			'objvar_object_convention_art3', 'objvar_object_convention_art4',
			'objvar_object_convention_art5', 'objvar_object_convention_art6',
			'objvar_object_convention_art7', 'objvar_object_convention_art8',
			'objvar_object_convention_art9', 'objvar_object_convention_sig',
			'objvar_object_signataire_intra_prof1', 'objvar_object_signataire_intra_prof2',
		));
	}
}
