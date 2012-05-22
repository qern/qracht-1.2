<?php
include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
$zoekwoord = $_GET['zoekwoord'];

//Deze php is bedoeld om met ajax te bepalen wat er in het nieuwsoverzicht getoond mag worden
        //haal nieuws op
    $what = 'a.id, a.titel, a.teaser, a.afbeelding, DATE_FORMAT(a.geschreven_op, \'%W %d %M %Y om %H:%i\') AS geschreven_op,
             UNIX_TIMESTAMP(a.laatste_wijziging) AS timestamp, b.voornaam, b.achternaam'; 
    $from='portal_nieuws a
           LEFT JOIN portal_gebruiker AS b ON(b.id = a.geschreven_door)';
    $where="a.titel LIKE '%$zoekwoord%' OR a.teaser LIKE '%$zoekwoord%' OR a.inhoud LIKE '%$zoekwoord%' 
            AND a.actief = 1 AND a.publicatiedatum <= CURDATE() AND (a.archiveerdatum >= CURDATE() OR a.archiveerdatum = 0000-00-00) AND b.actief = 1";
        $nieuws_result = sqlSelect($what, $from, $where);
    
    //maak nieuws 'aan'
    while($nieuws = mysql_fetch_array($nieuws_result)){
        //zijn er bestanden ?
        $what= 'COUNT(id) AS aantal'; $from = 'portal_nieuws_bestand'; $where='nieuws = '.$nieuws['id'];
            $bestanden = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        //zijn er foto's' ?
        $what= 'COUNT(b.id) AS aantal'; $from = 'portal_nieuws_album a LEFT JOIN portal_image AS b ON (b.album = a.album)'; $where='a.nieuws = '.$nieuws['id'].' AND b.actief = 1';
            $fotos = mysql_fetch_assoc(sqlSelect($what, $from, $where));

            $normaal_nieuws[$nieuws['timestamp']] = '
            <div id="message_'.$nieuws['id'].'"></div>
            <div class="nieuwsitem" onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem \'">
                <div class="nieuwsitem_header">
                    <a href="#" onclick="toonNieuws('.$nieuws['id'].')"><h3>'.$nieuws['titel'].'</h3></a>';
            if($bestanden['aantal'] > 0){
                 $normaal_nieuws[$nieuws['timestamp']] .= '
                    <div class="nieuwsitem_bestanden">
                        <img src="'.$etc_root.'functions/'.$functie_get.'/css/images/attachment.png" alt="bestanden" title="'.$bestanden['aantal'].' bestanden" />
                    </div>';
            }
            if($fotos['aantal'] > 0){
                 $normaal_nieuws[$nieuws['timestamp']] .= '
                    <div class="nieuwsitem_afbeeldingen">
                        <img src="'.$etc_root.'functions/'.$functie_get.'/css/images/image.png" alt="afbeeldingen" title="'.$fotos['aantal'].' foto\'s" />
                    </div>';
            }
            $normaal_nieuws[$nieuws['timestamp']] .='
                    <span class="nieuwsitem_author">geschreven door: '.$nieuws['voornaam'].' '.$nieuws['achternaam'].' - '.$nieuws['geschreven_op'].'</span>
                </div>
                <div class="nieuwsitem_content">
                    <p>';
             if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'])){
              $normaal_nieuws[$nieuws['timestamp']] .=  '
              <a href="#" onclick="toonNieuws('.$nieuws['id'].')">
                <img class="teaser_img" src="'.$etc_root.'lib/slir/'.slirImage(60,60).$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'].'" alt="'.$nieuws['titel'].'" />
              </a>';  
             }
             $normaal_nieuws[$nieuws['timestamp']] .= $nieuws['teaser'].'
                    </p>
                </div>
                <div class="nieuwsitem_next">
                    <a href="#" onclick="toonNieuws('.$nieuws['id'].')">
                        &nbsp;
                    </a>
                </div>'; 
            $what = 'b.id, b.inhoud, DATE_FORMAT(b.geschreven_op, \'%W %d %M %Y om %H:%i\') AS geschreven_op, b.geschreven_door, 
                     c.voornaam, c.achternaam, c.gebruikersnaam, 
                     d.path AS profielfoto, d.album';
            $from = 'portal_nieuws_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)
                     LEFT JOIN portal_image AS d ON (d.id = c.profielfoto)';
            $where = 'a.nieuws ='.$nieuws['id'].' AND b.actief = 1 AND c.actief = 1 AND d.actief = 1';
            //echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                if($reactie_aantal > 0){
                    $normaal_nieuws[$nieuws['timestamp']] .= '
                    <div id="nieuws_reacties_'.$nieuws['id'].'">
                    <a href="javascript:toggle_reacties('.$nieuws['id'].', \'nieuws\');" id="toon_reacties" class="tooltip"><h3>Reacties ('.$reactie_aantal.')</h3></a>
                        <div id="nieuws_reactielijst_'.$nieuws['id'].'" style="display:none;">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                           $normaal_nieuws[$nieuws['timestamp']] .= '
                           <div class="tijdlijn_reactie '.$class.'" onmouseover="this.className=\'tijdlijn_reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'tijdlijn_reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="/profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    <img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$reactie['album'].'/'.$reactie['profielfoto'].'" 
                                                    alt="de profielfoto" title="bekijk het profiel">               
                                                </a>
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
                                            <span class="datum">'.$reactie['geschreven_op'].'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$nieuws['timestamp']] .= '
                                            <a class="delete" href="'.$etc_root.'functions/home/includes/home_check.php?action=delete_reactie&reactie_id='.$reactie['id'].'&nieuws_id='.$nieuws['id'].'" title="Verwijder reactie">
                                                <img src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie"> 
                                            </a>';
                                        }             
                                        $normaal_nieuws[$nieuws['timestamp']] .='
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst">'.$reactie['inhoud'].'</div>           
                                </div>
                           </div>';
                         }
                    $normaal_nieuws[$nieuws['timestamp']] .= '
                        </div>
                    </div>
                    <div id="tijdlijn_reageren">
                    	<div class="wrapper_tijdlijn_reageren">
	                        <textarea class="textarea" id="nieuws_reactie_'.$nieuws['id'].'" cols="50" rows="3"></textarea>
    	                    <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$nieuws['id'].', \'nieuws\')">Reageer</button>
    	                </div>
                    </div>';
                }
            $normaal_nieuws[$nieuws['timestamp']] .='</div>
            ';
    }

    // haal reacties op
    $what='id, inhoud, geschreven_door'; 
    
    $what = 'a.nieuws AS id, b.titel AS titel, UNIX_TIMESTAMP(b.geschreven_op) AS timestamp, \'nieuws\' AS soort'; 
    $from = 'portal_nieuws_album a LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)';
        $where = 'a.album = '.$foto['album'].'
        UNION
        SELECT
            gebruiker AS id,
            \'\' AS titel,
            \'1\' AS timestamp,
            \'profiel\' AS soort
        FROM 
            portal_gebruiker_album
        WHERE
           album = '.$foto['album'];
           
    //haal foto's op
    $what = 'COUNT(a.id) AS aantal, MAX(a.id) AS anchor, a.path, a.omschrijving, a.album, UNIX_TIMESTAMP(MAX(a.geupload_op)) AS timestamp, 
             DATE_FORMAT(MAX(a.geupload_op), \'%W %d %M %Y om %H:%i\') AS geupload_op,
             b.naam, c.voornaam, c.achternaam, c.gebruikersnaam';
    $from = 'portal_image a
             LEFT JOIN portal_album AS b ON (b.id = a.album)
             LEFT JOIN portal_gebruiker AS c ON (c.id = a.geupload_door)';
    $where = 'a.actief = 1 AND c.actief = 1 
              GROUP BY (a.verzameling)
              ORDER BY a.album ASC, a.geupload_op ASC';
        $foto_result = sqlSelect($what, $from, $where);
    //echo "SELECT $what FROM $from WHERE $where";
    //maak foto's 'aan'
    while($foto = mysql_fetch_array($foto_result)){
        //is het ge�pload bij een profiel of een nieuwsitem ?
        $what = 'a.nieuws AS id, b.titel AS titel, UNIX_TIMESTAMP(b.geschreven_op) AS timestamp, \'nieuws\' AS soort'; 
        $from = 'portal_nieuws_album a LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)';
        $where = 'a.album = '.$foto['album'].'
        UNION
        SELECT
            gebruiker AS id,
            \'\' AS titel,
            \'1\' AS timestamp,
            \'profiel\' AS soort
        FROM 
            portal_gebruiker_album
        WHERE
           album = '.$foto['album'];
        $soort = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
        if($soort['soort'] == 'nieuws'){
            if($foto['aantal'] > 1){
               $normaal_nieuws[$foto['timestamp']] = '
                        <div class="nieuwsitem " onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                            <div class="nieuwsitem_header">
                                <a href="#" onclick="toonNieuws('.$soort['id'].')">
                                    <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft '.$foto['aantal'].' nieuwe foto\'s toegevoegd aan '."'".$soort['titel']."'".'</h4>
                                </a>
                                <!-- <span class="nieuwsitem_author">geupload door: '.$foto['voornaam'].' '.$foto['achternaam'].' - '.$foto['geupload_op'].'</span> -->
                            </div>
                            <!-- <div class="nieuwsitem_next">
                                <a href="#" onclick="toonNieuws('.$soort['id'].')">&nbsp;</a>
                            </div> -->
                        </div>';
                
            }else{
            $normaal_nieuws[$foto['timestamp']] = '
            <div class="nieuwsitem " onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                <div class="nieuwsitem_header">
                    <a href="#" onclick="toonNieuws('.$soort['id'].')">
                        <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft een nieuwe foto toegevoegd aan '."'".$soort['titel']."'".'</h4>
                    </a>
                    <!-- <span class="nieuwsitem_author">geupload door: '.$foto['voornaam'].' '.$foto['achternaam'].' - '.$foto['geupload_op'].'</span> -->
                </div>
                <div class="nieuwsitem_content">
                    <p>
                        <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['anchor'].'&refer=home">
                            <img class="nieuwsitem_foto" src="'.$etc_root.'lib/slir/'.slirImage(300,0, 0).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'">&nbsp;
                        </a>
                    </p>
                </div>
                <!-- <div class="nieuwsitem_next">
                    <a href="#" onclick="toonNieuws('.$soort['id'].')">
                        &nbsp;
                    </a>
                </div> -->'; 
            $what = 'b.id, b.inhoud, DATE_FORMAT(b.geschreven_op, \'%W %d %M %Y om %H:%i\') AS geschreven_op, b.geschreven_door,
                     c.voornaam, c.achternaam, c.gebruikersnaam, 
                     d.path AS profielfoto, d.album';
            $from = 'portal_image_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)
                     LEFT JOIN portal_image AS d ON (d.id = c.profielfoto)';
            $where = 'a.image ='.$foto['anchor'].' AND b.actief = 1 AND c.actief = 1 AND d.actief = 1';
            //echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                if($reactie_aantal > 0){
                    $normaal_nieuws[$foto['timestamp']] .= '
                    <div id="foto_reacties_'.$foto['anchor'].'">
                    <a href="javascript:toggle_reacties('.$foto['anchor'].', \'foto\');" id="toon_reacties" class="tooltip"><h3>Reacties ('.$reactie_aantal.')</h3></a>
                        <div id="foto_reactielijst_'.$foto['anchor'].'" class="foto_reactielijst" style="display:none;">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                           $normaal_nieuws[$foto['timestamp']] .= '
                           <div class="tijdlijn_reactie '.$class.'" onmouseover="this.className=\'tijdlijn_reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'tijdlijn_reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="/profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    <img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$reactie['album'].'/'.$reactie['profielfoto'].'" 
                                                    alt="de profielfoto" title="bekijk het profiel">               
                                                </a>
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
                                            <span class="datum">'.$reactie['geschreven_op'].'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$foto['timestamp']] .= '
                                            <a class="delete" href="'.$etc_root.'functions/home/includes/home_check.php?action=delete_reactie&reactie_id='.$reactie['id'].'&nieuws_id='.$soort['id'].'" title="Verwijder reactie">
                                                <img src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie"> 
                                            </a>';
                                        }              
                                        $normaal_nieuws[$foto['timestamp']] .='
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst">'.$reactie['inhoud'].'</div>           
                                </div>
                           </div>';
                         }
                    $normaal_nieuws[$foto['timestamp']] .= '
                        </div>
                    </div>
                    <div id="tijdlijn_reageren">
                    	<div class="wrapper_tijdlijn_reageren">
	                        <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="foto_reactie_'.$foto['anchor'].'" cols="50" rows="3"></textarea>
    	                    <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$foto['anchor'].', \'foto\')">Reageer</button>
    	                </div>
                    </div>';
                }
            $normaal_nieuws[$foto['timestamp']] .='</div>'; 
            }
        }
        elseif($soort['soort'] == 'profiel'){
            $normaal_nieuws[$foto['timestamp']] = '
            <div class="foto " onmouseover="this.className=\'foto_hover\'" onmouseout="this.className=\'foto\'">
                <div class="foto_header">
                    <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'">
                        <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft een nieuwe foto toegevoegd aan zijn/haar profiel toegevoegd</h4>
                    </a>
                    <!-- <span class="nieuwsitem_author">geupload door: '.$foto['voornaam'].' '.$foto['achternaam'].' - '.$foto['geupload_op'].'</span> -->
                </div>
                <div class="foto_content">
                    <p>
                        <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['anchor'].'&refer=home">
                            <img src="'.$etc_root.'lib/slir/'.slirImage(300,0, 0).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'">&nbsp;
                        </a>
                    </p>
                </div> '; 
            $what = 'b.id, b.inhoud, DATE_FORMAT(b.geschreven_op, \'%W %d %M %Y om %H:%i\') AS geschreven_op, b.geschreven_door, 
                     c.voornaam, c.achternaam, c.gebruikersnaam, 
                     d.path AS profielfoto, d.album';
            $from = 'portal_image_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)
                     LEFT JOIN portal_image AS d ON (d.id = c.profielfoto)';
            $where = 'a.image ='.$foto['anchor'].' AND b.actief = 1 AND c.actief = 1 AND d.actief = 1';
            //echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                if($reactie_aantal > 0){
                    $normaal_nieuws[$foto['timestamp']] .= '
                    <div id="foto_reacties_'.$foto['anchor'].'">
                    <a href="javascript:toggle_reacties('.$foto['anchor'].', \'foto\');" id="toon_reacties" class="tooltip"><h3>Reacties ('.$reactie_aantal.')</h3></a>
                        <div id="foto_reactielijst_'.$foto['anchor'].'" class="foto_reactielijst" style="display:none;">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                           $normaal_nieuws[$foto['timestamp']] .= '
                           <div class="tijdlijn_reactie '.$class.'" onmouseover="this.className=\'tijdlijn_reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'tijdlijn_reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="/profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    <img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$reactie['album'].'/'.$reactie['profielfoto'].'" 
                                                    alt="de profielfoto" title="bekijk het profiel">               
                                                </a>
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
                                            <span class="datum">'.$reactie['geschreven_op'].'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$foto['timestamp']] .= '
                                            <a class="delete" href="'.$etc_root.'functions/home/includes/home_check.php?action=delete_reactie&reactie_id='.$reactie['id'].'&nieuws_id='.$nieuws_id.'" title="Verwijder reactie">
                                                <img src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie"> 
                                            </a>';
                                        }              
                                        $normaal_nieuws[$foto['timestamp']] .='
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst">'.$reactie['inhoud'].'</div>           
                                </div>
                           </div>';
                         }
                    $normaal_nieuws[$foto['timestamp']] .= '
                        </div>
                    </div>
                    <div id="tijdlijn_reageren">
                    	<div class="wrapper_tijdlijn_reageren">
	                        <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="foto_reactie_'.$foto['anchor'].'" cols="50" rows="3"></textarea>
    	                    <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$foto['anchor'].', \'foto\')">Reageer</button>
    	                </div>
                    </div>';
                }
            $normaal_nieuws[$foto['timestamp']] .='</div>';
        }
           
    }
//plaats nieuws
?>