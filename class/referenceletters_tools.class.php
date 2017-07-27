<?php

class RfltrTools {
	
	static function setImgLinkToUrl($txt) {
		
		return strtr($txt, array('src="'.dol_buildpath('viewimage.php', 1) => 'src="'.dol_buildpath('viewimage.php', 2), '&amp;'=>'&'));
		
	}
	
}