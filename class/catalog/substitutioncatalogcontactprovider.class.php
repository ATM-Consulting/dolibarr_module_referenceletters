<?php

require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides external contact keys for the current business object.
 */
class SubstitutionCatalogContactProvider implements SubstitutionCatalogProviderInterface
{
	/** @var DoliDB */
	protected DoliDB $db;
	/** @var CommonDocGeneratorReferenceLetters */
	protected CommonDocGeneratorReferenceLetters $docgen;
	/** @var Translate */
	protected Translate $langs;

	/**
	 * @param DoliDB $db
	 * @param CommonDocGeneratorReferenceLetters $docgen
	 * @param Translate $langs
	 */
	public function __construct(DoliDB $db, CommonDocGeneratorReferenceLetters $docgen, Translate $langs)
	{
		$this->db = $db;
		$this->docgen = $docgen;
		$this->langs = $langs;
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		$elementType = isset($context['element_type']) ? (string) $context['element_type'] : '';
		if (in_array($elementType, array('contact', 'thirdparty'), true)) {
			return;
		}

		$object = isset($context['object']) ? $context['object'] : null;
		if (!is_object($object) || !method_exists($object, 'liste_type_contact')) {
			return;
		}

		$contactTypes = $object->liste_type_contact('external', 'position', 1);
		if (empty($contactTypes) || !is_array($contactTypes)) {
			return;
		}

		$contactStatic = new Contact($this->db);
		$contactStatic->id = 0;
		$contactStatic->statut = '';

		foreach ($contactTypes as $code => $label) {
			$contactPrefix = 'cust_contactclient_' . $code . '_1';
			$contactArray = $this->docgen->get_substitutionarray_contact($contactStatic, $this->langs, $contactPrefix);
			if (!empty($contactArray) && is_array($contactArray)) {
				$substArray = array_merge($substArray, $contactArray);
			}
		}
	}
}
