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
      <div id="sort_naam_rel">
      <form method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>" >
        <div>
          <?php echo   $naam_sortering // echo de hidden inputs voor de sortering op naam ?>  
          <input type="submit" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'" value="Naam" class="button" />
          </div>
      </form>
      </div>
<?php if($_GET['view'] != 'organisaties'){?>          
      <div id="sort_organisatie_rel">
      <form method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>" >
        <div>
          <?php echo $organisatie_sortering // echo de hidden inputs voor de sortering op naam ?>
          <input type="submit" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'" value="Organisatie" class="button" />
        </div>
      </form>
      </div>
<?php } ?>
        <div id="sort_archief_rel">
      <form method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>" >
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
//alle relaties ophalen

$what = "       a.id,
                a.voornaam,
                a.achternaam,
                a.adres,
                a.plaats,
                a.telefoonnummer,
                a.email         relatie_email,
                e.naam          organisatie_naam";
$from="         relaties a
                LEFT JOIN relatie_organisatie AS d ON (d.relatie_id = a.id)
                LEFT JOIN organisatie AS e ON(e.id = d.organisatie_id) ";
if(isset($_GET['relatie_id'])){$where = "a.id =".$_GET['relatie_id'];}
else{$where="a.id > 0 ";}

//moet het archief bekeken of niet ? Default: nee
if($_GET['archief'] == 'nee'){$where .= " AND a.actief = '1' ";}
elseif($_GET['archief'] == 'ja'){$where .= " AND a.actief = '0' ";}
else{$where .= " AND a.actief = '1' ";}
$where .= " AND e.id > 0 ";

//voeg de sortering toe aan de database query
$sort =  "ORDER BY a.voornaam ASC";
if($_GET['sort_naam'] == 'asc'){$sort = "ORDER BY a.voornaam ASC";}
elseif($_GET['sort_naam'] == 'desc'){$sort = "ORDER BY a.voornaam DESC";}
elseif($_GET['sort_organisatie'] == 'asc'){$sort = "ORDER BY e.naam ASC";}
elseif($_GET['sort_organisatie'] == 'desc'){$sort = "ORDER BY e.naam DESC";}

$where .= $sort;

//haal de informatie op.
$result = sqlSelect($what,$from,$where);
//Alle relaties in de het schema zetten
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
            <div class="adres_rel" >'.htmlspecialchars($row['adres']).'&nbsp;'.'<br /> '.$row['plaats'].'&nbsp;</div>
            <div class="telnummer_rel">'.$row['telefoonnummer'].'&nbsp;</div>
            <div class="email_rel">'.$row['relatie_email'].'&nbsp;</div>
            <div class="wijzig_rel"><a href="/functions/'.$_GET['function'].'/includes/relatie_wijzigen.php?id='.$relatie_id.'&view='.$view.'&login_id='.$login_id.'&archief='.$_GET['archief'].'&secure='.secureLineEncode().'" class="iframe">'.$link.'</a></div>       
        </div>';
} ?>
</div>
</div>
