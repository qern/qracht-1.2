<?php
$what="	COUNT( b.id ) AS aantal_activiteiten , a.id"; 
$from=" planning_iteratie a 
		LEFT JOIN planning_activiteit AS b ON ( b.iteratie = a.id AND b.status IN ( 'to do',  'onderhanden',  'acceptatie' ) ) ";  
$where="a.huidige_iteratie =1
		AND b.actief =1
		AND a.actief =1";
$aantal = countRows($what, $from, $where);
if($aantal == 1){
$iteratie = mysql_fetch_assoc(sqlSelect($what,$from,$where));
$iteratie_id = $iteratie['id'];
$wijzigen_url = '/'.$_GET['function'].'/dashboard/activiteit-wijzigen/activiteit-id=';
?>

<div id="planning_iteratie_schema">
    
<script>var iteratie_Id = '<?php echo $iteratie['id']; ?>';</script>
<div id="dashboard_menu">
    <div id="activators">
        <img src="/functions/<?php echo $_GET['function']; ?>/css/images/filter.png" alt="filters" id="filter_activator" class="active_activator" />
        <img src="/functions/<?php echo $_GET['function']; ?>/css/images/clock.png" alt="uren" id="tijd_activator" />
    </div>
    <div id="filter_prioriteit">
        <img class="active_prio" src="/functions/<?php echo $_GET['function'] ?>/css/images/remove_prio_24px.png" alt="verwijder filter op prioriteit" data-prio="0" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_1_24px.png" alt="filteren op prio 1" data-prio="1" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_2_24px.png" alt="filteren op prio 2" data-prio="2" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_3_24px.png" alt="filteren op prio 3" data-prio="3" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_4_24px.png" alt="filteren op prio 4" data-prio="4" />  
    </div>
    <div id="filter_recent">
    	<?php 
    	
    	if(getRecenten($login_id, $iteratie_id)){ $aantal_recenten = mysql_numrows(getRecenten($login_id, $iteratie_id)); }

    	if($aantal_recenten < $iteratie['aantal_activiteiten']){ ?>
    	<img src="/functions/<?php echo $_GET['function'] ?>/css/images/remove_recent.png" alt="zet de recenten uit" title="Klik hier om de recentmeldingen uit te zetten."  />
    	<?php } ?>
    </div>
    <div id="filters">
            <div id="organisatie_placeholder">
                <label for="organisatie_field">Welke organisatie(s)</label>
                <input type="text" id="organisatie_field" class="textfield" />
                
                <div id="organisaties">
                </div>
                
            </div>        
            
            <div id="projecten_placeholder">
                <label for="project_field">Welk(e) project(en)</label>
                <input type="text" id="project_field" class="textfield" />
                
                <div id="projecten">
                </div>
                
            </div>
           
             <div id="competentie_placeholder">
                <label for="competentie_field">Welk(e) competentie(s)</label>
                <input type="text" id="competentie_field" class="textfield" />
                
                <div id="competenties">
                </div>
                
            </div>
            
            <div id="medewerker_placeholder">
                <label for="medewerker_field">Welk(e) medewerker(s)</label>
                <input type="text" id="medewerker_field" class="textfield" />
                
                <div id="medewerkers">
                </div>
                
            </div>
        
            <div id="uren_zoekwoord">
                
                <p>Aantal uur</p>
                
                <div id="uren_min">
                    <label for="min_uren">min</label>
                    <input type="text" class="textfield" id="min_uren" />
                </div> 
                
                <div id="uren_max">
                    <label for="max_uren">max</label>
                    <input type="text" class="textfield" id="max_uren" />
                </div> 
                
            </div>
        
            <div id="zoekwoord_placeholder">
                
                <label for="zoekwoord_field">zoekwoord</label> 
                <input type="text" class="textfield" id="zoekwoord_field" name="zoekwoord" />
                
                <div id="zoekwoorden">
                </div>
                
            </div>
        
            <div id="filteren">
                <!--<button id="filters_verwijderen" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Filter verwijderen</button>-->
                <button id="filters_versturen" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">filteren</button>
            </div>

        </div>
        
        <div id="uren" style="display:none;">
        <?php
        
            $what = 'a.id, DATE_FORMAT(a.datum, \'%d %M %Y\') AS datum, a.huidige_iteratie, SUM(b.uur_aantal) AS geplande_uren, b.iteratie'; 
            $from = 'planning_iteratie a LEFT JOIN planning_activiteit AS b ON (b.iteratie = a.id AND b.actief = 1 AND b.status != \'done\') '; 
            $where = 'a.actief = 1 AND a.huidige_iteratie = 1';
                $iteratie_uren = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            
            $what = 'SUM( a.uren ) AS uren'; $from = 'portal_gebruiker_competentie a LEFT JOIN portal_gebruiker AS b ON ( b.id = a.gebruiker )'; $where = 'b.actief =1';
                $competentie_totaal = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
            //hoe veel iteraties zijn er (al) ?
            $what = 'iteratie_duur'; $from =  'planning_instellingen'; $where = 'actief = 1';
                $planning_config = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                 ?>
            <div class="iteratie_uren active_uren" >
                <div class="iteratie_top">
                    <?php
                        $overgebleven_uren = ( ( $competentie_totaal['uren'] * $planning_config['iteratie_duur'] ) - $iteratie_uren['geplande_uren'] );
                        if($overgebleven_uren > 0){ $iterate_totaal = '<span class="positief">'.$overgebleven_uren.' uur</span>'; }
                        elseif($overgebleven_uren == 0){ $iterate_totaal ='<span class="breakeven">'.$overgebleven_uren.' uur</span>';}
                        else{$iterate_totaal = '<span class="negatief">'.$overgebleven_uren.' uur</span>';}
                    ?>
                    <h2 class="iteratie_datum"><?php echo $iteratie_uren['datum'] ?></h2> <h2 class="iteratie_uur"><?php echo $iterate_totaal  ?></h2>
                </div>
                <div class="ajax_response">
                <?php 
                    $what = 'a.id, a.competentie AS naam, SUM(b.uren) AS totaal_uren'; $from = 'competentie a LEFT JOIN portal_gebruiker_competentie AS b ON (b.competentie = a.id)'; 
                    $where = '1 GROUP BY (a.id)';
                        $competenties = sqlSelect($what, $from, $where); 
                    //echo "SELECT $what FROM $from WHERE $where";
                    
                    while($competentie = mysql_fetch_array($competenties)){?>
                    <div class="competentie">
                        <div class="competentie_naam"><?php echo ucfirst($competentie['naam']); ?></div>
                        <div class="competentie_uren">
                        <?php 
                        
                        //hoeveel uur is er gebruikt van deze competentie, binnen deze iteratie
                        $what = 'SUM(uur_aantal) AS totaal'; $from = 'planning_activiteit'; $where = 'status !=  \'done\' AND competentie = '.$competentie['id'].' AND iteratie = '.$iteratie_uren['id'];
                            $competentie_uren = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                            
                        //meer dan 0 ? dan is het beschikbare uren - gebruikte uren
                        if($competentie_uren['totaal'] > 0){
                            $overgebleven_uren = ( ( $competentie['totaal_uren'] * $planning_config['iteratie_duur'] ) - $competentie_uren['totaal'] );
                            if($overgebleven_uren > 0){?>
                                <span class="positief"><?php echo $overgebleven_uren; ?></span>
                            <?php }elseif($overgebleven_uren == 0){?>
                                <span class="breakeven"><?php echo $overgebleven_uren; ?></span>
                            <?php }else{?>
                                <span class="negatief"><?php echo $overgebleven_uren; ?></span>
                            <?php }
                        }else{
                            $overgebleven_uren = ($competentie['totaal_uren'] * $planning_config['iteratie_duur']);
                            if($overgebleven_uren > 0){?>
                                <span class="positief"><?php echo $overgebleven_uren; ?></span>
                            <?php }elseif($overgebleven_uren == 0){?>
                                <span class="breakeven"><?php echo $overgebleven_uren; ?></span>
                            <?php }
                        }?>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
        </div>
</div>
    <div id="planning_main">
        
        <div id="planning_kolommen">  
            <div id="loading_img">
                <img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;
            </div>          
        </div>
        
    </div>
    
<?php
}else{?>
<div id="planning">
    <h2>Er is (nog) geen actieve iteratie. <a href="/planning/iteraties" title="iteratie aanmaken">Maak deze aan.</a></h2>
</div>
<?php } ?>
