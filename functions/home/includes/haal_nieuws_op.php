<?php 
    //echo "SELECT $what FROM $from WHERE $where";
    
    //haal nieuws op
    $what = 'a.id, a.titel, a.teaser, a.afbeelding, UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op,
             UNIX_TIMESTAMP(a.update_datum) AS timestamp, b.voornaam, b.achternaam, b.gebruikersnaam'; 
    $from='portal_nieuws a
           LEFT JOIN portal_gebruiker AS b ON(b.id = a.geschreven_door)';
    $where='a.actief = 1 AND a.is_belangrijk = 1 AND a.publicatiedatum <= CURDATE() AND (a.archiveerdatum >= CURDATE() OR a.archiveerdatum = 0000-00-00) AND b.actief = 1';
        $aantal_nieuws = countRows($what, $from, $where);
        
    if($aantal_nieuws > 0){
    $nieuws_result = sqlSelect($what, $from, $where);
    
    //maak nieuws 'aan'
    while($nieuws = mysql_fetch_array($nieuws_result)){
        //zijn er bestanden ?
        $what= 'COUNT(id) AS aantal'; $from = 'portal_nieuws_bestand'; $where='nieuws = '.$nieuws['id'];
            $bestanden = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        //zijn er foto's' ?
        $what= 'COUNT(b.id) AS aantal'; $from = 'portal_nieuws_album a LEFT JOIN portal_image AS b ON (b.album = a.album)'; $where='a.nieuws = '.$nieuws['id'].' AND b.actief = 1';
            $fotos = mysql_fetch_assoc(sqlSelect($what, $from, $where));

            $belangrijk_nieuws[$nieuws['timestamp']] = '
            <div class="nieuwsitem belangrijk" onmouseover="this.className=\'nieuwsitem_hover belangrijk belangrijk_hover\'" onmouseout="this.className=\'nieuwsitem belangrijk \'">
                <div class="nieuwsitem_header">
                    <a href="#" onclick="toonNieuws(\''.$nieuws['id'].'\')"><h3>'.$nieuws['titel'].'</h3></a>';
            if($bestanden['aantal'] > 0){
                 $belangrijk_nieuws[$nieuws['timestamp']] .= '
                    <div class="nieuwsitem_bestanden">
                        <img src="'.$etc_root.'functions/'.$functie_get.'/css/images/attachment.png" alt="bestanden" title="'.$bestanden['aantal'].' bestanden" />
                    </div>';
            }
            if($fotos['aantal'] > 0){
                 $belangrijk_nieuws[$nieuws['timestamp']] .= '
                    <div class="nieuwsitem_afbeeldingen">
                        <img src="'.$etc_root.'functions/'.$functie_get.'/css/images/image.png" alt="afbeeldingen" title="'.$fotos['aantal'].' foto\'s" />
                    </div>';
            }
            $belangrijk_nieuws[$nieuws['timestamp']] .='
                    <span class="nieuwsitem_author">geschreven door: '.$nieuws['voornaam'].' '.$nieuws['achternaam'].' - '.verstrekenTijd($nieuws['geschreven_op']).'</span>   
                </div>
                <div class="nieuwsitem_content">
                    <p>';
             if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'])){
              $belangrijk_nieuws[$nieuws['timestamp']] .=  '
              <a href="#" onclick="toonNieuws(\''.$nieuws['id'].'\')">
                <img class="teaser_img" src="'.$etc_root.'lib/slir/'.slirImage(60,60).$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'].'" alt="'.$nieuws['titel'].'" />
              </a>';  
             }
             $belangrijk_nieuws[$nieuws['timestamp']] .= $nieuws['teaser'].'
                    </p>
                </div>
               <div class="nieuwsitem_next">
                    <a href="#" onclick="toonNieuws(\''.$nieuws['id'].'\')">
                        &nbsp;
                    </a>
                </div>'; 
            $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door, 
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
            $from = 'portal_nieuws_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
            $where = 'a.nieuws ='.$nieuws['id'].' AND b.actief = 1 AND c.actief = 1';
            //echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                    $belangrijk_nieuws[$nieuws['timestamp']] .= '
                    <div id="nieuws_reacties_'.$nieuws['id'].'">
                         <div id="nieuws_reactielijst_'.$nieuws['id'].'" class="nieuws_reactielijst">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                                                        
                            $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1'; 
                                    $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                                    //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
                            if($profielfoto['path'] != null){
                                $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
                            }else{
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
                            }
                            
                           $belangrijk_nieuws[$nieuws['timestamp']] .= '
                           <div class="tijdlijn_reactie '.$class.'" onmouseover="this.className=\'tijdlijn_reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'tijdlijn_reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="/profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">'.$image.'</a>
                                                <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reactie_rechts">
                                    <div class="reactie_header">
                                        <p>
                                            <span class="auteur">
                                                <a href="/profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    '.$reactie['voornaam'].' '.$reactie['achternaam'].'
                                                </a>
                                            </span>
                                            <span class="datum">'.verstrekenTijd($reactie['geschreven_op']).'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $belangrijk_nieuws[$nieuws['timestamp']] .= '
                                            <img class="delete" src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie('.$nieuws['id'].',\'nieuws\','.$reactie['id'].')" />';
                                        }              
                                        $belangrijk_nieuws[$nieuws['timestamp']] .='
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst">'.$reactie['inhoud'].'</div>           
                                </div>
                           </div>';
                         }
                    $belangrijk_nieuws[$nieuws['timestamp']] .= '
                        </div>
                    </div>
                    
                    <div class="tijdlijn_reageren" id="nieuws_reageren_'.$nieuws['id'].'">
                        <div class="wrapper_tijdlijn_reageren">
	                        <span class="tijdlijn_reactie_profilepic">'.$gebruiker_profielfoto.'</span>
    	                    <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="nieuws_reactie_'.$nieuws['id'].'" cols="50" rows="3"></textarea>
 	                       <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$nieuws['id'].', \'nieuws\')">Reageer</button>
 	                    </div>
                    </div>';
                
            $belangrijk_nieuws[$nieuws['timestamp']] .='</div>
            ';
        
    }
    }

?>