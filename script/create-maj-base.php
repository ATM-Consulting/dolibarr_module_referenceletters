<?php
if (is_file('../main.inc.php'))
	$dir = '../';
else if (is_file('../../../main.inc.php'))
	$dir = '../../../';
else
	$dir = '../../';

if (! defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
	include ($dir . "master.inc.php");
} elseif (! defined('INC_FROM_DOLIBARR')) {
	include ($dir . "main.inc.php");
} else {
	global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
}
if (! defined('DB_HOST')) {
	define('DB_HOST', $dolibarr_main_db_host);
	define('DB_NAME', $dolibarr_main_db_name);
	define('DB_USER', $dolibarr_main_db_user);
	define('DB_PASS', $dolibarr_main_db_pass);
	define('DB_DRIVER', $dolibarr_main_db_type);
}

dol_include_once('/referenceletters/class/referenceletters.class.php');
dol_include_once('/referenceletters/class/referenceletterschapters.class.php');

global $db, $langs, $reinstalltemplate;

$langs->load('referenceletters@referenceletters');

$rfltr = new ReferenceLetters($db);

/**
 * ********************************
 */
/**
 * *********** Propal *************
 */
/**
 * ********************************
 */

$title = $langs->transnoentities('RefLtrPropal');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'propal';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td>MON LOGO ENTREPRISE</td>
			<td style="text-align:right"><strong>Proposition commerciale<br />
			R&eacute;f. :&nbsp;{object_ref}</strong><br />
			Date :&nbsp;{object_date}<br />
			Date de fin de validit&eacute; :&nbsp;{object_date_end}<br />
			Code client :&nbsp;{cust_company_customercode}<br />
			{objets_lies}</td>
		</tr>
	</tbody>
</table>';
	$rfltr->use_custom_header = 1;
	$rfltr->footer = '<div style="text-align:center"><br />
