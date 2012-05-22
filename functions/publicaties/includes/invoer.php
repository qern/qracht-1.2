<?php 
if($_SESSION['admin'] != null){
echo '<div id="mededelingen">';
//afhankelijk van het feit of er een id is meegegeven of niet, pas de header aan
if($_GET['nieuws_id'] == null){
    $geen_uploader = 1;
    echo '<h2>Maak nieuws aan</h2>';
    if($_SESSION['error'] != null){
            //vul de algemene variabelen. Deze worden ook zo gezet, als er gewijzigd wordt
            if($_SESSION['titel'] != null){$titel = $mededeling['titel']; unset($_SESSION['titel']);}
            if($_SESSION['belangrijk'] != null){$belangrijk = $mededeling['belangrijk']; unset($_SESSION['belangrijk']);}
            if($_SESSION['teaser'] != null){$teaser = $_SESSION['teaser']; unset($_SESSION['teaser']);}
            if($_SESSION['inhoud'] != null){$inhoud =$_SESSION['inhoud']; unset($_SESSION['inhoud']);}
            if($_SESSION['publicatiedatum'] != null){$publicatiedatum = $_SESSION['publicatiedatum']; unset($_SESSION['publicatiedatum']);}
            if($_SESSION['archiveerdatum'] != null){$archiveerdatum = $_SESSION['archiveerdatum']; unset($_SESSION['archiveerdatum']);}
            
            echo '<div id="errors">';
            foreach($_SESSION['error'] as $error){
                echo '<div class="error">'.$error.'</div>';
            }
            echo '</div>';
    }
}
else{
    echo '<h2>Wijzig nieuws</h2>';
    $nieuws_id = $_GET['nieuws_id'];
    //we gaan wijzigen, pak dus de gegevens voor de mededeling uit de database
    $what = 'titel, teaser, inhoud, DATE_FORMAT(publicatiedatum, \'%e-%m-%Y\') publicatie, DATE_FORMAT(archiveerdatum, \'%e-%m-%Y\') archiveer, is_belangrijk, afbeelding';
    $from = 'portal_nieuws';
    $where = 'id ='.$nieuws_id;
        $mededeling = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    //vul de algemene variabelen. Deze worden ook zo gezet, als er iets fout is gegaan...
    $titel = $mededeling['titel']; $teaser = $mededeling['teaser']; $inhoud = $mededeling['inhoud']; $publicatiedatum = $mededeling['publicatie']; $archiveerdatum = $mededeling['archiveer'];
    
    //waarschijnlijk is de mededeling ook gekoppeld aan een organisatie. Maak een array met ze allemaal erin. 
    //Deze kan dan iets later worden vergeleken met de organisaties
    $what = 'gebruiker'; $from = 'portal_nieuws_gebruiker'; $where = 'nieuws = '.$nieuws_id;
        $result = sqlSelect($what, $from, $where);
    
    while($gebruikers = mysql_fetch_array($result)){
        $gebruiker_ids[] = $gebruikers['gebruiker']; //maak de array:
    }
            
    //publicatie is niet altijd gevuld. zo niet: laat deze dan leeg!
    if($mededeling['publicatie'] == '0-00-0000'){
        $publicatiedatum = null;
    }else{
        $publicatiedatum = $mededeling['publicatie'];
    }
    
    //archiveerdatum is niet altijd gevuld. zo niet: laat deze dan leeg!
    if($mededeling['archiveer'] == '0-00-0000'){
        $archiveerdatum = null;
    }else{
        $archiveerdatum = $mededeling['archiveer'];
    }
    
     //tag(s) ophalen
    $what_tags = 'b.id, b.naam';
    $from_tags = 'portal_nieuws_tag a, portal_tag b';
    $where_tags = "a.nieuws = $nieuws_id AND b.id = a.tag";
        $aantal_tags = countRows($what_tags, $from_tags, $where_tags); $result_tags = sqlSelect($what_tags, $from_tags ,$where_tags);
        
    //bestand(en) ophalen
    $what_bestanden = 'id, path';
    $from_bestanden = 'portal_nieuws_bestand';
    $where_bestanden = "nieuws = $nieuws_id";
        $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden); $result_bestanden = sqlSelect($what_bestanden, $from_bestanden, $where_bestanden);
    
    //Album ophalen. Zowel het totale aantal aan albums, als eventueel het gekoppelde album
    $what_album = 'COUNT(a.id) aantal, b.album, c.naam';
    $from_album = 'portal_album a 
                    LEFT JOIN portal_nieuws_album AS b ON (b.nieuws = '.$nieuws_id.')
                    LEFT JOIN portal_album AS c ON (c.id = b.album)';
    $where_album = 'a.actief = 1 AND c.actief = 1';
        $album = mysql_fetch_assoc(sqlSelect($what_album, $from_album, $where_album));
    
}
if($album['album'] == 0){
        $what = 'MAX(id) AS id'; $from = 'portal_album'; $where='1=1';
            $album = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            $album_id = $album['id'] + 1;
                
        $table = 'portal_album'; $what = 'naam, gemaakt_door, gemaakt_op';
        $with_what = "(SELECT titel FROM portal_nieuws WHERE id = $nieuws_id), $login_id, NOW()";
            $nieuw_album_aanmaken = sqlInsert($table, $what, $with_what);
            
        $table='portal_nieuws_album'; $what = 'nieuws, album'; $with_what = "$nieuws_id, $album_id";
            $koppel_album_aan_nieuws = sqlInsert($table, $what, $with_what);
}else{
        $album_id = $album['album'];
}
 
            
        ?>
