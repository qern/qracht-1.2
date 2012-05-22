<?php 
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
$datum_interval = '10';
//$max_interval =  ceil((time() - 1322719200) / (60*60*24*30)); //je mag maar terug kijken tot 1 december 2011. Daarvoor, is er niets !
$max_interval =  ceil((time() - 1322719200) / (60*60*24*10)); //op basis van 10 dagen !!!!
//echo (time() - 1322719200);
//echo time() - 1322719200;
//Deze php is bedoeld om met ajax te bepalen wat er in het nieuwsoverzicht getoond mag worden
if($_GET['filter'] == 'nieuws' || $_GET['filter'] == 'alles'){
    
        //haal nieuws op
    $what = 'a.id, a.titel, a.teaser, a.afbeelding, UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op,
             UNIX_TIMESTAMP(a.update_datum) AS timestamp, b.voornaam, b.achternaam, b.gebruikersnaam'; 
    $from='portal_nieuws a
           LEFT JOIN portal_gebruiker AS b ON(b.id = a.geschreven_door)
           LEFT JOIN portal_nieuws_gebruiker AS c ON (c.nieuws = a.id)';
    $where='a.actief = 1 AND c.gebruiker = '.$login_id;
    if($_GET['interval'] > 1){
        $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
        $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
        $where .= ' AND  a.update_datum < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.update_datum >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY)';
    }else{
        $where .= ' AND a.update_datum >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY)';
    }
    $where .=' AND a.publicatiedatum <= CURDATE() AND (a.archiveerdatum >= CURDATE() OR a.archiveerdatum = 0000-00-00) AND b.actief = 1';
        $nieuws_result = sqlSelect($what, $from, $where);
   // echo "SELECT $what FROM $from WHERE $where";
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
                    <a href="#" onclick="toonNieuws(\''.$nieuws['id'].'\')"><h3>'.$nieuws['titel'].'</h3></a>';
            if($bestanden['aantal'] > 0){
                 $normaal_nieuws[$nieuws['timestamp']] .= '
                    <div class="nieuwsitem_bestanden">
                        <img src="'.$etc_root.'functions/home/css/images/attachment.png" alt="bestanden" title="'.$bestanden['aantal'].' bestanden" />
                    </div>';
            }
            if($fotos['aantal'] > 0){
                 $normaal_nieuws[$nieuws['timestamp']] .= '
                    <div class="nieuwsitem_afbeeldingen">
                        <img src="'.$etc_root.'functions/home/css/images/image.png" alt="afbeeldingen" title="'.$fotos['aantal'].' foto\'s" />
                    </div>';
            }
            $normaal_nieuws[$nieuws['timestamp']] .='
                    <span class="nieuwsitem_author">
                        Geschreven door: <a href="'.$etc_root.'profiel/'.$nieuws['gebruikersnaam'].'">'.$nieuws['voornaam'].' '.$nieuws['achternaam'].'</a> - Laatste update: '.verstrekenTijd($nieuws['timestamp']).'
                    </span>
                </div>
                <div class="nieuwsitem_content">
                    <p>';
             if(is_file($_SERVER['DOCUMENT_ROOT'].''.$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'])){
              $normaal_nieuws[$nieuws['timestamp']] .=  '
              <a href="#" onclick="toonNieuws(\''.$nieuws['id'].'\')">
                <img class="teaser_img" src="'.$etc_root.'lib/slir/'.slirImage(60,60).$etc_root.'files/nieuws_afbeelding/'.$nieuws['afbeelding'].'" alt="'.$nieuws['titel'].'" />
              </a>';  
             }
             $normaal_nieuws[$nieuws['timestamp']] .= $nieuws['teaser'].'
                    </p>
                </div>
                <div class="nieuwsitem_next">
                    <a href="#" onclick="toonNieuws(\''.$nieuws['id'].'\')">
                        &nbsp;
                    </a>
                </div>'; 
            $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door, 
                     c.voornaam, c.achternaam, c.gebruikersnaam,c.profielfoto';
            $from = 'portal_nieuws_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
            $where = 'a.nieuws ='.$nieuws['id'].' AND b.actief = 1 AND c.actief = 1 ORDER BY b.geschreven_op ASC';
            //echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                    $normaal_nieuws[$nieuws['timestamp']] .= '
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
                            
                           $normaal_nieuws[$nieuws['timestamp']] .= '
                           <div class="tijdlijn_reactie '.$class.'" onmouseover="this.className=\'tijdlijn_reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'tijdlijn_reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">'.$image.'</a>
                                                <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reactie_rechts">
                                    <div class="reactie_header">
                                        <p>
                                            <span class="auteur">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    '.$reactie['voornaam'].' '.$reactie['achternaam'].'
                                                </a>
                                            </span>
                                            <span class="datum">'.verstrekenTijd($reactie['geschreven_op']).'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$nieuws['timestamp']] .= '
                                            <img class="delete" src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie('.$nieuws['id'].',\'nieuws\','.$reactie['id'].')" />';
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
  <div class="tijdlijn_reageren" id="nieuws_reageren_'.$nieuws['id'].'">
                        <div class="wrapper_tijdlijn_reageren">
	                        <span class="tijdlijn_reactie_profilepic">'.$gebruiker_profielfoto.'</span>
	                        <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="nieuws_reactie_'.$nieuws['id'].'" cols="50" rows="3"></textarea>
                        	<button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$nieuws['id'].', \'nieuws\')">Reageer</button>
                        </div>
                    </div>';
                
            $normaal_nieuws[$nieuws['timestamp']] .='</div>
            ';
    }
    
    //haal bestanden op
    $what = 'COUNT(a.id) AS aantal, UNIX_TIMESTAMP(MAX(a.toegevoegd_op)) AS timestamp, UNIX_TIMESTAMP(MAX(a.toegevoegd_op)) AS toegevoegd_op, 
             b.id, b.titel,
             c.voornaam, c.achternaam'; 
    $from='portal_nieuws_bestand a
           LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)
           LEFT JOIN portal_gebruiker AS c ON (c.id = a.toegevoegd_door)
           LEFT JOIN portal_nieuws_gebruiker AS d ON (d.nieuws = a.nieuws)';
    $where='b.actief = 1 AND d.gebruiker = '.$login_id;
    if($_GET['interval'] > 1){
        $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
        $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
        $where .= ' AND  a.toegevoegd_op < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.toegevoegd_op >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY)';
    }else{
        $where .= ' AND a.toegevoegd_op >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY)';
    }
    
    $where .='  AND b.publicatiedatum <= CURDATE() AND (b.archiveerdatum >= CURDATE() OR b.archiveerdatum = 0000-00-00)
                AND c.actief = 1 
                GROUP BY (a.verzameling)
                ORDER BY a.toegevoegd_op ASC';
        $aantal_bestanden = countRows($what, $from, $where);
        
    if($aantal_bestanden > 0){
    $bestanden_result = sqlSelect($what, $from, $where);
    
    //maak bestanden 'aan'
    while($bestand = mysql_fetch_array($bestanden_result)){
            if($bestand['aantal'] > 1){
               $normaal_nieuws[$bestand['timestamp']] = '
                        <div class="nieuwsitem " onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                            <div class="nieuwsitem_header">
                                <a href="#" onclick="toonNieuws('.$bestand['id'].')">
                                    <h4>'.$bestand['voornaam'].' '.$bestand['achternaam'].' heeft '.$bestand['aantal'].' nieuwe bestanden toegevoegd aan '."'".$bestand['titel']."'".'</h4>
                                </a>
                                <span class="nieuwsitem_author">'.verstrekenTijd($bestand['toegevoegd_op']).'</span>
                            </div>
                            <!-- <div class="nieuwsitem_next">
                                <a href="#" onclick="toonNieuws('.$soort['id'].')">&nbsp;</a>
                            </div> -->
                        </div>';
                
            }
            else{
            $normaal_nieuws[$bestand['timestamp']] = '
            <div class="nieuwsitem " onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                <div class="nieuwsitem_header">
                    <a href="#" onclick="toonNieuws('.$bestand['id'].')">
                        <h4>'.$bestand['voornaam'].' '.$bestand['achternaam'].' heeft een nieuw bestand toegevoegd aan '."'".$bestand['titel']."'".'</h4>
                    </a>
                   <span class="nieuwsitem_author">'.verstrekenTijd($bestand['toegevoegd_op']).'</span>
                </div>
                <!--<div class="nieuwsitem_content">
                    <p>
                        <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['anchor'].'&refer=home">
                            <img class="nieuwsitem_foto" src="'.$etc_root.'lib/slir/'.slirImage(300,0, 0).''.$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'">&nbsp;
                        </a>
                    </p>
                </div>
                 <div class="nieuwsitem_next">
                    <a href="#" onclick="toonNieuws('.$soort['id'].')">
                        &nbsp;
                    </a>
                </div> -->
            </div>';
            }
    }
    }
}
if($_GET['filter'] == 'fotos' || $_GET['filter'] == 'alles'){
    if($_GET['interval'] > 1){$datum_interval = $datum_interval * $_GET['interval'];}
    //haal foto's op
    $what = 'COUNT(a.id) AS aantal, MAX(a.id) AS anchor, a.path, a.omschrijving, a.album, UNIX_TIMESTAMP(MAX(a.update_datum)) AS timestamp, 
             UNIX_TIMESTAMP(MAX(a.geupload_op)) AS geupload_op,
             b.naam, c.voornaam, c.achternaam, c.gebruikersnaam';
    $from = 'portal_image a
             LEFT JOIN portal_album AS b ON (b.id = a.album)
             LEFT JOIN portal_gebruiker AS c ON (c.id = a.geupload_door)';
    $where = 'a.actief = 1';
    if($_GET['interval'] > 1){
        $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
        $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
        $where .= ' AND  a.update_datum < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.update_datum >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY)';
    }else{
        $where .= ' AND a.update_datum >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY)';
    }
    $where .='  AND c.actief = 1 
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
                        <div class="nieuwsitem" onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                            <div class="nieuwsitem_header">
                                <a href="#" onclick="toonNieuws(\''.$soort['id'].'\')">
                                    <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft '.$foto['aantal'].' nieuwe foto\'s toegevoegd aan '."'".$soort['titel']."'".'</h4>
                                </a>
                                <span class="foto_datum">'.verstrekenTijd($foto['geupload_op']).'</span>
                            </div>
                            <!-- <div class="nieuwsitem_next">
                                <a href="#" onclick="toonNieuws('.$soort['id'].')">&nbsp;</a>
                            </div> -->
                        </div>';
                
            }else{
               // if($foto['timestamp'] ==  $foto['geupload_op']){
                    $tekst = '
                    <a href="#" onclick="toonNieuws(\''.$soort['id'].'\')">
                        <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft een nieuwe foto toegevoegd aan '."'".$soort['titel']."'".'</h4>
                    </a>';
                //}else{  $tekst = '';  }
            $normaal_nieuws[$foto['timestamp']] = '
            <div class="nieuwsitem" onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                <div class="nieuwsitem_header">
                    '.$tekst.'
                    <span class="foto_datum">'.verstrekenTijd($foto['geupload_op']).'</span>
                </div>
                <div class="nieuwsitem_content">
                    <p>
                        <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['anchor'].'&amp;refer=home">
                            <img class="nieuwsitem_foto" src="'.$etc_root.'lib/slir/'.slirImage(300,0, 0).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'">&nbsp;
                        </a>
                    </p>
                </div>
                <!-- <div class="nieuwsitem_next">
                    <a href="#" onclick="toonNieuws(\''.$soort['id'].'\')">
                        &nbsp;
                    </a>
                </div> -->'; 
            $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
            $from = 'portal_image_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
            $where = 'a.image ='.$foto['anchor'].' AND b.actief = 1 AND c.actief = 1  ORDER BY b.geschreven_op ASC';
            echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                    $normaal_nieuws[$foto['timestamp']] .= '
                    <div id="foto_reacties_'.$foto['anchor'].'">
                        <div id="foto_reactielijst_'.$foto['anchor'].'" class="foto_reactielijst">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                            $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1'; 
                                    $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                            //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
                            if($profielfoto['path'] != null){
                                
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
                            }else{
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
                            }
                            
                           $normaal_nieuws[$foto['timestamp']] .= '
                           <div class="tijdlijn_reactie" onmouseover="this.className=\'tijdlijn_reactie reactie_hover\'" onmouseout="this.className=\'tijdlijn_reactie\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">'.$image.'</a>
                                                <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reactie_rechts">
                                    <div class="reactie_header">
                                        <p>
                                            <span class="auteur">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    '.$reactie['voornaam'].' '.$reactie['achternaam'].'
                                                </a>
                                            </span>
                                            <span class="datum">'.verstrekenTijd($reactie['geschreven_op']).'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$foto['timestamp']] .= '
                                            <img class="delete" src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie('.$foto['anchor'].',\'foto\','.$reactie['id'].')" />';
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
 <div class="tijdlijn_reageren" id="foto_reageren_'.$foto['anchor'].'">
                    	<div class="wrapper_tijdlijn_reageren">
	                        <span class="tijdlijn_reactie_profilepic">'.$gebruiker_profielfoto.'</span>
	                        <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="foto_reactie_'.$foto['anchor'].'" cols="50" rows="3"></textarea>
	                        <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$foto['anchor'].', \'foto\')">Reageer</button>
	                    </div>
                    </div>';
                
            $normaal_nieuws[$foto['timestamp']] .='</div>'; 
            }
        }
        elseif($soort['soort'] == 'profiel'){
            if($foto['aantal'] > 1){
               $normaal_nieuws[$foto['timestamp']] = '
                        <div class="nieuwsitem" onmouseover="this.className=\'nieuwsitem_hover\'" onmouseout="this.className=\'nieuwsitem\'">
                            <div class="nieuwsitem_header">
                                <a href="'.$etc_root.'profiel/'.$foto['gebruikersnaam'].'">
                                    <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft '.$foto['aantal'].' nieuwe foto\'s toegevoegd aan zijn/haar profiel</h4>
                                </a>
                                <span class="foto_datum">'.verstrekenTijd($foto['geupload_op']).'</span>
                            </div>
                            <!-- <div class="nieuwsitem_next">
                                <a href="#" onclick="toonNieuws('.$soort['id'].')">&nbsp;</a>
                            </div> -->
                        </div>';
                
            }else{
                if($foto['timestamp'] ==  $foto['geupload_op']){
                    $tekst = '
                    <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['anchor'].'">
                        <h4>'.$foto['voornaam'].' '.$foto['achternaam'].' heeft een nieuwe foto toegevoegd aan zijn/haar profiel</h4>
                    </a>';
                }else{
                    $tekst = '';
                }
            $normaal_nieuws[$foto['timestamp']] = '
            <div class="foto" onmouseover="this.className=\'foto_hover\'" onmouseout="this.className=\'foto\'">
                <div class="foto_header">
                    '.$tekst.'
                    <span class="foto_datum">'.verstrekenTijd($foto['geupload_op']).'</span>
                </div>
                <div class="foto_content">
                    <p>
                        <a class="iframe" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['anchor'].'&amp;refer=home">
                            <img src="'.$etc_root.'lib/slir/'.slirImage(300,0, 0).$etc_root.'files/album/'.$foto['album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'">&nbsp;
                        </a>
                    </p>
                </div> '; 
            $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door, 
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
            $from = 'portal_image_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
            $where = 'a.image ='.$foto['anchor'].' AND b.actief = 1 AND c.actief = 1  ORDER BY b.geschreven_op ASC';
            //echo "SELECT $what FROM $from WHERE $where";
                $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
                    $normaal_nieuws[$foto['timestamp']] .= '
                    <div id="foto_reacties_'.$foto['anchor'].'">
                        <div id="foto_reactielijst_'.$foto['anchor'].'" class="foto_reactielijst">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                            $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1'; 
                                    $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                            //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
                            if($profielfoto['path'] != null){
                                
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
                            }else{
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
                            }
                            
                           $normaal_nieuws[$foto['timestamp']] .= '
                           <div class="tijdlijn_reactie" onmouseover="this.className=\'tijdlijn_reactie reactie_hover\'" onmouseout="this.className=\'tijdlijn_reactie\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">'.$image.'</a>
                                                <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reactie_rechts">
                                    <div class="reactie_header">
                                        <p>
                                            <span class="auteur">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    '.$reactie['voornaam'].' '.$reactie['achternaam'].'
                                                </a>
                                            </span>
                                            <span class="datum">'.verstrekenTijd($reactie['geschreven_op']).'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$foto['timestamp']] .= '
                                        <img class="delete" src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie('.$foto['anchor'].',\'foto\','.$reactie['id'].')" />';
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
<div class="tijdlijn_reageren" id="foto_reageren_'.$foto['anchor'].'">
                    	<div class="wrapper_tijdlijn_reageren">
	                        <span class="tijdlijn_reactie_profilepic">'.$gebruiker_profielfoto.'</span>
    	                    <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="foto_reactie_'.$foto['anchor'].'" cols="50" rows="3"></textarea>
        	                <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$foto['anchor'].', \'foto\')">Reageer</button>
        	            </div>
                    </div>';
                
            $normaal_nieuws[$foto['timestamp']] .='</div>';
        }
           
    }
    }
}
if($_GET['filter'] == 'status' || $_GET['filter'] == 'alles'){
    if($_GET['interval'] > 1){$datum_interval = $datum_interval * $_GET['interval'];}
    
    //haal statussen op
    $what = 'a.id, a.inhoud, UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op, a.gebruiker,
             UNIX_TIMESTAMP(a.update_datum) AS timestamp, b.id AS plaatser_id, b.gebruikersnaam, b.voornaam, b.achternaam, b.profielfoto'; 
    $from='portal_status a
           LEFT JOIN portal_gebruiker AS b ON(b.id = a.geplaatst_door)';
    $where='a.actief = 1';
    if($_GET['interval'] > 1){
        $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
        $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
        $where .= ' AND  a.update_datum < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.update_datum >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY) AND b.actief = 1';
    }else{
        $where .= ' AND a.update_datum >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY) AND b.actief = 1';
    }
    //echo "SELECT $what FROM $from WHERE $where";
        $aantal_statussen = countRows($what, $from, $where);
    
    if($aantal_statussen > 0){
    $status_result = sqlSelect($what, $from, $where);
    
    //maak statussen 'aan'
    while($status = mysql_fetch_array($status_result)){
        //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
        $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$status['profielfoto'].' AND actief = 1';
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                                    
        if($profielfoto['path'] == null){
            $auteur_profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
        }else{
            $auteur_profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
        }
        
        if($status['gebruiker'] == $status['plaatser_id']){
        $header = '<a href="'.$etc_root.'profiel/'.$status['gebruikersnaam'].'">'.$status['voornaam'].' '.$status['achternaam'].'</a>';
        }else{
        $what = 'voornaam, achternaam, gebruikersnaam'; $from = 'portal_gebruiker'; $where = 'id = '.$status['gebruiker'];
            $geplaatst_bij = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        $header = '<a href="'.$etc_root.'profiel/'.$status['gebruikersnaam'].'">'.$status['voornaam'].' '.$status['achternaam'].'</a> bij <a href="'.$etc_root.'profiel/'.$geplaatst_bij['gebruikersnaam'].'">'.$geplaatst_bij['voornaam'].' '.$geplaatst_bij['achternaam'].'</a>';
        }
        $normaal_nieuws[$status['timestamp']] = '
            <div id="message_'.$status['id'].'"></div>
            <div class="status">
                <div class="status_links">
                    <a href="'.$etc_root.'profiel/'.$status['gebruikersnaam'].'">'.$auteur_profiel_foto.'</a>
                </div>
                <div class="status_rechts">
                    <div class="status_header">
                        '.$header.'
                        <span class="status_geschreven_op">'.verstrekenTijd($status['geschreven_op']).'</span>
                    </div>
                    <div class="status_content">
                        <p>'.$status['inhoud'].'</p>
                    </div>
                </div>';
            
            $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door, 
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
            $from = 'portal_status_reactie a
                     LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                     LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
            $where = 'a.status ='.$status['id'].' AND b.actief = 1 AND c.actief = 1  ORDER BY b.geschreven_op ASC';
            //echo "SELECT $what FROM $from WHERE $where";
            $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
            
            $normaal_nieuws[$status['timestamp']] .= '
                <div id="status_reacties_'.$status['id'].'">
                    <div id="status_reactielijst_'.$status['id'].'">';
                        while($reactie = mysql_fetch_array($reactie_result)){
                            $what = 'path, album'; $from =  'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1'; 
                                    $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                            //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
                            if($profielfoto['path'] != null){
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
                            }else{
                                    $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
                            }
                            
                           $normaal_nieuws[$status['timestamp']] .= '
                           <div class="tijdlijn_reactie '.$class.'" onmouseover="this.className=\'tijdlijn_reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'tijdlijn_reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">'.$image.'</a>
                                                <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reactie_rechts">
                                    <div class="reactie_header">
                                        <p>
                                            <span class="auteur">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    '.$reactie['voornaam'].' '.$reactie['achternaam'].'
                                                </a>
                                            </span>
                                            <span class="datum">'.verstrekenTijd($reactie['geschreven_op']).'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            $normaal_nieuws[$status['timestamp']] .= '
                                            <img class="delete" src="'.$etc_root.'functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie('.$status['id'].',\'status\','.$reactie['id'].')" />';
                                        }             
                                        $normaal_nieuws[$status['timestamp']] .='
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst">'.$reactie['inhoud'].'</div>           
                                </div>
                           </div>';
                         }
                    $normaal_nieuws[$status['timestamp']] .= '
                        </div>
                    </div>
<div class="tijdlijn_reageren" id"="status_reageren_'.$status['id'].'">
                    	<div class="wrapper_tijdlijn_reageren">
	                        <span class="tijdlijn_reactie_profilepic">'.$gebruiker_profielfoto.'</span>
    	                    <textarea class="textarea reageren_dicht" onfocus="this.className=\'reageren_open textarea\'" onblur="this.className=\'reageren_dicht textarea\'" id="status_reactie_'.$status['id'].'" cols="50" rows="3"></textarea>
        	                <button class="button" onmouseover="this.className=\'button btn_hover\'" onmouseout="this.className=\'button\'" onclick="herlaadReactieform('.$status['id'].', \'status\')">Reageer</button>
        	            </div>
                    </div>';
                $normaal_nieuws[$status['timestamp']] .= '</div>';
                }
}
    
    //haal de verschillende links op, die geplaatst zijn
    $what = 'a.link, a.omschrijving, UNIX_TIMESTAMP(a.toegevoegd_op) AS timestamp,
             c.gebruikersnaam, c.voornaam, c.achternaam';
    $from = 'portal_link a
             LEFT JOIN portal_gebruiker_link AS b ON (b.link = a.id)
             LEFT JOIN portal_gebruiker AS c ON (c.id = b.gebruiker)';
    $where = 'a.actief = 1 AND c.actief = 1';
    if($_GET['interval'] > 1){
        $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
        $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
        $where .= ' AND  a.toegevoegd_op < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.toegevoegd_op >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY)';
    }else{
        $where .= ' AND a.toegevoegd_op >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY)';
    }
        $aantal_links = countRows($what, $from, $where);
        //echo "SELECT $what FROM $from WHERE $where".'<br />';
    if($aantal_links > 0){
        $links = sqlSelect($what, $from, $where);
                    
        while($link = mysql_fetch_array($links)){
            $normaal_nieuws[$link['timestamp']] = '
            <div class="link">
                <div class="link_link">
                    <a href="'.$etc_root.'profiel/'.$link['gebruikersnaam'].'">'.$link['voornaam'].' '.$link['achternaam'].'</a> heeft de link \'<a href="'.$link['link'].'" target="_blank">'.$link['omschrijving'].'</a>\' toegevoegd
                </div>
                <div class="link_datum">'.verstrekenTijd($link['timestamp']).'</div>
            </div>';
        }
    }

    //haal nu de verschillende wijzigingen op, aan het profiel per persoon
    //wanneer zijn de gegevens gewijzigd ?
    $what = 'a.wat, UNIX_TIMESTAMP(a.gewijzigd_op) AS timestamp,
             b.voornaam, b.achternaam, b.gebruikersnaam'; 
    $from = 'portal_gebruiker_recent a
             LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)';
    $where = 'b.actief = 1';   
        //echo "SELECT $what FROM $from WHERE $where".'<br />';
    if($_GET['interval'] > 1){
        $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
        $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
        $where .= ' AND  a.gewijzigd_op < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.gewijzigd_op >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY)';
    }else{
        $where .= ' AND a.gewijzigd_op >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY)';
    }
    $aantal_wijzigingen = countRows($what, $from, $where);
    if($aantal_wijzigingen > 0){
        $wijzigingen = sqlSelect($what, $from, $where);
                    
        while($wijziging = mysql_fetch_array($wijzigingen)){          
            
        if($wijziging['wat'] == 'persoonlijk'){$wat = 'zijn/haar persoonlijke informatie';}
        elseif($wijziging['wat'] == 'social'){$wat = 'zijn/haar sociale informatie';}
        elseif($wijziging['wat'] == 'profielfoto'){$wat = 'zijn/haar profielfoto';}
        elseif($wijziging['wat'] == 'kennis'){$wat = 'zijn/haar kennis gebieden';}
        elseif($wijziging['wat'] == 'goedin'){$wat = 'waar hij/zij goed in is';}
        else{$wat = 'iets';}
        //elseif($recente_wijziging == 'persoonlijk'){$wat = 'persoonlijke informatie';}
                    
        $normaal_nieuws[$wijziging['timestamp']] = '
            <div class="wijziging">
                <div class="wijziging_link">
                    <a href="'.$etc_root.'profiel/'.$wijziging['gebruikersnaam'].'">
                        '.$wijziging['voornaam'].' '.$wijziging['achternaam'].' heeft '.$wat.' gewijzigd
                    </a>
                </div>
                <div class="wijziging_datum">'.verstrekenTijd($wijziging['timestamp']).'</div>
            </div>';
        }
    }
    
    //wanneer is een gebruiker voor het laatst ingelogd ?
    $what = 'a.gebruiker_id, MAX(UNIX_TIMESTAMP(a.login_tijd)) AS timestamp,
             b.gebruikersnaam, b.voornaam, b.achternaam'; 
    $from= 'portal_inlog a 
            LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker_id)'; 
    $where= 'b.actief = 1 GROUP BY(a.gebruiker_id)';
        $aantal_recente_logins = countRows($what, $from, $where);
        // echo "SELECT $what FROM $from WHERE $where";

    if($aantal_recente_logins > 0){
        $recente_logins = sqlSelect($what, $from, $where);
        while($recente_login = mysql_fetch_array($recente_logins)){
            $normaal_nieuws[$recente_login['timestamp']] = '
            <div class="login">
                <div class="login_link">
                    <a href="'.$etc_root.'profiel/'.$recente_login['gebruikersnaam'].'">
                        '.$recente_login['voornaam'].' '.$recente_login['achternaam'].' is ingelogd
                    </a>
                </div>
                <div class="login_datum">'.verstrekenTijd($recente_login['timestamp']).'</div>
            </div>';
        }        
    }
}

//plaats nieuws
if(count($normaal_nieuws) > 0){
    krsort($normaal_nieuws);
        foreach($normaal_nieuws as $nieuws){
            echo $nieuws;
        }
}
else{
    if($max_interval <= $_GET['interval']){
        if($_GET['filter'] == 'nieuws'){$niet_gevonden = 'nieuwsitems';}
        elseif($_GET['filter'] == 'fotos'){$niet_gevonden  = 'foto\'s';}
        elseif($_GET['filter'] == 'status'){$niet_gevonden  = 'statussen';}
        echo '<div class="niets_gevonden">Er zijn geen '.$niet_gevonden.' meer gevonden.</div>';
    }else{
        if($_GET['filter'] == 'nieuws'){$niet_gevonden = 'nieuwsitems';}
        elseif($_GET['filter'] == 'fotos'){$niet_gevonden  = 'foto\'s';}
        elseif($_GET['filter'] == 'status'){$niet_gevonden  = 'statussen';}
        echo '<div class="niets_gevonden">Er zijn binnen de afgelopen periode geen '.$niet_gevonden.' gevonden. Klik hieronder op meer laden om verder terug te kijken </div>';
    }
}
?>