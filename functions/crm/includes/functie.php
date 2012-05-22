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