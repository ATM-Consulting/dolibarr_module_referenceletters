<?php

if(is_file('../main.inc.php'))$dir = '../';
else  if(is_file('../../../main.inc.php'))$dir = '../../../';
else $dir = '../../';

if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
	include($dir."master.inc.php");
}
elseif(!defined('INC_FROM_DOLIBARR')) {
	include($dir."main.inc.php");
} else {
	global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
}
if(!defined('DB_HOST')) {
	define('DB_HOST',$dolibarr_main_db_host);
	define('DB_NAME',$dolibarr_main_db_name);
	define('DB_USER',$dolibarr_main_db_user);
	define('DB_PASS',$dolibarr_main_db_pass);
	define('DB_DRIVER',$dolibarr_main_db_type);
}

dol_include_once('/referenceletters/class/referenceletters.class.php');
dol_include_once('/referenceletters/class/referenceletterschapters.class.php');

global $db;

$rfltr = new ReferenceLetters($db);

/***********************************/
/************* Propal **************/
/***********************************/

$title = 'EDITION_PERSO_PROPOSITION';

if($rfltr->fetch('', $title) <= 0) {
	
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'propal';
	$rfltr->status = 1;
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
	
	// Instanciation du contenu
	if(!empty($id_rfltr)) {
		
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
		
		$chapter->create($user);
		
	}
	
}


/************************************/
/************* Facture **************/
/************************************/
$title = 'EDITION_PERSO_FACTURE';
if($rfltr->fetch('', $title) <= 0) {
	
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'invoice';
	$rfltr->status = 1;
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
&nbsp;</div>
';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;
	
	$id_rfltr = $rfltr->create($user);
	
	// Instanciation du contenu
	if(!empty($id_rfltr)) {
		
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
		
		$chapter->create($user);
		
	}
	
}


/************* Commande **************/
$title = 'EDITION_PERSO_COMMANDE';
if($rfltr->fetch('', $title) <= 0) {
	
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type ='order';
	$rfltr->status = 1;
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
&nbsp;</div>
';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;
	
	$id_rfltr = $rfltr->create($user);
	
	// Instanciation du contenu
	if(!empty($id_rfltr)) {
		
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
		
		$chapter->create($user);
		
	}
	
}


/************* Contrat **************/
$title = 'EDITION_PERSO_CONTRAT';
if($rfltr->fetch('', $title) <= 0) {
	
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'contract';
	$rfltr->status = 1;
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
&nbsp;</div>
';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;
	
	$id_rfltr = $rfltr->create($user);
	
	// Instanciation du contenu
	if(!empty($id_rfltr)) {
		
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
		
		$chapter->create($user);
		
	}
	
}

