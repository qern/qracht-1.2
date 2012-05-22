<?php
if($_SESSION['login_id'] != null){
    $what ='
    a.id,  a.gebruikersnaam, a.wachtwoord,  a.voornaam, a.achternaam,
    a.email, a.is_admin, a.profielfoto, a.organisatie, b.album,
    c.startpagina';
    $from = '
    portal_gebruiker a
    LEFT JOIN portal_gebruiker_album AS b ON (b.gebruiker = a.id)
    LEFT JOIN portal_gebruiker_instellingen AS c ON (c.gebruiker = a.id)';
    $where = "
    a.id='".$_SESSION['login_id']."' AND a.actief = 1";
    $aantal_gebruikers = countRows($what, $from, $where);

    // klopt het dat er maar 1 row is die deze gebruikersnaam en wachtwoord heeft ? 
    if($aantal_gebruikers == 1) {
        $row = mysql_fetch_assoc(sqlSelect($what,$from,$where));
    
        $_SESSION['login_id'] = $row['id'];         			$_SESSION['naam'] = $row['voornaam'].' '.$row['achternaam'];
        $_SESSION['gebruikersnaam'] = $row['gebruikersnaam']; 	$_SESSION['email'] = $row['email']; 
        $_SESSION['gebruiker_album'] = $row['album'];  			$_SESSION['organisatie'] = $row['organisatie'];
		$_SESSION['startpagina'] = $row['startpagina'];

        //vul hier de mate van toegang deze gebruiker heeft:
        $_SESSION['admin'] = $row['is_admin'];
        
        //bepaal de profielfoto:
        $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$row['profielfoto'].' AND actief = 1'; 
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));                            
            //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
            if($profielfoto['path'] != null){
            	if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'])){
                		$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
				}else{ 	$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />'; }
            }else{ 		$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />'; }

    
        //als er 1 gebruiker is die deze gebruikersnaam & wachtwoord heeft, ga naar het dashboard   
        //mysql_query('INSERT INTO portal_inlog (gebruiker_id, login_tijd) VALUES('.$row['id'].', NOW())') or die(mysql_error());
                
    }//als er niet 1 gebruiker is... inloggen
    else{header('location: '.$site_name.'inloggen.php');}
    
}
elseif($_COOKIE['qern_inlog'] != null){
    $id = $_COOKIE['qern_inlog'];
    $what ='
    a.id,  a.gebruikersnaam, a.wachtwoord,  a.voornaam, a.achternaam,
    a.email, a.is_admin, a.profielfoto, a.organisatie, b.album,
    c.startpagina';
    $from = '
    portal_gebruiker a
    LEFT JOIN portal_gebruiker_album AS b ON (b.gebruiker = a.id)
    LEFT JOIN portal_gebruiker_instellingen AS c ON (c.gebruiker = a.id)';
    $where = "
    a.id='$id'  AND a.actief = 1";
    $aantal_gebruikers = countRows($what, $from, $where);

    // klopt het dat er maar 1 row is die deze gebruikersnaam en wachtwoord heeft ? 
    if($aantal_gebruikers == 1) {
        $row = mysql_fetch_assoc(sqlSelect($what,$from,$where));
        setcookie('qern_inlog', $id, time()+2678400, $etc_root);
       
        $_SESSION['login_id'] = $row['id'];         			$_SESSION['naam'] = $row['voornaam'].' '.$row['achternaam'];
        $_SESSION['gebruikersnaam'] = $row['gebruikersnaam']; 	$_SESSION['email'] = $row['email']; 
        $_SESSION['gebruiker_album'] = $row['album'];  			$_SESSION['organisatie'] = $row['organisatie'];
		$_SESSION['startpagina'] = $row['startpagina'];

        //vul hier de mate van toegang deze gebruiker heeft:
        $_SESSION['admin'] = $row['is_admin'];
        
        //bepaal de profielfoto:
        $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$row['profielfoto'].' AND actief = 1'; 
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));                            
            //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
            if($profielfoto['path'] != null){
            	if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'])){
                		$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
				}else{ 	$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />'; }
            }else{ 		$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />'; }
            
        //als er 1 gebruiker is die deze gebruikersnaam & wachtwoord heeft, ga naar het dashboard   
        //mysql_query('INSERT INTO portal_inlog (gebruiker_id, login_tijd) VALUES('.$row['id'].', NOW())') or die(mysql_error());
                
    }//als er niet 1 gebruiker is... inloggen
    else{header('location: '.$site_name.'inloggen.php');}
}
elseif($_POST['gebruikersnaam'] != null){
    $wachtwoord = md5($_POST['wachtwoord']);
    // Om sql injection tegen te gaan
    $gebruikersnaam =  mysql_escape_string(stripslashes($_POST['gebruikersnaam']));
    //$wachtwoord = mysql_escape_string(stripslashes());
    // pak onderstaande velden uit gebruikers en zet het id en het autorisatie niveau in een session
    $what ='
    a.id,  a.gebruikersnaam, a.wachtwoord,  a.voornaam, a.achternaam,
    a.email, a.is_admin, a.profielfoto, a.organisatie, b.album,
    c.startpagina';
    $from = '
    portal_gebruiker a
    LEFT JOIN portal_gebruiker_album AS b ON (b.gebruiker = a.id)
    LEFT JOIN portal_gebruiker_instellingen AS c ON (c.gebruiker = a.id)';
    $where = "
    a.gebruikersnaam='$gebruikersnaam'  AND a.wachtwoord='$wachtwoord'
    AND a.actief = 1";
    $aantal_gebruikers = countRows($what, $from, $where);
    
    
    // klopt het dat er maar 1 row is die deze gebruikersnaam en wachtwoord heeft ? 
    if($aantal_gebruikers == 1) {
        $row = mysql_fetch_assoc(sqlSelect($what, $from, $where));    
        
		//kijk of er urentool datums moeten worden bijgemaakt voor deze gebruiker
		checkUrentoolDatums( $row['id'] );
		
        //als er is aangegeven dat de gegevens onthouden moeten worden, maak dan een cookie aan.
        if($_POST['cookie'] == 'onthouden'){ setcookie('qern_inlog', $row['id'], time()+2678400, $etc_root); }                             
        
        $_SESSION['login_id'] = $row['id'];         			$_SESSION['naam'] = $row['voornaam'].' '.$row['achternaam'];
        $_SESSION['gebruikersnaam'] = $row['gebruikersnaam']; 	$_SESSION['email'] = $row['email']; 
        $_SESSION['gebruiker_album'] = $row['album'];  			$_SESSION['organisatie'] = $row['organisatie'];
		$_SESSION['startpagina'] = $row['startpagina'];
        
        //bepaal de profielfoto:
        $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$row['profielfoto'].' AND actief = 1'; 
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));                            
            //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
            if($profielfoto['path'] != null){
            	if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'])){
                		$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
				}else{ 	$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />'; }
            }else{ 		$_SESSION['profielfoto'] =  '<img src="'.$etc_root.'lib/slir/'.slirImage(25,25).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />'; }

        //vul hier de mate van toegang deze gebruiker heeft:
        $_SESSION['admin'] = $row['is_admin'];

        //als er 1 gebruiker is die deze gebruikersnaam & wachtwoord heeft, ga naar het dashboard   
        //mysql_query('INSERT INTO portal_inlog (gebruiker_id, login_tijd) VALUES('.$row['id'].', NOW())') or die(mysql_error());
        $log_inlogtijd = sqlInsert('portal_inlog', 'gebruiker_id, login_tijd', $row['id'].', NOW()');
    }
    // klopt het niet: geef dan deze (fout)melding
    else{
        $_SESSION['error'] = 'Helaas uw gebruikersnaam en wachtwoord combinatie is niet juist.<br />Probeer het opnieuw of neem contact op met de beheerder.';
        header('location: '.$site_name.'inloggen.php');
    }
}
else{header('location: '.$site_name.'inloggen.php');}
?>
