<?php
/*
* qracht CRM-tool 
* Beheer hier klanten/relaties
* copyright qern internet professionals 2010-2011
* mail: b.slob(at)qern(dot)nl
*/
require("sortering.php");
//selecteer alle benodigde informatie uit de database
//Alle relaties ophalen
?>
<div id="crm">
    <div id="actie_balk">
      <div id="sort_naam_zkr">
      <form method="get" action="<? echo $_SERVER['PHPSELF'] ?>">
        <div>
            <?php echo $naam_sortering // echo de hidden inputs voor de sortering op naam ?>          
            <input type="submit" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'" value="Naam" class="button" />
          </div>
      </form>
      </div>
<?php if($_GET['zoek_view'] != 'organisaties'){?>          
      <div id="sort_organisatie_zkr">
      <form method="get" action="<? echo $_SERVER['PHPSELF']; ?>">
      <div>
          <?php echo $organisatie_sortering // echo de hidden inputs voor de sortering op naam ?>
          <input type="submit" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'" value="Organisatie" class="button" />
          </div>
      </form>
      </div>
<?php } ?>
        <div id="sort_archief_zkr">
      <form method="get" action="<? echo $_SERVER['PHPSELF']; ?>">
      <div>
<?php
if($_GET['archief'] == 'ja'){
     echo $archiveren.'<input type="submit" onmouseout="this.className='."'button'".'" onmouseover="this.className='."'button btn_hover'".'" value="Terug naar lijst" class="button" />';}
else{echo $archiveren.'<input type="submit" onmouseout="this.className='."'button'".'" onmouseover="this.className='."'button btn_hover'".'" value="Naar archief" class="button" />';}
// echo via $archiveren de hidden inputs voor het archiveren of teruggaan naar lijst     
?>      
</div>
</form>
      </div>
    </div>

<?php
//Sortering toevoegen
if($_GET['zoek_view'] == 'relaties'){
$what = "       a.id,
                a.voornaam,
                a.achternaam,
                a.adres,
                a.plaats,
                a.telefoonnummer,
                a.email         relatie_email,
                c.naam          organisatie_naam";
$from="         relaties a
                LEFT JOIN relatie_organisatie AS b ON (b.relatie_id = a.id)
                LEFT JOIN organisatie AS c ON(c.id = b.organisatie_id) ";
$where="        a.id > 0 ";
//waar is op gezocht ? zet deze om in zoektermen en voeg deze toe aan de query
if($_GET['naam'] != null){ $where .= " AND (a.voornaam LIKE '%".$_GET['naam']."%' OR a.achternaam LIKE '%".$_GET['naam']."%') ";}
if($_GET['email'] != null){$where .= " AND a.email LIKE '%".$_GET['email']."%' ";}
if($_GET['plaats'] !=null ){$where .= " AND a.plaats LIKE '".$_GET['plaats']."' ";}

//Hoe moet er gesorteerd worden ? 
$sort =  "ORDER BY a.voornaam ASC";
if($_GET['sort_naam'] == 'asc'){$sort = "ORDER BY a.voornaam ASC";}
elseif($_GET['sort_naam'] == 'desc'){$sort = "ORDER BY a.voornaam DESC";}
elseif($_GET['sort_organisatie'] == 'asc'){$sort = "ORDER BY c.naam ASC";}
elseif($_GET['sort_organisatie'] == 'desc'){$sort = "ORDER BY c.naam DESC";}
}
//of alle organisaties ophalen               
elseif(isset($_GET['zoek_view'])== 'organisaties'){
$what = "       a.id,
                a.naam  bedrijfsnaam,
                a.badres,
                a.bplaats,
                a.telefoonnummer,
                a.faxnummer,
                a.email,
                b.naam  branche_naam";
$from="         organisatie a
                LEFT JOIN branche AS b ON (b.id = a.branche_id) ";
$where="        a.id > 0 ";
//waar is op gezocht ? zet deze om in zoektermen en voeg deze toe aan de query
if($_GET['naam'] != null){ $where .= " AND a.naam LIKE '%".$_GET['naam']."%' ";}
if($_GET['email'] != null){$where .= " AND a.email LIKE '%".$_GET['email']."%' ";}
if($_GET['contactpersoon'] != null){$where .= " AND (a.voornaam LIKE '%".$_GET['contactpersoon']."%' OR a.achternaam LIKE '%".$_GET['contactpersoon']."%') ";}  
if($_GET['plaats'] !=null ){$where .= " AND a.bplaats LIKE '%".$_GET['plaats']."%' ";}
if($_GET['branche'] >= '1'){$where .= " AND a.branche_id =".$_GET['branche'];}

//Hoe moet er gesorteerd worden ? (organisaties worden niet op 'organisatie' gesorteerd.. aangezien 'naam' gelijk staat aan 'organisatie')
$sort =  "ORDER BY a.naam ASC";
if($_GET['sort_naam'] == 'asc'){$sort = "ORDER BY a.naam ASC";}
elseif($_GET['sort_naam'] == 'desc'){$sort = "ORDER BY a.naam DESC";}
}

