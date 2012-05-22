<?php 
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
if($_GET['action'] == 'getActiviteiten'){
	$what ="c.id, c.werkzaamheden, c.competentie"; 
    $from = "organisatie a
             LEFT JOIN project AS b ON (b.organisatie = a.id)
             LEFT JOIN planning_activiteit AS c ON ( c.project = b.id)
             LEFT JOIN planning_iteratie AS d ON (d.id = c.iteratie)";
    $where="a.id = '".$_GET['organisatie']."' AND b.actief = 1 AND (c.status != 'nog te plannen' AND c.status != 'done') AND d.actief = 1 AND d.huidige_iteratie = 1";
    $aantal_werkaamheden = countRows($what, $from, $where);
	$activiteit = null;
    // als je nog niet klaar bent met typen... laat dan ook onderstaand niet zien !
    if($aantal_werkaamheden > 0){ $result = sqlSelect($what, $from, $where); ?>
        <select id="activiteit" name="activiteit" class="textfield" onchange="selectCompetentie( this.value )">
    <?php while($row = mysql_fetch_array($result)){
    	  	if($activiteit == null){$activiteit = $row['id'];} //heb ik nodig voor het bepalen van de competentie, passend bij deze activiteit
   	?>
            <option value="<?php echo $row['id']; ?>"><?php echo $row['werkzaamheden']; ?></option>
    <?php } ?>
        	<option value="0">Anders...</option>        
        </select>
        <script>selectCompetentie( '<?php echo $activiteit; ?>' );</script>
    <?php }else{ ?>
      <select name="activiteit" disabled="disabled" class="textfield">
        <option value>Vul eerst bedrijf in.</option>
      </select>
    <?php }
}elseif($_GET['action'] == 'selectCompetentie'){
		$what ="competentie AS id"; $from = "planning_activiteit"; $where="id = '".$_GET['activiteit']."'"; $select = mysql_fetch_assoc(sqlSelect($what, $from, $where));
		$what = 'id, competentie'; $from = 'competentie'; $where = '1'; $competenties = sqlSelect($what, $from, $where);
							
		while($competentie = mysql_fetch_array($competenties)){?>
		<option value="<?php echo $competentie['id'] ?>" <?php if($competentie['id'] == $select['id']){echo 'selected = "selected"';} ?>><?php echo $competentie['competentie']; ?></option>		
		<?php }
}elseif($_POST['action'] == 'updateUren'){
	
	//check werkzaamheden en maak deze veiliger voor de database
    if($_POST['werkzaamheden'] != null){ $werkzaamheden = mysql_real_escape_string(nl2br(htmlentities($_POST['werkzaamheden'], ENT_NOQUOTES, "UTF-8"))); }
    else{ $error['werkzaamheden_fout'] = 'u hebt uw werkzaamheden niet (correct) ingevuld'; }
     
    //check het aantal uur
    if($_POST['aantal_uur'] != null){ $aantal_uur = str_replace(',', '.', $_POST['aantal_uur']); }
    else{ $error['aantal_uurfout'] = 'u hebt het aantal gewerkte uren niet (correct) ingevuld'; }
	
	if(!$error){
        $what = 'a.id, a.naam';   $from = 'organisatie a, urentool_registratie b';  $where = 'b.id = '.$_POST['uren_id'].' AND a.id = b.organisatie';
	        $bedrijfs = mysql_fetch_assoc(sqlSelect($what,$from,$where));
            
        $table = "urentool_registratie";
        $what = "werkzaamheden = '$werkzaamheden', aantal_uur = '$aantal_uur', gewijzigd_op = NOW(), gewijzigd_door = $login_id ";
        $where = 'id = '.$_POST['uren_id'];
            $update_deze_registratie  = sqlUpdate($table,$what,$where);   //insert ze maar
        
        $what = '	b.id, DATE_FORMAT(b.datum, \'%e-%c-%Y\') AS datum'; 
        $from = '	urentool_registratie a
        			LEFT JOIN urentool_datum AS b ON (b.id = a.datum_id)';
		$where = '	a.id = '.$_POST['uren_id'];
	    	$datum = mysql_fetch_assoc(sqlSelect($what,$from,$where));
        ?>
            
       U hebt uw uren voor <a href="<?php echo $etc_root.'crm2/detail/organisatie_id='.$bedrijfs['id']?>" target="_blank"><?php echo $bedrijfs['naam'] ?></a> gewijzigd.<br /> De lijst wordt automatisch bijgewerkt
		<script>
		jQuery(function(){
			var opt = jQuery('#invoer').attr('data-laadUren').split('_'); 
		
			laadUren( opt[0], opt[1], '<?php echo $datum['datum'] ?>', '<?php echo $datum['id']; ?>'); 
			setTimeout("$('#dialog').dialog('close')",3000);
		});
		</script>
    <?php }else{$_SESSION['error'] = $error;} //als er fouten zijn, stop deze in de sessie, die komen straks wel.
	
	
	//echo 'uren ontvangen !';
}elseif($_POST['action'] == 'voerUrenIn'){
    
    if($_POST['datum_id'] == 'onb'){
        list($dag, $maand, $jaar) = explode(' ', $_POST['datum']);
        $maand_nummer = $maandnummer["$maand"];
        
        $what="id, gereed"; $from="urentool_datum"; $where="jaar = $jaar AND maand = $maand_nummer AND dag = $dag AND gebruiker = $login_id"; 
            
    }else{ $what="id, gereed"; $from="urentool_datum"; $where= 'id = '.$_POST['datum_id']; }
    
    $datum = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $datum_id = $datum['id'];
    
    //check nu alle dingen aan bedrijf. Hoeveel zijn er hierboven uit gekomen ? Is het er 1 pak dan het organisatie_id        
    if($_POST['bedrijf'] != null){
    	$what = 'id, naam';   $from = 'organisatie';  $where = "naam = '".$_POST['bedrijf']."'";
    		$rijen = countRows($what,$from,$where);
        if($rijen == '0'){ $error['bedrijf_teweinig'] = 'er is geen bedrijf bekend met die naam.'; }
        elseif($rijen >= 2){ $error['bedrijf_teveel'] = 'er zijn meerdere bedrijven bekend met die naam.'; }
        else{ $bedrijf = $_POST['bedrijf']; $row = mysql_fetch_assoc(sqlSelect($what,$from,$where)); $organisatie_id = $row['id']; }
    }else{ $error['bedrijf_fout'] = 'u hebt het bedrijf niet (correct) ingevuld'; }

    //check werkzaamheden en maak deze veiliger voor de database
    if($_POST['werkzaamheden'] != null){ $werkzaamheden = mysql_real_escape_string(nl2br(htmlentities($_POST['werkzaamheden'], ENT_NOQUOTES, "UTF-8"))); }
    else{ $error['werkzaamheden_fout'] = 'u hebt uw werkzaamheden niet (correct) ingevuld'; }
     
    //check het aantal uur
    if($_POST['aantal_uur'] != null){ $aantal_uur = str_replace(',', '.', $_POST['aantal_uur']); }
    else{ $error['aantal_uurfout'] = 'u hebt het aantal gewerkte uren niet (correct) ingevuld'; }
    
    //geen fouten ? go for it.
    if(!$error){       
        if($_POST['activiteit'] == null || $_POST['activiteit'] < 1){ $activiteit = '0'; }
        else{ $activiteit = $_POST['activiteit']; }
        
        $table = "urentool_registratie";
        $what = "datum_id, organisatie, activiteit, competentie, werkzaamheden, aantal_uur, gewijzigd_op, gewijzigd_door ";
        $with_what = $datum_id.", $organisatie_id, $activiteit, '".$_POST['competentie']."', '$werkzaamheden', '$aantal_uur', NOW(),'$login_id' ";
            
            $insert_nieuwe_uren = sqlInsert($table,$what,$with_what);   //insert ze maar
    ?>
        U hebt <?php echo $aantal_uur  ?> uur ingevuld voor <a href="<?php echo $etc_root.'crm2/detail/organisatie_id='.$organisatie_id; ?>" target="_blank"> <?php echo $row['naam'] ?> </a>.
        <?php 
        if($_POST['gereed'] == '1'){ }
        else{?>
        <br /> Als u geen data meer hebt in te vullen voor deze dag, druk dan op <span id ="inline_gereed" onclick="gereedDatum('<?php echo $_POST['datum_id']; ?>')" class="button">gereed</span> om de dag af te sluiten!
    <?php } //het is niet gereed, dus je kunt nog op gereed drukken !
    }else{$_SESSION['error'] = $error;} //als er fouten zijn, stop deze in de sessie, die komen straks wel. //als er fouten zijn, stop deze in de sessie, die komen straks wel.

}elseif($_POST['action'] == 'deleteUren'){
    $table = 'urentool_registratie'; $where = 'id = '.$_POST['id'];
        $delete_uren = sqlDelete($table, $where);?>
        U hebt de uren succesvol verwijderd
<?php }
elseif($_GET['action'] == 'dagGereed'){
    $data = explode('_', $_GET['data']);
    $gebruiker = $data[0];
    if($data[3] == 'onb'){
        list($dag, $maand, $jaar) = explode(' ', $data[2]);
        $maand_nummer = $maandnummer["$maand"];
       
        $what="id"; $from="urentool_datum"; $where="jaar = $jaar AND maand = $maand_nummer AND dag = $dag AND gebruiker = $gebruiker"; 
            $datum = mysql_fetch_assoc(sqlSelect($what, $from, $where)); 
    }
    else{
        $what="id"; $from="urentool_datum"; $where="id = ".$data[3]." AND gebruiker = $gebruiker"; 
            $datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    }
          
    if($datum['id'] != null){$datum_id = $datum['id'];} else{$datum_bestaat_niet = true;}
    
    if(!$datum_bestaat_niet){
        //zet deze datum op gereed
        $table = 'urentool_datum'; $what = 'gereed = 1'; $where = "id = $datum_id";
            $datum_gereed = sqlUpdate($table, $what, $where);
            
        //laat nu de volgende datum zien, of de melding dat er geen datums meer zijn.
        $what="id, datum  d2,  DATE_FORMAT(datum, '%e-%c-%Y') AS datum, dag,  maand,  jaar";
        $from="urentool_datum";
        $where="gereed = 0  AND gebruiker = $login_id AND datum <= CURDATE() ORDER BY d2 ASC";
        $tel_dagen = countRows($what,$from,$where);
        
        if($tel_dagen > 0){
            $row1= mysql_fetch_assoc(sqlSelect($what,$from,$where)); 
            
            $dag = $row1['dag']; $maand = $row1['maand']; $jaar = $row1['jaar']; 
            if($maand < 10){ $maand = '0'.$maand;}
            
            //maak de laadUren data aan
            $datum_id = $row1['id']; $datum = $dag." ".$maandnaam[$maand]." ".$jaar; $gereed = 0; 
        }else{
            //maak de laadUren data leeg
            $datum_id = ''; $datum = ''; $gereed = 1;    
        ?>Klik in het tekstvak hiernaast om een dag terug te zoeken.<br /> Klik op \'bekijk dag\' om de dag te bekijken <?php }?>
        <script>
            jQuery( function(){ 
                reloadDagenLijst();
                laadUren('<?php echo $login_id; ?>', <?php echo $gereed; ?>, '<?php echo $datum; ?>', '<?php echo $datum_id; ?>'); 
            }); 
        </script>
<?php }
        
}
elseif($_GET['action'] == 'reloadDagenLijst'){
    //haal de lijst op
    $what="id,  dag,  maand,  jaar,  datum  d2,  DATE_FORMAT(datum, '%W %d %M %Y') AS datum";
    $from="urentool_datum";
    $where="datum <= CURDATE()  AND gereed = 0  AND gebruiker = $login_id  ORDER BY d2 ASC";
    $result = sqlSelect($what,$from,$where);
    
    //hoeveel dagen zijn er die hier aan voldoen?
    $count_dagen = countRows($what,$from,$where);
    if($maand < 10){ $maand = '0'.$maand;}
    //als er meer dan 0 dagen zijn die er aan voldoen, maak dan een lijst van de datums.
    if($count_dagen > 0 ){
        while($row = mysql_fetch_array($result)){
            if($maand < 10){ $maand = '0'.$row['maand'];}else{$maand = $row['maand'];}?>
            <li class="selecteer_dag" onclick="laadUren('<?php echo $login_id; ?>', '0', '<?php echo $row['dag']." ".$maandnaam[$maand]." ".$row['jaar']; ?>', '<?php echo $row['id']; ?>')"><?php echo $row['datum']; ?></li>
    <?php }
    }else{                                        echo "<span id=\"lege_dagen\">Geen dagen meer</span>"; $leeg = 1;} 

}elseif($_GET['action'] == 'reloadForm'){?>
<script>
    jQuery(function(){
            var opt = jQuery('#invoer').attr('data-laadUren').split('_'),
                archief_opt = jQuery('#archief_nu').text(),                    
                toevoegen_options = { 
                    target:     '#succes_titel', 
                    url:        '/functions/urentool/includes/ajax.php', 
                    data: { action: 'voerUrenIn'},
                    success:    function() {  laadUren( opt[0], opt[1], jQuery('#datum_val').val(),  jQuery('#datum_id_val').val());  reloadForm();  archief(archief_opt, 'archief');   } 
                }; 
            //ververs de values in het formulier
            console.log(opt);
            jQuery('#datum_val').val(opt[2]); jQuery('#datum_id_val').val(opt[3]);
             
            var v1 = jQuery("#uren_toevoegen").bind("invalid-form.validate",function(){
            $("#error_titel").html("Er is een fout opgetreden:");})
            .validate({
                errorContainer:$("#error_titel, #fouttekst"),
                errorLabelContainer:"#errors",
                wrapper:"li",
                errorElement:"span",
                rules:{werkzaamheden:"required", activiteit:"required",bedrijf:{required: true, minlength: 2, remote: "/functions/urentool/includes/bedrijf.php" },competentie:"required", aantal_uur:{required:true, number: true}},
                messages:{werkzaamheden:"U dient een korte beschrijving van de gemaakte uren in te voeren.", activiteit:"U dient de activiteit of 'anders' te selecteren van de gemaakte uren",bedrijf:"U dient de klant in te voeren",competentie:"U dient de gebruikte competentie te selecteren van de gemaakte uren", aantal_uur:{required:"U dient het aantal gemaakte uren in te voeren", number: "Het aantal gemaakte uren dient een (decimaal) getal te zijn, met een .  in plaats van een ,"}},
                submitHandler: function(form) { 
                    jQuery(form).ajaxSubmit(toevoegen_options); 
                    jQuery('#succes_titel').show(); 
                    setTimeout("jQuery('#succes_titel').fadeOut(3000)",3000);
                }
            }); return false;
        });   
</script>
<form method="post" action="<?php echo $etc_root.'functions/urentool/includes/ajax.php' ?>" id="uren_toevoegen">
    <div id="bedrijf_activiteit">
        <span id="bedrijf_container"><label for="bedrijf">Bedrijf</label> <input type="text" id="bedrijf" name="bedrijf" class="textfield" /></span>
        <span id="activiteit_container">
            <select id="activiteit" name="activiteit" disabled="disabled" class="textfield">
                <option value="">Vul eerst bedrijf in.</option>
            </select>
        </span> 
    </div>
    <span id="werkzaamheden_container">
            <label for="werkzaamheden">Werkzaamheden</label>
            <textarea  class="textarea" id="werkzaamheden" cols="35" rows="5" name="werkzaamheden"></textarea>
    </span>
    <div id="categorie_aantal_uur">
        <select id="competentie" name="competentie" class="textfield">
                <option value>Kies een competentie</option>
				<?php 
					$what = 'id, competentie'; $from = 'competentie'; $where = '1';
						$competenties = sqlSelect($what, $from, $where);
					
					while($competentie = mysql_fetch_array($competenties)){?>
				<option value="<?php echo $competentie['id'] ?>"><?php echo $competentie['competentie']; ?></option>		
				<?php } ?>
        </select>
        <span id="aantal_uur_container"><label for="aantal_uur">Aantal uur</label><input type="text" id="aantal_uur" name="aantal_uur" class="textfield"  /></span>
    </div>
    <input type="hidden" id="datum_id_val" name="datum_id" value=""/>
    <input type="hidden" id="datum_val" name="datum" value=""/>
    <input type="hidden" id="datum_gereed_val" name="gereed" value="0" />          
    <input type="submit" id="verstuur_uren" value="verstuur uren" class="button" />
</form>	
<?php }else{
if($_GET['gereed'] == 1){
    //we zijn 'klaar'. Er staan geen datums meer open en de lijst is dus leeg. Dit moeten we communiceren
    //ook moeten we uitnodigen om andere dagen te bekijken
    
    //eerst even de h2 wijzigen
    //daarna uitnodigen om andere dagen te bekijken
    ?>   
    <script>
       jQuery(function(){ 
           jQuery('#invoer_titel h2').html('U hebt alle dagen ingevuld'); 
           jQuery('#dag_gereed').hide();
           jQuery('#uren_toevoegen').hide(); 
       });
    </script>
    Klik in het tekstvak hiernaast om een dag terug te zoeken.<br /> Klik op 'bekijk dag' om de dag te bekijken
<?php

 }else{
if($_GET['gebruiker'] != $login_id){ $gebruiker = $_GET['gebruiker']; }else{$gebruiker = $login_id; }
	if($_GET['datum_id'] == 'onb'){
		list($dag, $maand, $jaar) = explode(' ', $_GET['datum']);
		$maand_nummer = $maandnummer["$maand"];
		
    	$what="id, gereed"; $from="urentool_datum"; $where="jaar = $jaar AND maand = $maand_nummer AND dag = $dag AND gebruiker = $gebruiker"; 
			$datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	}else{
		$what="id, gereed"; $from="urentool_datum"; $where="id = ".$_GET['datum_id']." AND gebruiker = $gebruiker"; 
			$datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	}
    
    if($datum['id'] != null){$datum_id = $datum['id'];}  else{$datum_bestaat_niet = true;}
    
	//deze datum bestaat wel, dus laat alles maar zien
	if(!$datum_bestaat_niet){?>
<script>
       jQuery(function(){ 
           <?php if($datum['gereed'] == 1){ ?>
               jQuery('#invoer_titel h2').html('<?php echo $_GET['datum']; ?>' + '<span id="in_archief">--in archief--</span>');
               jQuery('#dag_gereed').hide(); 
               jQuery('#datum_gereed_val').val('1');
           <?php }else{ ?>
               jQuery('#dag_gereed').show();
               jQuery('#dag_gereed').on('click', function(){ dagGereed(); });
           <?php } ?> 
           jQuery('#uren_toevoegen').show(); 
       });
</script>		
<?php $what=" b.id AS reg_id, b.aantal_uur, b.werkzaamheden AS beschrijving, c.id AS org_id, c.naam, d.werkzaamheden AS activiteit, e.competentie";
	$from=" urentool_datum a
	        LEFT JOIN urentool_registratie AS b ON (b.datum_id = a.id) 
	        LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
	        LEFT JOIN planning_activiteit AS d ON ( d.id = b.activiteit )
			LEFT JOIN competentie AS e ON ( e.id = b.competentie ) ";
	$where=" a.id = $datum_id AND a.gebruiker = $gebruiker AND b.id IS NOT NULL";
		$aantal_uren = countRows($what, $from, $where);
	//echo "SELECT $what FROM $from WHERE $where";
	if($aantal_uren > 0){
?>
	<table id="urentable" border="0" cellspacing="3" cellpadding="0" >
	    <thead>
	        <tr>
	            <th id="klant_head">Klant</th>
	            <th id="werkzaamheden_head">Werkzaamheden</th>
	            <th id="categorie_head">Competentie</th>
	            <th id="uur_head">uur</th>
	            <th id="acties_head"> </th>
	        </tr>
	    </thead>
	    <tbody>
	<?php
	$result = sqlSelect($what,$from,$where);
	while($row = mysql_fetch_array($result)){
		if($i == 1){$class="en_om"; $i=2;}else{$class="om"; $i=1;}
	//if($row['id'] == $_GET['registratie_id']){  echo '<form action="'.$site_name.'functions/'.$_GET['function'].'/includes/urentool_check.php" method="post">'; }
	?>
    		<tr class="<?php echo $class; ?>">
	        	<td class="klant" valign="top" width="176">
	             	<a href="<?php echo $etc_root.'crm2/detail/organisatie-id='.$row['org_id']; ?>" target="_blank"><?php echo $row['naam'] ?></a>
	<?php if($row['activiteit'] != null){?>
					<p class="activiteit_text"><?php echo $row['activiteit'] ?></p>
	<?php }?>
	            </td>
	            <td class="werkzaamheden" valign="top" width="420"> <?php echo  $row['beschrijving']?> </td>
	            <td class="competentie" valign="top" width="50"> <?php echo $row['competentie'] ?> </td>
	            <td class="uur" valign="top" width="24"> <?php echo  $row['aantal_uur']; ?> </td>
	            <td class="acties" valign="top" width="45">                                    
				   <img class="delete" id="delete_<?php echo $row['reg_id']; ?>" src="<?php echo $etc_root; ?>functions/urentool/css/images/delete.png" alt="X" />
                   <img class="edit" id="edit_<?php echo $row['reg_id']; ?>" src="<?php echo $etc_root; ?>functions/urentool/css/images/edit.png" alt="wijzig" />
			    </td>
	       </tr>
<?php }//  einde while ?>
		</tbody>
  	</table>
<?php }//  einde 'if not empty registraties date'
else{?> U hebt nog geen uren geregistreerd voor deze datum, vul ze hieronder in <?php } 
}//  de doorgestuurde datumid (of gezochte dag) bestaat !
else{?> 
	U kunt alleen uren invullen bij dagen in het verleden, die voor u zijn aangemaakt  
<script>
       jQuery(function(){ 
           jQuery('#dag_gereed').hide(); 
           jQuery('#uren_toevoegen').hide(); 
       });
</script>
<?php }//  de doorgestuurde datumid (of gezochte dag) bestaat niet!
}//  er is geen 'gereed = 1' meegegeven
}//  dit is niet 'action == getActiviteiten'
?>
