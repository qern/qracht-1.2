<?php
    #portal directories instellingen
    $siteroot = $_SERVER['DOCUMENT_ROOT'].'/'; #de siteroot die gebruikt moet worden in de php
    $site_includes = $_SERVER['DOCUMENT_ROOT'].'/includes/'; #de root die specifiek wordt gebruikt voor de inlcudes
    $site_name = 'http://qracht.qern.nl/'; #de naam van de portal
    $etc_root = '/'; #de naam voor alle onderdelen die vanaf de root beginnen maar deze niet nodig hebben (Slir)
    
    #de database gegevens
    $db_host = 'mysql5.qern.nl';#de host
    $db_name = 'qern_qracht';#table naam
    $db_username = 'qern_qracht';#username
    $db_pass = 'Crealist01.';#wachtwoord

    #let's connect met de database en als de gegevens niet kloppen: die met die boodschap
    $con = mysql_connect($db_host, $db_username, $db_pass)or die("cannot connect");
    $select = mysql_select_db($db_name, $con)or die("cannot select DB");
    //de taal van de datums in php omzetten naar nederlands
    setlocale(LC_ALL, 'nl_NL', 'nl_nld'); mysql_query("SET lc_time_names = 'nl_NL'");
    
    //de lijst met bestanden die nodig zijn:
    require($site_includes."functions.php");
    if($_COOKIE['qern_inlog'] != null){require($site_includes."is_ingelogd.php");}
    elseif($_SESSION['login_id'] != null){require($site_includes."is_ingelogd.php");}
    elseif($_POST['gebruikersnaam'] != null){require($site_includes."is_ingelogd.php");}
	elseif($_SESSION['login_id'] ==  null ){header('location: '.$site_name.'inloggen.php');}
    else{require($site_includes."is_ingelogd.php");}
    
    //alle informatie van de ingelogde gebruiker.
    $gebruikers_naam = $_SESSION['gebruikersnaam']; //de naam van de ingelogde gebruiker
    $login_id = $_SESSION['login_id']; //de login_id van de ingelogde gebruiker. Te gebruiken bij bijvoorbeeld relatie_wijziging
    $naam = $_SESSION['naam'];
    $album_van_gebruiker = $_SESSION['album'];
    $gebruiker_profielfoto = $_SESSION['profielfoto'];
	$organisatie_id = $_SESSION['organisatie'];
    	
    $admin_email = 'b.slob@qern.nl';
	 
  	if(isset($_GET['function'])){
  		
		if($_SESSION['admin'] > 0){ $_SESSION['functie_get'] = $_GET["function"]; }
		else{
			$servicedesk_pagina = array ('servicedesk', 'overzicht', 'inzage', 'invoer');
			if(in_array($_GET["function"], $servicedesk_pagina)){ $_SESSION['functie_get'] = $_GET["function"];}
			else{ $_SESSION['functie_get'] = 'servicedesk'; }
		}  
		$functie_get = $_SESSION['functie_get'];
	  	$pagina = $_SERVER['DOCUMENT_ROOT'].$etc_root."functions/$functie_get/index.php";
	  	$functie_includes =  $_SERVER['DOCUMENT_ROOT'].$etc_root."functions/$functie_get/includes/";
	  	if(isset($_GET['page'])){ $pagina = $functie_includes.$_GET['page'].'.php'; }
	  	
	  	$functie_js =  "functions/$functie_get/js/";
	  	$functie_css =  "functions/$functie_get/css/";
		
		//print_r($_SESSION);
		//print_r($_GET);
	  	
		//echo $pagina;
		
        include $site_includes.'dashboard.php';
  	}else{
  		if($_SESSION['startpagina'] == null){
  			if($_SESSION['admin'] > 0){ header("location: $site_name".'home'); }  
  		  	else{ header("location: $site_name".'servicedesk');}
		}else{ header("location: $site_name".$_SESSION['startpagina']); }  
	}           
?>
