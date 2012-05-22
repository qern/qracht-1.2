<?php
    session_start();
    //require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
	require('planning_config.php');
    //welke iteratie moet in detail getoond worden, qua uren ?
    $iteratie_id = $_GET['id'];
$what = 'a.id, a.competentie AS naam, SUM(b.uren) AS totaal_uren'; $from = 'competentie a LEFT JOIN portal_gebruiker_competentie AS b ON (b.competentie = a.id)'; 
                    $where = '1 GROUP BY (a.id)';
                        $competenties = sqlSelect($what, $from, $where);
               
                    while($competentie = mysql_fetch_array($competenties)){?>
                    <div class="competentie">
                        <div class="competentie_naam"><?php echo ucfirst($competentie['naam']); ?></div>
                        <div class="competentie_uren">
                        <?php 
                        
                        //hoeveel uur is er gebruikt van deze competentie, binnen deze iteratie
                        $what = 'SUM(uur_aantal) AS totaal'; $from = 'planning_activiteit'; $where = 'status =  \'to do\' AND competentie = '.$competentie['id'].' AND iteratie = '.$iteratie_id;
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