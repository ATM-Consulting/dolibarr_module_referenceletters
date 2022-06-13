<?php

class ReferenceLettersTools {

	static function setImgLinkToUrl($txt) {

		return strtr($txt, array('src="'.dol_buildpath('viewimage.php', 1) => 'src="'.dol_buildpath('viewimage.php', 2), '&amp;'=>'&'));

	}

	static function setImgLinkToUrlWithArray($Tab) {

		foreach($Tab as $id_chapter=>&$TData) {
			$TData['content_text'] = self::setImgLinkToUrl($TData['content_text']);
		}
		return $Tab;
	}

	/**
	 * @param $obj peut être une convetion pour Agefodd ou une propal, une cmd, etc ...
	 * charge le modèle référence letter choisi
	 */
	static function load_object_refletter($id_object, $id_model, $obj='', $socid='', $lang_id='') {

		global $db, $conf;

		dol_include_once('/referenceletters/class/referenceletters.class.php');
		dol_include_once('/referenceletters/class/referenceletterselements.class.php');
		dol_include_once('/referenceletters/class/referenceletterschapters.class.php');

		$object_refletter = new Referenceletters($db);
		$res_fetch = $object_refletter->fetch($id_model);

		//
		if(empty($res_fetch)) {
			$object_refletter->fetch_all('', '', 0, 0, array('t.default_doc'=>1));
			$id_rfltr = $object_refletter->lines[key($object_refletter->lines)]->id;

			if(!empty($id_rfltr)) { // Il existe un modèle par défaut, on le charge
				$object_refletter->fetch($id_rfltr);
			}else{
				// sinon on prend le premier dans la liste.
				$object_refletter->fetch_all('DESC', 'rowid', 0, 0, array('t.element_type'=>"invoice"));
				$id_rfltr = $object_refletter->lines[key($object_refletter->lines)]->id;

				if(!empty($id_rfltr)) { // Il existe  ...  on le charge
					$object_refletter->fetch($id_rfltr);
				}
			}
		}

		if (! empty($lang_id)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($lang_id);
			$outputlangs->load('main');
			$outputlangs->load('agefodd@agefodd');
		} else {
			global $langs;
			$outputlangs=$langs;
		}

		$arrayObjectClass = array('Facture',
			'Commande',
			'Propal',
			'Contrat',
			'Societe',
			'Contact',
			'SupplierProposal',
			'CommandeFournisseur',
			'FactureFournisseur',
			'Expedition',
			'Fichinter');
		if(is_object($obj) && in_array(get_class($obj), $arrayObjectClass))  {
			$object = &$obj;
			if(empty($object->thirdparty) && is_callable(array($object, 'fetch_thirdparty'))) {
				$object->fetch_thirdparty();
			}

			if(get_class($object) === 'Contrat') {
				$lines = $object->getLinesArray();
				if (!empty($lines)) {
					$object->lines_active = array();

					foreach ($lines as $line) {
						if ($line->statut == 4) $object->lines_active[] = $line;
					}
				}
			}
		}
		else $object = self::load_agefodd_object($id_object, $object_refletter, $socid, $obj, $outputlangs);

		if (!empty($lang_id)) $langs_chapter = $outputlangs->defaultlang;
		else {
			if (empty($langs_chapter) && ! empty($conf->global->MAIN_MULTILANGS)) $langs_chapter = $object->thirdparty->default_lang;
			if (empty($langs_chapter)) $langs_chapter = $langs->defaultlang;
		}

		$object_chapters = new ReferencelettersChapters($db);
		$object_chapters->fetch_byrefltr($id_model, $langs_chapter);

		$content_letter = array();
		if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {
			foreach ($object_chapters->lines_chapters as $key => $line_chapter) {
				$options = array();
				if (is_array($line_chapter->options_text) && count($line_chapter->options_text) > 0) {
					foreach ($line_chapter->options_text as $key => $option_text) {
						$options[$key] = array (
							'use_content_option' => GETPOST('use_content_option_' . $line_chapter->id . '_' . $key, 'none'),
							'text_content_option' => GETPOST('text_content_option_' . $line_chapter->id . '_' . $key, 'none')
						);
					}
				}

				$content_letter[$line_chapter->id] = array (
					'content_text' => $line_chapter->content_text,
					'options' => $options,
					'same_page' => $line_chapter->same_page
				);
			}
		}

		// On load le modèle
		$instance_letter = new ReferenceLettersElements($db);
		$instance_letter->fetch($id_model);
		$instance_letter->srcobject=$object;
		$instance_letter->content_letter = self::setImgLinkToUrlWithArray($content_letter);
		if(is_object($object) && empty($object->thirdparty)) $object->fetch_thirdparty();
		//$instance_letter->ref = $instance_letter->getNextNumRef($object->thirdparty, $user->id, $element_type); // TODo pour l'instant on garde le même nom de pdf que fait agefodd
		$instance_letter->title = $object_refletter->title;
		$instance_letter->fk_element = $object->id;
		$instance_letter->element_type = $object_refletter->element_type;
		$instance_letter->fk_referenceletters = $id_model;
		$instance_letter->outputref = '';
		$instance_letter->use_custom_header = $object_refletter->use_custom_header;
		$instance_letter->use_custom_footer = $object_refletter->use_custom_footer;
		$instance_letter->header = self::setImgLinkToUrl($object_refletter->header);
		$instance_letter->footer = self::setImgLinkToUrl($object_refletter->footer);
		$instance_letter->use_landscape_format= $object_refletter->use_landscape_format;
		$instance_letter->title_referenceletters = $object_refletter->title;

		return array($instance_letter, $object);

	}

