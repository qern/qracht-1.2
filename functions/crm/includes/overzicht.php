<div id="overzicht">
<?php  $detail = $_REQUEST['detail'];
//Begin met het welkom heten van de mensen
	if($detail == 'relatie'){?>
		
		<h2>Relatieoverzicht</h2>
		
<?php }elseif($detail == 'organisatie'){?>
	
	<h2>Organisatieoverzicht</h2>
	
<?php }	
//het kan dat er gezocht is op een deel van een (organisatie-)naam.
//dit is een filter en moet worden meegenomen in de ajax!
if($_REQUEST['filter'] != null){ $filter = $_REQUEST['filter']; }else{$filter = '';}
?>
	<script>
		function loadLijst(orderBy){
			jQuery.ajax({
	            type : 'POST',
	            url : '/functions/crm/includes/overzicht_ajax.php',
	            dataType : 'html',
	            data: { detail: '<?php echo $detail ?>', filter: '<?php echo $filter; ?>', orderBy: orderBy},
	            success : function(data){
	                jQuery('#waiting').hide(1000);
	                jQuery('#overzichtslijst').html(data);
	            }
	        });return false;
		}
		jQuery(function(){
			<?php  if($detail == 'organisatie'){?>
			 loadLijst('naam_ASC');   
			<?php }elseif($detail == 'relatie'){?>
			 loadLijst('achternaam_ASC');
            <?php } ?>
            jQuery('.lijst_body .lijst_row').hover( function(){jQuery(this).addClass('lijst_row_hover');}, function(){jQuery(this).removeClass('lijst_row_hover'); });
            jQuery('.lijst_body .lijst_row').on('click', function(){
                loadCRM('detail', jQuery(this).attr('id'), '<?php echo $detail; ?>');
            });
        })
	</script>
	<div id="overzichtslijst">
		
	</div>
</div>
