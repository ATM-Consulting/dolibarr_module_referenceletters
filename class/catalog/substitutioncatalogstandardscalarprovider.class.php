<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides generic scalar keys for standard Dolibarr documents.
 */
class SubstitutionCatalogStandardScalarProvider implements SubstitutionCatalogProviderInterface
{
	/** @var Translate */
	protected Translate $langs;

	/**
	 * @param Translate $langs
	 */
	public function __construct(Translate $langs)
	{
		$this->langs = $langs;
		$this->langs->load('reflettersubstitution@referenceletters');
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		$elementType = isset($context['element_type']) ? $context['element_type'] : '';
		$scalarKeys = array('devise_label');

		if (in_array($elementType, array('expedition', 'shipping'), true)) {
			$scalarKeys[] = 'object_tracking_number';
			$scalarKeys[] = 'object_total_weight';
			$scalarKeys[] = 'object_total_volume';
			$scalarKeys[] = 'object_total_qty_ordered';
			$scalarKeys[] = 'object_total_qty_toship';
		}

		foreach ($scalarKeys as $key) {
			$substArray[$key] = $this->translateTag($key);
		}
	}

	/**
	 * @param string $tag
	 * @return string
	 */
	protected function translateTag(string $tag): string
	{
		$key = 'reflettershortcode_' . $tag;
		$translated = method_exists($this->langs, 'transnoentitiesnoconv')
			? $this->langs->transnoentitiesnoconv($key)
			: $this->langs->trans($key);

		return ($translated !== $key) ? $translated : $tag;
	}
}
