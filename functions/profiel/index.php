<?php
    $what = 'a.id, a.gebruikersnaam, a.voornaam, a.achternaam, a.email, a.telefoon,
             a.google_plus, a.twitter, a.skype, a.linkedin, a.facebook, a.youtube, a.hyves, a.is_goed_in,
             a.profielfoto, b.album AS foto_album';
    $from = 'portal_gebruiker a
             LEFT JOIN portal_gebruiker_album AS b ON (b.gebruiker = a.id)';
    if($_GET['gebruikersnaam'] != null){$where = "a.gebruikersnaam = '".$_GET['gebruikersnaam']."'";}
    else{$where = 'a.id ='.$login_id;}
    
    $profiel = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    $profiel_id = $profiel['id']; $profiel_naam = $profiel['voornaam'].' '.$profiel['achternaam']; $profielnaam = $profiel['gebruikersnaam'];
    
    //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
    $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$profiel['profielfoto'].' AND actief = 1';
        $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    if($profielfoto['path'] == null){
        $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(220,150).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
    }else{
        $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(220,150).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
    }
?>
<div id="profiel">

    <div id="profiel_left">
        <div id="info">
            <div id="info_img">
                <?php echo $profiel_foto; ?>
            </div>
            <div id="info_text">
                <h2><?php echo $profiel_naam ?></h2>
                <span id="gebruiker_id" style="display:none; visibility:hidden;"><?php echo $profiel_id ?></span>
            <?php 
                if($profiel['email'] != null){echo '<p id="email"> <a href="mailto:'.$profiel['email'].'">'.$profiel['email'].'</a></p>';}
                if($profiel['telefoon'] != null){echo '<p id="telefoon"> '.$profiel['telefoon'].'</p>';}
            ?>
            </div>
        </div>
        <div id="social">
            <div id="social_headers" class="profiel_header"> 
                <p class="info_title">Sociale <?php echo $profiel_naam;  ?></p>
            </div>
            <div id="social_links">
            <?php
            $image_src = $etc_root.'functions/'.$_GET['function'].'/css/images/'; //zet de src voor alle - te gebruiken - plaatjes
            if($profiel['google_plus'] != null){echo '  <a href="mailto:'.$profiel['email'].'" title="Google Plus" target="_blank"><img src="'.$image_src.'img_icon_24_google.png" alt="Google Plus" /></a>';}
            if($profiel['twitter'] != null){echo '<a href="http://www.twitter.com/'.$profiel['twitter'].'" title="twitter" target="_blank"><img src="'.$image_src.'img_icon_24_twitter.png" alt="twitter" /></a>';}
            if($profiel['skype'] != null){echo '  <a href="callto://'.$profiel['skype'].'" title="skype" target="_blank"><img src="'.$image_src.'img_icon_24_skype.png" alt="skype" /></a>';}
            if($profiel['linkedin'] != null){echo'<a href="'.$profiel['linkedin'].'" title="linkedin" target="_blank"><img src="'.$image_src.'img_icon_24_linkedin.png" alt="linkedin" /></a>';}
            if($profiel['facebook'] != null){echo'<a href="'.$profiel['facebook'].'" title="facebook" target="_blank"><img src="'.$image_src.'img_icon_24_facebook.png" alt="facebook" /></a>';}
            if($profiel['youtube'] != null){echo '<a href="http://www.youtube.com/user/'.$profiel['youtube'].'" title="youtube" target="_blank"><img src="'.$image_src.'img_icon_24_youtube.png" alt="youtube" /></a>';}
            if($profiel['hyves'] != null){echo '  <a href="'.$profiel['hyves'].'" title="hyves" target="_blank"><img src="'.$image_src.'img_icon_24_hyves.png" alt="hyves" /></a>';}
            ?>
            </div>
            <!--
            <div id="leuke_collega">
<?php
$what = "id";
$from = "portal_leuke_collega";
$where = "volgens = $login_id
          AND is_leuk = $profiel_id";
