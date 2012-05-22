<?php
session_start();
//laadt de nodige bestanden
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
if(isset($_GET['caption'])){
    if($_GET['caption'] != null){
        $table = 'portal_image'; $what ="omschrijving = '".$_GET['caption']."'";
        $where ='id ='.$_GET['foto_id'];
            $update_image = sqlUpdate($table, $what, $where);
            echo 'omschrijving ='.$_GET['caption'];
    }
}
if(isset($_GET['naam'])){
    if($_GET['naam'] != null){
        $table = 'portal_album'; $what ="naam = '".$_GET['naam']."'";
        $where ='id ='.$_GET['album'];
            $update_image = sqlUpdate($table, $what, $where);
            echo 'naam ='.$_GET['naam'];
    }
}
?>