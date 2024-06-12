<?php

print '<table class="border" width="100%">';

$soc=$object->thirdparty;

$linkback = '<a href="' . DOL_URL_ROOT . '/fourn/commande/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

$soc=$object->thirdparty;

// Ref
print '<tr><td>' . $langs->trans('Ref') . '</td><td colspan="5">';
print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
print '</td></tr>';

print '<tr><td>' . $langs->trans('Company') . '</td><td colspan="5">' . $soc->getNomUrl(1) . '</td>';
print '</tr>';

// Statut
print '<tr><td height="10">' . $langs->trans('Status') . '</td><td align="left" colspan="2">' . $object->getLibStatut(4) . '</td></tr>';

print '</table><br>';
