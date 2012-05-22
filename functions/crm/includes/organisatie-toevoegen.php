<?php
/*
* Deze php heeft als functie om de crm gegevens te wijzigen/aan te maken.
* Dit is het formulier om een organisatie te wijzigen/aan te maken.
* Voor de check wordt gebruik gemaakt van 'crm_check.php'.
* Als de check klaar is, komen ze hier terug en kunnen ze het window sluiten dmv een 'terug naar lijst'knop
* 
* Copyright qern internet professionals 2010-2011
* Mail: b.slob(at)qern(dot)nl
*/
if($_SESSION['id'] != null ||  ($_POST['id'] != null && $_POST['detail'] == 'organisatie')){
	
if($_SESSION['id']){$relatie_id = $_SESSION['id'];}
elseif($_POST['id']){$relatie_id = $_POST['id'];}
$organisatie_id = $_POST['id'];
$what = "       id,
                naam,
                omschrijving,
                branche_id,
                kvk_nummer,
                btw_nummer,
                badres,
                bpostcode,
                bplaats,
                padres,
                ppostcode,
                pplaats,
                land,
                telefoonnummer,
                faxnummer,
                email       organisatie_email,
                faxnummer,
                website,
                contactpersoon_id,
                status";
$from="         organisatie ";
$where="        actief = 1
                AND id = $organisatie_id";
$row = mysql_fetch_assoc(sqlSelect($what,$from,$where));
/*
        $row['naam'] = $_SESSION['naam'];             $row['omschrijving'] = $_SESSION['omschrijving'];
        $row['branche'] = $_SESSION['branche'];       $row['website'] = $_SESSION['website'];
        $row['kvk_nummer'] = $_SESSION['kvk_nummer']; $row['btw_nummer'] = $_SESSION['btw_nummer'];
        $row['badres'] = $_SESSION['badres'];         $row['bpostcode'] = $_SESSION['bpostcode'];
        $row['bplaats'] = $_SESSION['bplaats'];       $row['padres'] = $_SESSION['padres']; 
        $row['ppostcode'] = $_SESSION['ppostcode'];   $row['pplaats'] = $_SESSION['pplaats']; 
        $row['faxnummer'] = $_SESSION['faxnummer'];   $row['land'] = $_SESSION['land']; 
        $row['email'] = $_SESSION['email'];           $row['telefoonnummer'] = $_SESSION['telefoonnummer'];
        unset($_SESSION['naam'], $_SESSION['omschrijving'], $_SESSION['branche'], $_SESSION['website'], $_SESSION['btw_nummer'], $_SESSION['bpostcode'], $_SESSION['telefoonnummer'], $_SESSION['faxnummer'], $_SESSION['land'], $_SESSION['email'], $_SESSION['pplaats'], $_SESSION['ppostcode'], $_SESSION['padres'], $_SESSION['bplaats'], $_SESSION['bpostcode'],  $_SESSION['bpostcode'], $_SESSION['badres'], $_SESSION['kvk_nummer']);
        * 
        */
//nu pakken we het relatie_id van deze klant. Aan de hand hiervan gaan we de opmerkingen laden.
//ALLE OVERIGE SQL-QUERIES HIER!    
    
    //moet de ingelogde gebruiker gewaarschuwd worden bij deze klant, bij wijzigingen ?
    $what="id";  $from="waarschuw_mij";   $where="organisatie_id = $organisatie_id AND gebruiker_id=$login_id";
        $waarschuw_mij = countRows($what,$from,$where);

//EINDE SQL-QUERIES 
}
//alle branches moeten worden ingeladen en later geloopt worden.
$branche_what="naam, id"; $branche_from="branche"; $branche_where="actief = 1 ";
$aantal_branches = countRows($branche_what,$branche_from,$branche_where);
if($aantal_branches > 0) { $branche_result = sqlSelect($branche_what,$branche_from,$branche_where); }
?>
<script>

