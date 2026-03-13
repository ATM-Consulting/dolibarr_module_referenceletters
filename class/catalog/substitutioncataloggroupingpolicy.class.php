<?php

/**
 * Resolves the target UI group for automatically detected keys.
 */
class SubstitutionCatalogGroupingPolicy
{
	/** @var Translate */
	protected Translate $langs;

	/**
	 * @param Translate $langs
	 */
	public function __construct(Translate $langs)
	{
		$this->langs = $langs;
	}

	/**
	 * Resolve the display group label for a detected key.
	 *
	 * @param string $tag Detected substitution key.
	 * @param string $elementType Current referenceletters element type.
	 * @param bool $isAgefodd Whether the current document belongs to Agefodd.
	 * @return string
	 */
	public function resolveGroupLabel(string $tag, string $elementType, bool $isAgefodd): string
	{
		if ($isAgefodd) {
			$agefoddGroupPrefixes = array(
				'objvar_object_convention_' => 'RefLtrGroupAgefoddConventionAdvanced',
				'objvar_object_signataire_' => 'RefLtrGroupAgefoddConventionAdvanced',
				'objvar_object_session_catalogue_' => 'RefLtrGroupAgefoddSessionAdvanced',
				'objvar_object_formation_catalogue_' => 'RefLtrGroupAgefoddSessionAdvanced',
				'formation_' => 'RefLtrGroupAgefoddSessionAdvanced',
				'stagiaire_' => 'RefLtrGroupAgefoddSessionAdvanced',
				'time_stagiaire_' => 'RefLtrGroupAgefoddSessionAdvanced',
				'objvar_object_stagiaire_' => 'RefLtrGroupAgefoddTraineeAdvanced',
				'formation_agenda_ics' => 'RefLtrGroupAgefoddTraineeAdvanced',
				'objvar_object_formateur_session_' => 'RefLtrGroupAgefoddTrainerAdvanced',
				'trainer_' => 'RefLtrGroupAgefoddTrainerAdvanced',
				'line_' => 'RefLtrGroupAgefoddLinesAdvanced',
			);

			foreach ($agefoddGroupPrefixes as $prefix => $labelKey) {
				if (strpos($tag, $prefix) === 0) {
					return $this->langs->trans($labelKey);
				}
			}
		}

		$genericGroupPrefixes = array(
			'cust_contactclient_' => 'RefLtrGroupExternalContactsAdvanced',
			'cust_company_' => 'RefLtrGroupThirdpartyAdvanced',
			'line_' => 'RefLtrGroupLinesAdvanced',
			'objvar_' => 'RefLtrGroupObjectAdvanced',
			'object_' => 'RefLtrGroupObjectAdvanced',
			'referenceletters_' => 'RefLtrGroupReferenceLettersAdvanced',
		);

		foreach ($genericGroupPrefixes as $prefix => $labelKey) {
			if (strpos($tag, $prefix) === 0) {
				return $this->langs->trans($labelKey);
			}
		}

		return $this->langs->trans($isAgefodd ? 'RefLtrGroupAgefoddAdvanced' : 'RefLtrGroupOtherAdvancedKeys');
	}
}
