<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php'); 
$term = trim(strip_tags($_GET['term']));//retrieve the search term that autocomplete sends
$return_arr = array();
$qstring = "SELECT 
                naam
            FROM 
                organisatie 
            WHERE
                naam LIKE '%$term%' 
            AND actief=1";
$result = mysql_query($qstring)or DIE('jammer joh');//query the database for entries containing the term

while ($row = mysql_fetch_array($result,MYSQL_ASSOC))//loop through the retrieved values
{
        $row['value']=htmlentities(stripslashes($row['value']));
        $row['id']=(int)$row['id'];
        $row_set[] = $row['naam'];//build an array
}
echo json_encode($row_set);//format the array into json data
?>