<span style="font-size:8px">{mycompany_juridicalstatus} - SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} - Num VA :&nbsp;{mycompany_vatnumber}</span><br />
&nbsp;</div>
';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $this->errors, 'errors');
	}
	// Instanciation du contenu
	if (! empty($id_rfltr)) {

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<table cellpadding="1" cellspacing="1" style="width:550px">
	<tbody>
		<tr>
			<td style="width:50%">Emetteur :<br />
			&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="background-color:#e6e6e6; height:121px"><br />
						<strong>{mycompany_name}</strong><br />
						{object_contactsale}<br />
						{mycompany_address}<br />
						{mycompany_zip}&nbsp;{mycompany_town}<br />
						<br />
						T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
						Email : {mycompany_email}<br />
						Web :&nbsp;{mycompany_web}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:50%">Adress&eacute; &agrave; :<br />
			&nbsp;
			<table border="1" style="width:245px">
				<tbody>
					<tr>
						<td style="height:121px"><br />
						<strong>{cust_company_name}</strong><br />
						{cust_contactclient}<br />
						{cust_contactclientfact}<br />
						{cust_company_address}<br />
						{cust_company_zip}&nbsp;{cust_company_town}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<div style="text-align:right">Montants exprim&eacute;s en Euros</div>

<table border="1" style="cellpadding:1; cellspacing:1; width:530px">
	<tbody>
		<tr>
			<td style="width:50%">D&eacute;signation</td>
			<td style="width:10%">TVA</td>
			<td style="width:10%">P.U. HT</td>
			<td style="width:10%">Qt&eacute;</td>
			<td style="width:10%">R&eacute;duc.</td>
			<td style="width:10%">Total HT[!-- BEGIN lines --]</td>
		</tr>
		<tr>
			<td>{line_fulldesc}</td>
			<td style="text-align:right">{line_vatrate}</td>
			<td style="text-align:right">{line_up_locale}</td>
			<td style="text-align:right">{line_qty}</td>
			<td style="text-align:right">{line_discount_percent}</td>
			<td style="text-align:right">{line_price_ht_locale}[!-- END lines --]</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td rowspan="3" style="width:60%"><strong>Conditions de r&egrave;glement</strong> : {objvar_object_cond_reglement_doc}<br />
			<strong>Mode de r&egrave;glement</strong> : {objvar_object_mode_reglement}</td>
			<td style="width:20%">Total HT</td>
			<td style="text-align:right; width:20%">{objvar_object_total_ht}</td>
		</tr>
		<tr>
			<td style="background-color:#f5f5f5; width:20%">{tva_detail_titres}</td>
			<td style="background-color:#f5f5f5; text-align:right; width:20%">{tva_detail_montants}</td>
		</tr>
		<tr>
			<td style="background-color:#e6e6e6; width:20%">Total TTC</td>
			<td style="background-color:#e6e6e6; text-align:right; width:20%">{objvar_object_total_ttc}</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td style="width:55%">&nbsp;</td>
			<td style="width:45%"><br />
			Cachet, Date, Signature et mention &quot;Bon pour accord&quot;<br />
			&nbsp;
			<table border="1" cellpadding="1" cellspacing="1" style="width:200px">
				<tbody>
					<tr>
						<td style="height:70px; width:220px">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

//
// *********** Facture *************
//
$title = $langs->transnoentities('RefLtrInvoice');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'invoice';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td>MON LOGO ENTREPRISE</td>
			<td style="text-align:right"><strong>Facture<br />
			R&eacute;f. :&nbsp;{object_ref}</strong><br />
			Date facturation :&nbsp;{object_date}<br />
			Date &eacute;ch&eacute;ance :&nbsp;{object_date_limit}<br />
			Code client :&nbsp;{cust_company_customercode}<br />
			{objets_lies}</td>
		</tr>
	</tbody>
</table>';
	$rfltr->use_custom_header = 1;
	$rfltr->footer = '<div style="text-align:center"><br />
<span style="font-size:8px">{mycompany_juridicalstatus} - SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} - Num VA :&nbsp;{mycompany_vatnumber}</span><br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {

		// Instanciation du contenu

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<table cellpadding="1" cellspacing="1" style="width:550px">
	<tbody>
		<tr>
			<td style="width:50%">Emetteur :<br />
			&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="background-color:#e6e6e6; height:121px"><br />
						<strong>{mycompany_name}</strong><br />
						{object_contactsale}<br />
						{mycompany_address}<br />
						{mycompany_zip}&nbsp;{mycompany_town}<br />
						<br />
						T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
						Email : {mycompany_email}<br />
						Web :&nbsp;{mycompany_web}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:50%">Adress&eacute; &agrave; :<br />
			&nbsp;
			<table border="1" style="width:245px">
				<tbody>
					<tr>
						<td style="height:121px"><br />
						<strong>{cust_company_name}</strong><br />
						{cust_contactclient}<br />
						{cust_contactclientfact}<br />
						{cust_company_address}<br />
						{cust_company_zip}&nbsp;{cust_company_town}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<div style="text-align:right">Montants exprim&eacute;s en Euros</div>

<table border="1" style="cellpadding:1; cellspacing:1; width:530px">
	<tbody>
		<tr>
			<td style="width:50%">D&eacute;signation</td>
			<td style="width:10%">TVA</td>
			<td style="width:10%">P.U. HT</td>
			<td style="width:10%">Qt&eacute;</td>
			<td style="width:10%">R&eacute;duc.</td>
			<td style="width:10%">Total HT[!-- BEGIN lines --]</td>
		</tr>
		<tr>
			<td>{line_fulldesc}</td>
			<td style="text-align:right">{line_vatrate}</td>
			<td style="text-align:right">{line_up_locale}</td>
			<td style="text-align:right">{line_qty}</td>
			<td style="text-align:right">{line_discount_percent}</td>
			<td style="text-align:right">{line_price_ht_locale}[!-- END lines --]</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td rowspan="6" style="width:60%"><strong>Conditions de r&egrave;glement</strong> : {objvar_object_cond_reglement_doc}<br />
			<strong>Mode de r&egrave;glement</strong> : {objvar_object_mode_reglement}</td>
			<td style="width:20%">Total HT</td>
			<td style="text-align:right; width:20%">{objvar_object_total_ht}</td>
		</tr>
		<tr>
			<td style="background-color:#f5f5f5; width:20%">{tva_detail_titres}</td>
			<td style="background-color:#f5f5f5; text-align:right; width:20%">{tva_detail_montants}</td>
		</tr>
		<tr>
			<td style="background-color:#e6e6e6; width:20%">Total TTC</td>
			<td style="background-color:#e6e6e6; text-align:right; width:20%">{objvar_object_total_ttc}</td>
		</tr>
		<tr>
			<td style="width:20%">Pay&eacute;</td>
			<td style="text-align:right; width:20%">{deja_paye}</td>
		</tr>
		<tr>
			<td style="width:20%">Avoirs</td>
			<td style="text-align:right; width:20%">{somme_avoirs}</td>
		</tr>
		<tr>
			<td style="background-color:#e6e6e6; width:20%">Reste &agrave; payer</td>
			<td style="background-color:#e6e6e6; text-align:right; width:20%">{reste_a_payer}</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td style="width:55%">&nbsp;</td>
			<td style="width:45%">&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:200px">
				<tbody>
					<tr>
						<td style="height:70px; width:220px">{liste_paiements}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

//
// *********** Commande *************
//
$title = $langs->transnoentities('RefLtrOrder');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'order';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td>MON LOGO ENTREPRISE</td>
			<td style="text-align:right"><strong>Commande<br />
			R&eacute;f. :&nbsp;{object_ref}</strong><br />
			Date de commande :&nbsp;{object_date}<br />
			{objets_lies}</td>
		</tr>
	</tbody>
</table>';
	$rfltr->use_custom_header = 1;
	$rfltr->footer = '<div style="text-align:center"><br />
<span style="font-size:8px">{mycompany_juridicalstatus} - SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} - Num VA :&nbsp;{mycompany_vatnumber}</span><br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {
		// Instanciation du contenu

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<table cellpadding="1" cellspacing="1" style="width:550px">
	<tbody>
		<tr>
			<td style="width:50%">Emetteur :<br />
			&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="background-color:#e6e6e6; height:121px"><br />
						<strong>{mycompany_name}</strong><br />
						{object_contactsale}<br />
						{mycompany_address}<br />
						{mycompany_zip}&nbsp;{mycompany_town}<br />
						<br />
						T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
						Email : {mycompany_email}<br />
						Web :&nbsp;{mycompany_web}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:50%">Adress&eacute; &agrave; :<br />
			&nbsp;
			<table border="1" style="width:245px">
				<tbody>
					<tr>
						<td style="height:121px"><br />
						<strong>{cust_company_name}</strong><br />
						{cust_contactclient}<br />
						{cust_contactclientfact}<br />
						{cust_company_address}<br />
						{cust_company_zip}&nbsp;{cust_company_town}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<div style="text-align:right">Montants exprim&eacute;s en Euros</div>

<table border="1" style="cellpadding:1; cellspacing:1; width:530px">
	<tbody>
		<tr>
			<td style="width:50%">D&eacute;signation</td>
			<td style="width:10%">TVA</td>
			<td style="width:10%">P.U. HT</td>
			<td style="width:10%">Qt&eacute;</td>
			<td style="width:10%">R&eacute;duc.</td>
			<td style="width:10%">Total HT[!-- BEGIN lines --]</td>
		</tr>
		<tr>
			<td>{line_fulldesc}</td>
			<td style="text-align:right">{line_vatrate}</td>
			<td style="text-align:right">{line_up_locale}</td>
			<td style="text-align:right">{line_qty}</td>
			<td style="text-align:right">{line_discount_percent}</td>
			<td style="text-align:right">{line_price_ht_locale}[!-- END lines --]</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td rowspan="3" style="width:60%"><strong>Conditions de r&egrave;glement</strong> : {objvar_object_cond_reglement_doc}<br />
			<strong>Mode de r&egrave;glement</strong> : {objvar_object_mode_reglement}</td>
			<td style="width:20%">Total HT</td>
			<td style="text-align:right; width:20%">{objvar_object_total_ht}</td>
		</tr>
		<tr>
			<td style="background-color:#f5f5f5; width:20%">{tva_detail_titres}</td>
			<td style="background-color:#f5f5f5; text-align:right; width:20%">{tva_detail_montants}</td>
		</tr>
		<tr>
			<td style="background-color:#e6e6e6; width:20%">Total TTC</td>
			<td style="background-color:#e6e6e6; text-align:right; width:20%">{objvar_object_total_ttc}</td>
		</tr>
	</tbody>
</table>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

//
// *********** Contrat *************
//
$title = $langs->transnoentities('RefLtrContract');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'contract';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td>MON LOGO ENTREPRISE</td>
			<td style="text-align:right"><strong>Fiche contrat<br />
			R&eacute;f. :&nbsp;{object_ref}</strong><br />
			Date :&nbsp;{object_date_creation}<br />
			Code client :&nbsp;{cust_company_customercode}</td>
		</tr>
	</tbody>
</table>';
	$rfltr->use_custom_header = 1;
	$rfltr->footer = '<div style="text-align:center"><br />
<span style="font-size:8px">{mycompany_juridicalstatus} - SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} - Num VA :&nbsp;{mycompany_vatnumber}</span><br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {
		// Instanciation du contenu

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<table cellpadding="1" cellspacing="1" style="width:550px">
	<tbody>
		<tr>
			<td style="width:50%">Emetteur :<br />
			&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="background-color:#e6e6e6; height:121px"><br />
						<strong>{mycompany_name}</strong><br />
						{object_contactsale}<br />
						{mycompany_address}<br />
						{mycompany_zip}&nbsp;{mycompany_town}<br />
						<br />
						T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
						Email : {mycompany_email}<br />
						Web :&nbsp;{mycompany_web}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:50%">Adress&eacute; &agrave; :<br />
			&nbsp;
			<table border="1" style="width:245px">
				<tbody>
					<tr>
						<td style="height:121px"><br />
						<strong>{cust_company_name}</strong><br />
						{cust_contactclient}<br />
						{cust_contactclientfact}<br />
						{cust_company_address}<br />
						{cust_company_zip}&nbsp;{cust_company_town}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table border="1" style="width:530px">
	<tbody>
		<tr>
			<td>[!-- BEGIN lines --]{line_product_ref} -&nbsp;{line_product_label}<br />
			Quantit&eacute; :&nbsp;<strong>{line_qty}</strong> - Prix unitaire :&nbsp;<strong>{line_price_ht_locale}</strong><br />
			Date d&eacute;but pr&eacute;vue : <strong>{date_ouverture_prevue}</strong> - Date pr&eacute;vue fin de service : <strong>{date_fin_validite}</strong><br />
			Date d&eacute;but : <strong>{date_ouverture}</strong><br />
			{line_desc}<br />
			<br />
			[!-- END lines --]</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
<br />
<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:530px">
	<tbody>
		<tr>
			<td style="width:55%"><br />
			Pour&nbsp;{mycompany_name}, nom et signature :<br />
			&nbsp;
			<table border="1" cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="height:70px; width:220px">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:45%"><br />
			Pour&nbsp;{cust_company_name}, nom et signature :<br />
			&nbsp;
			<table border="1" cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="height:70px; width:220px">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

//
// *********** price request *************
//
$title = $langs->transnoentities('RefLtrSupplierProposals');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'supplier_proposal';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td>MON LOGO ENTREPRISE</td>
			<td style="text-align:right"><strong>Demande de prix<br />
			R&eacute;f. :&nbsp;{object_ref}</strong><br />
			Code fournisseur : :&nbsp;{cust_company_suppliercode}<br />
			{objets_lies}</td>
		</tr>
	</tbody>
</table>';
	$rfltr->use_custom_header = 1;
	$rfltr->footer = '<div style="text-align:center"><br />
<span style="font-size:8px">{mycompany_juridicalstatus} - SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} - Num VA :&nbsp;{mycompany_vatnumber}</span><br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {
		// Instanciation du contenu

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<table cellpadding="1" cellspacing="1" style="width:550px">
	<tbody>
		<tr>
			<td style="width:50%">Emetteur :<br />
			&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="background-color:#e6e6e6; height:121px"><br />
						<strong>{mycompany_name}</strong><br />
						{object_contactsale}<br />
						{mycompany_address}<br />
						{mycompany_zip}&nbsp;{mycompany_town}<br />
						<br />
						T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
						Email : {mycompany_email}<br />
						Web :&nbsp;{mycompany_web}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:50%">Adress&eacute; &agrave; :<br />
			&nbsp;
			<table border="1" style="width:245px">
				<tbody>
					<tr>
						<td style="height:121px"><br />
						<strong>{cust_company_name}</strong><br />
						{cust_contactclient}<br />
						{cust_contactclientfact}<br />
						{cust_company_address}<br />
						{cust_company_zip}&nbsp;{cust_company_town}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<div style="text-align:right">Montants exprim&eacute;s en Euros</div>

<table border="1" style="cellpadding:1; cellspacing:1; width:530px">
	<tbody>
		<tr>
			<td style="width:50%">D&eacute;signation</td>
			<td style="width:10%">TVA</td>
			<td style="width:10%">P.U. HT</td>
			<td style="width:10%">Qt&eacute;</td>
			<td style="width:10%">Total HT[!-- BEGIN lines --]</td>
		</tr>
		<tr>
			<td>{line_fulldesc}</td>
			<td style="text-align:right"></td>
			<td style="text-align:right"></td>
			<td style="text-align:right">{line_qty}</td>
			<td style="text-align:right">[!-- END lines --]</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td rowspan="3" style="width:60%"><strong>Date pr&egrave;vue de livraison</strong> : {object_date_livraison}<br />
			<strong>Mode de r&egrave;glement</strong> : {objvar_object_mode_reglement}</td>
		</tr>
	</tbody>
</table>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

//
// ************ Supplier order *************
//
$title = $langs->transnoentities('RefLtrSupplierOrders');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'order_supplier';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td>MON LOGO ENTREPRISE</td>
			<td style="text-align:right"><strong>Commande fournisseur {object_ref}<br />
			R&eacute;f. :&nbsp;{objvar_object_ref_supplier}</strong><br />
			Date pr&egrave;vue de livraison :&nbsp;{object_date_delivery_planed}<br />
			Code fournisseur : :&nbsp;{cust_company_suppliercode}<br />
			{objets_lies}</td>
		</tr>
	</tbody>
</table>';
	$rfltr->use_custom_header = 1;
	$rfltr->footer = '<div style="text-align:center"><br />
<span style="font-size:8px">{mycompany_juridicalstatus} - SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} - Num VA :&nbsp;{mycompany_vatnumber}</span><br />
&nbsp;</div>
';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {

		// Instanciation du contenu

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<table cellpadding="1" cellspacing="1" style="width:550px">
	<tbody>
		<tr>
			<td style="width:50%">Emetteur :<br />
			&nbsp;
			<table cellpadding="1" cellspacing="1" style="width:242px">
				<tbody>
					<tr>
						<td style="background-color:#e6e6e6; height:121px"><br />
						<strong>{mycompany_name}</strong><br />
						{object_contactsale}<br />
						{mycompany_address}<br />
						{mycompany_zip}&nbsp;{mycompany_town}<br />
						<br />
						T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
						Email : {mycompany_email}<br />
						Web :&nbsp;{mycompany_web}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td style="width:50%">Adress&eacute; &agrave; :<br />
			&nbsp;
			<table border="1" style="width:245px">
				<tbody>
					<tr>
						<td style="height:121px"><br />
						<strong>{cust_company_name}</strong><br />
						{cust_contactclient}<br />
						{cust_contactclientfact}<br />
						{cust_company_address}<br />
						{cust_company_zip}&nbsp;{cust_company_town}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<div style="text-align:right">Montants exprim&eacute;s en Euros</div>