jQuery(function(){
    
    jQuery("#organisatie_formulier").bind("invalid-form.validate",function(){ jQuery("#error_titel").html("Er is een fout opgetreden:");})
    .validate({
        errorContainer:$("#error_titel, #fouttekst"),
        errorLabelContainer:"#errors",
        wrapper:"li",
        errorElement:"span", 
        rules:{naam:"required", branche:"required", email:{email:true}, website:{url:true}},
        messages:{naam:"Vul een bedrijfsnaam in.", branche:"Kies een branche. Bestaat deze nog niet: voeg deze toe door op het plusje te klikken!", email:{email: "Vul een geldig e-mailadres in. Zoals <b>voorbeeld@voorbeeld.nl</b>"}, website: {url:"Vul een geldig webadres in. Zoals bijvorbeeld: http://nu.nl"}}
    });
    jQuery("label").inFieldLabels();
    jQuery('#addbranche').on('click', function(){ jQuery('#dialog').load('/functions/crm/includes/branche.php').dialog({ minWidth: 200, minHeight: 100, height:120, title: 'Branche toevoegen' });    });
    
});

function reloadBranches(){
    jQuery.ajax({
        type : 'GET',
        url : '/functions/crm/includes/branche.php',
        dataType : 'html',
        data: {action: 'reloadBranches'},
        success : function(data){ 
            jQuery('#branche_select').html(data);
        }
    });return false;
}
</script>
<div id="subprofiel">
    <?php if($_POST['id'] != null){ ?>
        <h2 id="subprofiel_titel">Organisatie wijzigen</h2>
    <?php }else{?>
        <h2 id="subprofiel_titel">Organisatie toevoegen</h2>
    <?php } ?>
    <form method="post" action="/functions/crm/includes/organisatie-check.php" id="organisatie_formulier">
        
        <div id="bedrijfsgegevens_wijzigen">
          <div id="wijzigen_organisatie">
            <div class="input_container" id="branche_select">
                <select class="branche_select textfield" name="branche" id="branche">
                    <option value>Branche</option>
