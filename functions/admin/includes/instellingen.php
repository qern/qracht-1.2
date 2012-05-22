<?php 
    $what = 'portal_titel, agenda_url, admin_email'; $from = 'portal_instellingen'; $where = 'actief = 1';
        $instelling = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>
<script>
function portalInfoOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/instellingen_check.php',
            dataType : 'html',
            data: {
                action : 'portalInfo',
                titel : $('#portal_titel').val(),
                agenda : $('#portal_agenda').val(),
                email : $('#portal_admin_emai').val()
            },
            success : function(data){
                $('#instellingen_response').html(data);
                $(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};
</script>
<div id="admin_center">
    <div id="links_invoer_header" class="admin_header">
        <p class="info_title">Algemene instellingen</p>
    </div>
     <div class="wijzig_info_response" id="instellingen_response">
     </div>
     
     <div class="wijzig_instellingen_form">
            
            <div id="portal_titel_row">
                <p class="input_label">Titel van de portal:</p>
                <input type="text" name="voornaam" id="portal_titel" class="textfield" value="<?php echo $instelling['portal_titel']; ?>" />
            </div>
            
            <div id="portal_agenda_row">
                <p class="input_label">Url voor Google Agenda:</p>
                <input type="text" name="achternaam" id="portal_agenda" class="textfield" value="<?php echo $instelling['agenda_url']; ?>" />
            </div>
            
            <div id="portal_admin_email_row">
                <p class="input_label">Admin e-mailadres:</p>
                <input type="text" name="email" id="portal_admin_emai" class="textfield" value="<?php echo $instelling['admin_email']; ?>" />
            </div>
            
            <div id="pers_send_row">
                <button id="pers_info" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="portalInfoOpslaan()">
                    Opslaan
                </button>
            
            </div>
            
     </div>
        
</div>
<div id="admin_right">
    <div id="links_invoer_header" class="admin_header">
        <p class="info_title">Hoe vind je de url voor Google Agenda :</p>
    </div>
    <div class="toelichting">
        <p></p>
        <h3>STAP 1: Login op je Google-account en ga naar je agenda.</h3>
        <p></p>  
        <p></p>
        <h3>STAP 2: Ga in je agenda naar de [Instellingen voor agenda].</h3>
        <p></p>
        <img src="<?php echo $etc_root ?>functions/admin/images/instellingen.png" alt="google agenda instellingen" style="float: right; padding-left: 0px; margin-left: 0px;">
        <p></p>
        <h3>STAP 3: Kies in het menu voor de menuoptie [Agenda's].</h3>
        <p></p>
        <img src="<?php echo $etc_root ?>functions/admin/images/menu.png" alt="menuopties" style="float: none; padding-left: 15px; margin-bottom: -35px; margin-top: -45px; margin-left: 0px;">
        <p></p>
        <h3>STAP 4: Klik op de desbetreffende agenda.</h3>
        <p></p>
        <p></p>
        <h3>STAP 5: Scroll helemaal naar beneden naar de optie [Priv&eacute;-adres:].</h3>
        <p></p>
        <p></p>
        <h3>STAP 6: Kopieer de link onder de button <img src="<?php echo $etc_root ?>functions/admin/images/xml.png" alt="xml" style="float: none; padding-left: 0px; margin-top: 0px; margin-left: 0px;"> en plak deze in het invulveld hierboven.</h3>
        <p></p>
        <p><i>Een voorbeeld:</i><br><br><img src="<?php echo $etc_root ?>lib/slir/<?php echo slirImage(500,0,0) ?><?php echo $etc_root ?>functions/admin/images/xml-url.png" alt="xml" style="float: none; padding-left: 0px; margin-top: 0px; margin-left: -15px;"></p>
    </div>
</div>
