<?php 
    session_start();
    //include de benodigde configuration
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    if($_POST['action'] == 'portalInfo'){
       $titel = $_POST['titel'] ; $agenda = $_POST['agenda'];
       $email = $_POST['email'] ;
        //is voornaam gevuld ? nee: error
       if(strlen($titel) < 1){$error['titel'] = 'Deze portal dient een naam te hebben';}
       
       if(strlen($agenda) < 1){$error['url'] = 'Deze portal dient een agendaurl te hebben';}
       else{
        $result = filter_var($agenda, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
        if($result){
        }else{
            $error['agenda_onjuist'] = 'U dient een correcte agendaurl in te voeren. <br /> Kijk hiernaast voor de uitleg';
        } 
       }
        //is voornaam gevuld ? nee: error
       if(strlen($email) < 1){$error['email'] = 'U dient een e-mailadres in te vullen';}
       else{
           if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
                $error['email_onjuist'] = 'U dient een correct e-mailadres in te voeren. <br /> Bijvoorbeeld: voorbeeld@voorbeeld.nl';
            }else{
                $laatste_punt = strpos (strrev($email), '.');
                $laatste_apenstaart = strpos (strrev($email), '@');
                $laatste_apenstaart_offset = $laatste_punt + 3;
                if($laatste_punt <= 1 || $laatste_punt == null || $laatste_punt > 4){
                    $error['email_onjuist'] = 'U dient een correct e-mailadres in te voeren. <br /> Bijvoorbeeld: voorbeeld@voorbeeld.nl';   
                }elseif($laatste_apenstaart < $laatste_apenstaart_offset){
                    $error['email_onjuist'] = 'U dient een correct e-mailadres in te voeren. <br /> Bijvoorbeeld: voorbeeld@voorbeeld.nl';
                }
            }
       }
       
       if(!$error){ //zijn er geen fouten, wijzig de database dan
			//wat is het max id?
			$what = 'MAX(id) AS laatste_instellingen'; $from = ' portal_instellingen'; $where = 'actief = 1';
			$instellingen = mysql_fetch_assoc(sqlSelect($what, $from, $where));
			
			//zet die op non actief
			$table = 'portal_instellingen'; $what = 'actief = 0'; $where = 'id ='.$instellingen['laatste_instellingen'];
            $maak_plaats_voor_nieuwe_instellingen = sqlUpdate($table, $what, $where);
        
			//en voer de nieuwe instellingen in
			$table = 'portal_instellingen';
			$what = 'portal_titel, agenda_url, admin_email, toegevoegd_door, toegevoegd_op';
			$with_what =  "'".mysql_real_escape_string($titel)."', '".htmlentities($agenda)."', '".mysql_real_escape_string($email)."', $login_id, NOW()";
                $update_gebruiker = sqlInsert($table, $what, $with_what);
           
			//laat de gebruiker weten dat het goed is gegaan
			$message = '<div class="wijzig_info_success">De gegevens zijn gewijzigd</div>';
           
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
?>
