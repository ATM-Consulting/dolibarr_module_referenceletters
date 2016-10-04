<?php
print '<table class="border" width="100%">';

$linkback = '<a href="' . DOL_URL_ROOT . '/comm/propal/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

$soc = $object->thirdparty;

// Ref
print '<tr><td width="20%">' . $langs->trans('Ref') . '</td>';
print '<td colspan="5">';
print $form->showrefnav($object, 'ref', $linkback, 1, 'facnumber', 'ref', $morehtmlref);
print '</td></tr>';

// Ref customer
print '<tr><td width="20%">';
print '<table class="nobordernopadding" width="100%"><tr><td>';
print $langs->trans('RefCustomer');
print '</td>';
print '</tr></table>';
print '</td>';
print '<td>';
print $object->ref_client;
print '</td></tr>';

// Third party
print '<tr><td>';
print '<table class="nobordernopadding" width="100%">';
print '<tr><td>' . $langs->trans('Company') . '</td>';
print '</td><td>';
print '</tr></table>';
print '</td><td>';
print ' &nbsp;' . $soc->getNomUrl(1, 'compta');
print ' &nbsp; ';
print '(<a href="' . DOL_URL_ROOT . '/compta/facture/list.php?socid=' . $object->socid . '">' . $langs->trans('OtherBills') . '</a>';
// Outstanding Bill
$outstandigBills = $soc->get_OutstandingBill();
print ' - ' . $langs->trans('CurrentOutstandingBill') . ': ';
print price($outstandigBills, '', $langs, 0, 0, - 1, $conf->currency);
if ($soc->outstanding_limit != '') {
	if ($outstandigBills > $soc->outstanding_limit)
		print img_warning($langs->trans("OutstandingBillReached"));
	print ' / ' . price($soc->outstanding_limit);
}
print ')';
print '</tr>';

// Date
print '<tr><td>';
print $langs->trans('Date');
print '</td></tr>';


// Statut
print '<tr><td>' . $langs->trans('Status') . '</td><td>' . $object->getLibStatut(4) . '</td></tr>';


print '</table><br>';