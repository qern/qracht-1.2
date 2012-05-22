<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php'); 
$request = trim(strtolower(str_replace(' ','_', $_GET['contactpersoon'])));//retrieve the search term that autocomplete sends
if($_GET['org_id'] != null){$organisatie_id = $_GET['org_id'];}
$return_arr = array();
$qstring = "SELECT 
                a.voornaam, 
                a.achternaam
            FROM 
                relaties a,
                relatie_organisatie b
            WHERE
                (a.voornaam LIKE '%$request%' OR a.achternaam LIKE '%$request%')                
            AND a.actief=1
            AND b.relatie_id = a.id
            AND b.organisatie_id = $organisatie_id";
$result = mysql_query($qstring)or DIE('jammer joh');//query the database for entries containing the term
//$valid = 'true';

while($row = mysql_fetch_array($result)){
    $naam = $row['voornaam'].' '.$row['achternaam'];
    if(strtolower($naam) ==  $request){$valid = 'true';}
    else{$valid = '"dit is helemaal fout'.$naam.$request.'"';}
}
echo $valid;
?>