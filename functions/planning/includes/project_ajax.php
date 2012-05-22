<?php 
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
	
	$what = 'id, titel'; $from =  'project'; $where = 'organisatie = '.$_GET['bedrijf'].' AND actief  = 1';
		$aantal_projecten = countRows($what, $from, $where);
		//echo "SELECT $what FROM $from WHERE $where";?>
	
	<?php if($aantal_projecten > 0){?>
	<select id="toevoegen_project" name="toevoegen_project"  class="textfield">
	<?php $projecten = sqlSelect($what, $from, $where); while($project = mysql_fetch_array($projecten)){?>
			<option value="<?php echo $project['id']; ?>"><?php echo $project['titel']; ?></option>
	<?php }//end while ?>
	   </select>
	<?php }else{?>
	    <select id="toevoegen_project" name="toevoegen_project" disabled="disabled" class="textfield">
	       <option value="">Voeg eerst project toe</option>
	    </select>
	    <a href="<?php echo $site_name ?>/planning/project-toevoegen/<?php echo $_GET['bedrijf'] ?>">Voeg project toe</a>
	<?php } ?>
	
	
