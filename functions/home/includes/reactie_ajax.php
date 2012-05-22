<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
   	
    //het id (van nieuws/foto/status) en het feitelijke doel
    //ook de reactie. Bij toevoegen een string, bij verwijderen een int.
    $id = $_POST['id']; $target = $_POST['doel']; $reactie = $_POST['reactie'];
    
    if($_POST['action'] == 'toevoegen'){
 
        //we voegen sowieso een reactie toe, dus doe dit eerst
        if($_POST['doel'] != null){
            $find = array('\"', "\\'"); $replace = array('"', '\'');
            $reactie = str_ireplace($find, $replace, $_POST['reactie']);
            $reactie = htmlentities($reactie, ENT_NOQUOTES, "UTF-8");
            $reactie = nl2br($reactie);
            
            $table = 'portal_reactie'; $what = 'inhoud, geschreven_door, geschreven_op';
            $with_what = "'".mysql_real_escape_string($reactie)."', $login_id, NOW()";
                $nieuwe_reactie = sqlInsert($table, $what, $with_what);
			
			$what = 'MAX(id) AS id'; $from = 'portal_reactie'; $where = '1';
				$reactie = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $reactie_id = $reactie['id'];
        }
    	
        //koppel deze daarna met nieuws of foto
        if($_POST['doel'] == 'nieuws' || $target = 'belangrijk_nieuws'){
            $table = 'portal_nieuws_reactie'; $what = 'nieuws, reactie';
            $with_what = "$id, $reactie_id";
            $koppel_nieuwe_reactie = sqlInsert($table, $what, $with_what);
            
            $table ="portal_nieuws"; $what = 'update_datum = NOW()'; $where = "id  = $id";
                $update_nieuws = sqlUpdate($table, $what, $where);
        }
        if($_POST['doel'] == 'foto'){
        	
            $table = 'portal_image_reactie'; $what = 'image, reactie';
            $with_what = "$id, $reactie_id";
            $koppel_nieuwe_reactie = sqlInsert($table, $what, $with_what);
            
            $table ="portal_image"; $what = 'update_datum = NOW()'; $where = "id  = $id";
                $update_nieuws = sqlUpdate($table, $what, $where);
        }
        if($_POST['doel'] == 'status'){
            $table = 'portal_status_reactie'; $what = 'status, reactie';
            $with_what = "$id, $reactie_id";
                $koppel_nieuwe_reactie = sqlInsert($table, $what, $with_what);
                
            $table ="portal_status"; $what = 'update_datum = NOW()'; $where = "id  = $id";
                $update_nieuws = sqlUpdate($table, $what, $where);
        }
    }
    elseif($_POST['action'] == 'verwijderen'){
           
        $table = 'portal_reactie'; $where = "id = $reactie";
            $verwijder_reactie = sqlDelete($table, $where);
            
        if($_POST['doel'] == 'nieuws'){
            $table = 'portal_nieuws_reactie'; $where = "reactie = $reactie";
                $verwijder_reactie = sqlDelete($table, $where);
        }
        if($_POST['doel'] == 'foto' || $target == 'foto_iframe'){
            $table = 'portal_image_reactie'; $where = "reactie = $reactie";
                $verwijder_reactie = sqlDelete($table, $where);
        }        
        if($_POST['doel'] == 'status'){
            $table = 'portal_status_reactie'; $where = "reactie = $reactie";
                $verwijder_reactie = sqlDelete($table, $where);
        } 
    }
    
    if($_POST['doel'] == 'nieuws' || $_POST['doel'] = 'belangrijk_nieuws'){
        //herlaad de reacties in de lijst
        $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
        $from = 'portal_nieuws_reactie a
                 LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                 LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
        $where = 'a.nieuws ='.$id.' AND b.actief = 1 AND c.actief = 1 ORDER BY b.geschreven_op ASC';
        $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);?>
        <div id="nieuws_reactielijst_<?php echo $id ?>"> 
        <?php   
        while($reactie = mysql_fetch_array($reactie_result)){
            
        //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
        $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        if($profielfoto['path'] == null){
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(200,150).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
        }else{
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(200,150).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
        }
        if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}?>
            <div class="tijdlijn_reactie <?php echo $class ?>" onmouseover="this.className='tijdlijn_reactie reactie_hover <?php echo $class ?>'" onmouseout="this.className='tijdlijn_reactie <?php echo $class ?>'">
                <div class="reactie_links">
                    <div class="reactie_foto">
                        <div class="reactie_profiel_pic">
                            <a href="/profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
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
                            <a href="/profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
                                <?php echo $reactie['voornaam'].' '.$reactie['achternaam'] ?>
                            </a>
                        </span>
                        <span class="datum"><?php echo verstrekenTijd($reactie['geschreven_op']) ?></span>
                        <?php if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                        <img class="delete" src="<?php echo $etc_root; ?>functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie(<?php echo $id.', \''.$target.'\','.$reactie['id'] ?>)" />
                        <?php } ?>              
                      </p>
                    </div>                  
                    <div class="reactie_tekst"><?php echo $reactie['inhoud'] ?></div>           
                </div>
            </div>
            <?php }//einde while Loop ?>
        </div>
    <?php }
    if($_POST['doel'] == 'foto'){
        //herlaad de reacties in de lijst
        $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
        $from = 'portal_image_reactie a
                 LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                 LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
        $where = 'a.image ='.$id.' AND b.actief = 1 AND c.actief = 1 ORDER BY b.geschreven_op ASC';
        $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
        ?> 
        <div id="foto_reactielijst_<?php echo $id ?>">
        <?php   
        while($reactie = mysql_fetch_array($reactie_result)){
            echo "SELECT $what FROM $from WHERE $where"; 
        //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
        $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        if($profielfoto['path'] == null){
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
        }else{
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
        }
       // if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}?>
            <div class="tijdlijn_reactie <?php echo $class ?>" onmouseover="this.className='tijdlijn_reactie reactie_hover <?php echo $class ?>'" onmouseout="this.className='tijdlijn_reactie <?php echo $class ?>'">
                <div class="reactie_links">
                    <div class="reactie_foto">
                        <div class="reactie_profiel_pic">c
                            <a href="/profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
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
                            <a href="/profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
                                <?php echo $reactie['voornaam'].' '.$reactie['achternaam'] ?>
                            </a>
                        </span>
                        <span class="datum"><?php echo verstrekenTijd($reactie['geschreven_op']) ?></span>
                        <?php if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                        <img class="delete" src="<?php echo $etc_root; ?>functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie(<?php echo $id.', \'foto\','.$reactie['id'] ?>)" />
                        <?php } ?>              
                      </p>
                    </div>                  
                    <div class="reactie_tekst"><?php echo $reactie['inhoud'] ?></div>           
                </div>
            </div>
            <?php }//einde while Loop ?>
        </div>
    <?php }
    if($_POST['doel'] == 'status'){           
        //herlaad de reacties in de lijst
        $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
        $from = 'portal_status_reactie a
                 LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                 LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
        $where = 'a.status ='.$id.' AND b.actief = 1 AND c.actief = 1 ORDER BY b.geschreven_op ASC';
        $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);?>

        <div id="status_reactielijst_<?php echo $id ?>">
        <?php   
        while($reactie = mysql_fetch_array($reactie_result)){
            
        //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
        $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        if($profielfoto['path'] == null){
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
        }else{
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
        }
       // if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}?>
            <div class="tijdlijn_reactie <?php echo $class ?>" onmouseover="this.className='tijdlijn_reactie reactie_hover <?php echo $class ?>'" onmouseout="this.className='tijdlijn_reactie <?php echo $class ?>'">
                <div class="reactie_links">
                    <div class="reactie_foto">
                        <div class="reactie_profiel_pic">
                            <a href="/profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
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
                            <a href="/profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
                                <?php echo $reactie['voornaam'].' '.$reactie['achternaam'] ?>
                            </a>
                        </span>
                        <span class="datum"><?php echo verstrekenTijd($reactie['geschreven_op']) ?></span>
                        <?php if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                        <img class="delete" src="<?php echo $etc_root; ?>functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie(<?php echo $id.', \'status\','.$reactie['id'] ?>)" />
                        <?php } ?>              
                      </p>
                    </div>                  
                    <div class="reactie_tekst"><?php echo $reactie['inhoud'] ?></div>           
                </div>
            </div>
            <?php }//einde while Loop ?>
        </div>
    <?php } 
    if($_POST['doel'] == 'foto_iframe'){
        //herlaad de reacties in de lijst
        $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
                     c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
        $from = 'portal_image_reactie a
                 LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
                 LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
        $where = 'a.image ='.$id.' AND b.actief = 1 AND c.actief = 1';
        $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);?>

        <?php   
        if($reactie_aantal > 0){
        while($reactie = mysql_fetch_array($reactie_result)){
            
        //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
        $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
            $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
        if($profielfoto['path'] == null){
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
        }else{
            $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
        }
       // if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}?>
            <div class="reactie <?php echo $class; ?>" onmouseover="this.className='reactie reactie_hover <?php echo $class; ?>'" onmouseout="this.className='reactie <?php echo $class; ?>'">
               <div class="reactie_links">
                <div class="reactie_foto">
                  <div class="reactie_profiel_pic">
                    <a href="/profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel"><?php echo $image; ?></a>
                    <div class="reactie_profiel_pic_bottom">&nbsp;</div>
                  </div>
                </div>
                </div>
                <div class="reactie_rechts">
                    <div class="reactie_header">
                        <p>
                            <span class="auteur">
                                <a href="/profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel">
                                    <?php echo $reactie['voornaam'].' '.$reactie['achternaam']; ?>
                                </a>
                            </span>
                            <span class="datum"><?php echo $reactie['geschreven_op']; ?></span>                    
                            <?php
                            if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){?>
                            <img class="delete" src="<?php echo $etc_root; ?>functions/home/css/images/delete.png" alt="X" title="Verwijder reactie" onclick="deleteReactie(<?php echo $status['id'].', \'foto_iframe\','.$reactie['id'] ?>)" />
                            <?php }?>
                        </p>
                    </div>                  
                    <div class="reactie_tekst"><?php echo $reactie['inhoud'] ?></div>           
                </div>
            </div>
            <?php }//einde while Loop ?>
        </div>
    <?php }else{echo 'Er zijn geen reactie\'s bij deze foto';}
    }
 ?>