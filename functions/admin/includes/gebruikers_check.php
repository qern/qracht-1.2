<?php
 session_start();
  include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');   
  include($_SERVER['DOCUMENT_ROOT'].$etc_root.'lib/phpmailer/class.phpmailer.php');   
    //wijzig een persoonlijk info van een gebruiker
    if($_POST['action'] == 'addUser'){
        
        if($_POST['voornaam'] == null){$error['voornaam'] = 'U dient een voornaam in te vullen';}
        else{$voornaam = $_POST['voornaam'];}
        
        if($_POST['achternaam'] == null){$error['achternaam'] = 'U dient een achternaam in te vullen';}
        else{$achternaam = $_POST['achternaam'];}
        
        if($_POST['email'] == null){$error['email'] = 'U dient een e-mailadres in te vullen';}
        else{
            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false){$error['email'] = 'U dient een correct e-mailadres in te voeren. <br /> Bijvoorbeeld: voorbeeld@voorbeeld.nl';}
            else{$email = $_POST['email'];}
        }
        
        if(!$error){
            $gebruikersnaam =  substr($voornaam, 0, 1).$achternaam;
            $what = 'id'; $from = 'portal_gebruiker'; $where = "gebruikersnaam LIKE '$gebruikersnaam%'";
                $count_gebruikersnamen = countRows($what, $from, $where);
            if($count_gebruikersnamen > 0){
                $gebruikersnaam = $gebruikersnaam.$count_gebruikersnamen;
            }
            
            $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
            srand((double)microtime()*1000000); 
            $i = 0; 
            $pass = '' ; 
            
            while ($i <= 7) { 
                $num = rand() % 33; 
                $tmp = substr($chars, $num, 1); 
                $pass = $pass . $tmp; 
                $i++; 
            } 
            
            $table = 'portal_gebruiker'; $what = 'gebruikersnaam, wachtwoord, voornaam, achternaam, email, gewijzigd_op, gewijzigd_door';
            $with_what =  "'".$gebruikersnaam."', '".md5($pass)."', '".$voornaam."', '".$achternaam."', '".$email."', NOW(), $login_id";
                $nieuwe_gebruiker = sqlInsert($table, $what, $with_what);
                echo '<script>searchUser(\'\', 1)</script>';
                
                $informUser['message'] = 'addUser';
        }?>
        <div id="add_user_response" <?php echo 'class="'; if(!$error){echo 'success'; }else{echo 'error';} echo '"'; ?> >
            <?php
                if(!$error){echo 'Gebruiker '.$voornaam.' '.$achternaam.' is toegevoegd';}
                else{
                    echo '<ul id="errors">';
                    
                    foreach($error as $fout){ echo '<li class="fout">'.$fout.'</li>'; }
                    
                    echo '</ul>';
                }
            ?>
        </div>
        <div id="add_voornaam" class="add_user_row">
            <span class="add_user_label">Voornaam</span>
            <input class="textfield add_user_input" type="text" id="input_voornaam" value="<?php if($error){echo $_POST['voornaam'];} ?>" />
        </div>
        <div id="add_achternaam" class="add_user_row">
            <span class="add_user_label">Achternaam</span>
            <input class="textfield add_user_input" type="text" id="input_achternaam" value="<?php if($error){echo $_POST['achternaam'];} ?>" />
        </div>
        <div id="add_email" class="add_user_row">
            <span class="add_user_label">E-mail</span>
            <input  class="textfield add_user_input" type="text" id="input_email" value="<?php if($error){echo $_POST['email'];} ?>" />
        </div>
        <div id="add_user_send" class="add_user_row">
            <button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="addUser();">Opslaan</button>
        </div>
    <?php    
    }
    elseif($_POST['action'] == 'save_info'){
        if($_POST['info'] == 'gebruikersnaam'){
            if(strlen($_POST['input']) < 1){$error = 'U dient een voornaam in te vullen';}
            else{
                $what = 'id'; $from ='portal_gebruiker'; $where="gebruikersnaam = '".mysql_real_escape_string($_POST['input'])."'";
                    $gebruikersnaam = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                    if($gebruikersnaam['id'] != null){
                        if($gebruikersnaam['id'] == $_POST['gebruiker']){}
                        else{$error = 'Deze gebruikersnaam is al in gebruik. Kies een andere';}
                    }else{
                        $gebruikersnaam = $_POST['input'];
                        $table='portal_gebruiker'; $what = "gebruikersnaam = '".mysql_real_escape_string($gebruikersnaam)."', gewijzigd_op = NOW(), gewijzigd_door = $login_id"; $where = 'id = '.$_POST['gebruiker'];
                            $update_gebruikersnaam = sqlUpdate($table, $what, $where);
                    }
            }
            
            if(!$error){
                echo '
                <span class="gebruiker_info" id="info_gebruikersnaam">Gebruikersnaam</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_gebruikersnaam" value="'.$_POST['input'].'" disabled="disabled" />
                <img id="edit_gebruikersnaam" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'gebruikersnaam\')"  />
                <img id="save_gebruikersnaam" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'gebruikersnaam\', '.$_POST['gebruiker'].')"  style="display:none"/>
                <span class="response success" id="gebruikersnaam_response">De gebruikersnaam is gewijzigd</span>';
                $informUser['message'] = 'changeUsername';
            }else{
                echo '
                <span class="gebruiker_info" id="info_gebruikersnaam">Gebruikersnaam</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_gebruikersnaam" value="'.$_POST['input'].'" />
                <img id="edit_gebruikersnaam" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'gebruikersnaam\')" style="display:none" />
                <img id="save_gebruikersnaam" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'gebruikersnaam\', '.$_POST['gebruiker'].')"  />
                <span class="response error" id="gebruikersnaam_response">'.$error.'</span>';
            }
        }
        if($_POST['info'] == 'voornaam'){
            //is voornaam gevuld ? nee: error
            if(strlen($_POST['input']) < 1){$error = 'U dient een voornaam in te vullen';}
            else{
                $table='portal_gebruiker'; $what = "voornaam = '".mysql_real_escape_string($_POST['input'])."', gewijzigd_op = NOW(), gewijzigd_door = $login_id"; $where = 'id = '.$_POST['gebruiker'];
                    $update_email = sqlUpdate($table, $what, $where);
            }
            
            if(!$error){
                
                echo '
                <span class="gebruiker_info" id="info_voornaam">Voornaam</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_voornaam" value="'.$_POST['input'].'" disabled="disabled" />
                <img id="edit_voornaam" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'voornaam\')"  />
                <img id="save_voornaam" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'voornaam\', 1)"  style="display:none"/>
                <span class="response success" id="voornaam_response">De voornaam is gewijzigd</span>';
            }else{
                
                echo '
                <span class="gebruiker_info" id="info_voornaam">Voornaam</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_voornaam" value="'.$_POST['input'].'" disabled="disabled" />
                <img id="edit_voornaam" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'voornaam\')" style="display:none" />
                <img id="save_voornaam" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'voornaam\', 1)"  />
                <span class="response error" id="voornaam_response">'.$error.'</span>'; 
            }
        }
        if($_POST['info'] == 'achternaam'){
            //is voornaam gevuld ? nee: error
            if(strlen($_POST['input']) < 1){$error = 'U dient een e-mailadres in te vullen';}
            else{ $table='portal_gebruiker'; $what = "achternaam = '".mysql_real_escape_string($_POST['input'])."', gewijzigd_op = NOW(), gewijzigd_door = $login_id"; $where = 'id = '.$_POST['gebruiker'];
                        $update_email = sqlUpdate($table, $what, $where);
            }
            
            if(!$error){
                echo '
                <span class="gebruiker_info" id="info_achternaam">Achternaam</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_achternaam" value="slob" disabled="disabled" />
                <img id="edit_achternaam" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'achternaam\')"  />
                <img id="save_achternaam" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'achternaam\', 1)"  style="display:none"/>
                <span class="response success" id="achternaam_response">De achternaam is gewijzigd</span>';
            }else{
                echo '
                <span class="gebruiker_info" id="info_achternaam">Achternaam</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_achternaam" value="slob" disabled="disabled" />
                <img id="edit_achternaam" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'achternaam\')" style="display:none" />
                <img id="save_achternaam" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'achternaam\', 1)"  />
                <span class="response error" id="achternaam_response">'.$error.'</span>'; 
            }
        }
        if($_POST['info'] == 'email'){
            //is voornaam gevuld ? nee: error
            if(strlen($_POST['input']) < 1){$error = 'U dient een e-mailadres in te vullen';}
            else{
                if (filter_var($_POST['input'], FILTER_VALIDATE_EMAIL) == false) {
                    $error = 'U dient een correct e-mailadres in te voeren. <br /> Bijvoorbeeld: voorbeeld@voorbeeld.nl';
                }else{
                    $table='portal_gebruiker'; $what = "email = '".mysql_real_escape_string($_POST['input'])."', gewijzigd_op = NOW(), gewijzigd_door = $login_id"; $where = 'id = '.$_POST['gebruiker'];
                        $update_email = sqlUpdate($table, $what, $where);
                }
            }
            
            if(!$error){
                echo '
                <span class="gebruiker_info" id="info_email">E-mailadres</span>
                <input class="textfield info_input info_disabled" type="text" id="input_info_email" value="'.$_POST['input'].'" disabled="disabled" />
                <img id="edit_email" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'email\')"  />
                <img id="save_email" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'email\', '.$_POST['gebruiker'].')"  style="display:none"/>
                <span class="response success" id="email_response">Het e-mailadres is gewijzigd</span>';
            }else{
                echo '
                <span class="gebruiker_info" id="info_email">E-mailadres</span>
                <input class="textfield info_input info_active" type="text" id="input_info_email" value="'.$_POST['input'].'" />
                <img id="edit_email" src="'.$etc_root.'functions/admin/css/images/edit.png" alt="edit" onclick="editInfo(\'email\')"   style="display:none"  />
                <img id="save_email" src="'.$etc_root.'functions/admin/css/images/save.png" alt="save" onclick="saveInfo(\'email\', '.$_POST['gebruiker'].')"/>
                <span class="response error" id="email_response">'.$error.'</span>'; 
            }
        }
    }
    elseif($_GET['action'] == 'showUser'){
        $gebruiker_id = $_GET['gebruiker_id'];
        $what = 'a.id, a.gebruikersnaam, a.voornaam, a.achternaam, a.email, a.actief, DATE_FORMAT(b.login_tijd, \'%e %M %Y\') AS login_tijd'; 
        $from = 'portal_gebruiker a LEFT JOIN portal_inlog AS b ON (b.gebruiker_id = a.id)'; 
        $where = 'a.id = '.$gebruiker_id.' ORDER BY b.login_tijd DESC';
            $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            $refresh_user = 1;
     }
    elseif($_GET['action'] == 'activeUser'){
        $what = 'id, voornaam, achternaam'; $from = 'portal_gebruiker'; $where = 'actief = '.$_GET['active'];
            $count_gebruikers = countRows($what, $from, $where);
        if($count_gebruikers > 0){
            $gebruikers = sqlSelect($what, $from, $where);
            
        while($gebruiker = mysql_fetch_array($gebruikers)){
            echo'
            <div class="gebruiker" onmouseover="this.className=\'gebruiker gebruiker_hover\'" onmouseout="this.className=\'gebruiker\'" onclick="showUser('.$gebruiker['id'].')">
                '.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].'
            </div>';
        }
        }else{
            echo 'Er zijn geen gebruikers gevonden';
        }
    }
    elseif($_GET['action'] == 'searchUser'){
        $what = 'id, voornaam, achternaam'; $from = 'portal_gebruiker'; 
        if(strlen($_GET['q']) > 0){
            $where = "(voornaam LIKE '%".$_GET['q']."%' OR achternaam LIKE '%".$_GET['q']."%' OR gebruikersnaam LIKE '%".$_GET['q']."%') AND actief = ".$_GET['actief'];
        }else{
            $where = 'actief = '.$_GET['actief'];
        }
            $count_gebruikers = countRows($what, $from, $where);
        if($count_gebruikers > 0){
            $gebruikers = sqlSelect($what, $from, $where);
            
        while($gebruiker = mysql_fetch_array($gebruikers)){
            echo'
            <div class="gebruiker" onmouseover="this.className=\'gebruiker gebruiker_hover\'" onmouseout="this.className=\'gebruiker\'" onclick="showUser('.$gebruiker['id'].')">
                '.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].'
            </div>';
        }
        }else{
            echo 'Er zijn geen gebruikers gevonden';
        }
    }
    elseif($_GET['action'] == 'changeActiveState'){
        $table = 'portal_gebruiker'; $what = 'gewijzigd_op =  NOW(), gewijzigd_door = '.$login_id.', actief = '.$_GET['actief'];
        $where = 'id = '.$_GET['gebruiker_id'];
        //echo "UPDATE $table SET $what WHERE $where";
            $update_actief_status = sqlUpdate($table, $what, $where); 
            
        $what = 'a.id, a.gebruikersnaam, a.voornaam, a.achternaam, a.email, a.actief, DATE_FORMAT(b.login_tijd, \'%e %M %Y\') AS login_tijd'; 
        $from = 'portal_gebruiker a LEFT JOIN portal_inlog AS b ON (b.gebruiker_id = a.id)'; 
        $where = 'a.id = '.$_GET['gebruiker_id'].' ORDER BY b.login_tijd DESC';
        $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        $refresh_user = 1; $change_active = $_GET['actief'];
    }
    elseif($_GET['action'] == 'refreshGebruikerTop'){
       if($_GET['actief'] == 1){$actief = 'class="active_switch"';}
       else{$non_actief = 'class="active_switch"';}
        ?>
        <div id="switch_actief">
            <span id="actief_switch" <?php echo $actief; ?> onmouseover="this.style.color = '#FF4E00'" onmouseout="this.style.color = '#027EC0'" onclick="activeUser(1)">Actief</span>
            <span id="non_actief_switch" <?php echo $non_actief; ?> onmouseover="this.style.color = '#FF4E00'" onmouseout="this.style.color = '#027EC0'" onclick="activeUser(0)">Non-actief</span>
        </div>
        <div id="zoeken_gebruiker">
            <label class="label" for="zoek_gebruiker">Zoek gebruiker</label>
            <img id="end_search" src="<?php echo $etc_root ?>functions/admin/css/images/end_search.png" onclick="searchUser('', <?php echo $_GET['actief'] ?>)" alt="end search" title="stop met zoeken" style="display:none;" />
            <input class="textfield" type="text" id="zoek_gebruiker" onkeyup="searchUser(this.value, <?php echo $_GET['actief'] ?>)" />
        </div>
    <?}
    elseif($_POST['action'] == 'resetPass'){
            $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
            srand((double)microtime()*1000000); 
            $i = 0; $pass = '' ; 
            while ($i <= 7) { 
                $num = rand() % 33; 
                $tmp = substr($chars, $num, 1); 
                $pass = $pass . $tmp; 
                $i++; 
            }
            
            $table = 'portal_gebruiker';
            $what =  "wachtwoord = '".md5($pass)."', gewijzigd_op = NOW(), gewijzigd_door =  $login_id";
            $where = 'id ='.$_POST['gebruiker_id'];
                $nieuwe_gebruiker = sqlUpdate($table, $what, $where);
        
        $what = 'a.id, a.gebruikersnaam, a.voornaam, a.achternaam, a.email, a.actief, DATE_FORMAT(b.login_tijd, \'%e %M %Y\') AS login_tijd'; 
        $from = 'portal_gebruiker a LEFT JOIN portal_inlog AS b ON (b.gebruiker_id = a.id)'; 
        $where = 'a.id = '.$_POST['gebruiker_id'].' ORDER BY b.login_tijd DESC';
            $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
        $refresh_user = 1; $reset_pass = 1;
        $informUser['message'] = 'resetPass';
        
    }
    
    if($refresh_user == 1){?>
    <div class="admin_header" id="meer_info_header">
        <p class="info_title">Informatie over <?php echo $gebruiker['voornaam'].' '.$gebruiker['achternaam']; ?></p>
    </div>
    <div id="gebruiker_opties">
        
        <?php if($gebruiker['actief'] == 1){?>
        <div id="change_active_state" class="gebruiker_optie" onmouseover="this.className='gebruiker_optie optie_hover'" onmouseout="this.className='gebruiker_optie'" onclick="activeCheck()">Gebruiker deactiveren</div>
        <?php }else{ ?>
        <div id="change_active_state" class="gebruiker_optie" onmouseover="this.className='gebruiker_optie optie_hover'" onmouseout="this.className='gebruiker_optie'" onclick="activeCheck()">Gebruiker activeren</div>        
        <?php } ?>
        
        <div id="reset_pass"  class="gebruiker_optie"  onmouseover="this.className='gebruiker_optie optie_hover'" onmouseout="this.className='gebruiker_optie'" onclick="passResetCheck()">Wachtwoord resetten</div>
        
        <?php
        if($change_active != null){?>
        <div id="active_change_response">
            <?php
                if($change_active == 1){echo 'Het account voor '.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].' is geactiveerd';}
                elseif($change_active == 0){echo 'Het account voor  '.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].' is gedeactiveerd';}
                elseif($reset_pass == 1){echo 'Het wachtwoord van '.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].' is gereset';}
            ?>
        </div>
        <?php }  if($gebruiker['actief'] == 1){?>
        <div id="confirm_activate_state" class="confirm_change" style="display:none;">
            <p class="warning">Waarschuwing: U wilt <?php echo $gebruiker['voornaam'].' '.$gebruiker['achternaam']; ?> deactiveren !</p>
            <p>U wilt deze gebruiker deactiveren. Dit houdt in dat hij/zij niet meer kan inloggen.<br />
               Het wachtwoord en gebruikersnaam blijven behouden.<br />
               NB : de gebruiker krijgt geen melding dat hij/zij is gedeactiveerd !<br />
               Als u hier mee door wilt gaan, klik op 'deactiveren'. Anders, klik op 'annuleren'.</p>
            <div id="accept_deactive_state" class="accept_change" onmouseover="this.className='accept_change change_hover'" onmouseout="this.className='accept_change'" onclick="changeActiveState(0, <?php echo $gebruiker['id'] ?>)">deactiveren</div>
            <div id="cancel_active_state" class="cancel_change" onmouseover="this.className='cancel_change change_hover'" onmouseout="this.className='cancel_change'" onclick="activeCheck()">annuleren</div>
        </div>
        <?php }else{?>
        <div id="confirm_activate_state" class="confirm_change" style="display:none;">
            <p class="warning">Waarschuwing: U wilt <?php echo $gebruiker['voornaam'].' '.$gebruiker['achternaam']; ?> activeren !</p>
            <p>U wilt deze gebruiker activeren. Dit houdt in dat hij/zij kan inloggen.<br />
               Het wachtwoord en gebruikersnaam zijn hetzelfde gebleven.<br />
               Wanneer de gebruiker deze niet meer weet, kunt u het wachtwoord resetten. Hier krijgt de gebruiker een e-mail van.<br />
               NB : de gebruiker krijgt geen melding dat hij/zij is geactiveerd !<br />
               Als u hier mee door wilt gaan, klik op 'deactiveren'. Anders, klik op 'annuleren'.</p>
            <div id="accept_active_state" class="accept_change" onmouseover="this.className='accept_change change_hover'" onmouseout="this.className='accept_change'" onclick="changeActiveState(1, <?php echo $gebruiker['id'] ?>)">activeren</div>
            <div id="cancel_active_state" class="cancel_change" onmouseover="this.className='cancel_change change_hover'" onmouseout="this.className='cancel_change'" onclick="activeCheck()">annuleren</div>
        </div>
        <?php } ?>
        <div id="confirm_pass_reset" class="confirm_change" style="display:none;">
            <p class="warning">Waarschuwing: U wilt <?php echo $gebruiker['voornaam'].' '.$gebruiker['achternaam']; ?>'s wachtwoord resetten !</p>
            <p>U wilt het wachtwoord resetten van deze gebruiker.<br />
               De gebruiker kan hierna niet meer inloggen met het oude wachtwoord.<br />
               Hij/zij krijgt een e-mail met daarin het nieuwe, tijdelijke, wachtwoord en de huidige gebruikersnaam.<br />
               Als u hier mee door wilt gaan, klik op 'reset wachtwoord'. Anders, klik op 'annuleren'.</p>
            <div id="accept_pass_reset"  class="accept_change" onmouseover="this.className='accept_change change_hover'" onmouseout="this.className='accept_change'" onclick="resetpass(<?php echo $gebruiker['id'] ?>)">reset wachtwoord</div>
            <div id="cancel_pass_reset" class="cancel_change" onmouseover="this.className='cancel_change change_hover'" onmouseout="this.className='cancel_change'" onclick="passResetCheck()">annuleren</div>
        </div>
    </div>
    <div id="gebruiker_info">
        <div id="info_gebruikersnaam" class="info_row">
            <span class="gebruiker_info" id="info_gebruikersnaam">Gebruikersnaam</span>
            <input class="textfield info_input info_disabled" type="text" id="input_info_gebruikersnaam" value="<?php echo $gebruiker['gebruikersnaam'] ?>" disabled="disabled" />
            <img id="edit_gebruikersnaam" src="<?php echo $etc_root ?>functions/admin/css/images/edit.png" alt="edit" onclick="editInfo('gebruikersnaam')"  />
            <img id="save_gebruikersnaam" src="<?php echo $etc_root ?>functions/admin/css/images/save.png" alt="save" onclick="saveInfo('gebruikersnaam', <?php echo $gebruiker['id'] ?>)"  style="display:none"/>
            <span class="response" id="gebruikersnaam_response"  style="display:none;"></span>
        </div>
        
          <div id="info_voornaam" class="info_row">
              <span class="gebruiker_info" id="info_voornaam">Voornaam</span>
              <input class="textfield info_input info_disabled" type="text" id="input_info_voornaam" value="<?php echo $gebruiker['voornaam'] ?>" disabled="disabled" />
              <img id="edit_voornaam" src="<?php echo $etc_root ?>functions/admin/css/images/edit.png" alt="edit" onclick="editInfo('voornaam')"  />
              <img id="save_voornaam" src="<?php echo $etc_root ?>functions/admin/css/images/save.png" alt="save" onclick="saveInfo('voornaam', <?php echo $gebruiker['id'] ?>)"  style="display:none"/>
              <span class="response" id="voornaam_response"  style="display:none;"></span>
          </div>
          
        <div id="info_achternaam" class="info_row">
            <span class="gebruiker_info" id="info_achternaam">Achternaam</span>
            <input class="textfield info_input info_disabled" type="text" id="input_info_achternaam" value="<?php echo $gebruiker['achternaam'] ?>" disabled="disabled" />
            <img id="edit_achternaam" src="<?php echo $etc_root ?>functions/admin/css/images/edit.png" alt="edit" onclick="editInfo('achternaam')"  />
            <img id="save_achternaam" src="<?php echo $etc_root ?>functions/admin/css/images/save.png" alt="save" onclick="saveInfo('achternaam', <?php echo $gebruiker['id'] ?>)"  style="display:none"/>
            <span class="response" id="achternaam_response" style="display:none;"></span>
        </div>
        
        <div id="info_email" class="info_row">
            <span class="gebruiker_info" id="info_email">E-mailadres</span>
            <input class="textfield info_input info_disabled" type="text" id="input_info_email" value="<?php echo $gebruiker['email'] ?>" disabled="disabled" />
            <img id="edit_email" src="<?php echo $etc_root ?>functions/admin/css/images/edit.png" alt="edit" onclick="editInfo('email')"  />
            <img id="save_email" src="<?php echo $etc_root ?>functions/admin/css/images/save.png" alt="save" onclick="saveInfo('email', <?php echo $gebruiker['id'] ?>)"  style="display:none"/>
            <span class="response" id="email_response"  style="display:none;"></span>
        </div>
        
        <div id="info_laatst_ingelogd" class="info_row">
            <span class="gebruiker_info" id="info_gebruikersnaam">Laatst Ingelogd</span>
            <input class="textfield info_input info_disabled" type="text" value="<?php echo $gebruiker['login_tijd']; ?>" disabled="disabled" />
        </div>
        
        <div id="info_to_profile" class="info_row">
            <a href="<?php echo $etc_root ?>profiel/<?php echo $gebruiker['gebruikersnaam']; ?>">Ga naar <?php echo $gebruiker['voornaam'].' '.$gebruiker['achternaam']; ?>'s profiel</a>
        </div>
        
    </div>        
    <?php } 
    if($informUser){
        $nu = strftime("%d %B %Y");
        
        
        if($informUser['message'] == 'changeUsername'){
            $what = 'voornaam, achternaam, email'; $from = 'portal_gebruiker'; $where = 'id = '.$_POST['gebruiker'];
                $email_gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
            $voornaam = $email_gebruiker['voornaam']; $achternaam = $email_gebruiker['achternaam']; $email = $email_gebruiker['email'];
                
            $subject = 'Uw gegevens zijn gewijzigd';
            $htmlbody = "
            <p>
                Beste $voornaam $achternaam,<br /><br />
                Op $nu is uw gebruikersnaam gewijzigd.<br />
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam te gebruiken.<br />
            </p>
            <p>
                Uw nieuwe gebruikersnaam is : $gebruikersnaam <br />
                Uw wachtwoord is gelijk gebleven.<br /><br />
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
				
                Op $nu is uw gebruikersnaam gewijzigd.
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam te gebruiken.
            
                Uw nieuwe gebruikersnaam is : $gebruikersnaam 
                Uw wachtwoord is gelijk gebleven.
				
                Als u uw gebruikersnaam wilt wijzigen, kunt u dat wanneer u ingelogd bent.
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.
                In het eerste venster, onderaan, vindt u de instelling om uw gebruikersnaam te wijzigen.

                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
                Wij nemen dan zo snel mogelijk contact met u op."; 
        }
        
        elseif($informUser['message'] == 'resetPass'){
            $what = 'voornaam, achternaam, email'; $from = 'portal_gebruiker'; $where = 'id = '.$_POST['gebruiker_id'];
                $email_gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
            $voornaam = $email_gebruiker['voornaam']; $achternaam = $email_gebruiker['achternaam']; $email = $email_gebruiker['email'];
            
            $subject = 'Uw gegevens zijn gewijzigd';
            $htmlbody = "
            <p>
                Beste $voornaam $achternaam,<br /><br />
                Op $nu is uw wachtwoord opnieuw ingesteld.<br />
                Als u wilt inloggen, dient u het onderstaande wachtwoord te gebruiken.<br />
            </p>
            <p>
                Uw nieuwe wachtwoord is : $pass <br />
                Uw gebruikersnaam is gelijk gebleven.<br /><br />
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
				
                Op $nu is uw wachtwoord opnieuw ingesteld.
                Klik op de volgende link om te kijken wat er voor commentaar is geplaatst.
                Als u wilt inloggen, dient u de onderstaande gebruikersnaam te gebruiken.
            
                Uw nieuwe wachtwoord is : $pass 
                Uw gebruikersnaam is gelijk gebleven.
				
                Als u uw wachtwoord wilt wijzigen, kunt u dat wanneer u ingelogd bent.
                Als u op uw naam klikt, rechtsboven in het scherm, komt u bij uw accountinstellingen.
                In het eerste venster, onderaan, vindt u de instelling om uw wachtwoord te wijzigen.

                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
                Wij nemen dan zo snel mogelijk contact met u op."; 
        }
        
        elseif($informUser['message'] == 'addUser'){
            $what = 'voornaam, achternaam, email'; $from = 'portal_gebruiker'; $where = 'id IN (SELECT MAX(id) FROM portal_gebruiker WHERE actief = 1)';
                $email_gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
            $voornaam = $email_gebruiker['voornaam']; $achternaam = $email_gebruiker['achternaam']; $email = $email_gebruiker['email'];
            
            $subject = 'Welkom op de HSPortal';
            
            $htmlbody = "
            <p>
                Beste $voornaam $achternaam,<br /><br />
                Welkom op de HSPortal.<br />
                Op dit intranet vind je het laatste nieuws, je digitale werkplek en veel meer.<br /><br />
                Het intranet is te bereiken via:<br />
                &nbsp; &nbsp; &nbsp; - &nbsp; De knop login op  <a href=\"http://buro-n11.qernproef.nl/\">http://buro-n11.qernproef.nl</a><br />
                &nbsp; &nbsp; &nbsp; - &nbsp; of de link <a href=\"http://buro-n11.qernproef.nl$etc_root\">http://buro-n11.qernproef.nl$etc_root</a><br />
            </p>
            <p>
                Je kunt inloggen met de volgende gegevens:<br /><br />
                Gebruikersnaam: $gebruikersnaam<br />
                Wachtwoord: $pass <br /><br />
                Als je je gebruikersnaam of wachtwoord wilt wijzigen, kunt u dat wanneer je ingelogd bent.<br />
                Als je op je naam klikt, rechtsboven in het scherm, kom je bij jouw accountinstellingen.<br />
                In het eerste venster, onderaan, vindt je de instelling om uw gebruikersnaam en/of wachtwoord te wijzigen.
            </p>
            
            <p>
                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.<br />
                Wij nemen dan zo snel mogelijk contact met u op.
            </p>
            ";
            
            $textbody = " 

                Beste $voornaam $achternaam,
				
                Welkom op de HSPortal!				
                Op dit intranet vind je het laatste nieuws, je digitale werkplek en veel meer.

				Het intranet is te bereiken via:
                   - De knop login op http://buro-n11.qernproef.nl
                   - of de directe link http://buro-n11.qernproef.nl$etc_root
            
                Je kunt inloggen met de volgende gegevens:
				
                Gebruikersnaam: $gebruikersnaam
                Wachtwoord: $pass 
				
                Als je je gebruikersnaam of wachtwoord wilt wijzigen, kunt u dat wanneer je ingelogd bent.
                Als je op je naam klikt, rechtsboven in het scherm, kom je bij jouw accountinstellingen.
                In het eerste venster, onderaan, vindt je de instelling om uw gebruikersnaam en/of wachtwoord te wijzigen.

                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
                Wij nemen dan zo snel mogelijk contact met u op."; 
        }
        
        $mail = new PHPMailer();
        $mail->SetFrom('support@qern.nl'," HSPortal ");
        $mail->AddReplyTo("support@qern.nl","HSPortal"); 
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