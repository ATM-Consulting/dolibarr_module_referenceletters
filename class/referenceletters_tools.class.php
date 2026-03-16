<?php


/*
 * This is a "namespace" class (i.e. it only has static methods).
 *
 */
class RfltrTools {

	/**
	 * Normalize historical Agefodd DocEdit element type aliases when reading
	 * persisted models. This keeps existing customer models visible in the UI
	 * without changing stored data or historical Agefodd file names.
	 *
	 * @param string $elementType
	 * @return string
	 */
	public static function normalizeAgefoddElementTypeAlias(string $elementType): string
	{
		$aliases = array(
			'rfltr_agefodd_certificat_completion_trainee' => 'rfltr_agefodd_certificate_completion_trainee',
			'rfltr_agefodd_fichepres_trainee' => 'rfltr_agefodd_fiche_presence_trainee',
		);

		return isset($aliases[$elementType]) ? $aliases[$elementType] : $elementType;
	}

	public static function setImgLinkToUrl($txt) {

		if (!is_string($txt) || $txt === '') {
			return '';
		}

		return strtr($txt, array('src="'.dol_buildpath('viewimage.php', 1) => 'src="'.dol_buildpath('viewimage.php', 2), '&amp;'=>'&'));

	}

	public static function setImgLinkToUrlWithArray($Tab) {

		foreach($Tab as $id_chapter=>&$TData) {
			$TData['content_text'] = self::setImgLinkToUrl($TData['content_text']);
		}
		return $Tab;
	}


	/**
	 * Load the selected referenceletters model.
	 *
	 * @param $id_object
	 * @param $id_model
	 * @param $object Can be an Agefodd convention, proposal, order, and so on.
	 * @param $socid
	 * @param $lang_id
	 * @param $fk_training
	 * @return array [0] => ReferenceLettersElements, [1] => $object
	 */

