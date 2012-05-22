<div id="home">
    <div id="activiteiten_row">
        <div id="iteratie_blok">
            <div id="iteratie_headers">
                <div class="kies_werknemer">
                    <select class="textfield" id="select_werknemer">
                    <?php
                        //wat is het id van de huidige iteratie. Die kunnen we gebruiken.
                        $what="id";$from="planning_iteratie"; $where="actief = 1 AND huidige_iteratie = 1";
                        	$iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $iteratie_id = $iteratie['id'];
                        
                        //welke werknemers bezig zijn met de werkzaamheden binnen deze iteratie
                        $what ="DISTINCT b.achternaam,  b.voornaam, b.id";
                        $from=" planning_activiteit a 
                                LEFT JOIN portal_gebruiker AS b ON(b.id = a.in_behandeling_door)
                                LEFT JOIN planning_iteratie AS c ON (c.id = a.iteratie)";
                        $where="a.actief = 1 AND b.actief = 1 AND c.huidige_iteratie = 1";
                        	$actieve_werknemers = sqlSelect($what, $from, $where);
						
                        while($werknemers_op_iteratie = mysql_fetch_array($actieve_werknemers)){
                            echo '<option value="'.$werknemers_op_iteratie['id'].'">'.ucfirst($werknemers_op_iteratie['voornaam']).' '.ucfirst($werknemers_op_iteratie['achternaam']).'</option>';
                        }
                        echo '<option value="0" selected="selected">Alles</option>';
                    ?>
                    </select>
                </div>
                <div class="iteratie_header">To do</div>
                <div class="iteratie_header">Onderhanden</div>
                <div class="iteratie_header">Acceptatie</div>
                <div class="iteratie_header">Done</div>
                <div class="iteratie_header">Totaal</div>
            </div>
            <div id="iteratie_ajax">De lijst wordt geladen &nbsp;</div>
        </div>
    </div>
    <div id="dashboard_blokken">
    	        
        <div class="dashboard_kolom">
            <div id="laatste_reacties">
                <div class="blok_content">
                <?php
                    //haal de lijst op
                    $what="
                        a.inhoud, DATE_FORMAT(a.geschreven_op, '%d %M %Y') AS geschreven_op, a.geschreven_op d2,
                        b.activiteit, c.gebruikersnaam, c.voornaam, c.achternaam, d.werkzaamheden ";
                    $from="
                        portal_reactie a
                        LEFT JOIN planning_reactie AS b ON (b.reactie = a.id)
                        LEFT JOIN portal_gebruiker AS c ON (c.id = a.geschreven_door)
                        LEFT JOIN planning_activiteit AS d ON (d.id = b.activiteit)";
                    $where="a.actief = 1  AND c.actief = 1 AND d.actief = 1 ORDER BY d2 DESC LIMIT 5";
                    //echo "SELECT $what FROM $from WHERE $where";
                    $aantal_reacties = countRows($what, $from, $where);
                ?>
                    <h2 class="blok_header">Laatste <?php echo $aantal_reacties ?> reacties</h2>

                    <?php
                    if($aantal_reacties > 0){
                    $result = sqlSelect($what,$from,$where);
                    while($row = mysql_fetch_array($result)){
                        	
                        $reactie = '';
                        
                        if(strlen($row['inhoud']) > 150){//delimit de tekst van de reactie. Het kan namelijk erg lang worden, zo'n reactie.
                            
                            $offset = strlen($row['inhoud']) - 150;//hoeveel tekens te veel hebben we ?
                            $tekst = substr($row['inhoud'], 0, -$offset);//we gaan de string verkleinen... met $offset aantal tekens van het einde af.
                            $woorden =  explode(' ', $tekst);//pak elk afzonderlijk woord
                            $aantal_woorden = COUNT($woorden);//tel hoeveel woorden er zitten in de array
                            $woorden[$aantal_woorden-1] = '...';//pak het laatste woord en maak daar ... van
                            foreach($woorden as $woord){ $reactie .= $woord.' '; }//gooi alle woorden achter elkaar en zet de spatie weer achter elk woord.
                            
                        }else{ $reactie = $row['inhoud']; }//de tekst mag gewoon de tekst blijven.
                        
                        if($i == 1){$class="en_om"; $i=2;}else{$class="om"; $i=1;}?>
                    
                        <div class="laatste_reactie <?php echo $class; ?>" onmouseover="this.className='laatste_reactie_hover <?php echo $class; ?>'" onmouseout="this.className='laatste_reactie <?php echo $class; ?>'">
                            <div class="reactie_header">
                                <div class="geschreven_door">
                                    <a href="/profiel/<?php echo $row['gebruikersnaam']; ?>" class="tooltip" title="bekijk het profiel van <?php echo $row['voornaam'].' '.$row['achternaam']; ?>" >
                                        <?php echo $row['voornaam'].' '.$row['achternaam']; ?>
                                    </a>
                                </div>
                                <div class="geschreven_op"><?php echo $row['geschreven_op']; ?>
                                </div>
                                <div class="reactie_link">
                                    <a href="/planning/detail/activiteit-id=<?php echo $row['activiteit']; ?>" class="tooltip" title="bekijk deze activiteit">
                                        Bekijk activiteit
                                    </a>
                                </div>                                
                                <div class="reactie_op"><?php echo $row['werkzaamheden']; ?></div>
                            </div>
                            <div class="reactie_body_teaser">
                                <p><?php echo $reactie; ?></p>
                            </div>
                            <div class="reactie_body_full">
                                <p><?php echo $row['inhoud']; ?></p>
                            </div>
                            
                        </div> 
                  <?php }
                  }	?>
 
                </div>
            </div>
        </div>
        
        <div class="dashboard_kolom last_kolom">
            <div id="nieuwe_uren">
                <div class="blok_content">
                    <h2>Openstaande dagen</h2>
                    <p>Klik op een dag om uren voor die dag in te vullen</p>
                    <ul id="in_te_vullen_dagen">
                    <?php
                    //haal de lijst op
                    $what="id,  dag,  maand,  jaar,  datum  d2,  DATE_FORMAT(datum, '%W %d %M %Y') AS datum";
                    $from="urentool_datum";
                    $where="datum <= CURDATE()  AND gereed = 0  AND gebruiker = $login_id  ORDER BY d2 ASC LIMIT 10";
                 		$count_dagen = countRows($what,$from,$where);//hoeveel dagen zijn er die hier aan voldoen?
            
                    //als er meer dan 0 dagen zijn die er aan voldoen, maak dan een lijst van de datums.
                    if($count_dagen > 0 ){
						$result = sqlSelect($what,$from,$where);
					 	while($row = mysql_fetch_array($result)){ ?>
                            <li class="selecteer_dag"><a href="/urentool/<?php echo $row['dag'].'-'.$row['maand'].'-'.$row['jaar'] ?>"><?php echo $row['datum'] ?></a></li> 
                        <?php }//einde while
                    }else{?>
                        <span id="lege_dagen">Geen dagen meer</span>
                    <?php }  ?>
                    </ul>
                    <a href="/urentool"  title="Klik hier om naar de urentool te gaan." class="tooltip" id="naar_urentool">
                        <h2>Naar Urentool</h2>
                    </a>
                </div>
            </div>
        </div>
     </div>
</div>