	/**
	 * Charge l'objet Agefodd session ainsi que toutes les données associées (liste des participants, horaires)
	 */
	static function load_agefodd_object($id_object, &$object_refletter, $socid='', $obj_agefodd_convention='', $outputlangs='') {

		global $db;

		dol_include_once('/agefodd/class/agsession.class.php');
		$object = new $object_refletter->element_type_list['rfltr_agefodd_convention']['objectclass']($db);
		$object->fetch($id_object);
		$object->load_all_data_agefodd_session($object_refletter, $socid, $obj_agefodd_convention, false, $outputlangs);

		return $object;

	}

	static function getAgefoddModelList() {

		global $db;

		$sql = 'SELECT rowid, title, element_type , default_doc
				FROM '.MAIN_DB_PREFIX.'referenceletters
				WHERE element_type LIKE "%agefodd%"
				AND entity IN (' . getEntity('referenceletters') . ")
				AND status=1";

		$resql = $db->query($sql);
		if(!empty($resql)) {

			$TModels=array();
			while($res = $db->fetch_object($resql)) {

				$TModels[$res->element_type][$res->rowid]=$res->title;

			}
			return $TModels;
		} else return 0;

	}

	static function getAgefoddModelListDefault() {

		global $db;
		$sql = 'SELECT rowid, title, element_type , default_doc
				FROM '.MAIN_DB_PREFIX.'referenceletters
				WHERE element_type LIKE "%agefodd%"
				AND entity IN (' . getEntity('referenceletters') . ")
				AND status=1";

		$resql = $db->query($sql);
		if(!empty($resql)) {

			$TModels=array();
			while($res = $db->fetch_object($resql)) {

				$TModels[]=$res;

			}
			return $TModels;
		} else return 0;

	}

	static function getAgefoddModelListDefaultJSON() {
		$TDefaultModel=array();
		$TModel = self::getAgefoddModelListDefault();
		if (is_array($TModel) && count($TModel)>0) {
			foreach($TModel as $line) {
				if (!empty($line->default_doc) && !array_key_exists($line->element_type, $TDefaultModel) && $line->element_type!=='rfltr_agefodd_convention')  {
					$TDefaultModel[str_replace('rfltr_agefodd_', '', $line->element_type)]=$line->rowid;
				}
			}
		}
		return json_encode($TDefaultModel);


	}

	static function print_js_external_models($page='document') {
		?>

		<script type="text/javascript">

			$(document).ready(function() {

				var defaultdoc=JSON.parse('<?php print self::getAgefoddModelListDefaultJSON();?>');
				console.log(defaultdoc);
				$("a[name^='builddoc_']").each(function () {
					if (defaultdoc[$(this).attr("name").split("__")[1]]) {
						var _href = $(this).attr("href");
						$(this).attr("href", _href + '&id_external_model='+defaultdoc[$(this).attr("name").split("__")[1]]);
					}

				});

				// Affichage de la liste des modèles disponibles
				$(".btn_show_external_model_list").click(function() {

					var class_to_show = '.' + $(this).attr('class_to_show');
					var val_link = $(this).text();

					if(val_link == '+') {
						// $(class_to_show).show();
						$(this).parent().find('#id_external_model').show();
						$(this).html('-');
					} else if(val_link == '-') {
						// $(class_to_show).hide();
						$(this).parent().find('#id_external_model').hide();
						$(this).html('+');
					}

				});
				// Sélection du modèle et génération du document
				$(".id_external_model").change(function() {

					var path = '<?php echo $_SERVER['PHP_SELF']; ?>' + '?id=' + <?php echo GETPOST('id', 'none'); ?> + '&model=' + $(this).attr('model') + '&action=create&id_external_model=' + $(this).val();
					// On récupère l'attribut name du lien présent dans la première ligne liste_titre avant celle sur laquelle on se trouve
					lignetitre = $(this).parent().parent();
					while(!lignetitre.hasClass('liste_titre')) {
						lignetitre = lignetitre.prev();
					}
					var sessiontrainerid = lignetitre.find('a').attr('name');
					<?php

					if($page === 'document') {
					?>
					if(typeof sessiontrainerid != 'undefined' && sessiontrainerid == 'trainerid'+$(this).attr('socid')) {
						path = path + '&sessiontrainerid=' + $(this).attr('socid');
					} else {
						if($(this).attr('model') == 'fiche_pedago_modules' || $(this).attr('model') == 'fiche_pedago'){
							adresse = $(this).prev().prev().attr('href');
							idform = adresse.substr(adresse.indexOf('idform=')+7);
							path = path + '&idform=' + idform;
						} else if($(this).attr('model') == 'courrier'){
							adresse = $(this).prev().prev().attr('href');
							goodlink = $(this).prev().prev().attr('name');
							if (typeof goodlink ==='undefined') {
								adresse = $(this).prev().prev().prev().attr('href');
							}
							cour = adresse.substr(adresse.indexOf('&cour=')+6);
							if(cour.indexOf('&') !== -1){
								courrier = cour.substr(0, cour.indexOf('&'));
							} else {
								courrier = cour.substr(0);
							}
							path = path + '&cour=' + courrier + '&socid=' + $(this).attr('socid');
						} else {
							path = path + '&socid=' + $(this).attr('socid');
						}
					}
					<?php
						} elseif($page === 'document_by_trainee') {
						?>path = path + '&sessiontraineeid=' + $(this).attr('socid');<?php
					}

					?>

					document.location.href=path;

				});

			});

		</script>

		<?php

	}

	/**
	 *    Return a HTML area with the reference of object and a navigation bar for a business object
	 *    To add a particular filter on select, you must set $object->next_prev_filter to SQL criteria.
	 *
	 *    @param	object	$object			Object to show
	 *    @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
	 *    @param	string	$morehtml  		More html content to output just before the nav bar
	 *    @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
	 *    @param	string	$fieldid   		Name of field id into database to use for select next and previous (we make the select max and min on this field)
	 *    @param	string	$fieldref   	Name of field ref of object (object->ref) to show or 'none' to not show ref.
	 *    @param	string	$morehtmlref  	More html to show after ref
	 *    @param	string	$moreparam  	More param to add in nav link url.
	 *	  @param	int		$nodbprefix		Do not include DB prefix to forge table name
	 *	  @param	string	$morehtmlleft	More html code to show before ref
	 *	  @param	string	$morehtmlstatus	More html code to show under navigation arrows (status place)
	 *	  @param	string	$morehtmlright	More html code to show after ref
	 * 	  @return	string    				Portion HTML with ref + navigation buttons
	 */
	static function showrefnav($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $morehtmlright = '')
	{
		global $langs, $conf;

		$ret = '';
		if (empty($fieldid))
			$fieldid = 'rowid';
		if (empty($fieldref))
			$fieldref = 'ref';

		// print "paramid=$paramid,morehtml=$morehtml,shownav=$shownav,$fieldid,$fieldref,$morehtmlref,$moreparam";
		$object->load_previous_next_ref_custom((isset($object->next_prev_filter) ? $object->next_prev_filter : ''), $fieldid);
		$previous_ref = $object->ref_previous ? '<a data-role="button" data-icon="arrow-l" data-iconpos="left" href="' . $_SERVER["PHP_SELF"] . '?' . $paramid . '=' . urlencode($object->ref_previous) . $moreparam . '">' . (empty($conf->dol_use_jmobile) ? img_picto($langs->trans("Previous"), 'previous.png') : '&nbsp;') . '</a>' : '';
		$next_ref = $object->ref_next ? '<a data-role="button" data-icon="arrow-r" data-iconpos="right" href="' . $_SERVER["PHP_SELF"] . '?' . $paramid . '=' . urlencode($object->ref_next) . $moreparam . '">' . (empty($conf->dol_use_jmobile) ? img_picto($langs->trans("Next"), 'next.png') : '&nbsp;') . '</a>' : '';

		// print "xx".$previous_ref."x".$next_ref;
		if ($previous_ref || $next_ref || $morehtml) {
			$ret .= '<table class="nobordernopadding" width="100%"><tr class="nobordernopadding"><td class="nobordernopadding">';
		}

		$ret .= $object->$fieldref;
		if ($morehtmlref) {
			$ret .= ' ' . $morehtmlref;
		}

		if ($morehtml) {
			$ret .= '</td><td class="nobordernopadding" align="right">' . $morehtml;
		}
		if ($shownav && ($previous_ref || $next_ref)) {
			$ret .= '</td><td class="nobordernopadding" align="center" width="20">' . $previous_ref . '</td>';
			$ret .= '<td class="nobordernopadding" align="center" width="20">' . $next_ref;
		}
		if ($previous_ref || $next_ref || $morehtml) {
			$ret .= '</td></tr></table>';
		}
		return $ret;
	}

	/**
	 * Return a Select Element
	 *
	 * @param strint $selected
	 * @param string $htmlname
	 * @return select HTML
	 */
	static function selectElementType($selected='',$htmlname='element_type',$showempty=0, $in_array=array()) {
		global $langs, $db;

		require_once 'referenceletters.class.php';

		$refletter = new Referenceletters($db);
		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$select_elemnt .= '<option value=""></option>';
		}
		foreach($refletter->element_type_list as $element_type=>$array_data) {
			$langs->load($array_data['trans']);

			if(!empty($in_array)) {

				if(!in_array($element_type, $in_array)) continue;

			}

			if ($selected==$element_type) {
				$option_selected=' selected="selected" ';
			}else {
				$option_selected='';
			}

			$module = '';
			if(strpos($element_type, 'rfltr_agefodd_') !== false) $module = $langs->trans('Module103000Name') . ' - ';

			$select_elemnt .= '<option value="' . $element_type . '" '.$option_selected.'>' . $module . $langs->trans($array_data['title']) . '</option>';
		}

		$select_elemnt .= '</select>';
		return $select_elemnt;
	}

	/**
	 * Return a Select Element
	 *
	 * @param string $selected
	 * @param string $htmlname
	 * @return select HTML
	 */
	static function selectStatus($selected='',$htmlname='element_type',$showempty=1) {
		global $langs, $db;

		$status_array=array();

		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$status_array[-1]='';
		}
		require_once 'referenceletters.class.php';
		$refletter = new Referenceletters($db);

		$status_array+=$refletter->TStatus;

		foreach($status_array as $key=>$val) {
			if ($selected==$key) {
				$option_selected=' selected="selected" ';
			}else {
				$option_selected='';
			}

			$select_elemnt .= '<option value="' . $key . '" '.$option_selected.'>' . $langs->trans($val) . '</option>';
		}

		$select_elemnt .= '</select>';
		return $select_elemnt;
	}

