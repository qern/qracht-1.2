<?php 
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
	
	if($_POST['action'] == 'nieuweCompetentie'){
		
		$table = 'portal_gebruiker_competentie'; $what = 'gebruiker, uren, competentie';
			$with_what = "$login_id, 0, ".$_POST['competentie'];
			
		$nieuwe_competentie = sqlInsert($table, $what, $with_what);
		echo "INSERT INTO $table($what) VALUES $with_what";
	}elseif($_POST['action'] == 'wijzigUren'){
		
		$table = 'portal_gebruiker_competentie'; $what = 'uren = '.$_POST['uren'];
			$where = "gebruiker = $login_id AND competentie = ".$_POST['competentie'];
			
		$update_uren = sqlUpdate($table, $what, $where);
		//echo "UPDATE $table SET $what WHERE $where";
	}elseif($_POST['action'] == 'verwijdeCompetentie'){
		$table = 'portal_gebruiker_competentie'; $where = 'competentie = '.$_POST['competentie'];
			
		$verwijder_competentie = sqlDelete($table, $where);
	}
	
	if($_GET['action'] == 'reloadForm'){
		//welke competenies zijn al bekend ?
		$what = 'competentie'; $from = 'portal_gebruiker_competentie'; $where= "gebruiker = $login_id";
			$reeds_bekende_competenties = sqlSelect($what, $from, $where);
			
		// haal alle competenties op
		$what = 'id, competentie'; $from = 'competentie'; $where = '1';
			$competenties = sqlSelect($what, $from, $where);
 		echo  '<option value="">Kies een competentie</option>';
		while($reeds_bekende_competentie = mysql_fetch_array($reeds_bekende_competenties)){ $bekende_competenties[] = $reeds_bekende_competentie['competentie']; }
			while($competentie = mysql_fetch_array($competenties)){
				if(!in_array($competentie['id'], $bekende_competenties)){?>
			<option value="<?php echo $competentie['id'] ?>"><?php echo $competentie['competentie'] ?></option>
		<?php 	}//einde if
			}//einde while
     }elseif($_GET['action'] == 'reloadCompetenties'){
     	// haal de competenties op, die bij de gebruiker horen. En hoeveel uur hij er voor heeft staan
		$what = 'a.uren, b.id, b.competentie';  $from = 'portal_gebruiker_competentie a LEFT JOIN competentie AS b ON (b.id = a.competentie)';
		$where = "a.gebruiker = $login_id";
			$competentie_uren = sqlSelect($what, $from, $where);
	 	while($competentie_uur = mysql_fetch_array($competentie_uren)){?>
            <div class="competentie_uur">
            	<div class="competentie"><?php echo $competentie_uur['competentie']; ?></div>
            	<div class="delete"><img id="delete_<?php echo $competentie_uur['id'] ?>" title="Verwijder competentie" alt="Verwijder kennis" src="/functions/account/css/images/delete.png" class="kennis_delete_img" style="display:none;" /></div>
            	<div class="uren"><input type="text" class="textfield" name="competentie_<?php echo $competentie_uur['id'] ?>" value="<?php echo $competentie_uur['uren']; ?>" /></div>
            </div>
        <?php }//einde while
     }//einde elseif
 ?>