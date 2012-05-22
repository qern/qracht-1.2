<?php
    include($_SERVER['DOCUMENT_ROOT'].'/portal/check_configuration.php');
    
    //haal de foto  + omschrijving op
    $what = 'a.id, a.path, a.omschrijving, a.volgorde, DATE_FORMAT(a.geupload_op, \'%W %d %M %Y om %H:%i\') AS geupload_op,
             b.id AS album, b.naam, COUNT(c.id) AS aantal, 
             d.voornaam, d.achternaam, d.gebruikersnaam';
    $from = 'portal_image a 
             LEFT JOIN portal_album AS b ON (b.id = a.album) 
             LEFT JOIN portal_image AS c ON (c.album = b.id)
             LEFT JOIN portal_gebruiker AS d ON (d.id = a.geupload_door)';
    $where = 'a.id ='.$_GET['image_id'].' AND b.actief = 1 AND c.actief = 1 AND d.actief = 1'; 
        $foto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
    //is het geüpload bij een profiel of een nieuwsitem ?
        $what = 'a.nieuws AS id, b.titel AS titel, \'nieuws\' AS soort'; 
        $from = 'portal_nieuws_album a LEFT JOIN portal_nieuws AS b ON (b.id = a.nieuws)';
        $where = 'a.album = '.$foto['album'].'
        UNION
        SELECT
            gebruiker AS id,
            \'\' AS titel,
            \'profiel\' AS soort
        FROM 
            portal_gebruiker_album
        WHERE
           album = '.$foto['album'];
        
        $soort = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        //echo "SELECT $what FROM $from WHERE $where";
    /*
    //haal het id van de volgende en de vorige foto op (indien aanwezig);
    if($foto['volgorde'] == 1 && $foto['volgorde'] < $foto['aantal']){
        $what = 'id AS volgende'; $from = 'portal_image'; $where='volgorde = ('.$foto['volgorde'].' + 1) AND actief = 1';
        $volgende = 1;
        
        $nav = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    }
    elseif($foto['volgorde'] > 1 && $foto['volgorde'] < $foto['aantal']){
        $what = 'a.id AS volgende, b.id AS vorige'; $from = 'portal_image a, portal_image b'; 
        $where='a.volgorde = ('.$foto['volgorde'].' + 1) AND a.actief = 1 AND b.volgorde = ('.$foto['volgorde'].' - 1) AND b.actief = 1';
        $volgende = 1; $vorige = 1;
        
        $nav = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    }elseif($foto['volgorde'] == $foto['aantal'] && $foto['aantal'] > 1){
        $what = 'id AS vorige'; $from = 'portal_image'; $where='volgorde = ('.$foto['volgorde'].' - 1) AND actief = 1';
        $vorige = 1;
        
        $nav = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    }else{}
    */
    //haal alle reacties op
    $what = 'b.id, b.inhoud, b.geschreven_door, DATE_FORMAT(b.geschreven_op, \'%W %d %M %Y om %H:%i\') AS geschreven_op'; 
    $from = 'portal_image_reactie a LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)'; $where = 'a.image = '.$foto['id'].' AND b.actief = 1 ORDER BY b.geschreven_op DESC';
        $count_reacties = countRows($what, $from, $where); $reacties_result = sqlSelect($what, $from, $where);
        
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $site_name; ?>css/standaard_css/main.css">  
    <link rel="stylesheet" type="text/css" href="<?php echo $site_name; ?>functions/afbeeldingen/css/afbeeldingen_tonen.css">  
