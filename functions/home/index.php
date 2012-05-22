<?php
    //aangezien het ophalen en neerzetten van de informatie van deze pagina erg groot is, wordt dit extern gedaan:
    include($_SERVER['DOCUMENT_ROOT'].$etc_root.'functions/home/includes/haal_nieuws_op.php');
	
	//kijk of er urentool datums moeten worden bijgemaakt voor deze gebruiker
	//checkUrentoolDatums( $login_id );
?>
<script>
	function herlaadReactieform(id, doel) {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root; ?>functions/home/includes/reactie_ajax.php',
            dataType : 'html',
            data: {
                id : id,
                action: 'toevoegen',
                doel: doel,
                reactie : $('#' + doel + '_reactie_'+id).val()
            },
            success : function(data){
                $('#' + doel + '_reacties_'+id).html(data);
                $('#' + doel + '_reactie_'+id).val('');
            }
        });

        return false;
};

function deleteReactie(id, doel, reactie) {
        $.ajax({
            type : 'POST',
            url : '<?php echo $etc_root; ?>functions/home/includes/reactie_ajax.php',
            dataType : 'html',
            data: {
                id : id,
                action: 'verwijderen',
                doel: doel,
                reactie : reactie
            },
            success : function(data){
                $('#' + doel + '_reacties_'+id).html(data);
                $('#' + doel + '_reactie_'+id).val('');
            }
        });

        return false;
};
function leesVerderNieuwslijst(filter, interval) {
    if(interval === 1){
        $('#waiting').show(600);
        $('#overig_nieuwslijst').html('');
    }
        $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/home/includes/nieuwslijst.php',
            dataType : 'html',
            data: { filter : filter, interval: interval },
            success : function(data){
                if(interval === 1){$('#waiting').hide(600);}
                $('#overig_nieuwslijst').append(data);
                if(((huidigeDatum - startDatum) - ((60*60*24*10) * interval)) > 0){
                    $('#overige_nieuws_meer_lezen').html('<span class="next_batch" onclick="leesVerderNieuwslijst(\''+ filter +'\', ' + (interval + 1) + ')">Meer laden...</span>');
                }else{$('#overige_nieuws_meer_lezen').html('');}
                if(filter === 'fotos'){
                    $(".ppt").remove();
                    $(".pp_overlay").remove();
                    $(".pp_pic_holder").remove();
                    $("a.iframe").fancybox(
                    {'overlayShow': true,
                    'overlayColor': '#000',
                    'overlayOpacity': 0.7,
                    'hideOnContentClick': true,  
                    'titleShow': false,
                    'transitionIn': 'elastic',
                    'transitionOut': 'elastic',
                    'speedIn': 600,
                    'speedOut': 200,
                    'centerOnScroll': true,
                    'scrolling': 'auto',
                    'cyclic': true,
                    'width': 750,
                    'height': 750
                    });
                }
            }
        });

        return false;
};
</script>
<div id="dashboard">
    <div id="dashboard_left">
       	<div id="links">Links</div>
        <?php
            $what = 'b.link, b.omschrijving'; 
            $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
            $where = 'a.gebruiker = 999 AND a.top_positie > 0 ORDER BY a.top_positie ASC LIMIT 10';
                $aantal_top_links = countRows($what, $from, $where); 
            if($aantal_top_links > 0){
               $links = sqlSelect($what, $from, $where);
                while($link = mysql_fetch_array($links)){?>
                    <div class="link">
                        <div class="link_url">
                            <a href="<?php echo $link['link']; ?>" target="_blank" title="<?php echo $link['omschrijving']; ?>"><?php echo $link['omschrijving']; ?></a>
                        </div>
                    </div>
            <?php }
            if($aantal_top_links < 10){
                $what = 'b.link, b.omschrijving'; 
                $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
                $where = 'a.gebruiker = 999 AND a.top_positie = 0 ORDER BY a.top_positie ASC LIMIT '.(10 - $aantal_top_links);
                    $aantal_links = countRows($what,$from,$where);
                    if($aantal_links > 0){
                        $links = sqlSelect($what,$from,$where);
                        while($link = mysql_fetch_array($links)){?>
                        <div class="link">
                            <div class="link_url">
                                <a href="<?php echo $link['link']; ?>" target="_blank" title="<?php echo $link['omschrijving']; ?>"><?php echo $link['omschrijving']; ?></a>
                            </div>
                        </div>
            <?php       }
                    }
            }
            }else{echo 'Er zijn nog geen algemene links toegevoegd';}?> 
        <div id="agenda">Agenda</div>
        <?php include($_SERVER['DOCUMENT_ROOT'].$etc_root.'/functions/home/includes/agenda.php'); ?>
    </div>
    <div id="dashboard_center">
        <div id="nieuws_lijst">
            <div id="belangrijk nieuws">
            <?php 
                if(count($belangrijk_nieuws) > 0){
                    krsort($belangrijk_nieuws);
                    echo '<h2>Belangrijk nieuws </h2>';
                    foreach($belangrijk_nieuws as $nieuws){
                        echo $nieuws;
                    }
                }?>
            </div>
            <div id="overig_nieuws">
            	<h2>Overig nieuws</h2>
	            <div id="homepage_navigatie">
	                <div id="filters">
	                    <div id="filter_alles"><button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="leesVerderNieuwslijst('alles', 1)">Alles</button></div>
	                    <div id="filter_nieuws"><button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="leesVerderNieuwslijst('nieuws', 1)">Nieuws</button></div>
	                    <div id="filter_foto"><button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="leesVerderNieuwslijst('fotos', 1)">Foto's</button></div>
	                    <div id="filter_reactie"><button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="leesVerderNieuwslijst('status', 1)">Activiteiten</button></div>
	                </div>
	                <?php
	                /*
	                <div id="zoekbalk">
	                    <form>
	                        <input id="vul_in" type="text" name="" value="zoeken" class="textfield" />
	                        <img id="submit" src="<?php echo $etc_root ?>functions/<?php echo $functie_get ?>/css/images/search.png" onclick="showUser(document.getElementById('vul_in').value)"/>
	                    </form>
	                </div>
	                */
	                ?>
	            </div>
	            <div id="waiting" style="display:none;">
	                <!--<span id="waiting_text">De lijst wordt geladen</span>-->
	                <span id="waiting_img"><img src="<?php echo $etc_root ?>functions/<?php echo $functie_get; ?>/css/images/ajax_loader.gif" alt="loading" />&nbsp;</span>
	            </div>
	            <div id="overig_nieuwslijst">                
	            <?php 
	                if(count($normaal_nieuws) > 0){
	                    krsort($normaal_nieuws);
	                    foreach($normaal_nieuws as $nieuws){
	                        echo $nieuws;
	                    }
	                }
	            ?>  
	            </div>          
                <div id="overige_nieuws_meer_lezen">
                </div>
            </div>
        </div>
    </div>
    <div id="dashboard_right">
        <?php 
            if($_GET['nieuws_id'] != null){
                include($_SERVER['DOCUMENT_ROOT'].$etc_root.'/functions/'.$functie_get.'/includes/toonnieuws.php');
            }
        ?>
    </div>
</div>
