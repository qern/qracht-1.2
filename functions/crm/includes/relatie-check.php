<?php   
session_start();
/*
* Deze php heeft als functie om de crm gegevens te wijzigen/aan te maken.
* Dit is de check om een relatie te wijzigen/aan te maken.
* Als de check klaar is, gaan ze terug naar het formulier
* 
* Copyright qern internet professionals 2010-2011
* Mail: b.slob(at)qern(dot)nl
*/
unset($_SESSION['naam'], $_SESSION['omschrijving'], $_SESSION['branche'], $_SESSION['website'], $_SESSION['btw_nummer'], $_SESSION['bpostcode'], $_SESSION['telefoonnummer'], $_SESSION['faxnummer'], $_SESSION['land'], $_SESSION['email'], $_SESSION['pplaats'], $_SESSION['ppostcode'], $_SESSION['padres'], $_SESSION['bplaats'], $_SESSION['bpostcode'],  $_SESSION['bpostcode'], $_SESSION['badres'], $_SESSION['kvk_nummer']);
require($_SERVER['DOCUMENT_ROOT']."/check_configuration.php");
require($_SERVER['DOCUMENT_ROOT'].$etc_root."lib/phpmailer/class.phpmailer.php");

//MOET ER EEN RELATIE WORDEN AANGEMAAKT ?
if($_POST['actie'] =='aanmaken'){    
    //alle gegevens zijn ontvangen. Er wordt gecheckt of er een bedrijfsnaam is ingevuld.
    //als er niets is ingevuld of er is geen bedrijf met een dergelijke naam, geef een fout terug.
    //anders vul een variabele om later te kunnen inserten.
    
    if($_POST['voornaam'] == null){ $error['voornaam leeg'] = 'Een relatie dient minstens een voornaam te hebben.'; }
    if($_POST['bedrijfsnaam'] == null){ $error['bedrijf_selecteren'] = 'U moet een bedrijf selecteren bij een nieuwe relatie.'; }
    else{
        $what = 'id'; $from = 'organisatie'; $where = "naam LIKE '%".$_POST['bedrijfsnaam']."%' ";
        	$aantal_bedrijven = countRows($what, $from, $where);
    
    	if($aantal_bedrijven == 1){ $bedrijf = mysql_fetch_assoc(sqlSelect($what,$from,$where)); }
	    else{ $error['geen_bedrijven'] = 'Er zijn geen bedrijven bekend met een naam die lijkt op '.$_POST['bedrijfsnaam'].'.'; }
    }
    
    //als er geen fouten zijn, dan pas gaan we alles in de datbase zetten.
    if($error == null){
        $table = "relaties";
        $what = "voornaam, achternaam, adres, postcode, plaats, land, telefoonnummer, mobiel, email, website, geslacht, aangemaakt_op, aangemaakt_door, actief";
        $with_what = "'".$_POST['voornaam']."','";
        $with_what .= $_POST['achternaam']."','";
        $with_what .= $_POST['adres']."','";
        $with_what .= $_POST['postcode']."','";
        $with_what .= $_POST['plaats']."','";
        $with_what .= $_POST['land']."','";
        $with_what .= $_POST['telefoonnummer']."','";
        $with_what .= $_POST['mobiel']."','";
        $with_what .= $_POST['email']."','";
        $with_what .= $_POST['website']."','";
        $with_what .= $_POST['geslacht']."',";
        $with_what .= "NOW(),'";
        $with_what .= $login_id."',";
        $with_what .= "1";
        	$insert_relaties = sqlInsert($table,$what,$with_what);
        
        $what= 'MAX(id) AS id'; $from = 'relaties'; $where='actief = 1';
			$relatie = mysql_fetch_array(sqlSelect($what, $from, $where)); $relatie_id = $relatie['id'];
			
        $table="relatie_organisatie";
        $what ="relatie, organisatie";
        $with_what = $relatie_id.",".$bedrijf['id'];
        	$insert_relatie_organisatie_link = sqlInsert($table, $what, $with_what);
    
        //als de functie_titel is ingevuld, moet de tabel functie worden aangevuld met de functie is aangegeven.
        if($_POST['functie_titel'] != null){
            $table = "functie";
            $what = "id, titel, omschrijving";
            $with_what = $_POST['functie_titel']."','".$_POST['functie_omschrijving']."'";
            	$insert_functie = sqlInsert($table,$what,$with_what); //voer de insert uit
            
            $table="relatie_organisatie";
            $what = "functie = (SELECT max(a.id) FROM functie a WHERE 1 = 1 )";
            $where = 'relatie = '.$relatie_id;
            	$update_relaties = sqlUpdate($table, $what, $where);
        }
    }else{
        $_SESSION['relatie_error'] = $error;
        $_SESSION['voornaam'] = $_POST['voornaam'];             $_SESSION['achternaam'] = $_POST['achternaam'];
        $_SESSION['geslacht'] = $_POST['branche'];              $_SESSION['website'] = $_POST['website'];
        $_SESSION['adres'] = $_POST['adres'];                   $_SESSION['postcode'] = $_POST['postcode'];
        $_SESSION['plaats'] = $_POST['plaats'];                 $_SESSION['land'] = $_POST['land']; 
        $_SESSION['mobiel'] = $_POST['mobiel'];                 $_SESSION['telefoonnummer'] = $_POST['telefoonnummer'];
        $_SESSION['email'] = $_POST['email'];                   $_SESSION['bedrijf'] = $_POST['bedrijf'];
        $_SESSION['functie_titel'] =$_POST['functie_titel'];    $_SESSION['functie_omschrijving'] = $_POST['functie_omschrijving'];
    }        
}
//MOET ER EEN RELATIE WORDEN GEWIJZIGD ?
elseif($_POST['actie'] == 'wijzigen'){
    
    $relatie_id = $_POST['relatie_id'];//het relatie_id is meegestuurd, stop deze in een eenvoudig te gebruiken variabele.
    $functie_id = $_POST['functie_id'];//het functie_id is meegestuurd, indien aanwezig,  stop deze in een eenvoudig te gebruiken variabele.

    //alle gegevens zijn ontvangen. Er wordt gecheckt of er een bedrijfsnaam is ingevuld.
    //als er niets is ingevuld of er is geen bedrijf met een dergelijke naam, geef een fout terug.
    //anders update de tabel met de nieuwe info.
    if($_POST['voornaam'] == null){ $error['voornaam leeg'] = 'Een relatie dient minstens een voornaam te hebben.'; }
    if($_POST['bedrijfsnaam'] == null){ $error['bedrijf_selecteren'] = 'U moet een bedrijf selecteren bij een relatie.';}
    else{
        $what = 'id'; $from = 'organisatie';  $where = "naam LIKE '".$_POST['bedrijfsnaam']."' ";
        $aantal_bedrijven = countRows($what, $from, $where);

        if($aantal_bedrijven == 1){
            $bedrijf = mysql_fetch_assoc(sqlSelect($what,$from,$where));
            
            $table = 'relatie_organisatie'; $what = "organisatie = '".$bedrijf['id']."'"; $where = "relatie = $relatie_id";
                $update_relatie_organisatie = sqlUpdate($table, $what, $where);
                
        }else{ $error['geen_bedrijven'] = 'Er zijn geen bedrijven bekend met een naam die lijkt op '.$_POST['bedrijfsnaam'].'.'; }
    }
    
    if($error == null){
        $table = "relaties";
        $what = "voornaam = '".mysql_real_escape_string($_POST['voornaam'])."', ";
        $what .= "achternaam = '".mysql_real_escape_string($_POST['achternaam'])."',";
        $what .= "adres = '".mysql_real_escape_string($_POST['adres'])."',";
        $what .= "postcode = '".$_POST['postcode']."',";
        $what .= "plaats = '".mysql_real_escape_string($_POST['plaats'])."',";
        $what .= "land = '".mysql_real_escape_string($_POST['land'])."',";
        $what .= "telefoonnummer = '".mysql_real_escape_string($_POST['telefoonnummer'])."',";
        $what .= "mobiel = '".mysql_real_escape_string($_POST['mobiel'])."',";
        $what .= "email = '".mysql_real_escape_string($_POST['email'])."',";
        $what .= "website = '".mysql_real_escape_string($_POST['website'])."',";
        $what .= "geslacht = '".$_POST['geslacht']."'";
        $where = "id = $relatie_id";
    		//echo "UPDATE $table SET $what WHERE $where";
            $update_relaties = sqlUpdate($table,$what,$where);
        
        //als de functie_titel is ingevuld, moet de tabel functie worden aangevuld met de functie is aangegeven.
        if($_POST['functie_titel'] != null){            

            if($functie_id != null){//is ie gevuld en was ie gevuld ? updaten
                $table = "functie";
                $what = "titel ='".$_POST['functie_titel']."',";
                $what .= "omschrijving ='".$_POST['functie_omschrijving']."'";
                $where = "id = '$functie_id'";
                	$update_functie = sqlUpdate($table,$what,$where);//voer de update uit
            }else{//is ie gevuld maar was ie leeg ? inserten
                $table="functie";
                $what = "titel, omschrijving";
                $with_what = "'".$_POST['functie_titel']."',";
                $with_what .= "'".$_POST['functie_omschrijving']."'";
                	$insert_functie = sqlInsert($table, $what, $with_what);
               
                $table="relatie_organisatie";
            	$what = "functie = (SELECT max(a.id) FROM functie a WHERE 1 = 1 )";
            	$where = "relatie = $relatie_id";
            		$update_relaties = sqlUpdate($table, $what, $where);
            }
        }
      
	  	//wie moeten er allemaal gewaarschuwd worden over de wijzigingen
        $what= "gebruiker_id";
        $from = "waarschuw_mij";
        $where = "relatie_id ='$relatie_id'";
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
                        $subject = "Een wijziging op het fobeco CRM bij '".$_POST['voornaam'].' '.$_POST['achternaam']."'";
                        $htmlbody = "<h1>Een wijziging op het fobeco CRM bij '".$_POST['voornaam'].' '.$_POST['achternaam']."'</h1>
                        <p>Beste $voornaam $achternaam,<br />
                        Op $nu is er door $gewijzigd_door een wijziging doorgevoerd voor ".$_POST['voornaam'].' '.$_POST['achternaam'].".<br />
                        Klik op de volgende link om te kijken wat er gewijzigd is.<br />
                        <a href=\"http://qracht.nl/index.php?function=crm&view=organisaties&organisatie_id=$organisatie_id\" title=\"ga naar ".$_POST['voornaam'].' '.$_POST['achternaam']."\">Bekijk de wijzigingen</a>
                        </p>";
                        $textbody = " Beste $voornaam $achternaam, 
                        Op $nu is er door $gewijzigd_door een wijziging doorgevoerd voor ".$_POST['voornaam'].' '.$_POST['achternaam'].".
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
                }
            }
        }
    }else{
        $_SESSION['relatie_error'] = $error;
        $_SESSION['voornaam'] = $_POST['voornaam'];             $_SESSION['achternaam'] = $_POST['achternaam'];
        $_SESSION['geslacht'] = $_POST['branche'];              $_SESSION['website'] = $_POST['website'];
        $_SESSION['adres'] = $_POST['adres'];                   $_SESSION['postcode'] = $_POST['postcode'];
        $_SESSION['plaats'] = $_POST['plaats'];                 $_SESSION['land'] = $_POST['land']; 
        $_SESSION['mobiel'] = $_POST['mobiel'];                 $_SESSION['telefoonnummer'] = $_POST['telefoonnummer'];
        $_SESSION['email'] = $_POST['email'];                   $_SESSION['bedrijf'] = $_POST['bedrijf'];
        $_SESSION['functie_titel'] =$_POST['functie_titel'];    $_SESSION['functie_omschrijving'] = $_POST['functie_omschrijving'];
    }
}
//DE UITEINDELIJKE AFHANDELING. ALS ER GEEN FOUTMELDINGEN ZIJN, MAAK DAN
if(!$error){ header('location: '.$site_name.'crm/detail/relatie-id='.$relatie_id); }
else{ header('location: '.$site_name.'crm/relatie-toevoegen'); } 
?>
