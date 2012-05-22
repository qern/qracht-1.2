<?php
//This phpfile is solely for all the general functions floating around. 
//This should be required in all files where it is needed.
/*
*   1. van maandnaam -> maandnummer 
*   2. van maandnummer -> maandnummer
*   3. fotodivider
*   4. sql select
*   5. sql update
*   6. sql insert
*   7. sql delete  
*   8. count rows (mysql_num_rows)
*   9. image maker, on upload
*   10. zorgt ervoor dat er een secure lijn gemaakt wordt naar checks. De verificatie zit in de *_check.php's
*   11. om nieuwe recentmeldingen aan te maken op basis van activiteit_id (planning !)
*   12. om te laten zien dat de gebruiker deze recentmelding nog niet heeft gezien/ afgevinkt.
*/
$maandnaam = array(
    '01' => 'januari', '02' => 'februari', '03' => 'maart', '04' => 'april', '05' => 'mei', '06' => 'juni', 
    '07' => 'juli', '08' => 'augustus', '09' =>  'september', '10' => 'oktober', '11' => 'november', '12'  => 'december'
);
$maandnummer = array(
	'januari' => 1, 'februari' => 2, 'maart' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 
	'juli' => 7, 'augustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'december' => 12
);
function translateDate($timestamp, $formaat){  
    //haal alle mogelijke strftimes op
    $weekdag = strftime('%w', $timestamp); $maand = strftime('%m', $timestamp);
    //van maandnummer -> maandnaam
    $maandnaam = array(
        '01' => 'januari', '02' => 'februari', '03' => 'maart', '04' => 'april', '05' => 'mei', '06' => 'juni', 
        '07' => 'juli', '08' => 'augustus', '09' =>  'september', '10' => 'oktober', '11' => 'november', '12'  => 'december'
    );
    $weekdagen = array(
        '0' => 'zondag', '1' => 'maandag', '2' => 'dinsdag', '3' => 'woensdag', 
        '4' => 'donderdag', '5' => 'vrijdag', '6' => 'zaterdag'
    );
    if(php_uname('s') ==  'WIN' || php_uname('s') ==  'Windows NT'){$dag_formaat = '%#d';}// Win
    else{$dag_formaat = '%e';}// Other 
    if($formaat == 'weekdag'){ return $weekdagen["$weekdag"];}
    if($formaat == 'maand'){ return $maandnaam["$maand"];}
    if($formaat == 'dag maand'){ return strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"];}
    if($formaat == 'dag maand tijd'){ return strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"].' om '.strftime('%H:%M', $timestamp);}
    if($formaat == 'weekdag dag maand'){ return $weekdagen["$weekdag"].' '.strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"];}
    if($formaat == 'weekdag dag maand tijd'){ return $weekdagen["$weekdag"].' '.strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"].' om '.strftime('%H:%M', $timestamp);}
    if($formaat == 'dag maand jaar'){return strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"].' '.strftime('%Y', $timestamp);}
    if($formaat == 'dag maand jaar tijd'){return strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"].' '.strftime('%Y om %H:%M', $timestamp);}
    if($formaat == 'weekdag dag maand jaar'){return $weekdagen["$weekdag"].' '.strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"].' '.strftime('%Y', $timestamp);}
    if($formaat == 'weekdag dag maand jaar tijd'){return $weekdagen["$weekdag"].' '.strftime($dag_formaat, $timestamp).' '.$maandnaam["$maand"].' '.strftime('%Y om %H:%M', $timestamp);}
}


//sql select & connect
function sqlSelect($what,$from,$where){
    $result = mysql_query("SELECT $what FROM $from WHERE $where");
    return $result;        
   	if(!$result){return mysql_error();}
   	else{return $result;}    
}	

//sql update & connect (what = {kolomnaam = nieuwe waarde}. Bijvoorbeeld id = 2)
function sqlUpdate($table,$what,$where){
   $query = mysql_query("UPDATE $table SET $what  WHERE $where "); 
   if(!$query){return mysql_error();}
   else{return $query;}    
}

//sql insert & connect ($what = kolomnaam) ($with_what = info per kolom)
function sqlInsert($table,$what,$with_what, $meer = 0){
    if($meer == 1){  $query = mysql_query("INSERT INTO $table ($what) $with_what"); }
    else{ 	$query = mysql_query("INSERT INTO $table ($what) VALUES($with_what)"); }
    if(!$query){return mysql_error();}
   	else{return $query;}  
}

//sql delete & connect
function sqlDelete($table,$where){
    $sql = "DELETE FROM $table WHERE $where";
    $result=mysql_query($sql);  
   	if(!$result){return mysql_error();}
   	else{return $result;}          
}

function countRows($what,$from,$where){
    $result = mysql_query("SELECT $what FROM $from WHERE $where");
   	if(!$result){return mysql_error();}
   	else{$rijen = mysql_num_rows($result); return $rijen;}    
}

function secureLineEncode(){ $secure = md5(date('m.d.y')); return $secure; }

//functie om de SLIR class goed te laden
function slirImage($width, $height, $cropping = 1){
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/portal/.htaccess')){
        if($cropping == 1){
            $slir="w$width-h$height-c$width.$height";
        }else{
            $slir="w$width";
            if($height == 0){
                $slir="w$width";
            }elseif($width == 0){
                $slir="h$height";
            }
        }
    }else{
        if($cropping == 1){
            $slir="?w=$width&h=$height&c=$width.$height&i=";
        }elseif($cropping == 0){
            if($height == 0){
                $slir="?w=$width&i=";
            }elseif($width == 0){
                $slir="?h=$height&i=";
            }
        }
    }
    return $slir;
}

//function om aan te geven hoe lang geleden iets was
function verstrekenTijd($timestamp){
    $nu = time(); //wat is de huidige tijd ?
    $difference = $nu - $timestamp;
    if($difference < 60){
		if($difference <= 0){
			$verstreken_tijd = "zojuist";
		}
		elseif($difference > 1){
            $verstreken_tijd = "$difference seconden geleden";
        }else{
            $verstreken_tijd = "$difference seconde geleden";
        }
    }elseif($difference >= 60 && $difference < (60 * 60)){
        if($difference >= 60 && $difference <= 119){
            $verstreken_tijd = "1 minuut geleden";
        }else{
            //we ronden minuten naar beneden af. (dus 2 min. 30 sec. wordt 2 min geleden)
            $verstreken_minuten = floor($difference / 60);
            $verstreken_tijd = "$verstreken_minuten minuten geleden";
        }
    }elseif($difference >= 3600 && $difference < (60 * 60 * 24)){
        $verstreken_uren = floor($difference / 3600);
        $verstreken_tijd = "$verstreken_uren uur geleden";
    }elseif($difference >= (60 * 60 * 24) && $difference < ((60 * 60 * 24)*2)){
        $verstreken_tijd_gister = strftime('%H:%M', $timestamp);
        $verstreken_tijd = "gisteren om $verstreken_tijd_gister";
    }elseif(($difference >= (60 * 60 * 24)*2) && $difference < (60 * 60 * 24 * 365)){
        //$verstreken_datum = strftime('%e %B', $timestamp);
        $verstreken_tijd = translateDate($timestamp, 'dag maand tijd');
        //$verstreken_datum = strftime('%e %B om %H:%M', $timestamp);
        //$verstreken_tijd = "$verstreken_datum";
    }else{
        //$verstreken_datum = strftime('%e %B %Y', $timestamp);
        $verstreken_tijd = translateDate($timestamp, 'dag maand jaar tijd');
        //$verstreken_datum = strftime('%e %B %Y om %H:%M', $timestamp);
        //$verstreken_tijd = "$verstreken_datum";
    }
    return $verstreken_tijd;
}

function resizeImage($filepath, $imagenaam){
    // The file
//$file = $filepath;
//$filename = $imagenaam;

// Get new dimensions
list($width, $height) = getimagesize($filepath.$imagenaam);
if($width > 600){
    $ratio = $width / 600;
    $new_width = 600;
    $new_height = ceil($height / $ratio);
}else{
    $new_width = $width;
    $new_height = $height;
}
//extension of the destination image without a "." (dot).
$dst_ext = strtolower(end(explode(".", $imagenaam)));

// Resample
$image_p = imagecreatetruecolor($new_width, $new_height);

if($dst_ext == 'jpg'){
    $image = imagecreatefromjpeg($filepath.$imagenaam);
}elseif($dst_ext == 'jpeg'){
    $image = imagecreatefromjpeg($filepath.$imagenaam);
}elseif($dst_ext == 'png'){
    $image = imagecreatefrompng($filepath.$imagenaam);
}elseif($dst_ext == 'gif'){
    $image = imagecreatefromgif($filepath.$imagenaam);
}

// preserve transparency
if($dst_ext == "gif" || $dst_ext == "png"){
    $transparencyIndex = imagecolortransparent($image); 
    $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255); 
             
    if ($transparencyIndex >= 0) { 
        $transparencyColor    = imagecolorsforindex($image, $transparencyIndex);    
    } 
    
    $transparencyIndex    = imagecolorallocate($image_p, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']); 
    imagefill($image_p, 0, 0, $transparencyIndex); 
    imagecolortransparent($image_p, $transparencyIndex); 
    
    /*imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
    imagealphablending($image_p, false);
    imagesavealpha($image_p, true);*/
}

if($dst_ext == 'jpg'){
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    unlink($filepath.$imagenaam);
    imagejpeg($image_p, $filepath.$imagenaam, 75);
}elseif($dst_ext == 'jpeg'){
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    unlink($filepath.$imagenaam);
    imagejpeg($image_p, $filepath.$imagenaam, 75);
}elseif($dst_ext == 'png'){
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    unlink($filepath.$imagenaam);
    imagepng($image_p, $filepath.$imagenaam, 8);
}elseif($dst_ext == 'gif'){
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    unlink($filepath.$imagenaam);
    imagegif($image_p, $filepath.$imagenaam);
}
return true;

}

function checkUrl($url){
    if($url != null){
        if(strpos($url, 'http://') === false && strpos($url, 'https://') === false){$url = 'http://'.$url;}
        if(filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) !== false){
            $lru = strrev($url); $laatste_punt = strpos($lru, '.');
            if($laatste_punt > 3 || $laatste_punt <= 1){return false;}
            else{return $url;}
        }else{return false;}
    }else{return false;}
}

function checkEmail($email){
    if($email != null){
        if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false){
            return true;
        }else{return false;}
    }else{ return false;}
}
function createImages($profiel_id, $filename, $temp_name){
        $images = $temp_name;
        $images = $images;
        //maak eerst de nieuwe foto 
        $new_images = strtolower($filename);
        $path = pathinfo($new_images);
        if($path['extension'] == 'jpeg' || $path['extension'] == 'jpg'){ $images_orig = imagecreatefromjpeg($images); }
        elseif($path['extension'] == 'png'){$images_orig = imagecreatefrompng($images);}   
               
        $photoX = ImagesX($images_orig);  
        $photoY = ImagesY($images_orig); 
        $width=1024; //*** Fix Width & Heigh (Autu caculate) ***//  
        $size=GetimageSize($images);
        $height=round($width*$size[1]/$size[0]); 
        if($width > $photoX){$width= $photoX; $height= $photoY; $images_fin = ImageCreateTrueColor($width, $height);}
        else{$images_fin = ImageCreateTrueColor($width, $height);}  
        
        if(file_exists($_SERVER['DOCUMENT_ROOT']."/files/profiel_foto/$profiel_id/$new_images")){ $new_images = strftime('%d_%b_%Y').'_'.strtolower($filename); }
        else{$new_images = strtolower($filename);}  
        ImageCopyResampled($images_fin, $images_orig, 0, 0, 0, 0, $width+1, $height+1, $photoX, $photoY);  
        if($path['extension'] == 'jpeg' || $path['extension'] == 'jpg'){ $images_orig = ImageJPEG($images_fin,$_SERVER['DOCUMENT_ROOT']."/files/profiel_foto/$profiel_id/".$new_images);}
        elseif($path['extension'] == 'png'){$images_orig = ImagePNG($images_fin,$_SERVER['DOCUMENT_ROOT']."/files/profiel_foto/$profiel_id/".$new_images);}
}
function recentMaken($activiteit_id, $login_id){
	$table = 'planning_recent';
    // Wanneer een activiteit op gezien = 0 staat... is het recent ;
    // En voor iedereen, behalve de wijzigaar zelf, is het nu recent ;)	
    $what = "DISTINCT gebruiker"; $where = 'activiteit = '.$activiteit_id;
    	$aantal = countRows($what, $table, $where);
	if($aantal <= 0){
		/*
		 * ER ZIJN NOG GEEN RECENTE GEBRUIKERS VOOR DEZE ACTIVITEIT. INSERT DE WIJZIGAAR
		 * */
    	$what = 'activiteit, gebruiker, gezien, gezien_op';
		$with_what = "$activiteit_id, $login_id, 1, NOW()";
			$voor_het_eerst_gezien = sqlInsert($table, $what, $with_what);
	}else{
		$result = sqlSelect($what, $table, $where);
		/*
		 * ER ZIJN RECENTE GEBRUIKERS VOOR DEZE ACTIVITEIT. WELKE?
		 * */
	 	while($row = mysql_fetch_array($result)){ $recente_gebruiker[] = $row['gebruiker']; }
	
		if(!in_array($login_id, $recente_gebruiker)){
			/*
			 * ER ZIJN RECENTE GEBRUIKERS VOOR DEZE ACTIVITEIT. EN DE WIJZIGAAR HOORT DAAR NIET BIJ... INSERT DE WIJZIGAAR
			 * */
			$what = 'activiteit, gebruiker, gezien, gezien_op';
			$with_what = "$activiteit_id, $login_id, 1, NOW()";
				$voor_het_eerst_gezien = sqlInsert($table, $what, $with_what);
		}
		
		foreach($recente_gebruiker AS $gebruiker){
			if($gebruiker == $login_id){
				/*
				 * ER ZIJN RECENTE GEBRUIKERS VOOR DEZE ACTIVITEIT. EN DIT IS DE WIJZIGAAR, DUS ZET DEZE GEBRUIKER OP GEZIEN
				 * */ 
			  $what = 'gezien = 1, gezien_op = NOW()';
			  $where = 'activiteit = '.$activiteit_id.' AND gebruiker = '.$login_id; 
			  	$update_het = sqlUpdate($table, $what, $where);
			}else{
				/*
				 * ER ZIJN RECENTE GEBRUIKERS VOOR DEZE ACTIVITEIT. EN DIT IS NIET DE WIJZIGAAR, DUS ZET DEZE GEBRUIKER OP NIET GEZIEN
				 * */
			  $what = 'gezien = 0';
			  $where = 'activiteit = '.$activiteit_id.' AND gebruiker = '.$gebruiker; 
			  	$update_het = sqlUpdate($table, $what, $where);
			}
		}
	}
    return true;
}
function isRecent($activiteit_id, $login_id){
    $what = "gezien"; $from = 'planning_recent';  $where = 'activiteit = \''.$activiteit_id.'\' AND gebruiker ='.$login_id;
    $recentmelding = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    if($recentmelding['gezien'] == 1){
        $response = 'al_gezien';
    }else{
        $response = 'niet_gezien';
    }
    return $response;
}
function getRecenten($login_id, $iteratie){
	$what = 'a.activiteit, a.gezien'; $from = 'planning_recent a LEFT JOIN planning_activiteit AS b ON (b.id = a.activiteit)'; 
	$where = "a.gebruiker = $login_id AND a.gezien = 1 AND b.iteratie IN ($iteratie) AND b.actief = 1 AND b.status != 'done'";
		$aantal_gezien = countRows($what, $from, $where);
	if($aantal_gezien > 0){
		$gezien = sqlSelect($what, $from, $where);
		return $gezien;
	}else{ return false; }
}

//wat is er dan eigenlijk gewijzigd ?
function recenteWijziging($activiteit, $login_id){
	$what = 'DATE_FORMAT(gezien_op, \'%Y-%m-%d %k-%i-%s\') AS gezien_op'; $from ='planning_recent'; $where = 'gebruiker = '.$login_id.' AND activiteit = '.$activiteit;
	$laatst = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	
	//als het leeg is, dan is de activiteit nog nooit ingezien 
	if($laatst['gezien_op'] != null){
		$message = 'De volgende dingen zijn veranderd: ';
		//haal reacties op
		$what = '	a.inhoud, 
					c.voornaam, c.achternaam';
		$from = '	portal_reactie a
					LEFT JOIN planning_reactie AS b ON (b.reactie = a.id)
					LEFT JOIN portal_gebruiker AS c ON (c.id = a.geschreven_door)';
		$where = '	a.geschreven_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND b.activiteit = '.$activiteit.' AND a.geschreven_door != '.$login_id;
			//$message .= "SELECT $what FROM $from WHERE $where";
			$aantal_reacties = countRows($what, $from, $where);
			if($aantal_reacties == 1){
				$reactie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
				$message .=  '<br /> '.$reactie['voornaam'].' '.$reactie['achternaam'].' heeft een reactie achtergelaten';
				
			}
			elseif($aantal_reacties >  1){$message .= '<br /> Er zijn '.$aantal_reacties.' reacties geplaatst';}
			
		//haal tags op
		$what = '	a.naam,
					c.voornaam, c.achternaam';
		$from = '	portal_tag a
					LEFT JOIN planning_tag AS b ON (b.tag = a.id)
					LEFT JOIN portal_gebruiker AS c ON (c.id = b.toegevoegd_door)';
		$where = '	b.toegevoegd_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND b.activiteit = '.$activiteit.' AND b.toegevoegd_door != '.$login_id;
			//echo "SELECT $what FROM $from WHERE $where";
			$aantal_tags = countRows($what, $from, $where);
			if($aantal_tags == 1){
				$tag = mysql_fetch_assoc(sqlSelect($what, $from, $where));
				$message .=  '<br /> '.$tag['voornaam'].' '.$tag['achternaam'].' heeft een tag toegevoegd';
			}
			elseif($aantal_tags > 1){$message .= '<br /> Er zijn '.$aantal_tags.' tags toegevoegd';}
			
		//haal bestanden op
		
		$what = '	a.bestand,
					b.voornaam, b.achternaam';
		$from = '	planning_bestand a
					LEFT JOIN portal_gebruiker AS b ON (b.id = a.gewijzigd_door)';
		$where = '	a.gewijzigd_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND a.activiteit = '.$activiteit.' AND a.gewijzigd_door != '.$login_id;
			//echo "SELECT $what FROM $from WHERE $where";
			$aantal_bestanden = countRows($what, $from, $where);
			if($aantal_bestanden == 1){
				$bestand = mysql_fetch_assoc(sqlSelect($what, $from, $where));
				$message .=  '<br /> '.$bestand['voornaam'].' '.$bestand['achternaam'].' heeft een bestand toegevoegd';
			}
			elseif($aantal_bestanden > 1){$message .= '<br /> Er zijn '.$aantal_bestanden.' bestanden toegevoegd';}
			
		//is er iets veranderd aan het totaal ?
		$what = '	b.voornaam, b.achternaam'; 
		$from = '	planning_activiteit a
					LEFT JOIN portal_gebruiker AS b ON (b.id = a.gewijzigd_door)'; 
		$where = '	a.status_datum > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND a.id = '.$activiteit.' AND a.gewijzigd_door != '.$login_id;
			//echo "SELECT $what FROM $from WHERE $where";
			$gewijzigde_activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
			if($gewijzigde_activiteit['voornaam'] != null){
				$message .=  '<br /> '.$gewijzigde_activiteit['voornaam'].' '.$gewijzigde_activiteit['achternaam'].' heeft de status aangepast';
			}
			
		//is er iets veranderd aan het totaal ?
		$what = '	b.voornaam, b.achternaam'; 
		$from = '	planning_activiteit a
					LEFT JOIN portal_gebruiker AS b ON (b.id = a.gewijzigd_door)'; 
		$where = '	a.gewijzigd_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND a.id = '.$activiteit.' AND a.gewijzigd_door != '.$login_id;
			//echo "SELECT $what FROM $from WHERE $where";
			$gewijzigde_activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
			if($gewijzigde_activiteit['voornaam'] != null){
				$message .=  '<br /> '.$gewijzigde_activiteit['voornaam'].' '.$gewijzigde_activiteit['achternaam'].' heeft de activiteit aangepast';
			}
			
	}else{ $message = 'U hebt deze activiteit nog nooit ingezien.'; }
	return $message;
}


//is het betreffende jaar een schrikkeljaar ?
function leapYear($year, $maand){
	if ($year % 4 == 0) {
      if ($year % 100 == 0) { if($year % 400 == 0){ $februari = 29; }else{ $februari = 28; };}
	  else { $februari = 29; }
   	} else { $februari = 28; }
	$maanden = array( '1' => 31, '2' => $februari, '3' => 31, '4' => 30, '5' => 31, '6' => 30, '7' => 31, '8' => 31, '9' => 30, '10' => 31, '11' => 30, '12' => 31 );
	return $maanden[$maand];
}
//voor het aanmaken van nieuwe urentool datums, on the fly
function checkUrentoolDatums( $gebruiker_id ){
	if($gebruiker_id != null && $gebruiker_id > 0){
   $what = 'datum, dag, maand, jaar'; $from = 'urentool_datum'; $where = "gebruiker = $gebruiker_id ORDER BY datum DESC";
   		$datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
   		
	list($vandaag_dag, $vandaag_maand, $vandaag_jaar) = explode('-', strftime('%#d-%m-%Y'));
	if($datum['datum'] == null){ $max_dag = $vandaag_dag; $max_maand = $vandaag_maand; $max_jaar = $vandaag_jaar; }
	elseif($vandaag_dag == $datum['dag'] && $vandaag_maand == $datum['maand'] && $vandaag_jaar == $datum['jaar']){ return false; /* we doen niets. Deze dag bestaat al */}
	elseif($vandaag_jaar <  $datum['jaar'] || $vandaag_maand < $datum['maand'] || $vandaag_dag < $datum['dag']){ return false; /* we doen niets. Deze dag bestaat al */}
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
            
			if($max_dag == 1){ $i = 0 ;}else{ $i = 1;}
            
			for($i; $i <= $aantal_dagen; $i++){
			     
				if($max_dag == $vandaag_dag){ $nieuwe_dag = $max_dag; }
                else{ $nieuwe_dag =  $max_dag+$i; }
                
				$insert_time_value[] = "(STR_TO_DATE('$nieuwe_dag $vandaag_maand $vandaag_jaar','%d %m %Y'), $nieuwe_dag, $vandaag_maand, $vandaag_jaar, $gebruiker_id, NOW(), $gebruiker_id)";
			}
		}
		
		$insert_time = 'INSERT INTO urentool_datum (datum, dag, maand, jaar, gebruiker, gewijzigd_op, gewijzigd_door) VALUES ';  // na dit alles: bouwen wij de query op.
		$tot_laatste = count($insert_time_value)-1;  // De laatste value, mag geen ',' op het einde hebben.. de rest wel. Welke is dus het laatste
		foreach($insert_time_value AS $value){
			$insert_time .= $value;
			if($tot_laatste != 0){$tot_laatste --; $insert_time .= ', ';} // als er geen aantal meer is af te trekken.. is dát de laatste.
		}
		//return mysql_query($insert_time); // en voeren wij deze uit
		return $insert_time;
	}
	}
}
function encrypt($string, $key) {
	$result = '';
	for($i=0; $i<strlen($string); $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)+ord($keychar));
		$result.=$char;
	}
	return base64_encode($result);
}
function decrypt($string, $key) {
	$result = '';
	$string = base64_decode($string);
	for($i=0; $i<strlen($string); $i++) {
		$char = substr($string, $i, 1);
	  	$keychar = substr($key, ($i % strlen($key))-1, 1);
	  	$char = chr(ord($char)-ord($keychar));
	  	$result.=$char;
	}
	return $result;
}
?>
