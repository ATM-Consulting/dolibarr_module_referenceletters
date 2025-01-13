<?php
print '<table class="border" width="100%">';

print '<tr><td width="25%">' . $langs->trans("ThirdPartyName") . '</td><td colspan="3">';
print $form->showrefnav($object, 'socid', '', intval(empty($user->societe_id)), 'rowid', 'nom');
print '</td></tr>';

if (getDolGlobalString('SOCIETE_USEPREFIX')) // Old not used prefix field
{
	print '<tr><td>' . $langs->trans('Prefix') . '</td><td colspan="3">' . $object->prefix_comm . '</td></tr>';
}

if (!empty($object->client)) {
	print '<tr><td>';
	print $langs->trans('CustomerCode') . '</td><td colspan="3">';
	print $object->code_client;
	if ($object->check_codeclient() != 0)
		print ' <font class="error">(' . $langs->trans("WrongCustomerCode") . ')</font>';
	print '</td></tr>';
}

if ($object->fournisseur) {
	print '<tr><td>';
	print $langs->trans('SupplierCode') . '</td><td colspan="3">';
	print $object->code_fournisseur;
	if ($object->check_codefournisseur() != 0)
		print ' <font class="error">(' . $langs->trans("WrongSupplierCode") . ')</font>';
	print '</td></tr>';
}

if (isModEnabled('barcode')) {
	print '<tr><td>' . $langs->trans('Gencod') . '</td><td colspan="3">' . $object->barcode . '</td></tr>';
}

print "<tr><td valign=\"top\">" . $langs->trans('Address') . "</td><td colspan=\"3\">";
dol_print_address($object->address, 'gmap', 'thirdparty', $object->id);
print "</td></tr>";

// Zip / Town
print '<tr><td width="25%">' . $langs->trans('Zip') . '</td><td width="25%">' . $object->zip . "</td>";
print '<td width="25%">' . $langs->trans('Town') . '</td><td width="25%">' . $object->town . "</td></tr>";

// Country
if ($object->country) {
	print '<tr><td>' . $langs->trans('Country') . '</td><td colspan="3">';
	$img = picto_from_langcode($object->country_code);
	print($img ? $img . ' ' : '');
	print $object->country;
	print '</td></tr>';
}

// EMail
print '<tr><td>' . $langs->trans('EMail') . '</td><td colspan="3">';
print dol_print_email($object->email, 0, $object->id, 'AC_EMAIL');
print '</td></tr>';

// Web
print '<tr><td>' . $langs->trans('Web') . '</td><td colspan="3">';
print dol_print_url($object->url);
print '</td></tr>';

// Phone / Fax
print '<tr><td>' . $langs->trans('Phone') . '</td><td>' . dol_print_phone(!empty($object->tel) ? $object->tel :  '', $object->country_code, 0, $object->id, 'AC_TEL') . '</td>';
print '<td>' . $langs->trans('Fax') . '</td><td>' . dol_print_phone($object->fax, $object->country_code, 0, $object->id, 'AC_FAX') . '</td></tr>';

print '</table>';
