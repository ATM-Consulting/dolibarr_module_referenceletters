<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides Agefodd session-related catalog groups.
 */
class SubstitutionCatalogAgefoddSessionProvider implements SubstitutionCatalogProviderInterface
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

		if (empty($context['is_agefodd']) || empty($context['is_session_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		$extrafields = new ExtraFields($this->db);

		$substArray[$groupLabels['session']] = array(
			'formation_nom' => 'Intitulé de la formation',
			'formation_nom_custo' => 'Intitulé formation (pour les documents PDF)',
			'formation_ref' => 'Référence de la formation',
			'formation_id' => 'Id de la formation',
			'formation_programme' => 'Programme de la formation',
			'formation_statut' => 'Statut de la formation',
			'formation_date_debut' => 'Date de début de la formation',
			'formation_date_debut_formated' => 'Date de début de la formation mise en forme',
			'formation_date_fin' => 'Date de fin de la formation',
			'formation_date_fin_formated' => 'Date de fin de la formation mise en forme',
			'objvar_object_date_text' => 'Date de la session',
			'formation_duree' => 'Durée de la formation',
			'formation_duree_session' => 'Durée de la session',
			'session_nb_days' => 'Nombre de jours dans le calendrier de la session',
			'formation_commercial' => 'commercial en charge de la formation',
			'formation_commercial_phone' => 'téléphone commercial en charge de la formation',
			'formation_commercial_mobile_phone' => 'téléphone mobile du commercial en charge de la formation',
			'formation_commercial_mail' => 'email commercial en charge de la formation',
			'formation_societe' => 'Société concernée',
			'formation_commentaire' => 'Commentaire de la session',
			'formation_type' => 'Type de session',
			'formation_but' => 'But de la formation',
			'formation_methode' => 'Methode de formation',
			'formation_moyens_pedagogique' => 'Moyens pédagogiques',
			'formation_nb_stagiaire' => 'Nombre de stagiaire de la formation',
			'formation_nb_stagiaire_convention' => 'Nombre de stagiaires convention',
			'formation_nb_place' => 'Nombre de places',
			'formation_stagiaire_convention' => 'Libellé stagiaire convention',
			'formation_type_stagiaire' => 'Caractéristiques des stagiaires',
			'formation_documents' => 'Documents nécessaires à la formation',
			'formation_equipements' => 'Equipements nécessaires à la formation',
			'formation_lieu_phone' => 'Téléphone du lieu de formation',
			'formation_prerequis' => 'Pré-requis',
			'formation_prix' => 'Prix de la session',
			'formation_ref_produit' => 'Référence produit liée',
			'formation_refint' => 'Référence interne session',
			'formation_obj_peda' => 'Objectifs pédagogiques',
			'formation_lieu' => 'Lieu de la formation',
			'formation_lieu_adresse' => 'Adresse du lieu de formation',
			'formation_lieu_cp' => 'Code postal du lieu de formation',
			'formation_lieu_ville' => 'Ville du lieu de formation',
			'formation_lieu_acces' => 'Instruction d\'accès au lieu lieu de formation',
			'formation_lieu_horaires' => 'Horaires du lieu de formation',
			'formation_lieu_notes' => 'Commentaire du lieu de formation',
			'formation_lieu_divers' => 'Infos Repas, Hébergements, divers',
			'formation_Accessibility_Handicap_label' => 'Titre Accessibilité Handicap',
			'formation_Accessibility_Handicap' => 'Accessible aux personnes handicapés',
			'formation_commercial_invert' => 'Commercial en charge (prenom nom)',
			'stagiaire_presence_total' => 'Nombre d heure de présence par participants',
			'stagiaire_presence_bloc' => 'Présentation en bloc des heures de présences participants',
			'stagiaire_temps_realise_total' => 'Nombre d heure des sessions au statut "Réalisé"',
			'stagiaire_temps_att_total' => 'Nombre d heure des sessions au statut "Annulé trop tard"',
			'stagiaire_temps_realise_att_total' => 'Nombre d heure des sessions au statut "Réalisé" + "Annulé trop tard"',
			'time_stagiaire_temps_realise_total' => 'Temps réalise total formaté',
			'time_stagiaire_temps_att_total' => 'Temps annulé tardif formaté',
			'time_stagiaire_temps_realise_att_total' => 'Temps réalisé + annulé tardif formaté',
			'AgfMentorList' => 'Liste des référents',
			'Mentor_administrator' => 'Référent Administratif',
			'Mentor_pedagogique' => 'Référent pédagogique',
			'Mentor_handicap' => 'Référent handicap',
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
			'objvar_object_trainer_text' => 'Tous les foramteurs séparés par des virgules (Nom prenom)',
			'objvar_object_trainer_text_invert' => 'Tous les foramteurs séparés par des virgules (Prenom nom)',
			'objvar_object_id' => 'Id de la session',
			'trainer_cost_planned' => 'Coût planifié formateur',
			'trainer_datehourtextline' => 'Horaire(s) calendrier formateur',
			'trainer_datetextline' => 'Date(s) calendrier formateur',
			'objvar_object_dthour_text' => 'Tous les horaires au format texte avec retour à la ligne',
			'objvar_object_trainer_day_cost' => 'Cout formateur (cout/nb de creneaux)'
		);

		$substArray[$groupLabels['participants']] = array(
			'line_civilite' => 'Libellé civilité',
			'line_civilite_short' => 'Code civilité',
			'line_nom' => 'Nom participant',
			'line_prenom' => 'Prénom participant',
			'line_nom_societe' => 'Société du participant',
			'line_societe_address' => 'Adresse de la société du participant',
			'line_societe_town' => 'Ville de la société du participant',
			'line_societe_zip' => 'Code postal de la société du participant',
			'line_societe_mail' => 'Adresse mail de la société du participant',
			'line_poste' => 'Poste occupé au sein de sa société',
			'line_phone' => 'Téléphone pro / Téléphone mobile',
			'line_phone_pro' => 'Téléphone pro',
			'line_phone_mobile' => 'Téléphone mobile',
			'line_email' => 'Email du participant',
			'line_siret' => 'SIRET de la société du participant',
			'line_birthday' => 'Date de naissance du participant',
			'line_birthdayformated' => 'Date de naissance du participant formatée',
			'line_birthplace' => 'Lieu de naissance du participant',
			'line_place_birth' => 'Lieu de naissance du participant',
			'line_type' => 'Type de financement',
			'line_code_societe' => 'Code de la société du participant',
			'line_statut' => 'Statut du participant dans la session',
			'line_presence_bloc' => 'Bloc des présences détaillées',
			'line_presence_total' => 'Total des présences',
			'line_stagiaire_presence_bloc' => 'Bloc des présences stagiaire',
			'line_stagiaire_presence_total' => 'Temps de présence total stagiare',
			'line_stagiaire_temps_realise_total' => 'Temps réalisé total',
			'line_stagiaire_temps_att_total' => 'Temps annulé tardif total',
			'line_stagiaire_temps_realise_att_total' => 'Temps réalisé + annulé tardif total',
			'line_time_stagiaire_temps_realise_total' => 'Temps réalisé total formaté',
			'line_time_stagiaire_temps_att_total' => 'Temps annulé tardif formaté',
			'line_time_stagiaire_temps_realise_att_total' => 'Temps réalisé + annulé tardif formaté',
			'line_financiers_trainee' => $this->langs->trans('FinanciersTrainee'),
			'line_alternate_financier_trainee' => $this->langs->trans('AlternateFinancierTrainee')
		);

		$stagExtralabels = $extrafields->fetch_name_optionals_label('agefodd_stagiaire', true);
		if (!empty($stagExtralabels)) {
			foreach ($stagExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['participants']]['line_options_' . $extrakey] = 'Champ complémentaire : ' . $extralabel;
			}
		}

		$socExtralabels = $extrafields->fetch_name_optionals_label('societe', true);
		if (!empty($socExtralabels)) {
			foreach ($socExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['participants']]['line_societe_options_' . $extrakey] = 'Champ complémentaire société : ' . $extralabel;
			}
		}

		$substArray[$groupLabels['participants']]['line_societe_options_sirene_update_date_locale'] = 'Champ complémentaire société : date MAJ Sirene formatée';
		$substArray[$groupLabels['participants']]['line_societe_options_sirene_update_date_rfc'] = 'Champ complémentaire société : date MAJ Sirene RFC';

		if (!empty($conf->agefoddcertificat->enabled)) {
			$substArray[$groupLabels['participants']]['line_certif_code'] = 'Numéro du certificat';
			$substArray[$groupLabels['participants']]['line_certif_label'] = 'Libellé du certificat';
			$substArray[$groupLabels['participants']]['line_certif_date_debut'] = 'Date de début du certificat';
			$substArray[$groupLabels['participants']]['line_certif_date_fin'] = 'Date de fin du certificat';
			$substArray[$groupLabels['participants']]['line_certif_date_alerte'] = 'Date d\'alerte du certificat';
		}

		$substArray[$groupLabels['steps']] = array(
			'line_step_label' => 'Label de l\'étape',
			'line_step_date_start' => 'Date de début de l\'étape',
			'line_step_date_end' => 'Date de fin de l\'étape',
			'line_step_duration' => 'Durée de l\'étape',
			'date_ouverture' => 'Date démarrage réelle étape',
			'date_ouverture_prevue' => 'Date prévue de démarrage étape',
			'date_fin_validite' => 'Date fin réelle étape',
			'line_step_lieu' => 'Lieu de la formation',
			'line_step_lieu_adresse' => 'Adresse du lieu de l\'étape',
			'line_step_lieu_cp' => 'Code postal du lieu de l\'étape',
			'line_step_lieu_ville' => 'Ville du lieu de l\'étape',
			'line_step_lieu_acces' => 'Instruction d\'accès au lieu lieu de l\'étape',
			'line_step_lieu_horaires' => 'Horaires du lieu de l\'étape',
			'line_step_lieu_notes' => 'Commentaire du lieu de l\'étape',
			'line_step_lieu_divers' => 'Infos Repas, Hébergements, divers'
		);

		$substArray[$groupLabels['step']] = array(
			'step_label' => 'Label de l\'étape',
			'step_date_start' => 'Date de début de l\'étape',
			'step_date_end' => 'Date de fin de l\'étape',
			'step_duration' => 'Durée de l\'étape',
		);

		$substArray[$groupLabels['horaires']] = array(
			'line_date_session' => 'Date de la session',
			'line_heure_debut_session' => 'Heure début session',
			'line_heure_fin_session' => 'Heure fin session'
		);

		$substArray[$groupLabels['formateurs']] = array(
			'line_formateur_nom' => 'Nom du formateur',
			'line_formateur_prenom' => 'Prénom du formateur',
			'line_formateur_phone' => 'Téléphone du formateur',
			'line_formateur_phone_mobile' => 'Téléphone mobile du formateur',
			'line_formateur_phone_perso' => 'Téléphone perso du formateur',
			'line_formateur_mail' => 'Adresse mail du formateur',
			'line_formateur_address' => 'Adresse du formateur',
			'line_formateur_town' => 'Ville du formateur',
			'line_formateur_zip' => 'Code postal du formateur',
			'line_formateur_socname' => 'Nom de la société associée au formateur',
			'line_formateur_statut' => 'Statut du formateur (Présent, Confirmé, etc...)'
		);

		$substArray[$groupLabels['financial_lines']] = array(
			'line_fin_desciption' => 'Description complète ligne financière',
			'line_fin_desciption_light_short' => 'Description courte ligne financière',
			'line_fin_qty' => 'Quantité ligne financière',
			'line_fin_tva_tx' => 'TVA ligne financière',
			'line_fin_amount_ht' => 'Montant HT ligne financière',
			'line_fin_amount_ttc' => 'Montant TTC ligne financière',
			'line_fin_discount' => 'Remise ligne financière',
			'line_fin_pu_ht' => 'Prix unitaire HT ligne financière',
			'line_unit' => 'Unité ligne financière',
		);

		if (!empty($groupLabels['training_modules'])) {
			$substArray[$groupLabels['training_modules']] = array(
				'line_module_title' => 'Titre du module de formation',
				'line_module_duration' => 'Durée du module de formation',
				'line_module_obj_peda' => 'Objectifs pédagogiques du module',
				'line_module_content_text' => 'Contenu du module de formation',
			);
		}

		$substArray[$groupLabels['pedagogic_objectives']] = array(
			'line_objpeda_rang' => 'Rang objectif pédagogique',
			'line_objpeda_description' => 'Description objectif pédagogique',
		);
	}
}
