<?php
/*
* Deze php heeft als functie om de crm gegevens te wijzigen/aan te maken.
* Dit is het formulier om een relatie te wijzigen/aan te maken.
* Voor de check wordt gebruik gemaakt van 'relatie_check.php'.
* Als de check klaar is, komen ze hier terug om de wijzigingen te zien.
* 
* Copyright qern internet professionals 2010-2011
* Mail: b.slob(at)qern(dot)nl
*/
if($_SESSION['id'] != null ||  ($_POST['id'] != null && $_POST['detail'] == 'relatie')){
	
if($_SESSION['id']){$relatie_id = $_SESSION['id'];}
elseif($_POST['id']){$relatie_id = $_POST['id'];}

$what = "       a.id,
                a.voornaam,
                a.achternaam,
                a.adres,
                a.postcode,
                a.plaats,
                a.land,
                a.telefoonnummer,
                a.mobiel,
                a.email         relatie_email,
                a.website,
                a.geslacht,
                c.id            organisatie_id,
                c.naam          organisatie_naam,
                d.id            functie_id,
                d.titel         functie_titel,
                d.omschrijving  functie_omschrijving";
$from="         relaties a              
                LEFT JOIN relatie_organisatie AS b ON (b.relatie = a.id)
                LEFT JOIN organisatie AS c ON(c.id = b.organisatie)
                LEFT JOIN functie AS d ON (d.id = b.functie)";
$where="        a.id = $relatie_id ";
$row = mysql_fetch_assoc(sqlSelect($what,$from,$where));
//echo "SELECT $what FROM $from WHERE $where";
//laadt alle mogelijke info in.

   
//nu pakken we het relatie_id van deze klant. Aan de hand hiervan gaan we de opmerkingen laden.
//ALLE OVERIGE SQL-QUERIES HIER!      
    $what="id";
    $from="waarschuw_mij";
    $where="relatie_id = $relatie_id AND gebruiker_id=$login_id";
    $waarschuw_mij = countRows($what,$from,$where);

    
    
//EINDE SQL-QUERIES 
}else{
    
    //kom je van een organisatie vandaan en heb je een organisatie_id meegenomen ?
    if($_GET['organisatie_id'] != null){
        $what = 'naam AS organisatie_naam'; $from = 'organisatie'; $where= 'id = '.$_GET['organisatie_id'];
            $row = mysql_fetch_assoc(sqlSelect($what,$from,$where));
    }
}
/*
    $row['voornaam'] = $_SESSION['voornaam'];             $row['achternaam'] = $_SESSION['achternaam'];
    $row['geslacht'] = $_SESSION['geslacht'];             $row['website'] = $_SESSION['website'];
    $row['adres'] = $_SESSION['adres'];                   $row['postcode'] = $_SESSION['postcode'];
    $row['plaats'] = $_SESSION['plaats'];                 $row['land'] = $_SESSION['land']; 
    $row['mobiel'] = $_SESSION['mobiel'];                 $row['telefoonnummer'] = $_SESSION['telefoonnummer'];
    $row['email'] = $_SESSION['email'];                   $row['organisatie_naam'] = $_SESSION['bedrijf'];
    $row['functie_titel'] = $_SESSION['functie_titel'];   $row['functie_omschrijving'] = $_SESSION['functie_omschrijving'];
    
    
    unset(
    $_SESSION['voornaam'], $_SESSION['achternaam'], $_SESSION['branche'],
    $_SESSION['adres'], $_SESSION['postcode'], $_SESSION['plaats'], 
    $_SESSION['land'], $_SESSION['telefoonnummer'], $_SESSION['mobiel'], 
    $_SESSION['website'], $_SESSION['email'], $_SESSION['bedrijf'],$_SESSION['functie_titel'], 
    $_SESSION['functie_omschrijving']
    );
    * */
?>
<script>
    
