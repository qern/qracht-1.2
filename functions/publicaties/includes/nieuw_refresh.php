<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
  if($_GET['action'] == 'refresh_file')  {
    $nieuws_id = $_GET['nieuws_id'];
    
    //moet er ook een bestand worden verwijderd ?
    if($_GET['subaction'] == 'delete'){
        //haal het path op, om te verwijderen
        $what_bestanden = 'path'; $from_bestanden = 'portal_nieuws_bestand'; $where_bestanden = 'id ='.$_GET['besand_id'];
            $te_verwijderen = mysql_fetch_assoc(sqlSelect($what_bestanden, $from_bestanden, $where_bestanden));
        
        //verwijder het bestand fysiek
        unlink($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/'.$nieuws_id.'/'.$te_verwijderen['path']);
        
        //verwijder daarna het record
        $table = 'portal_nieuws_bestand'; $where = 'id = '.$_GET['bestand_id'];
            $verwijder_bestand = sqlDelete($table, $where);
        
    }
    //bestand(en) ophalen
    $what_bestanden = 'id, path';
    $from_bestanden = 'portal_nieuws_bestand';
    $where_bestanden = "nieuws = $nieuws_id";
        $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden); $result_bestanden = sqlSelect($what_bestanden, $from_bestanden, $where_bestanden);
      
      while($row = mysql_fetch_array($result_bestanden)){
        echo '
        <div class="bestand" onmouseover="this.className='."'bestand_hover'".'" onmouseout="this.className='."'bestand'".'">
            <a class="bestand_link" href="'.$etc_root.'files/bestand/'.$nieuws_id.'/'.$row['path'].'" target="_blank" title="bekijk bestand">'.$row['path'].'</a>
            <span class="verwijder_bestand" onclick="deleteFile('.$row['id'].')">verwijderen</span>
        </div>';
        }
  }
  elseif($_GET['action'] == 'refresh_foto'){
      $album_id = $_GET['nieuws_id'];
      
      //moet er ook een foto worden verwijderd ?
      if($_GET['subaction'] == 'delete'){
         //haal het path op, om te verwijderen
        $what_foto = 'path'; $from_foto = 'portal_image'; $where_foto = 'id ='.$_GET['image_id'];
            $te_verwijderen = mysql_fetch_assoc(sqlSelect($what_foto, $from_foto, $where_foto));
        
        //verwijder het bestand fysiek
        unlink($_SERVER['DOCUMENT_ROOT'].$etc_root.'album/'.$album_id.'/'.$te_verwijderen['path']);
        
        //verwijder daarna het record
        $table = 'portal_image'; $where = 'id = '.$_GET['image_id'];
            $verwijder_foto = sqlDelete($table, $where);
      }
      
      //foto's ophalen
      $what_fotos = 'id, path, omschrijving'; $from_fotos = 'portal_image'; $where_fotos = 'album = '.$album_id.' ORDER BY volgorde ASC';
        $aantal_fotos = countRows($what_fotos, $from_fotos, $where_fotos); $result_fotos = sqlSelect($what_fotos, $from_fotos, $where_fotos);
      
      while($foto = mysql_fetch_array($result_fotos)){
        if($i == 1){$float="right"; $i = 0;}else{$float="left"; $i = 1;}?>
        
            <li id="afbeelding_<?php echo $foto['id']; ?>" style="float:<?php echo $float; ?>">
            
                <div class="foto_aanpassen" onmouseover="this.className='foto_aanpassen_hover';" onmouseout="this.className='foto_aanpassen';">
                        <span class="foto_options">
                            <span class="verwijder_foto" onclick="deleteImage('.$row['id'].')">X</a>
                        </span>
                        <div class="foto_holder">
                            <a href="<?php echo $etc_root; ?>lib/slir/w132-h100-c132.100<?php echo $etc_root; ?>files/album/<?php echo $album_id ?>/<?php echo $foto['path'] ?>" title="" class="iframe">
                                <img src="<?php echo $etc_root; ?>lib/slir/<?php echo slirImage(180,100) ?><?php echo $etc_root; ?>files/album/<?php echo $album_id ?>/<?php echo $foto['path'] ?>" alt="" title="<?php echo $foto['omschrijving'] ?>" class="achtergrond_afbeelding">
                            </a>
                        </div>
                        <form action="#" method="post">
                        <textarea cols="14" rows="3" name="caption" class="caption textarea" onkeyup="afbeeldingOmschrijving(<?php echo $foto['id'] ?>, this.value);"><?php echo $foto['omschrijving'] ?></textarea>
                        </form>
                    
                </div>
            </li>
<?php }
  }
?>