/************* Document exemple Agefodd **************/
$title = 'EDITION_PERSO_AGEFODD_EXEMPLE';
if($rfltr->fetch('', $title) <= 0) {
	
	$rfltr->entity = $conf->entity;
	$rfltr->title = $title;
	$rfltr->element_type = 'rfltr_agefodd_fiche_presence';
	$rfltr->status = 1;
	$rfltr->fk_user_author = $user->id;
	$rfltr->datec = dol_now();
	$rfltr->fk_user_mod = $obj->fk_user_mod;
	$rfltr->tms = dol_now();
	$rfltr->header = '<div style="text-align:center"><br />
<span style="font-size:10px"><strong>ENTETE<br />
PERSONNALISE</strong></span><br />
&nbsp;</div>';
	$rfltr->footer = '<div style="text-align:center"><em>PIED DE PAGE PERSONNALISE</em><br />
<br />
<br />
<br />
&nbsp;</div>';
	$rfltr->use_custom_footer = 1;
	$rfltr->use_landscape_format = 0;
	
	$id_rfltr = $rfltr->create($user);
	
	// Instanciation du contenu
	if(!empty($id_rfltr)) {
		
		$chapter = new ReferenceLettersChapters($db);
		$chapter->entity = $conf->entity;
		$chapter->fk_referenceletters = $id_rfltr;
		$chapter->lang = 'fr_FR';
		$chapter->sort_order = 1;
		$chapter->fk_user_author = $chapter->fk_user_mod = $user->id;
		$chapter->title = 'Contenu';
		$chapter->content_text = 'Intitul&eacute; formation : <strong>{formation_nom}</strong><br />
Date : du <strong>{formation_date_debut}</strong> au&nbsp;<strong>{formation_date_fin}</strong><br />
Lieu :&nbsp;<strong>{objvar_object_lieu_ref_interne} -&nbsp;{objvar_object_lieu_adresse}&nbsp;{objvar_object_lieu_cp}&nbsp;{objvar_object_lieu_ville}</strong><br />
Dur&eacute;e : <strong>{formation_duree}</strong> heure(s)<br />
<br />
Tiers convention <span style="color:#FF0000">(disponible uniquement sur PDF convention)</span> :<br />
<br />
<strong>{objvar_object_document_societe_name}<br />
{objvar_object_document_societe_address}<br />
{objvar_object_document_societe_zip}&nbsp;{objvar_object_document_societe_town}<br />
Repr&eacute;sent&eacute; par&nbsp;{objvar_object_signataire_intra}/{objvar_object_signataire_inter}</strong><br />
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br />
<br />
Liste horaires :<br />
<br />
[!-- BEGIN THorairesSession --]Le&nbsp;<strong>{line_date_session} </strong>:<br />
- D&eacute;but&nbsp;<strong>{line_heure_debut_session}</strong>&nbsp;<br />
- Fin&nbsp;<strong>{line_heure_fin_session}</strong><br />
[!-- END THorairesSession --]<br />
<br />
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br />
<br />
Tableau des participants :<br />
&nbsp;<br />
&nbsp;
<table border="1" cellpadding="1" cellspacing="1">
	<tbody>
		<tr>
			<td style="text-align:center"><span style="font-size:11px">Nom - Pr&eacute;nom</span></td>
			<td style="text-align:center"><span style="font-size:11px">Structure</span></td>
			<td style="text-align:center"><span style="font-size:11px">Fonction</span></td>
			<td style="text-align:center"><span style="font-size:11px">Type financement</span><span style="font-size:11px">[!-- BEGIN&nbsp;TStagiairesSession --]</span></td>
		</tr>
		<tr>
			<td style="text-align:center"><br />
			<strong><span style="font-size:11px">{line_civilite} {line_nom}&nbsp;{line_prenom}</span></strong><br />
			&nbsp;</td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_nom_societe} ({line_code_societe})</span></strong></td>
			<td style="text-align:center"><strong><span style="font-size:11px">{line_poste}</span></strong></td>
			<td style="text-align:center"><strong>{line_type}</strong><span style="font-size:11px">[!-- END&nbsp;TStagiairesSession --]</span></td>
		</tr>
	</tbody>
</table>
<br />
<br />
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br />
<br />
Tableau des participants au sein d&#39;une convention <span style="color:#FF0000">(disponible uniquement sur PDF convention)</span> :<br />
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
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br />
<br />
Liste des formateurs :<br />
<br />
[!-- BEGIN TFormateursSession --] Nom : <strong>{line_formateur_nom}, </strong>pr&eacute;nom : <strong>{line_formateur_prenom}</strong>, statut : <strong>{line_formateur_statut}</strong><br />
[!-- END TFormateursSession --]<br />
<br />
D&eacute;tail par formateur<span style="color:#FF0000"> (disponible uniquement sur contrat formateur)</span> :<br />
<br />
<strong>{objvar_object_formateur_session_name}&nbsp;{objvar_object_formateur_session_firstname}<br />
{objvar_object_formateur_session_address}<br />
{objvar_object_formateur_session_zip}&nbsp;{objvar_object_formateur_session_town}</strong><br />
Siret : <strong>{objvar_object_formateur_session_societe_idprof2}</strong><br />
<br />
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br />
<br />
Autres :<br />
<br />
Repr&eacute;sentant Agefodd : <strong>{objvar_object_AGF_ORGANISME_REPRESENTANT}</strong>';
		
		$chapter->create($user);
		
	}
	
}