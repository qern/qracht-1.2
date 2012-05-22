<?php
    if($_SESSION['admin'] != null){include('functions/'.$functie_get.'/includes/overzicht.php');}
    else{include('functions/home/');}
?>