<?php
session_start();
require($_SERVER['DOCUMENT_ROOT']."/check_configuration.php");
if($_POST['nieuwe_branche'] != null ){
	$branche = ucfirst(strtolower($_POST['nieuwe_branche']));
	$what = 'id'; $from = 'branche'; $where = "naam = '$branche'";
	if(countRows($what, $from, $where) > 0){
		echo 'Deze branche bestaat al.';
	}else{
        $table="branche";
        $what="naam, actief, gewijzigd_op, gewijzigd_door";
        $with_what= "'".mysql_real_escape_string($branche)."', 1, NOW(), $login_id"; //escape de string, voor de ' en " .
        	$nieuwe_branche = sqlInsert($table, $what, $with_what);
		echo 'De branche '.$_POST['nieuwe_branche'].' is toegevoegd';
	}
        
}elseif($_GET['action'] == 'reloadBranches'){
	$what = 'id, naam'; $from = 'branche'; $where = 'actief = 1 ORDER BY naam ASC';
		$branche_result = sqlSelect($what, $from, $where);?>
	<select class="branche_select textfield" name="branche">
	    <option value>Branche</option>
	<?php 
	while($branche = mysql_fetch_array($branche_result)){
		echo '<option value="'.$branche['id'].'">'.$branche['naam'].'</option>';
	}?>
	</select>
<?php 
}else{?>
                <div id="success"></div>
                <div id="branche_toevoegen_row">
                	<label for="nieuwe_branche">Voeg een branche toe</label>
                    <input type="text" name="nieuwe_branche" id="nieuwe_branche" class="textfield" />
                    <button id="branche_submit" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'">Toevoegen</button
                </div>

            
            <script>
            $(function(){ 
            	$("label").inFieldLabels();
	            $("#branche_submit").on('click', function(){
	            	var branche = $('#nieuwe_branche').val();
	            	if(branche.length <= 1){$('#success').html('"Vul een branche in of sluit het venster via het kruis, rechtsboven"').css({'color' : 'red'});}
	            	else{
		            	$.ajax({
		            		type : 'POST',
		            		url: '<?php echo $etc_root ?>functions/crm/includes/branche.php',
		            		dataType: 'html',
		            		data: { nieuwe_branche : branche },
		            		success : function(data){ $('#nieuwe_branche').val(''); $('#success').html(data).css({'color' : 'green'}); reloadBranches();}
		            	});return false;
	            	}
			    });
			});
			</script>
<?php } ?>
