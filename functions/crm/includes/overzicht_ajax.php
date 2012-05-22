<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');?>
<script>
    jQuery(function(){
            jQuery('.lijst_body .lijst_row').hover( function(){jQuery(this).addClass('lijst_row_hover');}, function(){jQuery(this).removeClass('lijst_row_hover'); });
            jQuery('.lijst_body .lijst_row').on('click', function(){
                loadCRM('detail', jQuery(this).attr('id'), '<?php echo $_POST['detail']; ?>');
            });
    })
</script>
<?php
// één voor één gaan we bepalen wat er gebeurt als het overzicht wordt geladen.

if($_POST['detail'] == 'organisatie'){
	$what = 'a.id, a.naam AS organisatienaam, a.badres, a.bpostcode, a.bplaats, a.land, a.telefoonnummer, a.email, b.naam AS branche';
	$from = 'organisatie a LEFT JOIN branche AS b ON (b.id = a.branche_id)';
	$where = 'a.actief = 1';
	if($_POST['filter'] != null){ $where .= ' AND a.naam LIKE \'%'.$_POST['filter'].'%\''; }
	if($_POST['orderBy'] != null){
		$order_by = explode('_', $_POST['orderBy']);	
 	    $orderby_naam = $order_by[0]; $orderby_volgorde = $order_by[1];
		if($orderby_naam != 'branche'){$orderbynaam = 'a.'.$orderby_naam;}
		else{$orderbynaam = 'b.naam';}
		$where .= " ORDER BY $orderbynaam $orderby_volgorde";
	}
		//echo  "SELECT $what FROM $from WHERE $where";
	$aantal_organisaties = countRows($what, $from, $where);
	if($aantal_organisaties > 0){$organisaties = sqlSelect($what, $from, $where);}
?>
<div id="organisatie_lijst_top" class="lijst_top">
    <?php 
        if($orderby_naam == 'naam'){
            if($orderby_volgorde == 'DESC'){$loadLijst_naam_volgorde = '_ASC';}
            else{$loadLijst_naam_volgorde = '_DESC';}
        }else{$loadLijst_naam_volgorde = '_ASC';}
        if($orderby_naam == 'branche'){
            if($orderby_volgorde == 'DESC'){$loadLijst_branche_volgorde = '_ASC';}
            else{$loadLijst_branche_volgorde = '_DESC';}
        }else{$loadLijst_branche_volgorde = '_ASC';}
        if($orderby_naam == 'bplaats'){
            if($orderby_volgorde == 'DESC'){$loadLijst_plaats_volgorde = '_ASC';}
            else{$loadLijst_plaats_volgorde = '_DESC';}
        }else{$loadLijst_plaats_volgorde = '_ASC';}
        if($orderby_naam == 'telefoonnummer'){
            if($orderby_volgorde == 'DESC'){$loadLijst_telefoon_volgorde = '_ASC';}
            else{$loadLijst_telefoon_volgorde = '_DESC';}
        }else{$loadLijst_telefoon_volgorde = '_ASC';}
        if($orderby_naam == 'email'){
            if($orderby_volgorde == 'DESC'){$loadLijst_email_volgorde = '_ASC';}
            else{$loadLijst_email_volgorde = '_DESC';}
        }else{$loadLijst_email_volgorde = '_ASC';}
    ?>
	<span class="organisatie_organisatienaam_header organisatie_header" onclick="loadLijst('naam<?php echo $loadLijst_naam_volgorde; ?>');">Organisatienaam</span>	
	<span class="organisatie_branche_header organisatie_header" onclick="loadLijst('branche<?php echo $loadLijst_branche_volgorde; ?>');">Branche</span>	
	<span class="organisatie_adres_header organisatie_header" onclick="loadLijst('bplaats<?php echo $loadLijst_plaats_volgorde; ?>');">Adres</span>	
	<span class="organisatie_telefoon_header organisatie_header" onclick="loadLijst('telefoonnummer<?php echo  $loadLijst_telefoon_volgorde; ?>');">Telefoonnummer</span>	
	<span class="organisatie_email_header organisatie_header" onclick="loadLijst('email<?php echo $loadLijst_email_volgorde; ?>');">E-mail</span>	
</div>
<div id="organisatie_lijst_body" class="lijst_body">
    <?php 
if($aantal_organisaties > 0){
    while($organisatie = mysql_fetch_array($organisaties)){?>
    <div class="organisatie_lijst_row lijst_row" id="<?php echo $organisatie['id']; ?>">
    	<div class="organisatie_organisatienaam organisatie_kolom"> <?php echo $organisatie['organisatienaam']; ?>&nbsp;</div>	
    	<div class="organisatie_branche_header organisatie_kolom"> <?php echo $organisatie['branche']; ?>&nbsp;</div>	
    	<div class="organisatie_adres_header organisatie_kolom">
    	<?php
    		$landen = array( 'nederland', 'Nederland', 'nl', 'NL', 'nld', 'NLD' );
    		if($organisatie['badres'] != null){ echo '<span class="organisatie_adres_adres">'.$organisatie['badres'].'</span>'; }
    		if($organisatie['bpostcode'] != null){ echo '<span class="organisatie_adres_postcode">'.$organisatie['bpostcode'].'</span>'; }
    		if($organisatie['bplaats'] != null){ echo '<span class="organisatie_adres_plaats">'.$organisatie['bplaats'].'</span>'; }
    		if($organisatie['land'] != null && !in_array($organisatie['land'], $landen)){ echo '<span class="organisatie_adres_land">'.$organisatie['land'].'</span>'; }
    	?>
    	&nbsp;
    	</div>	
    	<div class="organisatie_telefoon_header organisatie_kolom"><?php echo $organisatie['telefoonnummer']; ?>&nbsp;</div>	
    	<div class="organisatie_email_header organisatie_kolom"><?php echo $organisatie['email']; ?>&nbsp;</div>	
	</div>	
<?php }
}else{echo  'Er zijn organisaties, die voldoen aan uw filter... <a href="/crm/organisatie-toevoegen/">Voeg er een toe</a>';}?>  	
</div>
<?php }// einde van organisatie

