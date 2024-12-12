<?php
	print '<table class="border" width="100%">';

	$linkback = '<a href="' . DOL_URL_ROOT . '/comm/propal/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$soc=$object->thirdparty;

	// Ref
	print '<tr><td width="20%">' . $langs->trans('Ref') . '</td>';
	print '<td colspan="5">';

	 $fieldfac = $fieldfac ?? "";
	 $fieldfac .= "ref";

	print $form->showrefnav($object, 'ref', $linkback, 1, $fieldfac, 'ref', $morehtmlref ?? '');
	print '</td></tr>';

	// Ref customer
	print '<tr><td width="20%">';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('RefCustomer');
	print '</td>';
	print '</tr></table>';
	print '</td>';
	print '<td colspan="5">';
	print $object->ref_client;
	print '</td></tr>';

	// Third party
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%">';
	print '<tr><td>' . $langs->trans('Company') . '</td>';
	print '</td><td colspan="5">';
	print '</tr></table>';
	print '</td><td colspan="5">';
	print ' &nbsp;' . $soc->getNomUrl(1, 'compta');
	print ' &nbsp; ';
	print '(<a href="' . DOL_URL_ROOT . '/compta/facture/list.php?socid=' . $object->socid . '">' . $langs->trans('OtherBills') . '</a>';
	// Outstanding Bill
	if(method_exists($soc, 'get_OutstandingBill')) $outstandigBills = $soc->get_OutstandingBill();
	else {
		$TOutstandigBills = $soc->getOutstandingBills();
		$outstandigBills = $TOutstandigBills['opened'];
	}
	print ' - ' . $langs->trans('CurrentOutstandingBill') . ': ';
	print price($outstandigBills, '', $langs, 0, 0, - 1, $conf->currency);
	if ($soc->outstanding_limit != '') {
		if ($outstandigBills > $soc->outstanding_limit)
			print img_warning($langs->trans("OutstandingBillReached"));
		print ' / ' . price($soc->outstanding_limit);
	}
	print ')';
	print '</tr>';

	// Type
	print '<tr><td>' . $langs->trans('Type') . '</td><td colspan="5">';
	print $object->getLibType();
	if ($object->type == Facture::TYPE_REPLACEMENT) {
		$facreplaced = new Facture($db);
		$facreplaced->fetch($object->fk_facture_source);
		print ' (' . $langs->transnoentities("ReplaceInvoice", $facreplaced->getNomUrl(1)) . ')';
	}
	if ($object->type == Facture::TYPE_CREDIT_NOTE) {
		$facusing = new Facture($db);
		$facusing->fetch($object->fk_facture_source);
		print ' (' . $langs->transnoentities("CorrectInvoice", $facusing->getNomUrl(1)) . ')';
	}
	$totalpaye = $totalpaye ?? 0 ;
	// Statut
	print '<tr><td>' . $langs->trans('Status') . '</td>';
	print '<td align="left" colspan="3">' . ($object->getLibStatut(4, $totalpaye)) . '</td></tr>';

	print '</table><br>';
