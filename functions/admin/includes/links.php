<?php
    $what = 'a.top_positie, b.id, b.link, b.omschrijving, b.categorie';
    $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
    $where = 'a.gebruiker = 999 ORDER BY b.omschrijving ASC';
        $links_res = sqlSelect($what, $from, $where);
    $where = 'a.gebruiker = 999 AND a.top_positie > 0 ORDER BY a.top_positie ASC';
        $links_res2 = sqlSelect($what, $from, $where);
    
    
?>
<script>
function refreshForm() {
        $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/links_check.php',
            dataType : 'html',
            data: {action : 'refreshForm'},
            success : function(data){
                $('#links_invoer_form').html(data);
            }
        });

        return false;
};

function refreshAll() {
        $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/links_check.php',
            dataType : 'html',
            data: {action : 'refreshAll'},
            success : function(data){
                $('#links_overzicht_list').html(data);
            }
        });

        return false;
};

function refreshTop10() {
        $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/admin/includes/links_check.php',
            dataType : 'html',
            data: {action : 'refreshTop10'},
            success : function(data){
                $('#top_10_list').html(data);
            }
        });

        return false;
};

function linkOpslaan() {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/links_check.php',
            dataType : 'html',
            data: {
                action : 'createlink',
                url : $('#url_invoer').val(),
                beschrijving : $('#url_beschrijving_invoer').val(),
                categorie : $('#url_categorie_select').val()
            },
            success : function(data){
                $('#links_invoer_form').html(data);
                $(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};

function linkVerwijderen(linkId) {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/links_check.php',
            dataType : 'html',
            data: {
                action : 'deletelink',
                link_id : linkId
            },
            success : function(data){
                refreshAll();
                refreshTop10();
            }
        });

        return false;
};

function uitTop10(linkId){
    $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root ?>functions/admin/includes/links_check.php',
            dataType : 'html',
            data: {
                action : 'uitTop10',
                link_id : linkId
            },
            success : function(data){
                refreshTop10();
            }
        });

        return false;
}

jQuery('#url_beschrijving_invoer').keyup(function(){
    var text = jQuery('#url_beschrijving_invoer').val();
    var counter;
    var maxLength = 75;
    if(text.length < 45){
        counter = maxLength - text.length;
        text = text.substr(0,maxLength);        
        $('#overgebleven_beschrijving').removeClass('code_red code_orange');
    }else if(text.length < maxLength){
        counter = maxLength - text.length;
        text = text.substr(0,maxLength);        
        $('#overgebleven_beschrijving').removeClass('code_red').addClass('code_orange');
    }else if(text.length == maxLength){
        text = text.substr(0,maxLength);
        counter = 0;
        $('#overgebleven_beschrijving').removeClass('code_organge').addClass('code_red');
    }else{
        text = text.substr(0,maxLength);
        counter = 0;
        $('#overgebleven_beschrijving').removeClass('code_organge').addClass('code_red');
    }
    jQuery('#url_beschrijving_invoer').val(text);
    jQuery('#overgebleven_aantal').html(counter);
    
});

