<?php

require_once __DIR__ . '/substitutioncatalogproviderinterface.class.php';

/**
 * Provides the generic document line catalog.
 */
class SubstitutionCatalogDocumentLineProvider implements SubstitutionCatalogProviderInterface
{
	/** @var Translate */
	protected $langs;

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
		$this->langs->load('admin');
		$substArray[$this->langs->trans('RefLtrLines')] = array(
			'line_fulldesc' => 'Description complète',
			'line_product_ref' => 'Référence produit',
			'line_product_ref_fourn' => 'Référence produit fournisseur (pour les documents fournisseurs)',
			'line_product_label' => 'Libellé produit',
			'line_libelle' => 'Libellé du produit/service',
			'line_product_type' => 'Type produit',
			'line_product_desc' => 'Description produit',
			'line_product_barcode' => 'Code barre produit',
			'line_desc' => 'Description',
			'line_vatrate' => 'Taux de TVA',
			'line_localtax1_rate' => 'Taux taxe locale 1',
			'line_localtax2_rate' => 'Taux taxe locale 2',
			'line_up' => 'Prix unitaire (format numérique)',
			'line_multicurrency_code' => 'Code devise de la ligne',
			'line_multicurrency_subprice' => 'Prix unitaire devisé (format numérique)',
			'line_up_locale' => 'Prix unitaire (format prix)',
			'line_multicurrency_subprice_locale' => 'Prix unitaire devisé (format prix)',
			'line_qty' => 'Qté ligne',
			'line_qty_asked' => 'Qté demandée',
			'line_qty_shipped' => 'Qté expédiée',
			'line_discount_percent' => 'Remise ligne',
			'line_price_ht' => 'Total HT ligne (format numérique)',
			'line_multicurrency_total_ht' => 'Total HT ligne devisé (format numérique)',
			'line_price_ttc' => 'Total TTC ligne (format numérique)',
			'line_multicurrency_total_ttc' => 'Total TTC ligne devisé (format numérique)',
			'line_price_ht_locale' => 'Total HT ligne (format prix)',
			'line_multicurrency_total_ht_locale' => 'Total HT ligne devisé (format prix)',
			'line_price_ttc_locale' => 'Total TTC ligne (format prix)',
			'line_multicurrency_total_ttc_locale' => 'Total TTC ligne devisé (format prix)',
			'line_price_vat' => 'Montant TVA (format numérique)',
			'line_multicurrency_total_tva' => 'Montant TVA devisé (format numérique)',
			'line_price_vat_locale' => 'Montant TVA (format prix)',
			'line_multicurrency_total_tva_locale' => 'Montant TVA devisé (format prix)',
			'line_total_up' => 'Prix unitaire total (format numérique)',
			'line_total_up_locale' => 'Prix unitaire total (format prix)',
			'line_rang' => 'Rang de la ligne',
			'line_pos' => 'Position de la ligne',
			'line_weight' => 'Poids ligne',
			'line_vol' => 'Volume ligne',
			'line_date_start' => 'Date début service',
			'line_date_start_locale' => 'Date début service format 1',
			'line_date_start_rfc' => 'Date début service format 2',
			'line_date_end' => 'Date fin service',
			'line_date_end_locale' => 'Date fin service format 1',
			'line_date_end_rfc' => 'Date fin service format 2',
			'line_options_show_total_ht' => 'Afficher le total HT sur une ligne de sous-total',
			'line_options_show_reduc' => 'Afficher la réduction sur une ligne de sous-total',
			'line_options_subtotal_show_qty' => 'Afficher la quantité sur une ligne de sous-total',
			'date_ouverture' => 'Date démarrage réelle (réservé aux contrats)',
			'date_ouverture_prevue' => 'Date prévue de démarrage (réservé aux contrats)',
			'date_fin_validite' => 'Date fin réelle (réservé aux contrats)',
		);
	}
}
