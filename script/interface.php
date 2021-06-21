<?php
if (!defined("NOCSRFCHECK")) define("NOSCRFCHECK");

if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disable token renewal

require '../config.php';
require_once '../class/referenceletters.class.php';
require_once '../class/referenceletterschapters.class.php';
require_once '../class/html.formreferenceletters.class.php';
require_once '../lib/referenceletters.lib.php';

$get=GETPOST('get', 'none');
$set=GETPOST('set', 'none');

switch ($get) {
	default:
		break;
}

switch ($set) {
    case 'sortChapter':
            $Tjson = array(
            'errors' => 0
            ,'saved' => 0
            ,'message' => ''
        );
	    //$object_id = GETPOST('object_id', 'none');
	    $roworder  = GETPOST('roworder', 'none');

	    $TOrder = explode(',', $roworder);
	    if(is_array($TOrder)){
	        $TOrder = array_map('intval', $TOrder);

	        $sort_order = 0;
	        foreach ($TOrder as $id)
	        {
	            $sort_order++;

	            $object_chapters = new ReferenceLettersChapters($db);
	            if($object_chapters->fetch($id)>0)
	            {
	                $object_chapters->sort_order = $sort_order;
	                if($object_chapters->update($user)>0){
	                    $Tjson['saved']++;
	                }
	                else {
	                    $Tjson['errors']++;
	                }
	            }

	        }
	    }

	    print json_encode($Tjson);
	    exit();

		break;
	case 'content':
	    $id=GETPOST('id', 'none');
	    $type=GETPOST('type', 'none');
	    $content=GETPOST('content','none');
	    $Tjson = array(
	        'status' => 0
	        ,'message' => ''
	    );

	    //$Tjson = array_merge($Tjson, $_POST);
	    if (!empty($user->rights->referenceletters->write))
	    {
    	    if( $type == 'chapter_text'){
    	        $object_chapters = new ReferenceLettersChapters($db);
    	        if($object_chapters->fetch($id)>0)
    	        {
    	            $object_chapters->content_text = $content;
    	            if($object_chapters->update($user)>0)
    	            {
    	                $Tjson['status'] = true;
    	            }
    	        }else{
    	            $Tjson['message'] = 'NotFound';
    	        }
    	    }
    	    elseif( $type == 'header'){

    	    }
    	    elseif( $type == 'footer'){

    	    }
	    }

	    print json_encode($Tjson);
	    exit();

		break;
	case 'setfield':

		$Tjson = array(
			'status' => 0
			,'message' => ''
		);

		if (!empty($user->rights->referenceletters->write))	{
			$id=GETPOST('id','int');
			$field=GETPOST('field','alpha');
			$value=GETPOST('value','int');
			$object_chapters = new ReferenceLettersChapters($db);
			if($object_chapters->fetch($id)>0)
			{
				$object_chapters->$field = $value;

				if($object_chapters->update($user)>0) {
					$Tjson['status'] = true;
				} else {
					$Tjson['message'] = $object_chapters->error;
				}
			}
		}

		print json_encode($Tjson);
		exit();
		break;
	default:
		break;
}
