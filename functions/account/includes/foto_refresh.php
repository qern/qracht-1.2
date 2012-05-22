<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $album_id = $_GET['album'];
    if($_GET['action'] == 'deleteImage'){
        $id = $_GET['image_id'];
        $what = 'path'; $from = 'portal_image'; $where = "id = $id";
            $delete_image = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        //eerst ontkoppelen
        $table = 'portal_image'; $where = "id = $id";
            $unlin_image = sqlDelete($table, $where);
        //daarna unlinken
        unlink($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$album_id.'/'.$delete_image['path']);
    }
?>
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