<script>
function uploaders(){
    var uploader = new qq.FileUploader({
    element: document.getElementById('upload_image'),
    template: '<div class="qq-uploader">' + 
                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                '<div class="qq-upload-button">Kies afbeelding</div>' + 
             '</div>',
    action: '<?php echo $etc_root; ?>functions/publicaties/includes/upload_nieuws_foto.php',
    allowedExtensions: ['jpeg', 'jpg', 'png', 'gif'],
    debug: true,
    params: { nieuws: '<?php echo $nieuws_id ?>', album: '<?php echo $album_id; ?>'},
    onSubmit : function(file , ext){
        //if (ext && new RegExp('^(' + allowed.join('|') + ')$').test(ext)){
        if (ext && /^(jpg|png|jpeg|gif)$/.test(ext)){
            // extension is not allowed
            jQuery('#photoerror').html('Fout: er mogen alleen afbeeldingen worden ge�pload');
            // cancel upload
            return false;   
        }else{
                        
        } 
    },

    listElement: document.getElementById('image-queue'),
    //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]],
    onComplete: function(id, fileName, responseJSON){
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_refresh.php',
            dataType : 'html',
            data: {action : 'refresh_foto', album: '<?php echo $album_id; ?>'},
            success : function(data){
                jQuery('#album_afbeeldingen').html(data);
            }
        });
                        
        return false;
    }
});
    var uploader = new qq.FileUploader({
    element: document.getElementById('upload_file'),
    template: '<div class="qq-uploader">' + 
                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                '<div class="qq-upload-button">Kies bestand</div>' +
                 
             '</div>',
    action: '<?php echo $etc_root; ?>functions/publicaties/includes/upload_nieuws_bestand.php',
    debug: true,
    params: { nieuws: '<?php echo $nieuws_id ?>' },
    listElement: document.getElementById('file-queue'),
    onComplete: function(id, fileName, responseJSON){
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_refresh.php',
            dataType : 'html',
            data: {action : 'refresh_file', nieuws_id : '<?php echo $nieuws_id ?>'},
            success : function(data){
                jQuery('#bestanden_lijst').html(data);
            }
        });
                        
        return false;
    }
});
}

<?php if($geen_uploader == null){ echo '$(document).ready(uploaders);';} ?>

