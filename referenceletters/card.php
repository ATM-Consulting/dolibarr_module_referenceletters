<?php
/* References letters
 * Copyright (C) 2014  HENRY Florian  florian.henry@open-concept.pro
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
 *	\file		index.php
*	\ingroup	referenceletters
*	\brief		index page
*/

// Load environment

require '../config.php';
require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$confirm = GETPOST('confirm', 'alpha');

$refltrtitle=GETPOST('refltrtitle', 'alpha');
$refltrelement_type=GETPOST('refltrelement_type', 'alpha');
$refltruse_landscape_format=GETPOST('refltruse_landscape_format', 'alpha');
$refltrdefault_doc=GETPOST('refltrdefault_doc', 'alpha');


// Access control
// Restrict access to users with invoice reading permissions
restrictedArea($user, 'referenceletters');

// Load translation files required by the page
$langs->load("referenceletters@referenceletters");

$object = new ReferenceLetters($db);
$object_chapters = new ReferenceLettersChapters($db);
if(!empty($id)) {
	$result=$object->fetch($id);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	}
	$result=$object_chapters->fetch_byrefltr($id);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	}
}


$extrafields = new ExtraFields($db);

$error = 0;


// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'referenceletterscard'
));


/*
 * Actions
*/

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

if ($action == "add") {
	$object->title = $refltrtitle;
	$object->element_type = $refltrelement_type;
	$object->use_landscape_format = $refltruse_landscape_format;
	$object->default_doc = $refltrdefault_doc;

	$extrafields->setOptionalsFromPost($extralabels, $object);

	$result = $object->create($user);
	if ($result < 0) {
		$action = 'create';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->referenceletters->delete) {
	$result = $object->delete($user);
	if ($result < 0) {
		setEventMessage($object->errors, 'errors');
	} else {
		header('Location:' . dol_buildpath('/referenceletters/referenceletters/list.php', 1));
	}
} elseif ($action == "clone") {

	$object_clone = new ReferenceLetters($db);
	$result = $object_clone->createFromClone($object->id);
	if ($result < 0) {
		setEventMessage($object_clone->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $result);
	}
} elseif($action=='setrefltrtitle') {
	$object->title = $refltrtitle;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltrtitle';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
}elseif($action=='setrefltrelement') {
	$object->element_type = $refltrelement_type;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltrelement';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif($action=='setrefltruse_landscape_format' && isset($_REQUEST['modify'])) {

	$object->use_landscape_format = $refltruse_landscape_format;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltruse_landscape_format';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif($action=='setrefltrdefault_doc' && isset($_REQUEST['modify'])) {

	$object->default_doc = $refltrdefault_doc;
	$result = $object->update($user);
	if ($result < 0) {
		$action = 'editrefltrdefault_doc';
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif($action=='addbreakpage') {
	$object_chapters_breakpage = new ReferenceLettersChapters($db);
	$object_chapters_breakpage->fk_referenceletters=$object->id;
	$object_chapters_breakpage->title ='';
	$object_chapters_breakpage->content_text = '@breakpage@';
	$object_chapters_breakpage->sort_order=$object_chapters_breakpage->findMaxSortOrder();
	$object_chapters_breakpage->lang=$object_chapters_breakpage->findPreviewsLanguage();
	$result = $object_chapters_breakpage->create($user);
	if ($result < 0) {
		$action = 'addbreakpage';
		setEventMessage($object_chapters_breakpage->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
} elseif ($action=='addbreakpagewithoutheader') {
	$object_chapters_breakpage = new ReferenceLettersChapters($db);
	$object_chapters_breakpage->fk_referenceletters=$object->id;
	$object_chapters_breakpage->title ='';
	$object_chapters_breakpage->content_text = '@breakpagenohead@';
	$object_chapters_breakpage->sort_order=$object_chapters_breakpage->findMaxSortOrder();
	$object_chapters_breakpage->lang=$object_chapters_breakpage->findPreviewsLanguage();
	$result = $object_chapters_breakpage->create($user);
	if ($result < 0) {
		$action = 'addbreakpagewithoutheader';
		setEventMessage($object_chapters_breakpage->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
}elseif ($action == "changestatus") {

	if (empty($object->status)) {
		$object->status=ReferenceLetters::STATUS_VALIDATED;
	} else {
		$object->status=ReferenceLetters::STATUS_DRAFT;
	}
	$result = $object->update($user);
	if ($result < 0) {
		setEventMessage($object->error, 'errors');
	} else {
		header('Location:' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
	}
}

/*
 * VIEW
*/
$title = $langs->trans('Module103258Name');
$arrayofcss = array('referenceletters/css/view_documents.css');
$arrayofjs = array();
llxHeader('',$title, '', '', 0, 0, $arrayofjs, $arrayofcss);

$form = new Form($db);
$formrefleter = new FormReferenceLetters($db);

$now = dol_now();
// Add new proposal
if ($action == 'create' && $user->rights->referenceletters->write) {
	print_fiche_titre($langs->trans("RefLtrCreate"), '', dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	print '<form name="addreferenceletters" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';

	print '<table class="border" width="100%">';

	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrElement');
	print '</td>';
	print '<td>';
	print $formrefleter->selectElementType($refltrelement_type, 'refltrelement_type');
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td class="fieldrequired"  width="20%">';
	print $langs->trans('RefLtrTitle');
	print '</td>';
	print '<td>';
	print '<input type="text" name="refltrtitle" size="20" value="' . $refltrtitle . '"/>';
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td width="20%">';
	print $langs->trans('RefLtrUseLandscapeFormat');
	print '</td>';
	print '<td>';
	print $form->selectyesno('refltruse_landscape_format', $refltruse_landscape_format, 1);
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td width="20%">';
	print $langs->trans('RefLtrDefaultDoc');
	print '</td>';
	print '<td>';
	print $form->selectyesno('refltrdefault_doc', $refltrdefault_doc, 1);
	print '</td>';
	print '</tr>';

	// Other attributes
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook

	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'edit');
	}

	print '</table>';

	print '<center>';
	print '<input type="submit" class="button" value="' . $langs->trans("Create") . '">';
	print '&nbsp;<input type="button" class="button" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
	print '</center>';

	print '</form>';
} else {

	/*
	 * Show object in view mode
	*/
	$head = referenceletterPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans('Module103258Name'), 0, dol_buildpath('/referenceletters/img/object_referenceletters.png', 1), 1);

	// Confirm form
	$formconfirm = '';
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('RefLtrDelete'), $langs->trans('RefLtrConfirmDelete'), 'confirm_delete', '', 0, 1);
	}

	if (empty($formconfirm)) {
		$parameters = array();
		$formconfirm = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	}
	if (!empty($formconfirm)) {
		print $formconfirm;
	}

	$linkback = '<a href="' . dol_buildpath('/referenceletters/referenceletters/list.php', 1) . '">' . $langs->trans("BackToList") . '</a>';
	print $linkback;

	print '<div style="vertical-align: middle; margin-bottom: 10px">';
	print '<div class="pagination"><ul>';
	print '<li class="noborder litext">'.$linkback.'</li>';
	print '</ul></div>';
	print '<div class="inline-block floatleft valignmiddle refid refidpadding">';
	print $langs->trans('RefLtrTitle').' : '. $form->editfieldval("RefLtrTitle",'refltrtitle',$object->title,$object,$user->rights->referenceletters->write);
	if ($action !== 'editrefltrtitle') print '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltrtitle&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>'.'<BR>';
	print '<div class="refidno">';
	if ($action=='editrefltrelement') {
		print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
		print '<input type="hidden" name="action" value="setrefltrelement">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';
		print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
		print '<tr><td>';
		print $langs->trans('RefLtrElement').$formrefleter->selectElementType($object->element_type, 'refltrelement_type');
		print '</td>';

		print '<td align="left">';
		print '<input type="submit" class="button'.(empty($notabletag)?'':' ').'" name="modify" value="'.$langs->trans("Modify").'">';
		print'<input type="submit" class="button'.(empty($notabletag)?'':' ').'" name="cancel" value="'.$langs->trans("Cancel").'">';
		print'</td>';

		print '</tr></table>'."\n";
		print '</form>'."\n";
		print '<br>'."\n";
	} else {
		print $langs->trans('RefLtrElement').' : '. $object->displayElement(). '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltrelement&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>'.'<BR>';
	}
	print $langs->trans('RefLtrUseLandscapeFormat') . ' : ';
	if ($action !== 'editrefltruse_landscape_format') print '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltruse_landscape_format&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>';
	print '&nbsp;' . $form->editfieldval("RefLtrUseLandscapeFormat",'refltruse_landscape_format',$object->use_landscape_format,$object,$user->rights->referenceletters->write, 'select;1:'.$langs->trans('Yes').',0:'.$langs->trans('No')) . '<br>';
	print $langs->trans('RefLtrDefaultDoc') . ' : ';
	if ($action !== 'editrefltrdefault_doc') print '&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?action=editrefltrdefault_doc&id=' . $object->id .'">' . img_picto('edit', 'edit') . '</a>';
	print '&nbsp;' . $form->editfieldval("RefLtrDefaultDoc",'refltrdefault_doc',$object->default_doc,$object,$user->rights->referenceletters->write, 'select;1:'.$langs->trans($object->TDefaultDoc[ReferenceLetters::DEFAULTDOC_YES]).',0:'.$langs->trans($object->TDefaultDoc[ReferenceLetters::DEFAULTDOC_NO])) . '<br>';

	// Other attributes
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook

	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields);
	}

	print '</div>';
	print '</div>';
	print '</div>';

	if (is_array($object_chapters->lines_chapters) && count($object_chapters->lines_chapters)>0) {

		$pageCurrentNum = 1;
		
		print '<div class="underbanner clearboth"></div>';
		print '<div  id="sortablezone" class="docedit_docboard">';
		
		print '<div class="info">'.$langs->trans('doceditinfo_viewlimit').'</div>';
		$classOrientation = "portrait";
		if(!empty($object->use_landscape_format))
		{
		    $classOrientation = "landscape";
		}
		
		print '<div id="page_'.$pageCurrentNum.'"  class="docedit_document '.$classOrientation.'" data-page="'.$pageCurrentNum.'" >';
		
		_print_docedit_header($object);
		$nbChapterInPage = 0;
		foreach ($object_chapters->lines_chapters as $line_chapter) {
		    
		    
		    if ($line_chapter->content_text=='@breakpage@' || $line_chapter->content_text=='@breakpagenohead@') {
		        
		        // reset nb chapters in page
		        $nbChapterInPage = 0;
		        
		        // first close page
		        _print_docedit_footer($object);
		        print '</div><!-- END docedit_document -->';
		        
		        // add break page element
		        print '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$line_chapter->id.'" >';
		        if ($line_chapter->content_text=='@breakpagenohead@')
		        {
		            print $langs->trans('RefLtrAddPageBreakWithoutHeader');
		            print '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
		            $norepeat=true;
		        }
		        else // if $line_chapter->content_text=='@breakpage@' 
		        {
		            print $langs->trans('RefLtrPageBreak');
		            print '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
		            $norepeat=false;
		        }
		        print '</div>';
		        
		        // start new page
		        $pageCurrentNum++;
		        print '<div id="page_'.$pageCurrentNum.'"  class="docedit_document '.$classOrientation.'" data-page="'.$pageCurrentNum.'" >';
		        _print_docedit_header($object, $norepeat);
		        
		    } else {
		        $nbChapterInPage++;
		        print '<div id="chapter_'.$line_chapter->id.'" class="sortable docedit_document_body docedit_document_bloc" data-sortable-chapter="'.$line_chapter->id.'">';
		        
		        // Button and infos
		        print '<div class="docedit_infos docedit_infos_left">';
		        
		       
		        if ($user->rights->referenceletters->write) {
		            if(!empty($conf->global->DOCEDIT_CHAPTERS_SORTABLE)){
		                print '<span class="docedit_infos_icon handle" ><span class="fa fa-th marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('MoveChapter').'" title="'.$langs->trans('MoveChapter').'"></span></span>';
		            }
		            
		            if(!empty($conf->global->DOCEDIT_CHAPTERS_INLINE_EDITION)){ 
		                print '<span class="docedit_infos_icon docedit_save" data-target="#chapter_body_text_'.$line_chapter->id.'"  ><span class="fa fa-save marginleftonly valignmiddle" style=" color: #444;" alt="'.$langs->trans('Save').'" title="'.$langs->trans('Save').'"></span></span>';
		            }
		            
		            print '<a  href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=edit">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
		            print '<a class="docedit_infos_icon" href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $line_chapter->id . '&action=delete">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
		            
		        }
		        
		        print '</div><!-- END docedit_infos -->';
		        
		        
		        print '<div class="docedit_infos docedit_infos_top">';
		        print '<span class="docedit_title_type" >';
		        print $langs->trans('RefLtrTitle');
		        if (! empty($conf->global->MAIN_MULTILANGS))
		        {
		            $s=picto_from_langcode($line_chapter->lang);
		            print ($s?' '.$s:'');
		        }
		        print ' : </span>';
		        //print $langs->trans('RefLtrTitle');
		        print '<span class="docedit_title" >'. $line_chapter->title.'</span>';
		        
		        print '</div><!-- END docedit_infos_top -->';
		       
		        
		  
		        //print $langs->trans('RefLtrText');
		        $editInline = '';
		        if(!empty($conf->global->DOCEDIT_CHAPTERS_INLINE_EDITION)){ $editInline = ' contenteditable="true" '; }
		        
		        print '<div  class="docedit_document_body_text" '.$editInline.' id="chapter_body_text_'.$line_chapter->id.'" data-id="'.$line_chapter->id.'"  data-type="chapter_text" >';
		        print $line_chapter->content_text;
		        print '</div><!-- END docedit_document_body_text -->';
		        
		        
		        
		        if (is_array($line_chapter->options_text) && count($line_chapter->options_text)>0) {
		            
		            print '<div class="docedit_document_option">';
		            
		            
		            print $langs->trans('RefLtrOption');
		            
		            
		            if(!empty($line_chapter->readonly))
		            {
		                print ' <span class="docedit_document_option_read_only" >'.$langs->trans('RefLtrReadOnly').'</span>';
		            }
		            
		            
		            foreach($line_chapter->options_text as $key=>$option_text) {
		                print '<label class="docedit_label" ><input type="checkbox" readonly="readonly" disabled="disabled" name="'.$key.'"> '.$option_text.'</label>';
		            }
		            print '</div><!-- END docedit_document_option -->';
		        } 
		       
		        
		       
		        
		        
		        print '</div><!-- end docedit_document_body -->';
		    }
		}
		
		_print_docedit_footer($object);
		
		print '</div><!-- END docedit_document -->';
		
		print '</div><!-- end docedit_docboard -->';
		
		if(!empty($conf->global->DOCEDIT_CHAPTERS_SORTABLE))
		{
		    // experimental, not finish
		    print '<script>$( function() {';
		    

	        print '
	        $( ".docedit_document" ).sortable({
                cursor: "move",
	            placeholder: "ui-state-highlight",
	            connectWith: ".docedit_document",
	            items: ".sortable:not(.sortabledisable)",
	            handle: ".handle",
                stop: function (event, ui) {
						$(".slide-placeholder-animator").remove();

						console.log("onstop");
						console.log(getOrder());

						$.ajax({
		    	            data: {
								object_id: '.$object->id.',
						    	roworder: getOrder(),
                                set: "sortChapter"
							},
		    	            type: "POST",
                            dataType: "json",
		    	            url: "'.dol_buildpath('referenceletters/script/interface.php',1).'",
		    	            success: function(data) {
               	                console.log(data);
                                if(data.saved > 0){
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Saved')).'");
                                }else{
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Error')).' : " + data.message, "error", 3000);
                                }
		    	            }
		    	        });
		    	  },

                revert: 150,
                start: function(e, ui){
                    
                    placeholderHeight = ui.item.outerHeight();
                    ui.placeholder.height(placeholderHeight + 15);
                    $(\'<div class="slide-placeholder-animator" data-height="\' + placeholderHeight + \'"></div>\').insertAfter(ui.placeholder);
                
                },
                change: function(event, ui) {
                    
                    ui.placeholder.stop().height(0).animate({
                        height: ui.item.outerHeight() + 15
                    }, 300);
                    
                    placeholderAnimatorHeight = parseInt($(".slide-placeholder-animator").attr("data-height"));
                    
                    $(".slide-placeholder-animator").stop().height(placeholderAnimatorHeight + 15).animate({
                        height: 0
                    }, 300, function() {
                        $(this).remove();
                        placeholderHeight = ui.item.outerHeight();
                        $(\'<div class="slide-placeholder-animator" data-height="\' + placeholderHeight + \'"></div>\').insertAfter(ui.placeholder);
                    });
                    
                },

	          });

                function getOrder() {
                    var data = "";
            
                    $("[data-sortable-chapter]").each(function(){
                       if(data.length>0){
                            data += ",";
                       }
                       data += $(this).attr("data-sortable-chapter");
                    });
                   return data;
                }
            ';
	        
	    
		    
		    print '} );</script>';
		}
		
		if(!empty($conf->global->DOCEDIT_CHAPTERS_INLINE_EDITION))
		{
		    // experimental, not finish
		    
			print '<script>';
		    // The "instanceCreated" event is fired for every editor instance created.
		    print ' CKEDITOR.on( \'instanceCreated\', function ( event ) {
		                  var editor = event.editor, element = editor.element;
		        
		            // Customize the editor configuration on "configLoaded" event,
		            // which is fired after the configuration file loading and execution. 
		            // This makes it possible to change the configuration before the editor initialization takes place.
		            editor.on( \'configLoaded\', function () {
		                
		                // Remove redundant plugins to make the editor simpler.
				    editor.config.removePlugins = \'flash,forms,iframe,newpage,smiley,specialchar,templates\';


		                editor.config.customConfig = ckeditorConfig;
		                editor.config.readOnly = false;
		                editor.config.htmlEncodeOutput =false;
		                editor.config.allowedContent =false;
		                editor.config.extraAllowedContent = \'\';
		                editor.config.fullPage = false;
		                editor.config.toolbarStartupExpanded=false;
		                editor.config.language= \''.$langs->defaultlang.'\';
		                editor.config.textDirection= \''.$langs->trans("DIRECTION").'\';
                        width: element.offsetWidth,
                        editor.config.filebrowserBrowseUrl = ckeditorFilebrowserBrowseUrl;   
                        editor.config.filebrowserImageBrowseUrl = ckeditorFilebrowserImageBrowseUrl;
                        editor.config.filebrowserWindowWidth = \'900\';
                        editor.config.filebrowserWindowHeight = \'500\';
                        editor.config.filebrowserImageWindowWidth = \'900\';
                        editor.config.filebrowserImageWindowHeight = \'500\';

                    	// Used for notes fields
                    	editor.config.toolbar_dolibarr_inline_notes =
                    	[
                    	 	[\'SpellChecker\', \'Scayt\'],// \'Cut\',\'Copy\',\'Paste\',\'-\', are useless, can be done with right click, even on smarpthone
                    	 	[\'Undo\',\'Redo\',\'-\',\'Find\',\'Replace\'],
                    	    [\'Format\',\'Font\',\'FontSize\'],
                    	 	[\'Bold\',\'Italic\',\'Underline\',\'Strike\',\'Superscript\',\'-\',\'TextColor\',\'BGColor\',\'RemoveFormat\'],
                    	 	[\'NumberedList\',\'BulletedList\',\'Outdent\',\'Indent\'],
                    	 	[\'JustifyLeft\',\'JustifyCenter\',\'JustifyRight\',\'JustifyBlock\'],
                    	    [\'Link\',\'Unlink\',\'Image\',\'Table\',\'HorizontalRule\',\'SpecialChar\'],
                    	 	[\'Source\']
                    	];

		                editor.config.toolbar = editor.config.toolbar_dolibarr_inline_notes;

		            } );
		    } );
		    ';
		    
		    
		    print ' $( function() { ';
		    print '
                   $(".docedit_save").click(function(btnsave) {

                        var saveTarget = $($(this).data("target"));
                        
                        if(CKEDITOR.instances[saveTarget.attr("id")] != undefined)
                        {
                            var evt = CKEDITOR.instances[saveTarget.attr("id")];
                            // getData() returns CKEditor\'s HTML content.
                            console.log( evt ); //evt.editor.getData().length 


                            $.ajax({
                              method: "POST",
                              url: "'.dol_buildpath('referenceletters/script/interface.php',1).'",
                              dataType: "json",
                              data: { set: "content" , id: saveTarget.data("id") , type: saveTarget.data("type"), content: evt.getData() }
                            })
                            .done(function( data ) {
                                if(data.status){
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Saved')).'");
                                }else{
                                    $.jnotify("'.dol_escape_js($langs->transnoentities('Error')).' : " + data.message, "error", 3000);
                                }
                            });

                        }

                   });
            ';
		    print '} );</script>';
		}
	}
    print '<style>.ui-state-highlight::before { content: "'.$langs->trans('PlaceHere').'"; }</style>';
	print "</div>\n";

	/*
	 * Barre d'actions
	*/
	print '<div class="tabsAction">';
	if ($user->rights->referenceletters->write) {
	    print '<div class="inline-block divButAction">';
	    print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters/card.php',1).'?action=addbreakpage&id='.$object->id.'">' . $langs->trans("RefLtrAddPageBreak") . '</a>';
	    print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters/card.php',1).'?action=addbreakpagewithoutheader&id='.$object->id.'">' . $langs->trans("RefLtrAddPageBreakWithoutHeader") . '</a>';
	    print '<a class="butAction" href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?action=create&idletter='.$object->id.'">' . $langs->trans("RefLtrNewChaters") . '</a>';
	    print "</div><br>";
		//print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=edit">' . $langs->trans("Edit") . "</a></div>\n";
		print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=clone">' . $langs->trans("Clone") . "</a></div>\n";
	} else {
		print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans("Edit") . "</font></div>";
	}

	// Activ/Unactiv
	if ($user->rights->referenceletters->write) {
		if (empty($object->status)) {
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=changestatus">' . $langs->trans("RefLtrActive") . "</a></div>\n";
		} else {
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=changestatus">' . $langs->trans("RefLtrUnactive") . "</a></div>\n";
		}
	}

	// Delete
	if ($user->rights->referenceletters->delete) {
		print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete">' . $langs->trans("Delete") . "</a></div>\n";
	} else {
		print '<div class="inline-block divButAction"><font class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans("Delete") . "</font></div>";
	}
	print '</div>';

}



// Page end
llxFooter();
$db->close();

function _print_docedit_footer($object){
    global $langs, $conf, $user;
    
    print '<div class="sortable sortableHelper docedit_document_body docedit_document_bloc"></div>';
    
    print '<div class="docedit_document_footer docedit_document_bloc">';
    
    
    // Button and infos
    print '<div class="docedit_infos docedit_infos_left">';
    if ($user->rights->referenceletters->write) {
        print '<a  href="'.dol_buildpath('/referenceletters/referenceletters/footer.php',1).'?id=' . $object->id .'">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
    }
    print '</div><!-- END docedit_infos -->';
    
    
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


function _print_docedit_header($object, $norepeat=false){
    global $langs, $conf, $user;
    
    
    
    print '<div class="docedit_document_head docedit_document_bloc">';
    
    // Button and infos
    print '<div class="docedit_infos docedit_infos_left">';
    if ($user->rights->referenceletters->write) {
        print '<a  href="'.dol_buildpath('/referenceletters/referenceletters/header.php',1).'?id=' . $object->id .'">' . img_picto($langs->trans('Edit'), 'edit') . '</a>';
    }
    
    print '</div><!-- END docedit_infos -->';
    
    
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

