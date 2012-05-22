<?php 
    session_start();
    require($_SERVER['DOCUMENT_ROOT']."/check_configuration.php");
    $huidig_iteratie_id = $_REQUEST['huidig_iteratie_id'];
    
    if($_REQUEST['nog_te_plannen'] != null){
        parse_str($_REQUEST['nog_te_plannen'], $sort_nogteplannen);
        foreach($sort_nogteplannen['activiteit'] as $volgorde => $id){
             list($activiteit_id, $bron) = explode('x', $id);
             $table="planning_activiteit";
             if($bron != 'nogteplannen'){
                $nogteplannen = true;
                $what = "status = 'nog te plannen', iteratie = 0, iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 ";
                echo recentMaken($id);
             }else{ $what = "status = 'nog te plannen', iteratie = 0, iteratie_volgorde = $volgorde, gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 "; }
             $where = "id = '$id'";
             $update_planning = sqlUpdate($table, $what, $where);
        }
        if($nogteplannen){echo 'nog_te_plannen,'.$nogteplannen;}
    }
    if($_REQUEST['huidige_iteratie']  != null){
        parse_str($_REQUEST['huidige_iteratie'], $sort_huidige);
        foreach($sort_huidige['activiteit'] as $volgorde => $id){
             list($activiteit_id, $bron) = explode('x', $id);
             $table="planning_activiteit";
             if($bron != 'huidig'){
                $huidig = true;
                $what = "status = 'to do', iteratie = $huidig_iteratie_id, iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 ";
                echo recentMaken($id);
             }else{ $what = "status = 'to do', iteratie = $huidig_iteratie_id, iteratie_volgorde = $volgorde, gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 ";  }
             $where = "id = '$id'";
             $update_planning = sqlUpdate($table, $what, $where);
        }
        if($huidig){echo 'huidige_iteratie,'.$huidig;}
    }
$what = "id, DATE_FORMAT(datum, '%d %M %Y') AS datum";
$from= "planning_iteratie";
$where="actief = 1 AND huidige_iteratie = 0";
    $huidige_iteratie = sqlSelect($what, $from, $where);
while($overige_iteratie = mysql_fetch_array($huidige_iteratie)){
    
$iteratie_id = $overige_iteratie['id'];

if($_REQUEST["iteratie_id_$iteratie_id"]  != null){
        parse_str($_REQUEST["iteratie_id_$iteratie_id"], $sort_overige);
        foreach($sort_overige['activiteit'] as $volgorde => $id){
             list($activiteit_id, $bron) = explode('x', $id);
             $table="planning_activiteit";
             if($bron != 'overig'){
                $overig = true;
                $what = "status = 'to do', iteratie = $iteratie_id, iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), gewijzigd_door = $login_id, in_behandeling_door = 0 ";
                echo recentMaken($id);
             }else{ $what = "status = 'to do', iteratie = $iteratie_id, iteratie_volgorde = $volgorde, gewijzigd_op = NOW(), gewijzigd_door = $login_id, in_behandeling_door = 0 "; }
             $where = "id = '$id' ";
             $update_planning = sqlUpdate($table, $what, $where);
        }
    }
    if($overig){echo 'overig,'.$overig;}
}
?>
