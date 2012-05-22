<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
//heel belangrijk: het profiel_id wordt gepakt zodat het overal gebruikt kan worden
$profiel_id = $_POST['profiel_id'];
//vindt een collega een andere collega 'leuk ?'
if($_GET['action'] == 'leuke_collega'){
    $profiel_id = $_GET['id'];
    $what = "id";
    $from = "profiel_leuke_collega";
    $where = "collega_id = $login_id
              AND profiel_id = $profiel_id";
    $aantal_collegas = countRows($what,$from,$where);
    
    if($aantal_collegas <= 0){
    $table="profiel_leuke_collega";
    $what = "profiel_id, collega_id, toegevoegd_op";
    $with_what = "$profiel_id, $login_id, NOW()";
    $gebruiker_vindt_collega_leuk = sqlInsert($table, $what, $with_what);  
    header("location: ".$site_name."profiel/profiel_id=$profiel_id");
    }  
}
//Moet er persoonlijke info worden toegevoegd/aangepast ? :
elseif($_GET['action'] == 'info'){
//pak de gegevens uit het formulier en plemp deze in de tabellen.
//eerst de profiel tabel
$table = "profiel";
$what  ="profieltekst ='".$_POST['profieltekst']."', ";
$what .="twitter ='".$_POST['twitter']."', ";
$what .="facebook ='".$_POST['facebook']."', ";
$what .="linkedin ='".$_POST['linkedin']."', ";
$what .="skype ='".$_POST['skype']."', " ;
$what .="hyves ='".$_POST['hyves']."', ";
$what .="youtube ='".$_POST['youtube']."', ";
$what .="gewijzigd_op = NOW(), ";
$what .="gewijzigd_door = $login_id";
$where = "id = $profiel_id";
$update_profiel = sqlUpdate($table,$what,$where);

// dan de gebruiker tabel
$table = "gebruiker";
$what  ="qwetternaam = '".$_POST['qwetternaam']."',";
$what .="aangemaakt_op = NOW(),";
$what .="aangemaakt_door = $login_id";
$where  ="id = $login_id";
$update_gebruiker = sqlUpdate($table,$what,$where);

//als laatste de relaties tabel 
$table = "relaties";
$what  = "email = '".$_POST['email']."',";
$what .= "mobiel = '".$_POST['mobiel']."',";
$what .= "plaats = '".$_POST['plaats']."',";
$what .= "aangemaakt_op = NOW(), ";
$what .= "aangemaakt_door = $login_id";
$where = "id = $relatie_id";
$update_relatie = sqlUpdate($table,$what,$where);

header("location: ".$site_name."profiel/wijzigen/info");
$_SESSION['succes_info'] = "u hebt met succes uw persoonlijke informatie gewijzigd";
}
//Moet er links worden toegevoegd/aangepast ? :
elseif($_GET['action'] == 'links'){
//eerst de oude links allemaal verwijderen
$table="profiel_links";
$where="profiel_id = $profiel_id";
$delete_all = sqlDelete($table, $where);

//als link_url (een bestaande dus) gevuld is:
if($_POST['link_url'] != null){
    $url_compleet = array_combine($_POST['link_url'], $_POST['link_omschrijving']); //combineer de 2 gegevens
    foreach ($url_compleet as $url => $omschrijving) {
        //check of de link meer dan 1 letter heeft. Dan worden niet lege links in de database geschreven!
        if(strlen($url) > 1){   
            //zit er al een http in de link ? ja -> niets doen. nee -> zet deze ervoor.
            $link_vergelijk = strpos($url, 'http://'); 
            if($link_vergelijk !== false){ $url = mysql_real_escape_string($url);}
            else{$url = mysql_real_escape_string($url); $url = 'http://'.$url;}
                    
            $omschrijving = mysql_real_escape_string($omschrijving);    //zorg ervoor dat ' & ", etc. gewoon werken
            $table_url="profiel_links";
            $what_url="profiel_id, url, omschrijving, toegevoegd_op ";
            $with_what_url = "$profiel_id, '$url', '$omschrijving', NOW()";
            $url_huidig_insert = sqlInsert($table_url,$what_url,$with_what_url); //insert ze daarna in de database
        }
    }
}
//als link_url_nieuw (een nieuwe dus) gevuld is:
if($_POST['link_url_nieuw'] != null){
    
    $url_compleet_nieuw = array_combine($_POST['link_url_nieuw'], $_POST['link_omschrijving_nieuw']); //combineer de 2 gegevens
    foreach ($url_compleet_nieuw as $url_nieuw => $omschrijving_nieuw) {
        
        //check of de link meer dan 1 letter heeft. Dan worden niet lege links in de database geschreven!
        if(strlen($url_nieuw) > 1){
            //zit er al een http in de link ? ja -> niets doen. nee -> zet deze ervoor.
            $link_nieuw_vergelijk = strpos($url_nieuw, 'http://'); 
            if($link_nieuw_vergelijk !== false){ $url_nieuw = mysql_real_escape_string($url_nieuw);}
            else{$url_nieuw = mysql_real_escape_string($url_nieuw); $url_nieuw = 'http://'.$url_nieuw;}
                    
            $omschrijving_nieuw = mysql_real_escape_string($omschrijving_nieuw);    //zorg ervoor dat ' & ", etc. gewoon werken
            $table_url="profiel_links";
            $what_url="profiel_id, url, omschrijving, toegevoegd_op ";
            $with_what_url = "$profiel_id, '$url_nieuw', '$omschrijving_nieuw', NOW()";
            $url_nieuw_insert = sqlInsert($table_url,$what_url,$with_what_url);  //insert ze daarna in de database
        }
    }
}
    //Als er een nieuwe url is ingevuld en gepost
    header("location: ".$site_name."profiel/wijzigen/links");
    $_SESSION['succes_links'] = "u hebt met succes uw links gewijzigd";
}
//Moet er een foto worden toegevoegd/aangepast ? :
elseif($_GET['action'] == 'foto'){
    if(isset($_GET['consequence'])){
        $foto_id = $_GET['id'];
        $profiel_id = $_GET['profiel_id'];
        if($_GET['consequence'] == 'profielfoto'){
            $table = "profiel";
            $what = "profielfoto_id = $foto_id";
            $where = "id = $profiel_id";
            $change_profile_pic = sqlUpdate($table, $what, $where);
        }
        elseif($_GET['consequence'] == 'deletion'){
            $what = "foto, thumbnail";   $from = "profiel_fotoalbum";   $where = "id = $foto_id";
            $result = sqlSelect($what, $from, $where); $row = mysql_fetch_assoc($result);        //haal de foto- en thumnailnaam op 
            
            unlink($_SERVER['DOCUMENT_ROOT'].'/files/profiel_foto/'.$row['foto']);          //verwijder de foto van de server
            unlink($_SERVER['DOCUMENT_ROOT'].'/files/profiel_foto/'.$row['thumbnail']);     //verwijder de thumbnail van de server
            
            $table = "profiel_fotoalbum";
            $where = "id = $foto_id";
            $delete_pic = sqlDelete($table, $where);
        }
    }
    if($_POST['caption'] != null){
        foreach($_POST['caption'] as $id => $caption){
        $caption = mysql_real_escape_string($caption);
        $table = "profiel_fotoalbum";
        $what  = "caption = '$caption',";
        $what .= "toegevoegd_op = NOW(), toegevoegd_door = $login_id";
        $where = "id = $id";
        $update_foto_caption = sqlUpdate($table,$what,$where);
        }
    }
    //zijn er nieuwe foto's ?
    if($_FILES['foto_nieuw'] != null){
        
        $foto_gegevens = array_combine($_FILES['foto_nieuw']['name'], $_FILES['foto_nieuw']['tmp_name']);   //combineer de naam en temp naam in een array.
        
       foreach($foto_gegevens as $naam => $tempnaam){
           if($naam != null){
               createImages($_SESSION['login_id'], $naam, $tempnaam);                                           //per rij, maak de foto's. Een grote en een thumb van +/- 130:90.
           }
       }

        $foto_compleet = array_combine($_FILES['foto_nieuw']['name'], $_POST['caption_nieuw']);             //combineer de caption en de naam van de foto    
        foreach($foto_compleet as $foto => $caption){
         $foto = $_SESSION['login_id']."/$foto";
         $table = "profiel_fotoalbum";
         $caption = mysql_real_escape_string($caption);
         $what = "profiel_id, foto, caption, toegevoegd_op, toegevoegd_door";
         $with_what = "$profiel_id, '$foto', '$caption', NOW(), $login_id";
         $insert_foto = sqlInsert($table, $what, $with_what);                                              //Deze worden in de database gezet.
        }
    }
    header("location: ".$site_name."profiel/wijzigen/foto");
    $_SESSION['succes_foto'] = "u hebt met succes uw foto's gewijzigd";
}
//Moet er kennis worden toegevoegd/aangepast ? :
elseif($_GET['action'] == 'kennis'){    
    //voor elke term, wordt een database actie uitgevoerd.
    if($_POST['term'] != null){
        $table="kenniskaart";
        $where="profiel_id = $profiel_id";
        $delete_all = sqlDelete($table, $where);
        foreach($_POST["term"] as $term){
            //Als de term leeg is... delete deze rij dan ook direct.
            if($term != null){
                //Als de term is gevuld, real escape de term. Voor de ' en de "...
                //update de datbase
                $term = mysql_real_escape_string($term);
                $table_kennis="kenniskaart";
                $what_kennis="profiel_id, term, toegevoegd_op";
                $with_what_kennis="$profiel_id, '$term', NOW()";
                $kennis_insert = sqlInsert($table_kennis,$what_kennis,$with_what_kennis);
            }
        }
    }
    //Als er een nieuwe term is ingevuld en gepost
    if($_POST['term_nieuw'] != null){
            
        //voor elke nieuwe post, wanneer deze niet leeg is!, insert de real escapede term.
        foreach($_POST["term_nieuw"] as $term_nieuw){
            if($term_nieuw != null){
                $term_nieuw = mysql_real_escape_string($term_nieuw);
                $table_kennis="kenniskaart";
                $what_kennis="profiel_id, term, toegevoegd_op";
                $with_what_kennis="$profiel_id, '$term_nieuw', NOW()";
                $kennis_insert = sqlInsert($table_kennis,$what_kennis,$with_what_kennis);
            }
        }            
    }
    header("location: ".$site_name."profiel/wijzigen/kennis");
    $_SESSION['succes_kennis'] = "u hebt met succes uw kenniskaart gewijzigd";
}
elseif($_GET['action'] == 'delete_reactie'){
    $table='portal_reactie'; 
    $what="actief = 0";
    $where = 'id = '.$_GET['reactie_id'];
            $zet_reactie_op_nonactief = sqlUpdate($table, $what, $where);
              
    header('location: '.$site_name.'profiel/'.$_GET['profiel']);
}
 ?>