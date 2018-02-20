<?php 

//	$res = 0;
//	if (! $res && file_exists("../main.inc.php")) {
//		$res = @include("../main.inc.php");
//	}
//	if (! $res && file_exists("../../main.inc.php")) {
//		$res = @include("../../main.inc.php");
//	}
//	if (! $res && file_exists("../../../main.inc.php")) {
//		$res = @include("../../../main.inc.php");
//	}
//	if (! $res) {
//		die("Main include failed");
//	}

	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else $dir = '../../';


	if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
		include($dir."master.inc.php");
	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		include($dir."main.inc.php");
	} else {
		global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
	}

	if(!defined('DB_HOST')) {
		define('DB_HOST',$dolibarr_main_db_host);
		define('DB_NAME',$dolibarr_main_db_name);
		define('DB_USER',$dolibarr_main_db_user);
		define('DB_PASS',$dolibarr_main_db_pass);
		define('DB_DRIVER',$dolibarr_main_db_type);
	}

	if(!dol_include_once('/abricot/inc.core.php')) {
		require __DIR__.'/class/listview.class.php'; // why not ? ;)
	}