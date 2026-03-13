<?php

/**
 * Resolves the target UI group for automatically detected keys.
 */
class SubstitutionCatalogGroupingPolicy
{
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
				'objvar_object_convention_' => 'Agefodd Convention avance',
				'objvar_object_signataire_' => 'Agefodd Convention avance',
				'objvar_object_session_catalogue_' => 'Agefodd Session avance',
				'objvar_object_formation_catalogue_' => 'Agefodd Session avance',
				'formation_' => 'Agefodd Session avance',
				'stagiaire_' => 'Agefodd Session avance',
				'time_stagiaire_' => 'Agefodd Session avance',
				'objvar_object_stagiaire_' => 'Agefodd Stagiaire avance',
				'formation_agenda_ics' => 'Agefodd Stagiaire avance',
				'objvar_object_formateur_session_' => 'Agefodd Formateur avance',
				'trainer_' => 'Agefodd Formateur avance',
				'line_' => 'Agefodd Lignes avancees',
			);

			foreach ($agefoddGroupPrefixes as $prefix => $label) {
				if (strpos($tag, $prefix) === 0) {
					return $label;
				}
			}
		}

		$genericGroupPrefixes = array(
			'cust_contactclient_' => 'Contacts externes avances',
			'cust_company_' => 'Tiers avance',
			'line_' => 'Lignes avancees',
			'objvar_' => 'Objet avance',
			'object_' => 'Objet avance',
			'referenceletters_' => 'ReferenceLetters avance',
		);

		foreach ($genericGroupPrefixes as $prefix => $label) {
			if (strpos($tag, $prefix) === 0) {
				return $label;
			}
		}

		return $isAgefodd ? 'Agefodd avance' : 'Autres cles avancees';
	}
}
