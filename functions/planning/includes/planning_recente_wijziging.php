<?php 
  session_start();
  require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    if($_GET['gezien'] != null){
      $table= 'planning_recent'; $what='gezien = 1, gezien_op = NOW()'; $where = 'planning_activiteit_id = '.$_GET['gezien'].' AND gebruiker_id ='.$_SESSION['login_id'];
      $dit_heeft_de_gebruiker_gezien = sqlUpdate($table, $what, $where);
      if($_GET['refer'] == 'iteraties_bewerken'){
          header('location: '.$site_name.'planning/iteraties_bewerken');
      }else{
          header('location: '.$site_name.'planning');
      }
    }
    if($_GET['alles_in_iteratie'] != null){
        $what = 'id'; $from = 'planning_activiteit'; 
        $where = 'planning_iteratie_id = '.$_GET['alles_in_iteratie']." AND status != 'done' AND actief = 1";
            $result = sqlSelect($what, $from, $where);
        
        while($row = mysql_fetch_array($result)){
            $table = 'planning_recent'; $what = 'gezien = 1, gezien_op = NOW()'; $where = 'gebruiker_id = '.$login_id.' AND planning_activiteit_id = '.$row['id'];
            $zet_alle_activiteiten_op_gelezen = sqlUpdate($table, $what, $where);
        }
        header('location: '.$site_name.'planning');
    }
  
?>