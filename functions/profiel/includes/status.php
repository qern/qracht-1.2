<?php 
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
$datum_interval = '10';
//$max_interval =  ceil((time() - 1322719200) / (60*60*24*30)); //je mag maar terug kijken tot 1 december 2011. Daarvoor, is er niets !
$max_interval =  ceil((time() - 1322719200) / (60*60*24*10)); //op basis van 10 dagen !!!!
if($_GET['interval'] > 1){$datum_interval = $datum_interval * $_GET['interval'];}
$profiel_id = $_REQUEST['gebruiker'];
//Deze php is bedoeld om met ajax te bepalen wat er in het nieuwsoverzicht getoond mag worden
if($_POST['action'] == 'create'){
    if($_POST['status'] != null){
        $find = array('\"', "\\'"); $replace = array('"', '\'');
        $status = str_ireplace($find, $replace, $_POST['status']);
        $status = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
        //$status = mysql_real_escape_string($status);
        $table="portal_status"; $what = 'inhoud, geschreven_op, geplaatst_door, gebruiker, laatste_wijziging, update_datum';  
        $with_what = "'".mysql_real_escape_string($status)."', NOW(), $login_id, ".$profiel_id.", NOW(), NOW()";
            $nieuwe_status = sqlInsert($table, $what, $with_what);
    }    
}
elseif($_POST['action'] == 'delete_status'){
    //haal de reacties op, om te verwijderen
    $what = 'reactie'; $from ='portal_status_reactie'; $where = 'status = '.$_POST['status_id'];
        $verwijder_reacties = sqlSelect($what, $from, $where);
    
    //verwijder de geassioci�rde reacties
    while($verwijder_reactie = mysql_fetch_array($verwijder_reacties)){
        $verwijder_reactie_id = $verwijder_reactie['reactie'];
        $table = "portal_status_reactie"; $where = "reactie = $verwijder_reactie_id";
            $delete_reactie_koppeling = sqlDelete($table, $where);
            
        $table = "portal_reactie"; $where = "id = $verwijder_reactie_id";
            $delete_reactie = sqlDelete($table, $where); 
    }
    
    //zet de status op non-actief
    $table = 'portal_status'; $what = 'actief = 0'; $where = 'id ='.$_POST['status_id'];
        $update_status_naar_nonactief = sqlUpdate($table, $what, $where);
}

$what = 'a.id, a.gebruiker, a.inhoud, UNIX_TIMESTAMP(a.geschreven_op) AS geschreven_op,
         b.gebruikersnaam, b.voornaam, b.achternaam, b.profielfoto';
$from = 'portal_status a 
         LEFT JOIN portal_gebruiker AS b ON (b.id = a.geplaatst_door)
         LEFT JOIN portal_image AS c ON (c.id = b.profielfoto)';
$where = 'a.gebruiker = '.$profiel_id.' AND a.actief = 1 AND b.actief = 1';
if($_GET['interval'] > 1){
    $oude_datum_interval = $datum_interval * ($_GET['interval'] - 1);
    $nieuwe_datum_interval = $datum_interval * $_GET['interval'];
    $where .= ' AND  a.update_datum < DATE_SUB(NOW(), INTERVAL '.$oude_datum_interval.' DAY) AND  a.update_datum >DATE_SUB(NOW(), INTERVAL '.$nieuwe_datum_interval.' DAY)';
}else{
    $where .= ' AND a.update_datum >DATE_SUB(NOW(), INTERVAL '.$datum_interval.' DAY)';
}

$where .= ' ORDER BY a.geschreven_op DESC';
//echo "SELECT $what FROM $from WHERE $where";
$count_statussen = countRows($what, $from, $where);
if($count_statussen > 0){
$statussen = sqlSelect($what, $from, $where);
while($status = mysql_fetch_array($statussen)){
    //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
    $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$status['profielfoto'].' AND actief = 1';
        $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
    if($profielfoto['path'] == null){
        $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
    }else{
        $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
    }?>
    <div class="status" onmouseover="this.className='status status_hover'" onmouseout="this.className='status'">
        <div class="status_left">
            <a href="<?php echo $etc_root; ?>profiel/<?php echo $reactie['gebruikersnaam'] ?>" title="bekijk het profiel">
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
                            <img class="delete" onclick="statusAction(<?php echo $status['gebruiker']; ?> , 'delete_status', <?php echo $status['id']; ?>)" 
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
     $what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, 
              c.voornaam, c.achternaam, c.gebruikersnaam, c.profielfoto';
     $from = 'portal_status_reactie a
              LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
              LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
     $where = 'a.status ='.$status['id'].' AND b.actief = 1 AND c.actief = 1 ORDER BY b.geschreven_op ASC';
     $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);
?>
            <div id="reactielijst_<?php echo $status['id'] ?>">
            <?php while($reactie = mysql_fetch_array($reactie_result)){
                  if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}
                  //haal de profielfoto apart op, aangezien deze niet altijd actief meer is
                  $what = 'path, album'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
                    $profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                  
                  if($profielfoto['path'] == null){
                    $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/profile-empty.jpg" alt="profielfoto" />';
                  }else{
                    $profiel_foto = '<img src="'.$etc_root.'lib/slir/'.slirImage(50,50).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="profielfoto" />';
                  }?>
                           
                 <div class="reactie <?php echo $class ?>" onmouseover="this.className='reactie reactie_hover <?php echo $class ?> '" onmouseout="this.className='reactie <?php echo $class ?>'">
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
                                                <a href="/profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel">
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
            	<div class="status_reageren" id="reageren_<?php echo $status['id']; ?>">
                <div class="status_reactie_profilepic"><?php echo $gebruiker_profielfoto ?></div>
                <textarea class="textarea" id="reactie_<?php echo $status['id'] ?>" cols="50" rows="3"></textarea>
                <div class="reageren_button_container">
                    <button class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="reactieForm(<?php echo $status['id'].', \'create_reactie\', 0'; ?>)">Reageer</button>
                </div>
            </div>
    </div>
<?php } 
}else{
    if($max_interval <= $_GET['interval']){
        echo '<div class="niets_gevonden">Er zijn geen statussen meer gevonden.</div>';
    }else{
        echo '<div class="niets_gevonden">Er zijn binnen de afgelopen periode geen statussen gevonden. Klik hieronder op meer laden om verder terug te kijken </div>';
    }
}?>