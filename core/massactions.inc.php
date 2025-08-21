<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * Add file in email form
 */

if (GETPOST('addfile', 'alpha'))
{
	$trackid = GETPOST('trackid', 'aZ09');

	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	// Set tmp user directory
	$vardir = $conf->user->dir_output."/".$user->id;
	$upload_dir_tmp = $vardir.'/temp';			 // TODO Add $keytoavoidconflict in upload_dir path
	dol_add_file_process($upload_dir_tmp, 0, 0, 'addedfile', '', null, $trackid);

	$massaction = 'presend';
}

/*
 * Remove file in email form
 */
if (!empty($_POST['removedfile']) && empty($_POST['removAll']))
{
	$trackid = GETPOST('trackid', 'aZ09');

	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	// Set tmp user directory
	$vardir = $conf->user->dir_output."/".$user->id;
	$upload_dir_tmp = $vardir.'/temp';			 // TODO Add $keytoavoidconflict in upload_dir path
	// TODO Delete only files that was uploaded from email form. This can be addressed by adding the trackid into the temp path then changing donotdeletefile to 2 instead of 1 to say "delete only if into temp dir"
	// GETPOST('removedfile','alpha') is position of file into $_SESSION["listofpaths"...] array.
	dol_remove_file_process(GETPOST('removedfile', 'alpha'), 0, 0, $trackid);
	$massaction = 'presend';
}

/*
 * Remove all files in email form
 */
if (GETPOST('removAll', 'alpha'))
{
	$trackid = GETPOST('trackid', 'aZ09');

	$listofpaths = array();
	$listofnames = array();
	$listofmimes = array();
	$keytoavoidconflict = empty($trackid) ? '' : '-'.$trackid;
	if (!empty($_SESSION["listofpaths".$keytoavoidconflict]))
		$listofpaths = explode(';', $_SESSION["listofpaths".$keytoavoidconflict]);
	if (!empty($_SESSION["listofnames".$keytoavoidconflict]))
		$listofnames = explode(';', $_SESSION["listofnames".$keytoavoidconflict]);
	if (!empty($_SESSION["listofmimes".$keytoavoidconflict]))
		$listofmimes = explode(';', $_SESSION["listofmimes".$keytoavoidconflict]);

	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	$formmail = new FormMail($db);
	$formmail->trackid = $trackid;

	foreach ($listofpaths as $key => $value)
	{
		$pathtodelete = $value;
		$filetodelete = $listofnames[$key];
		$result = dol_delete_file($pathtodelete, 1); // Delete uploded Files
		$langs->load("other");
	//	setEventMessages($langs->trans("FileWasRemoved", $filetodelete), null, 'mesgs');

		$formmail->remove_attached_files($key); // Update Session
	}
}


/*
 *
 * PRESEND
 *
 */

