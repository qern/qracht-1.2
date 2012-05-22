<script>
function addUser(){
    $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'addUser',
                voornaam: $('#input_voornaam').val(),
                achternaam: $('#input_achternaam').val(),
                email : $('#input_email').val()
            },
            success : function(data){
                $('#user_form').html(data);
                $('.success').delay(1000).fadeOut(1500);    
                //searchUser('', 1);
            }
        });
        return false;
}
function searchUser(query, activeState){ 
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'searchUser',
                q: query,
                actief: activeState
            },
            success : function(data){  
                if(query.length > 0){$('#end_search').show();}
                else{$('#end_search').hide();
                     $('#zoek_gebruiker').val('');
                }        
                $('#gebruiker_lijst_content').html(data);    
            }
        });
        return false;
}
function activeCheck(){
    var element = document.getElementById('confirm_activate_state');
    if(element.style.display == 'block'){
        element.style.display = 'none';
    }else{
        element.style.display = 'block';
    }
}
function passResetCheck(){
    var element = document.getElementById('confirm_pass_reset');
    if(element.style.display == 'block'){
        element.style.display = 'none';
    }else{
        element.style.display = 'block';
    }
}
function editInfo(info){
    $('#input_info_'+info).removeClass('info_disabled').addClass('info_active').attr("disabled", false);
    $('#edit_'+info).hide(); $('#save_'+info).show();
}
function activeUser(activeState){
    if(activeState == 1){$('#actief_switch').addClass('active_switch'); $('#non_actief_switch').removeClass('active_switch');}
    else{$('#actief_switch').removeClass('active_switch'); $('#non_actief_switch').addClass('active_switch');}
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'activeUser',
                active : activeState
            },
            success : function(data){
                $('#gebruiker_lijst_content').html(data);
                refreshGebruikerTop(activeState);
            }
        });
        return false;
}
function saveInfo(info, gebruiker){
    if($('#input_info_'+info).val().length > 0){
        $('#save_'+info).hide();
        $('#input_info_'+info).removeClass('info_active').addClass('info_disabled');
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'save_info',
                info : info,
                input : $('#input_info_'+info).val(),
                gebruiker: gebruiker
            },
            success : function(data){
                $('#info_'+info).html(data);
                $('.success').delay(1000).fadeOut(1500);               
            }
        });
        return false;
    }else{
        $('#'+info+'_response').show().addClass('error').html('U dient dit veld in te vullen');
    }
    
}
function showUser(gebruiker){
    $('#admin_right').html(
    '<div class="admin_header" id="meer_info_header">'+
        '<p class="info_title">De gebruiker wordt geladen....</p>'+
    '</div>');
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'showUser',
                gebruiker_id: gebruiker
            },
            success : function(data){
                $('#admin_right').html(data);
            }
        });
        return false;
}
function refreshGebruikerTop(activeState){
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'refreshGebruikerTop',
                actief: activeState
            },
            success : function(data){
                $('#gebruiker_lijst_top').html(data);
            }
        });
        return false;        
}
function changeActiveState(activeState, gebruiker){
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'changeActiveState',
                actief: activeState,
                gebruiker_id: gebruiker
            },
            success : function(data){
                $('#admin_right').html(data);
                if(activeState === 1){searchUser('', 0);}
                else if(activeState === 0){searchUser('', 1);}
            }
        });
        return false;        
}
function resetpass(gebruiker){
    $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/gebruikers_check.php',
            dataType : 'html',
            data: { 
                action : 'resetPass',
                gebruiker_id: gebruiker
            },
            success : function(data){
                $('#admin_right').html(data);
            }
        });
        return false;        
}
</script>
<div id="admin_center">
    <div id="add_user">
        <div class="admin_header" id="toevoegen_gebruiker_header">
            <p class="info_title">Voeg een gebruiker toe</p>
        </div>
        <div id="add_user_response" style="display:none;"></div>
        <div id="user_form">
            <div id="add_voornaam" class="add_user_row">
                <span class="add_user_label">Voornaam</span>
                <input class="textfield add_user_input" type="text" id="input_voornaam" />
            </div>
            <div id="add_achternaam" class="add_user_row">
                <span class="add_user_label">Achternaam</span>
                <input class="textfield add_user_input" type="text" id="input_achternaam" />
            </div>
            <div id="add_email" class="add_user_row">
                <span class="add_user_label">E-mail</span>
                <input  class="textfield add_user_input" type="text" id="input_email" />
            </div>
            <div id="add_user_send" class="add_user_row">
                <button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="addUser();">Opslaan</button>
            </div>
        </div>
    </div>
    <div id="gebruiker_lijst">
        <div class="admin_header" id="gebruiker_lijst_header">
            <p class="info_title">De gebruikers</p>
        </div>
        <div id="gebruiker_lijst_top">
            <div id="switch_actief">
                <span id="actief_switch" class="active_switch" onmouseover="this.style.color = '#FF4E00'" onmouseout="this.style.color = '#027EC0'" onclick="activeUser(1)">Actief</span>
                <span id="non_actief_switch" onmouseover="this.style.color = '#FF4E00'" onmouseout="this.style.color = '#027EC0'" onclick="activeUser(0)">Non-actief</span>
            </div>
            <div id="zoeken_gebruiker">
                <label class="label" for="zoek_gebruiker">Zoek gebruiker</label>
                <img id="end_search" src="<?php echo $etc_root ?>functions/admin/css/images/end_search.png" onclick="searchUser('', 1)" alt="end search" 
                     title="stop met zoeken" style="display:none;" />
                <input class="textfield" type="text" id="zoek_gebruiker" onkeyup="searchUser(this.value, 1)" />
            </div>
        </div>
        <div id="gebruiker_lijst_content">
            <?php
                $what = 'id, voornaam, achternaam'; $from =  'portal_gebruiker'; $where = 'actief = 1';
                $gebruikers = sqlSelect($what, $from, $where);
                while($gebruiker = mysql_fetch_array($gebruikers)){?>
                <div class="gebruiker" onmouseover="this.className='gebruiker gebruiker_hover'" onmouseout="this.className='gebruiker'" onclick="showUser(<?php echo $gebruiker['id']; ?>)">
                    <?php echo $gebruiker['voornaam'].' '.$gebruiker['achternaam']; ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<div id="admin_right">
    <div class="admin_header" id="meer_info_header">
        <p class="info_title">Klik hiernaast op een naam om gegevens in te zien/wijzigen</p>
    </div>
    
</div>
