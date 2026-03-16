<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddabstractprovider.class.php';

/**
 * Provides the Agefodd formation catalogue catalog.
 */
class SubstitutionCatalogAgefoddFormationProvider extends SubstitutionCatalogAgefoddAbstractProvider
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
		if (empty($context['is_agefodd']) || empty($context['is_formation_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (empty($groupLabels['formation_catalogue'])) {
			return;
		}

		$substArray[$groupLabels['formation_catalogue']] = $this->translateTags(array(
			'formation_nom', 'formation_ref', 'formation_id', 'formation_programme', 'formation_statut',
			'formation_duree', 'formation_but', 'formation_methode', 'formation_nb_place_dispo',
			'formation_nb_inscription_mini', 'formation_category', 'formation_category_bpf',
			'formation_product', 'formation_type_public', 'formation_methode_pedago',
			'formation_documents', 'formation_equipements', 'formation_pre_requis',
			'formation_moyens_peda', 'formation_sanction', 'formation_competences',
			'formation_nature', 'formation_Accessibility_Handicap', 'AgfMentorList',
			'Mentor_administrator', 'Mentor_pedagogique', 'Mentor_handicap',
		));

		$extrafields = new ExtraFields($this->db);
		$formationExtralabels = $extrafields->fetch_name_optionals_label('agefodd_formation_catalogue', true);
		if (!empty($formationExtralabels)) {
			foreach ($formationExtralabels as $extrakey => $extralabel) {
				$langFile = $extrafields->attributes['agefodd_formation_catalogue']['langfile'][$extrakey] ?? '';
				$substArray[$groupLabels['formation_catalogue']]['formation_options_' . $extrakey] = $this->translateFormationExtraFieldLabel($extralabel, (string) $langFile);
			}
		}

		if (!empty($groupLabels['pedagogic_objectives'])) {
			$substArray[$groupLabels['pedagogic_objectives']] = $this->translateTags(array(
				'line_objpeda_rang',
				'line_objpeda_description',
			));
		}

		if (!empty($groupLabels['training_modules'])) {
			$substArray[$groupLabels['training_modules']] = $this->translateTags(array(
				'line_module_title',
				'line_module_duration',
				'line_module_obj_peda',
				'line_module_content_text',
			));
		}
	}
}