$(function(){
    jQuery("#tag_input").autocomplete({
        source:'<?php echo $etc_root; ?>functions/publicaties/includes/tag_ajax.php',
        minLength:2
    });
});

$(function() {
        jQuery('#tag_input').keydown(function(event) {
            if (event.keyCode == '13' || event.keyCode == '188') {
                jQuery.ajax({
                    type : 'POST',
                    url : '<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_check.php',
                    dataType : 'html',
                    data: {
                        action : 'tag',
                        tag : jQuery('#tag_input').val(),
                        nieuws: '<?php echo $nieuws_id ?>'
                    },
                    success : function(data){
                        jQuery('#tag_input').val(""),
                        jQuery('#tags_lijst').html(data);
                        jQuery(".wijzig_info_success").fadeOut(5000);
                    }
                });

                return false;
            }
        });
});
function deletetag(tagId) {
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_check.php',
            dataType : 'html',
            data: {
                action : 'delete_tag',
                tag : tagId,
                nieuws: '<?php echo $nieuws_id ?>'
            },
            success : function(data){
                jQuery('#tags_lijst').html(data);
                jQuery(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};
function deleteFile(fileId) {
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_refresh.php',
            dataType : 'html',
            data: {
                action : 'refresh_file', 
                nieuws_id : '<?php echo $nieuws_id ?>',
                subaction: 'delete',
                bestand_id : fileId
            },
            success : function(data){
                jQuery('#bestanden_lijst').html(data);
                jQuery("#file-queue").html('');
            }
        });
                        
        return false;
};

function deleteImage(imageId) {
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_refresh.php',
            dataType : 'html',
            data: {action : 'refresh_foto', album: '<?php echo $album_id; ?>', subaction: 'delete', image_id : imageId},
            success : function(data){ jQuery('#album_afbeeldingen').html(data); jQuery("#image-queue").html(''); }
        }); return false;
};
</script>
<div id="nieuws_links">
<?php 
/*<iframe style="background:transparent;" width="780" height="900" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $site_name.'functions/'.$functie_get.'/includes/nieuws_beheren.php?nieuws_id='.$nieuws_id; ?>"></iframe>*/
?>
<form id="mededeling_invoer" name="nieuws" action="<?php echo $etc_root; ?>functions/publicaties/includes/nieuws_check.php" method="post" enctype="multipart/form-data">
<div id="titel_belangrijk_row">
    <label for="titel" class="tooltip" title="Voer hier de titel in voor de mededeling">Titel</label>
    <input class="textfield"  type="text" name="titel" id="titel" value="<?php echo $mededeling['titel']; ?>"/>
    <div id="belangrijk">
        <p>Is dit nieuwsitem <span class="tooltip" style="text-decoration:underline;" title="Een belangrijk nieuwitem staat altijd bovenin de lijst en is bedoeld voor nieuwsitems die iedereen moet zien.">Belangrijk</span> ?</p>
        <?php
        if($mededeling['is_belangrijk'] == 0){$niet_belangrijk = 'checked = checked';}
        elseif($mededeling['is_belangrijk'] == 1){$wel_belangrijk = 'checked = checked';}
        elseif($belangrijk == 0){$niet_belangrijk = 'checked = checked';}
        elseif($belangrijk == 1){$wel_belangrijk = 'checked = checked';}
        else{$niet = 'checked = checked';}
        ?>
        <div id="niet_belangrijk"><input type="radio" name="belangrijk" value="0" <?php echo $niet_belangrijk ?> /><span>Nee</span></div>
        <div id="wel_belangrijk"><input type="radio" name="belangrijk" value="1" <?php echo $wel_belangrijk ?> /><span>Ja</span></div>
        
    </div>
