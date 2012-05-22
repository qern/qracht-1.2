<?php
    session_start();
    require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    
    $wijzigen_url = '/planning/detail/activiteit-id=';
    if($_GET['filter'] != null){
        $filter =  '';
        if($_GET['filter']['organisatie'] != null){
            $organisaties = ' AND c.organisatie IN (';  $i = 0;
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
        
        if($_GET['filter']['van_datum'] != null || $_GET['filter']['tot_datum'] != null){
            $van_datum = $_GET['filter']['van_datum']; $tot_datum = $_GET['filter']['tot_datum'];
            if($tot_datum == null){  $van_datum = date("Y-m-d", strtotime($van_datum));
                $filter .= " AND DATE(a.status_datum) >= '$van_datum'";
            }elseif($van_datum == null){  $tot_datum = date("Y-m-d", strtotime($tot_datum));
                $filter .= " AND DATE(a.status_datum) <= '$tot_datum'";
            }else{  $van_datum = date("Y-m-d", strtotime($van_datum)); $tot_datum = date("Y-m-d", strtotime($tot_datum));
                $filter .= " AND a.status_datum BETWEEN '".$van_datum."' AND '".$tot_datum."'";
            }
        }
        
        if($_GET['filter']['prio'] > 0){
            $prio = $_GET['filter']['prio'];
            $filter .= " AND a.prioriteit = $prio";
        }
        
    }
    
    function getReactie($id, $toon = 'aantal'){
        global $etc_root, $wijzigen_url;
	       //haal het aantal commentaar op + de laatste
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
		  $aantal = countRows( $what_reacties, $from_reacties, $where_reacties );
		
		if($toon == 'aantal'){ return $aantal;}
		elseif($toon == 'laatste'){
		$laatste_reactie = mysql_fetch_assoc( sqlSelect( $what_reacties, $from_reacties, $where_reacties ) );	
		
		return  '
		<div class="commentaar_container" onmouseover="this.className='."'commentaar_container_hover'".';" onmouseout="this.className='."'commentaar_container'".'">
            <a class="commentaar" href="'.$wijzigen_url.$id.'" >
	         	<img src="'.$etc_root.'functions/planning/css/images/speech_bubble.png" alt="commentaar" />
	         	<span class="aantal_comments">'.$aantal.'</span> 
        	</a>
       
            <div class="laatste_comment">
                <div class="reactie">
                    <div class="reactie_links">
                        <div class="reactie_foto">
                            <div class="reactie_profiel_pic">'
                				.getProfielfoto($laatste_reactie['gebruikersid']).
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
                 </div>
              </div>
        </div>';
                  }
    }
	function getProfielfoto($gebruiker_id){
	    global $etc_root;
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
		return $image;               
	}
    
    if($_GET['action'] == 'reload'){
        $what  = "  a.id,
                    a.werkzaamheden,
                    a.uur_aantal,
                    a.prioriteit,
                    DATE_FORMAT(a.status_datum, '%d %M %Y') status_datum,
                    DATE_FORMAT(b.datum, '%d %M %Y') iteratie_einddatum,
                    c.titel, d.competentie, e.naam";
        
        $from  = "  planning_activiteit a
                    LEFT JOIN planning_iteratie AS b ON (b.id = a.iteratie)
                    LEFT JOIN project AS c ON (c.id = a.project)
                    LEFT JOIN competentie AS d ON (d.id = a.competentie)
                    LEFT JOIN organisatie AS e ON (e.id = c.organisatie)";
        
        $where = "
                    a.status = 'done' 
                    AND a.actief = 1  AND c.actief = 1 AND e.actief = 1
                    AND DATE(b.datum) < CURDATE() ";
        
        if($filter == null){ $where .= ' AND a.status_datum >DATE_SUB(NOW(), INTERVAL 30 DAY)';}
        else{$where .= $filter;} 
        
        $where .= ' ORDER BY a.status_datum ASC';
        //kijk eerst even hoeveel resultaten er zijn
        
        //echo "SELECT $what FROM $from WHERE $where";
        $aantal_results = countRows($what,$from,$where);
        if($aantal_results > 0){
            
        $archief_result = sqlSelect($what,$from,$where);
        $archief_array = array();
        
        while($row = mysql_fetch_array($archief_result)){

            $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = '.$row['id'];      
            $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
         
         
            $archief_activiteit = '
            <span class="dashboard_info">
                <span class="dashboard_organisatie">'.$row['naam'].'</span>
                <span class="dashboard_project">'.$row['titel'].'</span>
                <span class="dashboard_persoon">'.$row['competentie'].'</span>
                <span class="dashboard_afgerond"><i> <span class="tooltip" title="afgerond op '.$row['status_datum'].'">'.$row['status_datum'].'</span> </i> </span>
            </span>
            
             <span class="dashboard_meldingen_prio">
                <span class="prioriteit">
                    <img src="/functions/planning/css/images/prio_'.$row['prioriteit'].'_24px.png" alt="prio '.$row['prioriteit'].'" onclick="changePrioriteit('.$row['id'].', \'todo\')" />
                </span>
            </span>
            
            <span class="dashboard_werkzaamheden">'.$row['werkzaamheden'].'
                <div class="edit"><a href="'.$wijzigen_url.$row['id'].'"><img src="/functions/planning/css/images/edit.png" width="24" height="24"></a></div>
            </span>
            
            <span class="dashboard_meldingen">
                <span class="dashboard_uren">'.$row['uur_aantal'].'</span>';
            if(getReactie($row['id'], 'aantal') > 0){ $archief_activiteit .= getReactie($row['id'], 'laatste'); }// laat de laatste reactie zien.     
                        
                        
            if($aantal_bestanden > 0){$archief_activiteit .= '
                <div class="bestanden">
                    <a href="'.$wijzigen_url.$row['id'].'" class="bestanden_container">
                        <img src="/functions/planning/css/images/attachment.png" alt="'.$aantal_bestanden.' bestanden" />
                    </a>
                </div> ';    
            }
            $archief_activiteit.='</span>';

            $archief_array[] = $archief_activiteit;
        }//einde while
    
        //bepaal hoeveel arrays er in totaal zijn
        $aantal_arrays = count($archief_array);
        //deel dit door het aantal kolommen dat we willen : 4
        //rond dit naar boven af. Zo weten we hoeveer er maximaal per kolom moeten (bij oneven is er bij de laatste gewoon 1 minder.)
        $aantal_per_kolom = ceil($aantal_arrays/4);
    }
  }
?>
<div class="archief_kolom">
<?php 
    //kijk of er meer dan 0 reaties zijn
    if($aantal_results > 0){
            //begin nu met tellen.
            $i_1 = 0; 
            foreach($archief_array as $archief_rij){
                if($i_1 <= $aantal_per_kolom-1 && $i_1 <= $aantal_arrays-1){
                        
                    if($i_class_1 == 1){$class_1 = 'act_b'; $i_class_1 = 0;}else{$class_1 = 'act_a'; $i_class_1 = 1;}
                    
                    echo '<div class="'.$class_1.'">'.$archief_array["$i_1"] .'</div>';
                    
                    $i_1++;
                }

            }
    }else{ echo '<div class="act_a">Dit filter leverde geen activiteiten op. Probeer het alstublieft opnieuw.</div>'; }
?>
</div>
<?php 
    //kijk of er meer dan 0 reaties zijn
    if($aantal_results > 0 && $aantal_arrays >= 2){
        echo '<div class="archief_kolom">'; 
            //tellen voor de tweede kolom
            $i_2 = $i_1;
            foreach($archief_array as $archief_rij){
                if($i_2 <= $aantal_per_kolom+$i_1-1 && $i_2 <= $aantal_arrays-1){
                    if($i_class_2 == 1){$class_2 = 'act_b'; $i_class_2 = 0;}else{$class_2 = 'act_a'; $i_class_2 = 1;}
                    echo '<div class="'.$class_2.'" onmouseover="this.className='."'act_hover'".';" onmouseout="this.className='."'$class_2'".';">'.
                    $archief_array["$i_2"]
                    .'</div>';
                    $i_2++;
                }

            }
        echo  '</div>';
    }
?>

<?php 
    //kijk of er meer dan 0 reaties zijn
    if($aantal_results > 0 && $aantal_arrays >= 3){
        echo '<div class="archief_kolom">';
        //tellen voor de derde kolom
        $i_3 = $i_2; 
        foreach($archief_array as $archief_rij){
            if($i_3 <= $aantal_per_kolom+$i_2-1 && $i_3 <= $aantal_arrays-1){
                if($i_class_3 == 1){$class_3 = 'act_b'; $i_class_3 = 0;}else{$class_3 = 'act_a'; $i_class_3 = 1;}
                echo '<div class="'.$class_3.'" onmouseover="this.className='."'act_hover'".';" onmouseout="this.className='."'$class_3'".';">'.
                $archief_array["$i_3"]
                .'</div>';
                $i_3++;
            }

        }
        echo  '</div>';
    }
?>

<?php 
    //kijk of er meer dan 0 reaties zijn
    if($aantal_results > 0 && $aantal_arrays >= 4){
        echo '<div class="archief_kolom_last">';
        //tellen voor de laatste kolom
        $i_4 = $i_3;
        foreach($archief_array as $archief_rij){
            if($i_4 <= $aantal_per_kolom+$i_3-1 && $i_4 <= $aantal_arrays-1){
                if($i_class_4 == 1){$class_4 = 'act_b'; $i_class_4 = 0;}else{$class_4 = 'act_a'; $i_class_4 = 1;}
                echo '<div class="'.$class_4.'" onmouseover="this.className='."'act_hover'".';" onmouseout="this.className='."'$class_4'".';">'.
                $archief_array["$i_4"]
                .'</div>';
                $i_4++;
            }

        }
        echo  '</div>';
    }
?>