<table border="1" style="cellpadding:1; cellspacing:1; width:530px">
	<tbody>
		<tr>
			<td style="width:50%">D&eacute;signation</td>
			<td style="width:10%">TVA</td>
			<td style="width:10%">P.U. HT</td>
			<td style="width:10%">Qt&eacute;</td>
			<td style="width:10%">R&eacute;duc.</td>
			<td style="width:10%">Total HT[!-- BEGIN lines --]</td>
		</tr>
		<tr>
			<td>{line_fulldesc}</td>
			<td style="text-align:right">{line_vatrate}</td>
			<td style="text-align:right">{line_up_locale}</td>
			<td style="text-align:right">{line_qty}</td>
			<td style="text-align:right">{line_discount_percent}</td>
			<td style="text-align:right">{line_price_ht_locale}[!-- END lines --]</td>
		</tr>
	</tbody>
</table>
&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td rowspan="3" style="width:60%"><strong>Conditions de r&egrave;glement</strong> : {object_payment_term}<br />
			<strong>Mode de r&egrave;glement</strong> : {object_payment_mode}</td>
			<td style="width:20%">Total HT</td>
			<td style="text-align:right; width:20%">{objvar_object_total_ht}</td>
		</tr>
		<tr>
			<td style="background-color:#f5f5f5; width:20%">{tva_detail_titres}</td>
			<td style="background-color:#f5f5f5; text-align:right; width:20%">{tva_detail_montants}</td>
		</tr>
		<tr>
			<td style="background-color:#e6e6e6; width:20%">Total TTC</td>
			<td style="background-color:#e6e6e6; text-align:right; width:20%">{objvar_object_total_ttc}</td>
		</tr>
	</tbody>
