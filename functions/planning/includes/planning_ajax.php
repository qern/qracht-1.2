<?php
    session_start();
    require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    
    $wijzigen_url = '/planning/detail/activiteit-id=';
    if($_GET['filter'] != null){
        $filter =  '';
        if($_GET['filter']['organisatie'] != null){
            $organisaties = ' AND c.id IN (';  $i = 0;
            foreach($_GET['filter']['organisatie'] AS $organisatie){
                if($i == 0){$organisaties .= $organisatie;} else{$organisaties .= ', '.$organisatie;} $i++;
            }$organisaties .= ')'; $filter .= $organisaties;
        }
        
        if($_GET['filter']['project'] != null){
            $projecten = ' AND a.project IN (';  $i = 0;
            foreach($_GET['filter']['project'] AS $project){
                if($i == 0){$projecten .= $project;} else{$projecten .= ', '.$project;} $i++;
            }$projecten .= ')'; $filter .= $projecten;
        }
        
        if($_GET['filter']['competentie'] != null){
            $competenties = ' AND a.competentie IN (';  $i = 0;
            foreach($_GET['filter']['competentie'] AS $competentie){
                if($i == 0){$competenties .= $competentie;} else{$competenties .= ', '.$competentie;} $i++;
            }$competenties .= ')'; $filter .= $competenties;
        }
        
        if($_GET['filter']['medewerker'] != null){
            $medewerkers =  ' AND a.in_behandeling_door IN (';  $i = 0;
            foreach($_GET['filter']['medewerker'] AS $medewerker){
                if($i == 0){$medewerkers .= $medewerker;} else{$medewerkers .= ', '.$medewerker;} $i++;
            }$medewerkers .= ')'; $filter .= $medewerkers;
        }

        //filter op min, max of min en max uren.
        if($_GET['filter']['min_uren'] != null || $_GET['filter']['max_uren'] != null){
          $min_uren = $_GET['filter']['min_uren']; $max_uren = $_GET['filter']['max_uren'];
          //mooiste/makkelijkste manier is door eliminatie. Als beide niet leeg zijn, dan doe je beide.
          if($max_uren == null){
              $filter .= " AND a.uur_aantal >= '$min_uren'";
          }elseif($min_uren == null){
              $filter .= " AND a.uur_aantal <= '$max_uren'";
          }else{
              $filter .= " AND a.uur_aantal BETWEEN '$min_uren' AND '$max_uren'";
          }
        }
        
        if($_GET['filter']['zoekwoord'] != null){
            $werkzaamheden = ''; $html_detail = ''; $i = 0;
            foreach($_GET['filter']['zoekwoord'] AS $zoekwoord){
                if($i == 0){
                    $werkzaamheden .= "a.werkzaamheden LIKE '%$zoekwoord%'";
                    $html_detail .= "a.html_detail LIKE '%$zoekwoord%'";
                } 
                else{
                    $werkzaamheden .= " OR a.werkzaamheden LIKE '%$zoekwoord%'";
                    $html_detail .= " OR a.html_detail LIKE '%$zoekwoord%'";
                }$i++;
            }
            $zoekwoorden = " AND ( ($werkzaamheden) OR ($html_detail) )"; 
            $filter .= $zoekwoorden;
        }
        
 if($_GET['filter']['prio'] > 0){
            $prio = $_GET['filter']['prio'];
            $filter .= " AND a.prioriteit = $prio";
        }
    }
    
    function getReactie($id, $toon = 'aantal'){
        global $etc_root, $wijzigen_url;
	       //haal het aantal commentaar op + de laatste
	    $what_reacties=" 	b.id";
        $from_reacties="	planning_reactie a LEFT JOIN portal_reactie AS b ON (b.id =  a.reactie)";
        $where_reacties="	a.activiteit = $id AND b.actief = 1 ";
		  $aantal = countRows( $what_reacties, $from_reacties, $where_reacties );
		
		if($toon == 'aantal'){ return $aantal;}
		elseif($toon == 'laatste'){return  '
		<div class="commentaar_container" data-shown="false" data-activiteit="'.$id.'">
            <a class="commentaar" href="'.$wijzigen_url.$id.'" >
	         	<img src="'.$etc_root.'functions/planning/css/images/speech_bubble.png" alt="commentaar" />
	         	<span class="aantal_comments">'.$aantal.'</span> 
        	</a>
       
            <div class="laatste_comment"> &nbsp; </div>
        </div>';
      }
    }
    
    if($_GET['action'] == 'reload'){
        $kolommen = explode(',', $_GET['kolom']);
        $iteratie_id = $_GET['iteratie_id'];
		if(getRecenten($login_id, $iteratie_id)){
			$recenten = getRecenten($login_id, $iteratie_id);
			//$is_gezien = array();
			while($recent = mysql_fetch_array($recenten)){ $is_gezien[] = $recent['activiteit']; }
		}
        if(in_array('todo', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){ echo '<div id="kolom_todo">';}

$what  = "a.id, a.werkzaamheden, a.uur_aantal, a.prioriteit,
          b.titel, c.naam, d.competentie ";
$from  = "planning_activiteit a
          LEFT JOIN project AS b ON (b.id = a.project)
          LEFT JOIN organisatie AS c ON (c.id = b.organisatie)
          LEFT JOIN competentie AS d ON (d.id = a.competentie)";
$where = "a.iteratie = $iteratie_id  AND a.status = 'to do' AND a.actief = 1
          AND b.actief = 1 AND c.actief = 1";
$where .= $filter;          
if($query != null){$where .= $query;} 
$where .= ' ORDER BY a.iteratie_volgorde ASC';
$todo_result = sqlSelect($what,$from,$where);
//echo "SELECT $what FROM $from WHERE $where"; 
$what = "sum(uur_aantal) uren"; $from = "planning_activiteit";
$where= "iteratie = $iteratie_id AND status = 'to do' AND actief = 1";
    $todo_uren = sqlSelect($what, $from, $where);
    $todo_totaal_aantal = mysql_fetch_assoc($todo_uren);


 echo'        <div class="kolom_titel">
                    <div class="titel_a"><h2 class="titel">To Do</h2></div>';
if($todo_totaal_aantal['uren'] > 0){echo '<div class="titel_b"><span title="Aantal uur">'.$todo_totaal_aantal['uren'].' uur</span></div>';}
echo'         </div>

                 <ul class="activiteiten" id="todo">';
while($todo = mysql_fetch_array($todo_result)){
     
    
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = \''.$todo['id'].'\'';
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($count_1 == 1){$count_1 = 0; $class_1="act_b";}else{$count_1 = 1; $class_1="act_a";}
    echo '<li class="'.$class_1.' '.isRecent($todo['id'], $login_id).'" id="activiteit_'.$todo['id'].'xtodo" onmouseover="this.className='."'act_hover ".isRecent($todo['id'], $login_id)."'".';" onmouseout="this.className='."'$class_1 ".isRecent($todo['id'], $login_id)."'".'">
            
            
            <span class="dashboard_info">
                <span class="dashboard_organisatie">'.$todo['naam'].'</span>
                <span class="dashboard_project">'.$todo['titel'].'</span>
                <span class="dashboard_persoon">'.$todo['competentie'].'</span>
            </span>
            
            
            <span class="dashboard_meldingen_prio">
                <span class="prioriteit">
                    <img src="/functions/planning/css/images/prio_'.$todo['prioriteit'].'_24px.png" alt="prio '.$todo['prioriteit'].'" onclick="changePrioriteit('.$todo['id'].', \'todo\')" />
                </span>
            </span>
            
            
            <span class="dashboard_werkzaamheden">'.$todo['werkzaamheden'].'
                <div class="edit"><a href="'.$wijzigen_url.$todo['id'].'"><img src="/functions/planning/css/images/edit.png" width="24" height="24"></a></div>
            </span>
            
            <span class="dashboard_meldingen">';
            
   				echo'    <span class="dashboard_uren">'.$todo['uur_aantal'].'</span>';

				if(!isset($is_gezien) || !in_array($todo['id'], $is_gezien)){echo '
	                    <span class="recentmelding_container">
	                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="'.$todo['id'].'" data-kolom="todo" />
	                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
	                    </span>';
						
                }

		                if(getReactie($todo['id'], 'aantal') > 0){ echo getReactie($todo['id'], 'laatste'); }// laat de laatste reactie zien.     

                        if($aantal_bestanden > 0){echo '
                            <div class="bestanden">
                                <a href="'.$wijzigen_url.$todo['id'].'" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="'.$aantal_bestanden.' bestanden" />
                                </a>
                            </div> ';    
                        }
                         
                        echo '
                </span>
    		</li>';
}                 

echo '          </ul>';
if($_GET['kolom'] == 'alles'){ echo '</div>';}
        }
        if(in_array('onderhanden', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){ echo '<div id="kolom_onderhanden">';}
          
$what  = "a.id, a.status, a.werkzaamheden, a.uur_aantal, a.in_behandeling_door, a.prioriteit,
          b.titel, c.naam, d.competentie, e.voornaam, e.achternaam, e.gebruikersnaam";
$from  = "planning_activiteit a
          LEFT JOIN project AS b ON (b.id = a.project)
          LEFT JOIN organisatie AS c ON (c.id = b.organisatie)
          LEFT JOIN competentie AS d ON (d.id = a.competentie)
          LEFT JOIN portal_gebruiker AS e ON (e.id = a.in_behandeling_door)";
$where = "a.iteratie = $iteratie_id  AND a.status = 'onderhanden' AND a.actief = 1
           AND b.actief = 1 AND c.actief = 1 AND e.actief = 1";
$where .= $filter;          
if($query != null){$where .= $query;} 
$where .= ' ORDER BY a.iteratie_volgorde ASC';
//echo "SELECT $what FROM $from WHERE $where";
$onderhanden_result = sqlSelect($what,$from,$where);

$what = "sum(uur_aantal) uren";
$from = "planning_activiteit";
$where= "iteratie = $iteratie_id AND status = 'onderhanden' AND actief = 1";
$onderhanden_uren = sqlSelect($what, $from, $where);
$onderhanden_totaal_aantal = mysql_fetch_assoc($onderhanden_uren);
 echo'        <div class="kolom_titel">
                    <div class="titel_a"><h2 class="titel">Onderhanden</h2></div>'; 
if($onderhanden_totaal_aantal['uren'] > 0){echo '<div class="titel_b"><span title="Aantal uur">'.$onderhanden_totaal_aantal['uren'].' uur</span></div>';}
echo'         </div>

                 <ul class="activiteiten" id="onderhanden">';
while($onderhanden = mysql_fetch_array($onderhanden_result)){
	    
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = \''.$onderhanden['id'].'\'';
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($count_2 == 1){$count_2 = 0; $class_2="act_b";}else{$count_2 = 1; $class_2="act_a";}
    echo'
    <li class="'.$class_2.' '.isRecent($onderhanden['id'], $login_id).'" id="activiteit_'.$onderhanden['id'].'xonderhanden" onmouseover="this.className='."'act_hover ".isRecent($onderhanden['id'], $login_id)."'".';" onmouseout="this.className='."'$class_2 ".isRecent($onderhanden['id'], $login_id)."'".'">
        
            <span class="dashboard_info">
                <span class="dashboard_organisatie">'.$onderhanden['naam'].'</span>
                <span class="dashboard_project">'.$onderhanden['titel'].'</span>
                <span class="dashboard_persoon">'.$onderhanden['competentie'].' / <span class="persoon_color">'.$onderhanden['voornaam'].' '.$onderhanden['achternaam'].'</span></span>
            </span>
            
            
            <span class="dashboard_meldingen_prio">
                <span class="prioriteit">
                    <img src="/functions/planning/css/images/prio_'.$onderhanden['prioriteit'].'_24px.png" alt="prio '.$onderhanden['prioriteit'].'" onclick="changePrioriteit('.$onderhanden['id'].', \'onderhanden\')" />
                </span>
            </span>
            
            
            <span class="dashboard_werkzaamheden">'.$onderhanden['werkzaamheden'].'
                <div class="edit"><a href="'.$wijzigen_url.$onderhanden['id'].'"><img src="/functions/planning/css/images/edit.png" width="24" height="24"></a></div>
            </span>
            
            
            <span class="dashboard_meldingen">';
            
                
                    

					echo    '<span class="dashboard_uren">'.$onderhanden['uur_aantal'].'</span>';
					
					
					if(!isset($is_gezien) || !in_array($onderhanden['id'], $is_gezien)){echo '
	                    <span class="recentmelding_container">
	                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="'.$onderhanden['id'].'" data-kolom="onderhanden" />
	                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
	                    </span>';
                	}

                		if(getReactie($onderhanden['id'], 'aantal') > 0){
		                	echo getReactie($onderhanden['id'], 'laatste');// laat de laatste reactie zien.
		                }     
                        
                        
                        if($aantal_bestanden > 0){echo '
                            <div class="bestanden">
                                <a href="'.$wijzigen_url.$onderhanden['id'].'" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="'.$aantal_bestanden.' bestanden" />
                                </a>
                            </div> ';    
                        }
                         
                        echo '
                </span>        
    </li>';
}    
echo            '</ul>';
if($_GET['kolom'] == 'alles'){ echo '</div>';}
        }
        if(in_array('acceptatie', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){ echo '<div id="kolom_acceptatie">';}
		   
$what  = "a.id, a.werkzaamheden, a.uur_aantal, a.acceptatie_door, a.prioriteit,
          b.titel, c.naam, d.competentie ";
$from  = "planning_activiteit a
          LEFT JOIN project AS b ON (b.id = a.project)
          LEFT JOIN organisatie AS c ON (c.id = b.organisatie)
          LEFT JOIN competentie AS d ON (d.id = a.competentie)";
$where = "a.iteratie = $iteratie_id  AND a.status = 'acceptatie' AND a.actief = 1
          AND b.actief = 1 AND c.actief = 1";
$where .= $filter;            
/*$what  = "a.id,
          a.werkzaamheden,
          a.uur_aantal,
          a.acceptatie_door,
          a.prioriteit,
          b.naam";
$from  = "planning_activiteit a
          LEFT JOIN organisatie AS b ON (b.id = a.organisatie)";
$where = "a.planning_iteratie_id = $iteratie_id 
      AND a.status = 'acceptatie'
      AND a.actief = 1 
      AND b.actief = 1 ";*/ 
if($query != null){$where .= $query;} 
$where .= ' ORDER BY a.iteratie_volgorde ASC';
$acceptatie_result = sqlSelect($what,$from,$where);

$what = "sum(uur_aantal) uren";
$from = "planning_activiteit";
$where= "iteratie = $iteratie_id AND status = 'acceptatie' AND actief=1";
$acceptatie_uren = sqlSelect($what, $from, $where);
$acceptatie_totaal_aantal = mysql_fetch_assoc($acceptatie_uren);
 echo'        <div class="kolom_titel">
                    <div class="titel_a"><h2 class="titel">Acceptatie</h2></div>'; 
if($acceptatie_totaal_aantal['uren'] > 0){echo '<div class="titel_b"><span title="Aantal uur">'.$acceptatie_totaal_aantal['uren'].' uur</span></div>';}
echo'         </div>

                 <ul class="activiteiten" id="acceptatie">';
                 
while($acceptatie = mysql_fetch_array($acceptatie_result)){
  	
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = \''.$acceptatie['id'].'\'';
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($count_3 == 1){$count_3 = 0; $class_3="act_b";}else{$count_3 = 1; $class_3="act_a";}
    if($acceptatie['acceptatie_door'] != 0 ){
        $what="gebruikersnaam, voornaam, achternaam";    
        $from="portal_gebruiker";
        $where='id ='.$acceptatie['acceptatie_door'].' AND actief = 1';
        $acceptatie_persoon = sqlSelect($what,$from,$where);
        $acceptatie_persoon = mysql_fetch_assoc($acceptatie_persoon);
        
        echo '
        <li class="'.$class_3.' '.isRecent($acceptatie['id'], $login_id).'" id="activiteit_'.$acceptatie['id'].'xacceptatie" onmouseover="this.className='."'act_hover ".isRecent($acceptatie['id'], $login_id)."'".';" onmouseout="this.className='."'$class_3 ".isRecent($acceptatie['id'], $login_id)."'".'">
            
            <span class="dashboard_info">
                <span class="dashboard_organisatie">'.$acceptatie['naam'].'</span>
                <span class="dashboard_project">'.$acceptatie['titel'].'</span>
                <span class="dashboard_persoon">'.$acceptatie['competentie'].' / <span class="persoon_color">'.$acceptatie_persoon['voornaam'].' '.$acceptatie_persoon['achternaam'].'</span></span>
            </span>
            
            
            <span class="dashboard_meldingen_prio">
                <span class="prioriteit">
                    <img src="/functions/planning/css/images/prio_'.$acceptatie['prioriteit'].'_24px.png" alt="prio '.$acceptatie['prioriteit'].'" onclick="changePrioriteit('.$acceptatie['id'].', \'acceptatie\')" />
                </span>
            </span>
            
            
            <span class="dashboard_werkzaamheden">'.$acceptatie['werkzaamheden'].'
                <div class="edit"><a href="'.$wijzigen_url.$acceptatie['id'].'"><img src="/functions/planning/css/images/edit.png" width="24" height="24"></a></div>
            </span>
            
            
            <span class="dashboard_meldingen">';
			
			            echo    '<span class="dashboard_uren">'.$acceptatie['uur_aantal'].'</span>';
						                
					if(!isset($is_gezien) || !in_array($acceptatie['id'], $is_gezien)){echo '
		                    <span class="recentmelding_container">
		                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="'.$acceptatie['id'].'" data-kolom="acceptatie" />
		                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
		                    </span>';
							
	                }

                		if(getReactie($acceptatie['id'], 'aantal') > 0){
		                	echo getReactie($acceptatie['id'], 'laatste');// laat de laatste reactie zien.
		                }
                        
                        if($aantal_bestanden > 0){echo '
                            <div class="bestanden">
                                <a href="'.$wijzigen_url.$acceptatie['id'].'" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="'.$aantal_bestanden.' bestanden" />
                                </a>
                            </div> ';    
                        }
                         
                        echo '
                </span>  
        </li>';
    }else{
        echo'
        <li class="'.$class_3.' '.isRecent($acceptatie['id'], $login_id).'" id="activiteit_'.$acceptatie['id'].'xacceptatie" onmouseover="this.className='."'act_hover ".isRecent($acceptatie['id'], $login_id)."'".';" onmouseout="this.className='."'$class_3 ".isRecent($acceptatie['id'], $login_id)."'".'">
            <span class="dashboard_info">
                <span class="dashboard_organisatie">'.$acceptatie['naam'].'</span>
                <span class="dashboard_project">'.$acceptatie['titel'].'</span>
                <span class="dashboard_persoon">'.$acceptatie['competentie'].' / <span onclick="pakActiviteitOp('.$acceptatie['id'].')" class="oppakken"">oppakken</span></span>
            </span>
            
            
            <span class="dashboard_meldingen_prio">
                <span class="prioriteit">
                    <img src="/functions/planning/css/images/prio_'.$acceptatie['prioriteit'].'_24px.png" alt="prio '.$acceptatie['prioriteit'].'" onclick="changePrioriteit('.$acceptatie['id'].', \'acceptatie\')" />
                </span>
            </span>
            
            
            <span class="dashboard_werkzaamheden">'.$acceptatie['werkzaamheden'].'
                <div class="edit"><a href="'.$wijzigen_url.$acceptatie['id'].'"><img src="/functions/planning/css/images/edit.png" width="24" height="24"></a></div>
            </span>
            
            
            <span class="dashboard_meldingen">';
			
			 	echo    '<span class="dashboard_uren">'.$acceptatie['uur_aantal'].'</span>';
				

				if(!isset($is_gezien) || !in_array($acceptatie['id'], $is_gezien)){echo '
	                    <span class="recentmelding_container">
	                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="'.$acceptatie['id'].'" data-kolom="acceptatie" />
	                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
	                    </span>';
						
                }

                		if(getReactie($acceptatie['id'], 'aantal') > 0){
		                	echo getReactie($acceptatie['id'], 'laatste');// laat de laatste reactie zien.
		                }
                        
                        if($aantal_bestanden > 0){echo '
                            <div class="bestanden">
                                <a href="'.$wijzigen_url.$acceptatie['id'].'" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="'.$aantal_bestanden.' bestanden" />
                                </a>
                            </div> ';    
                        }
                         
                        echo '
                </span>  
        </li>';
    }
}
echo            '</ul>';
if($_GET['kolom'] == 'alles'){ echo '</div>';}
        }
        if(in_array('done', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){ echo '<div id="kolom_done">';}

$what  = "a.id, a.werkzaamheden, a.uur_aantal, a.prioriteit, DATE_FORMAT(a.status_datum, '%e %M %Y') status_datum,
          b.titel, c.naam, d.competentie";
$from  = "planning_activiteit a
          LEFT JOIN project AS b ON (b.id = a.project)
          LEFT JOIN organisatie AS c ON (c.id = b.organisatie)
          LEFT JOIN competentie AS d ON (d.id = a.competentie)";
$where = "a.iteratie = $iteratie_id  AND a.status = 'done' AND a.actief = 1
          AND b.actief = 1 AND c.actief = 1";
$where .= $filter;  
/*$what  = "a.id,
          a.werkzaamheden,
          a.uur_aantal,
          DATE_FORMAT(a.status_datum, '%e %M %Y') status_datum,
          b.naam";
$from  = "planning_activiteit a
          LEFT JOIN organisatie AS b ON (b.id = a.organisatie)";
$where = "a.planning_iteratie_id = $iteratie_id 
      AND a.status = 'done' 
      AND a.actief = 1
       AND b.actief = 1 ";*/
      
if($query != null){$where .= $query;} 
$where .= ' ORDER BY a.status_datum DESC';
$done_result = sqlSelect($what,$from,$where);

$what = "DATE_FORMAT(datum, '%W %e %M %Y') datum";
$from = "planning_iteratie";
$where= "id = $iteratie_id";
$done_deadline = sqlSelect($what, $from, $where);
$done_deadline = mysql_fetch_assoc($done_deadline);
 echo'        <div class="kolom_titel">
                    <div class="titel_a"><h2 class="titel">Done</h2></div>
                    <div class="titel_b">'.$done_deadline['datum'].'</div>
              </div>

                 <ul class="activiteiten" id="done">';
while($done = mysql_fetch_array($done_result)){
    
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = \''.$done['id'].'\'';
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($count_4 == 1){$count_4 = 0; $class_4="act_b";}else{$count_4 = 1; $class_4="act_a";}
    echo'<li class="'.$class_4.'" id="activiteit_'.$done['id'].'xdone" onmouseover="this.className='."'act_hover'".';" onmouseout="this.className='."'$class_4'".';">
    <span class="dashboard_info">
            <span class="dashboard_organisatie">'.$done['naam'].'</span>
                <span class="dashboard_project">'.$done['titel'].'</span>
                <span class="dashboard_persoon">'.$done['competentie'].' / <span class="persoon_color">'.$done['voornaam'].' '.$done['achternaam'].'</span></span>
            </span>         
            
            <span class="dashboard_werkzaamheden">'.$done['werkzaamheden'].'
                <div class="edit"><a href="'.$wijzigen_url.$done['id'].'"><img src="/functions/planning/css/images/edit.png" width="24" height="24"></a></div>
            </span>
            
            
            <span class="dashboard_meldingen">';
       echo    '<span class="dashboard_uren">'.$done['uur_aantal'].'</span>';
                 		if(getReactie($done['id'], 'aantal') > 0){
		                	echo getReactie($done['id'], 'laatste');// laat de laatste reactie zien.
		                }
                        
                        if($aantal_bestanden > 0){echo '
                            <div class="bestanden">
                                <a href="'.$wijzigen_url.$done['id'].'" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="'.$aantal_bestanden.' bestanden" />
                                </a>
                            </div> ';    
                        }
                         
                        echo '
                </span>  
            </li>';
}    
echo            '</ul>';
if($_GET['kolom'] == 'alles'){ echo '</div>';}
        }
    }
?>
