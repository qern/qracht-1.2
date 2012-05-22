<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    //is er een registratie_id meegegeven? dan moet er geüpdate worden ipv geinsert.
if(isset($_POST['registratie_id'])){    
    //check hoeveel bedrijven er zijn met de verstuurde naam
    $what = 'a.naam';   $from = 'organisatie a, urentool_registratie b';  $where = 'b.id = '.$_POST['registratie_id'].' AND a.id = b.organisatie_id';
    $bedrijfs = mysql_fetch_assoc(sqlSelect($what,$from,$where));
    

    //check werkzaamheden en maak deze veiliger voor de database
    if($_POST['werkzaamheden'] != null){
        $werkzaamheden = htmlentities($_POST['werkzaamheden'], ENT_NOQUOTES, "UTF-8");
        $werkzaamheden = nl2br($werkzaamheden);
        $werkzaamheden = mysql_real_escape_string($werkzaamheden);
    }else{$error['werkzaamheden_fout'] = 'u hebt uw werkzaamheden niet (correct) ingevuld';}
     
    //check het aantal uur
    if($_POST['aantal_uur'] != null){$aantal_uur = str_replace(',', '.', $_POST['aantal_uur']);
    }else{$error['aantal_uurfout'] = 'u hebt het aantal gewerkte uren niet (correct) ingevuld';}
    
    //geen fouten ? go for it.
    if(!$error){
        
        $table = "urentool_registratie";
        $what = "werkzaamheden = '$werkzaamheden', aantal_uur = '$aantal_uur', gewijzigd_op = NOW(), gewijzigd_door = $login_id ";
        $where = 'id = '.$_POST['registratie_id'];
            
            $update_deze_registratie  = sqlUpdate($table,$what,$where);   //insert ze maar
            
        $_SESSION['success'] = "<p>U hebt <b>$aantal_uur uur </b> ingevuld voor <b>".$bedrijfs['naam']."</b>, als u geen data meer hebt in te vullen voor deze dag, druk dan op 'gereed' om de dag af te sluiten!</p>";
    }else{$_SESSION['error'] = $error;} //als er fouten zijn, stop deze in de sessie, die komen straks wel.
    
    header("location: ".$site_name."urentool/".$_POST['datum']); //ga terug. Wat er ook uitgekomen is, fouten of niet.      
}
    //is er een datum doorgegeven, ofwel... zijn er uren ingevuld?
elseif(isset($_POST['datum'])){    
    //check hoeveel bedrijven er zijn met de verstuurde naam
    $what = 'id';   $from = 'organisatie';  $where = "naam = '".$_POST['bedrijf']."'";
    $rijen = countRows($what,$from,$where);
    
    //check nu alle dingen aan bedrijf. Hoeveel zijn er hierboven uit gekomen ? Is het er 1 pak dan het organisatie_id        
    if($_POST['bedrijf'] != null){
        if($rijen == '0'){$error['bedrijf_teweinig'] = 'er is geen bedrijf bekend met die naam.';}
        elseif($rijen >= 2){$error['bedrijf_teveel'] = 'er zijn meerdere bedrijven bekend met die naam.';}
        else{$bedrijf = $_POST['bedrijf']; $row = mysql_fetch_assoc(sqlSelect($what,$from,$where)); $organisatie_id = $row['id'];}
    }else{$error['bedrijf_fout'] = 'u hebt het bedrijf niet (correct) ingevuld';}

    //check werkzaamheden en maak deze veiliger voor de database
    if($_POST['werkzaamheden'] != null){
        $werkzaamheden = htmlentities($_POST['werkzaamheden'], ENT_NOQUOTES, "UTF-8");
        $werkzaamheden = nl2br($werkzaamheden);
        $werkzaamheden = mysql_real_escape_string($werkzaamheden);
    }else{$error['werkzaamheden_fout'] = 'u hebt uw werkzaamheden niet (correct) ingevuld';}
     
    //check het aantal uur
    if($_POST['aantal_uur'] != null){$aantal_uur = str_replace(',', '.', $_POST['aantal_uur']);
    }else{$error['aantal_uurfout'] = 'u hebt het aantal gewerkte uren niet (correct) ingevuld';}
    
    //geen fouten ? go for it.
    if(!$error){
        list($dag, $maand, $jaar) = explode('-', $_POST['datum']);
        $what="id"; $from="urentool_datums";$where="dag=$dag AND maand = $maand AND jaar = $jaar AND gebruiker_id = $login_id"; 
        $datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
        if($_POST['activiteit'] == null){
            $activiteit = '0';
        }elseif($_POST['activiteit'] < 1){
            $activiteit = '0';
        }else{
            $activiteit = $_POST['activiteit'];
        }
        
        $table = "urentool_registratie";
        $what = "urentool_datums_id, organisatie_id, planning_activiteit_id, categorie, werkzaamheden, aantal_uur, gewijzigd_op, gewijzigd_door ";
        $with_what = $datum['id'].", $organisatie_id, $activiteit, '".$_POST['categorie']."', '$werkzaamheden', '$aantal_uur', NOW(),'$login_id' ";
            
            $insert_nieuwe_uren = sqlInsert($table,$what,$with_what);   //insert ze maar
            
        $_SESSION['success'] = "<p>U hebt <b>$aantal_uur uur </b> ingevuld voor <b>$bedrijf</b>, als u geen data meer hebt in te vullen voor deze dag, druk dan op 'gereed' om de dag af te sluiten!</p>";
    }else{$_SESSION['error'] = $error;} //als er fouten zijn, stop deze in de sessie, die komen straks wel.
    
    header("location: ".$site_name."urentool/".$_POST['datum']); //ga terug. Wat er ook uitgekomen is, fouten of niet.      
}
//moet er een registratie verwijderd worden ?
elseif($_GET['action'] == 'delete'){
    //doe dat dan maar
    $table="urentool_registratie";  $where="id = ".$_GET['registratie_id'];
        $delete_registratie = sqlDelete($table, $where);
    
    header("location: ".$site_name."urentool/".$_GET['datum']);
}
else{header("location: ".$site_name."inloggen.php");} //als je niet een datum hebt ingevuld, heb je hier niets te zoeken.

?>