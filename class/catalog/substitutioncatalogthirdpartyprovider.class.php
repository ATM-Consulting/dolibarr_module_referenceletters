<?php

require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides generic thirdparty extrafield keys.
 */
class SubstitutionCatalogThirdpartyProvider implements SubstitutionCatalogProviderInterface
{
	/** @var DoliDB */
	protected DoliDB $db;

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
		$thirdparty = isset($context['thirdparty']) && is_object($context['thirdparty']) ? $context['thirdparty'] : null;
		if (!is_object($thirdparty) || empty($thirdparty->id)) {
			return;
		}

		$extrafields = new ExtraFields($this->db);
		$labels = $extrafields->fetch_name_optionals_label('societe', true);
		if (empty($labels) || !is_array($labels)) {
			return;
		}

		foreach ($labels as $extrakey => $extralabel) {
			$substArray['cust_company_options_' . $extrakey] = 'Champ complémentaire tiers : ' . $extralabel;

			if (preg_match('/(^|_)(date|datetime)$/', $extrakey) || preg_match('/(_date|date_)|(_datetime|datetime_)/', $extrakey)) {
				$substArray['cust_company_options_' . $extrakey . '_locale'] = 'Champ complémentaire tiers : ' . $extralabel . ' formaté';
				$substArray['cust_company_options_' . $extrakey . '_rfc'] = 'Champ complémentaire tiers : ' . $extralabel . ' RFC';
			}
		}
	}
}
