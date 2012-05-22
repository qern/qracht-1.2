<?php
    session_start();
    setcookie('vdspeld_ingelogd', false, time()-2678400);
    include($_SERVER['DOCUMENT_ROOT'].'/portal/check_configuration.php');
    $naam = $_SESSION['naam'];
    //als een van de volgende sessies is gezet... destroy deze dan
    if(isset($_SESSION['login_id'])){unset($_SESSION['login_id']);}
    if(isset($_SESSION['gebruikersnaam'])){unset($_SESSION['gebruikersnaam']);}
    if(isset($_SESSION['email'])){unset($_SESSION['email']);}
    if(isset($_SESSION['naam'])){unset($_SESSION['naam']);}
        
    //vernietig hier de crm toegang variabelen
    unset($_SESSION['admin']); unset($_SESSION['admin']);
    
    $_SESSION['uitgelogd'] = "<strong>Dank u voor uw bezoek aan qracht, $naam. U bent bij deze uitgelogd.</strong>";
    
    //header("location: http://qracht.nl/index.php");
	
    header('location: '.$site_name.'inloggen.php');
    
?>