<?php 

	$res = 0;
	if (! $res && file_exists("../main.inc.php")) {
		$res = @include("../main.inc.php");
	}
	if (! $res && file_exists("../../main.inc.php")) {
		$res = @include("../../main.inc.php");
	}
	if (! $res && file_exists("../../../main.inc.php")) {
		$res = @include("../../../main.inc.php");
	}
	if (! $res) {
		die("Main include failed");
	}
	
	require __DIR__.'/class/listview.class.php'; // why not ? ;)