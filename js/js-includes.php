<script>var domain = '<?php echo $etc_root; ?>';</script>
<?php
//dit is de standaard javascript. Die universeel geladen dient te orden.
    if(isset($functie_get)){
    	if(file_exists($functie_js.$functie_get.'.js')){
                echo '<script src="'.$etc_root.$functie_js.$functie_get.'.js" type="text/javascript"></script>';
                }
        if(isset($_GET['page'])){
            if(file_exists($functie_js.$functie_get.'_'.$_GET['page'].'.js')){
                echo '<script src="'.$etc_root.$functie_js.$functie_get.'_'.$_GET['page'].'.js" type="text/javascript"></script>';
            }
        }elseif($_GET['function'] == 'planning'){
            if(file_exists($functie_js.$functie_get.'_dashboard.js')){
                echo '<script src="'.$etc_root.$functie_js.$functie_get.'_dashboard.js" type="text/javascript"></script>';
            }
        }
    }
    
if($_GET['function'] == 'admin'){
    echo '
    <script>
        function toonGebruikers(str) {
        var xmlhttp;
        if (str.length == 0) {
            str = "leeg";
        }
        if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else { // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
        
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("users").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", "/functions/'.$functie_get.'/includes/gebruikers.php?actief='.$actief.'&q=" + str, true);
        xmlhttp.send();
        }       
    </script>
    <script> $(function(){ $("#succes").fadeOut(5000); }); </script>';

}
elseif($_GET['function'] == 'organisatie'){
    echo '
    <script>
        function toonOrganisaties(str) {
        var xmlhttp;
        if (str.length == 0) {
            str = "leeg";
        }
        if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else { // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
        
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("users").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", "'.$etc_root.'functions/'.$functie_get.'/includes/organisatie.php?actief='.$actief.'&q=" + str, true);
        xmlhttp.send();
        }       
    </script>
    <script type="text/javascript"> $(function(){ $("#succes").fadeOut(5000); }); </script>';
}
elseif($_GET['function'] == 'nieuws'){/*?>
<script type="text/javascript">
    $(document).ready(function() {
                $('#upload_image').uploadify({
                    'uploader'       : '<?php echo $site_name;?>lib/uploadify/uploadify.swf',
                    'script'         : '<?php echo $site_name;?>functions/<?php echo $functie_get;?>/includes/upload_nieuws_foto.php?ids=<?php echo $album_id.'_'.$nieuws_id ?>',
                    'cancelImg'      : '<?php echo $site_name;?>lib/uploadify/cancel.png',
                    'folder'         : ''.$etc_root.'files/album/<?php echo $album_id ?>',
                    'multi'          : true,
                    'auto'           : true,
                    'fileExt'        : '*.jpg;*.gif;*.png',
                    'fileDesc'       : 'Afbeeldingen (.JPG, .GIF, .PNG)',
                    'queueID'        : 'image-queue',
                    'queueSizeLimit' : 5,
                    'simUploadLimit' : 5,
                    'removeCompleted': false,
                    'onSelectOnce'   : function(event,data) {
                        $('#status-message').text(data.filesSelected + ' bestanden zijn toegevoegd aan de queue.');
                    },
                    'onAllComplete'  : function(event,data) {
                        $('#status-message').text(data.filesUploaded + ' bestanden geupload, ' + data.errors + ' errors.');
                    }
                });                
    });
    $(document).ready(function() {
                $('#upload_documenten').uploadify({
                    'uploader'       : '<?php echo $site_name;?>lib/uploadify/uploadify.swf',
                    'script'         : '<?php echo $site_name;?>functions/<?php echo $functie_get;?>/includes/upload_nieuws_bestand.php?ids=<?php echo $nieuws_id.'_'.$_login_id ?>',
                    'cancelImg'      : '<?php echo $site_name;?>lib/uploadify/cancel.png',
                    'folder'         : ''.$etc_root.'files/bestand/<?php echo $_GET['nieuws_id'] ?>',
                    'multi'          : true,
                    'auto'           : true,
                    //'fileExt'        : '*.jpg;*.gif;*.png',
                    //'fileDesc'       : 'Image Files (.JPG, .GIF, .PNG)',
                    'queueID'        : 'file-queue',
                    'queueSizeLimit' : 5,
                    'simUploadLimit' : 5,
                    'removeCompleted': false,
                    'onSelectOnce'   : function(event,data) {
                        $('#status-message').text(data.filesSelected + ' bestanden zijn toegevoegd aan de queue.');
                    },
                    'onAllComplete'  : function(event,data) {
                        $('#status-message').text(data.filesUploaded + ' bestanden geupload, ' + data.errors + ' errors.');
                    }
                });                
    });
    

// ---------------------------------------------
// Drag - Drop voor afbeeldingen
// ---------------------------------------------
$(function(){
    $("#album_afbeeldingen").sortable({
        connectWith:'.afbeeldingen',
        update:function(){
            $.ajax({
                type:"POST",url:"'.$etc_root.'functions/nieuws/includes/sortering_afbeelding.php",            
                data:{
                    afbeelding:$("#album_afbeeldingen").sortable('serialize')
                },
                success:function(html){}
            });
        }
    });});    
    

</script>
<?php
*/}elseif($functie_get == 'home'){
    echo '
    <script>
        function toonNieuws(str) {
        var xmlhttp;
        if (str.length == 0) {
            str = "leeg";
        }
        if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else { // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
        
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("dashboard_right").innerHTML = xmlhttp.responseText;
                $(".ppt").remove();
                $(".pp_overlay").remove();
                $(".pp_pic_holder").remove();
                $("a.iframe").fancybox(
                {\'overlayShow\': true,
                \'overlayColor\': \'#000\',
                \'overlayOpacity\': 0.7,
                \'hideOnContentClick\': true,  
                \'titleShow\': false,
                \'transitionIn\': \'elastic\',
                \'transitionOut\': \'elastic\',
                \'speedIn\': 600,
                \'speedOut\': 200,
                \'centerOnScroll\': true,
                \'scrolling\': \'auto\',
                \'cyclic\': true,
                \'width\': 750,
                \'height\': 750
                });
            }
        }
        xmlhttp.open("GET", "'.$etc_root.'functions/'.$functie_get.'/includes/toonnieuws.php?q=" + str, true);
        xmlhttp.send();
        }  
    </script>';
}
elseif($functie_get == 'planning'){
	if($_GET['page'] == 'detail'){?>
		<script>
			function laatHTMLzien(){
				$(function() {
			    $("#html_tekst").ckeip({
			  <?php if($_GET['detail'] == 'activiteit'){?>
			        e_url: '/functions/planning/includes/check.php?action=html_tekst&activiteit_id=<?php echo $activiteit_id; ?>',
			  <?php }else{?>
                    e_url: '/functions/planning/includes/check.php?action=html_tekst&project_id=<?php echo $project_id; ?>',     
              <?php } ?>
			        e_width:100, 
			        e_height:100,
			        e_hover_color:'#E6DBCE'
			        });
			    });
			}$(document).ready(laatHTMLzien);
		</script>
	<?php }
}
?>
