<?php

    //haal op wat nodig is:
    $what = 'gebruikersnaam, voornaam, achternaam, email, telefoon, facebook, twitter, google_plus, linkedin, hyves, skype, youtube';
    $from = 'portal_gebruiker';  
if($_POST['gebruiker_id']){$where = 'id = '.$_POST['gebruiker_id'];}
else{$where = 'id = '.$login_id;}
    
        $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>
<script>
function persInfoOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/info_check.php',
            dataType : 'html',
            data: {
                action : 'persinfo',
                voornaam : $('#pers_voornaam').val(),
                achternaam : $('#pers_achternaam').val(),
                email : $('#pers_email').val(),
                telefoon : $('#pers_telefoon').val()
            },
            success : function(data){
                $('#pers_info_response').html(data);
                $(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};

function loginInfoOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/info_check.php',
            dataType : 'html',
            data: {
                action : 'logininfo',
                gebruikersnaam : $('#login_gebruikersnaam').val(),
                wachtwoord : $('#login_wachtwoord').val(),
                wachtwoord_check : $('#login_wachtwoord_check').val()
            },
            success : function(data){
                $('#login_info_response').html(data);
                $(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};

function socialInfoOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/info_check.php',
            dataType : 'html',
            data: {
                action : 'socialinfo',
                facebook : $('#social_facebook').val(),
                twitter : $('#social_twitter').val(),
                google_plus : $('#social_google_plus').val(),
                linkedin : $('#social_linkedin').val(),
                youtube : $('#social_youtube').val(),
                skype : $('#social_skype').val(),
                hyves : $('#social_hyves').val()
            },
            success : function(data){
                $('#social_info_response').html(data);
                $(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};
</script>
<div id="account_center">
    <div id="wijzig_pers_info" class="wijzig_info">
    
        <div id="pers_info_header" class="account_header">
                <p class="info_title">Wijzig uw persoonlijke informatie</p>
        </div>
        
        <div class="wijzig_info_response" id="pers_info_response">
        </div>
        
        <div class="wijzig_info_form">
            
            <div id="pers_voornaam_row">
                <p class="input_label">Voornaam:</p>
                <input type="text" name="voornaam" id="pers_voornaam" class="textfield" value="<?php echo $info['voornaam']; ?>" />
            </div>
            
            <div id="pers_achternaam_row">
                <p class="input_label">Achternaam:</p>
                <input type="text" name="achternaam" id="pers_achternaam" class="textfield" value="<?php echo $info['achternaam']; ?>" />
            </div>
            
            <div id="pers_email_row">
                <p class="input_label">E-mailadres:</p>
                <input type="text" name="email" id="pers_email" class="textfield" value="<?php echo $info['email']; ?>" />
            </div>
            
            <div id="pers_telefoon_row">
                <p class="input_label">Telefoonnummer:</p>
                <input type="text" name="telefoon" id="pers_telefoon" class="textfield" value="<?php echo $info['telefoon']; ?>" />
            </div>
            
            <div id="pers_send_row">
                <button id="pers_info" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="persInfoOpslaan()">
                    Opslaan
                </button>
            
            </div>
            
        </div>
        
    </div>
    
    <div id="wijzig_login_info"  class="wijzig_info">
    
        <div id="login_info_header" class="account_header">
                <p class="info_title">Wijzig uw login informatie</p>
        </div>
        
        <div class="wijzig_info_response" id="login_info_response">
        </div>
        
        <div class="wijzig_info_form">
            
            <div id="login_gebruikersnaam_row">
                <p class="input_label">Gebruikersnaam :</p>
                <input type="text" name="gebruikersnaam" id="login_gebruikersnaam" class="textfield" value="<?php echo $info['gebruikersnaam']; ?>" />
            </div>
            
            <div class="uitleg">Uw wachtwoord wijzigen is niet verplicht</div>
            
            <div id="login_wachtwoord_row">
                <p class="input_label">Nieuw wachtwoord :</p>
                <input type="password" name="wachtwoord" id="login_wachtwoord" class="textfield" />
            </div>
            
            <div id="login_wachtwoord_check_row">
                <p class="input_label">Wachtwoord opnieuw :</p>
                <input type="password" name="wachtwoord_check" id="login_wachtwoord_check" class="textfield" />
            </div>
            
            <div id="login_send_row">
                <button id="login_info" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="loginInfoOpslaan()">
                    Opslaan
                </button>
            </div>
            
        </div>
        
    </div>
    
</div>
<div id="account_right">

    <div id="wijzig_social_info"  class="wijzig_info">
    
        <div id="social_info_header" class="account_header">
                <p class="info_title">Wijzig uw sociale media informatie</p>
        </div>
        
        <div class="wijzig_info_response" id="social_info_response">
        </div>
        
        <div class="wijzig_info_form">
            <div class="uitleg">Klik op het plaatje om te zien of uw profiel correct gekoppeld is.</div>
            <div id="social_facebook_row" class="social_wijzig_row">
                <div class="social_image_column">
                    <a href="<?php echo $info['facebook']; ?>" title="bekijk uw facebook profiel" target="_blank">
                        <span class="social_preview_img" id="facebook_sprite">&nbsp;</span>
                    </a>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Facebook url :</p>-->
                    <input type="text" name="Facebook" id="social_facebook" class="textfield" value="<?php echo $info['facebook']; ?>" />
                </div>
            </div>
            
            <div id="social_twitter_row" class="social_wijzig_row">
                <div class="social_image_column">    
                    <a href="http://www.twitter.com/<?php echo $info['twitter']; ?>" title="bekijk uw twitter profiel" target="_blank">
                        <span class="social_preview_img" id="twitter_sprite">&nbsp;</span>
                    </a>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Twitter gebruikersnaam :</p>-->
                    <input type="text" name="Twitter" id="social_twitter" class="textfield" value="<?php echo $info['twitter']; ?>" />
                </div>
            </div>
            
            <div id="social_google_plus_row" class="social_wijzig_row">
                <div class="social_image_column">
                    <a href="<?php echo $info['google_plus']; ?>" title="bekijk uw google plus profiel" target="_blank">
                        <span class="social_preview_img" id="google_plus_sprite">&nbsp;</span>
                    </a>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Google+ url :</p>-->
                    <input type="text" name="Google_plus" id="social_google_plus" class="textfield" value="<?php echo $info['google_plus']; ?>" />
                </div>
            </div>
            
            <div id="social_linkedin_row" class="social_wijzig_row">
                <div class="social_image_column">
                    <a href="<?php echo $info['linkedin']; ?>" title="bekijk uw linkedin profiel" target="_blank">
                        <span class="social_preview_img" id="linkedin_sprite">&nbsp;</span>
                    </a>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Linkedin url :</p>-->
                    <input type="text" name="Linkedin" id="social_linkedin" class="textfield" value="<?php echo $info['linkedin']; ?>" />
                </div>
            </div>
            
            <div id="social_hyves_row" class="social_wijzig_row">
                <div class="social_image_column">
                    <a href="<?php echo $info['hyves']; ?>" title="bekijk uw hyves profiel" target="_blank">
                        <span class="social_preview_img" id="hyves_sprite">&nbsp;</span>
                    </a>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Hyves url :</p>-->
                    <input type="text" name="Hyves" id="social_hyves" class="textfield" value="<?php echo $info['hyves']; ?>" />
                </div>
            </div>
            
            <div id="social_skype_row" class="social_wijzig_row">
                <div class="social_image_column">                    
                    <span class="social_preview_img" id="skype_sprite">&nbsp;</span>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Skype gebruikersnaam :</p>-->
                    <input type="text" name="Skype" id="social_skype" class="textfield" value="<?php echo $info['skype']; ?>" />
                </div>
            </div>
            
            <div id="social_youtube_row" class="social_wijzig_row">
                <div class="social_image_column">
                    <a href="http://www.youtube.com/user/<?php echo $info['youtube']; ?>" title="bekijk uw youtube profiel" target="_blank">
                        <span class="social_preview_img" id="youtube_sprite">&nbsp;</span>
                    </a>
                </div>
                <div class="social_input_column">
                    <!--<p class="input_label">Youtube gebruikersnaam :</p>-->
                    <input type="text" name="Youtube" id="social_youtube" class="textfield" value="<?php echo $info['youtube']; ?>" />
                </div>
            </div>
            
            <div id="social_send_row">
                <button id="social_info" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="socialInfoOpslaan()">Opslaan</button>
            </div>
            
        </div>
    </div>
</div>