</div>
<div id="beschrijving_row">
    <label for="beschrijving" class="tooltip" title="Voer hier de korte beschrijving in van de mededeling">Intro, wordt als teaser getoond en bovenaan het complete nieuws</label>
    <textarea class="textarea" id="beschrijving" name="teaser" cols="100" rows="3"><?php echo $mededeling['teaser']; ?></textarea>
    <div id="overgebleven_beschrijving">
      <?php
        if($mededeling['teaser'] != null){ $overgebleven_aantal = 200 - strlen($mededeling['teaser']);}
        else{$overgebleven_aantal = 200;}
      ?>
      Resterend aantal tekens: <span id="overgebleven_aantal"><?php echo $overgebleven_aantal; ?></span>
    </div>
</div>
<div id="inhoud_row">
    <textarea class="textarea" id="inhoud" name="inhoud" cols="80" rows="10"><?php if($mededeling['inhoud'] != null){echo $mededeling['inhoud'];}else{ echo 'Inhoud, de (rest van de) mededeling';} ?></textarea>
</div>
<div id="publicatie_row">
    <div id="datum_publicatie">
        <label class="tooltip" for="publicatiedatum" title="Selecteer hier de publicatiedatum van de mededeling">publicatiedatum</label>
        <input type="text" id="publicatiedatum" name="publicatiedatum" class="textfield datum_kiezen" value="<?php echo $publicatiedatum; ?>" />
        </p>
    </div>
    <div id="datum_archiveren">
        <label class="tooltip" for="archiveerdatum" title="Selecteer hier de archiveerdatum van de mededeling">archiveerdatum</label>
        <input type="text" id="archiveerdatum" name="archiveerdatum" class="textfield datum_kiezen" value="<?php echo $archiveerdatum; ?>" /></p>
    </div>
</div>

<div id="nieuws_foto_upload">
<p>Afbeelding bij nieuwsbericht:</p>
<?php
# Is er een foto?
# Zo ja... ook tonen!
if ($mededeling['afbeelding'] != NULL){
    echo '<div class="nieuws_afbeelding"><img src="'.$etc_root.'lib/slir/'.slirImage(400,100).$etc_root.'files/nieuws_afbeelding/'.$mededeling['afbeelding'].'" alt="'.$mededeling['titel'].'" title="'.$mededeling['titel'].'" class="achtergrond_afbeelding"/></div>';
    }
?>    

    <input type="text" id="afbeelding" name="afbeelding" class="file_input_textbox textfield <?php echo $tooltip; ?>" <?php echo $form_title; ?>="Selecteer een afbeelding bij het nieuwsbericht." readonly="readonly" />
    <div class="file_input_div">
        <input type="hidden" id="afbeelding_aanwezig" name="afbeelding_aanwezig" value="<?php echo $mededeling['afbeelding']; ?>" />
        <input type="button" value="Kies bestand" class="file_input_button button"/>
        <input type="file" id="file" name="file" class="file_input_hidden" onchange="javascript: document.getElementById('afbeelding').value = this.value" />        
    </div>
</div>

<div id="bedoeld_voor_row">
<?php
    //pak alle organisaties
    $what = 'id, voornaam, achternaam'; $from = 'portal_gebruiker'; $where = 'actief = 1';
    $aantal_gebruikers = countRows($what,$from, $where);
    if($aantal_gebruikers > 0){?>
    
    <p><span id="bedoeld_voor_titel">Voor welke gebruikers is dit nieuwsitem bedoeld?</span><input type="button" class="check button" value="Selecteer alle" onmouseover="this.className = 'check button btn_hover'" onmouseout="this.className = 'check button'" /></p>
    
<?php
        $result = sqlSelect($what, $from, $where);
    //loop door de organisaties heen
    while($gebruiker = mysql_fetch_array($result)){
        echo '
        <div class="bedoeld_voor">';
        
        //zijn we in edit-modus ?
        if($_GET['nieuws_id'] != null){
            //JA
            //en zitten er gebruikers in de array ? ofwel zijn er organisaties gekoppeld aan deze mededeling ?
            if(count($gebruiker) > 0){
                //JA
                //komt het organisatie id voor in de array met gekoppelde id's ?
                if(in_array($gebruiker['id'], $gebruiker_ids)){
                    //JA
                    //dan is deze al gekoppeld en geef dit aan door deze te checken
                    echo '<input type="checkbox" value="'.$gebruiker['id'].'" name="bedoeld_voor['.$gebruiker['id'].']" checked="checked" />';
                }
                //NEE
                //dan doen we een 'normale' checkbox
                else{ echo '<input type="checkbox" value="'.$gebruiker['id'].'" name="bedoeld_voor['.$gebruiker['id'].']" />'; }
            }
        }
        //NEE
        //dan doen we een 'normale' checkbox
        else{ 
            if(count($gebruiker) > 0){
                echo '<input type="checkbox" value="'.$gebruiker['id'].'" name="bedoeld_voor['.$gebruiker['id'].']" />'; 
            }
        }
        
        echo '<span class="bedoeld_voor_naam">'.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].'</span>
        </div>';
    }
    }else{?>
    <p><span id="bedoeld_voor_titel">Voor welke gebruikers is dit nieuwsitem bedoeld?</span></p>
        <div class="bedoeld_voor">
            <h3>Er zijn (nog) geen (actieve) gebruikers....</h3>
        </div>
 <?php   }
        
