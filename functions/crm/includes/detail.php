<?php
 if($_POST['detail']){
     $detail = $_POST['detail'];
     $id = $_POST['id'];
 }else{
      $detail = $_GET['detail'];
      $id = $_GET['id']; 
 }

	if($detail == 'relatie'){
		
		//haal de persoonlijke gegevens op
		$what = 'id, geslacht, voornaam, achternaam,	
				 adres, postcode, plaats, land,
				 telefoonnummer, mobiel, email, website';
		$from =  'relaties'; $where = 'id = '.$id.' AND actief = 1';
			//echo "Relatie: SELECT $what FROM $from WHERE $where";
			$relatie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
			
		//haal de gegevens van de organisatie(s) op, waar de relatie bij hoort
		//en pak ook de functie die de relatie vervuld
		$what = 'b.id, b.naam AS bedrijfsnaam, b.telefoonnummer, b.email, b.website,
				 c.titel, c.omschrijving';
		$from = 'relatie_organisatie a 
				 LEFT JOIN organisatie AS b ON (b.id = a.organisatie)
				 LEFT JOIN functie AS c ON (c.id = a.functie)';
		$where = 'a.relatie = '.$id.' AND b.actief = 1';
			//echo "Organisatie: SELECT $what FROM $from WHERE $where  <br />";
			$functies = countRows($what, $from, $where);
			if($functies > 0){ $organisaties = sqlSelect($what, $from, $where); }
			

	}
	elseif($detail == 'organisatie'){
		
		//haal de bedrijfsgegevens op
		$what = 'a.id, a.naam AS bedrijfsnaam, a.omschrijving, a.kvk_nummer, a.btw_nummer,	
				 a.badres, a.bpostcode, a.bplaats, a.padres, a.ppostcode, a.pplaats, a.land,
				 a.telefoonnummer, a.faxnummer, a.email, a.website,
				 b.naam AS branche';
		$from =  'organisatie a LEFT JOIN branche AS b ON (b.id = a.branche_id)'; $where = 'a.id = '.$id.' AND a.actief = 1';
			$organisatie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
			
		//haal de gegevens van de relatie(s) op, die bij deze organisatie horen
		//en pak ook de functie die de relatie vervuld
		$what = 'b.id, b.voornaam, b.achternaam, b.telefoonnummer, b.mobiel, b.email,
				 c.titel, c.omschrijving';
		$from = 'relatie_organisatie a 
				 LEFT JOIN relaties AS b ON ( b.id = a.relatie )
				 LEFT JOIN functie AS c ON (c.id = a.functie)';
		$where = 'a.organisatie = '.$id.' AND b.actief = 1';
			$contactpersonen = countRows($what, $from, $where); 
			if($contactpersonen > 0){ $relaties = sqlSelect($what, $from, $where); }

	}
	//van waar moeten de bestanden en tags worden gehaald	
	$what_bestanden = 'id, bestand'; $what_tags = 'a.id, b.naam';
	$from_bestanden = $detail.'_bestand'; $from_tags = $detail.'_tag a, portal_tag b';
	$where_bestanden = $detail.' = '.$id.''; $where_tags = 'a.'.$detail.' = '.$id.' AND b.id = a.tag';
	//echo "Tag: SELECT $what_tags FROM $from_tags WHERE $where_tags <br />";
	//echo "Bestanden: SELECT $what_bestanden FROM $from_bestanden WHERE $where_bestanden  <br />";
		$aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden); $aantal_tags = countRows($what_tags, $from_tags, $where_tags);
		$result_bestanden = sqlSelect($what_bestanden, $from_bestanden, $where_bestanden); $result_tags = sqlSelect($what_tags, $from_tags ,$where_tags);
    
