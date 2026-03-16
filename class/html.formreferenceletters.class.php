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
	public function __construct(DoliDB $db)
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
	 * @param string $selected Selected
	 * @param string $htmlname HTML name
	 * @param int    $showempty Show empty
	 * @param array  $in_array  In array
	 * @return string select HTML
	 */
	public function selectElementType($selected = '', string $htmlname = 'element_type', int $showempty = 0, array $in_array = array()): string {
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
	 * @param string $selected  Selected
	 * @param string $htmlname  HTML name
	 * @param int    $showempty Show empty
	 * @return string select HTML
	 */
	public function selectStatus($selected = '', string $htmlname = 'element_type', int $showempty = 1): string {
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
	 * @param string $selected  Selected
	 * @param string $htmlname  HTML name
	 * @param int    $showempty Show empty
	 * @return string HTML
	 */
	public function selectDefaultDoc($selected = '', string $htmlname = 'defaultdoc', int $showempty = 1): string {
		global $langs;

		$status_array=array();

		$select_elemnt = '<select class="flat" name="' . $htmlname . '">';
		if (!empty($showempty)) {
			$status_array[-1]='';
		}
		require_once 'referenceletters.class.php';
		$refletter = new Referenceletters($this->db);
		$refletter->TDefaultDoc = $refletter->TDefaultDoc ?? [];
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
	 * @param string $selected     Selected
	 * @param string $htmlname     HTML name
	 * @param string $element_type Element type
	 * @param int    $showempty    Show empty
	 * @return string select HTML
	 */
	public function selectReferenceletters($selected = '', string $htmlname = 'refletter', string $element_type = '', int $showempty = 0): string {
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

	public function displaySubstitutionKey(User $user, ReferenceLetters $reflettersobject): string {
		global $langs,$bc;

        $form = new Form($this->db);
		$html=$this->getSubstitutionKeyTable($user,$reflettersobject);

		return $form->textwithpicto($langs->trans("RefLtrDisplayTag"), $html, 1, 'help', '', 0, 2, 'refltertags');
	}

	/**
	 * Add list of substitute key
	 *
	 * @param User $user
	 * @param ReferenceLetters $reflettersobject
	 */
	public function displaySubstitutionKeyAdvanced(User $user, ReferenceLetters $reflettersobject): string {
		global $langs;

		$html = '<div id="subtitutionkey" style="display: none;" >';

		$html .= '<div class="search-filter-wrap"  >';
		$html .= '<i class="fa fa-search"></i>';
		$html .= '<input type="text" id="item-filter" class="search-filter" data-target="" value="" placeholder="'.$langs->trans('Search').'" />';
		$html .= '<span id="filter-count-wrap" >'.$langs->trans('Result').': <span id="filter-count" ></span></span>';
		$html .= '</div>';

		$uiData = $reflettersobject->getSubstitutionKeyUiData($user);
		$subs_array = isset($uiData['tags']) && is_array($uiData['tags']) ? $uiData['tags'] : array();
		$loop_array = isset($uiData['loops']) && is_array($uiData['loops']) ? $uiData['loops'] : array();
		$loopNoticeByGroup = $this->buildLoopNoticeByGroup($loop_array);

		$html .= '<div id="accordion-refltertags" >';

		$loopSectionNotice = is_object($langs) && method_exists($langs, 'transnoentitiesnoconv') ? $langs->transnoentitiesnoconv('RefLtrLoopSectionNotice') : $langs->trans('RefLtrLoopSectionNotice');
		$technicalNotice = is_object($langs) && method_exists($langs, 'transnoentitiesnoconv') ? $langs->transnoentitiesnoconv('RefLtrTechnicalConstantsNotice') : $langs->trans('RefLtrTechnicalConstantsNotice');

		if (!empty($loop_array)) {
			$html .= '<h3 class="accordion-refltertags-title">'.$langs->trans('RefLtrLoopSectionTitle').'<span class="h3-element-count badge" data-element-count=""></span></h3>';
			$html .= '<div class="accordion-refltertags-body">';
			$html .= '<div class="referenceletter-loop-notice">'.$loopSectionNotice.'</div>';
			$html .= '<table class="referenceletter-subtitutionkey-table referenceletter-subtitutionloop-table">';
			$html .= '<colgroup>';
			$html .= '<col class="referenceletter-subtitutionkey-col-desc">';
			$html .= '<col class="referenceletter-subtitutionkey-col-tag">';
			$html .= '<col class="referenceletter-subtitutionkey-col-format">';
			$html .= '</colgroup>';
			$html .= '<tr class="liste_titre">';
			$html .= '<th>'.$langs->trans('Description').'</th>';
			$html .= '<th>'.$langs->trans('RefLtrLoopSyntax').'</th>';
			$html .= '<th>'.$langs->trans('RefLtrLoopMarker').'</th>';
			$html .= '</tr>';
			foreach ($loop_array as $loop) {
				$loopSyntax = str_replace("\\n", "\n", $loop['syntax']);
				$loopSyntaxDisplay = htmlspecialchars($loopSyntax, ENT_QUOTES, 'UTF-8');
				$html .= '<tr class="oddeven searchable search-match">';
				$html .= '<td class="referenceletter-subtitutionkey-desc">';
				$html .= '<strong>' . dol_escape_htmltag($loop['label']) . '</strong><br>';
				$html .= dol_escape_htmltag($loop['description']);
				if (!empty($loop['group_usage_label'])) {
					$html .= '<div class="referenceletter-entry-meta">';
					$html .= '<span class="referenceletter-entry-usage">' . dol_escape_htmltag($loop['group_usage_label']) . '</span>';
					$html .= '</div>';
				}
				$html .= '</td>';
				$html .= '<td class="referenceletter-subtitutionkey-col">';
				$html .= '<span class="referenceletter-subtitutionkey referenceletter-subtitutionloop classfortooltip" title="' . $langs->trans('ClickToAddOnEditor') . '" data-shortcode="' . dol_escape_htmltag($loopSyntax) . '">';
				$html .= nl2br($loopSyntaxDisplay);
				$html .= '</span>';
				$html .= '</td>';
				$html .= '<td>';
				$html .= dol_escape_htmltag(implode(', ', $loop['sample_tags']));
				$html .= '</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}

		if (is_array($subs_array) && count($subs_array)>0) {
			foreach($subs_array as $block=>$data) {
				$html .= '<h3 class="accordion-refltertags-title">' . $block . '<span class="h3-element-count badge" data-element-count=""></span></h3>';

				$html .= '<div class="accordion-refltertags-body" >';
				if ($block === $langs->trans('RefLtrTechnicalConstantsTitle')) {
					$html .= '<div class="referenceletter-loop-notice">'.$technicalNotice.'</div>';
				} elseif (!empty($loopNoticeByGroup[$block])) {
					$html .= '<div class="referenceletter-loop-notice">'.$loopNoticeByGroup[$block].'</div>';
				}
				$html .= '<table class="referenceletter-subtitutionkey-table">';
				$html .= '<colgroup>';
				$html .= '<col class="referenceletter-subtitutionkey-col-desc">';
				$html .= '<col class="referenceletter-subtitutionkey-col-tag">';
				$html .= '<col class="referenceletter-subtitutionkey-col-format">';
				$html .= '</colgroup>';
				$html .= '<tr class="liste_titre">';
				$html .= '<th>'.$langs->trans('Description').'</th>';
				$html .= '<th>'.$langs->trans('RefLtrTag').'</th>';
				$html .= '<th>'.$langs->trans('Format').'</th>';
				$html .= '</tr>';
				if (is_array($data) && count($data) > 0) {
					foreach ($data as $key => $value) {
						$html .= '<tr class="oddeven searchable search-match">';
						$html .= '    <td class="referenceletter-subtitutionkey-desc">';
						$html .= '        <span class="referenceletter-subtitutionkey classfortooltip" title="' . $langs->trans('ClickToAddOnEditor') . '" data-shortcode="{' . $key . '}" >';
						$html .= dol_escape_htmltag($value['description']);
						$html .= '</span>';
						if (!empty($value['type_label'])) {
							$html .= '<div class="referenceletter-entry-meta">';
							$html .= '<span class="referenceletter-entry-badge referenceletter-entry-badge-' . dol_escape_htmltag(strtolower($value['entry_type'])) . '">' . dol_escape_htmltag($value['type_label']) . '</span>';
							if (!empty($value['usage_hint'])) {
								$html .= '<span class="referenceletter-entry-usage">' . dol_escape_htmltag($value['usage_hint']) . '</span>';
							}
							$html .= '</div>';
						}
						$html .= '    </td>';
						$html .= '    <td class="referenceletter-subtitutionkey-col">';
						$html .= '        <span class="referenceletter-subtitutionkey classfortooltip" title="' . $langs->trans('ClickToAddOnEditor') . '"  data-shortcode="{' . $key . '}"  >{' . $key . '}</span>';
						$html .= '    </td>';
						$html .= '    <td>';
						$html .= dol_escape_htmltag($value['format_hint']);
						$html .= '    </td>';
						$html .= '</tr>';
					}
				}
				$html .= '</table>';
				$html .= '</div>';
			}

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
                         if (typeof $( "#subtitutionkey" ).dialog === "function") {
                             $( "#subtitutionkey" ).dialog( "open" );
                         } else {
                             $( "#subtitutionkey" ).show();
                         }

                         // Focus the search input.
                         $("#item-filter").focus();
                    });

                     $(".docedit_setbool").click(function() {

						// Get the chapter id.
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
                        var rawShortcode = $(this).data("shortcode");
                        var shortcode = (rawShortcode == undefined ? "" : String(rawShortcode)).replace(/\\\\n/g, "\n");

                        if(CKEDITOR.instances[shortcodeTarget.attr("id")] != undefined)
                        {
                            var evt = CKEDITOR.instances[shortcodeTarget.attr("id")];

                            try {
                                evt.insertHtml(shortcode);

                                $.jnotify("'.dol_escape_js($langs->transnoentities('RefLtrShortCodeAdded')).' : " + shortcode,"3000","false",{ remove: function (){}})  ;

                            }catch (err) {
                                $.jnotify("'.dol_escape_js($langs->transnoentities('RefLtrShortCodeAddError')).'","error","true",{ remove: function (){}})  ;

                            }

                            $( "#subtitutionkey" ).dialog( "close" );
                        }
                        else if (shortcodeTarget.length && (shortcodeTarget.is("textarea") || shortcodeTarget.is("input")))
                        {
                            var targetNode = shortcodeTarget.get(0);
                            var start = typeof targetNode.selectionStart === "number" ? targetNode.selectionStart : shortcodeTarget.val().length;
                            var end = typeof targetNode.selectionEnd === "number" ? targetNode.selectionEnd : start;
                            var currentValue = shortcodeTarget.val() || "";
                            shortcodeTarget.val(currentValue.substring(0, start) + shortcode + currentValue.substring(end));
                            targetNode.selectionStart = targetNode.selectionEnd = start + shortcode.length;
                            shortcodeTarget.focus();
                            $.jnotify("'.dol_escape_js($langs->transnoentities('RefLtrShortCodeAdded')).' : " + shortcode,"3000","false",{ remove: function (){}})  ;
                            $( "#subtitutionkey" ).dialog( "close" );
                        }
                        else{
                            $.jnotify("'.dol_escape_js($langs->transnoentities('RefLtrShortCodeAddError')).'","error","true",{ remove: function (){}})  ;
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

                <link rel="stylesheet" type="text/css" href="'.dol_buildpath('/referenceletters/css/view_documents.css', 1).'">
                ';
		return $html;
	}


    /**
     *
     * @param User             $user             User
     * @param ReferenceLetters $reflettersobject Ref letters object
     * @return string
     */
	public function getSubstitutionKeyTable(User $user, ReferenceLetters $reflettersobject): string
	{
	        global $langs,$bc;

        $uiData = $reflettersobject->getSubstitutionKeyUiData($user);
        $subs_array = isset($uiData['tags']) && is_array($uiData['tags']) ? $uiData['tags'] : array();
        $loop_array = isset($uiData['loops']) && is_array($uiData['loops']) ? $uiData['loops'] : array();
        $loopNoticeByGroup = $this->buildLoopNoticeByGroup($loop_array);

	        $html='<table id="refltertags" >';

	        $loopSectionNotice = is_object($langs) && method_exists($langs, 'transnoentitiesnoconv') ? $langs->transnoentitiesnoconv('RefLtrLoopSectionNotice') : $langs->trans('RefLtrLoopSectionNotice');
	        $technicalNotice = is_object($langs) && method_exists($langs, 'transnoentitiesnoconv') ? $langs->transnoentitiesnoconv('RefLtrTechnicalConstantsNotice') : $langs->trans('RefLtrTechnicalConstantsNotice');

	        if (!empty($loop_array)) {
	            $html .= '<tr><td colspan="3"><div class="referenceletter-loop-notice">'.$loopSectionNotice.'</div></td></tr>';
	            $html.='<tr class="liste_titre">';
	            $html.='<td colspan="3">'.$langs->trans('RefLtrLoopSectionTitle').'</td>';
	            $html.='</tr>';
            $html.='<tr class="liste_titre">';
            $html.='<td>'.$langs->trans('Description').'</td>';
            $html.='<td>'.$langs->trans('RefLtrLoopSyntax').'</td>';
            $html.='<td>'.$langs->trans('RefLtrLoopMarker').'</td>';
            $html.='</tr>';
		            foreach ($loop_array as $loop) {
		                $loopSyntax = str_replace("\\n", "\n", $loop['syntax']);
		                $loopSyntaxDisplay = htmlspecialchars($loopSyntax, ENT_QUOTES, 'UTF-8');
		                $html.='<tr class="oddeven">';
	                $html.='<td><strong>'.dol_escape_htmltag($loop['label']).'</strong><br>'.dol_escape_htmltag($loop['description']);
	                if (!empty($loop['group_usage_label'])) {
	                    $html.='<div class="referenceletter-entry-meta">';
	                    $html.='<span class="referenceletter-entry-usage">'.dol_escape_htmltag($loop['group_usage_label']).'</span>';
	                    $html.='</div>';
	                }
	                $html.='</td>';
		                $html.='<td class="referenceletter-subtitutionkey">'.nl2br($loopSyntaxDisplay).'</td>';
                $html.='<td>'.dol_escape_htmltag(implode(', ', $loop['sample_tags'])).'</td>';
                $html.='</tr>';
            }
        }

	        if (is_array($subs_array) && count($subs_array)>0) {
	            foreach($subs_array as $block=>$data) {
	                $html.='<tr class="liste_titre">';
	                $html.='<td colspan="3">';
	                $html.=$block;
	                $html.='</td>';
	                $html.='</tr>';
		                if ($block === $langs->trans('RefLtrTechnicalConstantsTitle')) {
		                    $html .= '<tr><td colspan="3"><div class="referenceletter-loop-notice">'.$technicalNotice.'</div></td></tr>';
		                } elseif (!empty($loopNoticeByGroup[$block])) {
		                    $html .= '<tr><td colspan="3"><div class="referenceletter-loop-notice">'.$loopNoticeByGroup[$block].'</div></td></tr>';
		                }
	                $html.='<tr class="liste_titre">';
	                $html.='<td>';
	                $html.=$langs->trans('Description');
                $html.='</td>';
                $html.='<td width="50px">';
                $html.=$langs->trans('RefLtrTag');
                $html.='</td>';
                $html.='<td>';
                $html.=$langs->trans('Format');
                $html.='</td>';
                $html.='</tr>';
				if (count($data)>0) {
	                    foreach($data as $key=>$value) {
	                        $html.="<tr class=\"oddeven\">";
	                        $html.='<td>';
	                        $html.= dol_escape_htmltag($value['description']);
	                        if (!empty($value['type_label'])) {
	                            $html.='<div class="referenceletter-entry-meta">';
	                            $html.='<span class="referenceletter-entry-badge referenceletter-entry-badge-' . dol_escape_htmltag(strtolower($value['entry_type'])) . '">' . dol_escape_htmltag($value['type_label']) . '</span>';
	                            if (!empty($value['usage_hint'])) {
	                                $html.='<span class="referenceletter-entry-usage">' . dol_escape_htmltag($value['usage_hint']) . '</span>';
	                            }
	                            $html.='</div>';
	                        }
	                        $html.='</td>';
	                        $html.='<td class="referenceletter-subtitutionkey">';
	                        $html.='{'.$key.'}';
                        $html.='</td>';
                        $html.='<td>';
                        $html.= dol_escape_htmltag($value['format_hint']);
                        $html.='</td>';
                        $html.='</tr>';
                    }
                }
            }
        }

		$html.='</table>';

		return $html;
	}

	/**
	 * @deprecated Use displaySubstitutionKey() instead.
	 *
	 * @param User $user
	 * @param CommonObject $reflettersobject
	 * @return string
	 */
	public function displaySubtitutionKey(User $user, ReferenceLetters $reflettersobject): string {
		return $this->displaySubstitutionKey($user, $reflettersobject);
	}

	/**
	 * @deprecated Use displaySubstitutionKeyAdvanced() instead.
	 *
	 * @param User $user
	 * @param ReferenceLetters $reflettersobject
	 * @return string
	 */
	public function displaySubtitutionKeyAdvanced(User $user, ReferenceLetters $reflettersobject): string {
		return $this->displaySubstitutionKeyAdvanced($user, $reflettersobject);
	}

	/**
	 * @deprecated Use getSubstitutionKeyTable() instead.
	 *
	 * @param User $user
	 * @param ReferenceLetters $reflettersobject
	 * @return string
	 */
	public function getSubtitutionKeyTable(User $user, ReferenceLetters $reflettersobject): string {
		return $this->getSubstitutionKeyTable($user, $reflettersobject);
	}

	/**
	 * Build one notice per block when some loops use the same field group.
	 *
	 * @param array<int,array<string,mixed>> $loopArray
	 * @return array<string,string>
	 */
	protected function buildLoopNoticeByGroup(array $loopArray)
	{
		global $langs;

		$segmentsByGroup = array();

		foreach ($loopArray as $loop) {
			if (empty($loop['group_label']) || empty($loop['segment'])) {
				continue;
			}

			$groupLabel = (string) $loop['group_label'];
			if (empty($segmentsByGroup[$groupLabel])) {
				$segmentsByGroup[$groupLabel] = array();
			}

			$segmentsByGroup[$groupLabel][] = (string) $loop['segment'];
		}

		$notices = array();
		foreach ($segmentsByGroup as $groupLabel => $segments) {
			$segments = array_values(array_unique(array_filter($segments)));
			if (empty($segments)) {
				continue;
			}

			$segmentLabels = array();
			foreach ($segments as $segment) {
				$segmentLabels[] = '<code>' . dol_escape_htmltag($segment) . '</code>';
			}

			$template = is_object($langs) && method_exists($langs, 'transnoentitiesnoconv')
				? $langs->transnoentitiesnoconv('RefLtrBlockLoopsNotice', implode(', ', $segmentLabels))
				: $langs->trans('RefLtrBlockLoopsNotice', implode(', ', $segmentLabels));
			$notices[$groupLabel] = $template;
		}

		return $notices;
	}

	public function renderChapterHTML(ReferenceLettersChapters $chapter, string $mode='view'): string {
		global $langs;

		$urlToken = '';
        if (function_exists('newToken')) $urlToken = "&token=".newToken();

		if ($chapter->content_text=='@breakpagenohead@')
		{
			$out = '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$chapter->id.'" >';
			$out.= $langs->trans('RefLtrAddPageBreakWithoutHeader');
			if ($mode=='view') {
				$out.= '<a href="'.dol_buildpath('/referenceletters/referenceletters/chapter.php',1).'?id=' . $chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		elseif ($chapter->content_text=='@breakpage@')
		{
			$out = '<div class="sortable sortabledisable docedit_document_pagebreak"  data-sortable-chapter="'.$chapter->id.'" >';
			$out.= $langs->trans('RefLtrPageBreak');
			if ($mode=='view') {
				$out.= '<a href="' . dol_buildpath('/referenceletters/referenceletters/chapter.php', 1) . '?id=' . $chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		elseif (strpos($chapter->content_text,'@pdfdoc')===0) {
			$documentModel=str_replace('@','',str_replace('pdfdoc_','',$chapter->content_text));
			$out = '<div class="sortable sortabledisable docedit_pdfmodel"  data-sortable-chapter="'.$chapter->id.'" >';
			$out .= img_picto('', 'pdf') . $langs->trans('RefLtrPDFDoc').' ('.$documentModel.')';
			if ($mode == 'view') {
				$out .= '<a href="' . dol_buildpath('/referenceletters/referenceletters/chapter.php', 1) . '?id=' . $chapter->id . '&action=delete'.$urlToken.'">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
			}
		}
		$out .=  '</div>';
		return $out;
	}
}
