<?php
session_start();
/*
* Deze php heeft als functie om de crm gegevens te wijzigen/aan te maken.
* Dit is de check om een organisatie te wijzigen/aan te maken.
* Als de check klaar is, gaan ze terug naar het formulier
* 
* Copyright qern internet professionals 2010-2011
* Mail: b.slob(at)qern(dot)nl
*/
require($_SERVER['DOCUMENT_ROOT']."/check_configuration.php");
require($_SERVER['DOCUMENT_ROOT'].$etc_root."lib/phpmailer/class.phpmailer.php");
//wat is de inlognaam van de persoon die dit ding wijzigt/aanmaakt
if($_POST['actie'] =='aanmaken'){

    //er dient op z'n minst een naam te zijn & een branche
    if($_POST['naam'] == null){ $error['lege_naam'] = 'U dient een bedrijfsnaam in te voeren !'; }
    if($_POST['branche'] == null){ $error['lege_branche'] .= 'U dient een branche aan te geven !'; }
    
    if(!$error){
        //alle gegevens zijn ontvangen. Er wordt gecheckt of er een bedrijfsnaam is ingevuld. Zo niet.. dan is de bedrijfsnaam onbekend..
        $table = "organisatie";
        $what = "naam, omschrijving, branche_id, kvk_nummer, btw_nummer, badres, bpostcode, bplaats, padres, ppostcode, pplaats, land, telefoonnummer, faxnummer, email, website, actief, aangemaakt_op, aangemaakt_door";
        $with_what = "'".$_POST['naam']."','";
        $with_what .= $_POST['omschrijving']."','";
        $with_what .= $_POST['branche']."','";
        $with_what .= $_POST['kvk_nummer']."','";
        $with_what .= $_POST['btw_nummer']."','";
        $with_what .= $_POST['badres']."','";
        $with_what .= $_POST['bpostcode']."','";
        $with_what .= $_POST['bplaats']."','";
        $with_what .= $_POST['padres']."','";
        $with_what .= $_POST['ppostcode']."','";
        $with_what .= $_POST['pplaats']."','";
        $with_what .= $_POST['land']."','";
        $with_what .= $_POST['telefoonnummer']."','";
        $with_what .= $_POST['faxnummer']."','";
        $with_what .= $_POST['email']."','";
        $with_what .= $_POST['website']."',";
        $with_what .= "1, NOW(),";
        $with_what .= $login_id."";
        //echo "INSERT INTO $table ($what) VALUES $with_what";
        $insert_organisatie = sqlInsert($table,$what,$with_what);
        $what = 'MAX(id) AS id'; $from = 'organisatie'; $where = 'actief = 1';
            $organisatie = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $organisatie_id = $organisatie['id'];
    }else{
        $_SESSION['error'] = $error;
        $_SESSION['naam'] = $_POST['naam'];             $_SESSION['omschrijving'] = $_POST['omschrijving'];
        $_SESSION['branche'] = $_POST['branche'];       $_SESSION['website'] = $_POST['website'];
        $_SESSION['kvk_nummer'] = $_POST['kvk_nummer']; $_SESSION['btw_nummer'] = $_POST['btw_nummer'];
        $_SESSION['badres'] = $_POST['badres'];         $_SESSION['bpostcode'] = $_POST['bpostcode'];
        $_SESSION['bplaats'] = $_POST['bplaats'];       $_SESSION['padres'] = $_POST['padres']; 
        $_SESSION['ppostcode'] = $_POST['ppostcode'];   $_SESSION['pplaats'] = $_POST['pplaats']; 
        $_SESSION['faxnummer'] = $_POST['faxnummer'];   $_SESSION['land'] = $_POST['land']; 
        $_SESSION['email'] = $_POST['email'];           $_SESSION['telefoonnummer'] = $_POST['telefoonnummer']; 
    }
          
}
elseif($_POST['actie'] == 'wijzigen'){
    $organisatie_id = $_POST['organisatie_id'];
    //er dient op z'n minst een naam te zijn & een branche
    if($_POST['naam'] == null){ $error['lege_naam'] = 'U dient een bedrijfsnaam in te voeren !'; }
    if($_POST['branche'] == null){ $error['lege_branche'] .= 'U dient een branche aan te geven !'; }
    
    if(!$error){        
        //alle gegevens zijn ontvangen en in een variabele gestopt.
        $table = "organisatie";
        $what  = "naam='".$_POST['naam']."',";
        $what .= "omschrijving = '".$_POST['omschrijving']."',";
        $what .= "branche_id = '".$_POST['branche']."',";
        $what .= "kvk_nummer = '".$_POST['kvk_nummer']."',";
        $what .= "btw_nummer = '".$_POST['btw_nummer']."',";
        $what .= "badres = '".$_POST['badres']."',";
        $what .= "bpostcode = '".$_POST['bpostcode']."',";
        $what .= "bplaats = '".$_POST['bplaats']."',";
        $what .= "padres = '".$_POST['padres']."',";
        $what .= "ppostcode = '".$_POST['ppostcode']."',";
        $what .= "pplaats = '".$_POST['pplaats']."',";
        $what .= "land = '".$_POST['land']."',";
        $what .= "telefoonnummer = '".$_POST['telefoonnummer']."',";
        $what .= "faxnummer = '".$_POST['faxnummer']."',";
        $what .= "email = '".$_POST['email']."',";
        $what .= "website = '".$_POST['website']."',";
        $what .= "gewijzigd_op = NOW(),";
        $what .= "gewijzigd_door = $login_id";
        $where = "id = $organisatie_id";
        $update_organisatie = sqlUpdate($table,$what,$where);
        
        $what= "gebruiker_id";
        $from = "waarschuw_mij";
        $where = "organisatie_id ='$organisatie_id'";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
        
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what=" voornaam,
                            achternaam,
                            email";
                    $from=" relaties ";
                    $where="id = '$gebruikerid'
                            AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where);
                    $row1 = mysql_fetch_array($result1);
                
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $login_row['voornaam'].' '.$login_row['achternaam'];
                    $gebruik_id = $row1['id'];
                    $nu = strftime("%d %B %Y");
            
                    $from = "$gewijzigd_door@crm.fobeco.nl";
                    $subject = "Een wijziging op het fobeco CRM bij '".$_POST['naam']."'";
                    $htmlbody = "<h1>Een wijziging op het fobeco CRM bij '".$_POST['naam']."'</h1>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu is er door $gewijzigd_door een wijziging doorgevoerd voor ".$_POST['naam'].".<br />
                    Klik op de volgende link om te kijken wat er gewijzigd is.<br />
                    <a href=\"http://qracht.nl/index.php?function=crm&view=organisaties&organisatie_id=$organisatie_id\" title=\"ga naar ".$_POST['naam']."\">Bekijk de wijzigingen</a>
                    </p>";
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu is er door $gewijzigd_door een wijziging doorgevoerd voor ".$_POST['naam'].".
                    Klik op de volgende link om te kijken wat er gewijzigd is.
                    http://qracht.nl/index.php?function=crm&view=organisaties&organisatie_id=$organisatie_id";
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('crm@qracht.nl'," qracht CRM ");
                    $mail->AddReplyTo("info@qracht.nl","qracht"); 
                    $mail->AddAddress($row1['email']);
                            
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }   
                                            
                }//einde check of geadresseerde niet wijzigaar is.
            }//einde while
        }//einde if waarschuwingen
    }else{
        $_SESSION['organisatie_error'] = $error;
        $_SESSION['naam'] = $_POST['naam'];             $_SESSION['omschrijving'] = $_POST['omschrijving'];
        $_SESSION['branche'] = $_POST['branche'];       $_SESSION['website'] = $_POST['website'];
        $_SESSION['kvk_nummer'] = $_POST['kvk_nummer']; $_SESSION['btw_nummer'] = $_POST['btw_nummer'];
        $_SESSION['badres'] = $_POST['badres'];         $_SESSION['bpostcode'] = $_POST['bpostcode'];
        $_SESSION['bplaats'] = $_POST['bplaats'];       $_SESSION['padres'] = $_POST['padres']; 
        $_SESSION['ppostcode'] = $_POST['ppostcode'];   $_SESSION['pplaats'] = $_POST['pplaats']; 
        $_SESSION['faxnummer'] = $_POST['faxnummer'];   $_SESSION['land'] = $_POST['land']; 
        $_SESSION['email'] = $_POST['email'];           $_SESSION['telefoonnummer'] = $_POST['telefoonnummer']; 
    }
}
//DE UITEINDELIJKE AFHANDELING. ALS ER GEEN FOUTMELDINGEN ZIJN, MAAK DAN
if(!$error){ header('location: '.$site_name.'crm/detail/organisatie-id='.$organisatie_id); }
else{ header('location: '.$site_name.'crm/organisatie-toevoegen'); } 
?>
