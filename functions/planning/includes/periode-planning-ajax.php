<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
if($_GET['filter']){
    require($_SERVER['DOCUMENT_ROOT'].$etc_root.'includes/ajax_functions.php');
    $org_result = array();
    $term = trim(strip_tags($_GET['term']));                                                    //retrieve the search term that autocomplete sends
    if($_GET['filter'] == 'organisatie'){
        $qstring = "SELECT  c.id, c.naam 
                    FROM
                            planning_activiteit a 
                            LEFT JOIN project AS b ON (b.id = a.project)
                            LEFT JOIN organisatie AS c ON (c.id = b.organisatie)
                    WHERE   a.actief = 1 AND b.actief = 1 AND c.naam LIKE '%$term%'";
    }
    $result = mysql_query($qstring)or DIE('Geen database connectie. Raadpleeg de Webmaster');   //query the database for entries containing the term
    while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){if($row['voornaam']){$naam =  $row['voornaam'].' '.$row['achternaam'];}else{$naam = $row['naam'];} $row_set[$naam] = $row['id']; }        //loop through the retrieved values and make array
    foreach ($row_set as $key=>$value) { array_push($org_result, array("id"=>$value, "label"=>$key, "value" => strip_tags($key))); }
    echo array_to_json($org_result);//format the array into json data
}
elseif($_GET['action'] == 'laadProjecten'){
    $organisatie_id = $_GET['organisatie'];
    
    $what = 'id, titel'; $from =  'project'; $where = 'actief = 1 AND organisatie = '.$organisatie_id;
        $aantal_projecten = countRows($what, $from, $where);
    $toon_project = null;
    if($aantal_projecten > 0){    
        $projecten = sqlSelect($what, $from, $where);?>
        <select id="periode_proj" class="textfield" onchange="showActiviteiten( this.value )">
<?php   while($project = mysql_fetch_array($projecten)){
             if($toon_project == null){$toon_project = $project['id'];}?>
            <option value="<?php echo $project['id'] ?>"><?php echo $project['titel']; ?></option>    
        <?php }?>
        </select>
        <script>showActiviteiten('<?php echo $toon_project; ?>');</script>
<?php }//einde if $aantal_projecten
    else{?>
        <select id="periode_proj" class="textfield" disabled="disabled">
            <option value>Kies eerst een organisatie</option>
        </select>
    <?php }//einde else
}//einde if action == laadProjecten
elseif($_GET['action'] == 'koppelActviteitIteratie'){
	$table = 'planning_activiteit'; $what = "gewijzigd_op = NOW(), gewijzigd_door = $login_id, status = 'to do', status_datum = NOW(),	 iteratie = ".$_GET['iteratie']; $where = 'id = '.$_GET['activiteit'];
		$update_activiteit = sqlUpdate($table, $what, $where);
}
elseif($_GET['action'] == 'showActiviteiten'){ ?>
<script>
	jQuery(function() {
		jQuery( ".periode_iteratie" ).draggable({ revert: true });

		jQuery( ".detail_activiteit" ).droppable({
			activeClass: "ui-state-hover",
			hoverClass: "ui-state-active",
			drop: function( event, ui ) {
				var iteratie = ui.draggable.attr('data-iteratie'), activiteit = jQuery(this).attr('data-activiteit');
				koppelActiviteitIteratie(activiteit, iteratie);
			}
		});
	});
</script>
<div id="periode_nogteplannen">
	<h2>Nog te plannen</h2>
	<?php
    $project_id = $_GET['project'];
    $what = '   a.id,
                a.werkzaamheden,
                a.status,
                a.uur_aantal,
                b.competentie,
                DATE_FORMAT(a.status_datum, \'%d %M %Y\') status_datum,
                DATE_FORMAT(a.toegevoegd_op, \'%d %M %Y\') toegevoegd_op';
    $from = '   planning_activiteit a
                LEFT JOIN competentie AS b ON (b.id = a.competentie)';
    $where ='   a.actief = 1
				AND a.status = \'nog te plannen\'
                AND a.project = '.$project_id.'
                ORDER BY a.toegevoegd_op ASC'; 
    //echo "SELECT $what FROM $from WHERE $where";
    $aantal_activiteiten = countRows($what, $from, $where);
    if($aantal_activiteiten > 0){
        $project_activiteiten = sqlSelect($what, $from, $where);
        
        while($project_activiteit = mysql_fetch_array($project_activiteiten)){
            if($i == 1){ $class = 'en_om'; $i = 0; }else{ $class = 'om'; $i = 1; }
            ?>
        <div class="detail_activiteit <?php echo $class; ?>" data-activiteit="<?php echo $project_activiteit['id'] ?>">
            <span class="activiteit_top">
                <a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
                    <span class="activiteit_werkzaamheden"> <?php echo $project_activiteit['werkzaamheden'] ?></span>
                </a> 
            </span>

            <span class="activiteit_bottom">
                <a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
                    <span class="activiteit_uren"><?php echo $project_activiteit['uur_aantal'] ?></span>
                </a>
                <span class="activiteit_status"><?php echo $project_activiteit['status'] ?>&nbsp;/</span>
                <span class="activiteit_competentie">&nbsp;<?php echo $project_activiteit['competentie'] ?></span>
                <span class="activiteit_deadline"><?php  echo $project_activiteit['toegevoegd_op'] ?></span>
            </span>
            
        </div>
    <?php }
    }?>
 </div>
 <div id="periode_rest">
 <h2>Ingeplande activiteiten</h2>
 <?php
     $what = '  a.id,
                a.werkzaamheden,
                a.status,
                a.uur_aantal,
                b.competentie,
                DATE_FORMAT(a.status_datum, \'%d %M %Y\') status_datum,
                DATE_FORMAT(c.datum, \'%d %M %Y\') einddatum';
    $from = '   planning_activiteit a
                LEFT JOIN competentie AS b ON (b.id = a.competentie)
                LEFT JOIN planning_iteratie AS c ON (c.id = a.iteratie)';
    $where ='   a.actief = 1
				AND a.status != \'nog te plannen\'
                AND a.project = '.$project_id.'
                ORDER BY a.toegevoegd_op ASC'; 
    //echo "SELECT $what FROM $from WHERE $where";
    $aantal_activiteiten = countRows($what, $from, $where);
    if($aantal_activiteiten > 0){
        $project_activiteiten = sqlSelect($what, $from, $where);
        
        while($project_activiteit = mysql_fetch_array($project_activiteiten)){
            if($i == 1){ $class = 'en_om'; $i = 0; }else{ $class = 'om'; $i = 1; }
            ?>
        <div class="detail_activiteit <?php echo $class; ?>" data-activiteit="<?php echo $project_activiteit['id'] ?>">
            <span class="activiteit_top">
                <a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
                    <span class="activiteit_werkzaamheden"> <?php echo $project_activiteit['werkzaamheden'] ?></span>
                </a> 
            </span>

            <span class="activiteit_bottom">
                <a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
                    <span class="activiteit_uren"><?php echo $project_activiteit['uur_aantal'] ?></span>
                </a>
                <span class="activiteit_status"><?php echo $project_activiteit['status'] ?>&nbsp;/</span>
                <span class="activiteit_competentie">&nbsp;<?php echo $project_activiteit['competentie'] ?></span>
                <span class="activiteit_deadline"><?php  echo $project_activiteit['einddatum'] ?></span>
            </span>
            
        </div>
    <?php }
    }?>
 </div>
<?php } ?>
