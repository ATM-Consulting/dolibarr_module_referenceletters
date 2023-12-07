<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (($action == 'send' || ! empty($_REQUEST['sendmail'])) && ! $_POST['addfile'] && ! $_POST['removAll'] && ! $_POST['removedfile'] && ! $_POST['cancel'] && ! $_POST['modelselected']) {
	if (empty($trackid))
		$trackid = GETPOST('trackid', 'aZ09');

	$subject = '';
	$actionmsg = '';
	$actionmsg2 = '';
	if (! empty($conf->dolimail->enabled))
		$langs->load("dolimail@dolimail");
	$langs->load('mails');

	$sendtoid = 0;

	if (! empty($arrayofselected)) {

		foreach ( $arrayofselected as $id ) {
			if (is_object($object)) {
				$result = $object->fetch($id);
				if ($object->element_type == 'contact') {
					$fk_element = $object->fk_element;
					$sendobj = new Contact($db);
					$result = $sendobj->fetch($fk_element);
					$sendtoid = $fk_element;
					if(!empty($sendobj->socid)) {
						$thirdparty = new Societe($db);
						$thirdparty->fetch($sendobj->socid);
						$sendtosocid = $sendobj->socid;
					}

					if (empty($sendobj->mail)) {
						$sendobj = $thirdparty;
						if (! empty($sendobj->email))
							$sendto = $sendobj->email;
						else
							$result = 0;
					} else {

						$sendto = $sendobj->mail;
					}
				} else if ($object->element_type == 'thirdparty') {
					$sendobj = new Societe($db);
					$result = $sendobj->fetch($object->fk_element);
					$sendtosocid = $object->fk_element;
					if (! empty($sendobj->email))
						$sendto = $sendobj->email;
					else
						$result = 0;
				}
			} else
				$thirdparty = $mysoc;

			if ($result > 0) {

				$sendtocc = '';
				$sendtobcc = '';

				// Define $sendtocc
				$receivercc = $_POST['receivercc'];
				if (! is_array($receivercc)) {
					if ($receivercc == '-1')
						$receivercc = array();
					else
						$receivercc = array(
								$receivercc
						);
				}
				$tmparray = array();
				if (trim($_POST['sendtocc'])) {
					$tmparray[] = trim($_POST['sendtocc']);
				}
				if (count($receivercc) > 0) {
					foreach ( $receivercc as $key => $val ) {
						// Recipient was provided from combo list
						if ($val == 'thirdparty') // Id of third party
						{
							$tmparray[] = $thirdparty->name . ' <' . $thirdparty->email . '>';
						} elseif ($val) // Id du contact
						{
							$tmparray[] = $thirdparty->contact_get_property(( int ) $val, 'email');
							// $sendtoid[] = $val; TODO Add also id of contact in CC ?
						}
					}
				}
				$sendtocc = implode(',', $tmparray);

				if (dol_strlen($sendto)) {
					// Define $urlwithroot
					$urlwithouturlroot = preg_replace('/' . preg_quote(DOL_URL_ROOT, '/') . '$/i', '', trim($dolibarr_main_url_root));
					$urlwithroot = $urlwithouturlroot . DOL_URL_ROOT; // This is to use external domain name found into config file
					                                                  // $urlwithroot=DOL_MAIN_URL_ROOT; // This is to use same domain name than current

					require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';

					$langs->load("societe");
					$langs->load("contact");
					$langs->load("commercial");

					$fromtype = GETPOST('fromtype', 'alpha');

					if ($fromtype === 'robot') {
						$from = getDolGlobalString('MAIN_MAIL_EMAIL_FROM') . ' <' . getDolGlobalString('MAIN_MAIL_EMAIL_FROM') . '>';
					} elseif ($fromtype === 'user') {
						$from = $user->getFullName($langs) . ' <' . $user->email . '>';
					} elseif ($fromtype === 'company') {
						$from = getDolGlobalString('MAIN_INFO_SOCIETE_NOM') . ' <' . getDolGlobalString('MAIN_INFO_SOCIETE_MAIL') . '>';
					} elseif (preg_match('/user_aliases_(\d+)/', $fromtype, $reg)) {
						$tmp = explode(',', $user->email_aliases);
						$from = trim($tmp[($reg[1] - 1)]);
					} elseif (preg_match('/global_aliases_(\d+)/', $fromtype, $reg)) {
						$tmp = explode(',', getDolGlobalString('MAIN_INFO_SOCIETE_MAIL_ALIASES'));
						$from = trim($tmp[($reg[1] - 1)]);
					} elseif (preg_match('/senderprofile_(\d+)_(\d+)/', $fromtype, $reg)) {
						$sql = 'SELECT rowid, label, email FROM ' . MAIN_DB_PREFIX . 'c_email_senderprofile WHERE rowid = ' . ( int ) $reg[1];
						$resql = $db->query($sql);
						$obj = $db->fetch_object($resql);
						if ($obj) {
							$from = $obj->label . ' <' . $obj->email . '>';
						}
					} else {
						$from = $_POST['fromname'] . ' <' . $_POST['frommail'] . '>';
					}

					$replyto = $_POST['replytoname'] . ' <' . $_POST['replytomail'] . '>';
					$message = GETPOST('message', 'none');
					$subject = GETPOST('subject', 'none');

					// Make a change into HTML code to allow to include images from medias directory with an external reabable URL.
					// <img alt="" src="/dolibarr_dev/htdocs/viewimage.php?modulepart=medias&amp;entity=1&amp;file=image/ldestailleur_166x166.jpg" style="height:166px; width:166px" />
					// become
					// <img alt="" src="'.$urlwithroot.'viewimage.php?modulepart=medias&amp;entity=1&amp;file=image/ldestailleur_166x166.jpg" style="height:166px; width:166px" />
					$message = preg_replace('/(<img.*src=")[^\"]*viewimage\.php([^\"]*)modulepart=medias([^\"]*)file=([^\"]*)("[^\/]*\/>)/', '\1' . $urlwithroot . '/viewimage.php\2modulepart=medias\3file=\4\5', $message);

					$sendtobcc = GETPOST('sendtoccc', 'none');
					// Autocomplete the $sendtobcc
					// $autocopy can be MAIN_MAIL_AUTOCOPY_PROPOSAL_TO, MAIN_MAIL_AUTOCOPY_ORDER_TO, MAIN_MAIL_AUTOCOPY_INVOICE_TO, MAIN_MAIL_AUTOCOPY_SUPPLIER_PROPOSAL_TO...
					if (! empty($autocopy)) {
						$sendtobcc .= getDolGlobalString( $autocopy, (($sendtobcc ? ", " : "") . getDolGlobalString($autocopy)));
					}

					$deliveryreceipt = $_POST['deliveryreceipt'];

					if ($action == 'send') {
						$actionmsg2 = $langs->transnoentities('MailSentBy') . ' ' . CMailFile::getValidAddress($from, 4, 0, 1) . ' ' . $langs->transnoentities('To') . ' ' . CMailFile::getValidAddress($sendto, 4, 0, 1);
						if ($message) {
							$actionmsg = $langs->transnoentities('MailFrom') . ': ' . dol_escape_htmltag($from);
							$actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('MailTo') . ': ' . dol_escape_htmltag($sendto));
							if ($sendtocc)
								$actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('Bcc') . ": " . dol_escape_htmltag($sendtocc));
							$actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('MailTopic') . ": " . $subject);
							$actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('TextUsedInTheMessageBody') . ":");
							$actionmsg = dol_concatdesc($actionmsg, $message);
						}
					}

					// Create form object
					include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
					$formmail = new FormMail($db);
					$formmail->trackid = $trackid; // $trackid must be defined

					$attachedfiles = $formmail->get_attached_files();
					$attachedfiles['paths'][] = DOL_DATA_ROOT . '/referenceletters/' . $object->element_type . '/' . $object->ref_int . '/' . $object->ref_int . '.pdf';
					$attachedfiles['names'][] = $object->ref_int . '.pdf';
					$attachedfiles['mimes'][] = 'application/pdf';

					$filepath = $attachedfiles['paths'];
					$filename = $attachedfiles['names'];
					$mimetype = $attachedfiles['mimes'];
					// Feature to push mail sent into Sent folder
					if (! empty($conf->dolimail->enabled)) {
						$mailfromid = explode("#", $_POST['frommail'], 3); // $_POST['frommail'] = 'aaa#Sent# <aaa@aaa.com>' // TODO Use a better way to define Sent dir.
						if (count($mailfromid) == 0)
							$from = $_POST['fromname'] . ' <' . $_POST['frommail'] . '>';
						else {
							$mbid = $mailfromid[1];

							/* IMAP Postbox */
							$mailboxconfig = new IMAP($db);
							$mailboxconfig->fetch($mbid);
							if ($mailboxconfig->mailbox_imap_host)
								$ref = $mailboxconfig->get_ref();

							$mailboxconfig->folder_id = $mailboxconfig->mailbox_imap_outbox;
							$mailboxconfig->userfolder_fetch();

							if ($mailboxconfig->mailbox_save_sent_mails == 1) {

								$folder = str_replace($ref, '', $mailboxconfig->folder_cache_key);
								if (! $folder)
									$folder = "Sent"; // Default Sent folder

								$mailboxconfig->mbox = imap_open($mailboxconfig->get_connector_url() . $folder, $mailboxconfig->mailbox_imap_login, $mailboxconfig->mailbox_imap_password);
								if (FALSE === $mailboxconfig->mbox) {
									$info = FALSE;
									$err = $langs->trans('Error3_Imap_Connection_Error');
									setEventMessages($err, $mailboxconfig->element, null, 'errors');
								} else {
									$mailboxconfig->mailboxid = $_POST['frommail'];
									$mailboxconfig->foldername = $folder;
									$from = $mailfromid[0] . $mailfromid[2];
									$imap = 1;
								}
							}
						}
					}

					// Make substitution in email content
					$substitutionarray = getCommonSubstitutionArray($langs, 0, null, $sendobj);
					$substitutionarray['__EMAIL__'] = $sendto;
					// $substitutionarray['__CHECK_READ__'] = (is_object($sendobj) && is_object($object->thirdparty)) ? '<img src="'.DOL_MAIN_URL_ROOT.'/public/emailing/mailing-read.php?tag='.$object->thirdparty->tag.'&securitykey='.urlencode($conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY).'" width="1" height="1" style="width:1px;height:1px" border="0"/>' : '';

					$parameters = array(
							'mode' => 'formemail'
					);
					complete_substitutions_array($substitutionarray, $langs, $sendobj, $parameters);

					$subject = make_substitutions($subject, $substitutionarray);
					$message = make_substitutions($message, $substitutionarray);

					if (method_exists($sendobj, 'makeSubstitution')) {
						$subject = $sendobj->makeSubstitution($subject);
						$message = $sendobj->makeSubstitution($message);
					}

					// Send mail (substitutionarray must be done just before this)
					if (empty($sendcontext))
						$sendcontext = 'standard';
					$mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, $sendtobcc, $deliveryreceipt, - 1, '', '', $trackid, '', $sendcontext);

					if ($mailfile->error) {
						setEventMessage($mailfile->error, 'errors');
						$massaction = 'presend';
					} else {
						$result = $mailfile->sendfile();
						if ($result) {
							// FIXME This must be moved into the trigger for action $trigger_name
							if (! empty($conf->dolimail->enabled)) {
								$mid = (GETPOST('mid', 'int') ? GETPOST('mid', 'int') : 0); // Original mail id is set ?
								if ($mid) {
									// set imap flag answered if it is an answered mail
									$dolimail = new DoliMail($db);
									$dolimail->id = $mid;
									$res = $dolimail->set_prop($user, 'answered', 1);
								}
								if ($imap == 1) {
									// write mail to IMAP Server
									$movemail = $mailboxconfig->putMail($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, $folder, $deliveryreceipt, $mailfile);
									if ($movemail)
										setEventMessages($langs->trans("MailMovedToImapFolder", $folder), null, 'mesgs');
									else
										setEventMessages($langs->trans("MailMovedToImapFolder_Warning", $folder), null, 'warnings');
								}
							}

							// Initialisation of datas
							if (is_object($sendobj)) {
								if (empty($actiontypecode))
									$actiontypecode = 'AC_OTH_AUTO'; // Event insert into agenda automatically

								$object->socid = $sendtosocid; // To link to a company
								$object->sendtoid = $sendtoid; // To link to contacts/addresses. This is an array.
								$object->actiontypecode = $actiontypecode; // Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
								$object->actionmsg = $actionmsg; // Long text
								$object->actionmsg2 = $actionmsg2; // Short text
								$object->trackid = $trackid;
								$object->fk_element = $sendobj->id;
								$object->elementtype = $object->element;
								if (is_array($attachedfiles) && count($attachedfiles) > 0) {
									$object->attachedfiles = $attachedfiles;
								}

								// Call of triggers
								if (! empty($trigger_name)) {
									include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
									$interface = new Interfaces($db);

									$result = $interface->run_triggers($trigger_name, $object, $user, $langs, $conf);
									if ($result < 0) {
										setEventMessages($interface->error, $interface->errors, 'errors');
									}
								}
							}

							// Redirect here
							// This avoid sending mail twice if going out and then back to page
							$mesg = $langs->trans('MailSuccessfulySent', $mailfile->getValidAddress($from, 2), $mailfile->getValidAddress($sendto, 2));
							setEventMessages($mesg, null, 'mesgs');
						} else {
							$langs->load("other");
							$mesg = '<div class="error">';
							if ($mailfile->error) {
								$mesg .= $langs->trans('ErrorFailedToSendMail', $from, $sendto);
								$mesg .= '<br>' . $mailfile->error;
							} else {
								$mesg .= 'No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS';
							}
							$mesg .= '</div>';

							setEventMessages($mesg, null, 'warnings');
							$action = 'presend';
						}
					}
				} else {
					$langs->load("errors");
					setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("MailTo")), null, 'warnings');
					dol_syslog('Try to send email with no recipient defined', LOG_WARNING);
					$action = 'presend';
				}
			} else {
				$langs->load("other");
				setEventMessages($langs->trans('ErrorFailedToReadObject', $object->element), null, 'errors');
				dol_syslog('Failed to read data of object id=' . $object->id . ' element=' . $object->element);
				$action = 'presend';
			}
		}
		header('Location: ' . $_SERVER["PHP_SELF"] . '?removAll=1');
		exit();
	}
}