jQuery(function(){
    
    jQuery("#relatie_formulier").bind("invalid-form.validate",function(){ jQuery("#error_titel").html("Er is een fout opgetreden:");})
    .validate({
        errorContainer:$("#error_titel, #fouttekst"),
        errorLabelContainer:"#errors",
        wrapper:"li",
        errorElement:"span", 
        rules:{voornaam: "required", bedrijfsnaam:{required: true, minlength: 2, remote: "/functions/crm/includes/bedrijf.php" }, email:{email:true}, website:{url:true}},
        messages:{
			voornaam: "U dient een voornaam in te geven", 
			bedrijfsnaam:{
				required:"Vul een bedrijfsnaam in. Als het bedrijf nog niet bestaat, maak deze dan eerst aan alstublieft", 
				minlength:"Een bedrijf bestaat uit minstens 2 tekens",
				remote: "De door u opgegeven organisatie bestaat (nog) niet. Kies een ander, of maak de gewenste organisatie aan."
			},
			email:{email: "Vul een geldig e-mailadres in. Zoals <b>voorbeeld@voorbeeld.nl</b>"}, 
			website: {url:"Vul een geldig webadres in. Zoals bijvorbeeld: http://nu.nl"}}
    });
    jQuery("label").inFieldLabels();
    jQuery("#bedrijfsnaam").autocomplete({source: "/functions/crm/includes/autocomplete_bedrijf.php",minLength:2});
    
});
    
</script>
<div id="subprofiel">
	<?php 
	if($_POST['id'] != null){
			if($_POST['detail'] == 'relatie'){?>
		<h2 id="subprofiel_titel">Relatie wijzigen</h2>
	<?php }elseif($_POST['detail'] == 'organisatie'){?>
		<h2 id="subprofiel_titel">Contactpersoon toevoegen</h2>
	<?php }
	}else{?>
		<h2 id="subprofiel_titel">Relatie toevoegen</h2>
	<?php }	?>
    <form method="post" action="/functions/crm/includes/relatie-check.php" id="relatie_formulier">
                
        <div id="persoonsgegevens_wijzigen">
        	<h3>Persoonlijke gegevens</h3>
    		<div id="wijzigen_persoon">
	            <div class="input_container">
	            	<select tabindex="-1" class="aanhef_select textfield" name="geslacht">
	<?php if($row['geslacht'] == 'de heer'){$selected_aanhef_dhr = 'selected="selected"';}
	elseif($row['geslacht'] == 'mevrouw'){$selected_aanhef_mvr = 'selected="selected"';}
	elseif($row['geslacht'] == 'onbekend'){$selected_aanhef_onbekend = 'selected="selected"';}
	else{$selected_aanhef_onbekend = 'selected="selected"';}
	?>
	        			<option value>Aanhef</option>
	        			<option value="onbekend"<?php echo $selected_aanhef_onbekend?>>Onbekend</option>
	             		<option value="de heer"<?php echo $selected_aanhef_dhr?>>de heer</option>
	             		<option value="mevrouw"<?php echo $selected_aanhef_mvr?>>mevrouw</option>
	            	</select>
	            </div>    
	            <div class="input_container">
	            	<label for="voornaam" class="tooltip label" title="Vul hier de voornaam in">voornaam</label>
	                <input id="voornaam" tabindex="1" class="voornaam_input textfield" type="text" name="voornaam" value="<?php echo $row['voornaam']; ?>" />
	           </div> 
	           <div class="input_container">
	           		<label for="achternaam" class="tooltip label" title="Vul hier de achternaam in">achternaam</label> 
	                <input id="achternaam" tabindex="2" class="achternaam_input textfield" type="text" name="achternaam" value="<?php echo $row['achternaam']; ?>" />
	           </div>
          	</div>
          	<div id="wijzigen_adres">
	             <div class="input_container">
	             	<label for="adres" class="tooltip label" title="Vul hier uw adres in. Dit bestaat uit een straat + huisnummer met eventuele toevoegingen">adres</label> 
	                <input id="adres" tabindex="6" class="straat_input textfield" type="text" name="adres" value="<?php echo $row['adres']; ?>" />
	             </div> 
	             <div class="input_container">
	             	<label for="postcode" class="tooltip label" title="Vul hier de postcode in">postcode</label> 
	                <input id="postcode" tabindex="7" class="postcode_input textfield" type="text" name="postcode" value="<?php echo $row['postcode']; ?>" />
                </div>  
	            <div class="input_container">
	            	<label for="plaats" class="tooltip label" title="Vul hier de woonplaats in">plaats</label> 
	                <input id="plaats" tabindex="8" class="plaats_input textfield" type="text" name="plaats" value="<?php echo $row['plaats']; ?>" />
                </div>
	             <div class="input_container">
	             	<label for="land" class="tooltip label" title="Vul hier het land in">land</label> 
	                <input id="land" tabindex="9" class="land_input textfield" type="text" name="land"  value="<?php echo $row['land']; ?>" />
                </div>
          </div>
          <div id="wijzigen_contact">
             <div class="input_container">
             	<label for="email"class="tooltip label" title="Vul hier het e-mailadres in">e-mail</label> 
                <input tabindex="11" class="email_input textfield" type="text" name="email" id="email" value="<?php echo $row['relatie_email']; ?>" /></div> 
             <div class="input_container">
             	<label for="tel" class="tooltip label" title="Vul hier het telefoonnummer in">telefoonnummer</label> 
                <input id="tel" tabindex="12" class="tel_input textfield" type="text" name="telefoonnummer" value="<?php echo $row['telefoonnummer']; ?>" /> </div> 
             <div class="input_container">
             	<label for="mobiel" class="tooltip label" title="Vul hier het mobiele telefoonnummer in">mobiel</label> 
                <input id="mobiel"  tabindex="13" class="mobiel_input textfield" type="text" name="mobiel" value="<?php echo $row['mobiel']; ?>" /></div>
             <div class="input_container">
             	<label for="website"  class="tooltip label" title="Vul hier de website URL in">website</label> 
                <input id="website"  tabindex="14" class="website_input textfield" type="text" name="website" value="<?php echo $row['website']; ?>" /></div>
          </div>
          
          <?php if($_POST['id'] != null && $_POST['detail'] == 'relatie'){?> <input type="hidden" name="relatie_id" value="<?php echo $relatie_id; ?>" /> <?php } ?>
