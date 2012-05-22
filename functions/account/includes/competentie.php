<?php
	if($_POST['gebruiker_id']){$gebruiker_id = $_POST['gebruiker_id'];}	
	//welke competenies zijn al bekend ?
	$what = 'competentie'; $from = 'portal_gebruiker_competentie'; $where= "gebruiker = $gebruiker_id";
		$reeds_bekende_competenties = sqlSelect($what, $from, $where);
		
	// haal alle competenties op
	$what = 'id, competentie'; $from = 'competentie'; $where = '1';
		$competenties = sqlSelect($what, $from, $where);
		
	// haal de competenties op, die bij de gebruiker horen. En hoeveel uur hij er voor heeft staan
	$what = 'a.uren, b.id, b.competentie';  $from = 'portal_gebruiker_competentie a LEFT JOIN competentie AS b ON (b.id = a.competentie)';
	$where = "a.gebruiker = $gebruiker_id";
	//echo "SELECT $what FROM $from WHERE $where";
		$competentie_uren = sqlSelect($what, $from, $where);
?>

<script>
var url = '<?php echo $etc_root ?>functions/account/includes/competentie_check.php';
jQuery(function(){
	jQuery('#competentie_overzicht_list input').on('keyup', function(){ saveUren( jQuery(this).attr('name').split('_')[1], jQuery(this).val() ); });
	jQuery('.competentie_uur').hover(function(){jQuery(this).find('img').show();},function(){jQuery(this).find('img').hide();});
	jQuery('#competentie_overzicht_list .delete img').on('click', function(){ deleteCompetentie( jQuery(this).attr('id').split('_')[1] ); });
	jQuery('#competentie_opslaan').on('click', function(){ saveCompetentie(); });
})
function saveCompetentie(){
	jQuery.ajax({
		type : 'POST',dataType : 'html', data: { action : 'nieuweCompetentie', competentie : jQuery('#select_competentie').val() }, url : url, success : function(data){ reloadForm(); reloadCompetenties(); }
	});return false;	
}
function saveUren( competentie, uren ){
	jQuery.ajax({
	    type : 'POST',dataType : 'html', data: { action : 'wijzigUren', competentie : competentie, uren : uren }, url : url, success : function(data){}
	});return false;
}
function deleteCompetentie( competentie ){
	jQuery.ajax({
	        type : 'POST',dataType : 'html',data: { action : 'verwijdeCompetentie', competentie : competentie },  url : url, success : function(data){reloadCompetenties();}
    });return false;
}
function reloadForm(){
	jQuery.ajax({
            type : 'GET', dataType : 'html', data: { action : 'reloadForm'}, url : url, success : function(data){ jQuery("#select_competentie").html(data);}
    });return false;
}

function reloadCompetenties(){
	jQuery.ajax({
            type : 'GET', dataType : 'html', data: { action : 'reloadCompetenties'}, url : url,
            success : function(data){ 
            	jQuery("#competentie_overzicht_list").html(data);
            	jQuery('#competentie_overzicht_list input').on('keyup', function(){ saveUren( jQuery(this).attr('name').split('_')[1], jQuery(this).val() ); });
            	jQuery('#competentie_overzicht_list .delete img').hover(function(){jQuery(this).show();},function(){jQuery(this).hide();});
            	jQuery('#competentie_overzicht_list .delete img').on('click', function(){ deleteCompetentie( jQuery(this).attr('id').split('_')[1] ); });
            }
    });return false;
}
</script>
<div id="account_center">
    <div id="competentie_invoer">
        
        <div id="competentie_invoer_header" class="account_header">
            <p class="info_title">Bij welke competentie bent u betrokken ?</p>
        </div>
        <div id="competentie_invoer_form">
            <select id="select_competentie" class="textfield">
            	<option value="">Kies een competentie</option>
            	<?php 
            	while($reeds_bekende_competentie = mysql_fetch_array($reeds_bekende_competenties)){ $bekende_competenties[] = $reeds_bekende_competentie['competentie']; }
            		while($competentie = mysql_fetch_array($competenties)){
            			if(!in_array($competentie['id'], $bekende_competenties)){?>
            		<option value="<?php echo $competentie['id'] ?>"><?php echo $competentie['competentie'] ?></option>
            	<?php 	}//einde if
					}//einde while
            	?>
            </select>
            <span id="competentie_opslaan" class="button">Toevoegen</span>
        </div>                
    </div>
    <div id="competentie_overzicht">
        <div id="competentie_overzicht_header" class="account_header">
            <p class="info_title">Uw reeds gekoppelde competenties: </p>
        </div>
        <div id="competentie_overzicht_headers">
        	<div class="competentie_header">Competentie</div>
        	<div class="uren_header">Uren per week</div>
        </div>
        <div id="competentie_overzicht_list">
        	
        <?php while($competentie_uur = mysql_fetch_array($competentie_uren)){?>
            <div class="competentie_uur">
            	<div class="competentie"><?php echo $competentie_uur['competentie']; ?></div>
            	<div class="delete"><img id="delete_<?php echo $competentie_uur['id'] ?>" title="Verwijder competentie" alt="Verwijder kennis" src="/functions/account/css/images/delete.png" class="kennis_delete_img" style="display:none;" /></div>
            	<div class="uren"><input type="text" class="textfield" name="competentie_<?php echo $competentie_uur['id'] ?>" value="<?php echo $competentie_uur['uren']; ?>" /></div>
            </div>
        <?php }?>
        </div>
    </div>
</div>
<div id="account_right">
   &nbsp;
</div>