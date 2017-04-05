<?php
	
	require '../config.php';
	require_once '../class/referenceletters.class.php';
	require_once '../class/referenceletterschapters.class.php';
	require_once '../class/html.formreferenceletters.class.php';
	require_once '../class/referenceletterselements.class.php';
	
	
	require_once '../lib/referenceletters.lib.php';
	dol_include_once('/compta/facture/class/facture.class.php');
	
	$refltrelement_type=GETPOST('refltrelement_type','alpha');
	$idletter=GETPOST('idletter','int');
	
	$formrefleter = new FormReferenceLetters($db);
	
	llxHeader();
	$head = referenceletterMassPrepareHead();
	dol_fiche_head($head, 'card', $langs->trans('Module103258Name'), 0, dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	echo '<form name="addreferenceletters" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	echo '<input type="hidden" name="action" value="choice">';
	
	echo '<table class="border" width="100%">';
	
	echo '<tr>';
	echo '<td class="fieldrequired"  width="20%">';
	echo $langs->trans('RefLtrElement');
	echo '</td>';
	echo '<td>';
	echo $formrefleter->selectElementType($refltrelement_type, 'refltrelement_type',0,array('invoice'));
	
	if($refltrelement_type) print $formrefleter->selectReferenceletters($idletter, 'idletter', $refltrelement_type);
	
	echo '<input class="butAction" type="submit" name="ok_type" value="'.$langs->trans('Ok').'" />';
		
	echo '</td>';
	echo '</tr>';
	
	echo '</table>';
	
	if(!empty($refltrelement_type) && $idletter) {
		
		_show_ref_letter($idletter);
		
		if($refltrelement_type == 'invoice') {
			
			_list_invoice();
			
		}
		
	}
	
	
	echo '</form>';
	
	dol_fiche_end();
	
	llxFooter();

function _show_ref_letter($idletter) {
	
	global $db, $langs, $conf, $user;
	
	$object_chapters = new ReferencelettersChapters($db);
	$object_element = new ReferenceLettersElements($db);
	$object_refletter = new Referenceletters($db);
	
	$langs_chapter = $langs->defaultlang;
	$result = $object_chapters->fetch_byrefltr($idletter, $langs_chapter);
	
	print '<table class="border" width="100%" id="ref-letter">';
	if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {
		
		print '<tr>';
		print '<td  width="20%">';
		print $langs->trans('RefLtrTitle');
		print '</td>';
		print '<td>';
		print '<input type="text" class="flat" name="title_instance" id="title_instance" size="30" value="' . GETPOST('title_instance') . '">';
		print '</td>';
		print '</tr>';
		
		print '<tr>';
		print '<td  width="20%">';
		print $langs->trans('RefLtrREF_LETTER_OUTPUTREFLET');
		print '</td>';
		print '<td>';
		print '<input type="checkbox" class="flat" name="outputref" '.(!empty($conf->global->REF_LETTER_OUTPUTREFLET)?'checked="checked"':'').' id="outputref" value="1">';
		print '</td>';
		print '</tr>';
		
		foreach ( $object_chapters->lines_chapters as $key => $line_chapter ) {
			if ($line_chapter->content_text == '@breakpage@') {
				print '<tr><td colspan="2" style="text-align:center;font-weight:bold">';
				print '<input type="hidden" name="content_text_' . $line_chapter->id . '" value="' . $line_chapter->content_text . '"/>';
				print $langs->trans('RefLtrPageBreak');
				print '</td></tr>';
			}elseif ($line_chapter->content_text == '@breakpagenohead@') {
				print '<tr><td colspan="2" style="text-align:center;font-weight:bold">';
				print '<input type="hidden" name="content_text_' . $line_chapter->id . '" value="' . $line_chapter->content_text . '"/>';
				print $langs->trans('RefLtrAddPageBreakWithoutHeader');
				print '</td></tr>';
			} else {
				print '<tr style="'.(!empty($line_chapter->readonly)?'display:none':'').'">';
				print '<td  width="20%">';
				print $langs->trans('RefLtrText');
				print '</td>';
				print '<td>';
				
				require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
				$nbrows = ROWS_2;
				if (! empty($conf->global->MAIN_INPUT_DESC_HEIGHT))
					$nbrows = $conf->global->MAIN_INPUT_DESC_HEIGHT;
					$enable = (isset($conf->global->FCKEDITOR_ENABLE_SOCIETE) ? $conf->global->FCKEDITOR_ENABLE_SOCIETE : 0);
					$doleditor = new DolEditor('content_text_' . $line_chapter->id, $line_chapter->content_text, '', 150, 'dolibarr_notes_encoded', '', false, true, $enable, $nbrows, 70);
					$doleditor->Create();
					print '</td>';
					print '</tr>';
					
					print '<tr style="'.(!empty($line_chapter->readonly)?'display:none':'').'">';
					print '<td  width="20%">';
					print $langs->trans('RefLtrOption');
					print '</td>';
					print '<td>';
					if (is_array($line_chapter->options_text) && count($line_chapter->options_text) > 0) {
						foreach ( $line_chapter->options_text as $key => $option_text ) {
							if (! empty($option_text)) {
								print '<input type="checkbox" checked="checked" name="use_content_option_' . $line_chapter->id . '_' . $key . '" value="1"><input type="texte class="flat" size="20" name="text_content_option_' . $line_chapter->id . '_' . $key . '" value="' . $option_text . '" ><br>';
							}
						}
					}
					print '</td>';
					print '</tr>';
			}
		}
		
		
	}
	print '</table>';
	
}
	
function _list_invoice() {
	global $conf,$db,$user,$langs,$refltrelement_type,$idletter;
	
	//J'ai essayé, mais le copier/coller était trop dur
	$l=new Listview($db, 'listInvoice');
	
	$sql="SELECT f.rowid,f.type, f.facnumber, f.datef,f.date_lim_reglement, s.nom,s.town,s.zip, '' as 'action'
		FROM ".MAIN_DB_PREFIX."facture as f LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (f.fk_soc = s.rowid)
		WHERE f.entity IN (".getEntity('invoice',1).") AND f.fk_statut = 1 ";
	//var_dump($sql);
	echo $l->render($sql,array(
		
			'title'=>array(
					'facnumber'=>$langs->trans("Ref"),
					'datef'=>$langs->trans("DateInvoice"),
					'date_lim_reglement'=>$langs->trans("DateDue"),
					'nom'=>$langs->trans("ThirdParty"),
					'town'=>$langs->trans("Town"),
					'zip'=>$langs->trans("Zip"),
					'action'=>$langs->trans("Action").' <input type="checkbox" value="1" id="check-all" checked="checked" >',
			)
			,'eval'=>array(
					'facnumber'=>'_get_link_invoice(@rowid@)'
					,'action'=>'_get_check(@rowid@)'
			)
			,'type'=>array(
					'datef'=>'date'
					,'date_lim_reglement'=>'date'
			)
			,'search'=>array(
					
					'date_lim_reglement'=>array('search_type'=>'calendars')
					
			)
			,'list'=>array(
					'param_url'=>'refltrelement_type=invoice'
			)
			,'limit'=>array( 
					'nbLine'=>(GETPOST('limit','int') ? GETPOST('limit') : $conf->liste_limit )
			)
			
	));
	//var_dump($l->db);
	
	if($refltrelement_type) {
		
		echo '<div class="tabsAction">';
		echo '<input type="button" class="butAction" name="bt_generate" value="'.$langs->trans('Generate').'"> ';
		
		echo '</div>';
		
	}
	
	
	?>
	<script type="text/javascript">
	$("#check-all").change(function() {

		$("input[rel=invoicetogen]").prop("checked", $(this).prop("checked"));
		
	});

	$('input[name=bt_generate]').click(function() {

		var data = { justinformme:1, element_type: "<?php echo $refltrelement_type ?>", action: "buildoc", idletter:"<?php echo $idletter?>" };

		$('#ref-letter input,#ref-letter textarea').each(function(i,item) {
			$item = $(item);

			if($item.attr('type') == 'checkbox') {
				if($item.prop('checked')) data[$item.attr('name')]= $item.val();
				else null;
			}
			else{
				data[$item.attr('name')]=$item.val();
			}
			
			
			
		});
		
		var $togen = $('input[rel=invoicetogen]');
		var nb = $togen.length;
		var cpt = 0;
		
		var $bar = $('<div id="progressbar"></div>').progressbar({
		      max : nb
		      ,value : 0
	    });

		var $div = $('<div />');
		$div.append($bar);
		$div.append('<div class="info"></div>');
		
		$div.dialog({
			'title':"<?php echo $langs->transnoentities('GenerationInProgress') ?>"
			,'modal':true
			
		});

		
		$togen.each(function(i,item) {
			var $item = $(item);
			
			var $td = $item.closest('td');

			data["id"] = $item.val();

			$td.html('...');

			$.ajax({
				url:"instance.php"
				,data:data
				,dataType:'html'
				,method:'post'
			}).done(function(res) {

				cpt++;

				$bar.progressbar( "value", cpt );
				$div.find('.info').html(cpt+' / '+nb);
				
				if(res == 1) {

					$td.html('<?php echo img_picto('','on'); ?>');

					if(cpt == nb){

						$div.find('.info').html('<?php echo  $langs->transnoentities('AllDocumentsGenerated') ?>');
						
					}

				}
				else {
					$td.html('<?php echo img_picto('','off'); ?> '+res);
					
				}

			});
			
			
		});
		
	});
	
	</script>
	<?php 
	
}

function _get_check($id) {
	
	return '<input type="checkbox" value="'.$id.'" name="TInvoice[]" rel="invoicetogen" checked="checked" >';
	
}

function _get_link_invoice($id) {

	global $conf,$db,$user,$langs;
	
	$facture = new Facture($db);
	$facture->fetch($id); // TODO improve perf
	
	return $facture->getNomUrl(1,'',200,0,'',0,1)
		.' '.img_picto('','object_referenceletters.png@referenceletters').' <a href="'.dol_buildpath('/referenceletters/referenceletters/instance.php',1).'?id='.$id.'&element_type=invoice">'.$langs->trans('RefLtrLetters').'</a>' ;
}