$(function(){
    $("#top_10_list").sortable({
        connectWith:'.links_top_10',
        update:function(){
            $.ajax({
                type:"POST",
                url:"<?php echo $etc_root ?>functions/admin/includes/links_check.php",            
                data:{
                    action: 'sort_link',
                    source: 'top',
                    link:$("#top_10_list").sortable('serialize')
                },
                success:function(data){
                    $('#top_10_list').html(data);
                    refreshTop10();
                }
            });
        }
    });
    $("#links_overzicht_list").sortable({
        connectWith:'.links_top_10',
        update:function(){
            $.ajax({
                type:"POST",
                url:"<?php echo $etc_root ?>functions/admin/includes/links_check.php",            
                data:{
                    action: 'sort_link',
                    source: 'all',
                    link:$("#top_10_list").sortable('serialize')
                },
                success:function(data){
                    $('#top_10_list').html(data);
                    refreshAll();
                    refreshTop10();
                }
            });
        }
    });
});
</script>
<div id="admin_center">
    <div id="links_invoer">
        <div id="links_invoer_header" class="account_header">
            <p class="info_title">Nieuwe link toevoegen</p>
        </div>
        <div id="links_invoer_form">
            <label for="url">Voer hier het hele webadres in (dit begint met http://)</label>
            <div class="link_input">
                <input type="text" id="url_invoer" name="url" class="textfield" />
            </div>
            
            <label for="url_beschrijving">Schrijf een korte beschrijving van de link</label>
            <div class="link_input">
                <input type="text" id="url_beschrijving_invoer" name="beschrijving" class="textfield" />
                
                <div id="overgebleven_beschrijving">
<span id="overgebleven_tekst">Resterend aantal tekens:</span>
                    <span id="overgebleven_aantal">75</span>
                </div>
            </div>
            
            <label for="url_categorie">Wat voor soort link is het ?</label>
            <div class="link_input">
                <select name="categorie" id="url_categorie_select" class="textfield">
                    <option value="zakelijk">Zakelijk</option>
                    <option value="prive">Priv&eacute;</option>
                    <option value="hobby">Hobby</option>
                    <option value="overig">Overig</option>
                </select>
            </div>
            
            <button id="link_opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="linkOpslaan()">
                Opslaan
            </button>
        </div>
    </div>
    <div id="links_overzicht">
        <div id="links_overzicht_header" class="account_header">
            <p class="info_title">Uw reeds ingevoerde links</p>
        </div>
        <ul id="links_overzicht_list">
        <?php while($link = mysql_fetch_array($links_res)){?>
            <li id="link_<?php echo $link['id']; ?>" class="link" onmouseover="this.className='link link_hover'" onmouseout="this.className='link'">
                <div class="link_left">
                    <a href="<?php echo $link['link']; ?>" title="<?php echo $link['omschrijving'] ?>" target="_blank"><?php echo $link['omschrijving'] ?></a>
                    <span class="categorie">Categorie:  <?php echo $link['categorie']; ?></span>
                </div>
                <div class="link_right">
                    <div class="link_delete">
                        <img class="link_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder link" title="Verwijder link" onclick="linkVerwijderen(<?php echo $link['id'] ?>)" />
                    </div>
                </div>
            </li>
        <?php }?>
        </ul>
    </div>
</div>
<div id="admin_right">
    <div id="top_10_links">
        <div id="links_top_10_header" class="account_header">
            <p class="info_title">Uw links top - 10</p>
        </div>
        <ul id="top_10_list" class="links_top_10">
        <?php 
        $volgnummer = 1;
        while($link = mysql_fetch_array($links_res2)){
            if($volgnummer < 11){?>
            <li id="link_<?php echo $link['id']; ?>" class="top_link" onmouseover="this.className='top_link link_hover'" onmouseout="this.className='top_link'">
                <div class="link_left">
                    <span class="link_volgnummer"><?php echo $volgnummer; ?></span>
                    <a href="<?php echo $link['link']; ?>" title="<?php echo $link['omschrijving'] ?>" target="_blank"><?php echo $link['omschrijving'] ?></a>
                    <span class="categorie">Categorie: <?php echo $link['categorie']; ?></span>
                </div>
                <div class="link_right">
                    <div class="link_uit_top_10" onmouseover="this.className='link_uit_top_10 link_uit_top_10_hover'" onmouseout="this.className='link_uit_top_10'" 
                         onclick="uitTop10(<?php echo $link['id'] ?>)" >
                         Uit top 10
                    </div>
                    <div class="link_delete">
                        <img class="link_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder link" title="Verwijder link" onclick="linkVerwijderen(<?php echo $link['id'] ?>)" />
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
            <li class="top_link top_link_leeg">
                <span class="link_volgnummer"><?php echo $i; ?></span>
                &nbsp;leeg
            </li>
        <?php }
        }?>
        </ul>
    </div>
</div>
