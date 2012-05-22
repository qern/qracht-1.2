<?php
    #de siteroot die gebruikt moet worden in de php
    $siteroot = $_SERVER['DOCUMENT_ROOT'];
    
    #de root die specifiek wordt gebruikt voor de inlcudes
    $site_includes = $_SERVER['DOCUMENT_ROOT'].'/includes/';    
    
    #de naam van de site
    $site_name = 'http://www.qracht.nl/';
    
    #de database gegevens:
    #de host
    $db_host = 'mysql5.qracht.nl';
    #table naam
    $db_name = 'qracht';
    #username
    $db_username = 'qracht';
    #wachtwoord
    $db_pass = 'Crealist01.';
    #let's connect met de database en als de gegevens niet kloppen: die met die boodschap
    $con = mysql_connect($db_host, $db_username, $db_pass)or die("cannot connect");
    $select = mysql_select_db($db_name, $con)or die("cannot select DB");
    //de taal van de datums in php omzetten naar nederlands
    setlocale(LC_ALL, 'nld_nld');
    mysql_query("SET lc_time_names = 'nl_NL'");
    
    //de lijst met bestanden die nodig zijn:
    require($site_includes."functions.php");
    if($_COOKIE['ingelogd'] != null){require($site_includes."is_ingelogd.php");}
    elseif($_SESSION['relatie_id'] != null){require($site_includes."is_ingelogd.php");}
    elseif($_POST['gebruikersnaam'] != null){require($site_includes."is_ingelogd.php");}
	elseif($_SESSION['relatie_id'] ==  null ){header("location:$site_naam/inloggen.php");}
    else{require($site_includes."is_ingelogd.php");}
    
    $relatie_id = $_SESSION['relatie_id']; //de relatie id van de ingelogde gebruiker
    $gebruikers_naam = $_SESSION['gebruiker_naam']; //de naam van de ingelogde gebruiker
    $login_id = $_SESSION['login_id']; //de login_id van de ingelogde gebruiker. Te gebruiken bij bijvoorbeeld relatie_wijziging
    $naam = $_SESSION['naam'];
    
    if($_GET['config'] != 'check'){
        include $site_includes.'dashboard.php';    
    }
    
?>