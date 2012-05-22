<div id="account">
    <script>
    function toonOpties(optie, gebruikerId){
       jQuery('#waiting').show(1000);
        // Loop door de menuitems.
        jQuery( "#account_menu span.menuitem" ).each( function( intIndex ){ if(jQuery(this).hasClass('active')){ jQuery(this).removeClass('active'); } } );//als een item active is.. verwijder dan de active status
        jQuery.ajax({
            type : 'POST',dataType : 'html',data: { optie : optie, gebruiker_id : gebruikerId },
            url : '<?php echo $etc_root ?>functions/account/includes/opties.php',
            success : function(data){
                jQuery('#waiting').hide(1000);
                jQuery('#account_ajax').html(data);
                jQuery('#'+optie+'_item').addClass('active');
                jQuery('#content .button').hover( function(){ jQuery(this).addClass('btn_hover'); }, function(){ jQuery(this).removeClass('btn_hover')} ); 
                if(optie == 'foto'){             
                    jQuery(".ppt").remove();
                    jQuery(".pp_overlay").remove();
                    jQuery(".pp_pic_holder").remove();
                    jQuery("a.iframe").fancybox({
                    'overlayShow': true,
                    'overlayColor': '#000',
                    'overlayOpacity': 0.7,
                    'hideOnContentClick': true,  
                    'titleShow': false,
                    'transitionIn': 'elastic',
                    'transitionOut': 'elastic',
                    'speedIn': 600,
                    'speedOut': 200,
                    'centerOnScroll': true,
                    'scrolling': 'auto',
                    'cyclic': true,
                    'width': 750,
                    'height': 750
                    });
                };
            }
        });return false;
    };
    </script>
    <div id="account_menu">
        <span class="menuitem active" id="sprite_info_item" onclick="toonOpties('info', <?php echo $login_id ?>)">  <span class="sprite_image" id="sprite_info"></span> <h3>Info</h3></span>
        <span class="menuitem" id="foto_item" onclick="toonOpties('foto', <?php echo $login_id ?>)">         <span class="sprite_image" id="sprite_foto"></span>   <h3>Foto's</h3></span>
        <span class="menuitem" id="kennis_item" onclick="toonOpties('kennis', <?php echo $login_id ?>)">     <span class="sprite_image" id="sprite_kennis"></span> <h3>Kennis</h3></span>
        <span class="menuitem" id="links_item" onclick="toonOpties('links', <?php echo $login_id ?>)">       <span class="sprite_image" id="sprite_links"></span>   <h3>Links</h3></span>
        <span class="menuitem" id="competentie_item" onclick="toonOpties('competentie', <?php echo $login_id ?>)">       <span class="sprite_image" id="sprite_competentie"></span>   <h3>Competentie</h3></span>
        <span class="menuitem" id="instellingen_item" onclick="toonOpties('instellingen', <?php echo $login_id ?>)">       <span class="sprite_image" id="sprite_instellingen"></span>   <h3>Instellingen</h3></span>
    </div>
    
    <div id="account_rest">
        <div id="waiting" style="display:none;">
            <span id="waiting_text">De lijst wordt geladen</span>
            <span id="waiting_img"><img src="<?php echo $etc_root ?>functions/<?php echo $functie_get; ?>/css/images/ajax_loader.gif" alt="loading" />&nbsp;</span>
        </div>
        
        <div id="account_ajax">
            <?php
            
				$opties = array('info', 'foto', 'kennis', 'links', 'competentie', 'instellingen');  $gebruiker_id = $login_id;
             	if($_GET['actie'] != null && in_array($_GET['actie'], $opties)){include('functions/account/includes/'.$_GET['actie'].'.php');}
				else{include('functions/account/includes/info.php');} 
             ?>
        </div>
    </div>
</div>