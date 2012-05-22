<?php 
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $term = trim(strip_tags($_GET['term']));                                                    //retrieve the search term that autocomplete sends
    $qstring = "SELECT kennis FROM portal_kenniskaart WHERE kennis LIKE '%$term%'";
    $result = mysql_query($qstring)or DIE('Geen database connectie. Raadpleeg de Webmaster');   //query the database for entries containing the term
    while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){$row_set[] = $row['kennis']; }        //loop through the retrieved values and make array
    echo json_encode($row_set);                                                                 //format the array into json data
?>