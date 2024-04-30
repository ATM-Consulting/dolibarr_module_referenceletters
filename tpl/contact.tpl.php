<?php
print '<table class="border" width="100%">';

$linkback = '<a href="' . DOL_URL_ROOT . '/contact/list.php?restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>';

// Ref
print '<tr><td width="20%">' . $langs->trans("Ref") . '</td><td colspan="3">';
print $form->showrefnav($object, 'id', $linkback);
print '</td></tr>';

// Name
print '<tr><td width="20%">' . $langs->trans("Lastname") . ' / ' . $langs->trans("Label") . '</td><td width="30%">' . $object->lastname . '</td>';
print '<td width="20%">' . $langs->trans("Firstname") . '</td><td width="30%">' . $object->firstname . '</td></tr>';

// Company
if (!getDolGlobalString('SOCIETE_DISABLE_CONTACTS')) {
	if ($object->socid > 0) {
		$objsoc = new Societe($db);
		$objsoc->fetch($object->socid);
		
		print '<tr><td>' . $langs->trans("Company") . '</td><td colspan="3">' . $objsoc->getNomUrl(1) . '</td></tr>';
	} 

	else {
		print '<tr><td>' . $langs->trans("Company") . '</td><td colspan="3">';
		print $langs->trans("ContactNotLinkedToCompany");
		print '</td></tr>';
	}
}

// Civility
print '<tr><td>' . $langs->trans("UserTitle") . '</td><td colspan="3">';
print $object->getCivilityLabel();
print '</td></tr>';
print '</table>';