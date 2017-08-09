<?php

require_once DOL_DOCUMENT_ROOT . '/includes/odtphp/Segment.php';

class SegmentRfltr extends Segment {
	
	/**
	 * Constructor
	 *
	 * @param string $name  name of the segment to construct
	 * @param string $xml   XML tree of the segment
	 * @param string $odf   odf
	 */
	public function __construct($name, $xml, $odf)
	{
		$this->name = (string) $name;
		$this->xml = (string) $xml;
		$this->odf = $odf;
		/*$zipHandler = $this->odf->getConfig('ZIP_PROXY');
		$this->file = new $zipHandler($this->odf->getConfig('PATH_TO_TMP'));*/
		$this->_analyseChildren($this->xml);
	}
	
	/**
	 * Replace variables of the template in the XML code
	 * All the children are also called
	 * Complete the current segment with new line
	 *
	 * @return string
	 */
	public function merge()
	{
		// To provide debug information on line number processed
		global $count;
		if (empty($count)) $count=1;
		else $count++;
		
		if (empty($this->savxml)) $this->savxml = $this->xml;       // Sav content of line at first line merged, so we will reuse original for next steps
		$this->xml = $this->savxml;
		$tmpvars = $this->vars;                                     // Store into $tmpvars so we won't modify this->vars when completing data with empty values
		
		// Search all tags fou into condition to complete $tmpvars, so we will proceed all tests even if not defined
		$reg='@\[!--\sIF\s([{}a-zA-Z0-9\.\,_]+)\s--\]@smU';
		preg_match_all($reg, $this->xml, $matches, PREG_SET_ORDER);
		//var_dump($tmpvars);exit;
		foreach($matches as $match)   // For each match, if there is no entry into this->vars, we add it
		{
			if (! empty($match[1]) && ! isset($tmpvars[$match[1]]))
			{
				$tmpvars[$match[1]] = '';     // Not defined, so we set it to '', we just need entry into this->vars for next loop
			}
		}
		
		// Conditionals substitution
		// Note: must be done before static substitution, else the variable will be replaced by its value and the conditional won't work anymore
		foreach($tmpvars as $key => $value)
		{
			// If value is true (not 0 nor false nor null nor empty string)
			if ($value)
			{
				// Remove the IF tag
				$this->xml = str_replace('[!-- IF '.$key.' --]', '', $this->xml);
				// Remove everything between the ELSE tag (if it exists) and the ENDIF tag
				$reg = '@(\[!--\sELSE\s' . $key . '\s--\](.*))?\[!--\sENDIF\s' . $key . '\s--\]@smU'; // U modifier = all quantifiers are non-greedy
				$this->xml = preg_replace($reg, '', $this->xml);
			}
			// Else the value is false, then two cases: no ELSE and we're done, or there is at least one place where there is an ELSE clause, then we replace it
			else
			{
				// Find all conditional blocks for this variable: from IF to ELSE and to ENDIF
				$reg = '@\[!--\sIF\s' . $key . '\s--\](.*)(\[!--\sELSE\s' . $key . '\s--\](.*))?\[!--\sENDIF\s' . $key . '\s--\]@smU'; // U modifier = all quantifiers are non-greedy
				preg_match_all($reg, $this->xml, $matches, PREG_SET_ORDER);
				foreach($matches as $match) { // For each match, if there is an ELSE clause, we replace the whole block by the value in the ELSE clause
					if (!empty($match[3])) $this->xml = str_replace($match[0], $match[3], $this->xml);
				}
				// Cleanup the other conditional blocks (all the others where there were no ELSE clause, we can just remove them altogether)
				$this->xml = preg_replace($reg, '', $this->xml);
			}
		}
		$this->xmlParsed .= str_replace(array_keys($tmpvars), array_values($tmpvars), $this->xml);
		if ($this->hasChildren()) {
			foreach ($this->children as $child) {
				$this->xmlParsed = str_replace($child->xml, ($child->xmlParsed=="")?$child->merge():$child->xmlParsed, $this->xmlParsed);
				$child->xmlParsed = '';
			}
		}
		$reg = "/\[!--\sBEGIN\s$this->name\s--\](.*)\[!--\sEND\s$this->name\s--\]/sm";
		$this->xmlParsed = preg_replace($reg, '$1', $this->xmlParsed);
		
		return $this->xmlParsed;
	}
	
}