<?php
if(isset($branche_result)){
	while($branche = mysql_fetch_array($branche_result)){
		if($branche['id'] == $row['branche_id']){echo '<option value="'.$branche['id'].'" selected="selected">'.$branche['naam'].'</option>';}
		else{echo '<option value="'.$branche['id'].'">'.$branche['naam'].'</option>';}
	}
}
?>
             
                </select>
                <img id="addbranche" title="Voeg een branche toe" alt="addbranche" src="<?php echo $etc_root ?>functions/crm/css/images/add.png" />
             </div>    
             <div class="input_container">
                 <label for="naam" class="tooltip" title="Vul hier de bedrijfsnaam in">Bedrijfsnaam:</label>
                 <input id="naam" tabindex="1" class="naam_input textfield" type="text" name="naam" value="<?php echo $row['naam']; ?>" />
             </div> 
             <div class="input_container">
                <label for="kvk" class="tooltip" title="Vul hier het kamer van koophandelnummer in">kvknummer:</label>
                <input id="kvk" tabindex="2" class="kvk_input textfield" type="text" name="kvk_nummer" value="<?php if($row['kvk_nummer'] != 0){ echo $row['kvk_nummer'] ; }?>" />
             </div>
             <div class="input_container">
                <label for="btw" class="tooltip" title="Vul hier het BTW-nummer in">btwnummer:</label> 
                <input id="btw" tabindex="3" class="btw_input textfield" type="text" name="btw_nummer" value="<?php if($row['btw_nummer'] != 0){ echo $row['btw_nummer'] ; }?>"/>
            </div>          
            <div class="input_container">
                 <label for="bedrijf_omschrijving" class="tooltip" title="beschrijf hier de functie">bedrijfsomschrijving:</label> 
                <textarea id="bedrijf_omschrijving" tabindex="4" class="textarea bedrijf_omschrijving_input" cols="25" rows="3" name="omschrijving"><?php echo$row['omschrijving'] ; ?></textarea>
            </div>
          </div>
          <div id="wijzigen_org_adres">
            <div class="input_container">
                <label for="badres" class="tooltip" title="Vul hier de straat en huisnummer van het bezoekadres in">bezoekadres:</label> 
                <input id="badres"tabindex="5" class="straat_input textfield" type="text" name="badres" value="<?php echo $row['badres'] ; ?>" />
            </div> 
            <div class="input_container">
                <label for="bpostcode" class="tooltip" title="Vul hier de postcode van het bezoekadres in">postcode:</label> 
                <input id="bpostcode" tabindex="6" class="nummer_input textfield" type="text" name="bpostcode" value="<?php echo $row['bpostcode'] ; ?>" />
            </div>
            <div class="input_container">
                <label for="bplaats" class="tooltip" title="Vul hier de plaats van het bezoekadres in">plaats:</label> 
                <input id="bplaats" tabindex="7" class="postcode_input textfield" type="text" name="bplaats" value="<?php echo $row['bplaats'] ; ?>" />
            </div>  
            <div class="input_container">
                <label for="padres" class="tooltip" title="Vul hier de straat en huisnummer of de postbus van het postadres in">postadres:</label> 
                <input id="padres" tabindex="8" class="plaats_input textfield" type="text" name="padres" value="<?php echo $row['padres'] ; ?>" />
            </div>
            <div class="input_container">
                <label for="ppostcode" class="tooltip" title="Vul hier de postcode van het postadres in">postcode:</label> 
                <input id="ppostcode" tabindex="9" class="nummer_input textfield" type="text" name="ppostcode" value="<?php echo $row['ppostcode'] ; ?>" />
            </div>
            <div class="input_container">
                <label for="pplaats" class="tooltip" title="Vul hier de plaats van het postadres in">plaats:</label> 
                <input id="pplaats" tabindex="10" class="postcode_input textfield" type="text" name="pplaats" value="<?php echo $row['pplaats'] ; ?>" />
            </div> 
            <div class="input_container">
                <label for="land" class="tooltip" title="Vul hier het land in">land:</label> 
                <input id="land" tabindex="11" class="land_input textfield" type="text" name="land"  value="<?php echo $row['land'] ; ?>" />
            </div>
          </div>
          <div id="wijzigen_org_contact">
            <div class="input_container">
                <label for="email" class="tooltip" title="Vul hier het e-mailadres in">e-mail:</label> 
                <input tabindex="13" class="email_input textfield" type="text" name="email" id="email" value="<?php echo $row['organisatie_email'] ; ?>" />
            </div> 
            <div class="input_container">
                <label for="tel" class="tooltip" title="Vul hier het telefoonnummer in">telefoonnummer:</label> 
                <input id="tel" tabindex="14" class="tel_input textfield" type="text" name="telefoonnummer" value="<?php echo $row['telefoonnummer'] ; ?>" /> 
            </div> 
            <div class="input_container">
                <label for="fax" class="tooltip" title="Vul hier het faxnummer in">fax:</label> 
                <input id="fax" tabindex="15" class="fax_input textfield" type="text" name="faxnummer" value="<?php echo $row['faxnummer'] ; ?>" /> 
            </div> 
            <div class="input_container">
                <label for="website" class="tooltip" title="Vul hier de website URL in">website:</label> 
                <input id="website" tabindex="16" class="website_input textfield" type="text" name="website" value="<?php echo $row['website'] ; ?>" />
            </div>
          </div>
          
<?php         
if($_POST['id'] == null){echo '<input type="hidden" name="actie" value="aanmaken" />'; }
elseif($_POST['id'] != null){echo '<input type="hidden" name="organisatie_id" value="'.$organisatie_id.'" /> <input type="hidden" name="actie" value="wijzigen" />';}
?>
          <div id="opslaan_container">
            <input type="submit" tabindex="18" value="Opslaan" id="opslaan" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'"  class="button" />
          </div>
        </div>
        
        <div id="error_container">
            <h2 id="error_titel"><?php if($_SESSION['organisatie_error']){ echo 'Er is een fout opgetreden:'; } ?>&nbsp;</h2>
            <ul id="errors">
            <?php 
                if($_SESSION['organisatie_error']){
                    foreach($_SESSION['organisatie_error'] AS $error){
                        echo '<li><span>'.$error.'</span></li>';
                    } 
                }
                unset($_SESSION['organisatie_error']);
            ?>
            </ul>
        </div>
        
    </form> 
</div>
<div id="dialog"></div>
