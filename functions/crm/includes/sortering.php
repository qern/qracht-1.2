<?php
//maak een algemene variabele zodat het sorteren goed blijft gaan
$getincludes = "";
if(isset($_GET['function'])){$getincludes .= ' <input type="hidden" name="function" value="crm" /> ';}
if(isset($_GET['page'])){$getincludes .= ' <input type="hidden" name="page" value="'.$_GET['page'].'" /> ';}
if(isset($_GET['view'])){$getincludes .= ' <input type="hidden" name="view" value="'.$_GET['view'].'" /> ';}
if(isset($_GET['zoek_view'])){$getincludes .= '<input type="hidden" name="zoek_view" value="'.$_GET['zoek_view'].'" />';}
if(isset($_GET['naam'])){$getincludes .= '<input type="hidden" name="naam" value="'.$_GET['naam'].'" />';}
if(isset($_GET['email'])){$getincludes .= '<input type="hidden" name="email" value="'.$_GET['email'].'" />';}
if(isset($_GET['plaats'])){$getincludes .= '<input type="hidden" name="plaats" value="'.$_GET['plaats'].'" />';}
if(isset($_GET['branche'])){$getincludes .= '<input type="hidden" name="branche" value="'.$_GET['branche'].'" />';}
if(isset($_GET['contactpersoon'])){$getincludes .= '<input type="hidden" name="contactpersoon" value="'.$_GET['contactpersoon'].'" />';}
if(isset($_GET['sort_naam'])){$sortering = '<input type="hidden" name="sort_naam" value="'.$_GET['sort_naam'].'" />';}
if(isset($_GET['sort_organisatie'])){$sortering = '<input type="hidden" name="sort_organisatie" value="'.$_GET['sort_organisatie'].'" />';}
if(isset($_GET['archief'])){$archief = '<input type="hidden" name="archief" value="'.$_GET['archief'].'" />';}

//de verschillende sorteringsmethoden worden gecheckt en de associatieve variabelen worden gevuld.
//alleen de variabelen, archiveren, naam_sortering en organisatie_sortering hoeven worden getoond. De rest zit daarin verwerkt.
//dit voor overzichtelijkheid en peformance
if($_GET['archief'] !=  null){
    if($_GET['archief'] == 'ja'){$archiveren = $getincludes.$sortering.'<input name="archief" type="hidden" value="nee" />';}
    elseif($_GET['archief'] == 'nee'){$archiveren = $getincludes.$sortering.'<input name="archief" type="hidden" value="ja" />';}
}   else{$archiveren = $getincludes.$sortering.'<input name="archief" type="hidden" value="ja" />';}

if($_GET['sort_naam'] != null){
    if($_GET['sort_naam'] == 'asc'){$naam_sortering = $getincludes.$archief.'<input name="sort_naam" type="hidden" value="desc" />';}
    elseif($_GET['sort_naam'] == 'desc'){$naam_sortering = $getincludes.$archief.'<input name="sort_naam" type="hidden" value="asc" />';}
}else{$naam_sortering = $getincludes.$archief.'<input name="sort_naam" type="hidden" value="desc" />';}

if($_GET['sort_organisatie'] != null){
    if($_GET['sort_organisatie'] == 'asc'){$organisatie_sortering = $getincludes.$archief.'<input name="sort_organisatie" type="hidden" value="desc" />';}
    elseif($_GET['sort_organisatie'] == 'desc'){$organisatie_sortering = $getincludes.$archief.'<input name="sort_organisatie" type="hidden" value="asc" />';}
}else{$organisatie_sortering = $getincludes.$archief.'<input name="sort_organisatie" type="hidden" value="desc" />';}

if($_SESSION['toegang']['crm'] == 'beheerder'){$view = 'input';}
else{$view = 'text';}
if($view =='text'){$link = 'bekijk';}elseif($view == 'input'){ $link = 'wijzig';}else{$link = 'bekijk';}
?>