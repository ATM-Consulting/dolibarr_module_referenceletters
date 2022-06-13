<?php

dol_include_once('/referenceletters/lib/odf.lib.php');
require_once DOL_DOCUMENT_ROOT . '/includes/odtphp/zip/PclZipProxy.php';
class OdfRfltr extends Odf {

	/**
	 * Class constructor
	 *
	 * @param string $filename     The name of the odt file
	 * @param string $config       Array of config data
	 * @throws OdfException
	 */
	public function __construct($filename, $config = array(), $content='')
	{
		/*clearstatcache();

		if (! is_array($config)) {
			throw new OdfException('Configuration data must be provided as array');
		}
		foreach ($config as $configKey => $configValue) {
			if (array_key_exists($configKey, $this->config)) {
				$this->config[$configKey] = $configValue;
			}
		}

		$md5uniqid = md5(uniqid());
		if ($this->config['PATH_TO_TMP']) $this->tmpdir = preg_replace('|[\/]$|','',$this->config['PATH_TO_TMP']);	// Remove last \ or /
		$this->tmpdir .= ($this->tmpdir?'/':'').$md5uniqid;
		$this->tmpfile = $this->tmpdir.'/'.$md5uniqid.'.odt';*/	// We keep .odt extension to allow OpenOffice usage during debug.

		$this->contentXml = &strtr($content, array('&nbsp;'=>' ')); // Sinon erreur regex recherche [!-- BEGIN
		//$this->_moveRowSegments();
	}


	/**
	 * @override
	 * Function to convert a HTML string into an ODT string
	 *
	 * @param	string	$value	String to convert
	 */
	public function htmlToUTFAndPreOdf($value)
	{
		// We decode into utf8, entities
		$value=dol_html_entity_decode($value, ENT_QUOTES);

		// We convert html tags
		$ishtml=dol_textishtml($value);
		if ($ishtml)
		{
			// If string is "MYPODUCT - Desc <strong>bold</strong> with &eacute; accent<br />\n<br />\nUn texto en espa&ntilde;ol ?"
			// Result after clean must be "MYPODUCT - Desc bold with é accent\n\nUn texto en espa&ntilde;ol ?"

			// We want to ignore \n and we want all <br> to be \n
			$value=preg_replace('/(\r\n|\r|\n)/i','',$value);
			$value=preg_replace('/<br>/i',"\n",$value);
			$value=preg_replace('/<br\s+[^<>\/]*>/i',"\n",$value);
			$value=preg_replace('/<br\s+[^<>\/]*\/>/i',"\n",$value);

			//$value=preg_replace('/<strong>/','__lt__text:p text:style-name=__quot__bold__quot____gt__',$value);
			//$value=preg_replace('/<\/strong>/','__lt__/text:p__gt__',$value);

//			$value=dol_string_nohtmltag($value, 0);
		}

		return $value;
	}

	/**
	 * Move segment tags for lines of tables
	 * This function is called automatically within the constructor, so this->contentXml is clean before any other thing
	 *
	 * @return void
	 */
	private function _moveRowSegments()
	{
		// Replace BEGIN<text:s/>xxx into BEGIN xxx

		$this->contentXml = preg_replace('/\[!--\sBEGIN\srow.([\S]*)\s--\]/sm', '[!-- BEGIN \\1 --]', $this->contentXml);
		// Replace END<text:s/>xxx into END xxx
		$this->contentXml = preg_replace('/\[!--\sEND\s(row.[\S]*)\s--\]/sm', '[!-- END \\1 --]', $this->contentXml);


		// Search all possible rows in the document
		$reg1 = "#<table:table-row[^>]*>(.*)</table:table-row>#smU";
		preg_match_all($reg1, $this->contentXml, $matches);
		for ($i = 0, $size = count($matches[0]); $i < $size; $i++) {
			// Check if the current row contains a segment row.*
			$reg2 = '#\[!--\sBEGIN\s(row.[\S]*)\s--\](.*)\[!--\sEND\s\\1\s--\]#sm';
			if (preg_match($reg2, $matches[0][$i], $matches2)) {
				$balise = str_replace('row.', '', $matches2[1]);
				// Move segment tags around the row
				$replace = array(
					'[!-- BEGIN ' . $matches2[1] . ' --]'	=> '',
					'[!-- END ' . $matches2[1] . ' --]'		=> '',
					'<table:table-row'							=> '[!-- BEGIN ' . $balise . ' --]<table:table-row',
					'</table:table-row>'						=> '</table:table-row>[!-- END ' . $balise . ' --]'
				);
				$replacedXML = str_replace(array_keys($replace), array_values($replace), $matches[0][$i]);
				$this->contentXml = str_replace($matches[0][$i], $replacedXML, $this->contentXml);
			}
		}
	}

	/**
	 * Declare a segment in order to use it in a loop.
	 * Extract the segment and store it into $this->segments[]. Return it for next call.
	 *
	 * @param  string      $segment        Segment
	 * @throws Exception
	 * @return Segment
	 */
	public function setSegment($segment)
	{
		dol_include_once('/referenceletters/class/segment_rfltr.class.php');
		if (array_key_exists($segment, $this->segments)) {
			return $this->segments[$segment];
		}
		// $reg = "#\[!--\sBEGIN\s$segment\s--\]<\/text:p>(.*)<text:p\s.*>\[!--\sEND\s$segment\s--\]#sm";
		$reg = "#\[!--\sBEGIN\s$segment\s--\](.*)\[!--\sEND\s$segment\s--\]#sm";
		/*echo '<pre>';
		print_r($this->contentXml);
		echo '</pre>';
		exit;*/
		if (preg_match($reg, html_entity_decode($this->contentXml), $m) == 0) {
			dol_syslog(get_class($this).'::'.__METHOD__."'".$segment."' segment not found in the document. The tag [!-- BEGIN xxx --] or [!-- END xxx --] is not present into content file.");
			return null;
		} else {
			$this->segments[$segment] = new SegmentRfltr($segment, $m[1], $this);
			return $this->segments[$segment];
		}
	}


	/**
	 * Returns the parsed XML
	 *
	 * @return string
	 */
	public function getContentXml()
	{
		return $this->contentXml;
	}

}
