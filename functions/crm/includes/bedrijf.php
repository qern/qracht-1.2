<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
$request = trim(strtolower($_REQUEST['bedrijfsnaam']));//wat hebben we binnen gekregen?
$what = 'naam'; $from = 'organisatie'; $where = 'actief = 1';$bedrijven = sqlSelect($what, $from, $where);//haal alle mogelijke (actieve) bedrijven op
while($bedrijf = mysql_fetch_array($bedrijven)){ $bedrijven_lijst[] = strtolower($bedrijf['naam']); }// maak hier een array van. En, net als de request, in lower case. Het gaat om de letters, niet om de upper of lower case
if(in_array($request, $bedrijven_lijst)){echo 'true';}else{ echo 'false';}//komt het zoekwoord voor in de bovenstaande array ? Dan is het goed. Anders fout
?>
