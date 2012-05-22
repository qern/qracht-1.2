<?php
    if($_GET['nieuws_id'] == null){    
        session_start();
        include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
        $nieuws_id = $_GET['q'];
    }else{
        $nieuws_id = $_GET['nieuws_id'];
    }
     $what = 'a.id, a.titel, a.teaser, a.inhoud, a.afbeelding, UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op, 
              a.is_belangrijk, b.gebruikersnaam, b.voornaam, b.achternaam, c.album'; 
    $from='portal_nieuws a
           LEFT JOIN portal_gebruiker AS b ON(b.id = a.geschreven_door)
           LEFT JOIN portal_nieuws_album AS c ON(c.nieuws = a.id)';
    $where='a.id = '.$nieuws_id.' AND a.actief = 1 AND a.publicatiedatum <= CURDATE() AND (a.archiveerdatum >= CURDATE() OR a.archiveerdatum = 0000-00-00) AND b.actief = 1';
        $nieuws = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        $nieuws_id = $nieuws['id'];
    //haal alle bestanden op
    $what = 'path'; $from = 'portal_nieuws_bestand'; $where='nieuws = '.$nieuws_id;
        $count_bestanden = countRows($what, $from, $where); $bestanden_result = sqlSelect($what, $from, $where);
    
        if($count_bestanden > 0){$bestanden_href = "javascript:toggle_bestanden();";}else{$bestanden_href="#";}
    
    if($nieuws['album'] != null){
        $what = 'id, path, omschrijving'; $from = 'portal_image'; $where='album = '.$nieuws['album'].' AND actief = 1 ORDER BY volgorde ASC';
            $count_fotos = countRows($what, $from, $where); $fotos_result = sqlSelect($what, $from, $where);
        
        if($count_fotos > 0){$fotos_href = "javascript:toggle_fotos();";}else{$fotos_href="#";}
    }else{
        $fotos_href="#";
    }     
    
    //haal alle reacties op
    $what = 'b.id, b.inhoud, b.geschreven_door, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op'; 
    $from = 'portal_nieuws_reactie a LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)'; $where = 'a.nieuws = '.$nieuws_id.' AND b.actief = 1 ORDER BY b.geschreven_op DESC';
        $count_reacties = countRows($what, $from, $where); $reacties_result = sqlSelect($what, $from, $where);