</table>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

//
// *********** Document exemple Agefodd *************
//
$title = $langs->trans('RefLtrAgefodd');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {

	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'rfltr_agefodd_courrier';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '<table border="0" cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td style="width:400px"><span style="font-size:9px"><strong>{mycompany_name}</strong><br />
			{mycompany_address}<br />
			{mycompany_zip}&nbsp;{mycompany_town}<br />
			T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
			Email : {mycompany_email}<br />
			Web :&nbsp;{mycompany_web}<br />
			Formation / Session : {formation_ref}/ {objvar_object_ref}<br />
			Date : {objvar_object_date_text}</span></td>
			<td>YOUR LOGO</td>
		</tr>
	</tbody>
</table>';
	$rfltr->footer = '<hr /><div style="text-align:center"><span style="color:#95a5a6"><span style="font-size:6px">{mycompany_name} {mycompany_address} -&nbsp; {mycompany_zip}&nbsp;{mycompany_town} - T&eacute;l. : {mycompany_phone} - mail : {mycompany_email} - {mycompany_juridicalstatus} - Capital de {mycompany_capital} SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} -&nbsp; N&deg; d&eacute;claration d&#39;activit&eacute; (ce num&eacute;ro ne vaut pas agr&eacute;ment de l&#39;&eacute;tat) {__[AGF_ORGANISME_NUM]__} &nbsp; - pr&eacute;fecture {__[AGF_ORGANISME_PREF]__} &nbsp;Num VA :&nbsp;{mycompany_vatnumber}</span></span><br />
<br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {
		// Instanciation du contenu

		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = '<u>Formation : </u><br />
Intitul&eacute; formation : <strong>{formation_nom}</strong><br />
But de la formation : <strong>{formation_but}</strong><br />
M&eacute;thode de la formation : <strong>{formation_methode}</strong><br />
Pr&eacute;requis : <strong>{formation_prerequis}</strong><br />
Sanction : <strong>{formation_sanction}</strong><br />
Type (intra/inter) : <strong>{formation_type}</strong><br />
Type de participant : <strong>{formation_type_stagiaire}</strong><br />
Programme : <strong>{formation_programme}</strong><br />
Documents necessaires : <strong>{formation_documents}</strong><br />
Equipement necessaire : <strong>{formation_equipements}</strong><br />
Référence : <strong>{formation_ref}</strong><br />
Référence Interne : <strong>{formation_refint}</strong><br />
Objectif de formation text : <strong>{formation_obj_peda}</strong><br />
Tableau des objectifs :<br />
[!-- BEGIN TFormationObjPeda --] Priorit&eacute;/Rang : <strong>{line_objpeda_rang}, </strong>Description : <strong>{line_objpeda_description}</strong><br />
[!-- END TFormationObjPeda --]<br />
<br />
<u>Lieu</u> :&nbsp;<br />
<strong>{formation_lieu} -&nbsp;{formation_lieu_adresse}&nbsp;{formation_lieu_cp}&nbsp;{formation_lieu_ville}</strong><br />
Instruction d&#39;acces au lieu : <strong>{formation_lieu_acces}</strong><br />
Horaire du lieu : <strong>{formation_lieu_horaires}</strong><br />
Infos diverses :&nbsp;<strong>{formation_lieu_divers}</strong><br />
Commentaire Lieu : <strong>{formation_lieu_notes}</strong><br />
<br />
<u>Session : </u><br />
Id Session : <strong>{objvar_object_id}</strong><br />
Ref Session : <strong>{objvar_object_ref}</strong><br />
Client :<strong> {formation_societe} </strong>(Intra)<br />
Commenatire session : <strong>{formation_commentaire}</strong><br />
Date : du <strong>{formation_date_debut}</strong> au&nbsp;<strong>{formation_date_fin}</strong><br />
Autre format de date: <strong>{objvar_object_date_text}</strong><br />
Dur&eacute;e : <strong>{formation_duree}</strong> heure(s)<br />
Dur&eacute;e Session: <strong>{formation_duree_session}</strong> heure(s)<br />
Nombre de jours <strong>{session_nb_days}</strong> jour(s)<br />
Commercial de la session : <strong>{formation_commercial}</strong><br />
<br />
<u>Liste horaires :</u><br />
<br />
[!-- BEGIN THorairesSession --]Le&nbsp;<strong>{line_date_session} </strong>:<br />
- D&eacute;but&nbsp;<strong>{line_heure_debut_session}</strong>&nbsp;<br />
- Fin&nbsp;<strong>{line_heure_fin_session}</strong><br />
[!-- END THorairesSession --]<br />
<br />
Horaire session en texte : <strong>{objvar_object_dthour_text}</strong><br />
<br />
<br />
<u>Tableau des participants :</u><br />
&nbsp;<br />
&nbsp;
<table border="1" cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td style="text-align:center"><span style="font-size:11px">Nom - Pr&eacute;nom</span></td>
			<td style="text-align:center"><span style="font-size:11px">Structure</span></td>
			<td style="text-align:center"><span style="font-size:11px">Fonction</span></td>
			<td style="text-align:center"><span style="font-size:11px">Date de naissance</span></td>
			<td style="text-align:center"><span style="font-size:11px">Lieu de naissance</span></td>
			<td style="text-align:center"><span style="font-size:11px">Type financement</span>[!-- BEGIN TStagiairesSession --]</td>
		</tr>
		<tr>
			<td style="text-align:center"><br />
			<strong><span style="font-size:11px">{line_civilite} {line_nom}&nbsp;{line_prenom}</span></strong><br />
			&nbsp;</td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_nom_societe} ({line_code_societe})</span></strong></td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_poste}</span></strong></td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_birthday}</span></strong></td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_birthplace}</span></strong></td>
			<td style="text-align:center"><strong>{line_type}</strong>[!-- END TStagiairesSession --]</td>
		</tr>
	</tbody>
