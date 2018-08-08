<?php
/*
 * Copyright (C) 2016  Florian HENRY <florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file class/actions_referenceletters.class.php
 * \ingroup referenceletters
 * \brief This file is an example hook overload class file
 * Put some comments here
 */

/**
 * Class ActionsReferenceLetters
 */
class ActionsReferenceLetters
{
	/**
	 *
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array ();

	/**
	 *
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 *
	 * @var array Errors
	 */
	public $errors = array ();

	/**
	 * Constructor
	 */
	public function __construct($db) {
		$this->db = $db;
		$this->error = 0;
		$this->errors = array ();
		$this->resprints = null;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param array() $parameters Hook metadatas (context, etc...)
	 * @param CommonObject &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return int < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function afterPDFCreation($parameters, &$object, &$action, $hookmanager) {
		global $langs, $conf, $user;

		$error = 0; // Error counter
		dol_syslog("Hook '" . get_class($this) . "' for action '" . __METHOD__ . "' launched by " . __FILE__);
		if (in_array('referencelettersinstacecard', explode(':', $parameters['context']))) {
			if (! empty($conf->global->REF_LETTER_CREATEEVENT)) {
				$instance_letter = $parameters['instance_letter'];
				dol_syslog("Hook '" . get_class($this) . " id=" . $instance_letter->id);
				// var_dump($instance_letter);
				$langs->load('referenceletters@referenceletters');
				$now = dol_now();

				dol_include_once('/comm/action/class/actioncomm.class.php');
				$actioncomm = new ActionComm($this->db);
				$actioncomm->type_code = 'AC_LTR_DOC';
				if ($conf->global->REF_LETTER_TYPEEVENTNAME == 'normal') {
					$actioncomm->label = $langs->trans('RefLtrNewLetters') . ' ' . $instance_letter->ref_int;
				} else {
					// find refletter inforamtion
					dol_include_once('/referenceletters/class/referenceletters.class.php');
					$object_refletter = new Referenceletters($this->db);
					$result = $object_refletter->fetch($instance_letter->fk_referenceletters);
					if ($ret < 0) {
						$this->error = $object_refletter->error;
						$this->errors[] = $object_refletter->errors;

						dol_syslog(get_class($this) . $this->error, LOG_ERR);
						return - 1;
					}
					if ($instance_letter->element_type == 'thirdparty') {
						$actioncomm->label = $object_refletter->title . '-' . $instance_letter->srcobject->nom;
					} else {
						$actioncomm->label = $object_refletter->title . '-' . $instance_letter->srcobject->thirdparty->name;
					}

				}
				if(is_array($instance_letter->content_letter)) {
					$first_value = reset($instance_letter->content_letter);
				}
				$actioncomm->note = $first_value['content_text'];
				$actioncomm->datep = $now;
				$actioncomm->datef = $now;
				$actioncomm->durationp = 0;
				$actioncomm->punctual = 1;
				// Not applicable
				$actioncomm->percentage = - 1;
				// $actioncomm->contact = $contactforaction;
				if ($instance_letter->element_type == 'thirdparty') {
					$actioncomm->socid = $instance_letter->srcobject->id;
				} else {
					$actioncomm->socid = $instance_letter->srcobject->thirdparty->id;
				}
				$actioncomm->author = $user; // User saving action
				$actioncomm->userdone = $user; // User doing action
				$actioncomm->fk_element = $instance_letter->id;
				$actioncomm->elementtype = 'referenceletters_' . $instance_letter->element;
				$actioncomm->userownerid = $user->id;
				$ret = $actioncomm->create($user); // User qui saisit l'action
				if ($ret < 0) {
					$error = "Failed to insert : " . $actioncomm->error . " ";
					$this->error = $error;
					$this->errors[] = $error;

					dol_syslog(get_class($this) . $this->error, LOG_ERR);
					return - 1;
				} else {

					if (! empty($conf->global->REF_LETTER_EVTCOPYFILE)) {
						dol_include_once('/core/lib/files.lib.php');

						$objectref = dol_sanitizeFileName($instance_letter->ref_int);
						$srcdir = $conf->referenceletters->dir_output . "/".$instance_letter->element_type."/" . $objectref;
						$srcfile = $srcdir . '/' . $objectref . ".pdf";
						$destdir = $conf->agenda->dir_output . '/' . $ret;
						$destfile = $destdir . '/' . $objectref . ".pdf";
						if (dol_mkdir($destdir) >= 0) {
							$result = dol_copy($srcfile, $destfile);
							if ($result < 0) {
								$error = $langs->trans('RefLtrErrorCopyFile');
								$this->error = $error;
								$this->errors[] = $error;

								dol_syslog(get_class($this) . $this->error, LOG_ERR);
								return - 1;
							}
						}
					}
				}
			}

			// $this->results = array('myreturn' => $myvalue);
			// $this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
	}

	function doActions($parameters, &$object, &$action, $hookmanager) {

		global $db, $conf, $user, $langs;

		if(in_array($parameters['currentcontext'], array('propalcard', 'ordercard', 'contractcard', 'invoicecard', 'supplier_proposalcard', 'ordersuppliercard'))) {

			if($action === 'builddoc') {

				$model = GETPOST('model');

				// Récupération de l'id du modèle
				if(strpos($model, 'rfltr_') !== false) {

					dol_include_once('/referenceletters/core/modules/referenceletters/modules_referenceletters.php');
					dol_include_once('/referenceletters/class/referenceletters_tools.class.php');

					// Récupération l'id du modèle sélectionné
					$id_model = (int)explode('rfltr_', $model)[1];

					// Création et chargement d'une nouvelle instance de modèle
					$instance_rfltr = RfltrTools::load_object_refletter($object->id, $id_model, $object, '', GETPOST('lang_id'))[0];
					if(empty($instance_rfltr->ref_int)) $instance_rfltr->ref_int = $instance_rfltr->getNextNumRef($object->thirdparty, $user->id, $instance_rfltr->element_type);
					$instance_rfltr->create($user);

					if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang=GETPOST('lang_id','aZ09');
					if (! empty($newlang))
					{
						$outputlangs = new Translate("",$conf);
						$outputlangs->setDefaultLang($newlang);
					}

					// Création du PDF
					$result = referenceletters_pdf_create($db, $object, $instance_rfltr, $outputlangs, $instance_rfltr->element_type);

					if($result > 0) {

						// Renommage du fichier pour le mettre dans le bon répertoire pour qu'il apparaîsse dans la liste des fichiers joints sur la fiche de chaque élément
						$objectref = dol_sanitizeFileName($instance_rfltr->ref_int);
						$dir = $conf->referenceletters->dir_output . '/' .$instance_rfltr->element_type . '/' . $objectref;
						$file = $dir . '/' . $objectref . ".pdf";

						$objectref = dol_sanitizeFileName($object->ref);
						$classname = get_class($object);
						if($classname === 'CommandeFournisseur') $classname = 'supplier_order';
						$dir_dest = $conf->{strtolower($classname)}->dir_output;
						if (empty($dir_dest)) {
							dol_include_once('/referenceletters/class/referenceletters.class.php');
							$refstatic = new ReferenceLetters($this->db);
							if (array_key_exists('dir_output', $refstatic->element_type_list[$instance_rfltr->element_type])) {
								$dir_dest = $refstatic->element_type_list[$instance_rfltr->element_type]['dir_output'];
							}
						}
						if (empty($dir_dest)) {
							setEventMessage($langs->trans('RefLtrCannotCopyFile'),'errors');
						} else {
							$dir_dest .= '/' . $objectref;
							if (! file_exists($dir_dest))
							{
								dol_mkdir($dir_dest);
							}
							$file_dest = $dir_dest . '/' . $objectref . '.pdf';
							$test=$conf->{strtolower(get_class($object))}->dir_output;

							dol_copy($file, $file_dest);
						}

						// Header sur la même page pour annuler le traitement standard de génération de PDF
						$field_id = 'id';
						if(get_class($object) === 'Facture') $field_id = 'facid';
						header('location: '.$_SERVER['PHP_SELF'].'?id='.GETPOST($field_id)); exit;

					}

				}
			}

		}

	}

	function formBuilddocOptions($parameters, &$object, &$action, $hookmanager) {

		global $db;

		/***** Permet d'afficher les modèles disponibles dans la liste de génération de la fiche de chaque élément *****/

		// 1 - On récupère les modèles disponibles pour ce type de document
		$element = $object->element;
		if($element === 'facture') $element = 'invoice';
		if($element === 'commande') $element = 'order';
		if($element === 'contrat') $element = 'contract';

		$sql = 'SELECT rowid, title FROM '.MAIN_DB_PREFIX.'referenceletters WHERE element_type = "'.$element.'" AND entity IN (' . getEntity('referenceletters') . ') AND status=1';
		$resql = $db->query($sql);
		while($res = $db->fetch_object($resql)) $TModelsID[] = array('id'=>$res->rowid, 'title'=>$res->title);

		if(empty($TModelsID)) return 0;

		// 2 - On ajoute les données au selectmodels
		?>
		<script>

			$(document).ready(function(){
				var tab = new Array();
				<?php foreach($TModelsID as &$TData) { ?>
					var option = new Option('<?php print $db->escape($TData['title']); ?>', 'rfltr_<?php print $TData['id']; ?>');
					tab.push(option);
					$("#model").append(tab);
				<?php } ?>
			});

		</script>
		<?php
	}

}