	public static function load_object_refletter($id_object, $id_model, $object = null, $socid = '', $lang_id = '', $fk_training = 0) {

		global $db, $conf;

		dol_include_once('/referenceletters/class/referenceletters.class.php');
		dol_include_once('/referenceletters/class/referenceletterselements.class.php');
		dol_include_once('/referenceletters/class/referenceletterschapters.class.php');

		$object_refletter = new Referenceletters($db);
		$res_fetch = $object_refletter->fetch($id_model);
		if (!empty($object_refletter->element_type)) {
			$object_refletter->element_type = self::normalizeAgefoddElementTypeAlias((string) $object_refletter->element_type);
		}

		//
		if(empty($res_fetch)) {
			$object_refletter->fetch_all('', '', 0, 0, array('t.default_doc'=>1));

			if (!empty($object_refletter->lines[key($object_refletter->lines)]->id)) {
				$id_rfltr = $object_refletter->lines[key($object_refletter->lines)]->id;
			}


			if(!empty($id_rfltr)) { // A default model exists, load it.
				$object_refletter->fetch($id_rfltr);
				if (!empty($object_refletter->element_type)) {
					$object_refletter->element_type = self::normalizeAgefoddElementTypeAlias((string) $object_refletter->element_type);
				}
			}else{
				// Otherwise load the first model in the list.
				$object_refletter->fetch_all('DESC', 'rowid', 0, 0, array('t.element_type'=>"invoice"));
				$id_rfltr = $object_refletter->lines[key($object_refletter->lines)]->id;

				if(!empty($id_rfltr)) { // A fallback model exists, load it.
					$object_refletter->fetch($id_rfltr);
					if (!empty($object_refletter->element_type)) {
						$object_refletter->element_type = self::normalizeAgefoddElementTypeAlias((string) $object_refletter->element_type);
					}
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

		if(is_object($object) && in_array(get_class($object), $arrayObjectClass))  {

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
			else $object = self::load_agefodd_object($id_object, $object_refletter, $socid, is_object($object) ? $object : null, $outputlangs, $fk_training);

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

		// Load the selected model.
		$instance_letter = new ReferenceLettersElements($db);
		$instance_letter->fetch($id_model);
		$instance_letter->srcobject=$object;
		$instance_letter->content_letter = self::setImgLinkToUrlWithArray($content_letter);
		if(is_object($object) && empty($object->thirdparty)) $object->fetch_thirdparty();
		//$instance_letter->ref_int = $instance_letter->getNextNumRef($object->thirdparty, $user->id, $element_type); // TODO keep the historical Agefodd PDF file name for now.
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
	 * Load the Agefodd session object with all associated data (participants, schedules).
	 * @param $id_object
	 * @param $object_refletter
	 * @param $socid
	 * @param $obj_agefodd_convention
	 * @param $outputlangs
	 * @param $fk_training
	 * @return mixed
	 */
	public static function load_agefodd_object($id_object, &$object_refletter, $socid = 0, $obj_agefodd_convention = null, $outputlangs = null, $fk_training = 0) {

		global $db;
		if ($fk_training == 0) {
			dol_include_once('/agefodd/class/agsession.class.php');
			if (
				$object_refletter->element_type === 'rfltr_agefodd_convention'
				&& !is_object($obj_agefodd_convention)
				&& !empty($socid)
			) {
				dol_include_once('/agefodd/class/agefodd_convention.class.php');
				$convention = new Agefodd_convention($db);
				$result = $convention->fetch((int) $id_object, (int) $socid);
				if ($result > 0 && !empty($convention->id)) {
					$obj_agefodd_convention = $convention;
				}
			}
			$object = new $object_refletter->element_type_list['rfltr_agefodd_convention']['objectclass']($db);
			$object->fetch($id_object);
		}else{
			dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');
			$object = new $object_refletter->element_type_list['rfltr_agefodd_formation']['objectclass']($db);
			$object->fetch($fk_training);
		}
		// Load object data. The method name differs depending on the Agefodd version.
		$agefoddInfoLoader = 'load_all_data_agefodd';
		if (! method_exists($object, $agefoddInfoLoader)) {
			$agefoddInfoLoader = 'load_all_data_agefodd_session';
		}
		$object->$agefoddInfoLoader($object_refletter, (int) $socid, $obj_agefodd_convention, false, $outputlangs);

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
				$elementType = self::normalizeAgefoddElementTypeAlias((string) $res->element_type);
				$TModels[$elementType][$res->rowid]=$res->title;

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
				$res->element_type = self::normalizeAgefoddElementTypeAlias((string) $res->element_type);

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

				// Show the list of available models.
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
                // Select the model and generate the document.
                $('.id_external_model').change(function () {
                    let model = $(this).attr('model');
                    //Dans le cas du module agefoddcertificat il faut rediriger vers agefoddcertificat_documents.backend.php
                    if (['certificateA4_trainee', 'certificatecard_trainee', 'certificateA4', 'certificatecard'].includes(model)) {
                        var path = '<?php echo dol_escape_js(dol_buildpath('/agefoddcertificat/agefoddcertificat_documents.backend.php', 1)); ?>';
                    } else var path = '<?php echo dol_escape_js((string) $_SERVER['PHP_SELF']); ?>';
                    path += '?id='+ <?php echo (int) GETPOST('id', 'int'); ?> +'&model='+$(this).attr('model')+'&action=create&id_external_model='+$(this).val()+'&fk_step='+<?php echo (int) GETPOST('fk_step', 'int'); ?>;
                    var selectSocId = $(this).attr('socid');
                    var trainerCell = $(this).closest('td.trainerid');
                    var trainerId = trainerCell.length ? trainerCell.attr('trainerid') : '';
                    // Read the name attribute from the nearest previous liste_titre anchor.
                    lignetitre = $(this).parent().parent();
                    while (!lignetitre.hasClass('liste_titre')) {
                        lignetitre = lignetitre.prev();
                    }
					var sessiontrainerid = lignetitre.find('a').attr('name');
					<?php

						if($page === 'document') {
							?>
									if ((model == 'mission_trainer' || model == 'contrat_trainer') && trainerId) {
										path = path + '&sessiontrainerid=' + trainerId;
									} else if(typeof sessiontrainerid != 'undefined' && typeof selectSocId != 'undefined' && sessiontrainerid == 'trainerid'+selectSocId) {
										path = path + '&sessiontrainerid=' + selectSocId;
									} else {
										if($(this).attr('model') == 'fiche_pedago_modules' || $(this).attr('model') == 'fiche_pedago'){
											let idform = $(this).attr('data-idform');
											if (typeof idform !== 'undefined' && idform !== '') {
												path = path + '&idform=' + idform;
											}
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
										if (typeof selectSocId !== 'undefined' && selectSocId !== '') {
											path = path + '&cour=' + courrier + '&socid=' + selectSocId;
										} else {
											path = path + '&cour=' + courrier;
										}
									} else {
										if (typeof selectSocId !== 'undefined' && selectSocId !== '') {
											path = path + '&socid=' + selectSocId;
										}
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
