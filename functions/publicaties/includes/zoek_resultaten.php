<?php
session_start();
require ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
require ($_SERVER['DOCUMENT_ROOT'].$etc_root.'lib/ps_pagination.php');
	
	$column = $_GET['order']; $dir = $_GET['direction'];
    $q = "
    SELECT 
    id, titel, is_belangrijk, DATE_FORMAT(publicatiedatum, '%e %b %Y') publicatie,
    UNIX_TIMESTAMP(laatste_wijziging) laatst_gewijzigd
    FROM portal_nieuws
    WHERE actief = 1 ORDER BY $column $dir";
$rs = mysql_query( $q ) or die('Database Error: ' . mysql_error() . ' ' . $sql );
$pager = new PS_Pagination( $con, $q, 25, 4, null );
$rs = $pager->paginate();
$num = mysql_num_rows( $rs );
if($num > 0){
	while($row = mysql_fetch_array( $rs )){
           //zijn er bestanden
           $what = 'id'; $from = 'portal_nieuws_bestand'; $where='nieuws = '.$row['id'];
            $bestanden = countRows($what, $from, $where);
           //zijn er foto's
           $what = 'b.id'; $from = 'portal_nieuws_album a LEFT JOIN portal_image AS b ON (b.album = a.album)'; $where='a.nieuws = '.$row['id'].' AND b.actief = 1';
            $fotos = countRows($what, $from, $where);
                        
           $what = 'b.id'; $from = 'portal_nieuws_reactie a LEFT JOIN portal_reactie AS b ON (b.id = a.reactie)'; $where = 'a.nieuws = '.$row['id'].' AND b.actief = 1';
            $reacties = countRows($what, $from, $where);
            
           $what = 'id'; $from = 'portal_gebruiker'; $where = 'actief = 1';
            $totaal_aantal_gebruikers = countRows($what, $from, $where);
            
           $what = 'gebruiker'; $from = 'portal_nieuws_gebruiker'; $where= 'nieuws = '.$row['id']; 
            $bedoeldvoor = countRows($what, $from, $where);
            //echo $bedoeldvoor;
            
            if($bedoeldvoor == 1){
                $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                $what = 'gebruikersnaam, voornaam, achternaam'; $from = 'portal_gebruiker'; $where= 'id = '.$gebruiker['gebruiker']; 
                    $bedoeld_voor = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                    
                $bedoeld_voor_tekst = '<a href="'.$etc_root.'profiel/'.$bedoeld_voor['gebruikersnaam'].'">'.$bedoeld_voor['voornaam'].' '.$bedoeld_voor['achternaam'].'</a>';
            }elseif($bedoeldvoor < $totaal_aantal_gebruikers){
                $bedoeld_voor_tekst = $bedoeldvoor.' gebruikers';
            }else{
                $bedoeld_voor_tekst = 'Alle gebruikers';
            }
    ?> 
        <div class="nieuws_item">
            <?php if($row['is_belangrijk'] == 1){?>
            <div class="is_belangrijk belangrijk" id="nieuws_<?php echo $row['id']; ?>">
                <img class="img_prio" src="<?php echo $etc_root; ?>functions/publicaties/css/images/belangrijk.png" alt="hoge prioriteit" title="hoge prioriteit" onclick="veranderBelangrijk('<?php echo $row['id'] ?>', 0)" />
            </div>
            <?php }else{?>
            <div class="is_belangrijk" id="nieuws_<?php echo $row['id']; ?>">
                <img class="img_prio" src="<?php echo $etc_root; ?>functions/publicaties/css/images/onbelangrijk.png" alt="lage prioriteit" title="lage prioriteit" onclick="veranderBelangrijk('<?php echo $row['id'] ?>', 1)" />
            </div>  
            <?php }?>
            <div class="overzicht_titel"><a href="<?php echo $etc_root; ?>publicaties/wijzigen/nieuws-id=<?php echo $row['id'] ?>" title="nieuws wijzigen"><?php echo $row['titel'] ?></a></div>
            <div class="overzicht_publicatie"><?php echo $row['publicatie'] ?></div>
            <div class="overzicht_bedoeld_voor"><?php echo $bedoeld_voor_tekst ?></div>
            <div class="overzicht_bestanden">
                <?php 
                if($bestanden > 0){echo '<img src="'.$etc_root.'functions/publicaties/images/img_icon_file.png" alt="'.$bestanden.' bestanden aanwezig" title="'.$bestanden.' bestanden" />';}
                else{echo '&nbsp';}
                ?>  
            </div>
            <div class="overzicht_fotos">
                <?php 
                if($fotos > 0){echo '<img src="'.$etc_root.'functions/publicaties/images/img_icon_image.png" alt="'.$fotos.' foto\'s aanwezig" title="'.$fotos.' foto\'s" />';}
                else{echo '&nbsp;';}
                ?>
            </div>
            <div class="overzicht_reacties">
                <?php
                if($fotos > 0){echo $reacties;}
                else{echo '0';}
                ?>
	        </div>
	        <div class="overzicht_laatst_gewijzigd"><?php echo verstrekenTijd($row['laatst_gewijzigd']);?></div>
	    </div>
    <?php }
}else{
	//if no records found
	echo "Er is nog geen nieuws!";
}
//page-nav class to control
//the appearance of our page 
//number navigation
echo '<div id="pager">';
	//display our page number navigation
	echo $pager->renderFullNav();
echo "</div>";

?>