</table>
<br />
<br />
<u>Convention :</u><br />
Tiers convention <span style="color:#ff0000">(disponible uniquement sur PDF convention)</span> :<br />
<br />
Montant HT convention : {objvar_object_conv_amount_ht} Montant TVA convention : {objvar_object_conv_amount_tva} Montant TTC convention : {objvar_object_conv_amount_ttc}<br />
<strong>{objvar_object_document_societe_name}<br />
{objvar_object_document_societe_address}<br />
{objvar_object_document_societe_zip}&nbsp;{objvar_object_document_societe_town}</strong><br />
Repr&eacute;sent&eacute; par<strong>&nbsp;{objvar_object_signataire_intra}/{objvar_object_signataire_inter}</strong><br />
Nombre de participant de la convention<strong> </strong>{formation_nb_stagiaire_convention}<br />
<br />
<br />
<table border="0.5" cellpadding="1" cellspacing="1" style="width:440px">
	<tbody>
		<tr>
			<td style="background-color:#dddddd; text-align:center; width:40%">D&eacute;signation</td>
			<td style="background-color:#dddddd; text-align:center">TVA</td>
			<td style="background-color:#dddddd; text-align:center">PU H.T</td>
			<td style="background-color:#dddddd; text-align:center">R&eacute;duc.</td>
			<td style="background-color:#dddddd; text-align:center">Qt&eacute;</td>
			<td style="background-color:#dddddd; text-align:center">Total HT</td>
			<td style="background-color:#dddddd; text-align:center">Total TTC[!-- BEGIN TConventionFinancialLine --]</td>
		</tr>
		<tr>
			<td style="text-align:center">{line_fin_desciption}</td>
			<td style="text-align:center">{line_fin_tva_tx}</td>
			<td style="text-align:center">{line_fin_pu_ht}</td>
			<td style="text-align:center">{line_fin_discount}</td>
			<td style="text-align:center">{line_fin_qty}</td>
			<td style="text-align:center">{line_fin_amount_ht}</td>
			<td style="text-align:center">{line_fin_amount_ttc}[!-- END TConventionFinancialLine --]</td>
		</tr>
		<tr>
			<td colspan="5" style="border-color:#ffffff">&nbsp;</td>
			<td style="background-color:#dddddd">Total HT</td>
			<td>{objvar_object_conv_amount_ht}</td>
		</tr>
		<tr>
			<td colspan="5" style="border-color:#ffffff">&nbsp;</td>
			<td style="background-color:#dddddd">Total TVA</td>
			<td>{objvar_object_conv_amount_tva}</td>
		</tr>
		<tr>
			<td colspan="5" style="border-color:#ffffff">&nbsp;</td>
			<td style="background-color:#dddddd">Total TTC</td>
			<td>{objvar_object_conv_amount_ttc}</td>
		</tr>
	</tbody>
</table>
<br />
<br />
Tableau des participants au sein d&#39;une convention <span style="color:#ff0000">(disponible uniquement sur PDF convention)</span> :<br />
&nbsp;<br />
&nbsp;
<table border="1" cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td style="text-align:center"><span style="font-size:11px">Nom - Pr&eacute;nom</span></td>
			<td style="text-align:center"><span style="font-size:11px">Structure</span></td>
			<td style="text-align:center"><span style="font-size:11px">Fonction</span></td>
			<td style="text-align:center">Type financement[!-- BEGIN TStagiairesSessionConvention --]</td>
		</tr>
		<tr>
			<td style="text-align:center"><br />
			<strong><span style="font-size:11px">{line_civilite} {line_nom}&nbsp;{line_prenom}</span></strong><br />
			&nbsp;</td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_nom_societe} ({line_code_societe})</span></strong></td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_poste}</span></strong></td>
			<td style="text-align:center"><strong>{line_type}</strong>[!-- END TStagiairesSessionConvention --]</td>
		</tr>
	</tbody>
</table>
<br />
<br />
Ou en forme texte : {formation_stagiaire_convention}
<br />
Tableau des participants au sein d&#39;une convention <span style="color:#ff0000">(disponible uniquement sur PDF &nbsp;d&#39;une soci&eacute;te)</span> :<br />
&nbsp;<br />
&nbsp;
<table border="1" cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td style="text-align:center"><span style="font-size:11px">Nom - Pr&eacute;nom</span></td>
			<td style="text-align:center"><span style="font-size:11px">Structure</span></td>
			<td style="text-align:center"><span style="font-size:11px">Fonction</span></td>
			<td style="text-align:center">Type financement[!-- BEGIN TStagiairesSessionSoc --]</td>
		</tr>
		<tr>
			<td style="text-align:center"><br />
			<strong><span style="font-size:11px">{line_civilite} {line_nom}&nbsp;{line_prenom}</span></strong><br />
			&nbsp;</td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_nom_societe} ({line_code_societe})</span></strong></td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_poste}</span></strong></td>
			<td style="text-align:center"><strong>{line_type}</strong>[!-- END TStagiairesSessionSoc --]</td>
		</tr>
	</tbody>
