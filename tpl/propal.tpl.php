<?php
	print '<table class="border" width="100%">';

	$linkback = '<a href="' . DOL_URL_ROOT . '/comm/propal/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$soc=$object->thirdparty;
	
	// Ref
	print '<tr><td>' . $langs->trans('Ref') . '</td><td colspan="5">';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
	print '</td></tr>';

	// Ref customer
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td class="nowrap">';
	print $langs->trans('RefCustomer') . '</td>';
	print '</td>';
	print '</tr></table>';
	print '</td><td colspan="5">';
	print $object->ref_client;
	print '</td>';
	print '</tr>';

	// Company
	print '<tr><td>' . $langs->trans('Company') . '</td><td colspan="5">' . $soc->getNomUrl(1) . '</td>';
	print '</tr>';

	// Amount VAT
	print '<tr><td height="10">' . $langs->trans('AmountVAT') . '</td>';
	print '<td align="right" class="nowrap">' . price($object->total_tva, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
	print '<td></td></tr>';

	// Amount Local Taxes
	if ($mysoc->localtax1_assuj == "1" || $object->total_localtax1 != 0) 	// Localtax1
	{
		print '<tr><td height="10">' . $langs->transcountry("AmountLT1", $mysoc->country_code) . '</td>';
		print '<td align="right" class="nowrap">' . price($object->total_localtax1, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
		print '<td></td></tr>';
	}
	if ($mysoc->localtax2_assuj == "1" || $object->total_localtax2 != 0) 	// Localtax2
	{
		print '<tr><td height="10">' . $langs->transcountry("AmountLT2", $mysoc->country_code) . '</td>';
		print '<td align="right" class="nowrap">' . price($object->total_localtax2, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
		print '<td></td></tr>';
	}

	// Amount TTC
	print '<tr><td height="10">' . $langs->trans('AmountTTC') . '</td>';
	print '<td align="right" class="nowrap">' . price($object->total_ttc, '', $langs, 0, - 1, - 1, $conf->currency) . '</td>';
	print '<td></td></tr>';

	// Statut
	print '<tr><td height="10">' . $langs->trans('Status') . '</td><td align="left" colspan="2">' . $object->getLibStatut(4) . '</td></tr>';

	print '</table><br>';