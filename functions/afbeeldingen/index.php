<?php
    $what = 'DISTINCT a.id, a.naam, UNIX_TIMESTAMP(a.gemaakt_op)'; $from = 'portal_album a LEFT JOIN portal_image AS b ON (b.album = a.id)'; $where='a.actief = 1 AND b.id IS NOT NULL ORDER BY a.naam ASC';
        $album_res = sqlSelect($what, $from, $where);
?>
<div id="afbeeldingen">
<script>
function reloadImageTop(filter, view){
    $.ajax({
            type : 'GET',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'reloadImageTop',
                view : view,
                filter: filter
            },
            success : function(data){
                $('#afbeeldingen_top').html(data);
            }
        });
        return false;
}

function loadView(filter, view){
    reloadImageTop(filter, view);
    $('#afbeeldingen_result').hide("slide", {direction: "up"}, 0);
    $.ajax({
            type : 'GET',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'loadView',
                view : view,
                filter: filter
            },
            success : function(data){
                $('#afbeeldingen_result').html(data);
                $('#afbeeldingen_result').show("slide", {direction: "up"}, 0);
                if(view == 'foto'){
                    $("a.iframe").fancybox({
                    'overlayShow': true,
                    'overlayColor': '#000',
                    'overlayOpacity': 0.7,
                    'hideOnContentClick': true,
                    'titleShow': true,
                    'titlePosition' : 'outside',
                    'titleFormat' : function(title, currentArray, currentIndex, currentOpts) {
                        return '<span id="fancybox-title-over">Afbeelding ' + (currentIndex + 1) + ' van ' + currentArray.length + '</span>';
                    }, 
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

function fotoPaginering(huidigNummer, paginaNummer){
    if(huidigNummer > paginaNummer){
        $('#afbeeldingen_result').hide("slide", {direction: "left"}, 0);
    }else{
        $('#afbeeldingen_result').hide("slide", {direction: "right"}, 0);
    }
    $.ajax({
            type : 'GET',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'paginering',
                pagina : paginaNummer
            },
            success : function(data){
                $('#afbeeldingen_result').html(data);
                if(huidigNummer > paginaNummer){
                    $('#afbeeldingen_result').show("slide", {direction: "right"}, 0);
                }else{
                    $('#afbeeldingen_result').show("slide", {direction: "left"}, 0);
                }
            }
        });

        return false;
};

function loadAlbum(albumId, filter, view) {
        $('#afbeeldingen_result').hide("slide", {direction: "left"}, 0);
        $.ajax({
            type : 'GET',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'loadAlbum',
                album_id : albumId,
                filter: filter,
                view: view
            },
            success : function(data){
                $('#afbeeldingen_result').html(data);
                $('#afbeeldingen_result').show("slide", {direction: "right"}, 0);
                $("a.iframe").fancybox({
                    'overlayShow': true,
                    'overlayColor': '#000',
                    'overlayOpacity': 0.7,
                    'hideOnContentClick': true,
                    'titleShow': true,
                    'titlePosition' : 'outside',
                    'titleFormat' : function(title, currentArray, currentIndex, currentOpts) {
                        return '<span id="fancybox-title-over">Afbeelding ' + (currentIndex + 1) + ' van ' + currentArray.length + '</span>';
                    }, 
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
        });

        return false;
};

function zoekAlbum(zoekWoord) {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'zoekAlbum',
                zoekwoord : zoekWoord
            },
            success : function(data){
                if(zoekWoord.length > 0){
                    $('#end_album_search').show();
                }else{
                    $('#end_album_search').hide();
                    $('#zoek_album').val('');
                }
                $('#afbeeldingen_result').html(data);
            }
        });

        return false;
};

function zoekFoto(zoekWoord) {
        $.ajax({
            type : 'POST',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'zoekFoto',
                zoekwoord : zoekWoord
            },
            success : function(data){
                if(zoekWoord.length > 0){
                    $('#end_photo_search').show();
                }else{
                    $('#end_photo_search').hide();
                    $('#zoek_foto').val('');
                }
                $('#afbeeldingen_result').html(data);
            }
        });

        return false;
};

function toonAlleen(filter, view){
    $('#afbeeldingen_result').hide("slide", {direction: "right"}, 0);
    $.ajax({
            type : 'GET',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'loadView',
                view : view,
                filter: filter
            },
            success : function(data){
                $('#afbeeldingen_result').html(data);
                $('#afbeeldingen_result').show("slide", {direction: "left"}, 0);
            }
        });

        return false;
};

/*function toonFilter(filter, view){
    $('#afbeeldingen_result').hide("slide", {direction: "up"}, 0);
    reloadImageTop(filter, view);
    $.ajax({
            type : 'GET',
            url : '/portal/functions/afbeeldingen/includes/albumajax.php',
            dataType : 'html',
            data: {
                action : 'toonAlleen',
                filter : filter
            },
            success : function(data){
                $('#afbeeldingen_result').html(data);
                $('#afbeeldingen_result').show("slide", {direction: "up"}, 0);
            }
        });

        return false;
};*/
</script>
    <div id="afbeeldingen_top">
        <div id="view">
            <div id="view_header">Bekijk</div> 
            <div class="album_view active_view" onmouseover="this.className='album_view view_hover'" onmouseout="this.className='album_view'" onclick="loadView('alles', 'album')">Albums</div>
            <div class="foto_view" onmouseover="this.className='foto_view view_hover'" onmouseout="this.className='foto_view'"  onclick="loadView('profiel', 'foto')">Foto's</div>
        </div>
        <div id="filters">
            <div id="profiel_nieuws">
                <div id="profiel_nieuws_header">Toon</div>
                <div class="alles_filter active_filter" onmouseover="this.className='alles_filter filter_hover'" onmouseout="this.className='alles_filter'" 
                     onclick="loadView('alles', 'album')">
                    Alle
                </div>
                <div class="nieuws_filter" onmouseover="this.className='nieuws_filter filter_hover'" onmouseout="this.className='nieuws_filter'" 
                     onclick="loadView('nieuws', 'album')">
                    Nieuwsalbum's
                </div> 
                <div class="profiel_filter" onmouseover="this.className='profiel_filter filter_hover'" onmouseout="this.className='profiel_filter'" 
                     onclick="loadView('profiel', 'album')">
                    Profielalbum's
                </div>
            </div>
        </div>
        <div id="album_zoeken" style="display:block;">
                <div id="zoeken_header"> Zoek naar een album:</div> 
                <input type="text" class="textfield" id="zoek_album" onkeyup="zoekAlbum(this.value)" />
                <img id="end_search" src="/portal/functions/afbeeldingen/css/images/end_search.png" onclick="zoekAlbum('')" alt="end search" 
                     title="stop met zoeken" style="display:none;" />
        </div>
        <div id="foto_zoeken" style="display:none;">
                <div id="zoeken_header"> Zoek naar een foto:</div> 
                <input type="text" class="textfield" id="zoek_foto" onkeyup="zoekFoto(this.value)" />
                <img id="end_search" src="/portal/functions/afbeeldingen/css/images/end_search.png" onclick="zoekFoto('')" alt="end search" 
                     title="stop met zoeken" style="display:none;" />
        </div>
    </div>
    <div id="afbeeldingen_result">
    <?php
        $what = 'b.path, b.album'; $from = 'portal_gebruiker a LEFT JOIN portal_image AS b ON (b.id = a.profielfoto)';
        $where = 'a.actief = 1 AND b.actief = 1 ORDER BY b.id ASC';
        $profielfoto_album = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/portal/files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path']);
            if($width > $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url(\'/portal/lib/slir/'.slirImage(400,200).'/portal/files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].'\') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }elseif($width == $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url(/portal/lib/slir/'.slirImage(200,200).'/portal/files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }else{
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url(/portal/lib/slir/'.slirImage(0,200,0).'/portal/files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';   
            
            }
            $array[] ='    
            <div class="album profielfotos" onmouseover="this.className=\'album profielfotos album_hover\'" onmouseout="this.className=\'album profielfotos\'" onclick="loadAlbum(0, \'alles\', \'album\')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">Profielfoto\'s</div>
            </div>';
        
        while($album = mysql_fetch_array($album_res)){
            $what = 'path, omschrijving'; $from = 'portal_image'; $where = 'album = '.$album['id'].' AND volgorde = 1 AND actief = 1 ';
                $omslagfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path']);
            
            if($width > $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url(\'/portal/lib/slir/'.slirImage(400,200).'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path'].'\') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }elseif($width == $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url(/portal/lib/slir/'.slirImage(200,200).'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }else{
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url(/portal/lib/slir/'.slirImage(0,200,0).'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';   
            
            }                  
            /*
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path']);
            if($width > $height){
            $albums[] .= '<img src="/portal/lib/slir/'.slirImage(400,200).'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path'].'" alt="'.$omslagfoto['omschrijving'].'" title="'.$omslagfoto['omschrijving'].'" />';
            }elseif($width == $height){
            $albums[] .= '<img src="/portal/lib/slir/'.slirImage(200,200).'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path'].'" alt="'.$omslagfoto['omschrijving'].'" title="'.$omslagfoto['omschrijving'].'" />';
            }else{
            $albums[] .= '<img src="/portal/lib/slir/'.slirImage(200,0,0).'/portal/files/album/'.$album['id'].'/'.$omslagfoto['path'].'" alt="'.$omslagfoto['omschrijving'].'" title="'.$omslagfoto['omschrijving'].'" />';             }
            */
            $array[] ='    
            <div class="album" onmouseover="this.className=\'album album_hover\'" onmouseout="this.className=\'album\'" onclick="loadAlbum('.$album['id'].', \'alles\', \'album\')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">'.$album['naam'].'</div>
            </div>';
        }
        //bepaal hoeveer arrays er in totaal zijn
    $aantal_arrays = count($array);
    
    //deel dit door het aantal kolommen dat we willen : 3
    //de som verdelen we in een geheel getal en de decimaal.
    //$rounded = strval(round(($aantal_albums/3), 1)).'<br />';
    list($heel, $decimaal) = explode('.', round(($aantal_arrays/3), 1));
    
    //-------KOLOM 1
    
    //kijk of er meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kolom">
        <?php //begin nu met tellen.
        $i_1 = 0; 
        if($decimaal != null){
            if($decimaal == '3'){
                $aantal_in_kolom_1 = $heel+1;
            }elseif($decimaal == '7'){
                $aantal_in_kolom_1 = $heel+1;
            }
        }else{
            $aantal_in_kolom_1 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_1 <= $aantal_in_kolom_1-1 && $i_1 <= $aantal_arrays-1){?>
            <div class="afbeelding_item">
                <?php echo $array["$i_1"] ?>
            </div>
                   <?php 
            $i_1++;
                }

            }?>
    </div>  
    <?php }else{?>
            <div class="act_a">Dit filter leverde geen activiteiten op. Probeer het alstublieft opnieuw.</div> 
    <?php } 
    
    //-------KOLOM 2
    
    //kijk of  meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kolom">
        <?php //tellen voor de tweede kolom
        $i_2 = $i_1;
        if($decimaal != null){
            if($decimaal == '3'){
                $aantal_in_kolom_2 = $heel;
            }elseif($decimaal == '7'){
                $aantal_in_kolom_2 = $heel+1;
            }
        }else{
            $aantal_in_kolom_2 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_2 <= $aantal_in_kolom_2+$i_1-1 && $i_2 <= $aantal_arrays-1){?>
            <div class="afbeelding_item">
                <?php echo $array["$i_2"] ?>
            </div>
                   <?php 
            $i_2++;
            }
        }
         ?>
    </div>
        <?php
    }
    
    //-------KOLOM 3
    
    //kijk of  meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kolom_last">
        <?php //tellen voor de tweede kolom
        $i_3 = $i_2;
        if($decimaal != null){
            if($decimaal == '3'){
                $aantal_in_kolom_3 = $heel;
            }elseif($decimaal == '7'){
                $aantal_in_kolom_3 = $heel;
            }
        }else{
            $aantal_in_kolom_3 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_3 <= $aantal_in_kolom_3+$i_2-1 && $i_3 <= $aantal_arrays-1){?>
            <div class="afbeelding_item">
                <?php echo $array["$i_3"] ?>
            </div>
                   <?php 
            $i_3++;
            }
        }
         ?>
    </div>
        <?php
    }
?>
    </div>
</div>