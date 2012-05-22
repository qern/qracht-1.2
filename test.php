<?php
require('check_configuration.php');
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 'on');
/*
$what = 'DATE_FORMAT(gezien_op, \'%Y-%m-%d %k-%i-%s\') AS gezien_op'; $from ='planning_recent'; $where = 'activiteit = 290';
	$laatst = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	
//haal reacties op
$what = '	a.inhoud, 
			c.voornaam, c.achternaam';
$from = '	portal_reactie a
			LEFT JOIN planning_reactie AS b ON (b.reactie = a.id)
			LEFT JOIN portal_gebruiker AS c ON (c.id = a.geschreven_door)';
$where = '	a.geschreven_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND b.activiteit = 290';
	echo "SELECT $what FROM $from WHERE $where";
	$aantal_reacties = countRows($what, $from, $where);
	if($aantal_reacties == 1){$reactie = mysql_fetch_assoc(sqlSelect($what, $from, $where));}
	elseif($aantal_reacties > 1){echo 'Er zijn '.$aantal_reacties.' reacties geplaatst';}
	else{echo 'geen reacties';}
	echo '<br />';
	
//haal tags op
$what = '	a.naam,
			c.voornaam, c.achternaam';
$from = '	portal_tag a
			LEFT JOIN planning_tag AS b ON (b.tag = a.id)
			LEFT JOIN portal_gebruiker AS c ON (c.id = b.gewijzigd_door)';
$where = '	b.gewijzigd_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND b.activiteit = 290';
	echo "SELECT $what FROM $from WHERE $where";
	$aantal_tags = countRows($what, $from, $where);
	if($aantal_tags == 1){$tag = mysql_fetch_assoc(sqlSelect($what, $from, $where));}
	elseif($aantal_tags > 1){echo 'Er zijn '.$aantal_tags.' tags toegevoegd';}
	else{echo 'geen tags';}
	echo '<br />';
	
//haal bestanden op

$what = '	a.bestand,
			b.voornaam, b.achternaam';
$from = '	planning_bestand a
			LEFT JOIN portal_gebruiker AS b ON (b.id = a.gewijzigd_door)';
$where = '	a.gewijzigd_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND a.activiteit = 290';
	echo "SELECT $what FROM $from WHERE $where";
	$aantal_bestanden = countRows($what, $from, $where);
	if($aantal_bestanden == 1){$bestand = mysql_fetch_assoc(sqlSelect($what, $from, $where));}
	elseif($aantal_bestanden > 1){echo 'Er zijn '.$aantal_bestanden.' bestanden toegevoegd';}
	else{echo 'geen bestanden';}
	echo '<br />';
	
//is er iets veranderd aan het totaal ?
$what = 'id'; $from = 'planning_activiteit'; $where = 'gewijzigd_op > DATE_FORMAT(\''.$laatst['gezien_op'].'\',  \'%Y-%m-%d %k-%i-%s\' ) AND id = 290';
echo "SELECT $what FROM $from WHERE $where";
	$gewijzigde_activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	if($gewijzigde_activiteit['id'] != null){echo 'yup het is gewijzigd';}
	else{echo 'nope, niet gewijzigd';}
 */

	$ch = curl_init();
	$timeout = 5;
	$postdata= 'image=/images/logo_qracht_dummy.jpg';
	if($_GET['lock'] != null){ $postdata .= '&lock='.$_GET['lock'];}
	$options = array(
		CURLOPT_URL => 'http://www.qern.nl/login/index.php',
		CURLOPT_HEADER => false,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $postdata,
		CURLOPT_CONNECTTIMEOUT => $timeout
    );
	curl_setopt_array($ch, $options);
	$data = curl_exec($ch);
	curl_close($ch);
	echo $data;
?>