$aantal_collegas = countRows($what,$from,$where);
if($aantal_collegas <= 0){
echo'           <a href="/functions/'.$_GET['function'].'/includes/profiel_check.php?action=leuke_collega&id='.$profiel_id.'" title="leuke collega">
                    <img src="'.$image_src.'leuke_collega_button.png" alt="Leuke Collega" title="Vind jij deze collega leuk?">
                </a>';
}else{
echo '          <span id="al_gestemd">U heeft al gestemd voor deze collega.</span>
';
}
                

    $what = "id";
    $from = "portal_leuke_collega";
    $where =  "is_leuk = $profiel_id";
    $aantal_andere_collegas = countRows($what, $from, $where);
    echo        '<span id="leuke_collega_aantal">'.
                    $aantal_andere_collegas;
?>
                </span>
            </div>
            -->
        </div>
        
        <div id="is_goed_in">
            <div id="kennis_headers" class="profiel_header"> 
                <p class="info_title"><?php echo $profiel_naam;  ?> is goed in</p>
            </div>
            <p><?php
             if($profiel['is_goed_in'] != null){echo $profiel['is_goed_in']; }
             else{echo $profiel_naam.' heeft dit nog niet ingevuld.';}
             ?></p>
        </div>
        <div id="kenniskaart">
            <div id="kennis_headers" class="profiel_header"> 
                <p class="info_title"><?php echo $profiel_naam;  ?> heeft kennis over</p>
            </div>
        <?php
        $what="b.kennis"; $from="portal_gebruiker_kenniskaart a LEFT JOIN portal_kenniskaart AS b ON (b.id = a.kennis)"; 
        $where="a.gebruiker = $profiel_id AND a.top_positie > 0 ORDER BY top_positie ASC LIMIT 10";
            $hoeveelheid_top_kennis = countRows($what,$from,$where);
        if($hoeveelheid_top_kennis > 0){
            $result = sqlSelect($what,$from,$where);
            while($feitenkaart = mysql_fetch_array($result)){
                echo '
                <a class="kennis" href="'.$etc_root.'zoeken/kennis='.$feitenkaart['kennis'].'" title="bekijk wie ook kennis over dit onderwerp hebben.">
                    <span>'.$feitenkaart['kennis'].'</span>
                </a>';
            }
            if($hoeveelheid_top_kennis < 10){
                $what="b.kennis"; $from="portal_gebruiker_kenniskaart a LEFT JOIN portal_kenniskaart AS b ON (b.id = a.kennis)"; 
                $where="a.gebruiker = $profiel_id AND a.top_positie = 0 LIMIT ".(10 - $hoeveelheid_top_kennis);
                    $hoeveelheid_kennis = countRows($what,$from,$where);
                    if($hoeveelheid_kennis > 0){
                        $result = sqlSelect($what,$from,$where);
                        while($feitenkaart = mysql_fetch_array($result)){
                        echo '
                            <a class="kennis" href="'.$etc_root.'zoeken/kennis='.$feitenkaart['kennis'].'" title="bekijk wie ook kennis over dit onderwerp hebben.">
                                <span>'.$feitenkaart['kennis'].'</span>
                            </a>';
                        }
                    }
            }
        }else{
            echo $profiel_naam.' heeft nog geen kennis toegevoegd.';
        }