?>
<div id="crm_detail">
    <script>
        jQuery(function(){
            jQuery('#contactpersonen .contactpersoon_body').hover( 
				function(){jQuery(this).addClass('contactpersoon_hover');}, 
				function(){jQuery(this).removeClass('contactpersoon_hover'); }
            );
            jQuery('#contactpersonen .functie_edit img').on('click',function(){
                console.log(jQuery(this).attr('id').split('_')[1]);
            })
        });
    </script>
	<div id="detail_top">
		
	<?php if($detail == 'relatie'){?> 
		<h2><?php echo ucfirst($relatie['voornaam']).' '.ucfirst($relatie['achternaam']); ?></h2>
	<?php }elseif($detail == 'organisatie'){
		  	if($organisatie['website'] != null){ echo '<a href="'.$organisatie['website'].'" target="_blank">'; }?>
		<h2><?php echo $organisatie['bedrijfsnaam']; ?></h2> 
	<?php 	if($organisatie['website'] != null){ echo '</a>'; }
		  } ?>
		  
	<?php if($detail == 'relatie'){?> 
		<a href="/crm/relatie-wijzigen/relatie-id=<?php echo $id ?>">
			<span onmouseout="this.className = 'button'" onmouseover="this.className = 'button btn_hover'" class="button" id="detail_wijzigen">wijzigen</span>
		</a>
	<?php }elseif($detail == 'organisatie'){?>
		<a href="/crm/organisatie-wijzigen/organisatie-id=<?php echo $id ?>">
			<span onmouseout="this.className = 'button'" onmouseover="this.className = 'button btn_hover'" class="button" id="detail_wijzigen">wijzigen</span>
		</a>
	<?php }?>

	</div>
	<div id="detail_links">
		<div id="organisatie_deel">
		<?php if($detail == 'relatie'){?>
			
			<div id="organisatie_deel_header"><h2>Functie(s)</h2></div>
			<?php if($functies == 0 ){?>
            <span id="contactpersoon_toevoegen" class="button">Functie toevoegen</span>
            <?php }else{ ?>
			<div id="relatie_functies">
				<div id="relatie_functie_header">
					<div class="functie_bedrijf header">Bedrijfsnaam</div>
					<div class="functie_email header">E-mailadres</div>
					<div class="functie_telefoon header">Telefoonnummer</div>
					<div class="functie_titel header">Functie</div>
				</div>		
			<?php
				/*** 
				* we kijken naar een relatie.
				* een relatie kan bij meerdere organisatie een rol vervullen.
				* per organisatie (/rol) een rij. We hebben dan alleen :
				* bedrijfsnaam, email, telefoon, website, functie, (-omschrijving) 
				* wanneer niet bekend, staat er niets.
				***/ 
				
				while($organisatie = mysql_fetch_array($organisaties)){?>	
					<div class="relatie_functie_body">
						<div class="functie_bedrijf">
							<a href="/crm/detail/organisatie-id=<?php echo $organisatie['id']; ?>">
								<?php  echo '<span>'.$organisatie['bedrijfsnaam'].'</span>';?>
							</a>
						</div>
						<div class="functie_email">
						<?php
							if($organisatie['email'] != null){ echo '<a href="mailto: '.$organisatie['email'].'" target="_blank">'.$organisatie['email'].'</a>'; }
							else{ echo '<span>&nbsp;</span>';	}
						?>	
						</div>
						<div class="functie_telefoon">
						<?php
							if($organisatie['telefoon'] != null){ echo '<span>'.$organisatie['telefoonnummer'].'</span>'; }
							else{ echo '<span>&nbsp;</span>';	}
						?>	
						</div>
						<div class="functie_titel">
						<?php
							if($organisatie['titel'] != null){
								 if($organisatie['omschrijving']){$titel = ' title="'.$organisatie['omschrijving'].'" class="titel_omschrijving"';}	
								 echo '<span'.$titel.'>'.$organisatie['titel'].'</span>'; 
							}
							else{ echo '<span>&nbsp;</span>';	}
						?>	
						</div>
					</div>
				<?php }
				}?>
				</div>
			<?php }
			elseif($detail == 'organisatie'){?>
			<div id="organisatie_deel_header"><h2>Organisatiegegevens</h2></div>
			<div id="organisatie_info">
				<div id="bedrijfsinfo">
					<div id="branche" class="info_row">
						<label>Branche:</label>
						<span class="info_item"><?php echo $organisatie['branche']; ?>&nbsp;</span>
					</div>
					<div id="kvk" class="info_row">
						<label>KVK nummer:</label>
						<span class="info_item"><?php if($organisatie['kvk_nummer'] != 0){echo $organisatie['kvk_nummer'];} ?>&nbsp;</span>
					</div>
					<div id="btw" class="info_row">
						<label>BTW nummer:</label>
						<span class="info_item"><?php if($organisatie['btw_nummer'] != 0){echo $organisatie['btw_nummer'];} ?>&nbsp;</span>
					</div>
					<div id="beschrijving" class="info_row">
						<label>Beschrijving:</label>
						<span class="info_item"><?php echo $organisatie['beschrijving']; ?>&nbsp;</span>
					</div>
				</div>
				<div id="bedrijf_adres">
					<div id="bezoek_adres_container" class="address_row">
						<p>Bezoekadres</p>
						<?php
						$landen = array( 'nederland', 'Nederland', 'nl', 'NL', 'nld', 'NLD' );
							if($organisatie['badres'] != null){ echo '<span id="bezoek_adres">'.$organisatie['badres'].'</span>'; }
							if($organisatie['bpostcode'] != null){ echo '<span id="bezoek_postcode">'.$organisatie['bpostcode'].'</span>'; }
							if($organisatie['bplaats'] != null){ echo '<span id="bezoek_plaats">'.$organisatie['bplaats'].'</span>'; }
							if($organisatie['land'] != null && !in_array($organisatie['land'], $landen)){ echo '<span id="land">'.$organisatie['land'].'</span>'; }
						?>
					</div>
					<div id="post_adres_container" class="address_row">
						<p>Postadres</p>
						<?php
							if($organisatie['padres'] != null){ echo '<span id="post_adres">'.$organisatie['padres'].'</span>'; }
							if($organisatie['ppostcode'] != null){ echo '<span id="post_postcode">'.$organisatie['ppostcode'].'</span>'; }
							if($organisatie['pplaats'] != null){ echo '<span id="post_plaats">'.$organisatie['pplaats'].'</span>'; }
							if($organisatie['land'] != null && !in_array($organisatie['land'], $landen)){ echo '<span id="land">'.$organisatie['land'].'</span>'; }
						?>
					</div>
				</div>
				<div id="bedrijf_contact">
					<div id="email" class="info_row">
						<label>E-mailadres:</label>
						<span class="info_item"><?php echo $organisatie['email']; ?>&nbsp;</span>
					</div>
					<div id="telefoon" class="info_row">
						<label>Telefoonnummer:</label>
						<span class="info_item"><?php echo $organisatie['telefoonnummer']; ?>&nbsp;</span>
					</div>
					<div id="fax" class="info_row">
						<label>Faxnummer:</label>
						<span class="info_item"><?php echo $organisatie['faxnummer']; ?>&nbsp;</span>
					</div>
				</div>
			</div>	
			<?php 
			}?>
		</div>
		<div id="relatie_deel">
			<?php
			if($detail == 'relatie'){?>
			<div id="relatie_deel_header"><h2>Persoonsgegevens</h2></div>
			<div id="persoonlijke_info">
				<div id="persoonssinfo">
					<div id="geslacht" class="info_row">
						<label>Aanspreektitel:</label>
						<span class="info_item"><?php echo $relatie['geslacht']; ?>&nbsp;</span>
					</div>
					<div id="naam" class="info_row">
						<label>Naam:</label>
						<span class="info_item"><?php echo $relatie['voornaam'].' '.$relatie['achternaam']; ?></span>
					</div>
				</div>
				<div id="persoon_adres">
					<div id="adres_container" class="address_row">
					    <p>Adres</p>
						<?php
						$landen = array( 'nederland', 'Nederland', 'nl', 'NL', 'nld', 'NLD' );
							if($relatie['adres'] != null){ echo '<span id="adres">'.$relatie['adres'].'</span>'; }
							if($relatie['postcode'] != null){ echo '<span id="postcode">'.$relatie['postcode'].'</span>'; }
							if($relatie['plaats'] != null){ echo '<span id="plaats">'.$relatie['plaats'].'</span>'; }
							if($relatie['land'] != null && !in_array($relatie['land'], $landen)){ echo '<span id="land">'.$relatie['land'].'</span>'; }
						?>
					</div>
				</div>
				<div id="persoonscontact">
					<div id="email" class="info_row">
						<label>E-mailadres:</label>
						<span class="info_item"><?php echo $relatie['email']; ?>&nbsp;</span>
					</div>
					<div id="mobiel" class="info_row">
						<label>Mobiel:</label>
						<span class="info_item"><?php echo $relatie['mobiel']; ?>&nbsp;</span>
					</div>
					<div id="telefoon" class="info_row">
						<label>Telefoonnummer:</label>
						<span class="info_item"><?php echo $relatie['telefoonnummer']; ?>&nbsp;</span>
					</div>
					<div id="website" class="info_row">
						<label>Website:</label>
						<span class="info_item"><?php if($relatie['website'] != null){ echo '<a href="'.$relatie['website'].'" target="_blank">Website</a>'; } ?></span>
					</div>
				</div>
			</div>	
			<?php 
			}elseif($detail == 'organisatie'){?>
			<div id="relatie_deel_header">
				<h2>Contactpersonen</h2>
				<a href="/crm/relatie-toevoegen/organisatie-id=<?php echo $organisatie['id']; ?>"> 
					<span onmouseout="this.className = 'button'" onmouseover="this.className = 'button btn_hover'" class="button" id="detail_contactpersoon_toevoegen">Contactpersoon toevoegen</span>
				</a>
			</div>
			
			<div id="contactpersonen">
				<div class="relatie_functie_header">
					<div class="functie_bedrijf header">Naam</div>
					<div class="functie_email header">E-mailadres</div>
					<div class="functie_telefoon header">Telefoonnummer</div>
					<!--<div class="functie_mobiel_header">Mobiel</div>-->
					<div class="functie_titel header">Functie</div>
				</div>			
			<?php
				/*** 
				* we kijken naar een relatie.
				* een relatie kan bij meerdere organisatie een rol vervullen.
				* per organisatie (/rol) een rij. We hebben dan alleen :
				* bedrijfsnaam, email, telefoon, website, functie, (-omschrijving) 
				* wanneer niet bekend, staat er niets.
				***/ 
				if($contactpersonen > 0){
				while($relatie = mysql_fetch_array($relaties)){?>				
						<div class="contactpersoon_body">
							
							<div class="functie_naam">
								<a href="/crm/detail/relatie-id=<?php echo $relatie['id'] ?>">
									<?php echo $relatie['voornaam'].' '.$relatie['achternaam']; ?>
								</a>
							</div>
							<div class="functie_email">
							<?php  echo '<a href="mailto: '.$relatie['email'].'" target="_blank">'.$relatie['email'].'</a>'; ?>	
							</div>
							<div class="functie_telefoon">
							<?php
								if($relatie['telefoonnummer'] != null){ echo '<span>'.$relatie['telefoonnummer'].'</span>'; }
								else{ echo '<span>&nbsp;</span>';	}
							?>	
							</div>
							<!--<div class="functie_mobiel">
							<?php
								if($relatie['mobiel'] != null){ echo '<span>'.$relatie['mobiel'].'</span>'; }
								else{ echo '<span>&nbsp;</span>';	}
							?>	
							</div>-->
							<div class="functie_titel">
							<?php
								if($relatie['titel'] != null){
									 if($relatie['omschrijving']){$titel = ' title="'.$relatie['omschrijving'].'" ';}	
									 echo '<span'.$titel.'>'.$relatie['titel'].'</span>'; 
								}
								else{ echo '<span>&nbsp;</span>';	}
							?>	
							</div>
							<div class="functie_edit">
							   <a href="/crm/relatie-wijzigen/relatie-id=<?php echo $relatie['id'] ?>">
							    <img src="<?php echo $etc_root; ?>functions/crm/css/images/edit.png" alt="edit" id="edit_<?php echo $relatie['id']; ?>" />
							    &nbsp;
							   </a>
							</div>
						</div>
					<?php }  // einde contactpersonen while
					} // einde contactpersonen if
					else{ echo 'Er zijn nog geen contactpersonen geregistreerd. Klik op \'Contactpersoon toevoegen\' om er een toe te voegen'; 	}?>
	        </div>
		
			<?php } ?>
		</div>
	</div>
	<div id="detail_rechts">
	    &nbsp;
		<!--<div id="detail_tags">
                <div id="detail_tags_header">
                    <h3>Tags</h3>
                    <button id="add_detail_tag" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Tag toevoegen</button>
                </div>
                <div id="detail_tags_form" style="display:none">
                	<label class="label" for="add_detail_tag_input">Tag</label>
        			<input type="text" id="add_detail_tag_input" class="textfield" />
                </div>
                <div id="detail_tags_lijst">
                    <?php
                        if($aantal_tags > 0){
                            while($row = mysql_fetch_array($result_tags)){
                                $tag_list[] = 
                                '<div class="tag">
                                    <a class="tag_zoeken" href="?function=-zoek&query='.$row['naam'].'" target="_blank" title="zoek naar tag">'.$row['naam'].'</a>
                                    <span class="delete_tag" onmouseover="this.className=\'delete_tag delete_tag_hover\'" onmouseout="this.className=\'delete_tag\'" onclick="deletetag('.$row['id'].')"> X </span>
                                </div>';
                            }
                        }
                        if(count($tag_list) > 0){
                            foreach($tag_list as $tag){
                                echo $tag;
                            }
                        }else{
                            echo 'Nog geen tags';
                        }
                    ?>
                </div>
            </div>
            <div id="detail_bestanden">
                <div id="detail_tags_header">
                    <h3>Bestanden</h3> 
                    <button id="add_detail_file" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Bestand toevoegen</button>
                </div>
                <div id="detail_bestanden_form" style="display:none;">
                	<div id="status-message"></div>
        			<ul id="custom-queue"></ul>
        			<div id="upload_file"></div>
        			<div class="qq-upload-extra-drop-area">Drop files here too</div>
                </div>
                <div id="detail_bestanden_lijst">
                    <?php
                        if($aantal_bestanden > 0){
                            while($row = mysql_fetch_array($result_bestanden)){
                                echo '
                                <div class="bestand" onmouseover="this.className='."'bestand_hover'".'" onmouseout="this.className='."'bestand'".'">
                                    <a class="bestand_link" href="/files/planning_documenten/'.$activiteit_id.'/'.$row['bestand'].'" target="_blank" title="bekijk bestand">'.$row['bestand'].'</a>
                                    <span class="verwijder_bestand" onclick="deleteFile('.$row['id'].')">verwijderen</span>
                                </div>';
                            }
                        }else{
                            echo 'Nog geen bestanden';
                        }
                    ?>
                </div>
            </div>-->
	</div>
	<div id="detail_bottom">
		<div id="bottom_left"></div>
		<div id="bottom_rihgt"></div>
		<div id="bottom_left"></div>
	</div>
</div>
