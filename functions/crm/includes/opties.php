<?php 
    session_start();
    //include de benodigde configuration
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    //include de optie die nodig is.
    require($_POST['pagina'].'.php');

?>