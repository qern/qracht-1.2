<?php 
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
  
    parse_str($_REQUEST['afbeelding'], $sort_afbeelding);
    foreach($sort_afbeelding['afbeelding'] as $volgorde => $id){
        $volgorde++;
        $table="portal_image";
        $what = "volgorde = $volgorde";
        $where = "id = $id";
            $update_project_volgorde = sqlUpdate($table, $what, $where);
        echo "UPDATE $table SET $what WHERE $where".'<br />';
    }
    
?>