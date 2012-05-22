<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    
    //laadt een specifiek album
    if($_GET['action'] == 'loadAlbum'){?>
        <div id="fotoalbum_nav">
            <div id="Terug_naar_overzicht" onclick="toonAlleen('<?php echo $_GET['filter']; ?>', '<?php echo $_GET['view']; ?>')">&laquo;&laquo;Terug naar het overzicht</div>
    <?php 
        if($_GET['album_id'] > 0){
            $album_id = $_GET['album_id'];
            
            $what = 'a.nieuws AS id, b.titel AS titel, UNIX_TIMESTAMP(b.geschreven_op) AS timestamp, \'nieuws\' AS soort'; 
            $from = 'portal_nieuws_album a LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)';
            $where = 'a.album = '.$album_id.'
            UNION
            SELECT
                gebruiker AS id,
                \'\' AS titel,
                \'1\' AS timestamp,
                \'profiel\' AS soort
            FROM 
                portal_gebruiker_album
            WHERE
                album = '.$album_id;
                //echo "SELECT $what FROM $from WHERE $where";
                $soort = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
            if($soort['soort'] == 'profiel'){
                $what = 'voornaam, achternaam, gebruikersnaam'; $from = 'portal_gebruiker'; $where= 'id = '.$soort['id'];
                    $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                echo '
                    <div id="profiel_declaratie">
                        <span id="profiel_sub_declaratie">Foto\'s van:</span> '.$gebruiker['voornaam'].' '.$gebruiker['achternaam'].'
                    </div>
                    <div id="bekijk_bron"><a href="'.$etc_root.'profiel/'.$gebruiker['gebruikersnaam'].'" target="_blank">Bekijk profiel</a></div>';
            }
            elseif($soort['soort'] == 'nieuws'){
                echo '
                    <div id="nieuws_declaratie">
                        <span id="nieuws_sub_declaratie">Foto\'s van:</span> '.$soort['titel'].'
                    </div>
                    <div id="bekijk_bron"><a href="'.$etc_root.'home/nieuws='.$soort['id'].'" target="_blank">Bekijk artikel</a></div>';
            }
            echo '</div>';
            
            $what = 'id,path, omschrijving'; $from = 'portal_image'; $where = "album = $album_id AND actief = 1 ORDER BY volgorde ASC";
                $foto_res = sqlSelect($what, $from, $where);
            while($foto = mysql_fetch_array($foto_res)){
                
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$album_id.'/'.$foto['path']);
            
            if($width > $height){
            
            $image_span =  '
            <span class="foto_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(400,200).$etc_root.'files/album/'.$album_id.'/'.$foto['path'].'\') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }elseif($width == $height){
                
            $image_span =  '
            <span class="foto_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,200).$etc_root.'files/album/'.$album_id.'/'.$foto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }else{
                
            $image_span =  '
            <span class="foto_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,200,0).$etc_root.'files/album/'.$album_id.'/'.$foto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';   
            }                  
            
            $array[] = '    
            <div class="foto">
                <div class="foto_thumb">
                    <a class="iframe" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'" title="'.$foto['omschrijving'].'">
                        '.$image_span.'
                    </a>
                </div>
                <div class="foto_naam">'.$foto['omschrijving'].'</div>
            </div>';
            }
        }else{
            echo '</div>';//sluit de fotoalbum_nav af.
            $what = 'a.gebruikersnaam, a.voornaam, a.achternaam, b.id, b.path, b.album'; $from = 'portal_gebruiker a LEFT JOIN portal_image AS b ON (b.id = a.profielfoto)';
            $where = 'a.actief = 1 AND b.actief = 1 ORDER BY a.achternaam ASC, a.voornaam ASC';
                $profielfotos = sqlSelect($what, $from, $where);
                
            while($profielfoto = mysql_fetch_array($profielfotos)){
            
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path']);
            
            if($width > $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(400,200).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }elseif($width == $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,200).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }else{
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,200,0).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';   
            
            }                  
            
            $array[] ='    
            <div class="foto">
                <div class="foto_thumb">
                    <a class="iframe" rel="images" href="/portal/functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$profielfoto['id'].'" title="'.$profielfoto['voornaam'].' '.$profielfoto['achternaam'].'">
                        '.$image_span.'
                    </a>
                </div>
                <div class="foto_naam">
                    <a href="/portal/profiel/'.$profielfoto['gebruikersnaam'].'" target="_blank">
                        '.$profielfoto['voornaam'].' '.$profielfoto['achternaam'].'
                    </a>
                </div>
            </div>';
        }
    }    
        //gebruik de data om de het overzicht te herladen.
        $refreshImages = 1;
    }
   /*elseif($_GET['action'] == 'loadAlbumoverzicht'){
        $what = 'DISTINCT a.id, a.naam, UNIX_TIMESTAMP(a.gemaakt_op)'; $from = 'portal_album a LEFT JOIN portal_image AS b ON (b.album = a.id)'; $where='a.actief = 1 AND b.id IS NOT NULL ORDER BY a.naam ASC';
        $album_res = sqlSelect($what, $from, $where);
        
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
            $albums[] ='    
            <div class="album profielfotos" onmouseover="this.className=\'album profielfotos album_hover\'" onmouseout="this.className=\'album profielfotos\'" onclick="loadAlbum(0, \'alles\')">
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
            
            $albums[] ='    
            <div class="album" onmouseover="this.className=\'album album_hover\'" onmouseout="this.className=\'album\'" onclick="loadAlbum('.$album['id'].', \'alles\')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">'.$album['naam'].'</div>
            </div>';
        }
        //bepaal hoeveer arrays er in totaal zijn
        $aantal_albums = count($albums);
        //deel dit door het aantal kolommen dat we willen : 4
        //de som verdelen we in een geheel getal en de decimaal.
        //$rounded = strval(round(($aantal_albums/3), 1)).'<br />';
        list($heel, $decimaal) = explode('.', round(($aantal_albums/3), 1));
        echo '<div class="archief_kolom">';
        //kijk of er meer dan 0 reaties zijn
        if($aantal_albums > 0){
            //begin nu met tellen.
            $i_1 = 0; 
            if($decimaal != null){
                if($decimaal == '3'){
                    $aantal_in_kolom_1 = $heel;
                }elseif($decimaal == '7'){
                    $aantal_in_kolom_1 = $heel+1;
                }
            }else{
                $aantal_in_kolom_1 = $heel;
            }
            foreach($albums as $archief_rij){
                if($i_1 <= $aantal_in_kolom_1-1 && $i_1 <= $aantal_albums-1){
                    if($i_class_1 == 1){$class_1 = 'act_b1'; $i_class_1 = 0;}else{$class_1 = 'act_a1'; $i_class_1 = 1;}
                    
                    echo '<div class="'.$class_1.'" onmouseover="this.className='."'act_hover'".'" onmouseout="this.className='."'$class_1'".'">';
                    echo $albums["$i_1"];
                    echo '</div>';
                    
                    $i_1++;
                }

            }
    }else{
            echo '<div class="act_a">Dit filter leverde geen activiteiten op. Probeer het alstublieft opnieuw.</div>';
    }
    echo '</div>';
?>

<?php 
    //kijk of  meer dan 0 reaties zijn
    if($aantal_albums > 0){
        echo '<div class="archief_kolom">'; 
            //tellen voor de tweede kolom
            $i_2 = $i_1;
            if($decimaal != null){
                if($decimaal == '3'){
                    $aantal_in_kolom_2 = $heel-1;
                }elseif($decimaal == '7'){
                    $aantal_in_kolom_2 = $heel+1;
                }
            }else{
                $aantal_in_kolom_2 = $heel;
            }
            foreach($albums as $archief_rij){
                if($i_2 <= $aantal_in_kolom_2+$i_1-1 && $i_2 <= $aantal_albums-1){
                    if($i_class_2 == 1){$class_2 = 'act_b2'; $i_class_2 = 0;}else{$class_2 = 'act_a2'; $i_class_2 = 1;}
                    echo '<div class="'.$class_2.'" onmouseover="this.className='."'act_hover'".'" onmouseout="this.className='."'$class_2'".'">'.
                    $albums["$i_2"]
                    .'</div>';
                    $i_2++;
                }

            }
        echo  '</div>';
    }
?>

<?php 
    //kijk of er meer dan 0 reaties zijn
    if($aantal_albums > 0){
        echo '<div class="archief_kolom_last">';
        //tellen voor de derde kolom
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
        foreach($albums as $archief_rij){
            if($i_3 <= $aantal_in_kolom_3+$i_2-1 && $i_3 <= $aantal_albums-1){
                if($i_class_3 == 1){$class_3 = 'act_b3'; $i_class_3 = 0;}else{$class_3 = 'act_a3'; $i_class_3 = 1;}
                echo '<div class="'.$class_3.'" onmouseover="this.className='."'act_hover'".';" onmouseout="this.className='."'$class_3'".';">'.
                $albums["$i_3"]
                .'</div>';
                $i_3++;
            }

        }
        echo  '</div>';
    }
    }*/
    elseif($_POST['action'] == 'zoekAlbum'){
        $zoekwoord  = $_POST['zoekwoord'];
        $what = 'DISTINCT a.id, a.naam, UNIX_TIMESTAMP(a.gemaakt_op)'; $from = 'portal_album a LEFT JOIN portal_image AS b ON (b.album = a.id)'; 
        $where= "a.actief = 1 AND b.id IS NOT NULL AND a.naam LIKE '%$zoekwoord%' ORDER BY a.naam ASC";
        $album_res = sqlSelect($what, $from, $where);
          //echo "SELECT $what FROM $from WHERE $where";
          
          if($zoekwoord == null){
              $what = 'b.path, b.album'; $from = 'portal_gebruiker a LEFT JOIN portal_image AS b ON (b.id = a.profielfoto)';
        $where = 'a.actief = 1 AND b.actief = 1 ORDER BY b.id ASC';
        $profielfoto_album = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path']);
            if($width > $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(400,200).$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }elseif($width == $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,200).$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }else{
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,200,0).$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';   
            
            }
            $array[] ='    
            <div class="album profielfotos" onmouseover="this.className=\'album profielfotos album_hover\'" onmouseout="this.className=\'album profielfotos\'" onclick="loadAlbum(0, \'alles\', \'album\')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">Profielfoto\'s</div>
            </div>';
          }
        
        while($album = mysql_fetch_array($album_res)){
            $what = 'path, omschrijving'; $from = 'portal_image'; $where = 'album = '.$album['id'].' AND volgorde = 1 AND actief = 1 ';
                $omslagfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path']);
            
            if($width > $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(400,200).$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }elseif($width == $height){
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,200).$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';
            
            }else{
                
            $image_span =  '
            <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,200,0).$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path'].') no-repeat center center transparent;">
                &nbsp;
            </span>';   
            
            }                  
            
            $array[] ='    
            <div class="album" onmouseover="this.className=\'album album_hover\'" onmouseout="this.className=\'album\'" onclick="loadAlbum('.$album['id'].')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">'.$album['naam'].'</div>
            </div>';
        }
        //gebruik de data om de het overzicht te herladen.
        $refreshImages = 1;
    }
    /*elseif($_GET['action'] == 'toonAlleen'){
        $filter  = $_GET['filter'];
        if($filter == 'nieuws'){
            $what = 'DISTINCT c.id, c.naam, UNIX_TIMESTAMP(c.gemaakt_op)'; 
            $from = 'portal_nieuws_album a
                     LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)
                     LEFT JOIN portal_album AS c ON (c.id = a.album) 
                     LEFT JOIN portal_image AS d ON (d.album = c.id)
                     '; 
            $where= "b.actief = 1 AND c.actief = 1 AND d.id IS NOT NULL ORDER BY c.naam ASC";
        }elseif($filter == 'profiel'){
            $what = 'DISTINCT c.id, c.naam, UNIX_TIMESTAMP(c.gemaakt_op)'; 
            $from = 'portal_gebruiker_album a
                     LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)
                     LEFT JOIN portal_album AS c ON (c.id = a.album) 
                     LEFT JOIN portal_image AS d ON (d.album = c.id)
                     '; 
            $where= "b.actief = 1 AND c.actief = 1 AND d.id IS NOT NULL ORDER BY c.naam ASC";
        }elseif($filter == 'alles'){
        
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
            <div class="album profielfotos" onmouseover="this.className=\'album profielfotos album_hover\'" onmouseout="this.className=\'album profielfotos\'" onclick="loadAlbum(0, \'alles\')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">Profielfoto\'s</div>
            </div>';
            $what = 'DISTINCT a.id, a.naam, UNIX_TIMESTAMP(a.gemaakt_op)'; $from = 'portal_album a LEFT JOIN portal_image AS b ON (b.album = a.id)'; 
            $where= "a.actief = 1 AND b.id IS NOT NULL ORDER BY a.naam ASC";
        }
        //echo "SELECT $what FROM $from WHERE $where";
        $album_res = sqlSelect($what, $from, $where);
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
            
            $array[] ='    
            <div class="album" onmouseover="this.className=\'album album_hover\'" onmouseout="this.className=\'album\'" onclick="loadAlbum('.$album['id'].', \''.$filter.'\')">
                <div class="album_omslag">'.$image_span.'</div>
                <div class="album_naam">'.$album['naam'].'</div>
            </div>';
        }    
        //gebruik de data om de het overzicht te herladen.
        $refreshImages = 1;
    }*/
    elseif($_GET['action'] == 'loadView'){
        $filter  = $_GET['filter']; $view = $_GET['view'];
        if($view == 'album'){
            if($filter == 'nieuws'){
                $what = 'DISTINCT c.id, c.naam, UNIX_TIMESTAMP(c.gemaakt_op)'; 
                $from = 'portal_nieuws_album a
                         LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)
                         LEFT JOIN portal_album AS c ON (c.id = a.album) 
                         LEFT JOIN portal_image AS d ON (d.album = c.id)'; 
                $where= "b.actief = 1 AND c.actief = 1 AND d.id IS NOT NULL ORDER BY c.naam ASC";
            }elseif($filter == 'profiel'){
                $what = 'DISTINCT c.id, c.naam, UNIX_TIMESTAMP(c.gemaakt_op)'; 
                $from = 'portal_gebruiker_album a
                         LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)
                         LEFT JOIN portal_album AS c ON (c.id = a.album) 
                         LEFT JOIN portal_image AS d ON (d.album = c.id)'; 
                $where= "b.actief = 1 AND c.actief = 1 AND d.id IS NOT NULL ORDER BY c.naam ASC";
            }elseif($filter == 'alles'){
                $what = 'b.path, b.album'; $from = 'portal_gebruiker a LEFT JOIN portal_image AS b ON (b.id = a.profielfoto)';
                $where = 'a.actief = 1 AND b.actief = 1 ORDER BY b.id ASC';
                    $profielfoto_album = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
                list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path']);
                if($width > $height){
                    $image_span =  '
                    <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(400,200).$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') 
                          no-repeat center center transparent;">
                          &nbsp;
                    </span>';
                }elseif($width == $height){
                    $image_span =  '
                    <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,200).$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') 
                          no-repeat center center transparent;">
                          &nbsp;
                    </span>';
                }else{
                    $image_span =  '
                    <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,200,0).$etc_root.'files/album/'.$profielfoto_album['album'].'/'.$profielfoto_album['path'].') 
                          no-repeat center center transparent;">
                          &nbsp;
                    </span>';   
                }
                
                $array[] ='    
                <div class="album profielfotos" onmouseover="this.className=\'album profielfotos album_hover\'" onmouseout="this.className=\'album profielfotos\'" onclick="loadAlbum(0, \'alles\', \'album\')">
                    <div class="album_omslag">'.$image_span.'</div>
                    <div class="album_naam">Profielfoto\'s</div>
                </div>';
                
                $what = 'DISTINCT a.id, a.naam, UNIX_TIMESTAMP(a.gemaakt_op)'; $from = 'portal_album a LEFT JOIN portal_image AS b ON (b.album = a.id)'; 
                $where= "a.actief = 1 AND b.id IS NOT NULL ORDER BY a.naam ASC";
            }
            //echo "SELECT $what FROM $from WHERE $where";
                $album_res = sqlSelect($what, $from, $where);
            
            while($album = mysql_fetch_array($album_res)){
                $what = 'path, omschrijving'; $from = 'portal_image'; $where = 'album = '.$album['id'].' AND volgorde = 1 AND actief = 1 ';
                    $omslagfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
                list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path']);
            
                if($width > $height){
                    $image_span =  '
                    <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(400,200).$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path'].')
                          no-repeat center center transparent;">
                        &nbsp;
                    </span>';
                }elseif($width == $height){
                    $image_span =  '
                    <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,200).$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path'].')
                          no-repeat center center transparent;">
                        &nbsp;
                    </span>';
                }else{
                    $image_span =  '
                    <span class="album_omslag_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,200,0).$etc_root.'files/album/'.$album['id'].'/'.$omslagfoto['path'].')
                          no-repeat center center transparent;">
                        &nbsp;
                    </span>';   
                }                  
            
                $array[] ='    
                <div class="album" onmouseover="this.className=\'album album_hover\'" onmouseout="this.className=\'album\'" onclick="loadAlbum('.$album['id'].', \''.$filter.'\', \''.$view.'\')">
                    <div class="album_omslag">'.$image_span.'</div>
                    <div class="album_naam">'.$album['naam'].'</div>
                </div>';
            }          
            //gebruik de data om de het overzicht te herladen.
            $refreshImages = 1;
        }
        elseif($view == 'foto'){
            if($filter == 'nieuws'){
                $what = 'b.id fotoId, b.album, b.path, b.omschrijving, c.id nieuwsId, c.titel'; 
                $from = 'portal_nieuws_album a
                         LEFT JOIN portal_image AS b ON (b.album = a.album)
                         LEFT JOIN portal_nieuws AS c ON (c.id = a.nieuws)'; 
                $where= "b.actief = 1 AND c.actief = 1 ORDER BY b.volgorde ASC";
                $aantal_fotos = countRows($what, $from, $where);
                $where .= ' LIMIT 0, 9';
                
            }elseif($filter == 'profiel'){
                $what = 'b.id, b.album, b.path, b.omschrijving, c.gebruikersnaam, c.voornaam, c.achternaam'; 
                $from = 'portal_gebruiker_album a
                         LEFT JOIN portal_image AS b ON (b.album = a.album)
                         LEFT JOIN portal_gebruiker AS c ON (c.id = a.gebruiker)'; 
                $where= "b.actief = 1 AND c.actief = 1 ORDER BY b.volgorde ASC";
                $aantal_fotos = countRows($what, $from, $where);
                $where .= ' LIMIT 0, 9';
            }elseif($filter == 'alles'){
                $what = 'id, album, path, omschrijving'; 
                $from = 'portal_image'; 
                $where= "actief = 1 ORDER BY volgorde";
                $aantal_fotos = countRows($what, $from, $where);
                $where .= ' LIMIT 0, 9';
            }
            
            
            if($filter != 'alles'){
                
                $selecte_fotos = sqlSelect($what, $from, $where);
                //echo "SELECT $what FROM $from WHERE $where";
                while($foto = mysql_fetch_array($selecte_fotos)){
                    
                    // is de tekst meer dan 150 tekens ?
                    if(strlen($foto['omschrijving']) > 30){
                        //hoeveel tekens te veel hebben we ?
                        $offset = strlen($foto['omschrijving']) - 30;
                        //we gaan de string verkleinen... met $offset aantal tekens van het einde af.
                        $tekst = substr($foto['omschrijving'], 0, -$offset);
                        //pak elk afzonderlijk woord
                        $woorden =  explode(' ', $tekst);
                        //tel hoeveel woorden er zitten in de array
                        $aantal_woorden = COUNT($woorden);
                        //pak het laatste woord en maak daar ... van
                        $woorden[$aantal_woorden-1] = '...';
                        $omschrijving = '';
                        //gooi alle woorden achter elkaar en zet de spatie weer achter elk woord.
                        foreach($woorden as $woord){
                            $omschrijving .= $woord.' ';
                        }
                    }else{
                        //de tekst mag gewoon de tekst blijven.
                        $omschrijving = $foto['omschrijving'];
                    }
                    
                list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$foto['album'].'/'.$foto['path']);
                            
                if($width > $height){
                $image_span =  '
                    <span class="foto_klein_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(200,100).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].')
                           no-repeat center center transparent;">
                           &nbsp;
                    </span>';
            
                }elseif($width == $height){
                    $image_span =  '
                    <span class="foto_klein_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(100,100).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].')
                           no-repeat center center transparent;">
                           &nbsp;
                    </span>';
                }else{
                    $image_span =  '
                    <span class="foto_klein_image" style="background: url('.$etc_root.'lib/slir/'.slirImage(0,100, 0).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].')
                           no-repeat center center transparent;">
                           &nbsp;
                    </span>';            
                }                  
                if($filter == 'profiel'){
                    $array[] ='    
                    <div class="foto_klein">
                        <div class="foto_klein_thumb">
                            <a class="iframe" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'" 
                               title="'.$foto['voornaam'].' '.$foto['achternaam'].'">
                                '.$image_span.'
                            </a>
                        </div>
                        <div class="foto_klein_naam">
                            <div class="foto_klein_omschrijving">
                                '.$omschrijving.'
                            </div>
                            <div class="foto_klein_bron">
                                <a href="/portal/profiel/'.$foto['gebruikersnaam'].'" target="_blank" title="'.$foto['voornaam'].' '.$foto['achternaam'].'">
                                    profiel
                                </a>
                            </div>
                        </div>
                    </div>';
                }elseif($filter == 'nieuws'){
                    $array[] ='    
                    <div class="foto_klein">
                        <div class="foto_klein_thumb">
                            <a class="iframe" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['fotoId'].'" 
                               title="'.$foto['titel'].'">
                                '.$image_span.'
                            </a>
                        </div>
                        <div class="foto_klein_naam">
                            <div class="foto_klein_omschrijving">
                                '.$omschrijving.'
                            </div>
                            <div class="foto_klein_bron">
                                <a href="/portal/home/'.$foto['nieuwsId'].'" target="_blank" title="'.$foto['titel'].'">
                                    artikel
                                </a>
                            </div>
                        </div>
                    </div>';
                }
            }
        $refreshSmallImages = 1;
        }
        
    }
}
if($refreshImages ==  1){
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
}
if($refreshSmallImages == 1){
    //bepaal hoeveer arrays er in totaal zijn
    $aantal_arrays = count($array);
    
    //deel dit door het aantal kolommen dat we willen : 3
    //de som verdelen we in een geheel getal en de decimaal.
    //$rounded = strval(round(($aantal_albums/3), 1)).'<br />';
    list($heel, $decimaal) = explode('.', round(($aantal_arrays/6), 1));
    //echo "totaal: $aantal_arrays heel: $heel , decimaal: $decimaal";
    //-------KOLOM 1
    
    //kijk of er meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kleine_kolom">
        <?php //begin nu met tellen.
        $i_1 = 0; 
        if($decimaal != null){
            if($decimaal == '2'){$aantal_in_kolom_1 = $heel+1;}
            if($decimaal == '3'){$aantal_in_kolom_1 = $heel+1;}
            if($decimaal == '5'){$aantal_in_kolom_1 = $heel+1;}
            if($decimaal == '7'){$aantal_in_kolom_1 = $heel+1;}
            if($decimaal == '8'){$aantal_in_kolom_1 = $heel+1;}
        }else{
            $aantal_in_kolom_1 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_1 <= $aantal_in_kolom_1-1 && $i_1 <= $aantal_arrays-1){?>
            <div class="afbeelding_klein_item">
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
    <div class="afbeeldingen_kleine_kolom">
        <?php //tellen voor de tweede kolom
        $i_2 = $i_1;
        if($decimaal != null){
            if($decimaal == '2'){$aantal_in_kolom_2 = $heel+1;}
            if($decimaal == '3'){$aantal_in_kolom_2 = $heel+1;}
            if($decimaal == '5'){$aantal_in_kolom_2 = $heel+1;}
            if($decimaal == '7'){$aantal_in_kolom_2 = $heel+1;}
            if($decimaal == '8'){$aantal_in_kolom_2 = $heel;}
        }else{
            $aantal_in_kolom_2 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_2 <= $aantal_in_kolom_2+$i_1-1 && $i_2 <= $aantal_arrays-1){?>
            <div class="afbeelding_klein_item">
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
    <div class="afbeeldingen_kleine_kolom">
        <?php //tellen voor de tweede kolom
        $i_3 = $i_2;
        if($decimaal != null){
            if($decimaal == '2'){$aantal_in_kolom_3 = $heel+1;}
            if($decimaal == '3'){$aantal_in_kolom_3 = $heel+1;}
            if($decimaal == '5'){$aantal_in_kolom_3 = $heel+1;}
            if($decimaal == '7'){$aantal_in_kolom_3 = $heel;}
            if($decimaal == '8'){$aantal_in_kolom_3 = $heel;}
        }else{
            $aantal_in_kolom_3 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_3 <= $aantal_in_kolom_3+$i_2-1 && $i_3 <= $aantal_arrays-1){?>
            <div class="afbeelding_klein_item">
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
    
    //-------KOLOM 4
    
    //kijk of er meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kleine_kolom">
        <?php //begin nu met tellen.
        $i_4 = $i_3; 
        if($decimaal != null){
            if($decimaal == '2'){$aantal_in_kolom_4 = $heel+1;}
            if($decimaal == '3'){$aantal_in_kolom_4 = $heel+1;}
            if($decimaal == '5'){$aantal_in_kolom_4 = $heel;}
            if($decimaal == '7'){$aantal_in_kolom_4 = $heel;}
            if($decimaal == '8'){$aantal_in_kolom_4 = $heel;}
        }else{
            $aantal_in_kolom_4 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_4 <= $aantal_in_kolom_4+$i_3-1 && $i_4 <= $aantal_arrays-1){?>
            <div class="afbeelding_klein_item">
                <?php echo $array["$i_4"] ?>
            </div>
                   <?php 
            $i_4++;
                }

            }?>
    </div>  
    <?php }else{?>
            <div class="act_a">Dit filter leverde geen activiteiten op. Probeer het alstublieft opnieuw.</div> 
    <?php } 
    
    //-------KOLOM 5
    
    //kijk of  meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kleine_kolom">
        <?php //tellen voor de tweede kolom
        $i_5 = $i_4;
        if($decimaal != null){
            if($decimaal == '2'){$aantal_in_kolom_5 = $heel+1;}
            if($decimaal == '3'){$aantal_in_kolom_5 = $heel;}
            if($decimaal == '5'){$aantal_in_kolom_5 = $heel;}
            if($decimaal == '7'){$aantal_in_kolom_5 = $heel;}
            if($decimaal == '8'){$aantal_in_kolom_5 = $heel;}
        }else{
            $aantal_in_kolom_5 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_5 <= $aantal_in_kolom_5+$i_4-1 && $i_5 <= $aantal_arrays-1){?>
            <div class="afbeelding_klein_item">
                <?php echo $array["$i_5"] ?>
            </div>
                   <?php 
            $i_5++;
            }
        }
         ?>
    </div>
        <?php
    }
    
    //-------KOLOM 6
    
    //kijk of  meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kleine_kolom_last">
        <?php //tellen voor de tweede kolom
        $i_6 = $i_5;
        if($decimaal != null){
            if($decimaal == '2'){$aantal_in_kolom_6 = $heel;}
            if($decimaal == '3'){$aantal_in_kolom_6 = $heel;}
            if($decimaal == '5'){$aantal_in_kolom_6 = $heel;}
            if($decimaal == '7'){$aantal_in_kolom_6 = $heel;}
            if($decimaal == '8'){$aantal_in_kolom_6 = $heel;}
        }else{
            $aantal_in_kolom_6 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_6 <= $aantal_in_kolom_6+$i_5-1 && $i_6 <= $aantal_arrays-1){?>
            <div class="afbeelding_klein_item">
                <?php echo $array["$i_6"] ?>
            </div>
                   <?php 
            $i_6++;
            }
        }
         ?>
    </div>
        <?php
    }
}
if($_GET['action'] == 'reloadImageTop'){

    //op basis van het filter (alles, nieuws, profiel), moet het active filter worden bepaald
    if($_GET['filter'] == 'alles'){$filter_alles = 'active_filter';}
    elseif($_GET['filter'] == 'nieuws'){$filter_nieuws = 'active_filter';}
    elseif($_GET['filter'] == 'profiel'){$filter_profiel = 'active_filter';}
    else{$filter_alles = 'active_filter';}

    //op basis van de view (album of foto), moeten de teksten en de active view worden bepaald
    //het zoeken is ook op basis van de view (album of foto)
    if($_GET['view'] == 'album'){$album_view = 'active_view'; $alles_text = 'Alle'; $profiel_text = 'Profielalbums'; $nieuws_text = 'Nieuwsalbums'; $search_album = 'block'; $search_foto = 'none';}    
    elseif($_GET['view'] == 'foto'){$foto_view = 'active_view'; $alles_text = 'Alle'; $profiel_text = 'Profielfoto\'s'; $nieuws_text = 'Nieuwsfoto\'s'; $search_album = 'none'; $search_foto = 'block';}
    else{$album_view = 'active_view'; $alles_text = 'Alle'; $profiel_text = 'Profielalbum\'s'; $nieuws_text = 'Nieuwsalbum\'s'; $search_album = 'block'; $search_foto = 'none';}
    
    
    ?>
        <div id="view">
            <div id="view_header">Bekijk</div> 
            <div class="album_view <?php echo $album_view; ?>" onmouseover="this.className='album_view view_hover'" onmouseout="this.className='album_view'"  onclick="loadView('<?php echo $_GET['filter'] ?>', 'album')">Albums</div>
            <div class="foto_view <?php echo $foto_view; ?>" onmouseover="this.className='foto_view view_hover'" onmouseout="this.className='foto_view'" onclick="loadView('<?php echo $_GET['filter'] ?>', 'foto')">Foto's</div>
        </div>
        <div id="filters">
            <div id="profiel_nieuws">
                <div id="profiel_nieuws_header">Toon</div>
                <div class="alles_filter <?php echo $filter_alles; ?>" onmouseover="this.className='alles_filter filter_hover <?php echo $filter_alles; ?>'" 
                     onmouseout="this.className='alles_filter <?php echo $filter_alles; ?>'" onclick="loadView('alles', '<?php echo $_GET['view'] ?>')">
                    <?php echo $alles_text; ?>
                </div>
                <div class="nieuws_filter <?php echo $filter_nieuws; ?>" onmouseover="this.className='nieuws_filter filter_hover <?php echo $filter_nieuws; ?>'" 
                     onmouseout="this.className='nieuws_filter <?php echo $filter_nieuws; ?>'" onclick="loadView('nieuws', '<?php echo $_GET['view'] ?>')">
                    <?php echo $nieuws_text; ?>
                </div> 
                <div class="profiel_filter <?php echo $filter_profiel; ?>" onmouseover="this.className='profiel_filter filter_hover <?php echo $filter_profiel; ?>'" 
                     onmouseout="this.className='profiel_filter <?php echo $filter_profiel; ?>'"  onclick="loadView('profiel', '<?php echo $_GET['view'] ?>')">
                    <?php echo $profiel_text; ?>
                </div>
            </div>
        </div>
        <div id="album_zoeken" style="display:<?php echo $search_album ?>;">
                <div id="zoeken_header"> Zoek naar een album:</div> 
                <input type="text" class="textfield" id="zoek_album" onkeyup="zoekAlbum(this.value)" />
                <img id="end_search" src="<?php echo $etc_root ?>functions/afbeeldingen/css/images/end_search.png" onclick="zoekAlbum('')" alt="end search" 
                     title="stop met zoeken" style="display:none;" />
        </div>
        <div id="foto_zoeken" style="display:<?php echo $search_foto ?>;">
                <div id="zoeken_header"> Zoek naar een foto:</div> 
                <input type="text" class="textfield" id="zoek_foto" onkeyup="zoekFoto(this.value)" />
                <img id="end_search" src="<?php echo $etc_root ?>functions/afbeeldingen/css/images/end_search.png" onclick="zoekFoto('')" alt="end search" 
                     title="stop met zoeken" style="display:none;" />
        </div>
<?php } ?>