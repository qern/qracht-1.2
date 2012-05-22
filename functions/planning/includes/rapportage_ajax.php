<?php
    if($_GET['action'] != null){
        session_start();            
        require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    }
    if($_GET['action'] == 'project' || $_GET['action'] == null){?>
        
<div id="projectoverzicht">
    <h2>Bekijk hier het aantal geplande uren per project</h2>
    <div id="projectoverzicht_top">
        <span id="projectoverzicht_organisatie_header" class="projectoverzicht_header">Organisatie<br />
        	<span id="projectoverzicht_project_header">Project titel</span>
        </span>
        <!--<span id="projectoverzicht_project_header" class="projectoverzicht_header">Project titel</span>-->
        <span id="projectoverzicht_plan_uren_header" class="projectoverzicht_header">Gepland</span>
        <span id="projectoverzicht_bestede_uren_header" class="projectoverzicht_header">Besteed</span>
        <span id="projectoverzicht_balans_uren_header" class="projectoverzicht_header">Urensaldo</span>
    </div>
    <div id="projectoverzicht_rijen">
    <?php
    $what = 'DISTINCT a.organisatie AS id, b.naam'; $from = 'project a LEFT JOIN organisatie AS b ON (b.id = a.organisatie)'; $where = 'a.actief = 1';
            $organisaties = sqlSelect($what, $from, $where);

    while($organisatie = mysql_fetch_array($organisaties)){
        $organisatie_gepland_totaal = 0; $organisatie_besteed_totaal = 0; 
    ?>
        <div class="projectoverzicht_rij">
            <div class="projectoverzicht_organisatie"> <a href="/crm/detail/organisatie-id=<?php echo $organisatie['id']; ?>" title="bekijk organisatie"> <?php echo $organisatie['naam'] ?> </a> </div>
            <div class="projectoverzicht_lijst">
        <?php 
        $what = ' prj.titel project,
                  prj.id id,
                  DATE_FORMAT(prj.startdatum, \'%d %M %Y\') AS startdatum,
				  DATE_FORMAT(prj.einddatum, \'%d %M %Y\') AS einddatum,
                  sum(act.uur_aantal) gepland_aant_uur';
        $from = ' project prj,
                  planning_activiteit act';
        $where = 'prj.organisatie = '.$organisatie['id'].'
                  AND prj.id = act.project
                  AND act.actief = 1
                  AND act.geldig = 1
                  GROUP BY 1,2,3,4
                  UNION
                SELECT
                 prj.titel project,
                 prj.id id,
                 DATE_FORMAT(prj.startdatum, \'%d %M %Y\') AS startdatum,
				  DATE_FORMAT(prj.einddatum, \'%d %M %Y\') AS einddatum,
                 0
               FROM
                 organisatie org,
                 project prj
               WHERE
                 prj.organisatie = '.$organisatie['id'].'
                 AND prj.id NOT IN (SELECT act.project FROM planning_activiteit act WHERE act.actief = 1 AND act.geldig = 1)
                 GROUP BY 1,2,3,4';
            $project_uren = sqlSelect($what, $from, $where);
                           
            while($project = mysql_fetch_array($project_uren)){?>
                <div class="projectoverzicht_project" data-show="<?php echo $project['id']; ?>">
                    <div class="projectoverzicht_project_uren">
<!--                        <div class="projectoverzicht_project_titel"><span><?php echo $project['project'];?></span><a href="/planning/detail/project-id=<?php echo $project['id']; ?>" title="project bekijken/wijzigen">(naar project)</a></div>-->
                        <div class="projectoverzicht_project_titel">
                        	<a href="/planning/detail/project-id=<?php echo $project['id']; ?>" title="project bekijken/wijzigen"><?php echo $project['project'];?></a><br />
                        	<?php if($project['startdatum'] != null || $project['einddatum'] != null){ ?>
                        	<div class="project_datum">
                        		<?php if($project['startdatum'] != null){ ?><span class="project_datum_vanaf"><?php echo $project['startdatum']; ?></span><?php } ?>
                                <?php if($project['einddatum'] != null){ ?><span class="project_datum_tot"><?php echo $project['startdatum']; ?></span><?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="projectoverzicht_project_plan_uren"><?php echo $project['gepland_aant_uur']; ?></div>
                        <?php 
                            $what = 'SUM(b.aantal_uur) AS uren'; $from  = 'planning_activiteit a LEFT JOIN urentool_registratie AS b ON (b.activiteit = a.id)';
                            $where = 'a.project = '.$project['id'].' AND actief = 1';
                                $project_bestede = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                            
                            if($project_bestede['uren'] == 0){$project_bestede_uren = 0;}
                            else{$project_bestede_uren = $project_bestede['uren'];}
                            
                            $balans_uren = ($project['gepland_aant_uur'] - $project_bestede['uren']);
                            if($balans_uren > 0){$project_balans = " project_balans_pos";}      //boven nul... we hebben uren 'over'
                            elseif($balans_uren == 0|| $balans_uren == null){$project_balans = " project_balans_be"; $balans_uren = 0;}  //break even ... ofwel... 0
                            elseif($balans_uren < 0){$project_balans = " project_balans_neg";}  //onder nul... we hebben teveel besteed
                            
                        ?>
                        <div class="projectoverzicht_project_bestede_uren"><?php echo $project_bestede_uren; ?></div>
                        <div class="projectoverzicht_project_balans_uren <?php echo $project_balans ?>"><?php echo $balans_uren; ?></div>
                    </div>
                    <div class="projectoverzicht_competenties" data-project="<?php echo $project['id']; ?>" style="display:none;">
                        <?php
                            $what = 'comp.competentie,
                                     comp.id,
                                     sum(act.uur_aantal) gepland_aant_uur';
                            $from = '
                                     planning_activiteit act,
                                     competentie comp';
                            $where = '
                                     act.project = '.$project['id'].'
                                     AND act.actief = 1
                                     AND act.geldig = 1
                                     AND act.competentie = comp.id
                                     GROUP BY 1';
                            $competenties =  sqlSelect($what, $from, $where);
                            //echo  "SELECT $what FROM $from WHERE $where  <br />";
                            while($competentie = mysql_fetch_array($competenties)){?>
                            <div class="projectoverzicht_competentie">
                                <div class="projectoverzicht_competentie_titel"><?php echo $competentie['competentie']; ?></div>
                                <div class="projectoverzicht_competentie_plan_uren"><?php echo $competentie['gepland_aant_uur']; ?></div>
                                <?php 
                                    $what = 'SUM(b.aantal_uur) AS uren'; $from  = 'planning_activiteit a LEFT JOIN urentool_registratie AS b ON (b.activiteit = a.id)';
                                    $where = 'a.competentie = '.$competentie['id'].' AND a.project = '.$project['id'].' AND a.actief = 1';
                                        $competentie_bestede = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                                        
                                        if($competentie_bestede['uren'] == 0){$competentie_bestede_uren = 0;}
                                        else{$competentie_bestede_uren = $competentie_bestede['uren'];}
                                        
                                        $balans_uren = ($competentie['gepland_aant_uur'] - $competentie_bestede_uren);
                                        if($balans_uren > 0){$competentie_balans = "competentie_balans_pos";}      //boven nul... we hebben uren 'over'
                                        elseif($balans_uren == 0 || $balans_uren == null){$competentie_balans = "competentie_balans_be"; $balans_uren = 0;}  //break even ... ofwel... 0
                                        elseif($balans_uren < 0){$competentie_balans = "competentie_balans_neg";}  //onder nul... we hebben teveel besteed
                                ?>
                                <div class="projectoverzicht_competentie_bestede_uren"><?php echo $competentie_bestede_uren; ?></div>
                                <div class="projectoverzicht_competentie_balans_uren <?php echo $competentie_balans ?>"><?php echo $balans_uren; ?></div>
                            </div>    
                            <?php } ?>
                    </div>
                </div>
            <?php }
             ?>
            </div>
        </div>
    <?php } ?>
    </div>  
</div>
<?php }elseif($_GET['action'] == 'begroot'){?>  
        
<?php }?>
