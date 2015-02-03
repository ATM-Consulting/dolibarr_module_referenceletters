<?php
	print '<table class="border" width="100%">';

	$linkback = '<a href="' . DOL_URL_ROOT . '/comm/propal/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

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

	// Ligne info remises tiers
	print '<tr><td>' . $langs->trans('Discounts') . '</td><td colspan="5">';
	if ($soc->remise_percent)
		print $langs->trans("CompanyHasRelativeDiscount", $soc->remise_percent);
	else
		print $langs->trans("CompanyHasNoRelativeDiscount");
	print '. ';
	$absolute_discount = $soc->getAvailableDiscounts('', 'fk_facture_source IS NULL');
	$absolute_creditnote = $soc->getAvailableDiscounts('', 'fk_facture_source IS NOT NULL');
	$absolute_discount = price2num($absolute_discount, 'MT');
	$absolute_creditnote = price2num($absolute_creditnote, 'MT');
	if ($absolute_discount) {
		if ($object->statut > 0) {
			print $langs->trans("CompanyHasAbsoluteDiscount", price($absolute_discount, 0, $langs, 0, 0, -1, $conf->currency));
		} else {
			// Remise dispo de type non avoir
			$filter = 'fk_facture_source IS NULL';
			print '<br>';
			$form->form_remise_dispo($_SERVER["PHP_SELF"] . '?id=' . $object->id, 0, 'remise_id', $soc->id, $absolute_discount, $filter);
		}
	}
	if ($absolute_creditnote) {
		print $langs->trans("CompanyHasCreditNote", price($absolute_creditnote, 0, $langs, 0, 0, -1, $conf->currency)) . '. ';
	}
	if (! $absolute_discount && ! $absolute_creditnote)
		print $langs->trans("CompanyHasNoAbsoluteDiscount") . '.';
	print '</td></tr>';

	// Date of proposal
	print '<tr>';
	print '<td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('Date');
	print '</td>';
	if ($action != 'editdate' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editdate&amp;id=' . $object->id . '">' . img_edit($langs->trans('SetDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if (! empty($object->brouillon) && $action == 'editdate') {
		print '<form name="editdate" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="setdate">';
		$form->select_date($object->date, 're', '', '', 0, "editdate");
		print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
		print '</form>';
	} else {
		if ($object->date) {
			print dol_print_date($object->date, 'daytext');
		} else {
			print '&nbsp;';
		}
	}
	print '</td>';

	// Date end proposal
	print '<tr>';
	print '<td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('DateEndPropal');
	print '</td>';
	if ($action != 'editecheance' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editecheance&amp;id=' . $object->id . '">' . img_edit($langs->trans('SetConditions'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if (! empty($object->brouillon) && $action == 'editecheance') {
		print '<form name="editecheance" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="setecheance">';
		$form->select_date($object->fin_validite, 'ech', '', '', '', "editecheance");
		print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
		print '</form>';
	} else {
		if (! empty($object->fin_validite)) {
			print dol_print_date($object->fin_validite, 'daytext');
			if ($object->statut == 1 && $object->fin_validite < ($now - $conf->propal->cloture->warning_delay))
				print img_warning($langs->trans("Late"));
		} else {
			print '&nbsp;';
		}
	}
	print '</td>';
	print '</tr>';

	// Payment term
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('PaymentConditionsShort');
	print '</td>';
	if ($action != 'editconditions' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editconditions&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetConditions'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editconditions') {
		$form->form_conditions_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->cond_reglement_id, 'cond_reglement_id');
	} else {
		$form->form_conditions_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->cond_reglement_id, 'none');
	}
	print '</td>';
	print '</tr>';

	// Delivery date
	$langs->load('deliveries');
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('DeliveryDate');
	print '</td>';
	if ($action != 'editdate_livraison' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editdate_livraison&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetDeliveryDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editdate_livraison') {
		print '<form name="editdate_livraison" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="setdate_livraison">';
		$form->select_date($object->date_livraison, 'liv_', '', '', '', "editdate_livraison");
		print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
		print '</form>';
	} else {
		print dol_print_date($object->date_livraison, 'daytext');
	}
	print '</td>';
	print '</tr>';

	// Delivery delay
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('AvailabilityPeriod');
	if (! empty($conf->commande->enabled))
		print ' (' . $langs->trans('AfterOrder') . ')';
	print '</td>';
	if ($action != 'editavailability' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editavailability&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetAvailability'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editavailability') {
		$form->form_availability($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->availability_id, 'availability_id', 1);
	} else {
		$form->form_availability($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->availability_id, 'none', 1);
	}

	print '</td>';
	print '</tr>';

    // Shipping Method
    if (! empty($conf->expedition->enabled)) {
        print '<tr><td>';
        print '<table width="100%" class="nobordernopadding"><tr><td>';
        print $langs->trans('SendingMethod');
        print '</td>';
        if ($action != 'editshippingmethod' && $user->rights->propal->creer)
            print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editshippingmethod&amp;id='.$object->id.'">'.img_edit($langs->trans('SetShippingMode'),1).'</a></td>';
        print '</tr></table>';
        print '</td><td colspan="3">';
        if ($action == 'editshippingmethod') {
            $form->formSelectShippingMethod($_SERVER['PHP_SELF'].'?id='.$object->id, $object->shipping_method_id, 'shipping_method_id', 1);
        } else {
            $form->formSelectShippingMethod($_SERVER['PHP_SELF'].'?id='.$object->id, $object->shipping_method_id, 'none');
        }
        print '</td>';
        print '</tr>';
    }

	// Origin of demand
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('Source');
	print '</td>';
	if ($action != 'editdemandreason' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editdemandreason&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetDemandReason'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editdemandreason') {
		$form->formInputReason($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->demand_reason_id, 'demand_reason_id', 1);
	} else {
		$form->formInputReason($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->demand_reason_id, 'none');
	}
	print '</td>';
	print '</tr>';

	// Payment mode
	print '<tr>';
	print '<td width="25%">';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('PaymentMode');
	print '</td>';
	if ($action != 'editmode' && ! empty($object->brouillon))
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editmode&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetMode'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($action == 'editmode') {
		$form->form_modes_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->mode_reglement_id, 'mode_reglement_id');
	} else {
		$form->form_modes_reglement($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->mode_reglement_id, 'none');
	}
	print '</td></tr>';

	// Project
	if (! empty($conf->projet->enabled)) {
		$langs->load("projects");
		print '<tr><td>';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans('Project') . '</td>';
		if ($user->rights->propal->creer) {
			if ($action != 'classify')
				print '<td align="right"><a href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a></td>';
			print '</tr></table>';
			print '</td><td colspan="3">';
			if ($action == 'classify') {
				$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid');
			} else {
				$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none');
			}
			print '</td></tr>';
		} else {
			print '</td></tr></table>';
			if (! empty($object->fk_project)) {
				print '<td colspan="3">';
				$proj = new Project($db);
				$proj->fetch($object->fk_project);
				print '<a href="../projet/card.php?id=' . $object->fk_project . '" title="' . $langs->trans('ShowProject') . '">';
				print $proj->ref;
				print '</a>';
				print '</td>';
			} else {
				print '<td colspan="3">&nbsp;</td>';
			}
		}
		print '</tr>';
	}

	if ($soc->outstanding_limit)
	{
		// Outstanding Bill
		print '<tr><td>';
		print $langs->trans('OutstandingBill');
		print '</td><td align=right colspan=3>';
		print price($soc->get_OutstandingBill()) . ' / ';
		print price($soc->outstanding_limit, 0, '', 1, - 1, - 1, $conf->currency);
		print '</td>';
		print '</tr>';
	}

	if (! empty($conf->global->BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL) && $conf->banque->enabled)
	{
	    // Bank Account
	    print '<tr><td>';
	    print '<table width="100%" class="nobordernopadding"><tr><td>';
	    print $langs->trans('BankAccount');
	    print '</td>';
	    if ($action != 'editbankaccount' && $user->rights->propal->creer)
	        print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editbankaccount&amp;id='.$object->id.'">'.img_edit($langs->trans('SetBankAccount'),1).'</a></td>';
	    print '</tr></table>';
	    print '</td><td colspan="3">';
	    if ($action == 'editbankaccount') {
	        $form->formSelectAccount($_SERVER['PHP_SELF'].'?id='.$object->id, $object->fk_account, 'fk_account', 1);
	    } else {
	        $form->formSelectAccount($_SERVER['PHP_SELF'].'?id='.$object->id, $object->fk_account, 'none');
	    }
	    print '</td>';
	    print '</tr>';
	}

	// Other attributes
	$cols = 3;
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	// Amount HT
	print '<tr><td height="10" width="25%">' . $langs->trans('AmountHT') . '</td>';
	print '<td align="right" class="nowrap"><b>' . price($object->total_ht, '', $langs, 0, - 1, - 1, $conf->currency) . '</b></td>';
	print '<td></td>';

	// Margin Infos
	if (! empty($conf->margin->enabled)) {
		print '<td valign="top" width="50%" rowspan="4">';
		$object->displayMarginInfos();
		print '</td>';
	}
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