</table>
<br />
<br />
<br />
<u>Liste des formateurs :</u><br />
<br />
[!-- BEGIN TFormateursSession --] Nom : <strong>{line_formateur_nom}, </strong>pr&eacute;nom : <strong>{line_formateur_prenom}</strong>, statut : <strong>{line_formateur_statut}</strong><br />
[!-- END TFormateursSession --]<br />
<br />
ou en une ligne : <strong>{objvar_object_trainer_text}</strong><br />
Cout formateur (cout/nb de creneaux): <strong>{objvar_object_trainer_day_cost}</strong><br />
D&eacute;tail par formateur<span style="color:#ff0000"> (disponible uniquement sur contrat formateur)</span> :<br />
<br />
<strong>{objvar_object_formateur_session_lastname}&nbsp;{objvar_object_formateur_session_firstname}<br />
{objvar_object_formateur_session_address}<br />
{objvar_object_formateur_session_zip}&nbsp;{objvar_object_formateur_session_town}</strong><br />
Siret : <strong>{objvar_object_formateur_session_societe_idprof2}</strong><br />
Horaire du formateur dans la session avec heure :{trainer_datehourtextline}
Horaire du formateur dans la session sans heure :{trainer_datetextline}
<br />
<br />
<u>Autres :</u><br />
<br />
Repr&eacute;sentant Agefodd : <strong>{objvar_object_AGF_ORGANISME_REPRESENTANT}</strong> Numero de d&eacute;claration : <strong>{objvar_object_AGF_ORGANISME_NUM}</strong> Prefecture : <strong>{objvar_object_AGF_ORGANISME_PREF}</strong>';

		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}

		unset($chapter);
		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Saut de page dans une boucle';
		$chapter->content_text = 'Tableau des participants au sein d&#39;une convention (disponible uniquement sur PDF convention) :<br />
&nbsp;<br />
Saut de page dans une boucle (ex: un stagiaire par page)<br />
<br />
[!-- BEGIN TStagiairesSession --]<br />
<strong>{line_civilite} {line_nom}&nbsp;{line_prenom}<br />
<br />
{line_nom_societe} ({line_code_societe})</strong><br />
<br />
@breakpage@<br />
[!-- END TStagiairesSession --]

Saut de page dans une boucle (ex: un stagiaire PRESENT ou PARTIELLEMENT par page)<br />
<br />
[!-- BEGIN TStagiairesSessionPresent --]<br />
<strong>{line_civilite} {line_nom}&nbsp;{line_prenom}<br />
<br />
{line_nom_societe} ({line_code_societe})</strong><br />
<br />
@breakpage@<br />
[!-- END TStagiairesSessionPresent --]
';

		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

/**
 * *********** Document exemple convention Agefodd *************
 */
$title = $langs->trans('RefLtrAgefoddConvention');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'rfltr_agefodd_convention';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '&nbsp;<br />
&nbsp;<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td style="width:25%"></td>
			<td style="text-align:center; width:50%"><span style="font-size:14px"><strong>CONVENTION DE FORMATION<br />
			PROFESSIONNELLE CONTINUE</strong></span></td>
			<td style="width:25%"></td>
		</tr>
	</tbody>
</table>
';
	$rfltr->footer = '<div style="text-align:center"><span style="font-size:8px"><strong>{mycompany_name}</strong><br />
{mycompany_address} -&nbsp;{mycompany_zip}&nbsp;{mycompany_town} - <u>T&eacute;l. :</u>&nbsp;{myuser_phone}&nbsp;- <u>Fax :</u>&nbsp;{myuser_fax}<br />
<strong>email :&nbsp;{mycompany_email}&nbsp;- Site :&nbsp;{mycompany_web}</strong><br />
{mycompany_juridicalstatus}&nbsp;- Siret :&nbsp;{mycompany_idprof1} - APE :&nbsp;{mycompany_idprof3}</span><br />
<br />
<br />
<br />
&nbsp;</div>
';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_custom_header = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {
		// Instanciation du contenu
		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Page titre';
		$chapter->content_text = '&nbsp;
<div style="text-align:center"><br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<span style="color:#3498db"><span style="font-size:18px">{formation_nom}<br />
Du {formation_date_debut} au&nbsp;{formation_date_fin}</span></span><br />
<br />
<br />
<span style="color:#3498db"><span style="font-size:18px">{objvar_object_document_societe_name}<br />
{objvar_object_document_societe_address}<br />
{objvar_object_document_societe_zip} {objvar_object_document_societe_town}<br />
Repr&eacute;sent&eacute;e par {objvar_object_signataire_intra}</span></span></div>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}

		unset($chapter);
		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 2;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = '1ère page (articles 1, 2, 3 et 4)';
		$chapter->content_text = '<br />
<br />
<br />
<span style="font-size:11px"><strong>Entre les soussign&eacute;s :</strong><br />
<br />
La soci&eacute;t&eacute;&nbsp;{mycompany_name}, dont le si&egrave;ge social se situe au&nbsp;{mycompany_address},&nbsp;{mycompany_zip}&nbsp;{mycompany_town} et en cours&nbsp;et en cours d&#39;enregistrement comme organisme de formation aupr&egrave;s de la pr&eacute;fecture Ci-apr&egrave;s d&eacute;nomm&eacute;e &laquo; l&#39;organisme &raquo; d&#39;une part,<br />
<br />
<strong>Et</strong><br />
<br />
La soci&eacute;t&eacute;&nbsp;{objvar_object_document_societe_name}, situ&eacute;e au&nbsp;{objvar_object_document_societe_address},&nbsp;{objvar_object_document_societe_zip}&nbsp;{objvar_object_document_societe_town}, repr&eacute;sent&eacute;e par&nbsp;{objvar_object_signataire_intra} d&ucirc;ment habilit&eacute;(e) &agrave; ce faire.<br />
<br />
Ci-apr&egrave;s d&eacute;nomm&eacute;e &laquo; le client &raquo; d&#39;autre part,<br />
<strong>Est conclue la convention suivante, en application des dispositions du Livre III &ndash; VI&deg; du Code du Travail portant sur l&#39;organisation de la formation professionnelle continue dans le cadre de l&#39;&eacute;ducation permanente :<br />
<br />
Article 1 - Objet</strong><br />
La convention a pour objet la r&eacute;alisation d&#39;une prestation de formation par l&#39;organisme aupr&egrave;s de collaborateurs du client.<br />
<br />
<strong>Article 2 - D&eacute;tails du stage</strong><br />
L&#39;organisme accomplit l&#39;action de formation suivante : Formation : &laquo; {formation_nom} &raquo;<br />
Objectifs :<br />
- D&eacute;veloppement de comp&eacute;tences<br />
Type d&#39;action de formation : Actions d&rsquo;adaptation au poste de travail, li&eacute;es &agrave; l&#39;&eacute;volution ou au maintien dans l&rsquo;emploi ou de d&eacute;veloppement des comp&eacute;tences des salari&eacute;s.<br />
Date: le {formation_date_debut} Dur&eacute;e : {formation_duree} heures ou {formation_duree_session} heures, r&eacute;parties de la fa&ccedil;on suivante :<br />
[!-- BEGIN THorairesSession --]Le {line_date_session} ({line_heure_debut_session} / {line_heure_fin_session})<br />
[!-- END THorairesSession --]Evaluation et sanction : Feuilles d&rsquo;&eacute;margement par demi-journ&eacute;e; Evaluation des acquis par questions / r&eacute;ponses et mises en situation; Acquisition de connaissances donnant lieu &agrave; la d&eacute;livrance d&#39;une attestation de formation.<br />
Nombre de Participants :&nbsp;{formation_nb_stagiaire_convention}<br />
Lieu : {formation_lieu} {formation_lieu_adresse},&nbsp;{formation_lieu_cp}&nbsp;{formation_lieu_ville}<br />
<br />
<strong>Article 3 - Programme et m&eacute;thode</strong><br />
Cf. annexe 1 (Programme de formation)<br />
<br />
<strong>Article 4 - Effectif form&eacute;</strong><br />
L&#39;organisme formera les participants :<br />
[!-- BEGIN TStagiairesSessionConvention --]- {line_nom}&nbsp;{line_prenom}<br />
[!-- END TStagiairesSessionConvention --]</span>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}

		unset($chapter);
		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 3;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = '2nde page (articles 5, 6, 7 et 8)';
		$chapter->content_text = '<br />