</head>
<body>
<div id="foto_div">
    <div id="foto_div_header">    
        <div id="foto_div_navigatie">
        <?php /* if($vorige != null){?>
            <a id="vorige_foto" href="#" onclick="window.location.replace('<?php echo $site_name.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$nav['vorige']; ?>')"> &laquo; </a>       
        <?php } ?>
        if($_GET['refer'] == null){?>
            <div id="navigatie_tekst"><p>Foto <span id="huidige_foto"><?php echo $foto['volgorde']; ?></span> van <span id="totaal_fotos"><?php echo $foto['aantal']; ?></span></p></div> 
        <?php } 
         if($volgende != null){?>
            <a id="volgende_foto" href="#" onclick="window.location.replace('<?php echo $site_name.'functions/afbeeldingen/includes/afbeelding_tonen.php?image_id='.$nav['volgende']; ?>')"> &raquo; </a>        
    <?php } 
        */?>
        </div>
    </div>
    <div id="foto_div_foto">
    <?php 
        list($width, $height) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/portal/files/album/'.$foto['album'].'/'.$foto['path']);
        if($height > 400){
            $height = 400;
        }else{}
        if($width > $height){
            
        $image_span =  '
            <span id="foto_image" style="background: url(\'/portal/lib/slir/'.slirImage(600,400).'/portal/files/album/'.$foto['album'].'/'.$foto['path'].'\') no-repeat center center transparent; height:'.$height.'px;">
                &nbsp;
            </span>';
            
        }elseif($width == $height){
                
        $image_span =  '
            <span id="foto_image" style="background: url(/portal/lib/slir/'.slirImage(400,400).'/portal/files/album/'.$foto['album'].'/'.$foto['path'].') no-repeat center center transparent; height:'.$height.'px;">
                &nbsp;
            </span>';
            
        }else{
                
        $image_span =  '
            <span id="foto_image" style="background: url(/portal/lib/slir/'.slirImage(0,400,0).'/portal/files/album/'.$foto['album'].'/'.$foto['path'].') no-repeat center center transparent; height:'.$height.'px;">
                &nbsp;
            </span>';   
            
        }
        echo $image_span;    
        ?>
    </div>
    <p id="foto_credits">
        <?php
            if($soort['soort'] == 'profiel'){?>
            Ge&uuml;pload door <span id="profiel"><?php echo $foto['voornaam'].' '.$foto['achternaam']; ?></span> op <span id="geupload"><?php echo $foto['geupload_op']; ?></span>
        <?php }
            elseif($soort['soort'] == 'nieuws'){?>
            Op <span id="geupload"><?php echo $foto['geupload_op']; ?></span> ge&uuml;pload binnen het nieuwsitem <span id="nieuwsitem">'<?php echo $soort['titel']; ?>'</span> 
        <?php }
        ?>
        </p>
    <div id="foto_div_acties">
        <div id="foto_div_reacties">
            <div id="reageren">
        <h2>
            Reacties <?php if($count_reacties > 0){ echo "($count_reacties)";} ?> 
            <a href="javascript:toggle_form();" id="toon_formulier" title="reactie toevoegen">Reactie toevoegen</a>
        </h2>
        <div id="formulier" style="display: none">
                <p>Voeg een reactie toe:</p>
                <form id="reactie_formulier_formulier" method="post" action="/portal/functions/afbeeldingen/includes/afbeelding_check.php">
                    <textarea class="textarea" name="reactie" id="reactie" cols="95" rows="10"></textarea>
                    <input type="hidden" name="image_id" value="<?php echo $foto['id']; ?>" />
                    <input type="hidden" name="action" value="reactie" />
                    <input type="submit" value="opslaan" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'" id="opslaan_reactie" />
                </form>
            </div>
        <div id="reactie_lijst">
        <?php
            if($count_reacties > 0){
                                            
                    while($reactie = mysql_fetch_array($reacties_result)){
                        //haal de schrijver + foto van schrijver op
                        $what = 'a.voornaam, a.achternaam, a.gebruikersnaam, b.album,  b.path AS profielfoto';
                        $from = 'portal_gebruiker a LEFT JOIN portal_image AS b on (b.id = a.profielfoto)';
                        $where = 'a.id = '.$reactie['geschreven_door'].' AND b.actief = 1';
                            $reactie_user = mysql_fetch_assoc(sqlSelect($what, $from, $where));

                        
                        if($class_i == 0){$class="om"; $class_i = 1;}else{$class="en_om"; $class_i = 0;}?>
            <div class="reactie <?php echo $class; ?>" 
                 onmouseover="this.className='reactie reactie_hover <?php echo $class; ?>'" onmouseout="this.className='reactie <?php echo $class; ?>'">
               <div class="reactie_links">
                <div class="reactie_foto">
                  <div class="reactie_profiel_pic">
                    <a href="/profiel/<?php echo $reactie_user['gebruikersnaam']; ?>" title="bekijk het profiel">
                        <img src="/portal/lib/slir/<?php echo slirImage(80,80); ?>/portal/files/album/<?php echo $reactie_user['album'].'/'.$reactie_user['profielfoto']; ?>" 
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
                                <a href="/profiel/<?php echo $reactie_user['gebruikersnaam']; ?>" title="bekijk het profiel">
                                    <?php echo $reactie_user['voornaam'].' '.$reactie_user['achternaam']; ?>
                                </a>
                            </span>
                            <span class="datum"><?php echo $reactie['geschreven_op']; ?></span>                    
                            <?php
                            if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                            <a class="delete" href="/portal/functions/afbeeldingen/includes/afbeelding_check.php?action=delete_reactie&reactie_id=<?php echo $reactie['id']; ?>&image_id=<?php echo $foto['id'] ?>" title="Verwijder reactie">
                                <img src="/portal/functions/home/css/images/delete.png" alt="X" title="Verwijder reactie"> 
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
                    echo 'Er zijn geen reactie\'s bij dit item';
                }
        ?>
        </div>
    </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function toggle_form(){var ele = document.getElementById('formulier');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
</script>
</body>
</html>

