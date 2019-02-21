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

			$select_elemnt .= '<option value="' . $element_type . '" '.$option_selected.'>' . $langs->trans($array_data['title']) . '</option>';
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
	 *
	 *
	 * @param User $user
	 * @param CommonObject $object
	 */
	public function displaySubtitutionKey($user,$reflettersobject) {
		global $langs,$bc;

		$form = new Form($this->db);
		$subs_array=$reflettersobject->getSubtitutionKey($user);

		$html='<table id="refltertags">';

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
						$html.='<td>';
						$html.='{'.$key.'}';
						$html.='</td>';
						$html.='<td>';
						$html.=$value;
						$html.='</td>';
						$html.='</tr>';
					}
				}
			}
		}

		$html.='</table>';

		return $form->textwithpicto($langs->trans("RefLtrDisplayTag"), $html, 1, 'help', '', 0, 2, 'refltertags');
	}
}