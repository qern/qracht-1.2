<?php
    session_start();
    require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
	
    $wijzigen_url = '/planning/detail/activiteit-id=';
    if($_GET['filter'] != null){
        $filter =  '';
        if($_GET['filter']['organisatie'] != null){
            $organisaties = ' AND b.organisatie IN (';  $i = 0;
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
	 /*
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
    */
    if($_GET['action'] == 'reload'){
        $kolommen = explode(',', $_GET['kolom']);
        $iteratie_id = $_GET['iteratie_id'];
        if(in_array('nog_te_plannen', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){ ?>
<script>
jQuery('#overige_iteratie_nav span').on('click', function(){
       var direction = jQuery(this).attr('data-dir'); slideIteratie( direction );
    });
</script>     
    <div id="iteraties_bewerken_links">
        <div id="nog_te_plannen_container"> 
<?php }
    //de header van nog te plannen
    $what = "sum(uur_aantal) te_plannen";  $from = "planning_activiteit";  $where = "status = 'nog te plannen' AND actief = 1";
    $totaal = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>
            
            <div id="nog_te_plannen_titel">
                <h2>Nog te plannen</h2>
<?php if($totaal['te_plannen'] > 0){
        echo'   <span id="nog_te_plannen_totaal">
                  '.$totaal['te_plannen'].' uur
                </span>';} 
?>
            </div>
            <ul id="nog_te_plannen" class="activiteiten">
<?php
//de inhoud van nog te plannen
$what=" a.id,  a.werkzaamheden,  a.uur_aantal, a.prioriteit, 
		b.titel, c.naam, d.competentie";  
$from=" planning_activiteit a
        LEFT JOIN project AS b ON ( b.id = a.project )
		LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
        LEFT JOIN competentie AS d ON ( d.id = a.competentie )"; 
$where="a.status = 'nog te plannen' AND a.actief = 1 AND b.actief = 1 AND c.actief = 1";
$where .= $filter;
$where="a.status = 'nog te plannen'
    AND a.actief = 1
    AND b.actief = 1 
    AND c.actief = 1 
    $filter ORDER BY a.iteratie_volgorde ASC, a.status_datum DESC";
//echo "SELECT $what FROM $from WHERE $where"; 
    $te_plannen = sqlSelect($what, $from, $where);
    
if(getRecenten($login_id, 0)){
	$recenten = getRecenten($login_id, 0);
	//$is_gezien = array();
	while($recent = mysql_fetch_array($recenten)){ $nog_te_plannen_is_gezien[] = $recent['activiteit']; }
}

while($nog_te_plannen = mysql_fetch_array($te_plannen)){    
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = '.$nog_te_plannen['id'];
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($i == 1 ){$class="act_a"; $i = 0;}else{$class="act_b"; $i = 1;}?>

                <li class="plan_mij <?php echo $class; ?>" id="activiteit_<?php echo $nog_te_plannen['id']; ?>xnogteplannen" onmouseover="this.className='act_hover'" onmouseout="this.className='<?php echo $class; ?>'">
                    <span class="dashboard_info">
						<span class="dashboard_organisatie"><?php echo $nog_te_plannen['naam']; ?></span>
                		<span class="dashboard_project"><?php echo $nog_te_plannen['titel']; ?></span>
                		<span class="dashboard_persoon"><?php echo $nog_te_plannen['competentie']; ?></span>
                    </span>  
                    <span class="dashboard_meldingen_prio">
                        <span class="prioriteit">

                                <img src="/functions/planning/css/images/prio_<?php echo $nog_te_plannen['prioriteit'] ?>_24px.png" alt="prio <?php echo $nog_te_plannen['id'] ?>"  onclick="changePrioriteit( '<?php echo $nog_te_plannen['id'] ?>', 'nog_te_plannen')" />

                        </span>
                    </span>    
                        <span class="dashboard_werkzaamheden"><?php echo $nog_te_plannen['werkzaamheden'] ?>
                            <div class="edit"><a href="<?php echo $wijzigen_url.$nog_te_plannen['id'] ?>"><img src="/functions/planning/css/images/edit.png"></a></div>
                        </span> 
                        
                        <span class="dashboard_meldingen"> 
                        <?php echo '<span class="dashboard_uren">'.$nog_te_plannen['uur_aantal'].'</span>';
						
                        if(!isset($nog_te_plannen_is_gezien) || !in_array($nog_te_plannen['id'], $nog_te_plannen_is_gezien)){ ?> 
	                    <span class="recentmelding_container">
	                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="<?php echo $nog_te_plannen['id'] ?>" data-kolom="nogteplannen" />
	                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
	                    </span>						
                        <?php } 
						
                        if(getReactie($nog_te_plannen['id'], 'aantal') > 0){ echo getReactie($nog_te_plannen['id'], 'laatste'); }// laat de laatste reactie zien.
                                                
                        if($aantal_bestanden > 0){?>
                            <div class="bestanden">
                                <a href="<?php echo $wijzigen_url.$nog_te_plannen['id'] ?>" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="<?php echo $aantal_bestanden; ?> bestanden" />
                                </a>
                            </div>   
<?php } ?>
                    </span>          
        </li>
<?php } ?>
            </ul>
        </div>
        <?php
if($_GET['kolom'] == 'alles'){ ?>
        </div>
    </div>
<?php }
        }
        if(in_array('huidige_iteratie', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){
    if($_GET['display'] == 'slider'){ $display_class='class = "slider_container"'; 
        if($_GET['pinned'] == 'on'){ $iteraties_bewerken_rechts_css = 'style="position:fixed; margin-left:260px;"'; }
        elseif($_GET['pinned'] == 'off'){ $iteraties_bewerken_rechts_css = 'style="position:relative; margin-left:5px;"'; }
        else{ $iteraties_bewerken_rechts_css = 'style="position:fixed; margin-left:260px;"'; }
    }
    elseif($_GET['display'] == 'alles'){ $display_class='class = "rows_container"';
        if($_GET['pinned'] == 'on'){ $iteraties_bewerken_rechts_css = 'style="position:fixed; margin-left:260px;"'; }
        elseif($_GET['pinned'] == 'off'){ $iteraties_bewerken_rechts_css = 'style="position:relative; margin-left:5px;"'; } 
        else{ $iteraties_bewerken_rechts_css = 'style="position:relative; margin-left:5px;"'; }
    }
    ?>
     <div id="iteraties_bewerken_rechts" <?php echo $display_class; ?> <?php echo $iteraties_bewerken_rechts_css; ?>>
        <div id="overige_iteratie_nav">
            <?php if($_GET['display'] == 'slider'){?>
                <span id="overige_iteratie_prev" data-dir="prev" style="display:none;">Vorige</span>
                <span id="overige_iteratie_next" data-dir="next" >Volgende</span>
            <?php }elseif($_GET['display'] == 'alles'){ ?>
                 &nbsp;
            <?php } ?>
        </div>
        <div id="huidige_iteratie_container">
<?php }

//alle gegevens voor de huidige iteratie
$what = "id, DATE_FORMAT(datum, '%d %M %Y') AS datum";  $from= "planning_iteratie";  $where="huidige_iteratie = 1";
    $huidige_iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));  $iteratie_id = $huidige_iteratie['id'];

$what = "sum(uur_aantal) totaal_to_do";  $from = "planning_activiteit";  $where = "iteratie = $iteratie_id  AND status = 'to do' ";
$nog_te_doen = mysql_fetch_assoc(sqlSelect($what, $from, $where));  $uur_nog_te_doen = $nog_te_doen['totaal_to_do'];
?>
            <div id="huidige_iteratie_titel">
                <h2><?php echo $huidige_iteratie['datum'] ?></h2>
                <span id="huidige_iteratie_totaal">
                <?php if($uur_nog_te_doen > 0){ echo $uur_nog_te_doen.'uur';} ?> 
                </span>
            </div>
            <ul id="huidige_iteratie" class="activiteiten">

<?php
$what=" a.id,  a.werkzaamheden,  a.uur_aantal, a.prioriteit, 
		b.titel, c.naam, d.competentie";  
$from=" planning_activiteit a
        LEFT JOIN project AS b ON ( b.id = a.project )
        LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
        LEFT JOIN competentie AS d ON ( d.id = a.competentie )";  
$where="a.iteratie = $iteratie_id
    AND a.actief = 1
    AND a.status = 'to do'
    AND b.actief = 1 
    AND c.actief = 1 
    $filter ORDER BY a.iteratie_volgorde ASC";
//echo "SELECT $what FROM $from WHERE $where";
$select_iteratie = sqlSelect($what, $from, $where);

if(getRecenten($login_id, $iteratie_id)){
	$recenten = getRecenten($login_id, $iteratie_id);
	//$is_gezien = array();
	while($recent = mysql_fetch_array($recenten)){ $huidig_is_gezien[] = $recent['activiteit']; }
}

while($huidig= mysql_fetch_array($select_iteratie)){
   
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = '.$huidig['id'];
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($i == 1 ){$class="act_a"; $i = 0;}else{$class="act_b"; $i = 1;}?>
                <li class="<?php echo $class; ?>" id="activiteit_<?php echo $huidig['id']; ?>xhuidig" onmouseover="this.className='act_hover'" onmouseout="this.className='<?php echo $class; ?>'">
                 	
                 	<span class="dashboard_info">
						<span class="dashboard_organisatie"><?php echo $huidig['naam']; ?></span>
                		<span class="dashboard_project"><?php echo $huidig['titel']; ?></span>
                		<span class="dashboard_persoon"><?php echo $huidig['competentie']; ?></span>
                    </span>
                 	  
                    <span class="dashboard_meldingen_prio">
                        <span class="prioriteit">
                            
                                <img src="/functions/planning/css/images/prio_<?php echo $huidig['prioriteit'] ?>_24px.png" alt="prio <?php echo $huidig['id'] ?>" onclick="changePrioriteit( '<?php echo $huidig['id'] ?>', 'huidige_iteratie')" />

                        </span>
                    </span>    
                        <span class="dashboard_werkzaamheden"><?php echo $huidig['werkzaamheden'] ?>
                            <div class="edit"><a href="<?php echo $wijzigen_url.$huidig['id'] ?>"><img src="/functions/planning/css/images/edit.png"></a></div>
                        </span> 
                        
                        <span class="dashboard_meldingen"> 
                        <?php echo '<span class="dashboard_uren">'.$huidig['uur_aantal'].'</span>';
						
						
                        if(!isset($huidig_is_gezien) || !in_array($huidig['id'], $huidig_is_gezien)){ ?> 
	                    <span class="recentmelding_container">
	                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="<?php echo $huidig['id'] ?>" data-kolom="nogteplannen" />
	                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
	                    </span>						
                        <?php } 
						
                        if(getReactie($huidig['id'], 'aantal') > 0){ echo getReactie($huidig['id'], 'laatste'); }// laat de laatste reactie zien.
                        
                        if($aantal_bestanden > 0){?>
                            <div class="bestanden">
                                <a href="<?php echo $wijzigen_url.$huidig['id'] ?>" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="<?php echo $aantal_bestanden; ?> bestanden" />
                                </a>
                            </div>    
<?php } ?>
                    </span>          
        </li>
<?php } ?>
            </ul>

        <?php
if($_GET['kolom'] == 'alles'){?> </div> <?php }
        }
        if(in_array('overig', $kolommen) || in_array('alles', $kolommen)){
if($_GET['kolom'] == 'alles'){?>
    <div id="overig_container"> 
<?php }
//alle gegevens voor de overige iteraties, die nog moeten gebeuren
$what = "id, DATE_FORMAT(datum, '%d %M %Y') AS datum";
$from= "planning_iteratie";
$where="actief = 1 AND huidige_iteratie = 0 AND actief = 1 AND huidige_iteratie = 0 AND datum > (SELECT a.datum FROM planning_iteratie a WHERE a.huidige_iteratie = 1) LIMIT 5";
    $huidige_iteraties = sqlSelect($what, $from, $where);
    
$container_i = 0;
if($_GET['display'] == 'slider'){?>
<div id="iteratie_slider">        
<?php }
while($overige_iteratie = mysql_fetch_array($huidige_iteraties)){
if($_GET['display'] == 'slider'){
    if(($container_i % 2) == 0){
        if($container_i == 0){ echo '<div id="iteratie_container_1" class="overige_iteratie_slide">'; }
        elseif($container_i == 2){ echo '<div id="iteratie_container_2" class="overige_iteratie_slide" style="display:none;">'; }
        elseif($container_i == 4){ echo '<div id="iteratie_container_3" class="overige_iteratie_slide" style="display:none;">'; }
    }
}elseif($_GET['display'] == 'alles'){
$colum_right_i = $container_i+2; 
    if(($colum_right_i % 3) == 0){$container_class=" container_right";}
    else{unset($container_class);}
}
$iteratie_id = $overige_iteratie['id'];

$what="sum(uur_aantal) totaal_to_do";
$from="planning_activiteit";
$where="iteratie = $iteratie_id AND status = 'to do'";
$uren_result =sqlSelect($what,$from,$where);
$uren_to_do  = mysql_fetch_assoc($uren_result);
    
?>
        <div class="overige_iteratie_container<?php echo $container_class;?>">
            <div class="overige_iteratie_titel">
                <h2><?php echo $overige_iteratie['datum'] ?></h2>
                <span class="overige_iteratie_totaal">
                <?php echo $uren_to_do['totaal_to_do']; ?> uur
                </span>
            </div>
            <ul class="overige_iteratie activiteiten" id="iteratie_id_<?php echo $overige_iteratie['id']; ?>">

<?php
$what=" a.id,  a.werkzaamheden,  a.uur_aantal, a.prioriteit, 
		b.titel, c.naam, d.competentie";  
$from=" planning_activiteit a
        LEFT JOIN project AS b ON ( b.id = a.project )
		LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
        LEFT JOIN competentie AS d ON ( d.id = a.competentie )"; 
$where="a.iteratie = $iteratie_id
    AND a.actief = 1
    AND a.status = 'to do'
    AND b.actief = 1 
    AND c.actief = 1 
    $filter ORDER BY a.iteratie_volgorde ASC";

if(getRecenten($login_id, $iteratie_id)){
	$recenten = getRecenten($login_id, $iteratie_id);
	//$is_gezien = array();
	while($recent = mysql_fetch_array($recenten)){ $overig_is_gezien[] = $recent['activiteit']; }
}

$select_iteratie = sqlSelect($what, $from, $where);
while($overig= mysql_fetch_array($select_iteratie)){    
    $what_bestanden = 'id'; $from_bestanden = 'planning_bestand'; $where_bestanden = 'activiteit = '.$overig['id'];
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden);
    
    if($i == 1 ){$class="act_a"; $i = 0;}else{$class="act_b"; $i = 1;}?>
    
                <li class="plan_mij <?php echo $class; ?>" id="activiteit_<?php echo $overig['id']; ?>xoverig" onmouseover="this.className='act_hover'" onmouseout="this.className='<?php echo $class; ?>'">
                    <span class="dashboard_info">
						<span class="dashboard_organisatie"><?php echo $overig['naam']; ?></span>
                		<span class="dashboard_project"><?php echo $overig['titel']; ?></span>
                		<span class="dashboard_persoon"><?php echo $overig['competentie']; ?></span>
                    </span>
                    <span class="dashboard_meldingen_prio">
                        <span class="prioriteit">
                            
                                <img src="/functions/planning/css/images/prio_<?php echo $overig['prioriteit'] ?>_24px.png" alt="prio <?php echo $overig['id'] ?>"  onclick="changePrioriteit( '<?php echo $overig['id'] ?>', 'overig')" />

                        </span>
                    </span>    
                        <span class="dashboard_werkzaamheden"><?php echo $overig['werkzaamheden'] ?>
                            <div class="edit"><a href="<?php echo $wijzigen_url.$overig['id'] ?>"><img src="/functions/planning/css/images/edit.png"></a></div>
                        </span> 
                        
                        <span class="dashboard_meldingen"> 
                        <?php echo '<span class="dashboard_uren">'.$overig['uur_aantal'].'</span>';
		
                        if(!isset($overig_is_gezien) || !in_array($overig['id'], $overig_is_gezien)){ ?> 
	                    <span class="recentmelding_container">
	                        <img class="recentmelding" src="/functions/planning/css/images/warning.png" alt="commentaar" data-activiteit="<?php echo $overig['id'] ?>" data-kolom="nogteplannen" />
	                        <span class="recente_meldingen" style="display:none;" data-shown="false"><img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;</span>
	                    </span>						
                        <?php } 
						
                        if(getReactie($overig['id'], 'aantal') > 0){ echo getReactie($overig['id'], 'laatste'); }// laat de laatste reactie zien.
                        
                        if($aantal_bestanden > 0){?>
                            <div class="bestanden">
                                <a href="<?php echo $wijzigen_url.$overig['id'] ?>" class="bestanden_container">
                                    <img src="/functions/planning/css/images/attachment.png" alt="<?php echo $aantal_bestanden; ?> bestanden" />
                                </a>
                            </div>    
<?php } ?>
                    </span>          
        </li>
<?php }?>
            </ul>
        </div>
<?php   
    if($_GET['display'] == 'slider'){
      if(($container_i % 2) != 0){ echo '</div>'; }
    }
    $container_i++;
}
if($_GET['display'] == 'slider'){?>
</div>        
<?php }
if($_GET['kolom'] == 'alles'){?> </div>
    </div> <?php }
        }
    }
    elseif($_POST['action'] == 'dragDrop'){
        $huidig_iteratie_id = $_REQUEST['huidig_iteratie_id'];
    
        if($_REQUEST['nog_te_plannen'] != null){
            parse_str($_REQUEST['nog_te_plannen'], $sort_nogteplannen);
            foreach($sort_nogteplannen['activiteit'] as $volgorde => $id){
                 list($activiteit_id, $bron) = explode('x', $id);
                 $table="planning_activiteit";
                 if($bron != 'nogteplannen'){
                    $nogteplannen = $bron;  recentMaken($activiteit_id, $login_id);
                    $what = "status = 'nog te plannen', iteratie = 0, iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 ";
                 }else{ $what = "status = 'nog te plannen', iteratie = 0, iteratie_volgorde = $volgorde, gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 "; }
                 $where = "id = '$id'";
                 $update_planning = sqlUpdate($table, $what, $where);
            }
            if($nogteplannen){echo 'nog_te_plannen, '.$nogteplannen;}
        }
        
        if($_REQUEST['huidige_iteratie']  != null){
            parse_str($_REQUEST['huidige_iteratie'], $sort_huidige);
            foreach($sort_huidige['activiteit'] as $volgorde => $id){
                 list($activiteit_id, $bron) = explode('x', $id);
                 $table="planning_activiteit";
                 if($bron != 'huidig'){
                    $huidig = $bron;  recentMaken($activiteit_id, $login_id);
                    $what = "status = 'to do', iteratie = $huidig_iteratie_id, iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 ";
                 }else{ $what = "status = 'to do', iteratie = $huidig_iteratie_id, iteratie_volgorde = $volgorde, gewijzigd_op = NOW(), in_behandeling_door = 0, actief = 1 ";  }
                 $where = "id = '$id'";
                 $update_planning = sqlUpdate($table, $what, $where);
            }
            if($huidig){echo 'huidige_iteratie, '.$huidig;}
        }
        
        $what = "id, DATE_FORMAT(datum, '%d %M %Y') AS datum";
        $from= "planning_iteratie";
        $where="actief = 1 AND huidige_iteratie = 0";
            $huidige_iteratie = sqlSelect($what, $from, $where);
        while($overige_iteratie = mysql_fetch_array($huidige_iteratie)){
            
            $iteratie_id = $overige_iteratie['id'];
            
            if($_REQUEST["iteratie_id_$iteratie_id"]  != null){
                    parse_str($_REQUEST["iteratie_id_$iteratie_id"], $sort_overige);
                    foreach($sort_overige['activiteit'] as $volgorde => $id){
                         list($activiteit_id, $bron) = explode('x', $id);
                         $table="planning_activiteit";
                         if($bron != 'overig'){
                            $overig = $bron;  recentMaken($activiteit_id, $login_id);
                            $what = "status = 'to do', iteratie = $iteratie_id, iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), gewijzigd_door = $login_id, in_behandeling_door = 0 ";
                         }else{ $what = "status = 'to do', iteratie = $iteratie_id, iteratie_volgorde = $volgorde, gewijzigd_op = NOW(), gewijzigd_door = $login_id, in_behandeling_door = 0 "; }
                         $where = "id = '$id' ";
                         $update_planning = sqlUpdate($table, $what, $where);
                    }
                }
                if($overig){echo 'overig, '.$overig;}
            }
    }
?>