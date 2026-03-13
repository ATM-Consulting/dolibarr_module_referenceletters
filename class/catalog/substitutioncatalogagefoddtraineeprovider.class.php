<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddabstractprovider.class.php';

/**
 * Provides Agefodd current trainee catalog groups.
 */
class SubstitutionCatalogAgefoddTraineeProvider extends SubstitutionCatalogAgefoddAbstractProvider
{
	/**
	 * @param DoliDB $db
	 * @param Translate $langs
	 */
	public function __construct(DoliDB $db, Translate $langs)
	{
		parent::__construct($db, $langs);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['is_agefodd']) || empty($context['is_trainee_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (empty($groupLabels['trainee'])) {
			return;
		}

		$extrafields = new ExtraFields($this->db);
		$substArray[$groupLabels['trainee']] = $this->translateTags(array(
			'objvar_object_stagiaire_civilitel', 'objvar_object_stagiaire_nom',
			'objvar_object_stagiaire_prenom', 'objvar_object_stagiaire_mail',
			'objvar_object_stagiaire_socname', 'objvar_object_stagiaire_socaddr',
			'objvar_object_stagiaire_soczip', 'objvar_object_stagiaire_soctown',
			'objvar_object_lieu_adresse', 'objvar_object_lieu_ref_interne',
			'stagiaire_presence_total', 'stagiaire_presence_bloc', 'stagiaire_temps_realise_total',
			'stagiaire_temps_att_total', 'stagiaire_temps_realise_att_total',
			'formation_agenda_ics', 'formation_agenda_ics_url',
		)) + array(
			'objvar_object_financiers_trainee' => $this->langs->trans('FinanciersTrainee'),
			'objvar_object_alternate_financier_trainee' => $this->langs->trans('AlternateFinancierTrainee'),
		);

		$stagExtralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);
		if (!empty($stagExtralabels)) {
			foreach ($stagExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_options_' . $extrakey] = $this->translateExtraFieldLabel($extralabel);
			}
		}

		$socExtralabels = $extrafields->fetch_name_optionals_label('societe', true);
		if (!empty($socExtralabels)) {
			foreach ($socExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_soc_options_' . $extrakey] = $this->translateCompanyExtraFieldLabel($extralabel);
			}
		}

		if (isModEnabled('agefoddcertificat')) {
			$substArray[$groupLabels['trainee']] += $this->translateTags(array(
				'objvar_object_stagiaire_certif_code',
				'objvar_object_stagiaire_certif_label',
				'objvar_object_stagiaire_certif_date_debut',
				'objvar_object_stagiaire_certif_date_fin',
				'objvar_object_stagiaire_certif_date_alerte',
			));
		}
	}
}
