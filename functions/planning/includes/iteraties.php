<?php
$what="id, datum d2, DATE_FORMAT(datum, '%d %M %Y') AS datum, huidige_iteratie";
$from="planning_iteratie"; $where="actief = 1 ORDER BY d2 ASC";
$select_iteratie = sqlSelect($what, $from, $where);
?>
<div id="planning">
    <div id="overzicht_iteraties">
        
        <div id="overzicht_iteraties_header">
            <h2 id="overzicht_iteraties_titel">Overzicht iteraties</h2>
            <a href="/planning/iteraties_bewerken" id="bewerk_iteraties" class="tooltip" title="Bewerk deze iteraties, door nog te plannen acties toe te voegen.">bewerk</a>
        </div>    
        
        <div id="iteratie_lijst">
            <div id="iteratie_lijst_header">
                <div id="einddatum_header">Deadline</div>
                <div id="uur_resterend_header">Uur resterend</div>
            </div>
            <div id="iteraties">
<?php
while($iteratie = mysql_fetch_array($select_iteratie)){
    //eerst de bovenste rij vullen
    $iteratie_id = $iteratie['id'];
    
    //het aantal uren, dat nog resteerd voor deze iteratie
    $what = 'sum(uur_aantal)  uren';  $from="planning_activiteit"; $where="iteratie = $iteratie_id AND status !=  'nog te plannen' AND status !=  'done' AND actief = 1 ";
    $totaal = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    //check hoeveel open uren er nog staan in de huidige iteratie
    $what = 'sum(b.uur_aantal)  uren';  $from=" planning_iteratie a LEFT JOIN planning_activiteit AS b ON (b.iteratie = a.id)";
    $where="a.huidige_iteratie = 1 AND b.status !=  'nog te plannen' AND b.status !=  'done' AND b.actief = 1 ";
    $open_uren = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    
    //het aantal uren per status, van deze iteratie
    $what="status , sum( uur_aantal ) status_uren"; $from = "planning_activiteit"; $where = "iteratie = $iteratie_id AND actief = 1 GROUP BY status";
    $status_uren = sqlSelect($what, $from, $where);
    while($uren_per_status = mysql_fetch_array($status_uren)){
        $status = $uren_per_status['status'];
        $uren_status["$status"] = '<li><span class="status_status">'.$status.'</span>('.$uren_per_status['status_uren'].') </li>';
    }
    
    //het aantal uren per bedrijf, van deze iteratie
    $what=" sum(a.uur_aantal) bedrijfsuren,  c.naam";  $from=" planning_activiteit a LEFT JOIN project AS b ON (b.id = a.project)  LEFT JOIN organisatie AS c ON (c.id = b.organisatie)";
    $where ="a.iteratie = $iteratie_id  AND a.status !=  'nog te plannen'
         AND a.actief = 1  AND b.actief = 1  AND c.actief = 1  GROUP BY c.naam";
     $bedrijf_uren = sqlSelect($what, $from, $where);
    
    //welke werknemers bezig zijn met de werkzaamheden binnen deze iteratie
    $what ="DISTINCT (b.achternaam),  b.voornaam,  b.gebruikersnaam";
    $from=" planning_activiteit a LEFT JOIN portal_gebruiker AS b ON(b.id = a.in_behandeling_door)";
    $where="a.actief = 1  AND a.iteratie = $iteratie_id  AND b.actief = 1";
    $actieve_werknemers = sqlSelect($what, $from, $where);
	
	if($i == 1){ $class = 'en_om'; $i = 0; }else{ $class= 'om'; $i = 1; }	
?>
                <div class="iteratie_rij <?php echo $class; ?>" data-show="<?php echo $iteratie_id; ?>">
                    <div class="datum_veld"><?php echo $iteratie['datum'];  ?></div>
                    <div class="uur_resterend_veld">
                     <?php  if($totaal['uren'] > 0){echo $totaal['uren'];}
                            else{echo '<a href="/planning/iteraties_bewerken" class="tooltip" title="voeg een activiteit toe aan deze iteratie"> Geen activiteiten</a>';}
                     ?>
                     </div>
                    <div class="actie_veld">
<?php 

if($iteratie['huidige_iteratie'] == 1){ echo '<a href="/planning/dashboard" target="_blank" title="bekijk deze actieve iteratie">Actief</a>';}
else{
    if($open_uren['uren'] > 0){      
        echo '<div class="activeer_iteratie">
                <a href="#" class="tooltip" title="Er staan nog '.$open_uren['uren'].' uren open in de huidige iteratie. Zorg er eerst voor dat er geen uren meer open staan in de huidige iteratie. Daarna kunt u deze iteratie activeren.">
                    <img src="/functions/planning/css/images/activeer.png" class="activate_iteratie" />
                </a>
            </div>';
    }else{
        echo '<div class="activeer_iteratie">
                <a href="/functions/planning/includes/iteratie_check.php?iteratie_id='.$iteratie_id.'&action=activeer_iteratie" title="activeer iteratie">
                    <img src="/functions/planning/css/images/activeer.png" class="activate_iteratie" />
                </a>
            </div>';
    }
    if($totaal['uren'] > 0){      
        echo '<div class="delete_iteratie">
                <a href="#" class="tooltip" title="Er staan nog '.$totaal['uren'].' uren open in deze iteratie. Zorg er eerst voor dat er geen uren meer open staan in de huidige iteratie. Daarna kunt u deze iteratie verwijderen.">
                    <img src="/functions/planning/css/images/delete.png" class="verwijder_iteratie" />
                </a>
            </div>';
    }else{
     echo '<div class="delete_iteratie">
                <a href="/functions/planning/includes/iteratie_check.php?iteratie_id='.$iteratie_id.'&action=verwijder_iteratie" title="verwijder iteratie">
                    <img src="/functions/planning/css/images/delete.png" class="verwijder_iteratie" />
                </a>
            </div>';
     }
} ?>                  
                    </div>
                
                <div class="iteratie_sub" onmouseover="this.className='iteratie_sub iteratie_sub_hover'" onmouseout="this.className='iteratie_sub'" style="display:none;" data-iteratie="<?php echo $iteratie_id; ?>"> 
                    <div class="uren_per_status">
                        <ul class="uren-status">
                        <?php
                            echo $uren_status['to do'].$uren_status['onderhanden'].$uren_status['acceptatie'].$uren_status['done'];
                            unset($uren_status);
                        ?>
                        </ul>
                    </div>
                    <div class="uren_per_klant">
                        <ul class="uren-klant">
                        <?php
                            while($uren_per_bedrijf = mysql_fetch_array($bedrijf_uren)){
                                echo '<li><span class="uren_bedrijf">'.$uren_per_bedrijf['naam'].'</span><span class="bedrijf_uren">'.$uren_per_bedrijf['bedrijfsuren'].'</span> </li>';
                            }
                        ?>
                        </ul>
                    </div>
                    <div class="actieve_werknemers">
                        <ul class="werknemers">
<?php
while($werknemers_op_iteratie = mysql_fetch_array($actieve_werknemers)){
    echo '  <li>
                <a href="/profiel/'.$werknemers_op_iteratie['gebruikersnaam'].'" target="_blank" title="bekijk het profiel">'.$werknemers_op_iteratie['voornaam'].' '.$werknemers_op_iteratie['achternaam'].'</a> 
            </li>';
}
?>
                        </ul>
                    </div>
                    

                </div>  
                </div> 
<?php } ?>
            </div>
        </div>        
    </div>
</div>
