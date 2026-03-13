<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddabstractprovider.class.php';

/**
 * Provides Agefodd session-related catalog groups.
 */
class SubstitutionCatalogAgefoddSessionProvider extends SubstitutionCatalogAgefoddAbstractProvider
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
		if (empty($context['is_agefodd']) || empty($context['is_session_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		$extrafields = new ExtraFields($this->db);

		$substArray[$groupLabels['session']] = $this->translateTags(array(
			'formation_nom', 'formation_nom_custo', 'formation_ref', 'formation_id', 'formation_programme',
			'formation_statut', 'formation_date_debut', 'formation_date_debut_formated',
			'formation_date_fin', 'formation_date_fin_formated', 'objvar_object_date_text',
			'formation_duree', 'formation_duree_session', 'session_nb_days', 'formation_commercial',
			'formation_commercial_phone', 'formation_commercial_mobile_phone', 'formation_commercial_mail',
			'formation_societe', 'formation_commentaire', 'formation_type', 'formation_but',
			'formation_methode', 'formation_moyens_pedagogique', 'formation_nb_stagiaire',
			'formation_nb_stagiaire_convention', 'formation_nb_place', 'formation_stagiaire_convention',
			'formation_type_stagiaire', 'formation_documents', 'formation_equipements',
			'formation_lieu_phone', 'formation_prerequis', 'formation_prix', 'formation_ref_produit',
			'formation_refint', 'formation_obj_peda', 'formation_lieu', 'formation_lieu_adresse',
			'formation_lieu_cp', 'formation_lieu_ville', 'formation_lieu_acces',
			'formation_lieu_horaires', 'formation_lieu_notes', 'formation_lieu_divers',
			'formation_Accessibility_Handicap_label', 'formation_Accessibility_Handicap',
			'formation_commercial_invert', 'stagiaire_presence_total', 'stagiaire_presence_bloc',
			'stagiaire_temps_realise_total', 'stagiaire_temps_att_total',
			'stagiaire_temps_realise_att_total', 'time_stagiaire_temps_realise_total',
			'time_stagiaire_temps_att_total', 'time_stagiaire_temps_realise_att_total',
			'AgfMentorList', 'Mentor_administrator', 'Mentor_pedagogique', 'Mentor_handicap',
		)) + array(
			'presta_lastname' => $this->langs->trans('PrestaLastname'),
			'presta_firstname' => $this->langs->trans('PrestaFirstname'),
			'presta_soc_name' => $this->langs->trans('PrestaSocName'),
			'presta_soc_id' => $this->langs->trans('PrestaSocId'),
			'presta_soc_name_alias' => $this->langs->trans('PrestaSocNameAlias'),
			'presta_soc_code_client' => $this->langs->trans('PrestaSocCode'),
			'presta_soc_code_fournisseur' => $this->langs->trans('PrestaSocSupplier'),
			'presta_soc_email' => $this->langs->trans('PrestaSocEmail'),
			'presta_soc_phone' => $this->langs->trans('PrestaSocPhone'),
			'presta_soc_fax' => $this->langs->trans('PrestaSocFax'),
			'presta_soc_address' => $this->langs->trans('PrestaSocAddress'),
			'presta_soc_zip' => $this->langs->trans('PrestaSocZip'),
			'presta_soc_town' => $this->langs->trans('PrestaSocTown'),
			'presta_soc_country_id' => $this->langs->trans('PrestaSocCountryId'),
			'presta_soc_country_code' => $this->langs->trans('PrestaSocCountryCode'),
			'presta_soc_idprof1' => $this->langs->trans('PrestaSocIdprof1'),
			'presta_soc_idprof2' => $this->langs->trans('PrestaSocIdprof2'),
			'presta_soc_idprof3' => $this->langs->trans('PrestaSocIdprof3'),
			'presta_soc_idprof4' => $this->langs->trans('PrestaSocIdprof4'),
			'presta_soc_idprof5' => $this->langs->trans('PrestaSocIdprof5'),
			'presta_soc_idprof6' => $this->langs->trans('PrestaSocIdprof6'),
			'presta_soc_tvaintra' => $this->langs->trans('PrestaSocTvaIntra'),
			'presta_soc_note_public' => $this->langs->trans('PrestaSocNotePublic'),
			'presta_soc_note_private' => $this->langs->trans('PrestaSocNotePrivate'),
			'objvar_object_steps_date_text_without_tr' => $this->langs->trans('StepsDateTextWithoutTr'),
			'objvar_object_steps_date_text' => $this->langs->trans('StepsDateText'),
			'objvar_object_steps_facetoface_date_text_without_tr' => $this->langs->trans('StepsFaceToFaceDateTextWithoutTr'),
			'objvar_object_steps_facetoface_date_text' => $this->langs->trans('StepsFaceToFaceDateText'),
			'objvar_object_steps_remote_date_text' => $this->langs->trans('StepsRemoteDateText'),
		);

		$substArray[$groupLabels['participants']] = $this->translateTags(array(
			'line_civilite', 'line_civilite_short', 'line_nom', 'line_prenom', 'line_nom_societe',
			'line_societe_address', 'line_societe_town', 'line_societe_zip', 'line_societe_mail',
			'line_poste', 'line_phone', 'line_phone_pro', 'line_phone_mobile', 'line_email',
			'line_siret', 'line_birthday', 'line_birthdayformated', 'line_birthplace',
			'line_place_birth', 'line_type', 'line_code_societe', 'line_statut', 'line_presence_bloc',
			'line_presence_total', 'line_stagiaire_presence_bloc', 'line_stagiaire_presence_total',
			'line_stagiaire_temps_realise_total', 'line_stagiaire_temps_att_total',
			'line_stagiaire_temps_realise_att_total', 'line_time_stagiaire_temps_realise_total',
			'line_time_stagiaire_temps_att_total', 'line_time_stagiaire_temps_realise_att_total',
		)) + array(
			'line_financiers_trainee' => $this->langs->trans('FinanciersTrainee'),
			'line_alternate_financier_trainee' => $this->langs->trans('AlternateFinancierTrainee'),
		);

		$stagExtralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);
		if (!empty($stagExtralabels)) {
			foreach ($stagExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['participants']]['line_options_' . $extrakey] = $this->translateExtraFieldLabel($extralabel);
			}
		}

		$socExtralabels = $extrafields->fetch_name_optionals_label('societe', true);
		if (!empty($socExtralabels)) {
			foreach ($socExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['participants']]['line_societe_options_' . $extrakey] = $this->translateCompanyExtraFieldLabel($extralabel);
			}
		}

		$substArray[$groupLabels['participants']]['line_societe_options_sirene_update_date_locale'] = $this->translateTag('line_societe_options_sirene_update_date_locale');
		$substArray[$groupLabels['participants']]['line_societe_options_sirene_update_date_rfc'] = $this->translateTag('line_societe_options_sirene_update_date_rfc');

		if (isModEnabled('agefoddcertificat')) {
			$substArray[$groupLabels['participants']] += $this->translateTags(array(
				'line_certif_code',
				'line_certif_label',
				'line_certif_date_debut',
				'line_certif_date_fin',
				'line_certif_date_alerte',
			));
		}

		$substArray[$groupLabels['steps']] = $this->translateTags(array(
			'line_step_label', 'line_step_date_start', 'line_step_date_end', 'line_step_duration',
			'date_ouverture', 'date_ouverture_prevue', 'date_fin_validite', 'line_step_lieu',
			'line_step_lieu_adresse', 'line_step_lieu_cp', 'line_step_lieu_ville',
			'line_step_lieu_acces', 'line_step_lieu_horaires', 'line_step_lieu_notes',
			'line_step_lieu_divers',
		));

		$substArray[$groupLabels['step']] = $this->translateTags(array(
			'step_label', 'step_date_start', 'step_date_end', 'step_duration',
		));

		$substArray[$groupLabels['horaires']] = $this->translateTags(array(
			'line_date_session', 'line_heure_debut_session', 'line_heure_fin_session',
		));

		$substArray[$groupLabels['formateurs']] = $this->translateTags(array(
			'line_formateur_nom', 'line_formateur_prenom', 'line_formateur_phone',
			'line_formateur_phone_mobile', 'line_formateur_phone_perso', 'line_formateur_mail',
			'line_formateur_address', 'line_formateur_town', 'line_formateur_zip',
			'line_formateur_socname', 'line_formateur_statut',
		));

		$substArray[$groupLabels['financial_lines']] = $this->translateTags(array(
			'line_fin_desciption', 'line_fin_desciption_light_short', 'line_fin_qty',
			'line_fin_tva_tx', 'line_fin_amount_ht', 'line_fin_amount_ttc', 'line_fin_discount',
			'line_fin_pu_ht', 'line_unit',
		));

		if (!empty($groupLabels['training_modules'])) {
			$substArray[$groupLabels['training_modules']] = $this->translateTags(array(
				'line_module_title', 'line_module_duration', 'line_module_obj_peda', 'line_module_content_text',
			));
		}

		$substArray[$groupLabels['pedagogic_objectives']] = $this->translateTags(array(
			'line_objpeda_rang', 'line_objpeda_description',
		));
	}
}
