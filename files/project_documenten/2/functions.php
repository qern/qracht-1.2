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
}

//sql update & connect (what = {kolomnaam = nieuwe waarde}. Bijvoorbeeld id = 2)
function sqlUpdate($table,$what,$where){
   $query = mysql_query("UPDATE $table SET $what  WHERE $where ");
    return $query;        
}

//sql insert & connect ($what = kolomnaam) ($with_what = info per kolom)
function sqlInsert($table,$what,$with_what){
    $query = mysql_query("INSERT INTO $table ($what)
    VALUES($with_what)");
    return $query;        
}

//sql delete & connect
function sqlDelete($table,$where){
    $sql = "DELETE FROM $table   WHERE $where";
    $result=mysql_query($sql);
    return $result;        
}

function countRows($what,$from,$where){
    $result = mysql_query("SELECT $what FROM $from WHERE $where");
    $rijen = mysql_num_rows($result);
    return $rijen;
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
        if($difference > 1){
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
        if(filter_var($email, FILTER_VALIDATE_URL) !== false){
            return true;
        }else{return false;}
    }else{ return false;}
}
?>