<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
if($_POST['action'] == 'create_reactie'){
    $find = array('\"', "\\'"); $replace = array('"', '\'');
    $reactie = str_ireplace($find, $replace, $_POST['reactie']);
    $reactie = mysql_real_escape_string(nl2br(htmlentities($reactie, ENT_NOQUOTES, 'UTF-8')));
    //maak de reactie aan
$table="portal_reactie"; $what = 'inhoud, geschreven_op, geschreven_door';  $with_what = "'".$reactie."', NOW(), $login_id";
    $nieuwe_reactie = sqlInsert($table, $what, $with_what);
        
    //koppel de reactie aan de status
$table="portal_status_reactie"; $what="status, reactie"; $with_what = $_POST['id'].', (SELECT MAX(id) FROM portal_reactie WHERE 1)';
        $koppel_reactie_aan_nieuws = sqlInsert($table, $what, $with_what);
        
        $table ="portal_status"; $what = 'update_datum = NOW()'; $where = "id  = $id";
                $update_nieuws = sqlUpdate($table, $what, $where);
}
elseif($_POST['action'] == 'delete_reactie'){
    //Verwijder de reactie koppeling
    $table = "portal_status_reactie"; $where='reactie = '.$_POST['reactie_id'];
        $delete_reactie = sqlDelete($table, $where);
    
    //verwijder de reactie zelf
    $table = 'portal_reactie'; $where= 'id = '.$_POST['reactie_id'];
}
    
$table="portal_status"; $what = 'laatste_wijziging = NOW()'; $where = 'id = '.$_POST['id'];
    $update_nieuws = sqlUpdate($table, $what, $where);
    
$what = 'b.id, b.inhoud, UNIX_TIMESTAMP(b.geschreven_op) AS geschreven_op, b.geschreven_door,
         c.voornaam, c.achternaam, c.gebruikersnaam, 
         d.path AS profielfoto, d.album';
$from = 'portal_status_reactie a
         LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)
         LEFT JOIN portal_gebruiker AS c ON (c.id = b.geschreven_door)';
$where = 'a.status ='.$_POST['id'].' AND b.actief = 1 AND c.actief = 1 ORDER BY geschreven_op ASC';
    $reactie_aantal = countRows($what, $from, $where); $reactie_result = sqlSelect($what, $from, $where);

                        while($reactie = mysql_fetch_array($reactie_result)){
                            if($class_i == 1){$class="en_om"; $class_i = 0;}else{$class="om"; $class_i = 1;}
                           echo '
                           <div class="reactie '.$class.'" onmouseover="this.className=\'reactie reactie_hover '.$class.' \'" onmouseout="this.className=\'reactie '.$class.'\'">
                                <div class="reactie_links">
                                    <div class="reactie_foto">
                                        <div class="reactie_profiel_pic">
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
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
                                                <a href="'.$etc_root.'profiel/'.$reactie['gebruikersnaam'].'" title="bekijk het profiel">
                                                    '.$reactie['voornaam'].' '.$reactie['achternaam'].'
                                                </a>
                                            </span>
                                            <span class="datum">'.verstrekenTijd($reactie['geschreven_op']).'</span>';
                                        if($reactie['geschreven_door'] == $login_id || $_SESSION['admin'] == 1){
                                            echo '
                                               <span class="delete_reactie">
                                                <span class="delete_container">
                                                    <img class="delete" onclick="reactieForm('.$_POST['id'].', \'delete_reactie\', '.$reactie['id'].')" 
                                                         src="'.$etc_root.'functions/profiel/css/images/delete.png" alt="X" title="Verwijder reactie" />
                                                </span>
                                            </span>';
                                        }
                           echo'
                                        </p>
                                    </div>                  
                                    <div class="reactie_tekst">'.$reactie['inhoud'].'</div>           
                                </div>
                           </div>';
                         }
?>