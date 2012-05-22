<?php
$gebruiker = $login_id;
if( checkUrentoolDatums( $login_id ) != null && checkUrentoolDatums( $login_id ) != false ){ mysql_query( checkUrentoolDatums( $login_id ) ) or die( mysql_error() ); } //kijk of er urentool datums moeten worden bijgemaakt voor deze gebruiker 

?>
<div id="urentool">
    <div id="dagen">
        <div id="dagen_titel">
            <h2 class="titel">Dagen</h2>
        </div>
        <ul id="in_te_vullen_dagen">
        <?php
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
        ?>
        </ul>
        <div id="selecteer_dag">
            <form id="selecteer_datum"> 
            	<input type="text" name="datum" id="datum_kiezen" class="textfield" /> 
            </form>
            <button id="datum_verzend" class="button datum_verzend" onmouseout="this.className='button datum_verzend' " onmouseover="this.className='button btn_hover datum_verzend'">bekijk dag</button>
        </div>
    
    </div>
	
	<?php                     
if($datum != null){
	
    //haal de dag, maand en jaar uit de url
    list($dag, $maand, $jaar) = explode('-', $datum);
	
    $what="id, gereed"; $from="urentool_datum"; $where="jaar = $jaar AND maand = $maand AND dag = $dag AND gebruiker = $gebruiker"; 
    	$tel_dagen = countRows($what,$from,$where);
    	
    if($tel_dagen > 0){
    	//echo "SELECT $what FROM $from WHERE $where";
        $row1= mysql_fetch_assoc(sqlSelect($what,$from,$where));
       	$datum_id = $row1['id'];
        //aangezien maandnaam werkt met hele maanden, getallen (met 0), even de 0 er bij zetten.
        //dus geen 1, 2 maar 01, 02
        if($maand < 10){ $maand = '0'.$maand;}
         $mooie_datum = $dag." ".$maandnaam[$maand]." ".$jaar; 
         $titel =  $mooie_datum;
        
        //als dit record gereed is, laten zien dat we in het archief zitten.
       	if($row1['gereed'] == 1){ $titel.= '<span id="in_archief">--in archief--</span>'; $leeg = true; }
        
    } else{ $titel =  '<span id="opnieuw_kiezen">U hebt een dag gekozen, waar geen uren voor bestaan</span>'; $geen_form = true; $leeg = true; }
}else{
    $what="id, datum  d2,  DATE_FORMAT(datum, '%e-%c-%Y') AS datum, dag,  maand,  jaar";
    $from="urentool_datum";
    $where="gereed = 0  AND gebruiker = $login_id AND datum <= CURDATE() ORDER BY d2 ASC";
    $tel_dagen = countRows($what,$from,$where);
        
    if($tel_dagen > 0){
    	$row1= mysql_fetch_assoc(sqlSelect($what,$from,$where)); 
        
        $dag = $row1['dag']; $maand = $row1['maand']; $jaar = $row1['jaar']; $datum_id = $row1['id'];
    	if($maand < 10){ $maand = '0'.$maand;}
        
        $mooie_datum = $dag." ".$maandnaam[$maand]." ".$jaar;
        $titel = $mooie_datum; 
        
    }else{ $titel =  '<span id="opnieuw_kiezen">U hebt geen dagen meer om in te voeren.</span>'; $geen_form = true; $leeg = true; $kies_andere_dag = true; } 
}
if($_GET['gereed']){$gereed = $_GET['gereed'];}else{$gereed = 0;}

if($geen_form){ ?>
		<script>jQuery(function(){ jQuery('#uren_toevoegen').hide(); })</script>    
<?php } ?> 
	
    <div id="invoer" data-laadUren="<?php echo $gebruiker.'_'.$gereed.'_'.$dag.' '.$maandnaam[$maand].' '.$jaar.'_'.$datum_id; ?>" data-datumId="<?php echo $datum_id; ?>">
    	<div id="invoer_titel">
            <h2 class="titel"> <?php echo $titel; ?> </h2>
            <span title="Dag afronden" class="button" id="dag_gereed"<?php  if($leeg){ echo ' style="display:none;" '; }?>>gereed</span>
        </div>
        
<?php
?>
	<div id="error_container">
        	<h2 id="succes_titel"></h2>
        	
            <h2 id="error_titel"></h2>
            <ul id="errors">
			</ul>
    </div>
        <div id="invoer_tabel">
