<?php
session_start();
require ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
require($_SERVER['DOCUMENT_ROOT'].$etc_root."lib/phpmailer/class.phpmailer.php");

//$login_id = 2;
//die heb ik overal nodig
if($_REQUEST['activiteit'] != null || $_REQUEST['activiteit_id'] != null){$detail = 'activiteit';}
elseif($_REQUEST['project'] != null || $_REQUEST['project_id'] != null){$detail = 'project';}

if($_POST['action'] == 'activiteit_toevoegen'){
    if($_POST['toevoegen_klant'] != null){
        $what = 'id'; $from = 'organisatie'; $where = "naam ='".$_POST['toevoegen_klant']."'";
        	$count_klanten = countRows($what,$from,$where);
        if($count_klanten > 1){$error['teveel_klanten'] = 'Wees specifieker welke organisatie u bedoelt. Er zijn meerdere organisaties die <b>'.$_POST['klant'].'</b> heten.';}
        elseif($count_klanten < 1){
            $error['teweinig_klanten'] = 'Er bestaat (nog) geen organisatie met de naam <b>'.$_POST['toevoegen_klant'].'</b>. Als u deze organisatie nu wilt toevoegen, 
            <a href="/crm" title="organisatie toevoegen">klik hier</a> en kies '."'nieuwe organisatie toevoegen'".'.';}
        else{
            $_SESSION['klant'] = $_POST['toevoegen_klant'];
            $klant_result = sqlSelect($what,$from,$where);
            $klant = mysql_fetch_assoc($klant_result);
        }
    }else{$error['klant_leeg'] = 'Kies een klant om een activiteit aan toe te voegen';}
    
	
    if($_POST['toevoegen_werkzaamheden'] != null){
        $werkzaamheden = nl2br(htmlentities($_POST['toevoegen_werkzaamheden'], ENT_NOQUOTES, "UTF-8")); 
        $werkzaamheden = mysql_real_escape_string($werkzaamheden); 
       $_SESSION['werkzaamheden'] = $_POST['toevoegen_werkzaamheden'];
    }else{$error['werkzaamheden_leeg'] = 'Vul werkzaamheden in, die u voor deze klant wilt uitvoeren'; }
    
    if($_POST['toevoegen_uur'] > 0){
       $uur_aantal = $_POST['toevoegen_uur']; 
       $_SESSION['uur_aantal'] = $uur_aantal;
    }else{$error['te_weinig_uur'] = 'Vul minstens 1 uur in voor deze activiteit'; }
    
    if(!$error){
		$project_id = $_POST['toevoegen_project'];
        
        $table="planning_activiteit";
        $what="project, competentie, werkzaamheden, uur_aantal, status, iteratie_volgorde, status_datum, actief, gewijzigd_op, gewijzigd_door";
        $with_what ="$project_id, ".$_POST['toevoegen_competentie'].", '$werkzaamheden', '$uur_aantal', 'nog te plannen', 0, NOW(), 1, NOW(), $login_id";
        $insert_activiteit = sqlInsert($table, $what, $with_what);
        //echo $what.'<br />'.$with_what;
        $what="max(id)  id"; $from="planning_activiteit"; $where="actief = 1"; 
        $result= sqlSelect($what, $from, $where); $id = mysql_fetch_assoc($result);
        $id = $id['id'];
		
		if(!is_dir($_SERVER['DOCUMENT_ROOT']."/files/planning_documenten/$id")){mkdir($_SERVER['DOCUMENT_ROOT']."/files/planning_documenten/$id");}
        
        /*$what = "id"; $from = "planning_activiteit"; $where = "werkzaamheden = '$werkzaamheden' AND uur_aantal = '$uur_aantal' ";
        $activiteit = sqlSelect($what,$from,$where); $activiteit = mysql_fetch_assoc($activiteit); $activiteit_id = $activiteit['id'];*/
        //als alles goed is, hoeven we deze niet te onhouden
        unset($_SESSION['werkzaamheden']); unset($_SESSION['uur_aantal']); unset($_SESSION['klant']);
        
        recentMaken($id, $login_id);
        
        header("location:".$site_name."planning/detail/activiteit-id=$id");
        
    }else{
        $_SESSION['error'] = $error;
        header("location: ".$site_name."planning/activiteit-toevoegen");
    }
}
elseif($_POST['action'] == 'project_toevoegen'){
        
    if($_POST['toevoegen_klant'] != null){
        $what = 'id'; $from = 'organisatie'; $where = "naam ='".$_POST['toevoegen_klant']."'";
            $count_klanten = countRows($what,$from,$where);
        if($count_klanten > 1){$error['teveel_klanten'] = 'Wees specifieker welke organisatie u bedoelt. Er zijn meerdere organisaties die <b>'.$_POST['klant'].'</b> heten.';}
        elseif($count_klanten < 1){
            $error['teweinig_klanten'] = 'Er bestaat (nog) geen organisatie met de naam <b>'.$_POST['toevoegen_klant'].'</b>. Als u deze organisatie nu wilt toevoegen, 
            <a href="/crm" title="organisatie toevoegen">klik hier</a> en kies '."'nieuwe organisatie toevoegen'".'.';}
        else{
            $_SESSION['klant'] = $_POST['toevoegen_klant'];
            $klant_result = sqlSelect($what,$from,$where);
            $klant = mysql_fetch_assoc($klant_result);
        }
    }else{$error['klant_leeg'] = 'Kies een klant om een activiteit aan toe te voegen';}
    
    if($_POST['toevoegen_titel'] != null){
       $titel = $_POST['toevoegen_titel']; 
       $_SESSION['titel'] = $titel;
    }else{$error['titel_leeg'] = 'Vul een titel in voor dit project'; }
    
    if($_POST['toevoegen_beschrijving'] != null){
        $beschrijving = nl2br(htmlentities($_POST['toevoegen_beschrijving'], ENT_NOQUOTES, "UTF-8")); 
        $werkzaamheden = mysql_real_escape_string($werkzaamheden); 
       $_SESSION['beschrijving'] = $_POST['toevoegen_beschrijving'];
    }else{$error['beschrijving_leeg'] = 'Vul een beschrijving in van dit project'; }
       
    if(!$error){
       	
		
        $table="project";
        $what="organisatie, titel, beschrijving, html_detail, actief, toegevoegd_op, toegevoegd_door";
        $with_what = $klant['id'].", '$titel', '$beschrijving', '', 1, NOW(), $login_id";
        
        if($_POST['toevoegen_begindatum'] != null){$what .=  ', startdatum'; $with_what .= ", STR_TO_DATE('".$_POST['toevoegen_begindatum']."','%d %m %Y')";}
       	if($_POST['toevoegen_einddatum'] != null){$what .=  ', einddatum'; $with_what .= ", STR_TO_DATE('".$_POST['toevoegen_einddatum']."','%d %m %Y')";}
        
        $insert_activiteit = sqlInsert($table, $what, $with_what);
        //echo "INSERT INTO $table ($what) VALUES $with_what";
        $what="max(id)  id"; $from="project"; $where="actief = 1";
        $result= sqlSelect($what, $from, $where); $id = mysql_fetch_assoc($result);
        $id = $id['id'];
        
        if(!is_dir($_SERVER['DOCUMENT_ROOT']."/files/project_documenten/$id")){mkdir($_SERVER['DOCUMENT_ROOT']."/files/project_documenten/$id");}
        
        /*$what = "id"; $from = "planning_activiteit"; $where = "werkzaamheden = '$werkzaamheden' AND uur_aantal = '$uur_aantal' ";
        $activiteit = sqlSelect($what,$from,$where); $activiteit = mysql_fetch_assoc($activiteit); $activiteit_id = $activiteit['id'];*/
        
        //als alles goed is, hoeven we deze niet te onhouden
        unset($_SESSION['klant']); unset($_SESSION['titel']); unset($_SESSION['beschrijving']);
        
        recentMaken($id, $login_id);
        
        header("location:".$site_name."planning/detail/project-id=$id");
        
    }else{
        $_SESSION['error'] = $error;
        header("location: ".$site_name."planning/project-toevoegen");
    }
}
elseif($_POST['action'] == 'activiteit_wijzigen'){
    if($_POST['werkzaamheden'] != null){
       $werkzaamheden = nl2br($_POST['werkzaamheden']); 
       $werkzaamheden = mysql_real_escape_string($werkzaamheden); 
       $_SESSION['werkzaamheden'] = $_POST['werkzaamheden'];
    }else{$error['werkzaamheden_leeg'] = 'Vul werkzaamheden in, die u voor deze klant wilt uitvoeren'; }
    
    if($_POST['uur'] > 0){
       $uur_aantal = $_POST['uur']; 
       $_SESSION['uur'] = $_POST['uur'];
    }else{$error['te_weinig_uur'] = 'Vul minstens 1 uur in voor deze activiteit'; }
    
    if(!$error){
        $competentie = $_POST['competentie'];
        $table="planning_activiteit";
        $what="werkzaamheden = '$werkzaamheden', uur_aantal = '$uur_aantal', competentie = $competentie, gewijzigd_op = NOW(), gewijzigd_door = $login_id";
        $where = "id = ".$_POST['activiteit_id'];
        $update_activiteit = sqlUpdate($table, $what, $where);
        recentMaken($_POST['activiteit'], $login_id);
        ?>
        <h2>U hebt met succes de activiteit gewijzigd</h2>
        <script>jQuery(function(){ window.setTimeout('location.reload()', 5000); });</script>
        <?php 
    }else{  $_SESSION['error'] = $error;  print_r($error); }
}elseif($_POST['action'] == 'project_wijzigen'){
        
    if($_POST['titel'] != null){
       $titel = mysql_real_escape_string($_POST['titel']); 
       $_SESSION['titel'] = $_POST['titel'];
    }else{$error['lege_titel'] = 'Vul minstens 1 uur in voor deze activiteit'; }
    
    if($_POST['beschrijving'] != null){
       $beschrijving = nl2br($_POST['beschrijving']); 
       $beschrijving = mysql_real_escape_string($beschrijving); 
       $_SESSION['beschrijving'] = $_POST['werkzaamheden'];
    }else{$error['beschrijving_leeg'] = 'Vul werkzaamheden in, die u voor deze klant wilt uitvoeren'; }
    
    
    if(!$error){
        $table="project";
        $what="titel = '$titel', beschrijving = '$beschrijving', gewijzigd_op = NOW(), gewijzigd_door = $login_id";
		if($_POST['begindatum'] != null){$what .=  ", startdatum = STR_TO_DATE('".$_POST['begindatum']."','%d %m %Y')";}
       	if($_POST['einddatum'] != null){$what .=  ", einddatum = STR_TO_DATE('".$_POST['einddatum']."','%d %m %Y')";}
        $where = "id = ".$_POST['project_id'];
        $update_activiteit = sqlUpdate($table, $what, $where);
        ?>
        <h2>U hebt met succes het project gewijzigd</h2>
        <script>jQuery(function(){ window.setTimeout('location.reload()', 5000); });</script>
        <?php
        
    }else{  $_SESSION['error'] = $error;  print_r($error); }
}
// nu komen de acties, uitgevoerd vanuit het detail overzicht

elseif($_POST['action'] == 'activiteit_todo'){
    
    $what = 'id'; $from = 'planning_iteratie'; $where = 'huidige_iteratie = 1'; $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    $table = 'planning_activiteit'; $what = 'status = \'to do\', iteratie = '.$iteratie['id'].', status_datum = NOW(), actief = 1, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_naar_todo = sqlUpdate($table, $what, $where);
    $reloadActiviteit = true;
    recentMaken($_POST['activiteit'], $login_id);
    
}
elseif($_POST['action'] == 'activiteit_onderhanden'){
    
    $what = 'id'; $from = 'planning_iteratie'; $where = 'huidige_iteratie = 1'; $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    $table = 'planning_activiteit'; $what = 'status = \'onderhanden\', in_behandeling_door = '.$login_id.', iteratie = '.$iteratie['id'].', status_datum = NOW(), actief = 1, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_naar_onderhanden = sqlUpdate($table, $what, $where);
   $reloadActiviteit = true; 
   recentMaken($_POST['activiteit'], $login_id);
       
}
elseif($_POST['action'] == 'activiteit_acceptatie' || $_GET['action'] == 'pakActiviteitOp'){
    if($_POST['id']){$id = $_POST['id']; $reloadActiviteit = true; } 
    else{$id = $_GET['activiteit']; }
    
    $what = 'id'; $from = 'planning_iteratie'; $where = 'huidige_iteratie = 1'; $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    $table = 'planning_activiteit'; $what = 'status = \'acceptatie\', acceptatie_door = '.$login_id.', iteratie = '.$iteratie['id'].', status_datum = NOW(), actief = 1, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = "id = $id";
        $activiteit_naar_acceptatie = sqlUpdate($table, $what, $where);
 	recentMaken($_POST['activiteit'], $login_id);
     
}
elseif($_POST['action'] == 'activiteit_afronden'){
    
    $what = 'id'; $from = 'planning_iteratie'; $where = 'huidige_iteratie = 1'; $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    $table = 'planning_activiteit'; $what = 'status = \'done\', acceptatie_door = '.$login_id.', iteratie = '.$iteratie['id'].', status_datum = NOW(), actief = 1, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_naar_done = sqlUpdate($table, $what, $where);
    $reloadActiviteit = true;
	recentMaken($_POST['activiteit'], $login_id);
     
}
elseif($_POST['action'] == 'activiteit_delete'){
    $table = 'planning_activiteit'; $what = 'actief = 0, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_verwijderen = sqlUpdate($table, $what, $where);
    $goToDashboard = 'activiteit';
}
elseif($_POST['action'] == 'project_afronden'){
    $table = 'project'; $what = 'afgerond = 1, actief = 1, einddatum = NOW(), gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_verwijderen = sqlUpdate($table, $what, $where);
    $reloadProject = true;
}
elseif($_POST['action'] == 'project_activeren'){
    $table = 'project'; $what = 'afgerond = 0, actief = 1, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_verwijderen = sqlUpdate($table, $what, $where);
    $reloadProject = true;
}
elseif($_POST['action'] == 'project_delete'){
    $table = 'planning_activiteit'; $what = 'actief = 0, gewijzigd_op = NOW(), gewijzigd_door = '.$login_id;
    $where = 'id = '.$_POST['id'];
        $activiteit_verwijderen = sqlUpdate($table, $what, $where);
    $goToDashboard = 'project';
}
if($reloadActiviteit){?>
    <h2>U hebt met succes de activiteit gewijzigd</h2>  
<?php }
elseif($reloadProject){?>
    <h2>U hebt met succes het project gewijzigd</h2>    
<?php }
elseif($goToDashboard){ echo '<span id="activiteit_project_verwijderd">'.$goToDashboard.' succesvol verwijderd </span>'; }

elseif($_POST['action'] == 'tag'){
    $tag = $_POST['tag'];
    $what = 'DISTINCT id'; $from = 'portal_tag'; $where='id > 0';
    $aantal_tags = countRows($what, $from, $where);
    if($aantal_tags > 0){
        //echo 'er zijn meer tags <br />';
        //hoeveel tags zijn er met deze tagnaam, tel ze
        //gaat het over een activiteit of een project
        if($detail == 'activiteit'){ $what = 'DISTINCT a.id, b.activiteit'; $from = 'portal_tag a LEFT JOIN planning_tag AS b ON ( b.tag = a.id )'; $where="a.naam = '$tag'"; }
        elseif($detail == 'project'){ $what = 'DISTINCT a.id, b.project'; $from = 'portal_tag a LEFT JOIN project_tag AS b ON ( b.tag = a.id )'; $where="a.naam = '$tag'"; }
            
            $aantal_gekoppelde_tags = countRows($what, $from, $where);
        //is het er 1 of meer, dan kijken of het een bekende is. zo niet: toevoegen
        //niet bekend ? toevoegen.
        if($aantal_gekoppelde_tags > 0){
            //echo 'bestaat al! zoveel keer : '.$aantal_gekoppelde_tags.' <br />';
            $gekoppelde_tag = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            //gaat het over een activiteit of een project
            if($detail == 'activiteit'){
                if($_POST['activiteit'] == $gekoppelde_tag['activiteit']){
                    //doe niets... want de kennis is al bekend bij deze activiteit
                    //echo 'al gekoppeld <br />';
                }else{
                    //aangezien de ingevoerde kennis wel bestaat, maar (nog) niet is gekoppeld aan deze gebruiker.
                    //doen we dat nu !
                    $table = 'planning_tag'; $what = 'tag, activiteit, toegevoegd_op, toegevoegd_door';
                    $with_what = $gekoppelde_tag['id'].', '.$_POST['activiteit'].', NOW(), '.$login_id;
						recentMaken($_POST['activiteit'], $login_id);
                    //echo 'nu gekoppeld <br />';
                }
            }
            
            elseif($detail == 'project'){
                if($_POST['project'] == $gekoppelde_tag['project']){
                    //doe niets... want de kennis is al bekend bij dit project
                    //echo 'al gekoppeld <br />';
                }else{
                    //aangezien de ingevoerde kennis wel bestaat, maar (nog) niet is gekoppeld aan deze gebruiker.
                    //doen we dat nu !
                    $table = 'project_tag'; $what = 'tag, project, toegevoegd_op, toegevoegd_door';
                    $with_what = $gekoppelde_tag['id'].', '.$_POST['project'].', NOW(), '.$login_id;
                    //echo 'nu gekoppeld <br />';
                }
            }
            if(isset($table)){ $tag_koppelen = sqlInsert($table,$what,$with_what); }

        }else{
            //echo 'deze tag bestond nog niet <br />';
            //aangezien de ingevoerde tag (nog) niet bestaat, maken we deze aan.
            $table = 'portal_tag'; $what = 'naam';   $with_what = "'$tag'";
                $tag_toevoegen = sqlInsert($table,$what,$with_what);
            //echo 'tag bestaat nu <br />';
            //gaat het over een activiteit of een project
            if($detail == 'activiteit'){
                //ook koppelen we deze nieuwe kennis aan de activiteit.
                $table = 'planning_tag'; $what = 'tag, activiteit, toegevoegd_op, toegevoegd_door';
                $with_what = '(SELECT MAX(a.id) FROM portal_tag a), '.$_POST['activiteit'].', NOW(), '.$login_id;
                //echo 'en is gekoppeld: <br />'.$tag;
                recentMaken($_POST['activiteit'], $login_id);
            }
            
            if($detail == 'project'){
                //ook koppelen we deze nieuwe kennis aan de activiteit.
                $table = 'project_tag'; $what = 'tag, project, toegevoegd_op, toegevoegd_door';
                $with_what = '(SELECT MAX(a.id) FROM portal_tag a), '.$_POST['project'].', NOW(), '.$login_id;
                //echo 'en is gekoppeld: <br />'.$tag;
            }
            $tag_koppelen = sqlInsert($table,$what,$with_what);
			
        }
    }else{
        //echo 'deze tag bestond nog niet (eigenlijk geen een)<br />';
        //er bestaan nog geen tags.. dan doen we dit
        //aangezien de ingevoerde tag (nog) niet bestaat, maken we deze aan.
        $table = 'portal_tag'; $what = 'naam'; $with_what = "'$tag'";
            $tag_toevoegen = sqlInsert($table,$what,$with_what);
        
        //echo 'tag bestaat nu <br />';
        //gaat het over een activiteit of een project
        if($detail == 'activiteit'){
             //ook koppelen we deze nieuwe kennis aan de activiteit.
             $table = 'planning_tag'; $what = 'tag, activiteit, toegevoegd_op, toegevoegd_door';
             $with_what = '(SELECT MAX(a.id) FROM portal_tag a), '.$_POST['activiteit'].', NOW(), '.$login_id;
             //echo 'en is gekoppeld: <br />'.$tag;
			 recentMaken($_POST['activiteit'], $login_id);
        }elseif($detail == 'project'){   
             //ook koppelen we deze nieuwe kennis aan de activiteit.
             $table = 'project_tag'; $what = 'tag, project, toegevoegd_op, toegevoegd_door';
             $with_what = '(SELECT MAX(a.id) FROM portal_tag a), '.$_POST['project'].', NOW(), '.$login_id;
             //echo 'en is gekoppeld: <br />'.$tag;
        }
        $tag_koppelen = sqlInsert($table,$what,$with_what);
   }
   $refreshTags = 1; //nodig voor het refreshen
}
if($_GET['action'] == 'delete_tag'){
    if($detail == 'activiteit'){ $table = 'planning_tag'; $where = 'activiteit = '.$_GET['activiteit'].' AND tag = '.$_GET['tag']; }
    elseif($detail == 'project'){ $table = 'project_tag'; $where = 'project = '.$_GET['project'].' AND tag = '.$_GET['tag']; }
    $verwijder_tag = sqlDelete($table, $where);    $refreshTags = 1;
    
}
if($_GET['action'] == 'refreshTags'){$refreshTags = 1;}//nodig voor het refreshen}
if($_GET['action'] == 'refreshFiles'){$refreshFiles = 1;} //nodig voor het refreshen}
if($_POST['action'] == 'refreshReacties'){$resetReacties = 1;}
if($_GET['action'] == 'delete_file'){
    if($detail == 'activiteit'){ $bestand_id = $_REQUEST['activiteit'];$what = 'bestand'; $from =  'planning_bestand'; $map = 'planning_documenten/'.$bestand_id.'/';}
    elseif($detail == 'project'){$bestand_id = $_REQUEST['project']; $what = 'bestand'; $from =  'project_bestand'; $map = 'project_documenten/'.$bestand_id.'/';}
    $where = 'id = '.$_GET['bestand_id'];
        $bestand = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    $where = 'id = '.$_GET['bestand_id']; $verwijder_bestand = sqlDelete($from, $where);
       
    //verwijder bestand v/d server 
    $filename = $_SERVER['DOCUMENT_ROOT'].'/files/'.$map.$bestand['bestand'];
    if(is_file($filename)){unlink($filename);}
    
    $refreshFiles = 1; //nodig voor het refreshen
}
if($refreshTags == 1){
    //tag(s) ophalen
    $what = 'b.id, b.naam';
    if($detail == 'activiteit'){$tag_id = $_REQUEST['activiteit']; $from = 'planning_tag a, portal_tag b'; $where = "a.activiteit = $tag_id AND b.id = a.tag"; $message = 'deze activiteit'; }
    elseif($detail == 'project'){$tag_id = $_REQUEST['project']; $from = 'project_tag a, portal_tag b'; $where = "a.project = $tag_id AND b.id = a.tag"; $message = 'dit project'; }
      //echo "SELECT $what FROM $from WHERE $where";
      $aantal_tags = countRows($what, $from, $where);  $result_tags = sqlSelect($what, $from, $where);
    if($aantal_tags > 0){
        while($row = mysql_fetch_array($result_tags)){
            $tag_list[] = 
            '<div class="tag">
                <a class="tag_zoeken" href="?function=-zoek&query='.$row['naam'].'" target="_blank" title="zoek naar tag">'.$row['naam'].'</a>
                <span class="delete_tag" onmouseover="this.className=\'delete_tag delete_tag_hover\'" onmouseout="this.className=\'delete_tag\'" onclick="deletetag('.$row['id'].')"> X </span>
            </div>';
        }
    }
    if(count($tag_list) > 0){
        foreach($tag_list as $tag){ echo $tag; }
    }else{ echo 'er zijn nog geen tags voor '.$message; }
}
if($refreshFiles == 1){
    //bestand(en)
    $what_bestanden = 'id, bestand';
    if($detail == 'activiteit'){$bestand_id = $_REQUEST['activiteit'];$from_bestanden = 'planning_bestand'; $where_bestanden = "activiteit = '$bestand_id'"; $message = 'deze activiteit';}
    elseif($detail == 'project'){$bestand_id = $_REQUEST['project'];$from_bestanden = 'project_bestand'; $where_bestanden = "project = '$bestand_id'"; $message = 'dit project';}
        $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden); $result_bestanden = sqlSelect($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($aantal_bestanden > 0){
        while($row = mysql_fetch_array($result_bestanden)){
            echo '
            <div class="bestand" onmouseover="this.className='."'bestand_hover'".'" onmouseout="this.className='."'bestand'".'">
                <a class="bestand_link" href="/files/planning_documenten/'.$bestand_id.'/'.$row['bestand'].'" target="_blank" title="bekijk bestand">'.$row['bestand'].'</a>
                <span class="verwijder_bestand" onclick="deleteFile('.$row['id'].')">verwijderen</span>
            </div>';
        }
    }else{
        echo 'Er zijn nog geen bestanden ge&uuml;pload bij '.$message;
    }
}

elseif($_POST['action'] == 'reactie'){
    //als de reactie leeg is... niet de database zetten.. maar direct terug naar de activiteit.
    if($_POST['reactie'] != null){
        //haal de werkzaamheden op die in de mail gebruikt moeten worden
        /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
        $where= 'id ='.$_POST['activiteit_id'];
        $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
        $werkzaamheden = $info['werkzaamheden'];*/
        
        //zet alle tekens om in HTML.. voor de veiligheid.
        $reactie = nl2br(htmlentities($_POST['reactie'], ENT_NOQUOTES, "UTF-8"));
                    
        $table='portal_reactie'; 
        $what='inhoud, geschreven_op, geschreven_door, actief';
        $with_what .= "'".mysql_real_escape_string($reactie)."', NOW(), $login_id, 1";
            $voeg_reactie_toe = sqlInsert($table, $what, $with_what);
		
        if($detail == 'activiteit'){
            $table = 'planning_reactie'; $what = 'activiteit, reactie';
            $with_what = $_POST['activiteit_id'].', (SELECT MAX(id) AS id FROM portal_reactie WHERE actief = 1)';
			recentMaken($_POST['activiteit_id'], $login_id);
        }
        if($detail == 'project'){
            $table = 'project_reactie'; $what = 'project, reactie';
            $with_what = $_POST['project_id'].', (SELECT MAX(id) AS id FROM portal_reactie WHERE actief = 1)';    
        } 
            $koppel_reactie = sqlInsert($table, $what, $with_what);
       
        /*$what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "Een nieuwe reactie bij de activiteit '$werkzaamheden'";
                    $htmlbody = "
                    <p><b>Een nieuwe reactie bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu is er door $gewijzigd_door een nieuwe reactie geplaatst voor de activiteit '$werkzaamheden'.<br />
                    Klik op de volgende link om de reactie te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/detail/activiteit-id=".$_POST['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu is er door $gewijzigd_door een nieuwe reactie geplaatst voor de activiteit '$werkzaamheden'.
                    Klik op de volgende link om de reactie te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/detail/activiteit-id=".$_POST['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
                
            }
        }//afsluiting voor de waarschuwingen
        */
    }
   //header('location: '.$site_name.'planning/'.$_POST['refer'].'/detail/activiteit-id='.$_POST['activiteit_id']);
   $resetReacties = 1;
}
elseif($_POST['action'] == 'reactie_wijzigen'){
    //als de reactie leeg is... niet de database veranderen.. maar direct terug naar de activiteit.
    if($_POST['reactie'] != null){
        //haal de werkzaamheden op die in de mail gebruikt moeten worden
        /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
        $where= 'id ='.$_POST['activiteit_id'];
        $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
        $werkzaamheden = $info['werkzaamheden'];*/
        
        //zet alle tekens om in HTML.. voor de veiligheid.
        $reactie = nl2br(htmlentities($_POST['reactie'], ENT_NOQUOTES, "UTF-8"));
         
        $table='portal_reactie';
        $what .= "inhoud ='".mysql_real_escape_string($reactie)."', geschreven_op = NOW(), geschreven_door = $login_id";
		$where = 'id = '.$_POST['reactie_id'];       
            $wijzig_reactie = sqlUpdate($table, $what, $where);
       /* 
        $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "Een gewijzigde reactie bij de activiteit '$werkzaamheden'";
                    $htmlbody = "
                    <p><b>Een nieuwe reactie bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu is er door $gewijzigd_door een reactie gewijzigd voor de activiteit '$werkzaamheden'.<br />
                    Klik op de volgende link om de reactie te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/detail/activiteit-id=".$_POST['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu is er door $gewijzigd_door een reactie gewijzigd voor de activiteit '$werkzaamheden'.
                    Klik op de volgende link om de reactie te bekijken
                    http://".$site_name."planning/".$_POST['refer']."/detail/activiteit-id=".$_POST['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
        */
        recentMaken($_POST['activiteit_id'], $login_id);
        ?> 
             <h2>U hebt met succes de reactie gewijzigd</h2>
        <script>jQuery(function(){ re_loadReactie(); jQuery('#dialog').dialog('close') });</script>
     <?php
    }
	
}
elseif($_POST['action'] == 'delete_reactie'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];
    
    $what = 'geschreven_door'; $from = 'planning_reactie';
    $where = 'actief = 1 AND id = '.$_GET['reactie_id'];
    $verwijderd = mysql_fetch_assoc(sqlSelect($what, $from, $where));*/
    
    if($detail =='activiteit'){$table='planning_reactie';}
    elseif($detail == 'project'){$table='project_reactie';}
    $where = 'reactie = '.$_POST['reactie'];
            $verwijderKoppeling = sqlDelete($table, $where);
	
	$table='portal_reactie'; $where = 'id = '.$_POST['reactie'];
            $verwijderReactie = sqlDelete($table, $where);
   
   /* 
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_GET['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                //is een reactie van de te waarschuwen gebruiker, verwijderd ? zeg d�t dan.
                if($verwijderd['geschreven_door'] == $gebruikerid){$verwijderd_van = 'uw reactie';}
                elseif($verwijderd['geschreven_door'] == $login_id){$verwijderd_van = 'zijn/haar eigen reactie';}
                else{ $verwijderd_van = 'een reactie';}
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De prioriteit van de activiteit '$werkzaamheden' is gewijzigd";
                    $htmlbody = "
                    <p><b>De prioriteit van de activiteit '$werkzaamheden' is gewijzigd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door $verwijderd_van bij de activiteit '$werkzaamheden' verwijderd.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door $verwijderd_van bij de activiteit '$werkzaamheden' verwijderd.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/detail/activiteit-id='.$_GET['activiteit_id']);
	*/
	 $resetReacties = 1;
}
elseif($_POST['action'] == 'delen_check'){
    if($_POST['gebruikers'] != null){
             
        //wie heeft het gestuurd ?
        $what = 'email'; $from = 'portal_gebruiker'; $where= 'id = '.$login_id;
        $gedeeld_door = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
        if($detail == 'activiteit'){$what = 'werkzaamheden'; $from = 'planning_activiteit'; $where= 'id = '.$_POST['activiteit']; $delen = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $te_delen = $delen['werkzaamheden'];}
        if($detail == 'project'){$what = 'titel'; $from = 'project'; $where= 'id = '.$_POST['project']; $delen = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $te_delen = $delen['titel'];}
            
        
        //maak de gebruikers 'schoon' (en stop ze in een array)
        if(stripos($_POST['gebruikers'], '_') !== false){$gebruikers = explode('_', $_POST['gebruikers']);}
        elseif($_POST['gebruikers'] != null){$gebruikers = array($_POST['gebruikers']);}
        
        foreach($gebruikers as $delen_met){
            //haal eerst even alles op
            $what = 'voornaam, achternaam, email'; $from = 'portal_gebruiker'; $where= "id = $delen_met";
                $informatie = mysql_fetch_assoc(sqlSelect($what, $from, $where));

            //begin dan met het updaten van de database.
            if($detail == 'activiteit'){
                $table = 'planning_delen'; $what = 'activiteit, gebruiker, gedeeld_op, gedeeld_door';
                $with_what = $_POST['activiteit'].", $delen_met, NOW(), $login_id";
            }
            if($detail == 'project'){
                $table = 'project_delen'; $what = 'project, gebruiker, gedeeld_op, gedeeld_door';
                $with_what = $_POST['project'].", $delen_met, NOW(), $login_id";    
            }
                $insert_nieuw_deling = sqlInsert($table, $what, $with_what);
            
            if($_POST['toelichting'] != null){ $toelichting = nl2br(htmlentities($_POST['toelichting'], ENT_NOQUOTES, "UTF-8")); }
            
            //om te laten zien, wie er allemaal een mailtje hebben gekregen: 
            $verstuurd_naar[] = $informatie['voornaam']." ".$informatie['achternaam'];
            
            $nu = strftime("%d %B %Y");
            //als je iets met jezelf deelt.. ziet het er iets anders uit
            if($delen_met == $login_id){
                if($detail == 'activiteit'){
                    $subject = "Je hebt een activiteit met jezelf gedeeld";
                    $htmlbody = "
                    <h3>Je wilt graag een activiteit met jezelf delen, in de qracht planningstool</h3>
                    <p>Beste ".$informatie['voornaam']." ".$informatie['achternaam'].",<br />
                    Op $nu heb je een activiteit gedeeld met jezelf <br />
                    Klik op de volgende link om te kijken naar deze activiteit:<br />
                    <a href=\"".$site_name."planning/detail/activiteit-id=".$_POST['activiteit']."\" title=\"ga naar deze activiteit\">$te_delen</a>
                    </p>";
                    
                    if($toelichting != null){
                    $htmlbody .= "
                        <p>
                        Extra toelichting:<br />
                        ".$toelichting."</p>";
                    }
                    
                    $textbody = " 
                    Beste ".$informatie['voornaam']." ".$informatie['achternaam'].", 
                    Op $nu heb je de activiteit '$te_delen' gedeeld met jezelf
                    Klik op de volgende link om te kijken naar deze activiteit:"
                    .$site_name."planning/detail/activiteit-id=".$_POST['activiteit'];
                    
                    if($_POST['toelichting'] != null){
                        $textbody .= "
                        Extra toelichting:
                        ".$_POST['toelichting']."";
                    }
                }
                if($detail == 'project'){
                    $subject = "Je hebt een project met jezelf gedeeld";
                    $htmlbody = "
                    <h3>Je wil graag een project met jezelf delen, in de qracht planningstool</h3>
                    <p>Beste ".$informatie['voornaam']." ".$informatie['achternaam'].",<br />
                    Op $nu heb je een project gedeeld met jezelf <br />
                    Klik op de volgende link om te kijken naar dit project:<br />
                    <a href=\"".$site_name."planning/detail/project-id=".$_POST['project']."\" title=\"ga naar deze activiteit\">$te_delen</a>
                    </p>";
                    if($toelichting != null){
                        $htmlbody .= "
                            <p>
                            Extra toelichting:<br />
                            ".$toelichting."</p>";
                    }
                    
                    $textbody = " 
                    Beste ".$informatie['voornaam']." ".$informatie['achternaam'].", 
                    Op $nu heb je het project '$te_delen' gedeeld met jezelf
                    Klik op de volgende link om te kijken naar dit project:"
                    .$site_name."planning/detail/project-id=".$_POST['project'];
    
                    if($_POST['toelichting'] != null){
                        $textbody .= "
                            Extra toelichting:
                            ".$_POST['toelichting']."";   
                    }
                }
                
            }else{
                if($detail == 'activiteit'){
                    $subject = "$naam wil graag een activiteit met je delen";
                    $htmlbody = "
                        <h3>$naam wil graag een activiteit met je delen, in de qracht planningstool</h3>
                        <p>Beste ".$informatie['voornaam']." ".$informatie['achternaam'].",<br />
                        Op $nu is er door <a href=\"".$site_name."profiel/$gebruikers_naam\">$naam</a> een activiteit met je gedeeld <br />
                        Klik op de volgende link om te kijken welke activiteit dat is:<br />
                        <a href=\"".$site_name."planning/detail/activiteit-id=".$_POST['activiteit']."\" title=\"ga naar deze activiteit\">$te_delen</a>
                        </p>";
        
                    if($toelichting != null){
                        $htmlbody .= "
                            <p>
                            Extra toelichting:<br />
                            ".$toelichting."</p>";
                    }
                    
                    $textbody = " 
                        Beste ".$informatie['voornaam']." ".$informatie['achternaam'].", 
                        Op $nu is er door $naam de activiteit '$te_delen' met je gedeeld
                         Klik op de volgende link om te kijken welke activiteit dat is:"
                        .$site_name."planning/detail/activiteit-id=".$_POST['activiteit'];
                    if($_POST['toelichting'] != null){
                        $textbody .= "
                            Extra toelichting:
                            ".$_POST['toelichting']."";
                    }
                }
                if($detail == 'project'){
                    $subject = "$naam wil graag een project met je delen";
                    $htmlbody = "
                        <h3>$naam wil graag een project met je delen, in de qracht planningstool</h3>
                        <p>Beste ".$informatie['voornaam']." ".$informatie['achternaam'].",<br />
                        Op $nu is er door <a href=\"".$site_name."profiel/$gebruikers_naam\">$naam</a> een project met je gedeeld <br />
                        Klik op de volgende link om te kijken welk project dat is:<br />
                        <a href=\"".$site_name."planning/detail/project-id=".$_POST['project']."\" title=\"ga naar deze activiteit\">$te_delen</a>
                        </p>";
        
                    if($toelichting != null){
                        $htmlbody .= "
                            <p>
                            Extra toelichting:<br />
                            ".$toelichting."</p>";
                    }
                    
                    $textbody = " 
                        Beste ".$informatie['voornaam']." ".$informatie['achternaam'].", 
                        Op $nu is er door $naam het project '$te_delen' met je gedeeld
                         Klik op de volgende link om te kijken welk project dat is:"
                        .$site_name."planning/detail/project-id=".$_POST['project'];
                    if($_POST['toelichting'] != null){
                        $textbody .= "
                            Extra toelichting:
                            ".$_POST['toelichting']."";
                    }
                }
           
            }                                
            $mail = new PHPMailer();
            $mail->SetFrom($gedeeld_door['email'], $naam);
            $mail->AddReplyTo($admin_email,$admin_email_naam);
            $mail->AddAddress($informatie['email']);
            
            $mail->Subject = $subject;
            $mail->Body = $htmlbody;
            $mail->AltBody= $textbody;
            $mail->WordWrap = 50; 
            if(!$mail->Send()){
                $error = 'mailer error'.$mail->ErrorInfo;
            }
        }
        if($detail == 'activiteit'){ echo 'U hebt met succes de activiteit gedeeld met: '; }
        if($detail == 'project'){ echo 'U hebt met succes het project gedeeld met: '; }
        
        $i = 0;
        foreach($verstuurd_naar AS $gestuurd_naar){
            if($i == 0){ echo $gestuurd_naar; }
            else{ echo ', '.$gestuurd_naar; }
            $i++;
        } 
    }
}
elseif($_GET['action'] == 'delete_activiteit'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));  
    $werkzaamheden = $info['werkzaamheden'];
    
    $table='planning_activiteit';
    $what='actief = 0';
    $where='id = '.$_GET['activiteit_id'];
    $activiteit_non_actief = sqlUpdate($table, $what, $where);
    /*
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_GET['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                //is een reactie van de te waarschuwen gebruiker, verwijderd ? zeg d�t dan.
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De '$werkzaamheden' is verwijderd";
                    $htmlbody = "
                    <p><b>De activiteit '$werkzaamheden' is verwijderd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' verwijderd.<br />";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door bij de activiteit '$werkzaamheden' verwijderd";
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    */
    header('location: '.$site_name.'planning/'.$_GET['refer']);
}
elseif($_GET['action'] == 'afronden'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];*/
    
    $table='planning_activiteit';
    $what="status='done', status_datum = NOW(), gewijzigd_op=NOW(), gewijzigd_door=$login_id";
    $where='id='.$_GET['activiteit_id'];
    $afronden_activiteit = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id'], $login_id);
    /*
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_GET['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De activiteit '$werkzaamheden' is afgerond";
                    $htmlbody = "
                    <p><b>Een nieuwe reactie bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' afgerond.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' afgerond.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    */
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/detail/activiteit-id='.$_GET['activiteit_id']);
}
elseif($_GET['action'] == 'opnieuw_plannen'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];*/
    
    $table='planning_activiteit';
    $what="status='nog te plannen', planning_iteratie_id = 0, status_datum = NOW(), gewijzigd_op=NOW(), gewijzigd_door=$login_id";
    $where='id='.$_GET['activiteit_id'];
    $afronden_activiteit = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id'], $login_id);
    /*
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_GET['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De activiteit '$werkzaamheden' is in 'nog te plannen' gezet";
                    $htmlbody = "
                    <p><b>Een nieuwe reactie bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in 'nog te plannen' gezet.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in 'nog te plannen' gezet.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    */
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/detail/activiteit-id='.$_GET['activiteit_id']);
}
elseif($_GET['action'] == 'html_tekst' ){ 
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];
    */
    $html_detail = $_POST['content'];
    
    if($detail == 'activiteit'){ $table="planning_activiteit"; $where = "id = ".$_GET['activiteit_id']; }
    if($detail == 'project'){ $table="project"; $where = "id = ".$_GET['project_id']; }
    $what = "html_detail = '$html_detail'";
        $update_html_detail = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id'], $login_id);
    
    /*$what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_GET['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De detailtekst voor de activiteit '$werkzaamheden' is gewijzigd";
                    $htmlbody = "
                    <p><b>De detailtekst voor de activiteit '$werkzaamheden' is gewijzigd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' de detailtekst gewijzigd.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' de detailtekst gewijzigd.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    */
    
}
elseif($_GET['action'] == 'accept_activiteit'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    /*$what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];*/
    
    $table="planning_activiteit";
    $what="acceptatie_door = $login_id";
    $where="id = ".$_GET['activiteit_id'];
    $accepteer_activiteit = sqlUpdate($table, $what, $where);
    recentMaken($_GET['activiteit_id'], $login_id);
    
    /*$what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "activiteit =".$_GET['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De activiteit '$werkzaamheden' is in acceptatie genomen";
                    $htmlbody = "
                    <p><b>De detailtekst voor de activiteit '$werkzaamheden' is in acceptatie genomen</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in acceptatie genomen.<br />
                    Klik op de volgende link om de activiteit te bekijken<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu heeft $gewijzigd_door de activiteit '$werkzaamheden' in acceptatie genomen.
                    Klik op de volgende link om de activiteit te bekijken
                    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
            }
        }//afsluiting voor de waarschuwingen
    
    header("location: ".$site_name."planning");
     */
}

