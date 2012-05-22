<?php 
    session_start();
    //include de benodigde configuration
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    if($_GET['action'] == 'changeStartpage'){
    	$table = 'portal_gebruiker_instellingen'; $what = "startpagina = '".$_GET['startpagina']."', gewijzigd_door = $login_id, gewijzigd_op = NOW()"; $where = 'gebruiker = '.$_GET['gebruiker'];
			$changeStartpage = sqlUpdate($table, $what, $where);
			echo 'Startpagina succesvol gewijzigd';
    }
?>