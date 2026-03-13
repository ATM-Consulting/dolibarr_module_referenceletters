<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Shared translation helpers for Agefodd catalog providers.
 */
abstract class SubstitutionCatalogAgefoddAbstractProvider implements SubstitutionCatalogProviderInterface
{
	/** @var DoliDB|null */
	protected $db;

	/** @var Translate */
	protected $langs;

	/**
	 * @param DoliDB|null $db
	 * @param Translate $langs
	 */
	public function __construct($db, Translate $langs)
	{
		$this->db = $db;
		$this->langs = $langs;
		$this->langs->load('referenceletters@referenceletters');
		$this->langs->load('refflettersubtitution@referenceletters');
	}

	/**
	 * @param array<int,string> $tags
	 * @return array<string,string>
	 */
	protected function translateTags(array $tags): array
	{
		$translated = array();
		foreach ($tags as $tag) {
			$translated[$tag] = $this->translateTag($tag);
		}

		return $translated;
	}

	/**
	 * @param string $tag
	 * @return string
	 */
	protected function translateTag(string $tag): string
	{
		$key = 'reflettershortcode_' . $tag;
		$translated = $this->langs->trans($key);

		return ($translated !== $key) ? $translated : $tag;
	}

	/**
	 * @param string $fieldLabel
	 * @return string
	 */
	protected function translateExtraFieldLabel(string $fieldLabel): string
	{
		return $this->langs->trans('RefLtrCatalogExtraFieldLabel', $fieldLabel);
	}

	/**
	 * @param string $fieldLabel
	 * @return string
	 */
	protected function translateCompanyExtraFieldLabel(string $fieldLabel): string
	{
		return $this->langs->trans('RefLtrCatalogCompanyExtraFieldLabel', $fieldLabel);
	}

	/**
	 * @param string $fieldLabel
	 * @return string
	 */
	protected function translateFormationExtraFieldLabel(string $fieldLabel): string
	{
		return $this->langs->trans('RefLtrCatalogFormationExtraFieldLabel', $fieldLabel);
	}
}
