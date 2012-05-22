<?php
require('check_configuration.php');
  error_reporting(E_ALL ^ E_NOTICE);
  ini_set('display_errors', 'on');
   /*$what = 'datum, dag, maand, jaar'; $from = 'urentool_datum'; $where = 'gebruiker = 7 ORDER BY datum DESC';
   		$datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        //echo "SELECT $what FROM $from WHERE $where";
	list($vandaag_dag, $vandaag_maand, $vandaag_jaar) = explode('-', strftime('%#d-%m-%Y'));
	if($datum['datum'] == null){ $max_dag = $vandaag_dag; $max_maand = $vandaag_maand; $max_jaar = $vandaag_jaar; }
	elseif($vandaag_dag == $datum['dag'] && $vandaag_maand == $datum['maand'] && $vandaag_jaar == $datum['jaar']){/* we doen niets. Deze dag bestaat al }
	else{
		$max_dag = (int) $datum['dag']; $max_maand = (int) $datum['maand']; $max_jaar = (int) $datum['jaar']; 
		$vandaag_dag = (int) $vandaag_dag; $vandaag_maand = (int) $vandaag_maand; $vandaag_jaar = (int) $vandaag_jaar;
	}
	

	if($max_dag != null){
		// nu komt het aanmaken van de datums
		// te beginnen bij een verschil in jaren
	    if($max_jaar < $vandaag_jaar){
	    	//hoeveel jaren praten we over ?
			$aantal_jaren = $vandaag_jaar - $max_jaar;
			if($aantal_jaren == 1){
				//het scheelt 'maar' 1 jaar.. maar, hoeveel maanden ?
				$aantal_maanden = 13 - $max_maand;
				if($aantal_maanden == 1){
					//eerst zorgen dat we bij de eerste van de huidige maand komen
					$aantal_dagen =  leapYear( $max_jaar, $max_maand ) - $max_dag;
					for($i = 1; $i <= $aantal_dagen; $i++){
						$nieuwe_dag =  $max_dag+$i;
						$insert_time_value[] = "(STR_TO_DATE('$nieuwe_dag $max_maand $max_jaar','%d %m %Y'), $nieuwe_dag, $max_maand, $max_jaar, $gebruiker_id, NOW(), $gebruiker_id)";
					}
				}else{
					//eerst gaan we de voor-voorgaande maanden aanmaken en dan de laatste maand
					for($i = 0; $i < $aantal_maanden; $i++){
						if($i == 0){$begin_dag = $max_dag;}else{$begin_dag = 1;}
							
							$deze_maand = $max_maand + $i;
							$aantal_dagen_deze_maand = leapYear( $max_jaar, $deze_maand );
							for($i2 = 1; $i2 <= $aantal_dagen_deze_maand; $i2++){ $insert_time_value[] = "(STR_TO_DATE('$i2 $deze_maand $max_jaar','%d %m %Y'), $i2, $deze_maand, $max_jaar, $gebruiker_id, NOW(), $gebruiker_id)"; }
					}
				}
			}else{
				
			}
		// we beginnen hieronder met de eerste maand van het nieuwe (huidige) jaar
		$max_maand = 1;
	    }
		if($max_maand < $vandaag_maand){
			//Het is één of meer maanden geleden, hoeveel maanden ?
			$aantal_maanden = $vandaag_maand - $max_maand;
			if($aantal_maanden == 1){
				//Het is maar 1 maand, dus we makan die maand vol
				$aantal_dagen =  leapYear( $max_jaar, $max_maand ) - $max_dag;
				for($i = 0; $i <= $aantal_dagen; $i++){
					$nieuwe_dag =  $max_dag+$i;
					$insert_time_value[] = "(STR_TO_DATE('$nieuwe_dag $max_maand $vandaag_jaar','%d %m %Y'), $nieuwe_dag, $max_maand, $vandaag_jaar, $gebruiker_id, NOW(), $gebruiker_id)";
				}
			}else{
				//Het zijn er meerdere. Dus we vullen die maanden helemaal.
				for($i = 0; $i < $aantal_maanden; $i++){
					//alleen de eerste keer hoeft er rekening gehouden te worden met de begindag. Verder niet.
					if($i == 0){$begin_dag = $max_dag;}else{$begin_dag = 1;}
						$deze_maand = $max_maand + $i;
						$aantal_dagen_deze_maand = leapYear( $max_jaar, $deze_maand );
						
						for($i2 = $begin_dag; $i2 <= $aantal_dagen_deze_maand; $i2++){
							$insert_time_value[] = "(STR_TO_DATE('$i2 $deze_maand $vandaag_jaar','%d %m %Y'), $i2, $deze_maand, $vandaag_jaar, $gebruiker_id, NOW(), $gebruiker_id)";
						}
				}
			}
		// we beginnen hieronder bij de eerste dag van de maand.
		$max_dag = 1;
		}
		if($max_dag <= $vandaag_dag){
			//we zijn beland bij (of er was alleen maar deze/)de laatste maand. Hier hoeven we alleen de increment bij vandaag op te tellen.
			if($max_dag == $vandaag_dag){ $aantal_dagen = 1; }
			else{ $aantal_dagen = $vandaag_dag - $max_dag; }
			//echo $vandaag_dag.'vandaag en max'.$max_dag;     
			for($i = 1; $i <= $aantal_dagen; $i++){
				if($max_dag == $vandaag_dag){ $nieuwe_dag = $max_dag; }
				else{ $nieuwe_dag =  $max_dag+$i; }
				$insert_time_value[] = "(STR_TO_DATE('$nieuwe_dag $vandaag_maand $vandaag_jaar','%d %m %Y'), $nieuwe_dag, $vandaag_maand, $vandaag_jaar, $gebruiker_id, NOW(), $gebruiker_id)";
			}
		}
		
		$insert_time = 'INSERT INTO urentool_datums (datum, dag, maand, jaar, gebruiker_id, gewijzigd_op, gewijzigd_door) VALUES ';  // na dit alles: bouwen wij de query op.
		$tot_laatste = count($insert_time_value)-1;  // De laatste value, mag geen ',' op het einde hebben.. de rest wel. Welke is dus het laatste
		foreach($insert_time_value AS $value){
			$insert_time .= $value;
			if($tot_laatste != 0){$tot_laatste --; $insert_time .= ', ';} // als er geen aantal meer is af te trekken.. is dát de laatste.
		}
		//		$insert_time_sql = mysql_query($insert_time); // en voeren wij deze uit
		echo $insert_time;
	}
	/*$what = 'naam'; $from = 'organisatie'; $where = 'actief = 1';
		$bedrijven = sqlSelect($what, $from, $where);
		
		while($bedrijf = mysql_fetch_array(sqlSelect($what, $from, $where))){
			$bedrijven_lijst[] = strtolower($bedrijf['naam']);
		}
		
		if(in_array('smart tenders', $bedrijven_lijst)){echo 'true';}
else{ echo 'false';}
//echo checkUrentoolDatums(7).'x';
*/

	
?>