if ($massaction == 'presend')
{
	$langs->load("mails");
	if (!GETPOST('cancel', 'alpha'))
	{
		$listofselectedid = array();
		$listofselectedthirdparties = array();
		$listofselectedref = array();
		foreach ($arrayofselected as $toselectid)
		{
			$result = $objecttmp->fetch($toselectid);
			if ($result > 0)
			{
				$listofselectedid[$toselectid] = $toselectid;
				$thirdpartyid = ($objecttmp->fk_soc ? $objecttmp->fk_soc : $objecttmp->socid);
				if ($objecttmp->element == 'societe'){
					$thirdpartyid = $objecttmp->id;
					$modelmail = 'thirdparty';

				} else {
					$modelmail = 'all';
				}

				if ($objecttmp->element == 'expensereport')
					$thirdpartyid = $objecttmp->fk_user_author;

				$listofselectedthirdparties[$thirdpartyid] = $thirdpartyid;
				$listofselectedref[$thirdpartyid][$toselectid] = $objecttmp->ref;
			}
		}
	}

	print '<input type="hidden" name="massaction" value="confirm_presend">';

	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	$formmail = new FormMail($db);

	dol_fiche_head(null, '', '');

	// Cree l'objet formulaire mail
	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	$formmail = new FormMail($db);
	$formmail->withform = -1;
	$formmail->fromtype = (GETPOST('fromtype', 'none') ? GETPOST('fromtype', 'none') : getDolGlobalString('MAIN_MAIL_DEFAULT_FROMTYPE', 'user'));

	if ($formmail->fromtype === 'user')
	{
		$formmail->fromid = $user->id;
	}
	$formmail->trackid = $trackid;
	if (getDolGlobalString('MAIN_EMAIL_ADD_TRACK_ID') && ( getDolGlobalInt('MAIN_EMAIL_ADD_TRACK_ID') & 2)) // If bit 2 is set
	{
		include DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$formmail->frommail = dolAddEmailTrackId($formmail->frommail, $trackid);
	}
	$formmail->withfrom = 1;
	//$formmail->withform = 1;
	$liste = $langs->trans("AllRecipientSelected", count($arrayofselected));
	$formmail->withtoreadonly = 1;
	//$formmail->withoptiononeemailperrecipient = empty($liste)?0:((GETPOST('oneemailperrecipient', 'none')=='on')?1:-1);
	$formmail->withto = empty($liste) ? (GETPOST('sendto', 'alpha') ? GETPOST('sendto', 'alpha') : array()) : $liste;
	$formmail->withtofree = empty($liste) ? 1 : 0;
	$formmail->withtocc = 1;
	$formmail->withtoccc = getDolGlobalInt('MAIN_EMAIL_USECCC');
	$formmail->withtopic = 'DocEdit';
	$formmail->withfile = 2;
	// $formmail->withfile = 2; Not yet supported in mass action
	//$formmail->withmaindocfile = 1; // Add a checkbox "Attach also main document"

	$formmail->withbody = 1;
	$formmail->withdeliveryreceipt = 1;
	$formmail->withcancel = 1;

	// Make substitution in email content
	$substitutionarray = getCommonSubstitutionArray($langs, 0, null, $object);
	$substitutionarray['__EMAIL__'] = $sendto;
	$substitutionarray['__CHECK_READ__'] = (is_object($object) && is_object($object->thirdparty)) ? '<img src="'.DOL_MAIN_URL_ROOT.'/public/emailing/mailing-read.php?tag='.$object->thirdparty->tag.'&securitykey='.urlencode(getDolGlobalString('MAILING_EMAIL_UNSUBSCRIBE_KEY')).'" width="1" height="1" style="width:1px;height:1px" border="0"/>' : '';
	$substitutionarray['__PERSONALIZED__'] = ''; // deprecated
	$substitutionarray['__CONTACTCIVNAME__'] = '';

	$parameters = array(
		'mode' => 'formemail'
	);
	complete_substitutions_array($substitutionarray, $langs, $object, $parameters);

	// Tableau des substitutions
	$formmail->substit = $substitutionarray;

	// Tableau des parametres complementaires du post
	$formmail->param['action'] = $action;
	$formmail->param['models'] = $modelmail;
	$formmail->param['models_id'] = GETPOST('modelmailselected', 'int');
	$formmail->param['id'] = join(',', $arrayofselected);
	// $formmail->param['returnurl']=$_SERVER["PHP_SELF"].'?id='.$object->id;
	if (getDolGlobalString('MAILING_LIMIT_SENDBYWEB') && count($listofselectedthirdparties) > getDolGlobalInt('MAILING_LIMIT_SENDBYWEB'))
	{
		$langs->load("errors");
		print img_warning().' '.$langs->trans('WarningNumberOfRecipientIsRestrictedInMassAction', getdolglobalInt('MAILING_LIMIT_SENDBYWEB'));
		print ' - <a href="javascript: window.history.go(-1)">'.$langs->trans("GoBack").'</a>';
		$arrayofmassactions = array();
	}
	else
	{
		print $formmail->get_form();
	}

	dol_fiche_end();
}

/*
 *
 *
 * FIN PRESEND
 *
 */





