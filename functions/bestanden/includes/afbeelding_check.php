<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/portal/check_configuration.php');
if($_POST['action'] == 'reactie'){
    //als de reactie leeg is... niet de database zetten.. maar direct terug naar de activiteit.
    if($_POST['reactie'] != null){        
        //zet alle tekens om in HTML.. voor de veiligheid.
        $reactie = htmlentities($_POST['reactie'], ENT_NOQUOTES, "UTF-8");
        $reactie = nl2br($reactie);
            
        $table='portal_reactie'; 
        $what='inhoud, geschreven_op, geschreven_door';
        $with_what = "'".mysql_real_escape_string($reactie)."', ";
        $with_what .= "NOW(), $login_id";
            $voeg_reactie_toe = sqlInsert($table, $what, $with_what);
            
        $table = 'portal_image_reactie';
        $what = 'image, reactie';
        $with_what= $_POST['image_id'].', (SELECT MAX(id) FROM portal_reactie)';
            $voeg_reactie_toe_aan_nieuws = sqlInsert($table, $what, $with_what);
    }
    header('location: '.$site_name.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$_POST['image_id']);
}
elseif($_POST['action'] == 'reactie_wijzigen'){
    //als de reactie leeg is... niet de database veranderen.. maar direct terug naar de activiteit.
    if($_POST['reactie'] != null){
        //haal de werkzaamheden op die in de mail gebruikt moeten worden
        $what = 'werkzaamheden'; $from = 'planning_activiteit';
        $where= 'id ='.$_POST['activiteit_id'];
        $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
        
        //zet alle tekens om in HTML.. voor de veiligheid.
        $reactie = htmlentities($_POST['reactie'], ENT_NOQUOTES, "UTF-8");
        $reactie = nl2br($reactie);
            
        $table='planning_reactie'; 
        $what="tekst = '".$reactie."', geschreven_op = NOW(), geschreven_door = $login_id, gewijzigd_op = NOW(), gewijzigd_door = $login_id, actief = 1";
        $where = 'id = '.$_POST['reactie_id'];
            $wijzig_reactie = sqlUpdate($table, $what, $where);
    }
    header('location: '.$site_name.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$_POST['image_id']);
}
elseif($_GET['action'] == 'delete_reactie'){

    $table='portal_reactie'; 
    $what="actief = 0";
    $where = 'id = '.$_GET['reactie_id'];
            $zet_reactie_op_nonactief = sqlUpdate($table, $what, $where);
    
    header('location: '.$site_name.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$_GET['image_id']);
}
/*elseif($_POST['action'] == 'delen_check'){
    if($_POST['delen_met'] != null){
        $what = 'email'; $from = 'relaties'; $where= 'id = '.$relatie_id;
        $gedeeld_door = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        foreach($_POST['delen_met'] as $delen_met_relatie_id){
            //haal eerst even alles op
            $what = 'voornaam, achternaam, email'; $from = 'relaties'; $where= "id = $delen_met_relatie_id";
            $informatie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            $what = 'werkzaamheden'; $from = 'planning_activiteit'; $where= 'id = '.$_POST['activiteit'];
            $activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            //begin dan met het updaten van de database.
            $table = 'planning_delen'; $what = 'planning_activiteit_id, relatie_id, gedeeld_op, gedeeld_door';
            $with_what = ' '.$_POST['activiteit'].', '.$delen_met_relatie_id.', NOW(), '.$login_id;
            $insert_nieuw_deling = sqlInsert($table, $what, $with_what);
            
            if($_POST['toelichting'] != null){
                $toelichting = htmlentities($_POST['toelichting'], ENT_NOQUOTES, "UTF-8");
                $toelichting = nl2br($toelichting);
            }
            
            //om te laten zien, wie er allemaal een mailtje hebben gekregen: 
            $verstuurd_naar[] = $informatie['voornaam']." ".$informatie['achternaam'];
            
            $nu = strftime("%d %B %Y");
            //als je iets met jezelf deelt.. ziet het er iets anders uit
            if($delen_met_relatie_id == $relatie_id){

            
            $subject = "Je hebt een activiteit met jezelf gedeeld";
            $htmlbody = "
                <h3>Je wil graag een activiteit met je delen, in de qracht planningstool</h3>
                <p>Beste ".$informatie['voornaam']." ".$informatie['achternaam'].",<br />
                Op $nu heb je een activiteit gedeeld met jezelf <br />
                Klik op de volgende link om te kijken naar deze activiteit:<br />
                <a href=\"".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit']."\" title=\"ga naar deze activiteit\">".$activiteit['werkzaamheden']."</a>
                </p>";
if($toelichting != null){
            $htmlbody .= "
                <p>
                Extra toelichting:<br />
                ".$toelichting."</p>";
}
            $textbody = " 
                Beste ".$informatie['voornaam']." ".$informatie['achternaam'].", 
                Op $nu heb je een activiteit gedeeld met jezelf
                Klik op de volgende link om te kijken naar deze activiteit:"
                .$site_name."planning/activiteit-wijzigen/activiteit-id=".$_POST['activiteit'];
if($_POST['toelichting'] != null){
            $textbody .= "
                Extra toelichting:
                ".$_POST['toelichting']."";
}
                
            }else{
            
            $subject = "$naam wil graag een activiteit met je delen";
            $htmlbody = "
                <h3>$naam wil graag een activiteit met je delen, in de qracht planningstool</h3>
                <p>Beste ".$informatie['voornaam']." ".$informatie['achternaam'].",<br />
                Op $nu is er door <a href=\"".$site_name."profiel/$profielnaam\">$naam</a> een activiteit gedeeld met je <br />
                Klik op de volgende link om te kijken welke activiteit dat is:<br />
                <a href=\"".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit']."\" title=\"ga naar deze activiteit\">".$activiteit['werkzaamheden']."</a>
                </p>";
if($toelichting != null){
            $htmlbody .= "
                <p>
                Extra toelichting:<br />
                ".$toelichting."</p>";
}
            $textbody = " 
                Beste ".$informatie['voornaam']." ".$informatie['achternaam'].", 
                Op $nu is er door $naam een activiteit gedeeld met je
                 Klik op de volgende link om te kijken welke activiteit dat is:"
                .$site_name."planning/activiteit-wijzigen/activiteit-id=".$_POST['activiteit'];
if($_POST['toelichting'] != null){
            $textbody .= "
                Extra toelichting:
                ".$_POST['toelichting']."";
}
            }                                
            $mail = new PHPMailer();
            $mail->SetFrom($gedeeld_door['email'], $naam);
            $mail->AddReplyTo("info@qracht.nl","qracht"); 
            $mail->AddAddress($informatie['email']);
            
            $mail->Subject = $subject;
            $mail->Body = $htmlbody;
            $mail->AltBody= $textbody;
            $mail->WordWrap = 50; 
            if(!$mail->Send()){
                $error = 'mailer error'.$mail->ErrorInfo;
            }
        }
        $_SESSION['verstuurd_naar'] = $verstuurd_naar;
        header('location: '.$site_name.'planning/'.$_POST['refer'].'/activiteit-wijzigen/activiteit-id='.$_POST['activiteit']); 
    }
}
elseif($_GET['action'] == 'delete_activiteit'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
    
    $table='planning_activiteit';
    $what='actief = 0';
    $where='id = '.$_GET['activiteit_id'];
    $activiteit_non_actief = sqlUpdate($table, $what, $where);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                //is een reactie van de te waarschuwen gebruiker, verwijderd ? zeg dát dan.
                if($verwijderd['geschreven_door'] == $gebruikerid){$verwijderd_van = 'uw reactie';}
                elseif($verwijderd['geschreven_door'] == $login_id){$verwijderd_van = 'zijn/haar eigen reactie';}
                else{ $verwijderd_van = 'een reactie';}
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De '$werkzaamheden' is verwijderd";
                    $htmlbody = "
                    <p><b>De activiteit '$werkzaamheden' is verwijderd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' verwijderd.<br />";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door $verwijderd_van bij de activiteit '$werkzaamheden' verwijderd";
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header('location: '.$site_name.'planning/'.$_GET['refer']);
}
elseif($_GET['action'] == 'afronden'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
    
    $table='planning_activiteit';
    $what="status='done', status_datum = NOW(), gewijzigd_op=NOW(), gewijzigd_door=$login_id";
    $where='id='.$_GET['activiteit_id'];
    $afronden_activiteit = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id']);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De activiteit '$werkzaamheden' is afgerond";
                    $htmlbody = "
                    <p><b>Een nieuwe reactie bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' afgerond.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' afgerond.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/activiteit-wijzigen/activiteit-id='.$_GET['activiteit_id']);
}
elseif($_GET['action'] == 'opnieuw_plannen'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
    
    $table='planning_activiteit';
    $what="status='nog te plannen', planning_iteratie_id = 0, status_datum = NOW(), gewijzigd_op=NOW(), gewijzigd_door=$login_id";
    $where='id='.$_GET['activiteit_id'];
    $afronden_activiteit = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id']);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De activiteit '$werkzaamheden' is in 'nog te plannen' gezet";
                    $htmlbody = "
                    <p><b>Een nieuwe reactie bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in 'nog te plannen' gezet.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in 'nog te plannen' gezet.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/activiteit-wijzigen/activiteit-id='.$_GET['activiteit_id']);
}
elseif($_GET['action'] == 'html_tekst' ){ 
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
    
    $html_detail = $_POST['content'];
    
    $table="planning_activiteit";
    $what = "html_detail = '$html_detail'";
    $where = "id = '".$_GET['activiteit_id']."'";
    recentMaken($_GET['activiteit_id']);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De detailtekst voor de activiteit '$werkzaamheden' is gewijzigd";
                    $htmlbody = "
                    <p><b>De detailtekst voor de activiteit '$werkzaamheden' is gewijzigd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' de detailtekst gewijzigd.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' de detailtekst gewijzigd.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    $update_html_detail = sqlUpdate($table, $what, $where);
}
elseif($_GET['action'] == 'accept_activiteit'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
    
    $table="planning_activiteit";
    $what="acceptatie_door = $login_id";
    $where="id = ".$_GET['activiteit_id'];
    $accepteer_activiteit = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id']);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De activiteit '$werkzaamheden' is in acceptatie genomen";
                    $htmlbody = "
                    <p><b>De detailtekst voor de activiteit '$werkzaamheden' is in acceptatie genomen</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in acceptatie genomen.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in acceptatie genomen.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header("location: ".$site_name."planning");
}
elseif($_GET['action'] == 'prioriteit'){?>
 <html>
    <head>
        <link href="/css/standaard_css/main.css" rel="stylesheet" type="text/css" />
        <link href="/functions/planning/css/planning.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
                        <div id="prioriteit">
                    <p style="font-weight:700;">Wat voor prioriteit wilt u geven aan deze activiteit ?</p>
<?php
//haal alle gebruikers op die toegang hebben tot de planning, hieruit kan gekozen worden qua delen.
$what = 'prioriteit'; $from='planning_activiteit'; 
$where = "id = ".$_GET['activiteit_id'];
$activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
$active_prioriteit = 'class="active_prio"';
$inactive_prioriteit = 'class="inactive_prio" onmouseover="this.className='."'hover_prio'".'" onmouseout="this.className='."'inactive_prio'".'"';
if($activiteit['prioriteit'] == '1'){$prioriteit_1 = $active_prioriteit;}else{$prioriteit_1 = $inactive_prioriteit;}
if($activiteit['prioriteit'] == '2'){$prioriteit_2 = $active_prioriteit;}else{$prioriteit_2 = $inactive_prioriteit;}
if($activiteit['prioriteit'] == '3'){$prioriteit_3 = $active_prioriteit;}else{$prioriteit_3 = $inactive_prioriteit;}
if($activiteit['prioriteit'] == '4'){$prioriteit_4 = $active_prioriteit;}else{$prioriteit_4 = $inactive_prioriteit;}
?>
                    <div id="image_rij">
                        <div class="prio_image">                                                                                                                                                    
                            <a href="/functions/planning/includes/activiteit_check.php?action=prio_wijzigen&refer=iteratie_schema&prio=1&activiteit_id=<?php echo $_GET['activiteit_id'].'&refer='.$_GET['refer'] ?>">
                                <img src="/functions/planning/css/images/prio_1.png" alt="prio 1" <?php echo $prioriteit_1; ?> />
                            </a>
                        </div>
                        <div class="prio_image">
                            <a href="/functions/planning/includes/activiteit_check.php?action=prio_wijzigen&refer=iteratie_schema&prio=2&activiteit_id=<?php echo $_GET['activiteit_id'].'&refer='.$_GET['refer'] ?>">
                                <img src="/functions/planning/css/images/prio_2.png" alt="prio 2" <?php echo $prioriteit_2; ?> />
                            </a>
                        </div>
                        <div class="prio_image">
                            <a href="/functions/planning/includes/activiteit_check.php?action=prio_wijzigen&refer=iteratie_schema&prio=3&activiteit_id=<?php echo $_GET['activiteit_id'].'&refer='.$_GET['refer'] ?>">
                                <img src="/functions/planning/css/images/prio_3.png" alt="prio 3" <?php echo $prioriteit_3; ?> />
                            </a>
                        </div>
                        <div class="prio_image">
                            <a href="/functions/planning/includes/activiteit_check.php?action=prio_wijzigen&refer=iteratie_schema&prio=4&activiteit_id=<?php echo $_GET['activiteit_id'].'&refer='.$_GET['refer'] ?>">
                                <img src="/functions/planning/css/images/prio_4.png" alt="prio 4" <?php echo $prioriteit_4; ?> />
                            </a>
                        </div>
                    </div>
                    <div id="beschrijving_rij">
                        <div class="prio_beschrijving"> <b>Hoogste prioriteit</b><br /> Direct een blokkerend probleem. </div>
                        <div class="prio_beschrijving"> <b>Hoge prioriteit</b><br /> Op korte termijn een blokkerend probleem als we nu niets doen. </div>
                        <div class="prio_beschrijving"> <b>Normale prioriteit</b><br /> Belangrijk maar niet blokkerend. </div>
                        <div class="prio_beschrijving"> <b>Lage prioriteit</b><br /> Niet blokkerend, cosmetisch of gewoon mooi. </div>                
                    </div>
                </div>
    </body>
</html>   
    
<?}
elseif($_GET['action'] == 'prio_wijzigen'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $what));   $werkzaamheden = $info['werkzaamheden'];
    
    $table='planning_activiteit';
    $what = 'prioriteit = '.$_GET['prio'].', gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where =  'id = '.$_GET['activiteit_id'];
    $update_prio = sqlUpdate($table, $what, $where);
    //wat voor prio is het geworden ?
    if($_GET['prio'] == 1){$prioriteit = 'de hoogste prioriteit';}elseif($_GET['prio'] == 2){$prioriteit = 'de hoge prioriteit';}
    elseif($_GET['prio'] == 3){$prioriteit = 'de normale prioriteit';}elseif($_GET['prio'] == 4){$prioriteit = 'de lage prioriteit';}
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De prioriteit van de activiteit '$werkzaamheden' is gewijzigd";
                    $htmlbody = "
                    <p><b>De prioriteit van de activiteit '$werkzaamheden' is gewijzigd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de prioriteit van activiteit '$werkzaamheden' gewijzigd naar '$prioriteit'.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de prioriteit van activiteit '$werkzaamheden' gewijzigd naar '$prioriteit'.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    if($_GET['activiteit_wijzigen'] == 1){
        $_SESSION['gewijzigde_prio'] = $_GET['prio'];
        header('location: '.$site_name.'planning/'.$_GET['refer'].'/activiteit-wijzigen/activiteit-id='.$_GET['activiteit_id']);
    }else{
    echo '
        <p style="font-weight:bold;">U hebt met succes de prioriteit van deze activiteit aangepast.</p>
        <script type="text/javascript">
                parent.location.reload(true);
        </script>';
    }
}
elseif($_GET['action'] == 'delete_reactie'){
    $what = 'geschreven_door'; $from = 'planning_reactie';
    $where = 'actief = 1 AND id = '.$_GET['reactie_id'];
    $verwijderd = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    $table='planning_reactie'; 
    $what="gewijzigd_op = NOW(), gewijzigd_door = $login_id, actief = 0";
    $where = 'id = '.$_GET['reactie_id'];
            $zet_reactie_op_nonactief = sqlUpdate($table, $what, $where);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                //is een reactie van de te waarschuwen gebruiker, verwijderd ? zeg dát dan.
                if($verwijderd['geschreven_door'] == $gebruikerid){$verwijderd_van = 'uw reactie';}
                elseif($verwijderd['geschreven_door'] == $login_id){$verwijderd_van = 'zijn/haar eigen reactie';}
                else{ $verwijderd_van = 'een reactie';}
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De prioriteit van de activiteit '$werkzaamheden' is gewijzigd";
                    $htmlbody = "
                    <p><b>De prioriteit van de activiteit '$werkzaamheden' is gewijzigd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door $verwijderd_van bij de activiteit '$werkzaamheden' verwijderd.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door $verwijderd_van bij de activiteit '$werkzaamheden' verwijderd.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom('planning@qracht.nl'," qracht Planning tool ");
                    $mail->AddReplyTo("support@qern.nl","qracht"); 
                    $mail->AddAddress('bramslob@hotmail.com');
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/activiteit-wijzigen/activiteit-id='.$_GET['activiteit_id']);
}
elseif($_GET['action'] == 'volgen'){
    $table='planning_waarschuw_mij'; $what = 'planning_activiteit_id, gebruiker_id, gewijzigd_op';
    $with_what =  $_GET['activiteit_id'].', '.$login_id.', NOW()';
    $nieuwe_waarschuw_mij_aanmaken = sqlInsert($table, $what, $with_what);
        
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
                    
                $subject = "Een wijziging op het fobeco CRM bij '".$_POST['voornaam'].' '.$_POST['achternaam']."'";
                $htmlbody = "
                <h1>Een wijziging op het fobeco CRM bij '".$_POST['voornaam'].' '.$_POST['achternaam']."'</h1>
                <p>Beste $voornaam $achternaam,<br />
                Op $nu is er door $gewijzigd_door een wijziging doorgevoerd voor ".$_POST['voornaam'].' '.$_POST['achternaam'].".<br />
                Klik op de volgende link om te kijken wat er gewijzigd is.<br />
                <a href=\"http://qracht.nl/index.php?function=crm&view=organisaties&organisatie_id=$organisatie_id\" title=\"ga naar ".$_POST['voornaam'].' '.$_POST['achternaam']."\">Bekijk de wijzigingen</a></p>";
                    
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
    header();
}*/?>
