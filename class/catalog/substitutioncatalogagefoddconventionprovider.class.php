<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides Agefodd convention-specific catalog keys.
 */
class SubstitutionCatalogAgefoddConventionProvider implements SubstitutionCatalogProviderInterface
{
	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['is_agefodd']) || empty($context['is_convention_doc'])) {
			return;
		}

		$groupLabels = isset($context['group_labels']) && is_array($context['group_labels']) ? $context['group_labels'] : array();
		if (empty($groupLabels['convention'])) {
			return;
		}

		$substArray[$groupLabels['convention']] = array(
			'objvar_object_signataire_intra' => 'Nom du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_intra_poste' => 'Poste du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_intra_mail' => 'Mail du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_intra_phone' => 'Téléphone du signataire des intra-entreprise (contact session)',
			'objvar_object_signataire_inter' => 'Nom des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_signataire_inter_poste' => 'Poste des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_signataire_inter_mail' => 'Mail des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_signataire_inter_phone' => 'Téléphone des signataires des inter-entreprise (signataire sur le participants)',
			'objvar_object_convention_notes' => 'commentaire de la convention',
			'objvar_object_convention_id' => 'identifiant unique de la convention',
			'objvar_object_signataire_intra_prof1' => 'siret du signataire',
			'objvar_object_signataire_intra_prof2' => 'siren du signataire',
		);
	}
}