?>
        </div>
    </div>
    
    <div id="profiel_center"> 
        <div id="links_container">
            <div id="links_headers" class="profiel_header">
                <p class="info_title">Deze links vindt <?php echo $profiel_naam ?> leuk:</p>
                
                <?php
                /* 
                if($aantal_links > 0){
                <a href="'.$etc_root.'links/gebruiker=<?php echo $profiel_naam ?>">Bekijk alle links van <?php echo $profiel_naam ?></a>      
                 }
                 */ ?>
                 
            </div>
            <div class="wrapper_profiel_info">
            
            <?php
            $what = 'b.link, b.omschrijving'; 
            $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
            $where = 'a.gebruiker = '.$profiel_id.' AND a.top_positie > 0 ORDER BY a.top_positie ASC LIMIT 10';
                $aantal_top_links = countRows($what, $from, $where); 
            if($aantal_top_links > 0){
               $links = sqlSelect($what, $from, $where);
                while($link = mysql_fetch_array($links)){?>
                    <div class="link">
                        <div class="link_url">
                            <a href="<?php echo $link['link']; ?>" target="_blank" title="<?php echo $link['omschrijving']; ?>"><?php echo $link['omschrijving']; ?></a>
                        </div>
                    </div>
            <?php }
            if($aantal_top_links < 10){
                $what = 'b.link, b.omschrijving'; 
                $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
                $where = 'a.gebruiker = '.$profiel_id.' AND a.top_positie = 0 ORDER BY a.top_positie ASC LIMIT '.(10 - $aantal_top_links);
                    $aantal_links = countRows($what,$from,$where);
                    if($aantal_links > 0){
                        $links = sqlSelect($what,$from,$where);
                        while($link = mysql_fetch_array($links)){?>
                        <div class="link">
                            <div class="link_url">
                                <a href="<?php echo $link['link']; ?>" target="_blank" title="<?php echo $link['omschrijving']; ?>"><?php echo $link['omschrijving']; ?></a>
                            </div>
                        </div>
            <?php       }
                    }
            }
        }else{ echo $profiel_naam.' heeft nog geen links toegevoegd.'; }?>
            </div>
        </div>
        <div id="status_container">

        <div id="nieuwe_status">
            <div id="status_invoer_headers" class="profiel_header">
               <?php if($profiel_id == $login_id){?>
               <p class="info_title">Wat wil je kwijt ?</p>
               <?php }else{?>
               <p class="info_title">Wat wil je achterlaten voor <?php echo $profiel_naam; ?> ?</p>
               <?php } ?>
            </div>
            <div class="wrapper_profiel_info">
                <label for="status">Wat ben je aan het doen? Wat houdt je bezig? Wat wil je vertellen? Wat maakt je blij?</label>
                <textarea id="status_schrijven" name="status" cols="50" rows="4" class="textarea status_schrijven_dicht" onfocus="this.className='status_schrijven_open textarea'" onblur="this.className='status_schrijven_dicht textarea'" ></textarea>
                <button id="status_opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="statusAction(<?php echo $profiel_id ?>, 'create', 0)">Opslaan</button>
            </div>
        </div>
        <div id="statussen">
        <?php
            $what = 'a.id, a.gebruiker, a.inhoud, UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op,
                     b.gebruikersnaam, b.voornaam, b.achternaam, b.profielfoto';
            $from = 'portal_status a 
                     LEFT JOIN portal_gebruiker AS b ON (b.id = a.geplaatst_door)
                     LEFT JOIN portal_image AS c ON (c.id = b.profielfoto)';
            $where = 'a.gebruiker = '.$profiel_id.' AND a.actief = 1 AND b.actief = 1 ORDER BY a.geschreven_op DESC';
            //echo "SELECT $what FROM $from WHERE $where";
            $count_statussen = countRows($what, $from, $where);
        ?>
        <div id="<?php if($count_statussen > 0){ echo  'status_lijst_headers';} ?>" class="profiel_header">
                <p class="info_title">Prikbord van <?php echo $profiel_naam;  ?></p>
        </div>
        <div id="waiting" style="display:none;">
            <span id="waiting_text">De lijst wordt geladen</span>
            <span id="waiting_img"><img src="<?php echo $etc_root; ?>functions/<?php echo $functie_get; ?>/css/images/ajax_loader.gif" alt="loading" />&nbsp;</span>
        </div>
        <div class="wrapper_profiel_info" id="statussen_lijst">
        <?php
        /*
        if($count_statussen > 0){
        $statussen = sqlSelect($what, $from, $where);
        
        while($status = mysql_fetch_array($statussen)){
            //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
            $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$status['profielfoto'].' AND actief = 1';
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            if($profielfoto['path'] == null){
                $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(200,150).''.$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
            }else{
                $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(200,150).''.$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
            }?>
        <div class="status" onmouseover="this.className='status status_hover'" onmouseout="this.className='status'">
            <div class="status_left">
                <a href="<?php echo $etc_root; ?>profiel/<?php echo $profiel['gebruikersnaam'] ?>" title="bekijk het profiel">
                    <?php echo $profiel_foto; ?>
                </a>
            </div>
            <div class="status_right">
                <div class="status_name">
                    <a href="<?php echo $etc_root; ?>profiel/<?php echo $status['gebruikersnaam'] ?>" title="bekijk het profiel">
                       <h3><?php echo $status['voornaam'].' '.$status['achternaam']; ?></h3>
                    </a>
                    <?php if($status['gebruiker'] == $login_id || $_SESSION['admin'] == 1){ ?>
                    <div class="delete_status">
                        <div class="delete_container">
                            <img class="delete" onclick="statusAction(<?php echo $profiel_id; ?> , 'delete_status', <?php echo $status['id']; ?>)" 
                            src="<?php echo $etc_root; ?>functions/profiel/css/images/delete.png" alt="X" title="Verwijder status" />
                        </div>
                    </div>
                    <?php } ?>
                    <div class="status_datum">
                        <?php echo verstrekenTijd($status['geschreven_op']); ?>
                    </div>
                </div>
                <div class="status_content"><?php echo $status['inhoud'] ?></div>
            </div>
	 <?php 
     $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
              c.gebruikersnaam, c.voornaam, c.achternaam, c.profielfoto';
     $from = 'portal_status_reactie a
              LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
              LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)
              LEFT JOIN portal_image AS d ON (d.id = c.profielfoto)';
     $where = 'a.status ='.$status['id'].' AND b.actief = 1 AND c.actief = 1 AND d.actief = 1 ORDER BY geschreven_op ASC';
     //echo "SELECT $what FROM $from WHERE $where";
     $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);?>
        <div id="reacties_<?php echo $status['id'] ?>">
            <div id="reactielijst_<?php echo $status['id'] ?>" class="reactielijst">
                  <?php while($reactie = mysql_fetch_array($reactie_result)){
                        if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}
                        //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
                        $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
                            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
                        if($profielfoto['path'] == null){
                            $profiel_foto = '<img src="<?php echo $etc_root; ?>lib/slir/'.slirImage(200,150).'<?php echo $etc_root; ?>files/album/profile-empty.jpg" alt="profielfoto" />';
                        }else{
                            $profiel_foto = '<img src="<?php echo $etc_root; ?>lib/slir/'.slirImage(200,150).'<?php echo $etc_root; ?>files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
                        }
                        ?>
                 <div class="reactie <?php echo $class ?>" onmouseover="this.className='reactie reactie_hover <?php echo $class ?> '" onmouseout="this.className='reactie <?php echo $class ?>'">
                      <div class="reactie_links">
                        <div class="reactie_foto">
                            <div class="reactie_profiel_pic">
                                <a href="<?php echo $etc_root; ?>profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
                                    <?php echo $profiel_foto; ?>
                                </a>
                                <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reactie_rechts">
                                    <div class="reactie_header">
                                        <p>
                                            <span class="auteur">
                                                <a href="<?php echo $etc_root; ?>profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel">
                                                    <?php echo $reactie['voornaam'].' '.$reactie['achternaam'] ?>
                                                </a>
                                            </span>
                                            <?php if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                                            <span class="delete_reactie">
                                                <span class="delete_container">
                                                    <img class="delete" onclick="reactieForm(<?php echo $status['id'] ?>, 'delete_reactie', <?php echo $reactie['id'] ?>)" 
                                                         src="<?php echo $etc_root; ?>functions/profiel/css/images/delete.png" alt="X" title="Verwijder reactie" />
                                                </span>
                                            </span>
                                            
                                            <?php } ?>
                                            <span class="datum"><?php echo verstrekenTijd($reactie['geschreven_op']) ?></span>
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst"><?php echo $reactie['inhoud'] ?></div>           
                                </div>
                           </div>
                         <?php }?>
                        </div>
                        </div>
                        <div class="status_reageren" id="reageren_<?php echo $status['id']; ?>">
                            
                            <textarea class="textarea reageren_dicht" 
                             onfocus="this.className='reageren_open textarea'" onblur="this.className='reageren_dicht textarea'" id="reactie_<?php echo $status['id'] ?>" cols="50" rows="3"></textarea>
                             <span class="status_reactie_profilepic"> <?php echo $gebruiker_profielfoto ?></span>
                             <div class="reageren_button_container">
                                <button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="reactieForm(<?php echo $status['id'].', \'create_reactie\', 0'; ?>)">Reageer</button>
                             </div>
                        </div>
                    </div>
		<?php }
        }else{
            echo $profiel_naam.' heeft nog geen status(sen) geschreven.';
        } */?>
        <script>$(function(){leesVerderStatus('<?php echo $profiel_id ?>', 1);})</script>
        </div>
        <div id="overige_nieuws_meer_lezen"><span class="next_batch" onclick="leesVerderStatus(2)">Meer laden...</span></div>
        </div>
        </div>
    </div> 
    <div id="profiel_right">
        <div id="fotos">
            <div id="foto_headers" class="profiel_header"> 
                <p class="info_title">Foto's  van <?php echo $profiel_naam ?></p>
                <?php /*<a href="<?php echo $etc_root; ?>fotos/album=<?php echo $profiel['foto_album'] ?>">Bekijk alle foto's van <?php echo $profiel_naam ?></a>*/ ?>
            </div>
            <div class="wrapper_profiel_info">
	            <?php
	            if($profiel['foto_album'] != null){
	                $what = 'id, path, omschrijving'; $from = 'portal_image'; $where='album = '.$profiel['foto_album'].' AND actief = 1 ORDER BY volgorde ASC';
	                $count_fotos = countRows($what, $from, $where); $fotos_result = sqlSelect($what, $from, $where);
	                 if($count_fotos > 0){
	                    $i = 0;                        
	                    while($foto = mysql_fetch_array($fotos_result)){
	                        if($i < 8){
	                        echo '
	                        <a class="iframe" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'" title="'.$foto['omschrijving'].'">
	                            <img src="'.$etc_root.'lib/slir/'.slirImage(100,90).$etc_root.'files/album/'.$profiel['foto_album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'" />
	                        </a>';
	                        $i++;
	                        }else{
	                        echo '
	                        <a class="iframe" style="display:none;" rel="images" href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$foto['id'].'" title="'.$foto['omschrijving'].'">
	                            <img src="'.$etc_root.'lib/slir/'.slirImage(100,90).$etc_root.'files/album/'.$profiel['foto_album'].'/'.$foto['path'].'" alt="'.$foto['omschrijving'].'" />
	                            
	                        </a>';    
	                        }
	                    }
	                 }else{
	                      echo $profiel_naam.' heeft nog geen foto\'s ge&uuml;pload.';  
	                 }
	            }else{
	                echo $profiel_naam.' heeft nog geen foto\'s ge&uuml;pload.';
	            }
	            ?>
            </div>
        </div>
        <div id="recente_activiteit">
            <div id="recente_activiteit_headers" class="profiel_header"> 
                <p class="info_title">Recente activiteit van <?php echo $profiel_naam ?></p>
            </div>
            <div class="wrapper_profiel_info">
	            <?php
	            //zijn er recente activiteiten qua reacties ?
	            $what = 'id, inhoud, UNIX_TIMESTAMP(geschreven_op) AS geschreven_op';
	            $from = 'portal_reactie';
	            $where = 'geschreven_door = '.$profiel_id.' ORDER BY geschreven_op DESC LIMIT 5';
	                //echo "SELECT $what FROM $from WHERE $where".'<br />';
	                $aantal_reacties = countRows($what, $from, $where);
	            if($aantal_reacties > 0){
	                $recente_reacties = sqlSelect($what, $from, $where);
	                while($recente_reactie = mysql_fetch_array($recente_reacties)){
	                    $what = 'a.nieuws AS id, b.titel AS titel, \'nieuws\' AS soort'; 
	                    $from = 'portal_nieuws_reactie a LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)';
	                    $where = 'a.reactie = '.$recente_reactie['id'].'
	                              UNION
	                                SELECT 
	                                    a.image AS id, 
	                                    \'\' AS titel, 
	                                    \'foto\' AS soort 
	                                FROM 
	                                    portal_image_reactie a 
	                                    LEFT JOIN portal_image AS b ON (b.id = a.image)
	                                WHERE
	                                    a.reactie = '.$recente_reactie['id'].'
	                              UNION
	                                SELECT
	                                    a.status AS id,
	                                    \'\' AS titel,
	                                    \'status\' AS soort 
	                                FROM
	                                    portal_status_reactie a
	                                    LEFT JOIN portal_status AS b ON (b.id = a.status)
	                                WHERE
	                                    a.reactie = '.$recente_reactie['id'];
	                                    //echo "SELECT $what FROM $from WHERE $where";
	                    $soort = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	                    
	                    if($soort['soort'] == 'nieuws'){
	                        
	                        $recente_activiteit[$recente_reactie['geschreven_op']] = '
	                        <div class="recente_reactie">
	                            <div class="recente_reactie_link">
	                                <a href="'.$etc_root.'home/nieuws='.$soort['id'].'">
	                                    '.$profiel_naam.' heeft een reactie achtergelaten bij nieuwsitem '."'".$soort['titel']."'".'.
	                                </a>
	                            </div>
	                            <div class="recente_reactie_datum">
	                                '.verstrekenTijd($recente_reactie['geschreven_op']).'
	                            </div>
	                        </div>';
	                    }
	                    if($soort['soort'] == 'foto'){
	                        
	                        $recente_activiteit[$recente_reactie['geschreven_op']] = '
	                        <div class="recente_reactie">
	                            <div class="recente_reactie_link">
	                                <a href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$soort['id'].'">
	                                    '.$profiel_naam.' heeft een reactie achtergelaten bij een foto.
	                                </a>
	                            </div>
	                            <div class="recente_reactie_datum">
	                                '.verstrekenTijd($recente_reactie['geschreven_op']).'
	                            </div>
	                        </div>';
	                        
	                    }
	                    if($soort['soort'] == 'status'){
	                        $status_what = '
                            a.geplaatst_door, a.gebruiker,
                            b.voornaam AS geplaatst_voornaam, b.achternaam AS geplaatst_achternaam, b.gebruikersnaam AS geplaatst_profielnaam,
                            c.voornaam AS gebruiker_voornaam, c.achternaam AS gebruiker_achternaam, c.gebruikersnaam AS gebruiker_profielnaam';
	                        $status_from = 'portal_status a 
                                            LEFT JOIN portal_gebruiker AS b ON (b.id = a.geplaatst_door)
                                            LEFT JOIN portal_gebruiker AS c ON (c.id = a.gebruiker)'; 
                            $status_where='a.id = '.$soort['id'].' AND a.actief = 1 AND b.actief = 1';
	                            $gereageerd_op = mysql_fetch_assoc(sqlSelect($status_what, $status_from, $status_where));
                            if($gereageerd_op['geplaatst_door'] == $profiel_id){
                                if($gereageerd_op['geplaatst_door'] == $gereageerd_op['gebruiker']){
                                    $status_melding = $profiel_naam.' heeft een reactie achtergelaten bij zijn status';
                                }else{
                                    $status_melding = $profiel_naam.' heeft een reactie achtergelaten op zijn bericht bij het profiel van <a href="'.$etc_root.'profiel/'.$gereageerd_op['gebruiker_profielnaam'].'">'.$gereageerd_op['gebruiker_voornaam'].' '.$gereageerd_op['gebruiker_achternaam'].'</a>';
                                }                         
                            }elseif($gereageerd_op['gebruiker'] == $profiel_id){
                                $status_melding = $profiel_naam.' heeft een reactie achtergelaten op een bericht van <a href="'.$etc_root.'profiel/'.$gereageerd_op['geplaatst_profielnaam'].'">'.$gereageerd_op['geplaatst_voornaam'].' '.$gereageerd_op['geplaatst_achternaam'].'</a>, op zijn profiel';
                            }else{
                                if($gereageerd_op['geplaatst_door'] == $gereageerd_op['gebruiker']){
                                $status_melding = $profiel_naam.' heeft een reactie achtergelaten op op een bericht van <a href="'.$etc_root.'profiel/'.$gereageerd_op['geplaatst_profielnaam'].'">'.$gereageerd_op['geplaatst_voornaam'].' '.$gereageerd_op['geplaatst_achternaam'].'</a>, op zijn profiel';
                                }else{
                                $status_melding = $profiel_naam.' heeft een reactie achtergelaten op op een bericht van <a href="'.$etc_root.'profiel/'.$gereageerd_op['geplaatst_profielnaam'].'">'.$gereageerd_op['geplaatst_voornaam'].' '.$gereageerd_op['geplaatst_achternaam'].'</a>, op het profiel van <a href="'.$etc_root.'profiel/'.$gereageerd_op['gebruiker_profielnaam'].'">'.$gereageerd_op['gebruiker_voornaam'].' '.$gereageerd_op['gebruiker_achternaam'].'</a>';
                                }
                            }
	                        $recente_activiteit[$recente_reactie['geschreven_op']] = '
	                        <div class="recente_reactie">
	                            <div class="recente_reactie_link">'.$status_melding.'</div>
	                            <div class="recente_reactie_datum">
	                                '.verstrekenTijd($recente_reactie['geschreven_op']).'
	                            </div>
	                        </div>';
	                    }
	                  }
	                }
	            
	            if($profiel['foto_album'] != null){
	            //zijn er recente activiteiten qua foto's ?
	            $what = 'id, UNIX_TIMESTAMP(geupload_op) AS geupload_op';
	            $from = 'portal_image';
	            $where = 'album = '.$profiel['foto_album'].' AND geupload_door = '.$profiel_id.' ORDER BY geupload_op DESC LIMIT 5';
	                $aantal_fotos = countRows($what, $from, $where); 
	                 //echo "SELECT $what FROM $from WHERE $where".'<br />';
	            if($aantal_fotos > 0){
	                $recente_fotos = sqlSelect($what, $from, $where);
	                while($recente_foto = mysql_fetch_array($recente_fotos)){
	                $recente_activiteit[$recente_foto['geupload_op']] = '
	                        <div class="recente_reactie">
	                            <div class="recente_reactie_link">
	                                <a href="'.$etc_root.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$recente_foto['id'].'">
	                                    '.$profiel_naam.' heeft een foto ge&uuml;pload.
	                                </a>
	                            </div>
	                            <div class="recente_reactie_datum">
	                                '.verstrekenTijd($recente_foto['geupload_op']).'
	                            </div>
	                        </div>';
	                }
	            }
	            }
	            //zijn er recente activiteiten qua links ?
	            $what = 'b.link, b.omschrijving, UNIX_TIMESTAMP(b.toegevoegd_op) AS toegevoegd_op ';
	            $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
	            $where = 'a.gebruiker = '.$profiel_id.' AND b.toegevoegd_door = '.$profiel_id.' AND b.actief = 1 ORDER BY b.toegevoegd_op DESC LIMIT 5';
	                $aantal_links = countRows($what, $from, $where);
	                 //echo "SELECT $what FROM $from WHERE $where".'<br />';
	            if($aantal_links > 0){
	                $recente_links = sqlSelect($what, $from, $where);
	                while($recente_link = mysql_fetch_array($recente_links)){
	                $recente_activiteit[$recente_link['toegevoegd_op']] = '
	                        <div class="recente_reactie">
	                            <div class="recente_reactie_link">
	                                <a href="'.$recente_link['link'].'">
	                                    '.$profiel_naam.' heeft een link toegevoegd: '."'".$recente_link['omschrijving']."'".'.
	                                </a>
	                            </div>
	                            <div class="recente_reactie_datum">
	                                '.verstrekenTijd($recente_link['toegevoegd_op']).'
	                            </div>
	                        </div>';
	                }   
	            }
                
                //zijn er recente activiteiten qua statussen (bij eigen persoon of ander iemand) ?
                $what = 'UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op,
                         b.id AS bij_id, b.voornaam AS bij_voornaam, b.achternaam AS bij_achternaam, b.gebruikersnaam AS bij_gebruikersnaam,
                         c.id AS door_id, c.voornaam AS door_voornaam, c.achternaam AS door_achternaam, c.gebruikersnaam AS door_gebruikersnaam';
                $from = 'portal_status a LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker) LEFT JOIN portal_gebruiker AS c ON (c.id = a.geplaatst_door)';
                $where = 'a.geplaatst_door = '.$profiel_id.' AND a.actief = 1 ORDER BY a.geschreven_op DESC LIMIT 5';
                    $aantal_statussen = countRows($what, $from, $where);
                     //echo "SELECT $what FROM $from WHERE $where".'<br />';
                if($aantal_statussen > 0){
                    $recente_statussen = sqlSelect($what, $from, $where);
                    while($recente_status = mysql_fetch_array($recente_statussen)){
                    $recente_activiteit[$recente_status['geschreven_op']] = '
                            <div class="recente_status">
                                <div class="recente_status_link">
                                    <a href="'.$etc_root.'profiel/'.$recente_status['bij_gebruikersnaam'].'">';
                                    if($recente_status['bij_id'] == $recente_status['door_id']){
                    $recente_activiteit[$recente_status['geschreven_op']] .= $profiel_naam.' heeft een nieuwe status geschreven';
                                    }else{
                    $recente_activiteit[$recente_status['geschreven_op']] .= $profiel_naam.' heeft een bericht achtergelaten bij '.$recente_status['bij_voornaam'].' '.$recente_status['bij_achternaam'];
                                    }
                   $recente_activiteit[$recente_status['geschreven_op']] .= '
                                    </a>
                                </div>
                                <div class="recente_status_datum">
                                    '.verstrekenTijd($recente_status['geschreven_op']).'
                                </div>
                            </div>';
                    }   
                }
                
                //wanneer zijn de gegevens gewijzigd ?
                $what = 'wat, UNIX_TIMESTAMP(gewijzigd_op) AS gewijzigd_op'; $from = 'portal_gebruiker_recent'; $where = 'gebruiker = '.$profiel_id;
                    $aantal_recente_wijzigingen = countRows($what, $from, $where);
                    //echo "SELECT $what FROM $from WHERE $where".'<br />';
                if($aantal_recente_wijzigingen > 0){
                    $recente_wijzigingen = sqlSelect($what, $from, $where);
                    
                    while($recente_wijziging = mysql_fetch_array($recente_wijzigingen)){
                    if($recente_wijziging['wat'] == 'persoonlijk'){$wat = 'zijn/haar persoonlijke informatie';}
                    elseif($recente_wijziging['wat'] == 'social'){$wat = 'zijn/haar sociale informatie';}
                    elseif($recente_wijziging['wat'] == 'profielfoto'){$wat = 'zijn/haar profielfoto';}
                    elseif($recente_wijziging['wat'] == 'kennis'){$wat = 'zijn/haar kennis gebieden';}
                    elseif($recente_wijziging['wat'] == 'goedin'){$wat = 'waar hij/zij goed in is';}
                    else{$wat = 'iets';}
                    //elseif($recente_wijziging == 'persoonlijk'){$wat = 'persoonlijke informatie';}
                    //echo $recente_wijziging['gewijzigd_op'].'<br />';
                    $recente_activiteit[$recente_wijziging['gewijzigd_op']] = '
                            <div class="recente_wijziging">
                                <div class="recente_wijziging_link">'.$profiel_naam.' heeft '.$wat.' gewijzigd</div>
                                <div class="recente_wijziging_datum">
                                    '.verstrekenTijd($recente_wijziging['gewijzigd_op']).'
                                </div>
                            </div>';
                    }
                }
                
            //wanneer is deze gebruiker voor het laatst ingelogd ?
            $what = 'MAX(UNIX_TIMESTAMP(login_tijd)) AS login_tijd'; $from= 'portal_inlog'; $where= 'gebruiker_id = '.$profiel_id;
                $aantal_recente_logins = countRows($what, $from, $where);
                //echo "SELECT $what FROM $from WHERE $where";
            if($aantal_recente_logins > 0){
                
                $recente_login = mysql_fetch_assoc(sqlSelect($what, $from, $where));
               //echo $recente_login['login_tijd'];
                $recente_activiteit[$recente_login['login_tijd']] = '
                            <div class="recente_wijziging">
                                <div class="recente_wijziging_link">'.$profiel_naam.' is ingelogd</a>
                                </div>
                                <div class="recente_wijziging_datum">
                                    '.verstrekenTijd($recente_login['login_tijd']).'
                                </div>
                            </div>';
            }
	            //echo count($recente_activiteit);
	                if(count($recente_activiteit) > 0){
	                    krsort($recente_activiteit);
	                    $i = 0;
	                    foreach($recente_activiteit as $activiteit){
	                        if($i < 5){
	                            echo $activiteit;
	                        }else{}
	                        $i++;
	                    }
	                    echo '<p>&nbsp;</p>';
	                }else{
	                    echo '<p>'.$profiel_naam.' is nog niet actief geweest.</p>';
	                }
	            ?>
	        </div>
	    </div>
	</div>

</div>