	/**
	 * Return a Select Element
	 *
	 * @param strint $selected
	 * @param string $htmlname
	 * @return select HTML
	 */
	static function selectDefaultDoc($selected='',$htmlname='defaultdoc',$showempty=1) {
		global $langs, $db;

		$status_array=array();

		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$status_array[-1]='';
		}
		require_once 'referenceletters.class.php';
		$refletter = new Referenceletters($db);

		$status_array+=$refletter->TDefaultDoc;

		foreach($status_array as $key=>$val) {
			if ($selected==$key) {
				$option_selected=' selected="selected" ';
			}else {
				$option_selected='';
			}

			$select_elemnt .= '<option value="' . $key . '" '.$option_selected.'>' . $langs->trans($val) . '</option>';
		}

		$select_elemnt .= '</select>';
		return $select_elemnt;
	}

	/**
	 * Return a Select Element
	 *
	 * @param strint $selected
	 * @param string $htmlname
	 * @return select HTML
	 */
	static function selectReferenceletters($selected='',$htmlname='refletter',$element_type='',$showempty=0) {
		global $langs, $db;

		require_once 'referenceletters.class.php';

		$refletter = new Referenceletters($db);
		$filter=array('t.element_type'=>$element_type, 't.status'=>1);
		$TReferenceLetters = $refletter->fetchAll('ASC','t.title',0,0, array('customsql'=>"t.element_type='".$element_type."' AND t.status=1"));
		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$select_elemnt .= '<option value=""></option>';
		}

		foreach($TReferenceLetters as $line) {

			if ($selected==$line->id) {
				$option_selected=' selected="selected" ';
			}else {
				$option_selected='';
			}

			$select_elemnt .= '<option value="' . $line->id . '" '.$option_selected.'>' . $line->title . '</option>';
		}

		$select_elemnt .= '</select>';
		return $select_elemnt;
	}

	/**
	 * Helper display tag selector
	 *
	 * @param User $user user
	 * @param CommonObject $reflettersobject reference letters model
	 * @return string HTML to print
	 */

	static function displaySubtitutionKey($user,$reflettersobject) {
		global $langs,$db;

		$form = new Form($db);
		$html=$form->getSubtitutionKeyTable($user,$reflettersobject);

		return $form->textwithpicto($langs->trans("RefLtrDisplayTag"), $html, 1, 'help', '', 0, 2, 'refltertags');
	}

	/**
	 *
	 *
	 * @param User $user
	 * @param ReferenceLetters $reflettersobject
	 */
	static function displaySubtitutionKeyAdvanced($user, $reflettersobject) {
		global $langs;

		print '<div id="subtitutionkey" style="display: none;" >';

		print '<div class="search-filter-wrap"  >';
		print '<i class="fa fa-search"></i>';
		print '<input type="text" id="item-filter" class="search-filter" data-target="" value="" placeholder="'.$langs->trans('Search').'" ';
		print '<span id="filter-count-wrap" >'.$langs->trans('Result').': <span id="filter-count" ></span></span>';
		print '</div>';

		$subs_array=$reflettersobject->getSubtitutionKey($user);

		$html='<div id="accordion-refltertags" >';

		if (is_array($subs_array) && count($subs_array)>0) {
			foreach($subs_array as $block=>$data) {
				$html .= '<h3 class="accordion-refltertags-title">' . $block . '<span class="h3-element-count badge" data-element-count=""></span></h3>';

				$html .= '<div class="accordion-refltertags-body" >';
				$html .= '<table>';
				$html .= '<tr class="liste_titre">';
				$html .= '<th>'.$langs->trans('Description').'</th>';
				$html .= '<th width="50px">'.$langs->trans('RefLtrTag').'</th>';
				$html .= '<th>'.$langs->trans('Value').'</th>';
				$html .= '</tr>';
				if (is_array($data) && count($data) > 0) {
					$var = true;
					foreach ($data as $key => $value) {
						$html .= '<tr class="oddeven searchable search-match">';
						$html .= '    <td class="referenceletter-subtitutionkey-desc">';
						if (!empty($langs->tab_translate['reflettershortcode_' . $key])) {   // Translation is available
							$html .= '        <span class="referenceletter-subtitutionkey classfortooltip" title="' . $langs->trans('ClickToAddOnEditor') . '" data-shortcode="{' . $key . '}" >';
							$html .= $langs->trans('reflettershortcode_' . $key);
							$html .= '</span>';
						}
						$html .= '    </td>';
						$html .= '    <td class="referenceletter-subtitutionkey-col">';
						$html .= '        <span class="referenceletter-subtitutionkey classfortooltip" title="' . $langs->trans('ClickToAddOnEditor') . '"  data-shortcode="{' . $key . '}"  >{' . $key . '}</span>';
						$html .= '    </td>';
						$html .= '    <td>';
						$html .= dol_escape_htmltag($value);// to prevent js execution like redirect...
						$html .= '    </td>';
						$html .= '</tr>';
					}
				}
				$html .= '</table>';
				$html .= '</div>';
			}

			// Generate traduction for dev only
			/*print '<pre>';
			foreach($subs_array as $block=>$data) {
				print '#' . $block."\n";
				if (is_array($data) && count($data) > 0) {
					$var = true;
					foreach ($data as $key => $value) {
						print 'reflettershortcode_' . $key."=\n";
					}
				}
			}
			print '</pre>';*/
		}

		$html.='</div>';
		$html.= '</div>';
		$html.=  '<script>
                $( function() {

                    $("#accordion-refltertags" ).accordion({
                            collapsible: true,
                            heightStyle: "content",
                            navigation: true ,
                            active: false
                    });

                    $( "#subtitutionkey" ).dialog({
                      title: "'.$langs->transnoentities('RefSubtitutionTable').'",
                      width: $( document ).width() * 0.9,
                      modal: true,
                      autoOpen: false,
                      maxHeight: $( window ).height() * 0.9,
                      height: $( window ).height() * 0.9
                    });

                    $(".docedit_shortcode").click(function() {

                         // open dialog and add target key
                         $( "#subtitutionkey" ).data("target", $(this).data("target"));
                         $( "#subtitutionkey" ).dialog( "open" );

                         // Focus on search input
                         $("#item-filter").focus();
                    });

                     $(".docedit_setbool").click(function() {

						//Get the Chapter Id
						var chapter=$(this);

						$.ajax({
						  method: "POST",
						  url: "'.dol_buildpath('referenceletters/script/interface.php',1).'",
						  dataType: "json",
						  data: { set: "setfield" , id: chapter.data("id") , field: chapter.data("field"), value: chapter.data("valtoset") }
						})
						.done(function( data ) {
						    if(data.status){
						        $.jnotify("'.dol_escape_js($langs->transnoentities('Saved')).'");
						        if (chapter.children("span").first().hasClass(\'fa-toggle-on\')) {
						            chapter.children("span").first().removeClass(\'fa-toggle-on\').addClass(\'fa-toggle-off\');
						            chapter.data("valtoset",1);
						        } else {
						            chapter.children("span").first().removeClass(\'fa-toggle-off\').addClass(\'fa-toggle-on\');
						            chapter.data("valtoset",0);
						        }
						    }else{
						        $.jnotify("'.dol_escape_js($langs->transnoentities('Error')).' : " + data.message, "error", 3000);
						    }
						});
                    });

                   $(".referenceletter-subtitutionkey").click(function(btnshortcode) {

                        var shortcodeTarget = $($("#subtitutionkey").data("target"));

                        if(CKEDITOR.instances[shortcodeTarget.attr("id")] != undefined)
                        {
                            var evt = CKEDITOR.instances[shortcodeTarget.attr("id")];

                            try {
                                evt.insertHtml( $(this).data("shortcode")  );

                                $.jnotify("'.dol_escape_js($langs->transnoentities('RefLtrShortCodeAdded')).' : " + $(this).data("shortcode"),"3000","false",{ remove: function (){}})  ;

                            }catch (err) {
                                console.log("Unable to copy ckeditor not ready ?.");
                                $.jnotify("'.dol_escape_js($langs->transnoentities('RefLtrShortCodeAddError')).'","error","true",{ remove: function (){}})  ;

                            }

                            $( "#subtitutionkey" ).dialog( "close" );
                        }
                        else{
                            console.log("shortcodeTarget notfound");
                        }
                   });

                   $( document ).on("keyup", "#item-filter", function () {

                        var filter = $(this).val(), count = 0;
                        $("#subtitutionkey tr.searchable").each(function () {

                            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                                $(this).removeClass("search-match").hide();
                            } else {
                                $(this).addClass("search-match").show();
                                count++;
                            }
                        });

                        $("#filter-count").text(count);

                        updateBadgeCount();
                    });


                   updateBadgeCount = function () {
                       $("#subtitutionkey .h3-element-count").each(function(i, item) {
                            let divId = $(item).parent().attr("id");
                            let nb = $("div[aria-labelledby="+divId+"]").find("tr.searchable.search-match").length;
                            item.dataset.elementCount = nb;

                            if (nb > 0) $(this).addClass("badge-primary").removeClass("badge-secondary");
                            else $(this).addClass("badge-secondary").removeClass("badge-primary");
                        });

                   }
                   updateBadgeCount();

                });
                </script>

                <style>.ui-dialog { z-index: 1000 !important ;}</style>
                ';
		return $html;
	}

	static function renderChapterHTML(ReferenceLettersChapters $chapter, $mode='view') {
		global $langs;

		$urlToken = '';
		if (function_exists('newToken')) $urlToken = "&token=".newToken();

		if ($chapter->content_text=='@breakpagenohead@')
		{
			$out = '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$chapter->id.'" >';
			$out.= $langs->trans('RefLtrAddPageBreakWithoutHeader');
			if ($mode=='view') {
				$out.= '<a href="'.dol_buildpath('/referenceletters/referenceletterschapters_card.php',1).'?id=' . $chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		elseif ($chapter->content_text=='@breakpage@')
		{
			$out = '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$chapter->id.'" >';
			$out.= $langs->trans('RefLtrPageBreak');
			if ($mode=='view') {
				$out.= '<a href="' . dol_buildpath('/referenceletters/referenceletterschapters_card.php', 1) . '?id=' . $chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		elseif (strpos($chapter->content_text,'@pdfdoc')===0) {
			$documentModel=str_replace('@','',str_replace('pdfdoc_','',$chapter->content_text));
			$out = '<div class="sortable sortabledisable docedit_pdfmodel"  data-sortable-chapter="'.$chapter->id.'" >';
			$out .= img_pdf($langs->trans('RefLtrPDFDoc')) . $langs->trans('RefLtrPDFDoc').' ('.$documentModel.')';
			if ($mode == 'view') {
				$out .= '<a href="' . dol_buildpath('/referenceletters/referenceletterschapters_card.php', 1) . '?id=' . $chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		$out .=  '</div>';
		return $out;
	}

	static function _print_docedit_footer($object){
		global $langs, $conf, $user;

		print '<div class="sortable sortableHelper docedit_document_body docedit_document_bloc"></div>';

		print '<div class="docedit_document_footer docedit_document_bloc">';


		// Button and infos
		print '<div class="docedit_infos docedit_infos_left"><div class="docedit_sticky">';
		if ($user->rights->referenceletters->write) {
			print '<a  href="'.dol_buildpath('/referenceletters/footer.php',1).'?id=' . $object->id .'">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
		}
		print '</div></div><!-- END docedit_infos -->';


		print '<div class="docedit_infos docedit_infos_top">';
		print '<span class="docedit_title_type" >'. $langs->trans('RefLtrFooterTab').'</span><span class="docedit_title" ></span>';
		print '</div>';


		if($object->use_custom_footer){
			print $object->footer;
		}
		else{
			// TODO : add default footer
		}

		print '</div><!-- END docedit_document_footer -->';
	}


	static function _print_docedit_header($object, $norepeat=false){
		global $langs, $conf, $user;



		print '<div class="docedit_document_head docedit_document_bloc">';

		// Button and infos
		print '<div class="docedit_infos docedit_infos_left"><div class="docedit_sticky">';
		if ($user->rights->referenceletters->write) {
			print '<a  href="'.dol_buildpath('/referenceletters/header.php',1).'?id=' . $object->id .'">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
		}

		print '</div></div><!-- END docedit_infos -->';


		print '<div class="docedit_infos docedit_infos_top">';

		print '<span class="docedit_title_type" >'. $langs->trans('RefLtrHeaderTab').'</span><span class="docedit_title" ></span>';
		print '</div>';
		if(!$norepeat)
		{
			if($object->use_custom_header){
				print $object->header;
			}
			else{
				//var_dump($object->element_type);
				// Add default header
				if($object->element_type == 'invoice'){
					print $conf->global->INVOICE_FREE_TEXT;
				}
				elseif($object->element_type == 'propal'){
					print $conf->global->PROPOSAL_FREE_TEXT;
				}
				elseif($object->element_type == 'order'){
					print $conf->global->ORDER_FREE_TEXT;
				}
				elseif($object->element_type == 'contract'){
					print $conf->global->CONTRACT_FREE_TEXT;
				}
				elseif($object->element_type == 'order_supplier'){
					print $conf->global->SUPPLIER_ORDER_FREE_TEXT;
				}
				elseif($object->element_type == 'supplier_proposal'){
					print $conf->global->SUPPLIER_PROPOSAL_FREE_TEXT;
				}
			}
		}

		print '</div><!-- END docedit_document_head -->';
	}

}