<br />
<br />
<br />
<strong>Article 5 - Dispositions financi&egrave;res</strong><br />
L&#39;organisme d&eacute;clare &ecirc;tre assujetti &agrave; la TVA au sens de l&#39;article 261-4-4&deg;-a du CGI et des articles L.900-2 et R.950-4 du code du travail. En contrepartie de cette action de formation, le client devra s&#39;acquitter des sommes suivantes :<br />
<br />
<br />
&nbsp;
<table border="0.5" cellpadding="1" cellspacing="1" style="width:440px">
	<tbody>
		<tr>
			<td style="background-color:#dddddd; text-align:center; width:40%">D&eacute;signation</td>
			<td style="background-color:#dddddd; text-align:center">TVA</td>
			<td style="background-color:#dddddd; text-align:center">PU H.T</td>
			<td style="background-color:#dddddd; text-align:center">R&eacute;duc.</td>
			<td style="background-color:#dddddd; text-align:center">Qt&eacute;</td>
			<td style="background-color:#dddddd; text-align:center">Total HT</td>
			<td style="background-color:#dddddd; text-align:center">Total TTC[!-- BEGIN TConventionFinancialLine --]</td>
		</tr>
		<tr>
			<td style="text-align:center">{line_fin_desciption}</td>
			<td style="text-align:center">{line_fin_tva_tx}</td>
			<td style="text-align:center">{line_fin_pu_ht}</td>
			<td style="text-align:center">{line_fin_discount}</td>
			<td style="text-align:center">{line_fin_qty}</td>
			<td style="text-align:center">{line_fin_amount_ht}</td>
			<td style="text-align:center">{line_fin_amount_ttc}[!-- END TConventionFinancialLine --]</td>
		</tr>
		<tr>
			<td colspan="5" style="border-color:#ffffff">&nbsp;</td>
			<td style="background-color:#dddddd">Total HT</td>
			<td>{objvar_object_conv_amount_ht}</td>
		</tr>
		<tr>
			<td colspan="5" style="border-color:#ffffff">&nbsp;</td>
			<td style="background-color:#dddddd">Total TVA</td>
			<td>{objvar_object_conv_amount_tva}</td>
		</tr>
		<tr>
			<td colspan="5" style="border-color:#ffffff">&nbsp;</td>
			<td style="background-color:#dddddd">Total TTC</td>
			<td>{objvar_object_conv_amount_ttc}</td>
		</tr>
	</tbody>
</table>
<div style="text-align:right"><span style="font-size:8px"><em>Montant exprim&eacute;s en Euros</em></span></div>
<br />
<br />
<br />
<strong>Article 6 - Conditions de r&egrave;glement</strong><br />
La facture correspondant &agrave; la somme indiqu&eacute;e ci-dessus sera adress&eacute;e, service fait, par l&#39;organisme au client qui en r&eacute;glera le montant sur le compte de l&#39;organisme.<br />
<br />
<strong>Article 7 - D&eacute;dit ou abandon</strong><br />
En application de l&#39;article L 6354-1 du code du travail, il est convenu entre les signataires de la pr&eacute;sente convention, que faute de r&eacute;alisation totale ou partielle de la prestation de formation, l&#39;organisme de formation remboursera au cocontractant les sommes qu&#39;il aura ind&ucirc;ment per&ccedil;ues de ce fait. C&#39;est-&agrave;-dire les sommes qui ne correspondront pas &agrave; la r&eacute;alisation de la prestation de formation.<br />
La non r&eacute;alisation totale de l&#39;action due &agrave; la carence du prestataire ou au renoncement &agrave; la prestation par l&#39;acheteur ne donnera pas lieu &agrave; une facturation au titre de la formation professionnelle continue.<br />
La r&eacute;alisation partielle de la prestation de formation, imputable ou non &agrave; l&#39;organisme de formation ou &agrave; son client, ne donnera lieu qu&#39;&agrave; facturation, au titre de la formation professionnelle continue, des sommes correspondantes &agrave; la r&eacute;alisation effective de la prestation.<br />
En cas de d&eacute;dit par le client &agrave; moins de 5 jours francs, avant le d&eacute;but de l&#39;action mentionn&eacute;e &agrave; l&#39;Article 1, ou d&#39;abandon en cours de formation par un ou plusieurs participants, l&#39;organisme retiendra sur le co&ucirc;t total, les sommes qu&#39;il aura r&eacute;ellement d&eacute;pens&eacute;es ou engag&eacute;es pour la r&eacute;alisation de la dite action, conform&eacute;ment aux dispositions de l&#39;Article L 920-9 du Code du Travail.<br />
<br />
<strong>Article 8 - Litiges et comp&eacute;tence d&#39;attribution</strong><br />
En cas de litige entre les deux parties, celles-ci s&#39;engagent &agrave; rechercher pr&eacute;alablement une solution amiable.En cas d&#39;&eacute;chec d&#39;une solution n&eacute;goci&eacute;e, les parties conviennent express&eacute;ment d&#39;attribuer la comp&eacute;tence exclusive aux tribunaux de la pr&eacute;fecture dont d&eacute;pend Valence.<br />
<br />
Signatures<br />
Fait &agrave; Valence, le {current_date_fr}, en 2 exemplaires originaux, dont un remis ce jour au client.<br />
Ce document comporte trois (3) pages.<br />
<br />
<br />
<br />
&nbsp;
<table cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td style="text-align:center"><strong>Pour l&#39;Organisme de formation</strong><br />
			{mycompany_name}<br />
			Repr&eacute;sent&eacute;e par&nbsp;{mycompany_managers} (*)</td>
			<td style="text-align:center"><strong>Pour le client</strong><br />
			{objvar_object_signataire_intra} (*)</td>
		</tr>
	</tbody>
