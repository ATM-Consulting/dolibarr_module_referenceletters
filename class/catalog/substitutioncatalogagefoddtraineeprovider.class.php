<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides Agefodd current trainee catalog groups.
 */
class SubstitutionCatalogAgefoddTraineeProvider implements SubstitutionCatalogProviderInterface
{
	/** @var DoliDB */
	protected $db;
	/** @var Translate */
	protected $langs;

	/**
	 * @param DoliDB $db
	 * @param Translate $langs
	 */
	public function __construct(DoliDB $db, Translate $langs)
	{
		$this->db = $db;
		$this->langs = $langs;
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		global $conf;

		if (empty($context['is_agefodd']) || empty($context['is_trainee_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (empty($groupLabels['trainee'])) {
			return;
		}

		$extrafields = new ExtraFields($this->db);
		$substArray[$groupLabels['trainee']] = array(
			'objvar_object_stagiaire_civilitel' => 'Civilité du stagiaire',
			'objvar_object_stagiaire_nom' => 'Nom du stagiaire',
			'objvar_object_stagiaire_prenom' => 'Prénom du stagiaire',
			'objvar_object_stagiaire_mail' => 'Email du stagiaire',
			'objvar_object_stagiaire_socname' => 'Société du participant',
			'objvar_object_stagiaire_socaddr' => 'Adresse de la société du participant',
			'objvar_object_stagiaire_soczip' => 'Code postal de la société du participant',
			'objvar_object_stagiaire_soctown' => 'Ville de la société du participant',
			'objvar_object_lieu_adresse' => 'Adresse du lieu',
			'objvar_object_lieu_ref_interne' => 'Ref interne du lieu',
			'stagiaire_presence_total' => 'Nombre d heure de présence par participants',
			'stagiaire_presence_bloc' => 'Présentation en bloc des heures de présences participants',
			'stagiaire_temps_realise_total' => 'Nombre d heure des sessions au statut "Réalisé"',
			'stagiaire_temps_att_total' => 'Nombre d heure des sessions au statut "Annulé trop tard"',
			'stagiaire_temps_realise_att_total' => 'Nombre d heure des sessions au statut "Réalisé" + "Annulé trop tard"',
			'formation_agenda_ics' => 'Lien ICS de l\'agenda des participants',
			'formation_agenda_ics_url' => 'URL du lien ICS de l\'agenda des participants',
			'objvar_object_financiers_trainee' => $this->langs->trans('FinanciersTrainee'),
			'objvar_object_alternate_financier_trainee' => $this->langs->trans('AlternateFinancierTrainee')
		);

		$stagExtralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);
		if (!empty($stagExtralabels)) {
			foreach ($stagExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_options_' . $extrakey] = 'Champ complémentaire : ' . $extralabel;
			}
		}

		$socExtralabels = $extrafields->fetch_name_optionals_label('societe', true);
		if (!empty($socExtralabels)) {
			foreach ($socExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_soc_options_' . $extrakey] = 'Champ complémentaire société : ' . $extralabel;
			}
		}

		if (!empty($conf->agefoddcertificat->enabled)) {
			$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_certif_code'] = 'Numéro du certificat';
			$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_certif_label'] = 'Libellé du certificat';
			$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_certif_date_debut'] = 'Date de début du certificat';
			$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_certif_date_fin'] = 'Date de fin du certificat';
			$substArray[$groupLabels['trainee']]['objvar_object_stagiaire_certif_date_alerte'] = 'Date d\'alerte du certificat';
		}
	}
}
