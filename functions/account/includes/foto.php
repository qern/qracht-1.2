<?php
	if($_POST['gebruiker_id']){$gebruiker_id = $_POST['gebruiker_id'];}
    //haal op wat nodig is:
    $what = 'album';
    $from = 'portal_gebruiker_album'; $where = 'gebruiker = '.$gebruiker_id;
        $foto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        $album_id = $foto['album'];
    if($album_id == null){
        $table="portal_album"; $what="naam"; $with_what = "'$naam'";
            $nieuw_album = sqlInsert($table, $what, $with_what);
        
        $what= 'MAX(id) AS id'; $from ='portal_album'; $where = '1';
            $album = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            $album_id = $album['id'];
        
        $table = 'portal_gebruiker_album'; $what = 'gebruiker, album'; $with_what = $gebruiker_id.', '.$album_id;
            $koppel_nieuw_album_gebruiker = sqlInsert($table, $what, $with_what);
        mkdir($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$album_id.'/');
    }
?>

<script>        
        // ---------------------------------------------
// Drag - Drop voor afbeeldingen
// ---------------------------------------------

$(function(){ 
    $("#album_afbeeldingen").sortable({
        connectWith:'.afbeeldingen',
        update:function(){
            $.ajax({
                type:"POST",url:"<?php echo $etc_root ?>functions/nieuws/includes/sortering_afbeelding.php",            
                data:{
                    afbeelding:$("#album_afbeeldingen").sortable('serialize')
                },
                success:function(html){}
            });
        }
    });
    jQuery('#upload_image').html('woehahaha');
    <?php if(!isset($_POST['gebruiker_id'])){?>
	jQuery(".ppt").remove();
    jQuery(".pp_overlay").remove();
    jQuery(".pp_pic_holder").remove();
    jQuery("a.iframe").fancybox({
    'overlayShow': true,
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
    <?php } ?>
    
var uploader = new qq.FileUploader({
    element: document.getElementById('upload_image'),
    action: '<?php echo $etc_root ?>functions/account/includes/php.php',
    allowedExtensions: ['jpeg', 'jpg', 'png', 'gif'],
    debug: true,
    params: { album: '<?php echo $album_id; ?>'},
    listElement: document.getElementById('custom-queue'),
    extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]],
    onComplete: function(id, fileName, responseJSON){
        $.ajax({
        type : 'GET',
        url : '<?php echo $etc_root ?>functions/account/includes/foto_refresh.php',
        dataType : 'html',
        data: {
            action : 'refresh',
            album: '<?php echo $album_id; ?>'
        },
        success : function(data){
            $('#album_afbeeldingen').html(data);
            $("#custom-queue").html('');
        }
    });
    
    return false;
    },
});
});
function afbeeldingOmschrijving(fotoId, str) {
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/nieuws/includes/album_check.php',
            dataType : 'html',
            data: {foto_id : fotoId, caption: str},
            //success : function(data){ jQuery('#album_afbeeldingen').html(data); jQuery("#image-queue").html(''); }
        }); return false;
}; 
/*                   
function afbeeldingOmschrijving(fotoId, str){
            var ajaxRequest;  // The variable that makes Ajax possible!
            
            try{// Opera 8.0+, Firefox, Safari
                ajaxRequest = new XMLHttpRequest();
            } catch (e){
                try{// Internet Explorer Browsers
                    ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    try{
                        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch (e){// Something went wrong
                        alert("Your browser broke!");
                        return false;
                        }
                    }
                }
            
            ajaxRequest.open("GET", "<?php echo $etc_root ?>functions/nieuws/includes/album_check.php?foto_id="+fotoId +"&caption="+str, true);
            ajaxRequest.send(null);
}*/
function deleteImage(imageId) {
        jQuery.ajax({
            type : 'GET',
            url : '<?php echo $etc_root ?>functions/account/includes/foto_refresh.php',
            dataType : 'html',
            data: {action : 'deleteImage', album: '<?php echo $album_id; ?>', image_id : imageId},
            success : function(data){ jQuery('#album_afbeeldingen').html(data); jQuery("#image-queue").html(''); }
        }); return false;
};
    </script>
<div id="account_center">
    <div id="foto_center_placeholder">&nbsp;</div>
    <div id="add_photo" class="uploaden">
                    <div id="upload_foto_header" class="account_header">
                        <p class="info_title">Upload nieuwe foto's <span class="info_title_desc">(... of sleep ze vanuit je verkenner hierheen)</span></p>
                    </div>
                    <div class="fotos_uploaden">

                        <div id="status-message"></div>

                        <ul id="custom-queue"></ul>
                        
                        <div id="upload_image"></div>
                        <div class="qq-upload-extra-drop-area">Drop files here too</div>
                   
                    </div>
          </div>
</div>
<div id="account_right">
    <div id="foto_album">
        <ul id="album_afbeeldingen" class="afbeeldingen">
            <?php
            //foto's ophalen
            $what_fotos = 'id, path, omschrijving'; $from_fotos = 'portal_image'; $where_fotos = 'album = '.$album_id.' ORDER BY volgorde ASC';
                $aantal_fotos = countRows($what_fotos, $from_fotos, $where_fotos); $result_fotos = sqlSelect($what_fotos, $from_fotos, $where_fotos);
            
            if($aantal_fotos > 0){
                while($foto = mysql_fetch_array($result_fotos)){
                    if($i == 1){$float="right"; $i = 0;}else{$float="left"; $i = 1;}
                    ?>
            <li id="afbeelding_<?php echo $foto['id']; ?>">
            
                <div class="foto_aanpassen" onmouseover="this.className='foto_aanpassen_hover';" onmouseout="this.className='foto_aanpassen';">
                        <span class="foto_options">
                            <span class="verwijder_foto" onclick="deleteImage(<?php echo $foto['id']?>)">X</span>
                        </span>
                        <div class="foto_holder">
                            <a class="iframe" rel="images" href="<?php echo $etc_root ?>functions/afbeeldingen/includes/afbeelding_tonen.php?image_id=<?php echo $foto['id']; ?>" title="">
                                <img src="<?php echo $etc_root.'lib/slir/'.slirImage(150,100).$etc_root.'files/album/'.$album_id.'/'.$foto['path']; ?>" alt="" 
                                     title="<?php echo $foto['omschrijving'] ?>" class="achtergrond_afbeelding" />
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
