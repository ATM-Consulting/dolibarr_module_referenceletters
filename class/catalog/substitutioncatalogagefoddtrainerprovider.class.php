<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides Agefodd trainer-specific catalog groups.
 */
class SubstitutionCatalogAgefoddTrainerProvider implements SubstitutionCatalogProviderInterface
{
	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['is_agefodd']) || empty($context['is_trainer_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (!empty($groupLabels['trainer_mission'])) {
			$substArray[$groupLabels['trainer_mission']] = array(
				'objvar_object_formateur_session_lastname' => 'Nom du formateur',
				'objvar_object_formateur_session_firstname' => 'Prénom du formateur',
				'trainer_cost_planned' => 'Coût planifié formateur',
				'objvar_object_formateur_session_societe_name' => 'Structure employeuse du formateur',
			);
		}

		if (!empty($groupLabels['trainer_times'])) {
			$substArray[$groupLabels['trainer_times']] = array(
				'trainer_datehourtextline' => 'Horaire(s) calendrier formateur',
				'trainer_datetextline' => 'Date(s) calendrier formateur',
				'formation_agenda_ics' => 'Lien ICS de l\'agenda du formateur',
				'formation_agenda_ics_url' => 'URL du lien ICS de l\'agenda du formateur',
			);
		}
	}
}