//moet het archief bekeken of niet ? Default: nee
if($_GET['archief'] == 'nee'){$where .= " AND a.actief = '1' ";}
elseif($_GET['archief'] == 'ja'){$where .= " AND a.actief = '0' ";}
else{$where .= " AND a.actief = '1' ";}
if(isset($filter_branche)){$query .= " AND a.branche_id = '$filter_branche' ";}
//voeg de sortering toe aan de database query
$where .= $sort;
//haal de informatie op.
$result = sqlSelect($what,$from,$where);
//Alle relaties in de het schema zetten
if($_GET['zoek_view'] == 'relaties'){
    echo '<div id="resultaten">
        <div id="kolom_namen">
            <div id="id_kolom_rel">#</div>
            <div id="naam_kolom_rel">Naam</div>
            <div id="organisatie_kolom_rel">Organisatie</div>
            <div id="adres_kolom_rel">Adres</div>
            <div id="telnummer_kolom_rel">Telefoon</div>
            <div id="email_kolom_rel">Email</div>
            <div id="wijzig_kolom_rel">Wijzig</div>
        </div>
         
        <div id="resultaten_lijst">';
while($row = mysql_fetch_array($result)){  
    //nu pakken we het relatie_id van deze klant. Aan de hand hiervan gaan we de opmerkingen laden.
    $relatie_id = $row['id'];
    
    //om te kijken hoeveel er wel niet in de lijst staan, wordt bij elke row het nummer verhoogd en ook geechoed.
    $aantal++;
    //wisselende styling bepalen
    if($teller==1) { $class="even"; $teller=0; } else { $class="oneven"; $teller=1; }
    // EINDE KLEURSTYLING E.D.    
/*
* nu begint het echte werk: alles tonen. 
* Eerst de persoonlijke info.
* Dan een knop om de overige info in te zien/te wijzigen.  
*/
    echo '
        <div class="resultaat-rij '.$class.'">
            <div class="id_rel">'.$aantal.'</div>
            <div class="naam_rel">'.$row['voornaam'].'&nbsp;'.$row['achternaam'].'&nbsp;</span></div>
            <div class="organisatie_rel">'.htmlspecialchars($row['organisatie_naam']).'&nbsp;</span></div>
            <div class="adres_rel">'.htmlspecialchars($row['adres']).'&nbsp;'.'<br /> '.$row['plaats'].'&nbsp;</div>
            <div class="telnummer_rel">'.$row['telefoonnummer'].'&nbsp;</div>
            <div class="email_rel">'.$row['relatie_email'].'&nbsp;</div>
            <div class="wijzig_rel"><a href="/functions/'.$_GET['function'].'/includes/relatie_wijzigen.php?id='.$relatie_id.'&view='.$view.'&login_id='.$login_id.'&archief='.$_GET['archief'].'&secure='.secureLineEncode().'" class="iframe">">'.$link.'</a></div>       
        </div>';
}
    echo '</div>';
}

//Alle organisatie in de het schema zetten
elseif($_GET['zoek_view'] == 'organisaties'){
    echo '<div id="resultaten">
        <div id="kolom_namen">
            <div id="id_kolom_org">#</div>
            <div id="organisatie_kolom_org">Organisatie</div>
            <div id="branche_kolom_org">Branche</div>
            <div id="adres_kolom_org">Adres</div>
            <div id="telnummer_kolom_org">Telefoon</div>
            <div id="email_kolom_org">Email</div>
            <div id="wijzig_kolom_org">Wijzig</div>
        </div>
         
        <div id="resultaten_lijst">';
while($row = mysql_fetch_array($result)){
    
    //nu pakken we het presentatie id van deze klant. Aan de hand hiervan gaan we de opmerkingen laden
    $organisatie_id = $row['id'];

    $aantal++;
//wisselende styling bepalen
    if($teller==1) { $class="even"; $teller=0; } else { $class="oneven"; $teller=1; }
                                     
// EINDE KLEURSTYLING E.D.    
    echo '
        <div class="resultaat-rij '.$class.'">
            <div class="id_org">'.$aantal.'</div>
            <div class="organisatie_org">'.htmlspecialchars($row['bedrijfsnaam']).'&nbsp;</div>
            <div class="branche_org">'.$row['branche_naam'].'&nbsp;</div>
            <div class="adres_org">'.htmlspecialchars($row['badres']).'&nbsp;'.'<br /> '.$row['bplaats'].'&nbsp;</div>
            <div class="telnummer_org">'.$row['telefoonnummer'].'&nbsp;</div>
            <div class="email_org">'.$row['email'].'&nbsp;</div>  
            <div class="wijzig_org"><a href="/functions/'.$_GET['function'].'/includes/organisatie_wijzigen.php?id='.$organisatie_id.'&view='.$view.'&login_id='.$login_id.'&secure='.secureLineEncode().'" class="iframe">">'.$link.'</a></div> 
        </div>';
        
    }    
}
?>
</div>