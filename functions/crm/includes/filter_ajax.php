<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');

    require($_SERVER['DOCUMENT_ROOT'].$etc_root.'includes/ajax_functions.php');
    $org_result = array();
    if($_GET['filter'] == 'organisatie_auto' || $_GET['filter'] == 'relatie_auto'){
	    $term = trim(strip_tags($_GET['term']));//retrieve the search term that autocomplete sends
	    
	    //zoeken we naar een organisatie
	    if($_GET['filter'] == 'organisatie_auto'){$qstring = "SELECT id, naam FROM organisatie WHERE naam LIKE '%$term%'";}
		//of naar een relatie
	    if($_GET['filter'] == 'relatie_auto'){$qstring = "SELECT id, voornaam, achternaam FROM relaties WHERE voornaam LIKE '%$term%' OR achternaam LIKE '%$term%' ";}
		
	    $result = mysql_query($qstring)or DIE('Geen database connectie. Raadpleeg de Webmaster');   //query the database for entries containing the term
	    while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){if($row['voornaam']){$naam =  $row['voornaam'].' '.$row['achternaam'];}else{$naam = $row['naam'];} $row_set[$naam] = $row['id']; }        //loop through the retrieved values and make array
	    foreach ($row_set as $key=>$value) { array_push($org_result, array("id"=>$value, "label"=>$key, "value" => strip_tags($key))); }
	    echo array_to_json($org_result);//format the array into json data     
    }elseif($_POST['filter'] == 'organisatie' || $_POST['filter'] == 'relatie'){
    	echo $_POST['zoekwoord'];
	}                          
?>