<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides the Agefodd formation catalogue catalog.
 */
class SubstitutionCatalogAgefoddFormationProvider implements SubstitutionCatalogProviderInterface
{
	/** @var DoliDB */
	protected $db;

	/**
	 * @param DoliDB $db
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['is_agefodd']) || empty($context['is_formation_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (empty($groupLabels['formation_catalogue'])) {
			return;
		}

		$substArray[$groupLabels['formation_catalogue']] = array(
			'formation_nom' => 'Intitulé de la formation',
			'formation_ref' => 'Référence de la formation',
			'formation_id' => 'Id de la formation',
			'formation_programme' => 'Programme de la formation',
			'formation_statut' => 'Statut de la formation',
			'formation_duree' => 'Durée de la formation',
			'formation_but' => 'But de la formation',
			'formation_methode' => 'Methode de formation',
			'formation_nb_place_dispo' => 'nombre de places disponibles',
			'formation_nb_inscription_mini' => 'Nombre minimum d\'inscrits pour confirmer la session',
			'formation_category' => 'Catégorie formation',
			'formation_category_bpf' => 'Catégorie de formation prestation (BPF)',
			'formation_product' => 'Produit ou service associé',
			'formation_type_public' => 'Type de public',
			'formation_methode_pedago' => 'Méthodes pédagogiques',
			'formation_documents' => 'Documents nécessaires à la formation',
			'formation_equipements' => 'Equipements nécessaires à la formation',
			'formation_pre_requis' => 'Pré-requis',
			'formation_moyens_peda' => 'Moyens pédagogiques',
			'formation_sanction' => 'Sanction de la formation',
			'formation_competences' => 'Liste des compétences visées',
			'formation_nature' => 'Nature de l’action concourant au développement des compétences',
			'formation_Accessibility_Handicap' => 'Accessible aux personnes handicapés',
			'AgfMentorList' => 'Liste des référents',
			'Mentor_administrator' => 'Référent Administratif',
			'Mentor_pedagogique' => 'Référent pédagogique',
			'Mentor_handicap' => 'Référent handicap'
		);

		$extrafields = new ExtraFields($this->db);
		$formationExtralabels = $extrafields->fetch_name_optionals_label('agefodd_formation_catalogue', true);
		if (!empty($formationExtralabels)) {
			foreach ($formationExtralabels as $extrakey => $extralabel) {
				$substArray[$groupLabels['formation_catalogue']]['formation_options_' . $extrakey] = 'Champ complémentaire Formation : ' . $extralabel;
			}
		}
	}
}