elseif($_POST['detail'] == 'relatie'){
    $what = 'a.id, a.voornaam, a.achternaam, a.adres, a.postcode, a.plaats, a.land, a.telefoonnummer, a.email, c.naam AS organisatienaam';
    $from = 'relaties a LEFT JOIN relatie_organisatie AS b ON (b.relatie = a.id) LEFT JOIN organisatie AS c ON (c.id = b.organisatie)';
    $where = 'a.actief = 1 AND c.actief = 1';
    if($_POST['filter'] != null){ $where .= ' AND a.voornaam LIKE \'%'.$_POST['filter'].'%\' OR a.achternaam LIKE \'%'.$_POST['filter'].'%\''; }
    if($_POST['orderBy'] != null){
        $order_by = explode('_', $_POST['orderBy']);    
        $orderby_naam = $order_by[0]; $orderby_volgorde = $order_by[1];
        if($orderby_naam != 'organisatie'){$orderbynaam = 'a.'.$orderby_naam;}
        else{$orderbynaam = 'c.naam';}
        $where .= " ORDER BY $orderbynaam $orderby_volgorde";
    }
        //echo  "SELECT $what FROM $from WHERE $where";
    $aantal_relaties = countRows($what, $from, $where);
    if($aantal_relaties > 0){$relaties = sqlSelect($what, $from, $where);}
?>

<div id="relatie_lijst_top" class="lijst_top">
    <?php 
        if($orderby_naam == 'achternaam'){
            if($orderby_volgorde == 'DESC'){$loadLijst_naam_volgorde = '_ASC';}
            else{$loadLijst_naam_volgorde = '_DESC';}
        }else{$loadLijst_naam_volgorde = '_ASC';}
        if($orderby_naam == 'organisatie'){
            if($orderby_volgorde == 'DESC'){$loadLijst_branche_volgorde = '_ASC';}
            else{$loadLijst_branche_volgorde = '_DESC';}
        }else{$loadLijst_branche_volgorde = '_ASC';}
        if($orderby_naam == 'bplaats'){
            if($orderby_volgorde == 'DESC'){$loadLijst_plaats_volgorde = '_ASC';}
            else{$loadLijst_plaats_volgorde = '_DESC';}
        }else{$loadLijst_plaats_volgorde = '_ASC';}
        if($orderby_naam == 'telefoonnummer'){
            if($orderby_volgorde == 'DESC'){$loadLijst_telefoon_volgorde = '_ASC';}
            else{$loadLijst_telefoon_volgorde = '_DESC';}
        }else{$loadLijst_telefoon_volgorde = '_ASC';}
        if($orderby_naam == 'email'){
            if($orderby_volgorde == 'DESC'){$loadLijst_email_volgorde = '_ASC';}
            else{$loadLijst_email_volgorde = '_DESC';}
        }else{$loadLijst_email_volgorde = '_ASC';}
    ?>
    <span class="relatie_naam_header relatie_header" onclick="loadLijst('achternaam<?php echo $loadLijst_naam_volgorde  ?>');">Naam</span>  
    <span class="relatie_organisatie_header relatie_header" onclick="loadLijst('organisatie<?php echo $loadLijst_branche_volgorde; ?>');">Organisatie</span>    
    <span class="relatie_adres_header relatie_header" onclick="loadLijst('plaats<?php echo $loadLijst_plaats_volgorde; ?>');">Adres</span> 
    <span class="relatie_telefoon_header relatie_header" onclick="loadLijst('telefoonnummer<?php echo  $loadLijst_telefoon_volgorde; ?>');">Telefoonnummer</span>   
    <span class="relatie_email_header relatie_header" onclick="loadLijst('email<?php echo $loadLijst_email_volgorde; ?>');">E-mail</span>   
</div>
<div id="relatie_lijst_body" class="lijst_body">
<?php 
if($aantal_relaties > 0){
    while($relatie = mysql_fetch_array($relaties)){?>
    <div class="relatie_lijst_row lijst_row" id="<?php echo $relatie['id'] ?>">
        <div class="relatie_naam relatie_kolom"> <?php echo $relatie['voornaam'].' '.$relatie['achternaam']; ?>&nbsp;</div>  
        <div class="relatie_organisatie_header relatie_kolom"> <?php echo $relatie['organisatienaam']; ?>&nbsp;</div>   
        <div class="relatie_adres_header relatie_kolom">
        <?php
            $landen = array( 'nederland', 'Nederland', 'nl', 'NL', 'nld', 'NLD' );
            if($relatie['adres'] != null){ echo '<span class="relatie_adres_adres">'.$relatie['adres'].'</span>'; }
            if($relatie['postcode'] != null){ echo '<span class="relatie_adres_postcode">'.$relatie['postcode'].'</span>'; }
            if($relatie['plaats'] != null){ echo '<span class="relatie_adres_plaats">'.$relatie['plaats'].'</span>'; }
            if($relatie['land'] != null && !in_array($relatie['land'], $landen)){ echo '<span class="relatie_adres_land">'.$relatie['land'].'</span>'; }
        ?>
        &nbsp;
        </div>  
        <div class="relatie_telefoon_header relatie_kolom"><?php echo $relatie['telefoonnummer']; ?>&nbsp;</div>  
        <div class="relatie_email_header relatie_kolom"><?php echo $relatie['email']; ?>&nbsp;</div>    
    </div>  
<?php }
}else{echo  'Er zijn relaties, die voldoen aan uw filter... <a href="/crm/relatie-toevoegen/">Voeg er een toe</a>';}?>   
</div>
<?php } // einde van relatie ?>