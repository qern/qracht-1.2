<div id="admin">
    
    <div id="admin_menu">
        <span class="menuitem active" id="gebruikers_item" onclick="toonOpties('gebruikers', <?php echo $login_id ?>)"><img src="<?php echo $etc_root ?>functions/admin/images/icon_gebruikers.png" border="0" alt="Gebruikers" /> <h3>Gebruikers</h3></span>
        <span class="menuitem" id="instellingen_item" onclick="toonOpties('instellingen', <?php echo $login_id ?>)"><img src="<?php echo $etc_root ?>functions/admin/images/icon_instellingen.png" border="0" alt="Instellingen" /> <h3>Instellingen</h3></span>
        <span class="menuitem" id="links_item" onclick="toonOpties('links', <?php echo $login_id ?>)"><img src="<?php echo $etc_root ?>functions/admin/images/icon_links.png" border="0" alt="Links" /> <h3>Links</h3></span>
    </div>
    
    <div id="admin_rest">
        <div id="waiting" style="display:none;">
            <span id="waiting_img"><img src="<?php echo $etc_root ?>functions/<?php echo $functie_get; ?>/css/images/ajax_loader.gif" alt="loading" />&nbsp;</span>
        </div>
        
        <div id="admin_ajax">
            <?php include('functions/admin/includes/gebruikers.php'); ?>
        </div>
    </div>
</div>
