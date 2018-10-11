<?php

require '../config.php';
require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';
	
$get=GETPOST('get');
$set=GETPOST('set');

switch ($get) {
	default:
		break;
}

switch ($set) {
	case 'sortChapter': 
	    var_dump($_POST);
	        
	        
		break;
	default:
		break;
}
