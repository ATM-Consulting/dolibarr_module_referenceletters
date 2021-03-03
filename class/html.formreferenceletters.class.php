<?php

/* References letters
 * Copyright (C) 2014  HENRY Florian  florian.henry@open-concept.pro
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file lead/class/html.formreferenceletters.class.php
 * \ingroup referenceletters
 * \brief File of class with all html predefined components
 */
class FormReferenceLetters extends Form
{

	var $db;

	var $error;

	var $num;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db
	 *        	handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
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
	public function showrefnav($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $morehtmlright = '')
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
	public function selectElementType($selected='',$htmlname='element_type',$showempty=0, $in_array=array()) {
		global $langs;

		require_once 'referenceletters.class.php';

		$refletter = new Referenceletters($this->db);
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
	public function selectStatus($selected='',$htmlname='element_type',$showempty=1) {
		global $langs;

		$status_array=array();

		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$status_array[-1]='';
		}
		require_once 'referenceletters.class.php';
		$refletter = new Referenceletters($this->db);

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
	public function selectDefaultDoc($selected='',$htmlname='defaultdoc',$showempty=1) {
		global $langs;

		$status_array=array();

		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$status_array[-1]='';
		}
		require_once 'referenceletters.class.php';
		$refletter = new Referenceletters($this->db);

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
	public function selectReferenceletters($selected='',$htmlname='refletter',$element_type='',$showempty=0) {
		global $langs;

		require_once 'referenceletters.class.php';

		$refletter = new Referenceletters($this->db);
		$filter=array('t.element_type'=>$element_type, 't.status'=>1);
		$refletter->fetch_all('ASC','t.title',0,0,$filter);
		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$select_elemnt .= '<option value=""></option>';
		}
		foreach($refletter->lines as $key=>$line) {

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

	public function displaySubtitutionKey($user,$reflettersobject) {
		global $langs,$bc;

        $form = new Form($this->db);
		$html=$this->getSubtitutionKeyTable($user,$reflettersobject);

		return $form->textwithpicto($langs->trans("RefLtrDisplayTag"), $html, 1, 'help', '', 0, 2, 'refltertags');
	}

	/**
	 *
	 *
	 * @param User $user
	 * @param ReferenceLetters $reflettersobject
	 */
	public function displaySubtitutionKeyAdvanced($user, $reflettersobject) {
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


    /**
     *
     *
     * @param User $user
     * @param CommonObject $object
     */
    public function getSubtitutionKeyTable($user,$reflettersobject){
        global $langs,$bc;

        $subs_array=$reflettersobject->getSubtitutionKey($user);

        $html='<table id="refltertags" >';

        if (is_array($subs_array) && count($subs_array)>0) {
            foreach($subs_array as $block=>$data) {
                $html.='<tr class="liste_titre">';
                $html.='<td colspan="2">';
                $html.=$block;
                $html.='</td>';
                $html.='</tr>';
                $html.='<tr class="liste_titre">';
                $html.='<td width="50px">';
                $html.=$langs->trans('RefLtrTag');
                $html.='</td>';
                $html.='<td>';
                $html.=$langs->trans('Value');
                $html.='</td>';
                $html.='</tr>';
                if (count($data)>0) {
                    $var=true;
                    foreach($data as $key=>$value) {
                        $html.="<tr class=\"oddeven\">";
                        $html.='<td class="referenceletter-subtitutionkey">';
                        $html.='{'.$key.'}';
                        $html.='</td>';
                        $html.='<td>';
                        $html.= dol_escape_htmltag($value);// to prevent js execution like redirect...
                        $html.='</td>';
                        $html.='</tr>';
                    }
                }
            }
        }

        $html.='</table>';

        return $html;
    }

	public function renderChapterHTML(ReferenceLettersChapters $chapter, $mode='view') {
		global $langs;

		$urlToken = '';
		if (function_exists('newToken')) $urlToken = newToken();

		if ($chapter->content_text=='@breakpagenohead@')
		{
			$out = '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$chapter->id.'" >';
			$out.= $langs->trans('RefLtrAddPageBreakWithoutHeader');
			if ($mode=='view') {
				$out.= '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $chapter->id . '&action=delete&token='.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		elseif ($chapter->content_text=='@breakpage@')
		{
			$out = '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$chapter->id.'" >';
			$out.= $langs->trans('RefLtrPageBreak');
			if ($mode=='view') {
				$out.= '<a href="' . dol_buildpath('/referenceletters/referenceletters/chapter.php', 1) . '?id=' . $chapter->id . '&action=delete&token='.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		elseif (strpos($chapter->content_text,'@pdfdoc')===0) {
			$documentModel=str_replace('@','',str_replace('pdfdoc_','',$chapter->content_text));
			$out = '<div class="sortable sortabledisable docedit_pdfmodel"  data-sortable-chapter="'.$chapter->id.'" >';
			$out .= img_pdf($langs->trans('RefLtrPDFDoc')) . $langs->trans('RefLtrPDFDoc').' ('.$documentModel.')';
			if ($mode == 'view') {
				$out .= '<a href="' . dol_buildpath('/referenceletters/referenceletters/chapter.php', 1) . '?id=' . $chapter->id . '&action=delete&token='.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		$out .=  '</div>';
		return $out;
	}
}
