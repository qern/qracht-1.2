<?php
	if($_POST['gebruiker_id']){$gebruiker_id = $_POST['gebruiker_id'];}
    //haal op wat nodig is:
    $what = 'startpagina'; $from = 'portal_gebruiker_instellingen'; $where = 'gebruiker = '.$gebruiker_id;
        $instellingen = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	if($instellingen['startpagina'] == null){
		$what = 'gebruiker, gewijzigd_door, gewijzigd_op'; $with_what = $gebruiker_id.', '.$login_id.', NOW()';
			$nieuwe_instellingen = sqlInsert($from, $what, $with_what);
		$instellingen['startpagina'] = 'home';
	}
	$startpagina = $instellingen['startpagina'];
?>

<script>        
jQuery(function(){
	jQuery('#new_startpage').on('change', function(){
		jQuery.ajax({
            type : 'GET',dataType : 'html',data: { action: 'changeStartpage', startpagina : jQuery(this).val(), gebruiker : '<?php echo $gebruiker_id; ?>' },
            url : '<?php echo $etc_root ?>functions/account/includes/instellingen_check.php',
            success : function(data){ jQuery('#startpage_changed').css('color', 'green').html(data).show().delay(2000).hide()}
        });return false;
	});
});    

</script>
<div id="account_center">
	<div id="change_startpage">
	    <div id="change_startpage_header" class="account_header">  <p class="info_title">Selecteer hier bij welke functie u wilt komen na het inloggen</p>  </div>
	    <div id="startpage_changed"></div>
	    <div id="change_startpage_select">
			<select id="new_startpage" class="textfield">
			<?php
				//$startpaginas = array('account', 'afbeeldingen', 'admin', 'bestand', 'cms', 'crm', 'dashboard', 'home', 'invoer', 'inzage', 'kennis', 'kennis-toevoegen', 'nieuws', 'overzicht', 'planning', 'profiel', 'publicaties', 'servicedesk', 'urentool', 'wiki', 'zoeken');
				$startpaginas = array('crm' => 'CRM', 'planning' => 'Planning', 'profiel' => 'Profiel', 'home' => 'Sociale startpagina', 'urentool' => 'Urentool', 'dashboard' => 'Werkplek startpagina');

				foreach($startpaginas AS $mogelijke_startpagina => $titel){?>
					<option<?php if($mogelijke_startpagina == $startpagina){ echo  ' selected="selected"';}?> value="<?php echo $mogelijke_startpagina; ?>"><?php echo $titel; ?></option> 
				<?php }
			?>
	    	</select>
	    </div>
	</div>                
</div>
<div id="account_right">
    &nbsp;
</div>
