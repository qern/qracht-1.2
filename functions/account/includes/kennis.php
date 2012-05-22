<?php
	if($_POST['gebruiker_id']){$gebruiker_id = $_POST['gebruiker_id'];}
    $what = 'a.top_positie, b.id, b.kennis';
    $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_kenniskaart AS b ON (b.id = a.kennis)';
    $where = 'a.gebruiker = '.$gebruiker_id.' ORDER BY b.kennis ASC';
        $kennis_res = sqlSelect($what, $from, $where);
    $where = 'a.gebruiker = '.$gebruiker_id.' AND a.top_positie > 0 ORDER BY a.top_positie ASC';
        $kennis_res2 = sqlSelect($what, $from, $where);
        
        
    $what = 'is_goed_in'; $from = 'portal_gebruiker'; $where = 'id = '.$gebruiker_id;
        $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>

<script>
$(function(){
    $("#kennis_schrijven").autocomplete({
        source:'<?php echo $etc_root ?>functions/account/includes/kennis_ajax.php',
        minLength:2
    });
});

function goedinOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'update_goed_in',
                goed_in : $('#goed_in_schrijven').val()
            },
            success : function(data){}
        });

        return false;
};
function refreshAll() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {action : 'refreshAll'},
            success : function(data){
                $('#kennis_overzicht_list').html(data);
            }
        });

        return false;
};

function refreshTop10() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {action : 'refreshTop10'},
            success : function(data){
                $('#top_10_list').html(data);
            }
        });

        return false;
};
function kennisOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'createkennis',
                kennis : $('#kennis_schrijven').val()
            },
            success : function(data){
                $('#kennis_schrijven').val(""),
                $('#kennis_overzicht_list').html(data);
                $(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};

function kennisVerwijderen(kennisId) {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'deletekennis',
                kennis_id : kennisId
            },
            success : function(data){
                refreshAll();
                refreshTop10();
            }
        });

        return false;
};
function uitTop10(kennisId){
    $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/account/includes/kennis_check.php',
            dataType : 'html',
            data: {
                action : 'uitTop10',
                kennis_id : kennisId
            },
            success : function(data){
                $('#top_10_list').html(data);
            }
        });

        return false;
}

$(function(){
    $("#top_10_list").sortable({
        connectWith:'.kennis_top_10',
        update:function(){
            $.ajax({
                type:"POST",
                url:"<?php echo $etc_root ?>functions/account/includes/kennis_check.php",            
                data:{
                    action: 'sort_kennis',
                    source: 'top',
                    kennis:$("#top_10_list").sortable('serialize')
                },
                success:function(data){
                    $('#top_10_list').html(data);
                }
            });
        }
    });
    $("#kennis_overzicht_list").sortable({
        connectWith:'.kennis_top_10',
        update:function(){
            $.ajax({
                type:"POST",
                url:"<?php echo $etc_root ?>functions/account/includes/kennis_check.php",            
                data:{
                    action: 'sort_kennis',
                    source: 'all',
                    kennis:$("#top_10_list").sortable('serialize')
                },
                success:function(data){
                    $('#top_10_list').html(data);
                    refreshAll();
                }
            });
        }
    });
});
</script>
<div id="account_center">
    <div id="kennis_invoer">
    
        <div id="kennis_goed_in_header" class="account_header">
            <p class="info_title">Waar bent u goed in ?</p>
        </div>
        
        <div id="kennis_goed_in_form">
            <textarea id="goed_in_schrijven" name="status" cols="50" rows="4" class="textarea" onkeyup="goedinOpslaan()"><?php echo $gebruiker['is_goed_in']; ?></textarea>
        </div>
        
        <div id="kennis_invoer_header" class="account_header">
            <p class="info_title">Wat kunt u (goed) ?</p>
        </div>
        <div id="kennis_invoer_form">
            <input type="text" id="kennis_schrijven" name="kennis" class="textfield" />
            <button id="kennis_opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="kennisOpslaan()">Opslaan</button>
        </div>                
    </div>
    <div id="kennis_overzicht">
        <div id="kennis_overzicht_header" class="account_header">
            <p class="info_title">Uw reeds ingevoerde kennis</p>
        </div>
        <ul id="kennis_overzicht_list">
        <?php while($kennis = mysql_fetch_array($kennis_res)){?>
            <li id="kennis_<?php echo $kennis['id']; ?>" class="kennis" onmouseover="this.className='kennis kennis_hover'" onmouseout="this.className='kennis'">
                <div class="kennis_left"><?php echo $kennis['kennis']; ?></div>
                <div class="kennis_right">
                    <div class="kennis_delete">
                        <img class="kennis_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder kennis" title="Verwijder kennis" onclick="kennisVerwijderen(<?php echo $kennis['id'] ?>)" />
                    </div>
                    <div class="kennis_social">
                    <?php
                        $what = 'COUNT(gebruiker) AS aantal'; $from = 'portal_gebruiker_kenniskaart'; $where = 'kennis = '.$kennis['id'].' AND gebruiker != '.$gebruiker_id;
                            $collega_kennis = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                        if($collega_kennis['aantal'] > 0){
                            if($collega_kennis['aantal'] == 1){$title = $collega_kennis['aantal'].' collega kan dit ook';}
                            else{$title = $collega_kennis['aantal'].' collega\'s kunnen dit ook';}?>
                            <img class="collega_kennis" src="<?php echo $etc_root ?>functions/account/css/images/collega_kennis.png" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
                    <?php   $what = 'b.gebruikersnaam, b.voornaam, b.achternaam'; $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)'; 
                            $where = 'a.kennis = '.$kennis['id'].' AND a.gebruiker != '.$gebruiker_id;
                            $kennis_collegas = sqlSelect($what, $from, $where);
                            //dit is om later een sociale functie toe te voegen                            
                        }
                    ?>
                    </div>
                </div>
            </li>
        <?php }?>
        </ul>
    </div>
