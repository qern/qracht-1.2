<?php 
    session_start();
    //include de benodigde configuration
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    include($_SERVER['DOCUMENT_ROOT'].$etc_root.'lib/phpmailer/class.phpmailer.php');
    if($_POST['action'] == 'persinfo' || $_POST['action'] == 'socialinfo'){
        $what = 'a.id AS persoonlijk, b.id AS social'; $from = 'portal_gebruiker_recent a, portal_gebruiker_recent b'; 
        $where = 'a.gebruiker = '.$login_id.' AND a.wat = \'persoonlijk\' AND b.gebruiker = '.$login_id.' AND b.wat = \'social\'';
            $recent = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            //echo "SELECT $what FROM $from WHERE $where";
     }
    if($_POST['action'] == 'persinfo'){        
        $voornaam = $_POST['voornaam'] ; $achternaam = $_POST['achternaam'];
        $email = $_POST['email'] ; $telefoon = $_POST['telefoon'];
        
        //is voornaam gevuld ? nee: error
        if(strlen($voornaam) < 1){$error['voornaam'] = 'U dient een voornaam in te vullen';}
                
        //is voornaam gevuld ? nee: error
        if(strlen($achternaam) < 1){$error['achternaam'] = 'U dient een achternaam in te vullen';}
        
        //is voornaam gevuld ? nee: error
        if(checkEmail($email)){$error['email'] = 'U dient een geldig e-mailadres in te vullen, bijvoorbeeld voorbeeld@voorbeeld.nl';}
               
        //is voornaam gevuld ? nee: error
        if(strlen($telefoon) < 1){$error['telefoon_leeg'] = 'U dient een telefoonnummer in te vullen, met alleen cijfers (geen - of +)'; }
        else{
           if (preg_match("/\D/",$telefoon)){
            $error['telefoon_cijfers'] = 'Vul voor het telefoonnummer alleen cijfers in bijvoorbeeld 0612345678';
           }
        }
       
       
        if(!$error){ //zijn er geen fouten, wijzig de database dan
           $table = 'portal_gebruiker';
           $what = "
           voornaam = '".mysql_real_escape_string($voornaam)."',
           achternaam = '".mysql_real_escape_string($achternaam)."',
           email = '".mysql_real_escape_string($email)."',
           telefoon = '".mysql_real_escape_string($telefoon)."'";
           $where = 'id ='.$login_id;
                $update_gebruiker = sqlUpdate($table, $what, $where);
                
           if($recent['persoonlijk'] != null){
               $table = 'portal_gebruiker_recent'; $what = 'gewijzigd_op = NOW()'; $where = 'gebruiker = '.$login_id.' AND wat = \'persoonlijk\'';
                $update_recent = sqlUpdate($table, $what, $where);
           }else{
               $table = 'portal_gebruiker_recent'; $what = 'gebruiker, wat, gewijzigd_op'; 
               $with_what = $login_id.', \'persoonlijk\', NOW()';
                $insert_recent = sqlInsert($table, $what, $with_what);
           }
           //laat de gebruiker weten dat het goed is gegaan
           $message = '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
           
        }else{   //zijn er fouten, geef dan aan welke
           if(count($error) > 1){
                $message = '<div class="wijzig_info_error"> 
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }else{
                $message = '<div class="wijzig_info_error">
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }
           foreach($error as $fout){
               $message .= '<li class="info_error">'.$fout.'</li>';
           }
           $message .= '</ul><span class="error_footer">Uw gewijzigde informatie is nog niet opgeslagen!</span>
                        </div>';
       }
       echo $message;
    }
    elseif($_POST['action'] == 'logininfo'){
        $gebruikersnaam = $_POST['gebruikersnaam']; $wachtwoord = $_POST['wachtwoord'];
        $wachtwoord_check = $_POST['wachtwoord_check'];
        
        //is voornaam gevuld ? nee: error
       if(strlen($gebruikersnaam) < 1){$error['gebruikersnaam'] = 'U dient een gebruikersnaam in te vullen';}
       else{
           if (preg_match("/\s/", $gebruikersnaam)){//zitten er spaties in de gebruikersnaam ?
               $error['gebruikersnaam_witruimte'] = 'De gebruikersnaam dient uit ��n woord te bestaan, zonder witruimte';
           }else{
                //is de gebruikersnaam eigenlijk ongewijzigd ?
                if($gebruikersnaam == $_SESSION['gebruikersnaam']){
                    $change_username = 0;
                }
                else{
                    //nee ? dan een e-mail om de wijziging door de geven.
                    $change_username = 1;
                }
           }
       }
        
        //is het wachtwoord ingevuld ? i
       if(strlen($wachtwoord) > 1){
           
           
           //is het check veld dan wel ingevuld ?
           if(preg_match("/\s/", $wachtwoord)){$error['wachtwoord_witruimte'] = 'Uw wachtwoord dient uit ��n woord te bestaan, zonder witruimte';}//zitten er spaties in de ww ?
           elseif(strlen($wachtwoord_check) < 1){$error['wachtwoord'] = 'U dient uw nieuwe wachtwoord tweemaal in te voeren';}
           elseif(preg_match("/\s/", $wachtwoord_check)){$error['wachtwoord_check_witruimte'] = 'Uw wachtwoord dient uit ��n woord te bestaan, zonder witruimte';}//zitten er spaties in de check ww ?         
           //en komen de wachtwoorden dan wel overeen ?
           elseif($wachtwoord != $wachtwoord_check){$error['wachtwoord_fout'] = 'U hebt twee verschillende wachtwoorden ingevuld. Vul alstublieft tweemaal hetzelfde wachtwoord in';}
           
           //als het gevuld is en overeenkomst, maak dan het echte wachtwoord voor in de DB aan.
           else{
               $pass = md5($wachtwoord); 
               $what = 'id'; $from = 'portal_gebruiker'; $where = "id = $login_id AND wachtwoord = '".$pass."'";
                    $count_gebruikers = countRows($what, $from, $where);
               if($count_gebruikers > 0){
                   $change_pass = 0;
               }
               else{$change_pass = 1;}
           }
       }
       
       if(!$error){ //zijn er geen fouten, wijzig de database dan
           //$pass = md5($wachtwoord);
           $table = 'portal_gebruiker';
           $what = "
           gebruikersnaam = '".mysql_real_escape_string($gebruikersnaam)."', 
           gewijzigd_op = NOW(), gewijzigd_door = $login_id";
           
           if($pass != null){ $what .= ", wachtwoord = '$pass'"; }
           echo $what;
           $where = 'id ='.$login_id;
                $update_gebruiker = sqlUpdate($table, $what, $where);
           
           
           if($change_username == 1 && $change_pass == 1){
               $informUser['message'] = 'changeBoth';
               $message = '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
           }elseif($change_username ==0 && $change_pass == 0){
               //username en pass wijzigen niet!
               $message = '<div class="wijzig_info_success">Uw gebruikersnaam en wachtwoord zijn gelijk gebleven</div>';
           }elseif($change_pass == 1){
               $informUser['message'] = 'changePass';
               $message = '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
           }elseif($change_pass == 0){
               //pass wijzigt niet!
               $message = '<div class="wijzig_info_success">Uw wachtwoord is gelijk gebleven</div>';
           }elseif($change_username == 1){
               $informUser['message'] = 'changeUsername';
               $message = '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
           }elseif($change_username == 0){
               //username wijzigt niet!
               $message = '<div class="wijzig_info_success">Uw gebruikersnaam is gelijk gebleven</div>';
           }
           
           
           
           //laat de gebruiker weten dat het goed is gegaan
           
           
       }else{   //zijn er fouten, geef dan aan welke
           if(count($error) > 1){
                $message = '<div class="wijzig_info_error"> 
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }else{
                $message = '<div class="wijzig_info_error">
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }
           foreach($error as $fout){
               $message .= '<li class="info_error">'.$fout.'</li>';
           }
           $message .= '</ul><span class="error_footer">Uw gewijzigde informatie is nog niet opgeslagen!</span>
                        </div>';
       }
       echo $message;
        
    }
    elseif($_POST['action'] == 'socialinfo'){
       $facebook = $_POST['facebook'];          $twitter = $_POST['twitter'];
       $google_plus = $_POST['google_plus'];    $linkedin = $_POST['linkedin'];
       $hyves = $_POST['hyves'];                $skype = $_POST['skype']; 
       $youtube = $_POST['youtube'];
       
        //is facebook gevuld ? ja: is het een correcte url ?
       if($facebook != null){ if(!checkUrl){$error['facebook'] = 'U dient een correcte url voor Facebook in te voeren';} }
       //is Google Plus gevuld ? ja: is het een correcte url ?
       if($google_plus != null){ if(!checkUrl){$error['google'] = 'U dient een correcte url voor Google+ in te voeren';} }
       //is linkedin gevuld ? ja: is het een correcte url ?
       if($linkedin != null){ if(!checkUrl){$error['linkedin'] = 'U dient een correcte url voor Linkedin in te voeren';} }
       //is hyves gevuld ? ja: is het een correcte url ?
       if($hyves != null){ if(!checkUrl){$error['hyves'] = 'U dient een correcte url voor Hyves in te voeren';} }
       
       
       if(!$error){ //zijn er geen fouten, wijzig de database dan
           $table = 'portal_gebruiker';
           $what = "
           facebook = '".mysql_real_escape_string($facebook)."',
           twitter = '".mysql_real_escape_string($twitter)."',
           google_plus = '".mysql_real_escape_string($google_plus)."',
           linkedin = '".mysql_real_escape_string($linkedin)."',
           hyves = '".mysql_real_escape_string($hyves)."',
           skype = '".mysql_real_escape_string($skype)."',
           youtube = '".mysql_real_escape_string($youtube)."'";
           $where = 'id ='.$login_id;
                $update_gebruiker = sqlUpdate($table, $what, $where);
           
           if($recent['social'] != null){
               $table = 'portal_gebruiker_recent'; $what = 'gewijzigd_op = NOW()'; $where = 'gebruiker = '.$login_id.' AND wat = \'social\'';
                $update_recent = sqlUpdate($table, $what, $where);
           }else{
               $table = 'portal_gebruiker_recent'; $what = 'gebruiker, wat, gewijzigd_op'; 
               $with_what = $login_id.', \'social\', NOW()';
                $insert_recent = sqlInsert($table, $what, $with_what);
           }
           
           //laat de gebruiker weten dat het goed is gegaan
           $message = '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
           
       }else{   //zijn er fouten, geef dan aan welke
           if(count($error) > 1){
                $message = '<div class="wijzig_info_error"> 
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }else{
                $message = '<div class="wijzig_info_error">
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }
           foreach($error as $fout){
               $message .= '<li class="info_error">'.$fout.'</li>';
           }
           $message .= '</ul>
                        <span class="info_error">(tip: vergeet de http:// niet)</span>
                        <span class="error_footer">Uw gewijzigde informatie is nog niet opgeslagen!</span>
                        </div>';
       }
       echo $message;
    }else{echo 'Er is iets fout gegaan !';}
    
    if($informUser){
        $nu = strftime("%d %B %Y");
        $what = 'voornaam, achternaam, email'; $from = 'portal_gebruiker'; $where = 'id = '.$login_id;
                $email_gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
       
        $voornaam = $email_gebruiker['voornaam']; $achternaam = $email_gebruiker['achternaam']; $email = $email_gebruiker['email'];                        
        
        if($informUser['message'] == 'changeUsername'){

            $subject = 'Uw gegevens zijn gewijzigd';
            $htmlbody = "
            <p>
                Beste $voornaam $achternaam,<br />
                Op $nu heeft u uw gebruikersnaam gewijzigd.<br />
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam te gebruiken.<br />
            </p>
            <p>
                Uw nieuwe gebruikersnaam is : $gebruikersnaam <br />
                Uw wachtwoord is gelijk gebleven.<br />
                Als u uw gebruikersnaam wilt wijzigen, kunt u dat wanneer u ingelogd bent.<br />
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.<br />
                In het eerste venster, onderaan, vindt u de instelling om uw gebruikersnaam te wijzigen.
            </p>
            
            <p>
                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.<br />
                Wij nemen dan zo snel mogelijk contact met u op.
            </p>
            ";
            
            $textbody = " 
                Beste $voornaam $achternaam,
                Op $nu heeft u uw gebruikersnaam gewijzigd.
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam te gebruiken.
            
                Uw nieuwe gebruikersnaam is : $gebruikersnaam 
                Uw wachtwoord is gelijk gebleven.
                Als u uw gebruikersnaam wilt wijzigen, kunt u dat wanneer u ingelogd bent.
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.
                In het eerste venster, onderaan, vindt u de instelling om uw gebruikersnaam te wijzigen.

                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
                Wij nemen dan zo snel mogelijk contact met u op."; 
        }
        
        elseif($informUser['message'] == 'changePass'){

            $subject = 'Uw gegevens zijn gewijzigd';
            $htmlbody = "
            <p>
                Beste $voornaam $achternaam,<br />
                Op $nu heeft u uw wachtwoord opnieuw ingesteld.<br />
                Als u wilt inloggen, dient u het onderstaande wachtwoord te gebruiken.<br />
            </p>
            <p>
                Uw nieuwe wachtwoord is : $wachtwoord <br />
                Uw gebruikersnaam is gelijk gebleven.<br />
                Als u uw wachtwoord wilt wijzigen, kunt u dat wanneer u ingelogd bent.<br />
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.<br />
                In het eerste venster, onderaan, vindt u de instelling om uw wachtwoord te wijzigen.
            </p>
            
            <p>
                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.<br />
                Wij nemen dan zo snel mogelijk contact met u op.
            </p>
            ";
            
            $textbody = " 
                Beste $voornaam $achternaam,
                Op $nu heeft u uw wachtwoord opnieuw ingesteld.
                Klik op de volgende link om te kijken wat er voor commentaar is geplaatst.
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam te gebruiken.
            
                Uw nieuwe wachtwoord is : $wachtwoord 
                Uw gebruikersnaam is gelijk gebleven.
                Als u uw wachtwoord wilt wijzigen, kunt u dat wanneer u ingelogd bent.
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.
                In het eerste venster, onderaan, vindt u de instelling om uw wachtwoord te wijzigen.

                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
                Wij nemen dan zo snel mogelijk contact met u op."; 
        }
        
        elseif($informUser['message'] == 'changeBoth'){
        
            $subject = 'Uw gegevens zijn gewijzigd';
            $htmlbody = "
            <p>
                Beste $voornaam $achternaam,<br />
                Op $nu heeft u uw gebruikersnaam en wachtwoord gewijzigd.<br />
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam en wachtwoord te gebruiken.<br />
            </p>
            <p>
                Uw nieuwe gebruikersnaam is : $gebruikersnaam <br />
                Uw nieuwe wachtwoord is : $wachtwoord <br />
                Als u uw gebruikersnaam en/of wachtwoord wilt wijzigen, kunt u dat wanneer u ingelogd bent.<br />
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.<br />
                In het eerste venster, onderaan, vindt u de instelling om uw gebruikersnaam en/of wachtwoord te wijzigen.
            </p>
            
            <p>
                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.<br />
                Wij nemen dan zo snel mogelijk contact met u op.
            </p>
            ";
            
            $textbody = " 
                Beste $voornaam $achternaam,
                Op $nu heeft u uw gebruikersnaam en wachtwoord gewijzigd.
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam en wachtwoord te gebruiken.
            
                Uw nieuwe gebruikersnaam is : $gebruikersnaam 
                Uw nieuwe wachtwoord is : $wachtwoord 
                Als u uw gebruikersnaam en/of wachtwoord wilt wijzigen, kunt u dat wanneer u ingelogd bent.
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.
                In het eerste venster, onderaan, vindt u de instelling om uw gebruikersnaam en/of wachtwoord te wijzigen

                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
                Wij nemen dan zo snel mogelijk contact met u op."; 
        }
        
        $mail = new PHPMailer();
        $mail->SetFrom('info@vdspeld.nl'," Van der Speld intranet ");
        $mail->AddReplyTo("info@vdspeld.nl","Van der Speld intranet"); 
        $mail->AddAddress($email);
                    
        $mail->Subject = $subject;
        $mail->Body = $htmlbody;
        $mail->AltBody= $textbody;
        $mail->WordWrap = 50; 
        if(!$mail->Send()){
            $error = 'mailer error'.$mail->ErrorInfo;
        }
    }
?>