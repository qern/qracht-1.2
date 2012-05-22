<?php    
	error_reporting(E_ALL ^ E_NOTICE);
  	ini_set('display_errors', 'on');
    #portal directories instellingen
    $siteroot = $_SERVER['DOCUMENT_ROOT'].'/'; #de siteroot die gebruikt moet worden in de php
    $site_includes = $_SERVER['DOCUMENT_ROOT'].'/includes/'; #de root die specifiek wordt gebruikt voor de inlcudes
    $site_name = 'http://qracht.qern.nl/'; #de naam van de portal
    $etc_root = '/'; #de naam voor alle onderdelen die vanaf de root beginnen maar deze niet nodig hebben (Slir)
    $key = '90heib';
	
    #de database gegevens
    $db_host = 'mysql5.qern.nl';#de host
    $db_name = 'qern_qracht';#table naam
    $db_username = 'qern_qracht';#username
    $db_pass = 'Crealist01.';#wachtwoord
    
    #let's connect met de database en als de gegevens niet kloppen: die met die boodschap
    $con = mysql_connect($db_host, $db_username, $db_pass)or die("cannot connect");
    $select = mysql_select_db($db_name, $con)or die("cannot select DB");
    //de taal van de datums in php omzetten naar nederlands
    setlocale(LC_ALL, 'nld_nld'); mysql_query("SET lc_time_names = 'nl_NL'");
    
    //de lijst met bestanden die nodig zijn:
    require("includes/functions.php");

    $gebruikers_naam = $_SESSION['gebruikersnaam']; //de naam van de ingelogde gebruiker
    $login_id = $_SESSION['login_id']; //de login_id van de ingelogde gebruiker. Te gebruiken bij bijvoorbeeld relatie_wijziging
    $naam = $_SESSION['naam'];
    $album_van_gebruiker = $_SESSION['album'];
    $gebruiker_profielfoto = $_SESSION['profielfoto'];
    $organisatie_id = $_SESSION['organisatie'];
    
    $admin_email = 'b.slob@qern.nl';
    
    /* testvariabelen 
    $login_id = 1; #voor de test
    $gebruiker_naam = 'Bram Slob';
    $gebruiker_album = 6;
    $gebruiker_email = 'bramslob@hotmail.com';
    $admin_email = 'b.slob@qern.nl';
    $_SESSION['test'] = 'test';
    */
?>