elseif($_GET['action'] == 'volgen'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];    
    
    $table='planning_waarschuw_mij'; $what = 'activiteit, gebruiker_id, gewijzigd_op';
    $with_what =  $_GET['activiteit_id'].', '.$login_id.', NOW()';
    $nieuwe_waarschuw_mij_aanmaken = sqlInsert($table, $what, $with_what);
              
    $what = 'voornaam, achternaam, email'; $from = 'relaties'; $where = 'id='.$_SESSION['relatie_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    $voornaam = $info['voornaam']; $achternaam = $info['achternaam'];
    $nu = strftime("%d %B %Y");
                    
    $subject = "U volgt de activiteit '$werkzaamheden'";
    $htmlbody = "
    <h1>U volgt de activiteit '$werkzaamheden'</h1>
    <p>Beste $voornaam $achternaam,<br />
    Op $nu bent u begonnen de activiteit '$werkzaamheden' te volgen.<br />
    Wanneer er iets gewijzigd wordt aan deze activiteit krijgt u een e-mail zoals deze.<br />
    Hieronder vindt u de link, die u anders ook zult krijgen, om naar de activiteit te gaan.<br />
    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
    $textbody = " 
    Beste $voornaam $achternaam,
    Op $nu bent u begonnen de activiteit '$werkzaamheden' te volgen.
    Wanneer er iets gewijzigd wordt aan deze activiteit krijgt u een e-mail zoals deze.
    Hieronder vindt u de link, die u anders ook zult krijgen, om naar de activiteit te gaan.
    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
    $mail = new PHPMailer();
    $mail->SetFrom($admin_email,$admin_email_naam);
    $mail->AddReplyTo($admin_email,$admin_email_naam); 
    $mail->AddAddress($info['email']);
                
    $mail->Subject = $subject;
    $mail->Body = $htmlbody;
    $mail->AltBody= $textbody;
    $mail->WordWrap = 50; 
    if(!$mail->Send()){
        $error = 'mailer error'.$mail->ErrorInfo;
    }                          
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/detail/activiteit-id='.$_GET['activiteit_id']);
}
elseif($_GET['action'] == 'nietvolgen'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_GET['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];    
    
    
    $table='planning_waarschuw_mij'; $where = 'gebruiker_id = '.$login_id;
    $waaschuw_mij_verwijderen = sqlDelete($table, $where);
              
    $what = 'voornaam, achternaam, email'; $from = 'relaties'; $where = 'id='.$_SESSION['relatie_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    $voornaam = $info['voornaam']; $achternaam = $info['achternaam'];
    $nu = strftime("%d %B %Y");
                    
    $subject = "U volgt de activiteit '$werkzaamheden' niet meer";
    $htmlbody = "
    <h1>U volgt de activiteit '$werkzaamheden' niet meer</h1>
    <p>Beste $voornaam $achternaam,<br />
    Op $nu bent u begonnen de activiteit '$werkzaamheden' niet meer te volgen.<br />
    Wanneer er iets gewijzigd wordt aan deze activiteit krijgt u geen e-mail meer.<br />
    Als dit niet de bedoeling is of u wilt de activiteit weer volgen, ga dan naar de onderstaande link om de activiteit wederom te volgen.<br />
    <a href=\"http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id']."\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
    $textbody = " 
    Beste $voornaam $achternaam,
    Op $nu bent u begonnen de activiteit '$werkzaamheden' niet meer te volgen.
    Wanneer er iets gewijzigd wordt aan deze activiteit krijgt u geen e-mail meer.
    Als dit niet de bedoeling is of u wilt de activiteit weer volgen, ga dan naar de onderstaande link om de activiteit wederom te volgen.
    http://".$site_name."planning/".$_GET['refer']."/detail/activiteit-id=".$_GET['activiteit_id'];
                                
    $mail = new PHPMailer();
    $mail->SetFrom($admin_email,$admin_email_naam);
    $mail->AddReplyTo($admin_email,$admin_email_naam); 
    $mail->AddAddress($info['email']);
                
    $mail->Subject = $subject;
    $mail->Body = $htmlbody;
    $mail->AltBody= $textbody;
    $mail->WordWrap = 50; 
    if(!$mail->Send()){
        $error = 'mailer error'.$mail->ErrorInfo;
    }                                             
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/detail/activiteit-id='.$_GET['activiteit_id']);
}
if($resetReacties == 1){
	$what="
    		b.id, b.inhoud, b.geschreven_door, DATE_FORMAT(b.geschreven_op, '%W %d %M %Y om %H:%i') AS geschreven_op, 
    		c.gebruikersnaam, c.voornaam, c.achternaam, c.profielfoto";
    if($detail == 'activiteit'){
        $from = 'planning_reactie a ';
        $where = 'a.activiteit = '.$_POST['activiteit_id'];
    }
    if($detail == 'project'){
        $from = 'project_reactie a ';
        $where = 'a.project = '.$_POST['project_id'];
    }
    
    $from .=  
    	   'LEFT JOIN portal_reactie AS b ON (b.id =  a.reactie)
            LEFT JOIN portal_gebruiker as c ON (c.id = b.geschreven_door)';
    $where .=' AND b.actief = 1 AND c.actief = 1
                        ORDER BY b.geschreven_op DESC';
    
    //echo "SELECT $what FROM $from WHERE $where";
    $aantal_reacties = countRows($what, $from, $where);     

			if($aantal_reacties > 0){
			    $result_reacties = sqlSelect($what, $from, $where);
				while($reactie = mysql_fetch_array($result_reacties)){
	                //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
	                $what = 'album, path'; $from = 'portal_image'; $where = 'id = '.$reactie['profielfoto'].' AND actief = 1';
	                	$profielfoto = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	                if($reactie['profielfoto'] != null){
                        if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'])){
	                        $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
						}else{
							$image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
						}
	                }else{
	                        $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
	                }
					if($i == 0){ $class= 'en_om'; $i = 1; }else{ $class= 'om'; $i = 0; }?>
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
                        		<a href="/profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel"><?php echo $reactie['voornaam'].' '.$reactie['achternaam'] ?></a>
                        	</span>
                        	<span class="datum"><?php echo $reactie['geschreven_op'] ?></span>                  
                        	<?php if($reactie['geschreven_door'] == $login_id){ //ALLEEN WIJZIGEN/VERWIJDEREN ALS DE REACTIE VAN DE GEBRUIKER IS ?>
                        		<span class="delete"> <img id="delete_reactie" src="<?php echo $etc_root; ?>functions/planning/css/images/delete.png" alt="delete" onclick="deleteReactie(<?php echo $reactie['id']; ?>)"> </span> 
                        		<span class="wijzig" data-id="<?php echo $reactie['id'] ?>"> <img src="/functions/planning/css/images/edit.png" alt="delete"> </span>               
                        	<?php } ?> 
                    	</p>
                	</div>                  
                	<div class="reactie_tekst">
                        <p><?php echo $reactie['inhoud']; ?></p>
                    </div>          
                </div>
        	</div>
				<?php }
			}
}
if($_GET['action'] == 'activiteitGezien'){
	$table = 'planning_recent'; $activiteit_id = $_GET['activiteit'];
    // Wanneer een activiteit op gezien = 0 staat... is het recent ;
    // En voor iedereen, behalve de wijzigaar zelf, is het nu recent ;)	
    $what = "id"; $where = 'activiteit = '.$activiteit_id.' AND gebruiker = '.$login_id;
    	$aantal = countRows($what, $table, $where);
	if($aantal <= 0){
		/*
		 * ER ZIJN NOG GEEN RECENTE GEBRUIKERS VOOR DEZE ACTIVITEIT. INSERT DE WIJZIGAAR
		 * */
		$what = 'activiteit, gebruiker, gezien, gezien_op';
		$with_what = "$activiteit_id, $login_id, 1, NOW()";
			$voor_het_eerst_gezien = sqlInsert($table, $what, $with_what);
	}else{
		/*
		 * DE WIJZIGAAR IS BEKEND BIJ DEZE ACTIVITEIT, ZET DE ACTIVITEIT OP GEZIEN
		 * */	
		$what = 'gezien = 1, gezien_op = NOW()';
		$where = 'activiteit = '.$activiteit_id.' AND gebruiker = '.$login_id; 
			$update_het = sqlUpdate($table, $what, $where);

	}
}elseif($_GET['action'] == 'alleActiviteitenGezien'){

	//variabelen, nodig voor dit stuk code
	$statussen = "'nog te plannen', 'to do', 'onderhanden', 'acceptatie'"; $table = 'planning_recent';
	
	// Haal eerst alle mogelijke activiteiten op
	$what = 'id'; $from = 'planning_activiteit'; $where = 'status IN ('.$statussen.') AND actief = 1';
		$activiteiten_res = sqlSelect($what, $from, $where);
	while($activiteit = mysql_fetch_array($activiteiten_res)){$activiteiten[] = $activiteit['id'];}		
	
	// Haal alle bekende items op
	$what = 'activiteit'; $where = 'gebruiker = '.$login_id;
		$bekende_activiteiten_res = sqlSelect($what, $table, $where);
	while($bekende_activiteit = mysql_fetch_array($bekende_activiteiten_res)){$bekende_activiteiten[] = $bekende_activiteit['activiteit'];}

	// Als er bekende activiteiten zijn, vergelijk dan de array's. Anders : insert alle activiteiten als array's
	if(count($bekende_activiteiten) > 0){
		// welke items komen niet voor in de recenten, maar wel in de activiteiten en andersom => inserten
		$insert_deze_1 = array_diff($activiteiten, $bekende_activiteiten);
		$insert_deze_2 = array_diff($bekende_activiteiten, $activiteiten);
		$insert_deze = array_merge($insert_deze_1, $insert_deze_2); 
		
		//welke items komen zowel voor in de recenten als in de activiteiten (en andersom) => updaten
		$update_deze_1 = array_intersect($activiteiten, $bekende_activiteiten);
		$update_deze_2 = array_intersect($bekende_activiteiten, $activiteiten);
		$update_deze = array_intersect($update_deze_1, $update_deze_2);  $aantal_updates = count($update_deze);
	}else{ $insert_deze = $activiteiten; }
	
	$aantal_inserts = count($insert_deze);
	
	if($aantal_inserts > 0){
		$insert_sql = "INSERT INTO $table (activiteit, gebruiker, gezien, gezien_op) VALUES "; $i = 1;
		foreach($insert_deze AS $id){ 
			if($i < $aantal_inserts){ $insert_sql .= "($id, $login_id, 1, NOW()),"; }  else{ $insert_sql .= "($id, $login_id, 1, NOW())"; }  $i++;
		} mysql_query($insert_sql) or die(mysql_error());
	}
	
	if($aantal_updates > 0){
		$update_sql = "UPDATE $table SET gezien = 1, gezien_op = NOW() WHERE activiteit IN ( "; $i = 1; 
		foreach($update_deze AS $id){ 
			if($i < $aantal_updates){ $update_sql .= "$id, "; }  else{ $update_sql .= "$id)"; }  $i++;
		} mysql_query($update_sql) or die(mysql_error());
	}
	
}
elseif($_GET['action'] == 'getRecenteMeldingen'){ echo recenteWijziging($_GET['activiteit'], $login_id); }
elseif($_GET['action'] == 'showReactie'){
	$id = $_GET['activiteit'];
	$what_reacties="
    		b.id, b.inhoud, b.geschreven_door, DATE_FORMAT(b.geschreven_op, '%W %d %M %Y om %H:%i') AS geschreven_op, 
    		c.id AS gebruikersid, c.gebruikersnaam, c.voornaam, c.achternaam";
    $from_reacties=" 
    		planning_reactie a
    		LEFT JOIN portal_reactie AS b ON (b.id =  a.reactie)
            LEFT JOIN portal_gebruiker as c ON (c.id = b.geschreven_door)";
    $where_reacties="a.activiteit = $id
            AND b.actief = 1 AND c.actief = 1
            ORDER BY b.geschreven_op DESC";
		$laatste_reactie = mysql_fetch_assoc( sqlSelect( $what_reacties, $from_reacties, $where_reacties ) );
	
	$gebruiker_id = $laatste_reactie['gebruikersid'];

	$what_profielfoto = 'b.path, b.album';
	$from_profielfoto = 'portal_gebruiker a LEFT JOIN portal_image AS b ON (b.id = a.profielfoto)';
	$where_profielfoto = "a.id = $gebruiker_id AND a.actief = 1 AND b.actief = 1";
		//echo "SELECT $what_profielfoto FROM $from_profielfoto WHERE $where_profielfoto";
		$profielfoto = mysql_fetch_assoc(sqlSelect( $what_profielfoto, $from_profielfoto, $where_profielfoto ));		
		
	if($profielfoto['path'] != null){
		if(is_file($_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'])){
			$image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/'.$profielfoto['album'].'/'.$profielfoto['path'].'" alt="de profielfoto" title="bekijk het profiel" />';
		}else{//de profielfoto bestaat niet
			$image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
			//aangezien de afbeelding niet meer bestaat, moet er ook eigenlijk geen koppeling meer zijn !
			$ontkoppel_foto_gebruiker = sqlUpdate('portal_gebruiker', 'profielfoto = 0', "id = $gebruiker_id");
		}
	}else{
		$image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
	}       
	
	echo '<div class="reactie">
            <div class="reactie_links">
                <div class="reactie_foto">
                    <div class="reactie_profiel_pic">'
        				.$image.
        				'<div class="reactie_profiel_pic_bottom">&nbsp;</div>
                    </div>
                </div>
            </div>
            <div class="reactie_rechts">
                <div class="reactie_header">
                    <p>
                    <span class="auteur"><a href="'.$etc_root.'profiel/'.$laatste_reactie['gebruikersnaam'].'" title="bekijk het profiel">'.$laatste_reactie['voornaam'].' '.$laatste_reactie['achternaam'].'</a></span>
                    <span class="datum">'.$laatste_reactie['geschreven_op'].'</span>
                    </p>
                </div>
                <div class="reactie_tekst">
                    <p>'.$laatste_reactie['inhoud'].'</p>
                </div>
            </div>
         </div>';
}
elseif($_GET['action'] == 'displayAlleActiviteitenGezien'){
	$iteratie_id = $_GET['iteratie_id'];
	
	$what="	COUNT( id ) AS aantal_activiteiten"; 
	$from=" planning_activiteit ";  
	$where="actief = 1 AND iteratie = $iteratie_id AND status IN ( 'to do',  'onderhanden',  'acceptatie' )";
	$iteratie = mysql_fetch_assoc(sqlSelect($what,$from,$where));
	
	if(getRecenten($login_id, $iteratie_id)){ $aantal_recenten = mysql_numrows(getRecenten($login_id, $iteratie_id)); }

	if($aantal_recenten < $iteratie['aantal_activiteiten']){ ?>
	<img src="/functions/planning/css/images/remove_recent.png" alt="zet de recenten uit" title="Klik hier om de recentmeldingen uit te zetten."  />
	<?php }else{ echo  '&nbsp;'; }
}
?>
