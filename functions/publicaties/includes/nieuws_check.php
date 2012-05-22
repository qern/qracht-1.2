<?php
session_start();
//laadt de nodige bestanden
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');

//Moet er een nieuwe mededeling worden aangemaakt ?
if($_POST['action'] == 'aanmaken'){  
    
    if($_POST['titel'] == null){ $error['lege_titel'] = 'U dient een titel in te voeren';}
    else{$titel = mysql_real_escape_string($_POST['titel']); $_SESSION['titel'] = $titel;}
    
    if($_POST['belangrijk'] == null){ $belangrijk = 0; }
    else{$belangrijk = $_POST['belangrijk']; $_SESSION['belangrijk'] = $belangrijk;}
    
    if($_POST['teaser'] == null){$error['teaser'] = 'U dient een teasertekst in te voeren'; }
    else{$teaser = mysql_real_escape_string(htmlspecialchars($_POST['teaser'])); $_SESSION['teaser'] = $teaser;}
    
    if($_POST['inhoud'] == null){ $error['inhoud'] = 'U dient een inhoud in te voeren'; }
    else{$inhoud = mysql_real_escape_string($_POST['inhoud']); $_SESSION['inhoud'] = $inhoud;}
    
    if($_POST['publicatiedatum'] == null){ $publicatiedatum = strftime('%d-%m-%Y'); }
    else{$publicatiedatum = $_POST['publicatiedatum']; $_SESSION['publicatiedatum'] = $publicatiedatum;}
    
    if($_POST['archiveerdatum'] == null){$archiveerdatum = '00-00-00'; }
    else{$archiveerdatum = "STR_TO_DATE('".$_POST['archiveerdatum']."','%d-%m-%Y')"; $_SESSION['archiveerdatum'] = $archiveerdatum;}
	
    if($_FILES['file'] != null){
        if (is_uploaded_file($_FILES['file']['tmp_name'])){
        # Haal de juiste functies op
        # Zie het bestand voor instructies en/of bronverwijzingen
        require($_SERVER['DOCUMENT_ROOT'].$etc_root.'lib/afbeelding_uploaden.php');
      
        # upload de afbeeling naar de juiste plek
        # De bestandslocatie wordt geconfigureerd in  
        # de configuration.php
        $img_ff = 'file';     // Formulierveld naam
        $dst_img = strtolower($_FILES[$img_ff]['name']); // De naam die we de afbeelding geven (de originele naam in kleine letters).
        $dst_path = $siteroot.'files/nieuws_afbeelding/'; // De bestandslocatie.

        # UPLOADEN!
        # Deze functie cree�rt o.a. de variabele $afbeelding met de bestandsnaam
        uploadImage($img_ff, $dst_path, $dst_img);    
                
        }elseif ($_POST['afbeelding_aanwezig'] != NULL) {
            # Er is al een afbeelding aanwezig
            $afbeelding = $_POST['afbeelding_aanwezig'];
        }else {
            # Anders een verwijzing naar het no_image bestand
        }
    }
    
    if(!$error){
        $table = 'portal_nieuws';
        $what = 'titel, teaser, inhoud, afbeelding, is_belangrijk, publicatiedatum, archiveerdatum, geschreven_door, geschreven_op, laatste_wijziging, update_datum';
        $with_what = "'$titel', '$teaser', '$inhoud', '$afbeelding', $belangrijk, STR_TO_DATE('$publicatiedatum','%d-%m-%Y'), $archiveerdatum, $login_id, NOW(), NOW(), NOW()";
            $nieuwe_mededeling = sqlInsert($table, $what, $with_what);
			
		//pak nu het id van de nieuwste mededeling, anders moet deze query per loop gedaan worden.
        $what = 'MAX(id) AS id'; $from = 'portal_nieuws'; $where = 'actief = 1';
            $nieuws = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $nieuws_id = $nieuws['id'];
        
        if(count($_POST['bedoeld_voor']) > 0){
            //nu per aangeklikte organisatie een record hiervoor aanmaken.
            foreach($_POST['bedoeld_voor'] as $gebruiker_id => $organisatie_naam){
                $table = 'portal_nieuws_gebruiker';
                $what = 'nieuws, gebruiker';
                $with_what =  "$nieuws_id, $gebruiker_id";
                    $nieuwe_mededeling_koppelen_aan_organisatie = sqlInsert($table, $what, $with_what);
            }
        }
        header('location: '.$site_name.'publicaties/wijzigen/nieuws-id='.$nieuws_id);
    }else{
        $_SESSION['error'] = $error;
        header('location: '.$site_name.'publicaties/toevoegen');
    }
    
}
//moet er een mededeling worden geijwizingd.
elseif($_POST['action'] == 'wijzigen'){
    $nieuws_id = $_POST['nieuws_id'];

    if($_POST['titel'] == null){ $error['lege_titel'] = 'U dient een titel in te voeren';}
    else{$titel = mysql_real_escape_string($_POST['titel']); $_SESSION['titel'] = $titel;}
    
    if($_POST['belangrijk'] == null){ $belangrijk = 0; }
    else{$belangrijk = $_POST['belangrijk']; $_SESSION['belangrijk'] = $belangrijk;}
    
    if($_POST['teaser'] == null){$error['teaser'] = 'U dient een teasertekst in te voeren'; }
    else{$teaser = mysql_real_escape_string(htmlspecialchars($_POST['teaser'])); $_SESSION['teaser'] = $teaser;}
    
    if($_POST['inhoud'] == null){ $error['inhoud'] = 'U dient een inhoud in te voeren'; }
    else{$inhoud = mysql_real_escape_string($_POST['inhoud']); $_SESSION['inhoud'] = $inhoud;}
    
    if($_POST['publicatiedatum'] == null){ $publicatiedatum = strftime('%d-%m-%Y'); }
    else{$publicatiedatum = $_POST['publicatiedatum']; $_SESSION['publicatiedatum'] = $publicatiedatum;}
    
    if($_POST['archiveerdatum'] == null){$archiveerdatum = '00-00-00'; }
    else{$archiveerdatum = "STR_TO_DATE('".$_POST['archiveerdatum']."','%d-%m-%Y')"; $_SESSION['archiveerdatum'] = $archiveerdatum;}
    
    if (is_uploaded_file($_FILES['file']['tmp_name'])){
    # Haal de juiste functies op
    # Zie het bestand voor instructies en/of bronverwijzingen
    include($siteroot.'lib/afbeelding_uploaden.php');
      
        # upload de afbeeling naar de juiste plek
        # De bestandslocatie wordt geconfigureerd in 
        # de configuration.php
        $img_ff = 'file';     // Formulierveld naam
        $dst_img = strtolower($_FILES[$img_ff]['name']); // De naam die we de afbeelding geven (de originele naam in kleine letters).
        $dst_path = $siteroot.'files/nieuws_afbeelding/'; // De bestandslocatie.
        # UPLOADEN!
        # Deze functie cree�rt o.a. de variabele $afbeelding met de bestandsnaam
        uploadImage($img_ff, $dst_path, $dst_img);    
    }elseif ($_POST['afbeelding_aanwezig'] != NULL) {
        # Er is al een afbeelding aanwezig
        $afbeelding = $_POST['afbeelding_aanwezig'];
        }else {
            # Anders een verwijzing naar het no_image bestand
           
            }
    
    if(!$error){
        $table = 'portal_nieuws';
        $what = "titel = '$titel', 
                 teaser = '$teaser', 
                 inhoud = '$inhoud', 
                 afbeelding = '$afbeelding',
                 is_belangrijk = $belangrijk,
                 publicatiedatum = STR_TO_DATE('$publicatiedatum','%d-%m-%Y'), 
                 archiveerdatum = $archiveerdatum, 
                 laatste_wijziging = NOW(),
                 update_datum = NOW()";
        $where = "id = $nieuws_id";
        
        //echo $what;
            $nieuws_updaten = sqlUpdate($table, $what, $where);
        //echo "UPDATE $table SET $what WHERE $where".'<br />';
        //eerst alle entries verwijderen van koppelingen tussen dit nieuws en een organisatie.
        //wat er doorgestuurd is, is recent. Eerdere koppelingen niet meer.
        $table="portal_nieuws_gebruiker"; $where="nieuws = $nieuws_id";
            $verwijder_koppelingen = sqlDelete($table, $where);
        
        //nu per aangeklikte organisatie een record hiervoor aanmaken.
        foreach($_POST['bedoeld_voor'] as $gebruiker_id => $organisatie_naam){
            $table = 'portal_nieuws_gebruiker';
            $what = 'nieuws, gebruiker';
            $with_what =  "$nieuws_id, $gebruiker_id";
                $nieuws_koppelen_aan_organisatie = sqlInsert($table, $what, $with_what);
            //echo "INSERT INTO $table ($what) VALUES $where".'<br />';
        }
    }else{
        $_SESSION['error'] = $error;
    }
   header('location: '.$site_name."publicaties/wijzigen/nieuws-id=$nieuws_id");
    
}
if($_POST['action'] == 'tag'){
    $tag = $_POST['tag'];
    $what = 'DISTINCT id'; $from = 'portal_tag'; $where='id > 1';
    $aantal_tags = countRows($what, $from, $where);
    if($aantal_tags > 0){
                //hoeveel tags zijn er met deze tagnaam, tel ze
                $what = 'DISTINCT a.id, b.nieuws'; $from = 'portal_tag a, portal_nieuws_tag b'; $where="a.naam = '$tag' AND b.tag = a.id";
                $aantal_gekoppelde_tags = countRows($what, $from, $where);
                
                //is het er 1 of meer, dan kijken of het een bekende is. zo niet: toevoegen
                //niet bekend ? toevoegen.
                if($aantal_gekoppelde_tags > 0){
                    $gekoppelde_tag = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
                    if($_POST['nieuws'] == $gekoppelde_tag['nieuws']){
                        //doe niets... want de kennis is al bekend bij deze gebruiker
                        //echo '<div class="wijzig_info_success">Deze tag was al ingevoerd</div>';
                    }else{
                        //aangezien de ingevoerde kennis wel bestaat, maar (nog) niet is gekoppeld aan deze gebruiker.
                        //doen we dat nu !
                        $table = 'portal_nieuws_tag'; $what = 'tag, nieuws';
                        $with_what = $gekoppelde_tag['id'].', '.$_POST['nieuws'];
                            $tag_koppelen = sqlInsert($table,$what,$with_what);
                        //echo '<div class="wijzig_info_success">U hebt met succes de tag toegevoegd</div>';
                    }
                }else{
                    //aangezien de ingevoerde tag (nog) niet bestaat, maken we deze aan.
                    $table = 'portal_tag'; $what = 'naam';
                    $with_what = "'$tag'";
                        $tag_toevoegen = sqlInsert($table,$what,$with_what);
                    //echo $with_what;
                    //ook koppelen we deze nieuwe kennis aan de activiteit.
                    $table = 'portal_nieuws_tag'; $what = 'tag, nieuws';
                    $with_what = '(SELECT MAX(a.id) FROM portal_tag a WHERE id > 0), '.$_POST['nieuws'];
                        $tag_koppelen = sqlInsert($table,$what,$with_what);      
                    //echo '<div class="wijzig_info_success">U hebt met succes de tag toegevoegd</div>';  
                }
        }else{
            //er bestaan nog geen tags.. dan doen we dit
             //aangezien de ingevoerde tag (nog) niet bestaat, maken we deze aan.
             $table = 'portal_tag'; $what = 'naam';
             $with_what = "'$tag'";
                $tag_toevoegen = sqlInsert($table,$what,$with_what);
             //echo $with_what;       
             //ook koppelen we deze nieuwe kennis aan de activiteit.
             $table = 'portal_nieuws_tag'; $what = 'tag, nieuws';
             $with_what = '(SELECT MAX(a.id) FROM portal_tag a WHERE id > 0), '.$_POST['nieuws'];
                $tag_koppelen = sqlInsert($table,$what,$with_what); 
             //echo '<div class="wijzig_info_success">U hebt met succes de tag toegevoegd</div>';
        }
        $refreshTags = 1; $nieuws = $_POST['nieuws'];
}
if($_GET['action'] == 'delete_tag'){
    $table = 'portal_nieuws_tag';
    $where = 'nieuws = '.$_GET['nieuws'].' AND tag = '.$_GET['tag'];
        $verwijder_tag = sqlDelete($table, $where);
        
    $refreshTags = 1; $nieuws = $_GET['nieuws'];
}

if($refreshTags == 1){
    //tag(s) ophalen
    $what_tags = 'b.id, b.naam';
    $from_tags = 'portal_nieuws_tag a, portal_tag b';
    $where_tags = "a.nieuws = $nieuws AND b.id = a.tag";
      $aantal_tags = countRows($what_tags, $from_tags, $where_tags);  $result_tags = sqlSelect($what_tags, $from_tags ,$where_tags);
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
        foreach($tag_list as $tag){
            echo $tag;
        }
    }else{
        echo 'er zijn nog geen tags voor dit nieuwsitem';
    }
}
?>