</div>
<div id="account_right">
    <div id="top_10_kennis">
        <div id="kennis_top_10_header" class="account_header">
            <p class="info_title">Uw kennis top 10</p>
        </div>
        <ul id="top_10_list" class="kennis_top_10">
        <?php 
        $volgnummer = 1;
        while($topkennis = mysql_fetch_array($kennis_res2)){
            if($volgnummer < 11){
            ?>
            <li id="kennis_<?php echo $topkennis['id']; ?>" class="top_kennis" onmouseover="this.className='top_kennis kennis_hover'" onmouseout="this.className='top_kennis'">
                <div class="kennis_left">
                    <span class="kennis_volgnummer"><?php echo $volgnummer; ?></span>
                    &nbsp;<?php echo $topkennis['kennis']; ?>
                </div>
                <div class="kennis_right">
                    <div class="kennis_uit_top_10" onmouseover="this.className='kennis_uit_top_10 kennis_uit_top_10_hover'" onmouseout="this.className='kennis_uit_top_10'" 
                         onclick="uitTop10(<?php echo $topkennis['id'] ?>)" >
                         Uit top 10
                    </div>
                    <div class="kennis_delete">
                        <img class="kennis_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder kennis" title="Verwijder kennis" onclick="kennisVerwijderen(<?php echo $topkennis['id'] ?>)" />
                    </div>
                    <div class="kennis_social">
                    <?php
                        $what = 'COUNT(gebruiker) AS aantal'; $from = 'portal_gebruiker_kenniskaart'; $where = 'kennis = '.$topkennis['id'].' AND gebruiker != '.$gebruiker_id;
                            $collega_kennis = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                        if($collega_kennis['aantal'] > 0){
                            if($collega_kennis['aantal'] == 1){$title = $collega_kennis['aantal'].' collega kan dit ook';}
                            else{$title = $collega_kennis['aantal'].' collega\'s kunnen dit ook';}?>
                            <img class="collega_kennis" src="<?php echo $etc_root ?>functions/account/css/images/collega_kennis.png" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
                    <?php   $what = 'b.gebruikersnaam, b.voornaam, b.achternaam'; $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)'; 
                            $where = 'a.kennis = '.$topkennis['id'].' AND a.gebruiker != '.$gebruiker_id;
                            $kennis_collegas = sqlSelect($what, $from, $where);
                            //dit is om later een sociale functie toe te voegen                            
                        }
                    ?>
                    </div>
                </div>
            </li>
        <?php
            $volgnummer++;
            }
        }
        //als er minder dan 10 zijn, vul de rest op met lege plekken
        if($volgnummer < 11){
            for($i = $volgnummer; $i < 11; $i++){?>
            <li class="top_kennis top_kennis_leeg">
                <span class="kennis_volgnummer"><?php echo $i; ?></span>
                &nbsp;leeg
            </li>
        <?php }
        }?>
        </ul>
    </div>
</div>