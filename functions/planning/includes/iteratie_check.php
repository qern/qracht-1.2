<?php
session_start();
require_once ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
//moet er een iteratie worden geactiveerd ?
if($_GET['action'] == 'activeer_iteratie'){
    //zet de huidige iteratie op non-actief
    $table="planning_iteratie";  $what="actief = 0, huidige_iteratie = 0";  $where="huidige_iteratie = 1";
        $unset_oude_iteratie = sqlUpdate($table,$what,$where);
    
    //en maak deze iteratie de huidige.
    $table="planning_iteratie";  $what="huidige_iteratie = 1";  $where="id=".$_GET['iteratie_id'];
        $set_nieuwe_iteratie = sqlUpdate($table,$what,$where);
}
//moet er een iteratie worden verwijderd ?
elseif($_GET['action'] == 'verwijder_iteratie'){
    //dan moeten de activiteiten in deze iteratie naar nog te plannen
    $table="planning_activiteit";  $what="status = 'nog te plannen'";  $where ="planning_iteratie_id = ".$_GET['iteratie_id'];
        $gelinkte_activiteiten_nog_laten_plannen = sqlUpdate($table, $what, $where);
    
    //en mag de iteratie worden verwijderd.
    $table="planning_iteratie";  $what="actief = 0";  $where="id=".$_GET['iteratie_id'];
        $unset_oude_iteratie = sqlUpdate($table,$what,$where);
}
//moet er een iteratie worden toegevoegd ?
elseif(isset ($_POST['iteratie_datum'])){
    //er is toch wel een datum ingevuld!
    if($_POST['iteratie_datum'] == null){
        $error['geen_datum'] = 'u hebt geen datum ingegeven voor de iteratie.';
        $_SESSION['iteratie_toelichting'] = $_POST['iteratie_toelichting']; //dit doe ik zodat ze alleen de datum hoeven in te vullen...de toelichting, wordt onthouden
    }
    if(!$error){//geen fouten ? dan inserten maar !
        $table="planning_iteratie"; $what="datum, toelichting, actief, geldig, gewijzigd_op, gewijzigd_door";
        $with_what="str_to_date('".$_POST['iteratie_datum']."', '%d-%m-%Y'), '".$_POST['iteratie_toelichting']."', 1, 1, NOW(), $login_id";
            $insert_iteratie = sqlInsert($table, $what, $with_what);
    }
}
header("location: ".$site_name."planning/iteraties"); //en terug.
?>
