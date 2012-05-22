<div id="urentool">
    <script>
    function toonRapportage(optie, gebruikerId){
       jQuery('#waiting').show(1000);
        // Loop door de menuitems.
        jQuery( "#urentool_menu span.menuitem" ).each( function( intIndex ){ if(jQuery(this).hasClass('active')){ jQuery(this).removeClass('active'); } } );//als een item active is.. verwijder dan de active status
        jQuery.ajax({
            type : 'POST',dataType : 'html',data: { optie : optie },
            url : '<?php echo $etc_root ?>functions/urentool/includes/opties.php',
            success : function(data){
                jQuery('#waiting').hide(1000);
                jQuery('#urentool_ajax').html(data);
                jQuery('#'+optie+'_item').addClass('active');
                jQuery('#content .button').hover( function(){ jQuery(this).addClass('btn_hover'); }, function(){ jQuery(this).removeClass('btn_hover')} ); 
            }
        });return false;
    };
    </script>
    <div id="urentool_menu">
        <span class="menuitem active" id="sprite_info_item" onclick="toonRapportage('maand')">  <span class="sprite_image" id="sprite_info"></span> <h3>Maandoverzicht</h3></span>
        <span class="menuitem" id="foto_item" onclick="toonRapportage('gebruiker')">         <span class="sprite_image" id="sprite_foto"></span>   <h3>Gebruikeroverzicht</h3></span>
        <span class="menuitem" id="kennis_item" onclick="toonRapportage('organisatie')">     <span class="sprite_image" id="sprite_kennis"></span> <h3>Organisatieoverzicht</h3></span>
        <span class="menuitem" id="kennis_item" >     
            <span class="sprite_image" id="sprite_kennis"></span> 
            <a href="/functions/urentool/includes/uren_export.php" target="_blank"><h3>Export uren</h3></a>
        </span>
    </div>
    
    <div id="urentool_rest">
        <div id="waiting" style="display:none;">
            <span id="waiting_text">De lijst wordt geladen</span>
            <span id="waiting_img"><img src="<?php echo $etc_root ?>functions/account/css/images/ajax_loader.gif" alt="loading" />&nbsp;</span>
        </div>
        
        <div id="urentool_ajax">
            <?php include('functions/'.$functie_get.'/includes/maand.php'); ?>
        </div>
    </div>
</div>