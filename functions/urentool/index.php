<?php
	$mogelijke_paginas = array('rapportage');
	if(strpos($_GET['query'], '-') !== false){
	    $datum = str_replace('-0', '-', $_GET['query']);
	    //$datum = $_GET['query']; 
	    require('includes/urentool.php'); 
    }
	elseif(in_array($_GET['query'], $mogelijke_paginas)){ require('includes/rapportage.php'); }
	else{ require('includes/urentool.php'); }
?>