</table>
<br />
<br />
&nbsp;
<div style="text-align:center"><span style="font-size:8px">(*) Faire pr&eacute;c&eacute;der la signature de la mention &laquo; lu et approuv&eacute; &raquo; apr&egrave;s avoir paraph&eacute; chaque page de la pr&eacute;sente convention.</span></div>

<div style="text-align:center">&nbsp;</div>';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

/**
 * *********** Document exemple Feuille présence vide Agefodd *************
 */
$title = $langs->trans('RefLtrAgefoddFichePresence');
if ($reinstalltemplate) {
	$result = $rfltr->fetch('', $title);
	if ($result > 0) {
		$result = $rfltr->delete($user);
		if ($result < 0) {
			setEventMessages(null, $rfltr->errors, 'errors');
		}
	} elseif ($result < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	}
}
$result = $rfltr->fetch('', $title);
if ($result < 0) {
	setEventMessages(null, $rfltr->errors, 'errors');
}
if ($result == 0) {
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'rfltr_agefodd_fiche_presence_empty';
	$rfltr->status = 0;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '<table border="0" cellpadding="1" cellspacing="1" style="width:500px">
	<tbody>
		<tr>
			<td style="width:400px"><span style="font-size:9px"><strong>{mycompany_name}</strong><br />
			{mycompany_address}<br />
			{mycompany_zip}&nbsp;{mycompany_town}<br />
			T&eacute;l. : {mycompany_phone} - Fax :&nbsp;{mycompany_fax}<br />
			Email : {mycompany_email}<br />
			Web :&nbsp;{mycompany_web}<br />
			Formation / Session : {formation_ref}/ {objvar_object_ref}<br />
			Date : {objvar_object_date_text}</span></td>
			<td>YOUR LOGO</td>
		</tr>
	</tbody>
</table>';
	$rfltr->footer = '<hr />
<div style="text-align:center"><span style="color:#95a5a6"><span style="font-size:6px">{mycompany_name} {mycompany_address} -&nbsp; {mycompany_zip}&nbsp;{mycompany_town} - T&eacute;l. : {mycompany_phone} - mail : {mycompany_email} - {mycompany_juridicalstatus} - Capital de {mycompany_capital} SIRET :&nbsp;{mycompany_idprof2}<br />
NAF-APE :&nbsp;{mycompany_idprof3} -&nbsp; N&deg; d&eacute;claration d&#39;activit&eacute; (ce num&eacute;ro ne vaut pas agr&eacute;ment de l&#39;&eacute;tat) {__[AGF_ORGANISME_NUM]__} &nbsp; - pr&eacute;fecture {__[AGF_ORGANISME_PREF]__} &nbsp;Num VA :&nbsp;{mycompany_vatnumber}</span></span><br />
<br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_custom_header = 1;
	$rfltr->use_landscape_format = 0;

	$id_rfltr = $rfltr->create($user);
	if ($id_rfltr < 0) {
		setEventMessages(null, $rfltr->errors, 'errors');
	} else {
		// Instanciation du contenu
		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Page titre';
		$chapter->content_text = '<div style="text-align:center"><br />
<br />
<span style="font-size:20px"><strong>Feuille d&#39;&eacute;margement</strong></span><br />
&nbsp;
<div>Nous, &laquo; {mycompany_name} &raquo;,demeurant {mycompany_address} {mycompany_zip}&nbsp;{mycompany_town}, repr&eacute;sent&eacute;s par {__[AGF_ORGANISME_REPRESENTANT]__} ,</div>
</div>

<div>attestons par la pr&eacute;sente de la r&eacute;alit&eacute; des informations port&eacute;es ci-dessous &agrave; votre connaissance.</div>

<div style="text-align:left"><br />
<strong>La formation</strong></div>

<table border="0.5" cellpadding="1" cellspacing="1" style="width:540px">
	<tbody>
		<tr>
			<td>
			<table border="0" cellpadding="1" cellspacing="1" style="width:540px">
				<tbody>
					<tr>
						<td>
						<div>Intitul&eacute; : <strong>{formation_nom}</strong></div>

						<div>P&eacute;riode : {objvar_object_date_text} ({formation_duree_session}h)</div>
						</td>
						<td>Lieu de formation : {formation_lieu}<br />
						{formation_lieu_adresse}&nbsp;{formation_lieu_cp}&nbsp;{formation_lieu_ville}</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;

<div style="text-align:left"><br />
<strong>Le(s)/</strong><strong>La formateur/trice(s)</strong></div>

<table border="0.5" cellpadding="1" cellspacing="1" style="width:540px">
	<tbody>
		<tr>
			<td style="width:100px">Nom et pr&eacute;nom</td>
			<td style="width:440px">
			<div style="text-align:center">Signature<br />
			<span style="font-size:8px"><em>atteste(nt) par la(les) signature(s) avoir dispens&eacute; la formation ci-dessus nomm&eacute;e.</em></span></div>

			<table border="0.5" cellpadding="1" cellspacing="1" style="width:440px">
				<tbody>
					<tr>
						<td style="text-align:left">Date:</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="text-align:left">Heure :</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			[!-- BEGIN TFormateursSession --]</td>
		</tr>
		<tr>
			<td>{line_formateur_nom} {line_formateur_prenom}</td>
			<td>
			<table border="0.5" cellpadding="1" cellspacing="1" style="width:440px">
				<tbody>
					<tr>
						<td style="height:27px">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			[!-- END TFormateursSession --]</td>
		</tr>
	</tbody>
</table>
&nbsp;

<div style="text-align:left"><br />
<strong>Le(s)/ La participant(e(s))</strong></div>

<table border="0.5" cellpadding="1" cellspacing="1" style="width:540px">
	<tbody>
		<tr>
			<td style="width:100px">Nom et pr&eacute;nom</td>
			<td style="width:440px">
			<div style="text-align:center">Signature<br />
			<span style="font-size:8px"><em>atteste(nt) par la(les) signature(s) avoir re&ccedil;u la formation ci-dessus nomm&eacute;e</em></span></div>

			<table border="0.5" cellpadding="1" cellspacing="1" style="width:440px">
				<tbody>
					<tr>
						<td style="text-align:left">Date:</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="text-align:left">Heure :</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			[!-- BEGIN TStagiairesSession --]</td>
		</tr>
		<tr>
			<td>{line_nom} {line_prenom}<br />
			({line_nom_societe})</td>
			<td>
			<table border="0.5" style="width:440px">
				<tbody>
					<tr>
						<td style="height:27px">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			[!-- END TStagiairesSession --]</td>
		</tr>
	</tbody>
</table>
&nbsp;';
		$result = $chapter->create($user);
		if ($result < 0) {
			setEventMessages(null, $chapter->errors, 'errors');
		}
	}
}