<?php   
if($kies_andere_dag == null && $gereed == 0){
    if(isset($datum_id)){
		$what = 'dag, maand, jaar'; $from=" urentool_datum"; $where=" id = $datum_id";
			$datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    	
    	$what=" b.id AS reg_id, b.aantal_uur, b.werkzaamheden AS beschrijving, c.id AS org_id, c.naam, d.werkzaamheden AS activiteit, e.competentie";
		$from=" urentool_datum a
		        LEFT JOIN urentool_registratie AS b ON (b.datum_id = a.id) 
		        LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
		        LEFT JOIN planning_activiteit AS d ON ( d.id = b.activiteit )
				LEFT JOIN competentie AS e ON ( e.id = b.competentie ) ";
		$where=" a.id = $datum_id AND a.gebruiker = $gebruiker AND a.gereed = $gereed AND b.id IS NOT NULL";
        	$aantal_uren = countRows($what, $from, $where);
        	
		if($aantal_uren > 0 ){
?>
            <table id="urentable" border="0" cellspacing="2" cellpadding="0" >
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
                         	<p class="klant_text">
                         		<a href="<?php echo $etc_root.'crm2/detail/organisatie-id='.$row['org_id']; ?>" target="_blank"><?php echo $row['naam'] ?></a>
                         	</p>
<?php if($row['activiteit'] != null){?>
							<p class="activiteit_text"><?php echo $row['activiteit'] ?></p>
<?php }?>
                        </td>
                        <td class="werkzaamheden" valign="top" width="420"> <?php echo  $row['beschrijving']?> </td>
                        <td class="competentie" valign="top" width="50">  <?php echo $row['competentie'] ?> </td>
                        <td class="uur" valign="top" width="24">  <?php echo  $row['aantal_uur']; ?>   </td>
                        <td class="acties" valign="top" width="45"> 
						   <img class="delete" id="delete_<?php echo $row['reg_id']; ?>" src="<?php echo $etc_root; ?>functions/urentool/css/images/delete.png" alt="X" />
						   <img class="edit" id="edit_<?php echo $row['reg_id']; ?>" src="<?php echo $etc_root; ?>functions/urentool/css/images/edit.png" alt="wijzig" />
					   </td>
                   </tr>
                
<?php }//einde while ?>
			</tbody>
          </table>
<?php }
}//einde 'if not empty registraties date'
}else{?>Klik in het tekstvak hiernaast om een dag terug te zoeken.<br /> Klik op 'bekijk dag' om de dag te bekijken
<?php } ?>
        &nbsp;          
		</div>
		<div id="uren_toevoegen_form">
		    
            <script>
                jQuery(function(){
                    var opt = jQuery('#invoer').attr('data-laadUren').split('_'),
                        archief_opt = jQuery('#archief_nu').text(),                    
                        toevoegen_options = { 
                            target:     '#succes_titel', 
                            url:        '/functions/urentool/includes/ajax.php', 
                            data: { action: 'voerUrenIn'},
                            success:    function() {  laadUren( opt[0], opt[1], jQuery('#datum_val').val(), jQuery('#datum_id_val').val() );  reloadForm();  archief(archief_opt, 'archief');   } 
                        }; 

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
		
			<form method="post" action="<?php echo $etc_root.'functions/'.$functie_get.'/includes/ajax.php' ?>" id="uren_toevoegen">
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
							<?php }//einde competentie while ?>
	                </select>
	                <span id="aantal_uur_container"><label for="aantal_uur">Aantal uur</label><input type="text" id="aantal_uur" name="aantal_uur" class="textfield"  /></span>
	            </div>          
	            <input type="hidden" id="datum_id_val" name="datum_id" value="<?php echo $datum_id; ?>"/>
	            <input type="hidden" id="datum_val" name="datum" value="<?php echo $mooie_datum; ?>"/>
	            <input type="hidden" id="datum_gereed_val" name="gereed" value="0" />
	            <input type="submit" id="verstuur_uren" value="verstuur uren" class="button" />
	        </form>
		</div>			       
   	</div>
    <div id="archief">
        <div id="archief_titel">
        <?php
            $maand_nummer = strftime('%m'); $maand_naam = $maandnaam["$maand_nummer"];
            $jaar = strftime('%Y'); 
            
            //Vorige maand bepalen
            if ($maand_nummer == 1) {  $terug = 12;        $jaar_terug = $jaar-1;  }
            else{               $terug = $maand-1;  $jaar_terug = $jaar;    }
            //Volgende maand bepalen
            if ($maand_nummer == 12){  $verder = 1;        $jaar_verder = $jaar+1; }
            else{               $verder = $maand+1; $jaar_verder = $jaar;   } 
        ?>
            <a id="archief_terug" href="#" onclick="archief('<?php echo $terug.'_'.$jaar; ?>', 'archief')"> &laquo;&laquo; </a>
            <h2 id="datum_naam"> <?php echo strftime('%B').'&nbsp;'.$jaar; ?> </h2>
            <div id="archief_nu" style="display:none;"><?php echo $maand.'_'.$jaar; ?></div>
            <a id="archief_verder" href="#" onclick="archief('<?php echo $verder.'_'.$jaar; ?>', 'archief')"> &raquo;&raquo; </a>
        </div>
     <div id="archief_container">
        <div id="archief_headers">
        <div id="linker_kolom">
            <span id="klant_links">Klant</span>
            <span id="uur_links">Uur</span>
        </div>
        <div id="rechter_kolom">
            <span id="klant_rechts">Klant</span>
            <span id="uur_rechts">Uur</span>
        </div>
        </div>
    
        <div id="archief_rijen">
        <?php
            $what = "d.naam,  sum(b.aantal_uur) uren";
            $from = "urentool_datum a, urentool_registratie b, organisatie d";
            $where="a.id = b.datum_id   AND d.id = b.organisatie
                AND a.maand = $maand_nummer AND a.jaar = $jaar
                AND a.gebruiker = $login_id GROUP BY 1";
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result))
            {
                echo '<div class="archief_item">
                        <div class="bedrijfsnaam">'.$row['naam'].'</div>
                        <div class="aantal_uur">'.$row['uren'].'</div>
                    </div>';    
  }
?>

        </div>
    </div>
</div>
</div>