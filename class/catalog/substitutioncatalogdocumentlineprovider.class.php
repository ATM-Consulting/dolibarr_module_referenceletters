<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides the generic document line catalog.
 */
class SubstitutionCatalogDocumentLineProvider implements SubstitutionCatalogProviderInterface
{
	/** @var Translate */
	protected Translate $langs;

	/**
	 * @param Translate $langs Translator used to resolve dynamic group labels.
	 */
	public function __construct(Translate $langs)
	{
		$this->langs = $langs;
	}

	/**
	 * @param array<string,mixed> $substArray
	 * @param array<string,mixed> $context
	 * @return void
	 */
	public function appendCatalogKeys(array &$substArray, array $context = array()): void
	{
		if (empty($context['has_document_lines'])) {
			return;
		}

		$this->langs->load('admin');
		$keys = array(
			'line_fulldesc',
			'line_product_ref',
			'line_product_ref_fourn',
			'line_product_label',
			'line_libelle',
			'line_product_type',
			'line_product_desc',
			'line_product_barcode',
			'line_desc',
			'line_vatrate',
			'line_localtax1_rate',
			'line_localtax2_rate',
			'line_up',
			'line_multicurrency_code',
			'line_multicurrency_subprice',
			'line_up_locale',
			'line_multicurrency_subprice_locale',
			'line_qty',
			'line_qty_asked',
			'line_qty_shipped',
			'line_discount_percent',
			'line_price_ht',
			'line_multicurrency_total_ht',
			'line_price_ttc',
			'line_multicurrency_total_ttc',
			'line_price_ht_locale',
			'line_multicurrency_total_ht_locale',
			'line_price_ttc_locale',
			'line_multicurrency_total_ttc_locale',
			'line_price_vat',
			'line_multicurrency_total_tva',
			'line_price_vat_locale',
			'line_multicurrency_total_tva_locale',
			'line_total_up',
			'line_total_up_locale',
			'line_rang',
			'line_pos',
			'line_weight',
			'line_vol',
			'line_date_start',
			'line_date_start_locale',
			'line_date_start_rfc',
			'line_date_end',
			'line_date_end_locale',
			'line_date_end_rfc',
			'line_options_show_total_ht',
			'line_options_show_reduc',
			'line_options_subtotal_show_qty',
			'date_ouverture',
			'date_ouverture_prevue',
			'date_fin_validite',
		);
		$substArray[$this->langs->trans('RefLtrLines')] = $this->translateTags($keys);
	}

	/**
	 * @param array<int,string> $tags
	 * @return array<string,string>
	 */
	protected function translateTags(array $tags): array
	{
		$translated = array();
		foreach ($tags as $tag) {
			$key = 'reflettershortcode_' . $tag;
			$label = $this->langs->trans($key);
			$translated[$tag] = ($label !== $key) ? $label : $tag;
		}

		return $translated;
	}
}
