<?php


/*
 * This is a "namespace" class (i.e. it only has static methods).
 *
 */
class RfltrTools {

	public static function setImgLinkToUrl($txt) {

		return strtr($txt, array('src="'.dol_buildpath('viewimage.php', 1) => 'src="'.dol_buildpath('viewimage.php', 2), '&amp;'=>'&'));

	}

	public static function setImgLinkToUrlWithArray($Tab) {

		foreach($Tab as $id_chapter=>&$TData) {
			$TData['content_text'] = self::setImgLinkToUrl($TData['content_text']);
		}
		return $Tab;
	}

	/**
	 * Charge le modèle référence letter choisi
	 *
	 * @param Object $object peut être une convetion pour Agefodd ou une propal, une cmd, etc ...
	 * @return array [0] => ReferenceLettersElements, [1] => $object
	 */
	public static function load_object_refletter($id_object, $id_model, $object='', $socid='', $lang_id='') {

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
			$outputlangs->load('agefoddcertificat@agefoddcertificat');
		} else {
			global $langs;
			$outputlangs=$langs;
		}

		// TODO: replace this hard-coded list with array keys of (new ReferenceLetters($db))->element_type_list.
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
			if (empty($langs_chapter) && getDolGlobalString('MAIN_MULTILANGS')) $langs_chapter = $object->thirdparty->default_lang;
			if (empty($langs_chapter)) $langs_chapter = $langs->defaultlang;
		}

		$object_chapters = new ReferencelettersChapters($db);
		$object_chapters->fetch_byrefltr($id_model, $langs_chapter);

		$content_letter = array();
		if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {
			foreach ($object_chapters->lines_chapters as $line_chapter) {
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
		//$instance_letter->ref_int = $instance_letter->getNextNumRef($object->thirdparty, $user->id, $element_type); // TODO pour l'instant on garde le même nom de pdf que fait agefodd
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
	public static function load_agefodd_object($id_object, &$object_refletter, $socid='', $obj_agefodd_convention='', $outputlangs='') {

		global $db;

		dol_include_once('/agefodd/class/agsession.class.php');
		$object = new $object_refletter->element_type_list['rfltr_agefodd_convention']['objectclass']($db);
		$object->fetch($id_object);
		$object->load_all_data_agefodd_session($object_refletter, $socid, $obj_agefodd_convention, false, $outputlangs);

		return $object;

	}

	public static function getAgefoddModelList() {

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

	public static function getAgefoddModelListDefault() {

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

	public static function getAgefoddModelListDefaultJSON() {
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

	public static function print_js_external_models($page='document') {
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
                $('.id_external_model').change(function () {
                    let model = $(this).attr('model');
                    //Dans le cas du module agefoddcertificat il faut rediriger vers agefoddcertificat_documents.backend.php
                    if (['certificateA4_trainee', 'certificatecard_trainee', 'certificateA4', 'certificatecard'].includes(model)) {
                        var path = '<?php echo dol_buildpath('/agefoddcertificat/agefoddcertificat_documents.backend.php', 1); ?>';
                    } else var path = '<?php echo $_SERVER['PHP_SELF']; ?>';
                    path += '?id='+ <?php echo GETPOST('id', 'none'); ?> +'&model='+$(this).attr('model')+'&action=create&id_external_model='+$(this).val()+'&fk_step='+<?php echo intval(GETPOST('fk_step', 'int')); ?>;
                    // On récupère l'attribut name du lien présent dans la première ligne liste_titre avant celle sur laquelle on se trouve
                    lignetitre = $(this).parent().parent();
                    while (!lignetitre.hasClass('liste_titre')) {
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
                    if (['certificateA4_trainee', 'certificatecard_trainee', 'certificateA4', 'certificatecard'].includes(model)) {
                        path += '&returnurl='+'<?php echo $_SERVER['PHP_SELF']; ?>'+'?id='+ <?php echo GETPOST('id', 'int'); ?>;
                    }
					document.location.href=path;

				});

			});

		</script>

		<?php

	}

}