?>
<div id="nieuwsitem">
    <div id="nieuwsitem_header">
       <h2><?php echo $nieuws['titel']; ?></h2></a>
            <span class="nieuwsitem_author">
                Geschreven door: <a href="<?php echo $etc_root; ?>profiel/<?php echo $nieuws['gebruikersnaam']?>"><?php echo $nieuws['voornaam'].' '.$nieuws['achternaam']; ?></a>
                Laatste Update: <?php echo verstrekenTijd($nieuws['geschreven_op']); ?> 
            </span>
    </div>
    <?php
    if($count_bestanden > 0 || $count_fotos > 0){?>
    <div id="nieuwsitem_acties">
        <div id="nieuwsitem_acties_activators">
    <?php 
        if($count_bestanden > 0){echo'
        <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="javascript:toggle_bestanden();" value="">Bestanden ('.$count_bestanden.')</button>';
        }
        if($count_fotos > 0){echo'
        <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="javascript:toggle_fotos();" value="">Foto\'s ('.$count_fotos.')</button>';
        }
    ?>                  
        </div>
        <div id="bestanden" style="display:none;">
        <h2>Bestanden</h2>
        <?php 
        if($count_bestanden > 0){echo'
        <p>Klik op een van de onderstaande bestanden om deze te downloaden</p>'; 
                                                       
                    while($bestand = mysql_fetch_array($bestanden_result)){
                        echo '<a href="'.$etc_root.'functions/bestanden/includes/download.php?id='.$nieuws_id.'&file='.$bestand['path'].'" title="download bestand">'.$bestand['path'].'</a>';
                    }
                          
        }
        ?>
        </div>                    
        <div id="fotos" style="display:none;">
            <h2>Foto's</h2>
            <?php
                if($count_fotos > 0){
                    $i = 0;                        
                    while($foto = mysql_fetch_array($fotos_result)){
                        if($i < 5){
                        echo '
                        <a class="iframe" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'" title="'.$foto['omschrijving'].'">
                            <img src="'.$etc_root.'lib/slir/'.slirImage(90,90).$etc_root.'files/album/'.$nieuws['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'" />
                        </a>';
                        $i++;
                        }else{
                        echo '
                        <a class="iframe" style="display:none;" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'" title="'.$foto['omschrijving'].'">
                            <img src="'.$etc_root.'lib/slir/'.slirImage(90,90).$etc_root.'files/album/'.$nieuws['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'" />
                            
                        </a>';    
                        }
                    }
                
                }else{
                    echo 'Er zijn geen foto\'s bij dit item';
                }?>
        </div>
            
    </div>
 
    <?php
    }
    ?>
    <div class="nieuwsitem_content">
        <p class="teaser">
        <?php
            if($nieuws['afbeelding'] != null){
                if(file_exists($_SERVER['DOCUMENT_ROOT'].''.$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'])){echo  '
                    <a href="'.$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'].'">
                        <img src="'.$etc_root.'lib/slir/'.slirImage(120,120).$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'].'" alt="'.$nieuws['titel'].'" />
                    </a>';  
                }
            }
            echo $nieuws['teaser'];
        ?>
        </p>
        <?php echo $nieuws['inhoud']; ?>
    </div>
    <div id="reageren">
        <div id="formulier">
                <form id="reactie_formulier_formulier" method="post" action="<?php echo $etc_root; ?>functions/home/includes/home_check.php">
                    <textarea class="textarea" name="reactie" id="reactie" cols="95" rows="3"></textarea>
                    <input type="hidden" name="nieuws_id" value="<?php echo $nieuws_id; ?>" />
                    <input type="hidden" name="action" value="reactie" />
                    <input type="submit" value="Reageer" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'" id="opslaan_reactie" />
                </form>
            </div>
        <div id="reactie_lijst">
        <?php
            if($count_reacties > 0){
                                            
                    while($reactie = mysql_fetch_array($reacties_result)){
                        //haal de schrijver op
                        $what = 'voornaam, achternaam, gebruikersnaam, profielfoto';
                        $from = 'portal_gebruiker';
                        $where = 'id = '.$reactie['geschreven_door'];
                            $reactie_user = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                        
                        $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$reactie_user['profielfoto'].' AND actief = 1'; 
                                    $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));                            
                        //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
                        if($profielfoto['path'] != null){
                            $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
                        }else{
                            $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
                        }
                            
                        
                        if($class_i == 0){$class="om"; $class_i = 1;}else{$class="en_om"; $class_i = 0;}?>
            <div class="reactie <?php echo $class; ?>" 
                 onmouseover="this.className='reactie reactie_hover <?php echo $class; ?>'" onmouseout="this.className='reactie <?php echo $class; ?>'">
               <div class="reactie_links">
                <div class="reactie_foto">
                  <div class="reactie_profiel_pic">
                    <a href="/profiel/<?php echo $reactie_user['gebruikersnaam']; ?>" title="bekijk het profiel"><?php echo $image; ?></a>
                    <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                  </div>
                </div>
                </div>
                <div class="reactie_rechts">
                    <div class="reactie_header">
                        <p>
                            <span class="auteur">
                                <a href="/profiel/<?php echo $reactie_user['gebruikersnaam']; ?>" title="bekijk het profiel">
                                    <?php echo $reactie_user['voornaam'].' '.$reactie_user['achternaam']; ?>
                                </a>
                            </span>
                            <span class="datum"><?php echo verstrekenTijd($reactie['geschreven_op']); ?></span>   
                            <?php
                            if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                            <a class="delete" href="<?php echo $etc_root; ?>functions/home/includes/home_check.php?action=delete_reactie&reactie_id=<?php echo $reactie['id']; ?>&nieuws_id=<?php echo $nieuws_id ?>" title="Verwijder reactie">
                                <img src="<?php echo $etc_root; ?>functions/home/css/images/delete.png" alt="X" title="Verwijder reactie"> 
                            </a>              
                            <?php }?>                 
                        </p>
                    </div>                  
                    <div class="reactie_tekst"><?php echo $reactie['inhoud'] ?></div>           
                </div>
            </div>
                    <?php 
                    }
                
                }else{
                    echo 'Er zijn geen reacties achtergelaten bij dit nieuwsitem';
                }
        ?>
        </div>
    </div>
</div>