<?php
    echo '
    <link rel="stylesheet" type="text/css" href="/css/standaard_css/main.css" />';
    if(file_exists('functions/'.$_GET['function'].'/css/'.$_GET['function'].'.css')){
        echo'<link rel="stylesheet" type="text/css" href="/functions/'.$_GET['function'].'/css/'.$_GET['function'].'.css" />';
    }
?>