<?php          
if($_POST['id'] == null || ($_POST['id'] != null && $_POST['detail'] == 'organisatie')){echo '<input type="hidden" name="actie" value="aanmaken" />'; }     //als er geen id aanwezig is, maak dan een nieuwe relatie aan
elseif($_POST['id'] != null && $_POST['detail'] == 'relatie'){echo '<input type="hidden" name="actie" value="wijzigen" />';}  //als er wel een id aanwezig is, wijzig deze relatie dan
else{echo '<input type="hidden" name="actie" value="wijzigen" />'; }                        //default: wijzigen
?>        
        </div>
    	<div id="werkgegevens_wijzigen">
    		<h3>Organisatiegegevens</h3>
    		<div class="functie">
    			<div class="input_container">
					<?php 
						if($_POST['id'] != null && $_POST['detail'] == 'organisatie'){
							$what = 'naam'; $from = 'organisatie'; $where = 'id = '.$_POST['id'];
								$organisatie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
								$organisatie_naam = $organisatie['naam']; 
						}else{ $organisatie_naam = $row['organisatie_naam']; }
					?>
	           		<label for="bedrijfsnaam" class="tooltip label" title="Vul hier de bedrijfsnaam in">bedrijfsnaam</label> 
	                <input id="bedrijfsnaam" tabindex="3" class="bedrijfsnaam_input textfield" type="text" name="bedrijfsnaam" id="bedrijfsnaam" value="<?php echo $organisatie_naam; ?>"/>
	           </div>          
	           <div class="input_container">
	           		<label for="functie_titel" class="tooltip label" title="Vul hier de functie in">functie</label> 
	                <input id="functie_titel" tabindex="4" class="functie_titel_input textfield" type="text" name="functie_titel"  value="<?php echo $row['functie_titel']; ?>"/>
	           </div>
	           <div class="input_container">
	           		<label for="functie_omschrijving" class="tooltip label" title="beschrijf hier de functie">functie omschrijving</label> 
	                <textarea id="functie_omschrijving" tabindex="5" class="textarea functie_omschrijving_input" cols="30" rows="3" name="functie_omschrijving"><?php echo $row['functie_omschrijving']; ?></textarea>
	           </div>
	        </div>
	        
    	</div>
    	
    	<div id="opslaan_container">
            <input type="submit" tabindex="15" value="opslaan" id="opslaan" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'" class="button" />
          </div>
    	
    	<div id="error_container">
            <h2 id="error_titel"><?php if($_SESSION['organisatie_error']){ echo 'Er is een fout opgetreden:'; } ?></h2>
            <ul id="errors">
            <?php 
                if($_SESSION['relatie_error']){
                    foreach($_SESSION['relatie_error'] AS $error){
                        echo '<li><span>'.$error.'</span></li>';
                    } 
                }
                unset($_SESSION['relatie_error']);
            ?>
            </ul>
        </div>
        
    </form> 
    <div id="dialog"></div>
</div>
