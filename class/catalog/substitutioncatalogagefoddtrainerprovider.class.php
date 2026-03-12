<?php

require_once __DIR__ . '/substitutioncatalogagefoddabstractprovider.class.php';

/**
 * Provides Agefodd trainer-specific catalog groups.
 */
class SubstitutionCatalogAgefoddTrainerProvider extends SubstitutionCatalogAgefoddAbstractProvider
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
		if (empty($context['is_agefodd']) || empty($context['is_trainer_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (!empty($groupLabels['trainer_mission'])) {
			$substArray[$groupLabels['trainer_mission']] = $this->translateTags(array(
				'objvar_object_formateur_session_lastname',
				'objvar_object_formateur_session_firstname',
				'trainer_cost_planned',
				'objvar_object_formateur_session_societe_name',
			));
		}

		if (!empty($groupLabels['trainer_times'])) {
			$substArray[$groupLabels['trainer_times']] = $this->translateTags(array(
				'trainer_datehourtextline',
				'trainer_datetextline',
				'formation_agenda_ics',
				'formation_agenda_ics_url',
			));
		}
	}
}
