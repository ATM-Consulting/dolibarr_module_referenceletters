<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides generic scalar keys for standard Dolibarr documents.
 */
class SubstitutionCatalogStandardScalarProvider implements SubstitutionCatalogProviderInterface
{
	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		$elementType = isset($context['element_type']) ? $context['element_type'] : '';
		$scalarLabels = array(
			'devise_label' => 'Libellé de la devise du document',
		);

		if (in_array($elementType, array('expedition', 'shipping'), true)) {
			$scalarLabels['object_tracking_number'] = 'Numéro de suivi expédition';
			$scalarLabels['object_total_weight'] = 'Poids total du document';
			$scalarLabels['object_total_volume'] = 'Volume total du document';
			$scalarLabels['object_total_qty_ordered'] = 'Quantité totale commandée';
			$scalarLabels['object_total_qty_toship'] = 'Quantité totale à expédier';
		}

		foreach ($scalarLabels as $key => $label) {
			$substArray[$key] = $label;
		}
	}
}
