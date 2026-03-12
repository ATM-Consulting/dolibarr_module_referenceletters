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
			if (strpos($tag, 'objvar_object_convention_') === 0) {
				return 'Agefodd Convention avance';
			}
			if (strpos($tag, 'objvar_object_session_catalogue_') === 0 || strpos($tag, 'objvar_object_formation_catalogue_') === 0) {
				return 'Agefodd Session avance';
			}
			if (strpos($tag, 'objvar_object_stagiaire_') === 0 || strpos($tag, 'formation_agenda_ics') === 0) {
				return 'Agefodd Stagiaire avance';
			}
			if (strpos($tag, 'objvar_object_formateur_session_') === 0 || strpos($tag, 'trainer_') === 0) {
				return 'Agefodd Formateur avance';
			}
			if (strpos($tag, 'objvar_object_signataire_') === 0) {
				return 'Agefodd Convention avance';
			}
			if (strpos($tag, 'formation_') === 0 || strpos($tag, 'stagiaire_') === 0 || strpos($tag, 'time_stagiaire_') === 0) {
				return 'Agefodd Session avance';
			}
			if (strpos($tag, 'line_') === 0) {
				return 'Agefodd Lignes avancees';
			}
		}

		if (strpos($tag, 'cust_contactclient_') === 0) {
			return 'Contacts externes avances';
		}
		if (strpos($tag, 'cust_company_') === 0) {
			return 'Tiers avance';
		}
		if (strpos($tag, 'line_') === 0) {
			return 'Lignes avancees';
		}
		if (strpos($tag, 'objvar_') === 0) {
			return 'Objet avance';
		}
		if (strpos($tag, 'object_') === 0) {
			return 'Objet avance';
		}
		if (strpos($tag, 'referenceletters_') === 0) {
			return 'ReferenceLetters avance';
		}

		return $isAgefodd ? 'Agefodd avance' : 'Autres cles avancees';
	}
}
