<?php

class RfltrTools {
	
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
	 * charge le modèle référence letter choisi
	 */
	static function load_object_refletter($id_object, $id_model) {
		
		global $db, $conf;
		
		dol_include_once('/referenceletters/class/referenceletters.class.php');
		dol_include_once('/referenceletters/class/referenceletterselements.class.php');
		dol_include_once('/referenceletters/class/referenceletterschapters.class.php');
		
		$object_refletter = new Referenceletters($db);
		$object_refletter->fetch($id_model);
		
		$object = self::load_agefodd_object($id_object, $object_refletter);
		
		if (empty($langs_chapter) && ! empty($conf->global->MAIN_MULTILANGS)) $langs_chapter = $object->thirdparty->default_lang;
		if (empty($langs_chapter)) $langs_chapter = $langs->defaultlang;
		$object_chapters = new ReferencelettersChapters($db);
		$result = $object_chapters->fetch_byrefltr($id_model, $langs_chapter);
		
		$content_letter = array();
		if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters) > 0) {
			
			foreach ( $object_chapters->lines_chapters as $key => $line_chapter ) {
				
				$options = array();
				if (is_array($line_chapter->options_text) && count($line_chapter->options_text) > 0) {
					foreach ( $line_chapter->options_text as $key => $option_text ) {
						$options[$key] = array (
								'use_content_option' => GETPOST('use_content_option_' . $line_chapter->id . '_' . $key),
								'text_content_option' => GETPOST('text_content_option_' . $line_chapter->id . '_' . $key)
						);
					}
				}
				
				$content_letter[$line_chapter->id] = array (
						'content_text' => $line_chapter->content_text,
						'options' => $options
				);
			}
		}
		
		// On load le modèle
		$instance_letter = new ReferenceLettersElements($db);
		$instance_letter->fetch($id_model);
		$instance_letter->srcobject=$object;
		$instance_letter->content_letter = self::setImgLinkToUrlWithArray($content_letter);
		if(empty($object->thirdparty)) $object->fetch_thirdparty();
		$element_type='rfltr_agefodd_convention';
		//$instance_letter->ref_int = $instance_letter->getNextNumRef($object->thirdparty, $user->id, $element_type); // TODo pour l'instant on garde le même nom de pdf que fait agefodd
		$instance_letter->title = $object_refletter->title;
		$instance_letter->fk_element = $object->id;
		$instance_letter->element_type = $object_refletter->element_type;
		$instance_letter->fk_referenceletters = $id_model;
		$instance_letter->outputref = '';
		$instance_letter->use_custom_header = $object_refletter->use_custom_header;
		$instance_letter->use_custom_footer = $object_refletter->use_custom_footer;
		$instance_letter->header = self::setImgLinkToUrl($object_refletter->header);
		$instance_letter->footer = self::setImgLinkToUrl($object_refletter->footer);
		
		return array($instance_letter, $object);
		
	}
	
	/**
	 * Charge l'objet Agefodd session ainsi que toutes les données associées (liste des participants, horaires)
	 */
	static function load_agefodd_object($id_object, &$object_refletter) {
		
		global $db;
		
		$object = new $object_refletter->element_type_list['rfltr_agefodd_convention']['objectclass']($db);
		$object->fetch($id_object);
		$object->load_all_data_agefodd_session();
		
		return $object;
		
	}
	
	static function getAgefoddModelList() {
		
		global $db;
		
		$sql = 'SELECT rowid, title, element_type
				FROM '.MAIN_DB_PREFIX.'referenceletters
				WHERE element_type LIKE "%agefodd%"';
		
		$resql = $db->query($sql);
		if(!empty($resql)) {
			
			$TModels=array();
			while($res = $db->fetch_object($resql)) {
				
				$TModels[$res->element_type][$res->rowid]=$res->title;
				
			}
			return $TModels;
		} else return 0;
		
	}
	
}