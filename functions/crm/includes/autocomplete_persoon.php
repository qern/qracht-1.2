<?php
include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
$term = trim(strip_tags($_GET['term']));//retrieve the search term that autocomplete sends
if($_GET['org_id'] != null){$organisatie_id = $_GET['org_id'];}
$return_arr = array();
$qstring = "SELECT 
                a.voornaam, 
                a.achternaam
            FROM 
                relaties a,
                relatie_organisatie b
            WHERE
                (a.voornaam LIKE '%$term%'OR a.achternaam LIKE '%$term%')                
            AND a.actief=1
            AND b.relatie_id = a.id
            AND b.organisatie_id = $organisatie_id";
$result = mysql_query($qstring)or DIE('jammer joh');//query the database for entries containing the term

while ($row = mysql_fetch_array($result,MYSQL_ASSOC))//loop through the retrieved values
{
        $row['value']=htmlentities(stripslashes($row['value']));
        $row['id']=(int)$row['id'];
        $row_set[] = $row['voornaam'].' '.$row['achternaam'];//build an array
}
echo json_encode($row_set);//format the array into json data
?>