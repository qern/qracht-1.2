<div id="crm">
    <script>
    	jQuery(function(){

    		jQuery('#relatie_organisatie span').hover(
    			function(){ jQuery(this).addClass('btn_hover'); }, function(){ jQuery(this).removeClass('btn_hover')}
    		)
    		
    		jQuery('#relatie_organisatie span').on('click', function(){
    			jQuery( "#relatie_organisatie span" ).each( function( intIndex ){ if(jQuery(this).hasClass('active_focus')){ jQuery(this).removeClass('active_focus'); }}); //als een item active is.. verwijder dan de active status
    			
    			var toon = jQuery(this).attr('id').split('_')[0];
    			if(toon === 'relatie'){
    				jQuery('#organisatie_menu').hide();
    				jQuery('#relatie_menu').show();
    			}else{
    				jQuery('#relatie_menu').hide();
    				jQuery('#organisatie_menu').show();
    			}
    			jQuery('#'+toon+'_focus').addClass('active_focus');
    			
    		})
    		jQuery("#zoek_organisatie_naam").autocomplete({
				source:'/functions/<?php echo $functie_get; ?>/includes/filter_ajax.php?filter=organisatie_auto',
				minLength:2,
				select: function( event, ui ) {
					loadCRM('detail', ui.item.id, 'organisatie'); return false;
				}
			});
			jQuery("#zoek_relatie_naam").autocomplete({
				source:'/functions/<?php echo $functie_get; ?>/includes/filter_ajax.php?filter=relatie_auto',
				minLength:2,
				select: function( event, ui ) {
					loadCRM('detail', ui.item.id, 'relatie'); return false;
				}
			});  
			jQuery('#zoek_organisatie_send').on('click', function(){
				var zoekwoord = jQuery('#zoek_organisatie_naam').val();
				//console.log(zoekwoord);
				if(zoekwoord.length >= 2){
				    loadCRM('overzicht', '', 'organisatie', zoekwoord);
				}
			}); 
			jQuery('#zoek_relatie_send').on('click', function(){
				var zoekwoord = jQuery('#zoek_relatie_naam').val();
				//console.log(zoekwoord);
                if(zoekwoord.length >= 2){
                    loadCRM('overzicht', '', 'relatie', zoekwoord);
                }
			}); 
			<?php 
			     if($_GET['pagina'] != null){
			         if($_GET['pagina'] == 'relatie-toevoegen'){     
						 $pagina = 'relatie-toevoegen'; 
						 if($_GET['organisatie_id'] != null){$id = $_GET['organisatie_id']; $detail = 'organisatie';}
						 elseif($_GET['relatie_id'] != null){$id = $_GET['relatie_id']; $detail = 'relatie';}
						 else{$id = ''; $detail = 'relatie';}
						}
			         if($_GET['pagina'] == 'organisatie-toevoegen'){ $pagina = 'organisatie-toevoegen'; $id = ''; $detail = 'organisatie';  }
                     if($_GET['pagina'] == 'relatie-wijzigen'){     
						 $pagina = 'relatie-toevoegen'; 
						 if($_GET['organisatie_id'] != null){$id = $_GET['organisatie_id']; $detail = 'organisatie';}
						 elseif($_GET['relatie_id'] != null){$id = $_GET['relatie_id']; $detail = 'relatie';}
						 else{$id = ''; $detail = 'relatie';}
					 }
                     if($_GET['pagina'] == 'organisatie-wijzigen'){
						 $pagina = 'organisatie-toevoegen';
						 if($_GET['organisatie_id'] != null){$id = $_GET['organisatie_id']; $detail = 'organisatie';}
						 elseif($_GET['relatie_id'] != null){$id = $_GET['relatie_id']; $detail = 'relatie';}
						 else{$id = ''; $detail = 'relatie';} 
					 }
			         if($_GET['pagina'] == 'detail'){                $pagina = $_GET['pagina']; $id = $_GET['id']; $detail = $_GET['detail'];  }
			         if($_GET['pagina'] == 'relatie-overzicht'){     $pagina = 'overzicht'; $id = ''; $detail = 'relatie'; }
			         if($_GET['pagina'] == 'organisatie-overzicht'){ $pagina = 'overzicht'; $id = ''; $detail = 'organisatie'; }
			     }else{                                              $pagina = 'overzicht'; $id = ''; $detail = 'organisatie'; }//standaard !!
                                                              
			?>
			loadCRM('<?php echo $pagina; ?>', '<?php echo $id; ?>', '<?php echo $detail; ?>')
    	});
    	
        function loadCRM(page, id, detail, filter){
           if(filter === null){filter = '';}
	       jQuery('#waiting').show(1000);
	        // Loop door de menuitems.
	        jQuery( "#account_menu span.menuitem" ).each( function( intIndex ){ if(jQuery(this).hasClass('active')){ jQuery(this).removeClass('active'); }}); //als een item active is.. verwijder dan de active status bij de rest
	        jQuery.ajax({
	            type : 'POST',
	            url : '/functions/<?php echo $functie_get; ?>/includes/opties.php',
	            dataType : 'html',
	            data: { pagina: page, detail: detail, id: id, filter : filter },
	            success : function(data){
	                jQuery('#waiting').hide(1000);
	                jQuery('#crm_right').html(data);
	                jQuery('#' + page + '_item').addClass('active');
	            }
	        });return false;        
	      }
    </script>
    <div id="crm_left">
        <div id="relatie_organisatie">
            <span id="organisatie_focus" class="button active_focus">Organisatie</span>
            <span id="relatie_focus" class="button">Relatie</span>
        </div>
        <div id="organisatie_menu">
        	<span onclick="loadCRM('overzicht', 1, 'organisatie')" id="sprite_info_item" class="menuitem active">  
        		<span id="sprite_organisatie_overzicht" class="sprite_image"></span> <h3>Overzicht</h3>
        	</span>
        	<span onclick="loadCRM('organisatie-toevoegen', '', 'organisatie')" id="sprite_info_item" class="menuitem">  
        		<span id="sprite_organisatie_toevoegen" class="sprite_image"></span> <h3>Toevoegen</h3>
        	</span>
        	<div id="organisatie_zoeken">
        		<p>Zoek een organisatie</p>
        		<label class="label" for="zoek_organisatie_naam">Organisatie naam</label>
        		<input type="text" id="zoek_organisatie_naam" class="textfield" />
        		<button id="zoek_organisatie_send" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Zoeken</button>
        	</div>
        </div> 
        <div id="relatie_menu" style="display:none;">
        	<span onclick="loadCRM('overzicht', 1, 'relatie')" id="sprite_info_item" class="menuitem active">  
        		<span id="sprite_relatie_overzicht" class="sprite_image"></span> <h3>Overzicht</h3>
        	</span>
        	<span onclick="loadCRM('relatie-toevoegen', '', 'relatie')" id="sprite_info_item" class="menuitem">  
        		<span id="sprite_relatie_toevoegen" class="sprite_image"></span> <h3>Toevoegen</h3>
        	</span>
        	<div id="relatie_zoeken">
        		<p>Zoek een relatie</p>
        		<label class="label" for="zoek_relatie_naam">Relatie naam</label>
        		<input type="text" id="zoek_relatie_naam" class="textfield"  />
        		<button id="zoek_relatie_send" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Zoeken</button>
        	</div>
        </div>
    </div>
    <div id="crm_right"></div>
</div>
