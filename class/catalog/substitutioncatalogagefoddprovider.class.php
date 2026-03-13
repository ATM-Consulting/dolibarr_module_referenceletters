<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddformationprovider.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddsessionprovider.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddtraineeprovider.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddtrainerprovider.class.php';
require_once __DIR__ . '/substitutioncatalogagefoddconventionprovider.class.php';

/**
 * Orchestrates Agefodd-specific catalog providers.
 */
class SubstitutionCatalogAgefoddProvider implements SubstitutionCatalogProviderInterface
{
	/** @var SubstitutionCatalogProviderInterface[] */
	protected array $providers = array();

	/**
	 * @param DoliDB $db
	 * @param Translate $langs
	 */
	public function __construct(DoliDB $db, Translate $langs)
	{
		$this->providers = array(
			'formation' => new SubstitutionCatalogAgefoddFormationProvider($db, $langs),
			'session' => new SubstitutionCatalogAgefoddSessionProvider($db, $langs),
			'trainee' => new SubstitutionCatalogAgefoddTraineeProvider($db, $langs),
			'trainer' => new SubstitutionCatalogAgefoddTrainerProvider($langs),
			'convention' => new SubstitutionCatalogAgefoddConventionProvider($langs),
		);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['is_agefodd'])) {
			return;
		}

		$this->getProvider('formation')->appendCatalogKeys($substArray, $context);
		$this->getProvider('session')->appendCatalogKeys($substArray, $context);
		$this->getProvider('trainee')->appendCatalogKeys($substArray, $context);
		$this->getProvider('trainer')->appendCatalogKeys($substArray, $context);
		$this->getProvider('convention')->appendCatalogKeys($substArray, $context);
	}

	/**
	 * @param string $name
	 * @return SubstitutionCatalogProviderInterface|null
	 */
	protected function getProvider(string $name): ?SubstitutionCatalogProviderInterface
	{
		return isset($this->providers[$name]) ? $this->providers[$name] : null;
	}
}
