<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/portal/check_configuration.php');
    
   if($_POST['action'] == 'zoekBestand'){
        $zoekwoord  = $_POST['zoekwoord'];
        $what = 'b.id, b.nieuws, b.path, UNIX_TIMESTAMP(b.toegevoegd_op), c.titel';
        $from = 'portal_nieuws_gebruiker a, portal_nieuws_bestand b, portal_nieuws c'; 
    	$where="a.gebruiker = $login_id AND b.nieuws = a.nieuws AND c.id = a.nieuws AND b.actief = 1 AND b.path LIKE '%$zoekwoord%' ORDER BY a.path ASC"; 
        $bestanden_res_res = sqlSelect($what, $from, $where);
          //echo "SELECT $what FROM $from WHERE $where";
          
            
        while($bestand = mysql_fetch_array($bestand_res)){
            $bestandinfo = pathinfo($bestand['path']);
            // is de tekst meer dan 150 tekens ?
            if(strlen($bestandinfo['filename']) > 30){
                //hoeveel tekens te veel hebben we ?
                $offset = strlen($bestandinfo['filename']) - 30;
                //we gaan de string verkleinen... met $offset aantal tekens van het einde af.
                $tekst = substr($bestandinfo['filename'], 0, -$offset);
                $bestandsnaam = $tekst.'...';
            }else{
                //de tekst mag gewoon de tekst blijven.
                $bestandsnaam = $bestandinfo['filename'];
            }
    		$array[] ='    
                <div class="bestand" onmouseover="this.className=\'bestand bestand_hover\'" onmouseout="this.className=\'bestand\'">
                    <a href="/portal/functions/bestanden/includes/download.php?id='.$bestand['nieuws'].'&file='.$bestand['path'].'" target="_blank" title="download bestand">
                        '.$bestandsnaam.'.'.$bestandinfo['extension'].'
                    </a>
                </div>';
		}
        //gebruik de data om de het overzicht te herladen.
        $refreshFiles = 1;
    }
    
if($refreshFiles ==  1){
    //bepaal hoeveer arrays er in totaal zijn
    $aantal_arrays = count($array);
    
    //deel dit door het aantal kolommen dat we willen : 3
    //de som verdelen we in een geheel getal en de decimaal.
    //$rounded = strval(round(($aantal_albums/3), 1)).'<br />';
    list($heel, $decimaal) = explode('.', round(($aantal_arrays/3), 1));
    
    //-------KOLOM 1
    
    //kijk of er meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kolom">
        <?php //begin nu met tellen.
        $i_1 = 0; 
        if($decimaal != null){
            if($decimaal == '3'){
                $aantal_in_kolom_1 = $heel+1;
            }elseif($decimaal == '7'){
                $aantal_in_kolom_1 = $heel+1;
            }
        }else{
            $aantal_in_kolom_1 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_1 <= $aantal_in_kolom_1-1 && $i_1 <= $aantal_arrays-1){?>
            <div class="afbeelding_item">
                <?php echo $array["$i_1"] ?>
            </div>
                   <?php 
            $i_1++;
                }

            }?>
    </div>  
    <?php }else{?>
            <div class="act_a">Dit filter leverde geen activiteiten op. Probeer het alstublieft opnieuw.</div> 
    <?php } 
    
    //-------KOLOM 2
    
    //kijk of  meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kolom">
        <?php //tellen voor de tweede kolom
        $i_2 = $i_1;
        if($decimaal != null){
            if($decimaal == '3'){
                $aantal_in_kolom_2 = $heel;
            }elseif($decimaal == '7'){
                $aantal_in_kolom_2 = $heel+1;
            }
        }else{
            $aantal_in_kolom_2 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_2 <= $aantal_in_kolom_2+$i_1-1 && $i_2 <= $aantal_arrays-1){?>
            <div class="afbeelding_item">
                <?php echo $array["$i_2"] ?>
            </div>
                   <?php 
            $i_2++;
            }
        }
         ?>
    </div>
        <?php
    }
    
    //-------KOLOM 3
    
    //kijk of  meer dan 0 reaties zijn
    if($aantal_arrays > 0){?>
    <div class="afbeeldingen_kolom_last">
        <?php //tellen voor de tweede kolom
        $i_3 = $i_2;
        if($decimaal != null){
            if($decimaal == '3'){
                $aantal_in_kolom_3 = $heel;
            }elseif($decimaal == '7'){
                $aantal_in_kolom_3 = $heel;
            }
        }else{
            $aantal_in_kolom_3 = $heel;
        }
        foreach($array as $afbeelding_rij){
            if($i_3 <= $aantal_in_kolom_3+$i_2-1 && $i_3 <= $aantal_arrays-1){?>
            <div class="afbeelding_item">
                <?php echo $array["$i_3"] ?>
            </div>
                   <?php 
            $i_3++;
            }
        }
         ?>
    </div>
        <?php
    }
}
?>