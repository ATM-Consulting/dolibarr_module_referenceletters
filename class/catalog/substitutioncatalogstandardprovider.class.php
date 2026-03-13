<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';
require_once __DIR__ . '/substitutioncatalogstandardscalarprovider.class.php';
require_once __DIR__ . '/substitutioncatalogdocumentlineprovider.class.php';
require_once __DIR__ . '/substitutioncatalogcontactprovider.class.php';
require_once __DIR__ . '/substitutioncatalogthirdpartyprovider.class.php';
require_once __DIR__ . '/substitutioncatalogreferenceletterprovider.class.php';

/**
 * Orchestrates standard non-Agefodd catalog families.
 */
class SubstitutionCatalogStandardProvider implements SubstitutionCatalogProviderInterface
{
	/** @var SubstitutionCatalogProviderInterface[] */
	protected $providers = array();

	/**
	 * @param DoliDB $db
	 * @param CommonDocGeneratorReferenceLetters $docgen
	 * @param Translate $langs
	 */
	public function __construct(DoliDB $db, CommonDocGeneratorReferenceLetters $docgen, Translate $langs)
	{
		$this->providers = array(
			'standard_scalar' => new SubstitutionCatalogStandardScalarProvider($langs),
			'document_line' => new SubstitutionCatalogDocumentLineProvider($langs),
			'contact' => new SubstitutionCatalogContactProvider($db, $docgen, $langs),
			'thirdparty' => new SubstitutionCatalogThirdpartyProvider($db),
			'referenceletter' => new SubstitutionCatalogReferenceLetterProvider($docgen, $langs),
		);
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		// Keep the standard provider as a thin orchestrator so each family can evolve independently.
		foreach ($this->providers as $provider) {
			$provider->appendCatalogKeys($substArray, $context);
		}
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param string $elementType
	 * @return void
	 */
	public function appendStandardCatalogKeys(array &$substArray, string $elementType): void
	{
		$this->getProvider('standard_scalar')->appendCatalogKeys($substArray, array('element_type' => $elementType));
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @return void
	 */
	public function appendDocumentLineCatalogKeys(array &$substArray, bool $hasDocumentLines = false): void
	{
		$this->getProvider('document_line')->appendCatalogKeys($substArray, array('has_document_lines' => $hasDocumentLines));
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param object|null $object
	 * @return void
	 */
	public function appendExternalContactCatalogKeys(array &$substArray, ?object $object): void
	{
		$this->getProvider('contact')->appendCatalogKeys($substArray, array('object' => $object));
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @return void
	 */
	public function appendThirdpartyCatalogKeys(array &$substArray, ?object $thirdparty = null): void
	{
		$this->getProvider('thirdparty')->appendCatalogKeys($substArray, array('thirdparty' => $thirdparty));
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param object|null $referenceletters
	 * @return void
	 */
	public function appendReferenceLetterCatalogKeys(array &$substArray, ?object $referenceletters): void
	{
		$this->getProvider('referenceletter')->appendCatalogKeys($substArray, array('referenceletters' => $referenceletters));
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
