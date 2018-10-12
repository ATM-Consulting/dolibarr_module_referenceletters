<?php

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

global $db;

$TTables = array(
    'facture'=>'facture'
    ,'commande'=>'commande'
    ,'commande_fournisseur'=>'supplier_order'
    ,'contrat'=>'contrat'
    ,'propal'=>'propal'
    ,'societe'=>'societe'
    ,'supplier_proposal'=>'supplier_proposal'
);

foreach ($TTables as $table => $model)
{
    $sql = "SELECT t.rowid, t.model_pdf FROM " . MAIN_DB_PREFIX . $table . " as t WHERE model_pdf LIKE '%rfltr_%'";
    $resql = $db->query($sql);
    
    if ($resql && $db->num_rows($resql))
    {
        while ($obj = $db->fetch_object($resql))
        {
            $id_model = intVal(strtr($obj->model_pdf, array('rfltr_' => '')));
            
            $sql_ef = "SELECT rowid FROM ". MAIN_DB_PREFIX . $table . "_extrafields WHERE fk_object = " .$obj->rowid;
            $res_ef = $db->query($sql_ef);
            
            if ($res_ef)
            {
                if ($db->num_rows($res_ef))
                {
                    $ef = $db->fetch_object($res_ef);
                    $sql2 = "UPDATE " . MAIN_DB_PREFIX . $table . "_extrafields SET rfltr_model_id = " .$id_model . " WHERE rowid = " . $ef->rowid;
//                     var_dump($id_model, $obj->model_pdf, $sql2);
                }
                else
                {
                    $sql2 = "INSERT INTO " . MAIN_DB_PREFIX . $table . "_extrafields (fk_object, rfltr_model_id) VALUES (".$obj->rowid.", " . $id_model . ")";
                }
                
                $db->query($sql2);
                
            }
        }
        $updt = "UPDATE " . MAIN_DB_PREFIX . $table . " SET model_pdf = 'rfltr_dol_" .$model. "' WHERE model_pdf LIKE '%rfltr_%'";
        $db->query($updt);
    }
}