<?php

print '<table class="border" width="100%">';

$linkback = '<a href="'.DOL_URL_ROOT.'/contrat/list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

// Ref du contrat
print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
print "</td></tr>";

// Customer
print "<tr><td>".$langs->trans("Customer")."</td>";
print '<td colspan="3">'.$object->thirdparty->getNomUrl(1).'</td></tr>';

// Ligne info remises tiers
print '<tr><td>'.$langs->trans('Discount').'</td><td colspan="3">';
if ($object->thirdparty->remise_percent) print $langs->trans("CompanyHasRelativeDiscount",$object->thirdparty->remise_percent);
else print $langs->trans("CompanyHasNoRelativeDiscount");
$absolute_discount=$object->thirdparty->getAvailableDiscounts();
print '. ';
if ($absolute_discount) print $langs->trans("CompanyHasAbsoluteDiscount",price($absolute_discount),$langs->trans("Currency".$conf->currency));
else print $langs->trans("CompanyHasNoAbsoluteDiscount");
print '.';
print '</td></tr>';

// Statut contrat
print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">';
if ($object->statut==0) print $object->getLibStatut(2);
else print $object->getLibStatut(4);
print "</td></tr>";

// Date
print '<tr><td>'.$langs->trans("Date").'</td>';
print '<td colspan="3">'.dol_print_date($object->date_contrat,"dayhour")."</td></tr>\n";

// Projet
if (! empty($conf->projet->enabled))
{
	$langs->load("projects");
	print '<tr><td>';
	print '<table width="100%" class="nobordernopadding"><tr><td>';
	print $langs->trans("Project");
	print '</td>';
	require_once __DIR__ . '/../lib/referenceletters.lib.php';
	if ($action != "classify" && rl_userHasRight($user,'projet', 'creer' ) ) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=classify&amp;id='.$object->id.'">'.img_edit($langs->trans("SetProject")).'</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == "classify")
	{
		$form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id,$object->socid,$object->fk_project,"projectid");
	}
	else
	{
		$form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id,$object->socid,$object->fk_project,"none");
	}
	print "</td></tr>";
}

// Other attributes
$parameters=array('colspan' => ' colspan="3"');
$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook

print "</table>";
