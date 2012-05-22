<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
if(!$_POST['action']){
    require($_SERVER['DOCUMENT_ROOT'].$etc_root.'includes/ajax_functions.php');
    $org_result = array();
    $what = 'id'; $from = 'planning_iteratie'; $where = 'huidige_iteratie = 1';
        $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $huidige_iteratie = $iteratie['id'];
    $term = trim(strip_tags($_GET['term']));                                                    //retrieve the search term that autocomplete sends
    if($_GET['filter'] == 'organisatie'){
        $qstring = "SELECT  c.id, c.naam 
                    FROM
                            planning_activiteit a 
                            LEFT JOIN project AS b ON (b.id = a.project)
                            LEFT JOIN organisatie AS c ON (c.id = b.organisatie)
                    WHERE   a.actief = 1 AND a.iteratie = $huidige_iteratie AND b.actief = 1 AND c.naam LIKE '%$term%'";
    }
    
    elseif($_GET['filter'] == 'project'){
        if(stripos($_GET['organisatie'], '_') !== false){
            $clean_organisaties = explode('_', $_GET['organisatie']); 
            $organisaties = 'IN ('; $i = 0;
            foreach($clean_organisaties AS $organisatie){
                 if($i == 0){$organisaties .= $organisatie;} else{$organisaties .= ', '.$organisatie;} $i++;  
            }
            $organisaties .= ')';//$organisaties = str_ireplace(',)', ' )', $organisaties);
        }
        elseif($_GET['organisatie'] != null){$organisaties =  '= '.$_GET['organisatie'];}
        else{$organisaties = '> 0';}
        $qstring = "SELECT  b.id, b.titel AS naam 
                    FROM    planning_activiteit a
                            LEFT JOIN project AS b ON (b.id = a.project) 
                    WHERE   a.actief = 1  AND a.iteratie = $huidige_iteratie AND b.organisatie $organisaties AND b.titel LIKE '%$term%'";
    }
    
    elseif($_GET['filter'] == 'competentie'){
        if(stripos($_GET['project'], '_') !== false){
            $clean_projecten = explode('_', $_GET['project']);
            $projecten = 'IN (';  $i = 0;
            foreach($clean_projecten AS $project){
                if($i == 0){$projecten .= $project;} else{$projecten .= ', '.$project;} $i++;
            }
            $projecten .= ')'; //$projecten = str_ireplace(',)', ')'); 
        }
        elseif($_GET['project'] != null){$projecten =  '= '.$_GET['project'];}
        else{$projecten = '> 0';}
        $qstring = "SELECT b.id, b.competentie AS naam 
                    FROM   planning_activiteit a 
                           LEFT JOIN competentie AS b ON (b.id = a.competentie) 
                    WHERE  a.actief = 1 AND a.iteratie = $huidige_iteratie AND a.project $projecten AND b.competentie LIKE '%$term%'";        
    }
    
    elseif($_GET['filter'] == 'medewerker'){
        if(stripos($_GET['competentie'], '_') !== false){
            $clean_competenties = explode('_', $_GET['competentie']);                 
            $competenties = 'IN ('; $i = 0;
            foreach($clean_competenties AS $competentie){
                if($i == 0){$competenties .= $competentie;} else{$competenties .= ', '.$competentie;} $i++;
            }
            $competenties .= ')'; //$competenties = str_ireplace(',)', ')'); 
        }elseif($_GET['competentie'] != null){$competenties =  '= '.$_GET['competentie'];}
        else{$competenties = '> 0';}
        $qstring = "SELECT b.id, b.voornaam, b.achternaam 
            FROM planning_activiteit a LEFT JOIN portal_gebruiker AS b ON (b.id = a.in_behandeling_door) 
            WHERE a.actief = 1 AND a.iteratie  = $iteraties AND a.competentie $competenties AND (b.voornaam LIKE '%$term%' OR b.achternaam LIKE '%$term%') AND (a.in_behandeling_door = b.id OR a.acceptatie_door = b.id)";
        //echo $qstring;
    }
    elseif($_GET['filter'] == 'gebruiker'){
        $qstring = 'SELECT DISTINCT id, voornaam, achternaam FROM portal_gebruiker WHERE actief = 1 AND (voornaam LIKE \'%'.$term.'%\' OR achternaam LIKE \'%'.$term.'%\')';
    }
    //echo $qstring;
    $result = mysql_query($qstring)or DIE('Geen database connectie. Raadpleeg de Webmaster');   //query the database for entries containing the term
    while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){if($row['voornaam']){$naam =  $row['voornaam'].' '.$row['achternaam'];}else{$naam = $row['naam'];} $row_set[$naam] = $row['id']; }        //loop through the retrieved values and make array
    foreach ($row_set as $key=>$value) { array_push($org_result, array("id"=>$value, "label"=>$key, "value" => strip_tags($key))); }
    echo array_to_json($org_result);//format the array into json data                                
}else{
    //op dit moment bedoeld om te testen wat de uitkomst van een filter request is. Doet verder niets !//
    print_r($_REQUEST);
        $filter =  '';
        if($_POST['organisatie'] != null){
            $organisaties = ' AND a.organisatie IN (';  $i = 0;
            foreach($_POST['organisatie'] AS $organisatie){
                if($i == 0){$organisaties .= $organisatie;} else{$organisaties .= ', '.$organisatie;} $i++;
            }$organisaties .= ')'; $filter .= $organisaties;
        }
        
        if($_POST['project'] != null){
            $projecten = ' AND a.project IN (';  $i = 0;
            foreach($_POST['project'] AS $project){
                if($i == 0){$projecten .= $project;} else{$projecten .= ', '.$project;} $i++;
            }$projecten .= ')'; $filter .= $projecten;
        }
        
        if($_POST['competentie'] != null){
            $competenties = ' AND a.competentie IN (';  $i = 0;
            foreach($_POST['competentie'] AS $competentie){
                if($i == 0){$competenties .= $competentie;} else{$competenties .= ', '.$competentie;} $i++;
            }$competenties .= ')'; $filter .= $competenties;
        }
        
        if($_POST['medewerker'] != null){
            $medewerkers =  "AND a.in_behandeling_bij IN ( ";  $i = 0;
            foreach($_POST['medewerker'] AS $medewerker){
                if($i == 0){$medewerkers .= $medewerker;} else{$medewerkers .= ', '.$medewerker;} $i++;
            }$medewerkers .= ')'; $filter .= $medewerkers;
        }
        
        if($_POST['zoekwoord'] != null){
            $werkzaamheden = ''; $html_detail = ''; $i = 0;
            foreach($_POST['zoekwoord'] AS $zoekwoord){
                if($i == 0){
                    $werkzaamheden .= "a.werkzaamheden LIKE '%$zoekwoord%'";
                    $html_detail .= "a.html_detail LIKE '%$zoekwoord%'";
                } 
                else{
                    $werkzaamheden .= " OR a.werkzaamheden LIKE '%$zoekwoord%'";
                    $html_detail .= " OR a.html_detail LIKE '%$zoekwoord%'";
                }$i++;
            }
            $zoekwoorden = "AND ( ($werkzaamheden) OR ($html_detail) )"; 
            $filter .= $zoekwoorden;
        }
        echo $filter;
}
?>