?>


</div>
<?php 
if($_GET['nieuws_id'] == null){
    echo '<input type="hidden" name="action" value="aanmaken" />';
}else{
    echo '  <input type="hidden" name="action" value="wijzigen" />
            <input type="hidden" name="nieuws_id" value="'.$nieuws_id.'">';
}
?>
<div id="opslaan_row">
    <input type="submit" value="Opslaan" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'" />
</div>
</form>
</div>
<?php
    //tags en bestanden worden alleen toegevoegd aan een bestaand item
    if($nieuws_id != null){
?>
<div id="nieuws_rechts">
    <div id="tags">
        <div id="tags_header" class="upload_header">
            Tags<a href="javascript:toggle_tags();">Voeg tags toe</a>
        </div>
        <div id="new_tag" style="display:none;">
            <input type="text" name="tag" id="tag_input" class="textfield" />
        </div>
        <div id="tags_lijst">
        <?php
            while($row = mysql_fetch_array($result_tags)){
            $tag_list[] = 
            '<div class="tag">
                <a class="tag_zoeken" href="?function=-zoek&query='.$row['naam'].'" target="_blank" title="zoek naar tag">'.$row['naam'].'</a>
                <span class="delete_tag" onmouseover="this.className=\'delete_tag delete_tag_hover\'" onmouseout="this.className=\'delete_tag\'" onclick="deletetag('.$row['id'].')"> X </span>
            </div>';
            }

            if(count($tag_list) > 0){
                foreach($tag_list as $tag){
                    echo $tag;
                }
            }else{
                echo 'er zijn nog geen tags voor deze activiteit';
            }
        ?>
        </div>
    </div>
    <div id="bestanden">
        <div id="bestanden_upload_header" class="upload_header">
            Bestanden<a href="javascript:toggle_upload();">Voeg bestanden toe</a>
        </div>
        <div id="add_file" class="uploaden" style="display:none;">

                    <div><p>Je kan meerdere bestanden tegelijkertijd uploaden door er meerdere tegelijkertijd te selecteren</p></div>

                    <div class="demo-box">

                        <div id="status-message">Selecteer &eacute;&eacute;n of meerdere bestanden om te uploaden</div>

                        <div id="file-queue"></div>
                        
                        <div id="upload_file"></div>
                    
                    </div>
          </div>
    
        <div id="bestanden_lijst">
        <?php
        if($aantal_bestanden > 0){
        while($row = mysql_fetch_array($result_bestanden)){
        echo '
        <div class="bestand" onmouseover="this.className='."'bestand_hover'".'" onmouseout="this.className='."'bestand'".'">
            <a class="bestand_link" href="'.$etc_root.'files/bestand/'.$nieuws_id.'/'.$row['path'].'" target="_blank" title="bekijk bestand">'.$row['path'].'</a>
            <span class="verwijder_bestand" onclick="deleteFile('.$row['id'].')">verwijderen</span>
        </div>';
        }
        }else{
            echo 'Er zijn nog geen bestanden voor dit nieuwsitem';
        }
        ?>
                </div>
    </div>
    <div id="fotos">
        <div id="fotos_upload_header" class="upload_header">
            Fotoalbum<a href="javascript:toggle_album();">Voeg foto's toe</a>
        </div>
        <div id="add_photo" class="uploaden" style="display:none;">

                    <div><p>Je kan meerdere afbeeldingen tegelijkertijd uploaden door er meerdere tegelijkertijd te selecteren</p></div>

                    <div class="demo-box">

                        <div id="status-message">Selecteer &eacute;&eacute;n of meerdere afbeeldingen om te uploaden</div>
                        <div id="photoerror"></div>

                        <div id="image-queue"></div>
                        
                        <div id="upload_image"></div>
                    
                    </div>
          </div>
    
        <div id="foto_album_lijst">
        <?php 
         $what = 'naam'; $from = 'portal_album'; $where = "id = $album_id";
                        $album = mysql_fetch_assoc(sqlSelect($what, $from, $where));
         if($album['naam'] != null){?>
        <form name="album_titel_wijzigen">
            <div id="album_naam_wijzigen">
                <p>Typ hier de titel voor het album. De titel wordt automatisch opgeslagen.</p>
                <input type="text" name="album_titel" value="<?php echo $album['naam']; ?>" onkeyup="albumNaam(<?php echo $album_id ?>, this.value);" class="textfield" />
            </div>
        </form>
         
         <?php } ?>
        <ul id="album_afbeeldingen" class="afbeeldingen">
            <?php
            //foto's ophalen
            $what_fotos = 'id, path, omschrijving'; $from_fotos = 'portal_image'; $where_fotos = 'album = '.$album_id.' ORDER BY volgorde ASC';
                $aantal_fotos = countRows($what_fotos, $from_fotos, $where_fotos); $result_fotos = sqlSelect($what_fotos, $from_fotos, $where_fotos);
            
            if($aantal_fotos > 0){
                while($foto = mysql_fetch_array($result_fotos)){
                    if($i == 1){$float="right"; $i = 0;}else{$float="left"; $i = 1;}
                    ?>
            <li id="afbeelding_<?php echo $foto['id']; ?>" style="float:<?php echo $float; ?>">
            
                <div class="foto_aanpassen" onmouseover="this.className='foto_aanpassen_hover';" onmouseout="this.className='foto_aanpassen';">
                        <span class="foto_options">
                            <span class="verwijder_foto" onclick="deleteImage(<?php echo $foto['id']?>)">X</span>
                        </span>
                        <div class="foto_holder">
                            <a href="<?php echo $etc_root; ?>files/album/<?php echo $album_id ?>/<?php echo $foto['path'] ?>" title="Bekijk afbeelding" class="iframe">
                                <img src="<?php echo $etc_root; ?>lib/slir/<?php echo slirImage(180,100) ?><?php echo $etc_root; ?>files/album/<?php echo $album_id ?>/<?php echo $foto['path'] ?>" alt="" title="<?php echo $foto['omschrijving'] ?>" class="achtergrond_afbeelding">
                            </a>
                        </div>
                        <form action="#" method="post">
                        <textarea cols="14" rows="3" name="caption" class="caption textarea" onkeyup="afbeeldingOmschrijving(<?php echo $foto['id'] ?>, this.value);"><?php echo $foto['omschrijving'] ?></textarea>
                        </form>
                    
                </div>
            </li>
           <?php }
        }else{echo 'Er zitten nog geen afbeeldingen in dit album';}
        ?>
        </ul>   
        </div>
</div>
<?php   } ?>
</div>
<?php }else{
    echo '<a href="/home/" style="float:left; margin:30px 0 0 375px"><h2>Klik hier om terug te gaan naar het mededelingenoverzicht</h2></a>';
} ?>
