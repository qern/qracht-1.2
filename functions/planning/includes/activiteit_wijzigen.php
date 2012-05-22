<?php
    //haal alles op
    $what=" a.werkzaamheden,
            a.uur_aantal,
            a.status,
            DATE_FORMAT(a.status_datum, '%d %M %Y') status_datum,
            a.in_behandeling_door,
            a.acceptatie_door,
            a.html_detail,
            a.prioriteit,
            b.naam,
            DATE_FORMAT(c.datum, '%d %M %Y') iteratie_einddatum,
            d.project,
            e.competentie";
    $from=" planning_activiteit a
            LEFT JOIN organisatie AS b ON (b.id = a.organisatie_id AND b.actief = 1)
            LEFT JOIN planning_iteratie AS c ON (c.id = a.planning_iteratie_id)
            LEFT JOIN project AS d ON (d.id = a.project)
            LEFT JOIN competentie AS e ON (e.id = a.competentie)";
    $where ="a.id= '".$_GET['activiteit_id']."' AND a.actief = 1 ";
    $wijzigen_result = sqlSelect($what,$from,$where);
    $info = mysql_fetch_assoc($wijzigen_result);
    $activiteit_id = $_GET['activiteit_id'];
    //echo "SELECT $what FROM $from WHERE $where" ;
    $table='planning_recent'; $what='gezien = 1'; $where="planning_activiteit_id = '$activiteit_id' AND gebruiker_id = $login_id";
        $update_recentmelding_voor_deze_activiteit = sqlUpdate($table, $what, $where);
        
    /*//haal de andere mensen op, die ook deze activiteit volgen
    $what_waarschuwingen  = 'b.voornaam, b.achternaam, c.profielnaam'; 
    $from_waarschuwingen = "planning_waarschuw_mij a
             LEFT JOIN relaties AS b ON (b.login_id = a.gebruiker_id)
             LEFT JOIN gebruiker AS c ON (c.id = a.gebruiker_id)";        
    $where_waarschuwingen = "a.planning_activiteit_id = '$activiteit_id' AND b.actief = 1 AND c.actief = 1";
    $aantal_gewaarschuwden = countRows($what_waarschuwingen, $from_waarschuwingen, $where_waarschuwingen);
    if($aantal_gewaarschuwden > 0){$result_waarschuwingen = sqlSelect($what_waarschuwingen, $from_waarschuwingen, $where_waarschuwingen);};
    */
    //voor de tabs wil ik weten hoeveel reacties, bestanden en tags er zijn. Haal dus alles al hier op en doe later de while-loop.
    
    //reactie(s)
    $what_reacties="
    		b.id, b.inhoud, b.geschreven_door, DATE_FORMAT(b.geschreven_op, '%W %d %M %Y om %H:%i') AS geschreven_op, 
    		c.gebruikersnaam, c.voornaam, c.achternaam, 
    		d.album, d.path AS profielfoto";
    $from_reacties=" 
    		planning_reactie a
    		LEFT JOIN portal_reactie AS b ON (b.id =  a.reactie)
            LEFT JOIN portal_gebruiker as c ON (c.id = b.geschreven_door)
            LEFT JOIN portal_image as d ON (d.id = c.profielfoto)";
    $where_reacties="a.planning_activiteit_id = '$activiteit_id'
            AND b.actief = 1 AND c.actief = 1 AND d.actief = 1
            ORDER BY b.geschreven_op DESC";
    $aantal_reacties = countRows($what_reacties, $from_reacties, $where_reacties);     $result_reacties = sqlSelect($what_reacties, $from_reacties, $where_reacties);
    //echo "SELECT $what_reacties FROM $from_reacties WHERE $where_reacties" ;
    //bestaat de bestanden map al voor deze activiteit ?
    $path = $_SERVER['DOCUMENT_ROOT'].$etc_root.'files/planning_documenten/'.$activiteit_id.'/';
    if(is_dir($path)){}else{mkdir($path);}
    //bestand(en)
    $what_bestanden = 'id, bestand';
    $from_bestanden = 'planning_bestand';
    $where_bestanden = "planning_activiteit_id = '$activiteit_id'";
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden); $result_bestanden = sqlSelect($what_bestanden, $from_bestanden, $where_bestanden);
    
    //tag(s)
    $what_tags = 'a.id, b.naam';
    $from_tags = 'planning_tag a, portal_tag b';
    $where_tags = "a.activiteit = '$activiteit_id' AND b.id = a.tag";
    $aantal_tags = countRows($what_tags, $from_tags, $where_tags); $result_tags = sqlSelect($what_tags, $from_tags ,$where_tags);
    
    //
    
    //als er een ID staat aangegeven bij in_behandeling_door, dan moet er een opgehaald worden wie dat is + de profielnaam ophalen
    if($info['in_behandeling_door'] != 0){
        $what = "voornaam, achternaam, gebruikersnaam";
        $from ="portal_gebruiker";
        $where = "id = ".$info['in_behandeling_door']." AND actief = 1";
        $in_behandeling_door = mysql_fetch_assoc(sqlSelect($what,$from,$where));
    }
    //als er een ID staat aangegeven bij acceptatie_door, dan moet er een opgehaald worden wie dat is + de profielnaam ophalen
    if($info['acceptatie_door'] != 0){
        $what = "voornaam, achternaam, gebruikersnaam";
        $from ="portal_gebruiker";
        $where = "id =".$info['acceptatie_door']." AND actief = 1";
        $acceptatie_door = mysql_fetch_assoc(sqlSelect($what,$from,$where));
    }
?> 

<div id="planning">
<script>
var toegevoegde_gebruikers = [];
jQuery(function(){
    var uploader = new qq.FileUploader({
                        element: document.getElementById('upload_file'),
                        action: '/functions/planning/includes/upload_bestand.php',
                        debug: true,
                        params: { map: '<?php echo $activiteit_id; ?>'},
                        listElement: document.getElementById('custom-queue'),
                        extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]],
                        onComplete: function(id, fileName, responseJSON){
                            jQuery.ajax({
                            type : 'GET',
                            url : '/functions/planning/includes/activiteit_check.php',
                            dataType : 'html',
                            data: {
                                action : 'refreshFiles',
                                activiteit: '<?php echo $activiteit_id; ?>'
                            },
                            success : function(data){
                                jQuery('#detail_bestanden_lijst').html(data);
                                jQuery("#custom-queue").html('');
                            }
                        });
                        
                        return false;
                        },
                    });
    jQuery('#add_detail_tag_input').keydown(function(event) {
            if (event.keyCode == '13' || event.keyCode == '188') {
                jQuery.ajax({
                    type : 'POST',
                    url : '/functions/planning/includes/activiteit_check.php',
                    dataType : 'html',
                    data: {
                        action : 'tag',
                        tag : jQuery('#add_detail_tag_input').val(),
                        activiteit: '<?php echo $activiteit_id ?>'
                    },
                    success : function(data){
                        jQuery('#add_detail_tag_input').val(""),
                        jQuery('#detail_tags_lijst').html(data);
                        jQuery(".wijzig_info_success").fadeOut(5000);
                    }
                });

                return false;
            }
        });
	
	jQuery('#opslaan_reactie').on('click', function(){
		jQuery.ajax({
                    type : 'POST',
                    url : '/functions/planning/includes/activiteit_check.php',
                    dataType : 'html',
                    data: {
                        action : 'reactie',
                        reactie : jQuery('#detail_reactie').val(),
                        activiteit_id: '<?php echo $activiteit_id ?>'
                    },
                    success : function(data){
                        jQuery('#detail_reactie').val(""),
                        jQuery('#reactie_formulier').hide();
                        jQuery('#reacties_lijst').html(data);
                    }
                });return false;
	});
	jQuery('#image_rij img').on('click', function(){
               var prio = jQuery(this).attr('alt').split(' ')[1];
		jQuery.ajax({
					type : 'GET',
					url : '/functions/planning/includes/prio_ajax.php',
					dataType : 'html',
					data: {action: 'prioWijzigen', activiteit_id : '<?php echo $activiteit_id; ?>', prioriteit : prio, reload : 1},
                    success : function(data){
                        jQuery('#prioriteit').html(data);
                        jQuery('#image_rij img').on('click', function(){
                           var prio = jQuery(this).attr('alt').split(' ')[1];
                                jQuery.ajax({
                                            type : 'GET',
                                            url : '/functions/planning/includes/prio_ajax.php',
                                            dataType : 'html',
                                            data: {action: 'prioWijzigen', activiteit_id : '<?php echo $activiteit_id; ?>', prioriteit : prio, reload : 1},
                                            success : function(data){
                                                jQuery('#prioriteit').html(data);
                                            }
                                })
                        });
                    }
        })
   });
   
   jQuery('#detail_delen_medewerker_field').on('click', function(){
              laadGebruikers();
             console.log(toegevoegde_gebruikers + 'xx');
        });
   jQuery('#detail_delen_versturen').on('click', function(){
       var delen_met = '';
       if(toegevoegde_gebruikers.length === 0){delen_met = null}
       else if(toegevoegde_gebruikers.length === 1){delen_met = toegevoegde_gebruikers[0];}
       else{
            var i = 0;
            while(i < toegevoegde_gebruikers.length){
                if(i === 0){delen_met += toegevoegde_gebruikers[i];}
                else{delen_met += '_' + toegevoegde_gebruikers[i] ;}
                i++;
            }
        } 
        //alleen als er een gebruiker is aangegeven, mag dit worden verstuurd
        if(delen_met != null){
            jQuery.ajax({
                type : 'POST',
                url : '/functions/planning/includes/activiteit_check.php',
                dataType : 'html',
                data: {
                    action: 'delen_check', 
                    activiteit : '<?php echo $activiteit_id; ?>', 
                    gebruikers : delen_met,
                    toelichting : jQuery('#detail_delen_beschrijving_field').val()
                },
                success : function(data){
                    jQuery('#detail_delen_beschrijving_field').val('');
                    laadGebruikers(); toegevoegde_gebruikers = jQuery.grep(toegevoegde_gebruikers, function(n, i){ return (n != 0);});
                    jQuery('#detail_opties_callback').html(data).fadeOut(8000);
           }});return false;
       }
    });
jQuery('#detail_opties').on('click', function(){
    jQuery('#detail_opties_overzicht').slideToggle();
});
jQuery('#add_detail_tag').on('click', function(){
    jQuery('#detail_tags_form').fadeToggle();
});
jQuery('#add_detail_file').on('click', function(){
    jQuery('#detail_bestanden_form').fadeToggle();
});
jQuery('#add_detail_reactie').on('click', function(){
    jQuery('#reactie_formulier').fadeToggle();
});

jQuery("#detail_delen_medewerker_field").autocomplete({
	source: '/functions/planning/includes/filter_ajax.php?filter=gebruiker',
	minLength:2,
	select: function( event, ui ){
	    
	    //wordt dit al gedeeld met een gebruiker ?
	    if(jQuery.inArray(ui.item.id, toegevoegde_gebruikers) === -1){
    		jQuery('#detail_delen_gebruikers').append(
        		'<div id="gebruiker_'+ ui.item.id +'" class="gebruiker_filter">'+
        		'<span id="gebruiker_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
        		'<div id="gebruiker_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'gebruiker\')">X</span></div>'+
        		'</div>'
        	);
    		jQuery("#detail_delen_medewerker_field").val('');
		}else{
		    jQuery("#detail_delen_medewerker_field").val('');
		}
		laadGebruikers();
		return false;
	}
}); 
});
function laadGebruikers(){
var gebruikerDump = jQuery('#detail_delen_gebruikers').children('.gebruiker_filter');
gebruikerDump.children('#gebruiker_te_filteren').each(function() { var gebruikerId = jQuery(this).html();if(jQuery.inArray(gebruikerId, toegevoegde_gebruikers) < 0){toegevoegde_gebruikers.push(gebruikerId);} });
}//console.log(toegevoegde_gebruikers);

function deleteReactie(reactieId){
		jQuery.ajax({
                    type : 'POST',
                    url : '/functions/planning/includes/activiteit_check.php',
                    dataType : 'html',
                    data: { action : 'delete_reactie', reactie_id: reactieId, activiteit_id: '<?php echo $activiteit_id ?>' },
                    success : function(data){
                        jQuery('#reacties_lijst').html(data);
                    }
                });return false;
	};
function deletetag(tagId) {
        jQuery.ajax({
            type : 'GET',
            url : '/functions/planning/includes/activiteit_check.php',
            dataType : 'html',
            data: {
                action : 'delete_tag',
                tag : tagId,
                activiteit: '<?php echo $activiteit_id ?>'
            },
            success : function(data){
                jQuery('#detail_tags_lijst').html(data);
                jQuery(".wijzig_info_success").fadeOut(5000);
            }
        });

        return false;
};
function deleteFile(fileId) {
        jQuery.ajax({
            type : 'GET',
            url : '/functions/planning/includes/activiteit_check.php',
            dataType : 'html',
            data: {
                action : 'delete_file', 
                activiteit : '<?php echo $activiteit_id ?>',
                bestand_id : fileId
            },
            success : function(data){
                jQuery('#detail_bestanden_lijst').html(data);
                jQuery("#custom-queue").html('');
            }
        });
                        
        return false;
};
</script>

<?php 
                                //als de sessie voor meepraten is gezet, laad dan de fancybox.
                                if($_GET['reactie_wijzigen'] == 1){
                                    echo '
                                    <script type="text/javascript">
                                        function goToAnchor(nameAnchor){window.location.hash=nameAnchor;}
                                    </script>
                                    
}
                                    <a href="javascript:goToAnchor('."'".$_GET['reactie_id']."'".')" id="reactie_wijzigen" style="display:none;">
                                        Check of er wel meegepraat mag worden.
                                    </a>
                                    <script type="text/javascript">
                                        function LaunchFancyBox() { $("#reactie_wijzigen").trigger('."'".'click'."'".'); }; $(document).ready(LaunchFancyBox);    
                                    </script>
                                    ';
                                }
?>

<?php 
    if(isset($_SESSION['verstuurd_naar'])){
            if(count($_SESSION['verstuurd_naar'] > 1)){
                $verstuurd_naar_lijst = '<p id="succesvol_verstuurd_meer" style="color:green"> U hebt dit artikel succesvol gedeeld met :';
                foreach($_SESSION['verstuurd_naar'] as $verstuurd_naar){
                    $verstuurd_naar_lijst .= ' <b>'.$verstuurd_naar.'<b>, ';
                }
                $verstuurd_naar_lijst .= 'xys'; $verstuurd_naar_lijst = str_replace(', xys', '', $verstuurd_naar_lijst);
            }else{
                $verstuurd_naar_lijst = '<p id="succesvol_verstuurd_een" style="color:green"> U hebt dit artikel succesvol gedeeld met';
                foreach($_SESSION['verstuurd_naar'] as $verstuurd_naar){
                    $verstuurd_naar_lijst .= ' <b>'.$verstuurd_naar.'</b>';
                }
            }
        echo $verstuurd_naar_lijst.'</p>';
        unset($_SESSION['verstuurd_naar']);
    }elseif(isset($_SESSION['gewijzigde_prio'])){
        //haal het prioriteitnummer op
        $prio_id = $_SESSION['gewijzigde_prio'];
        //vertaal deze nummers naar tekst
        if($prio_id == 1){$prioriteit = 'de hoogste prioriteit';}elseif($prio_id == 2){$prioriteit = 'de hoge prioriteit';}
        elseif($prio_id == 3){$prioriteit = 'de normale prioriteit';}elseif($prio_id == 4){$prioriteit = 'de lage prioriteit';}
        //pomp deze tekst in de onderstaande zin, die fade wanneer getoond
        echo '<p id="gewijzigde_prio" style="color:green">U hebt de prioriteit van deze activiteit gewijzigd naar <b>'.$prioriteit.'</b></p>';
        unset($_SESSION['gewijzigde_prio']);
    }
?>
    <div id="activiteit_detail">
    	<div id="detail_top">
    		<div id="detail_status">
		    	<h2>
		    		<?php echo $info['status']; ?>
		    	</h2>	
	    		<?php 
				if($info['status'] == 'done'){
	                $gebruiker =  '<span id="done_datum">'.$info['status_datum'].'</span>';
	             }
	             //is er iemand die hier mee bezig is ?
	             elseif(isset($in_behandeling_door)){
	                //maar is dit dezelfde persoon als die het bekijkt.
	                //maak het dan g��n link naar het profiel
	                if($info['in_behandeling_door'] != $login_id){
	                    $gebruiker =  '<span id="ditbenik">'.$in_behandeling_door['voornaam'].' '. $in_behandeling_door['achternaam'].'</span>';
	                }
	                //anders wordt het een link naar het profiel van deze persoon.
	                else{
	                    $gebruiker = '
	                    <a href="/profiel/'.$in_behandeling_door['gebruikersnaam'].'" title="ga naar profiel" target="_blank">'.
	                        $in_behandeling_door['voornaam'].' '. $in_behandeling_door['achternaam']
	                    .'</a>';
	                }
	            
	                if(isset($acceptatie_door)){
	                    if($info['acceptatie_door'] == $login_id){
	                        $gebruiker =  '<span id="ditbenik">'.$acceptatie_door['voornaam'].' '. $acceptatie_door['achternaam'].'</span>';
	                    }else{
	                        $gebruiker =  '
	                        <a href="/profiel/'.$acceptatie_door['gebruikersnaam'].'" title="ga naar profiel" target="_blank">'.
	                            $acceptatie_door['voornaam'].' '. $acceptatie_door['achternaam']
	                        .'</a>';
	                    }
	                }
	        		
	             }
	             else{
	                echo '&nbsp;';
	             }
	             if($gebruiker != null){
	             	echo '<div id="detail_gebruiker">'.$gebruiker.'</div>';
	             }
	           ?>
	    	</div>
		    <div id="detail_titel">
		        <h2>
		            <span id="detail_werkzaamheden"><?php echo $info['werkzaamheden']; ?></span>
		            <span id="detail_project"><?php echo $info['project']; ?></span>
		        </h2>
		        <button id="detail_opties" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Opties</button>
		        
			
		    </div>
	    </div>
        <div id="detail_left">
           <div id="detail_klant"><?php echo $info['naam']; ?></div>
           <!--<div id="detail_project"><?php echo $info['project']; ?></div>-->
          
            <!--
            <div id="detail_tabel">
            <table border="0" cellspacing="0" cellpadding="0">
               <thead>
                  <th id="detail_competentie_header">Competentie</th>
                  <th id="detail_aantal_uur_header">Uren</th>
                  <th id="detail_deadline_header">Deadline</th>
               </thead>
               <tbody>
                  <tr>
                      <td id="detail_competentie"><?php echo $info['competentie']; ?></td>
                      <td id="detail_aantal_uur"><?php echo $info['uur_aantal']; ?></td>
                      <td id="detail_deadline"><?php echo $info['iteratie_einddatum']; ?></td>
                  </tr>
               </tbody>
            </table>
            </div>-->
            	<ul id="detail_tabel">
            		<li><span id="detail_competentie_header" class="detail_list_header">Competentie</span><span id="detail_competentie"><?php echo $info['competentie'] ?></span></li>
            		<li><span id="detail_aantal_uur_header" class="detail_list_header">Uren</span><span id="detail_aantal_uur"><?php echo $info['uur_aantal'] ?></span></li>
            		<li><span id="detail_deadline_header" class="detail_list_header">Deadline</span><span id="detail_deadline"><?php echo $info['iteratie_einddatum'] ?></span></li>
            	</ul>
           
        </div>
        <div id="detail_opties_overzicht" style="display:none;">
            <div id="detail_opties_callback"></div>                
        	<div id="detail_opties_links">
        		<p class="detail_opties_header">Ik wil deze activiteit...:</p>
        		<ul id="activiteit_verplaatsen">
        			<?php 
        			if($info['status'] != 'to do'){
        				// als de activiteit net is aangemaakt, al klaar is, of ergens anders in de planning staat.
        				// brengt de activiteit naar de huidige iteratie
        				echo '<li><span id="activiteit_todo">In to do zetten</span></li>';
					}
					if($info['status'] != 'onderhanden' && $info['in_acceptatie_door'] == 0){
						// als de activiteit niet onderhanden is en niet in acceptatie bij iemand loopt.
						echo '<li><span id="activiteit_onderhanden">Onderhanden nemen</span></li>';
					}
					if(($info['status'] == 'acceptatie' || $info['status'] == 'to do') && $info['in_acceptatie_door'] == 0){
						// als de activiteit wordt uitgevoerd, of nog niet in acceptatie is bij iemand.
						echo '<li><span id="activiteit_acceptatie">In acceptatie nemen</span></li>';
					}
					if($info['status'] != 'done'){
						// als de activiteit al klaar is, hoef je 'm niet weer af te ronden.
						// anders: kan dat altijd.
						// wordt dan verplaatst naar de done van de huidige iteratie.
						echo '<li><span id="activiteit_afronden">Afronden</span></li>';
					}
        			?>
        			<li><span id="activiteit_verwijderen">Verwijderen</span></li>
        		</ul>
        	</div>
        	<div id="detail_opties_center">
        		<p class="detail_opties_header">De nieuwe prioriteit moet worden:</p>
        	    <div id="prioriteit">
                    <p style="font-weight:700;">Wat voor prioriteit wilt u geven aan deze activiteit ?</p>
<?php
$active_prioriteit = 'class="active_prio"';
$inactive_prioriteit = 'class="inactive_prio" onmouseover="this.className='."'hover_prio'".'" onmouseout="this.className='."'inactive_prio'".'"';
if($info['prioriteit'] == '1'){$prioriteit_1 = $active_prioriteit;}else{$prioriteit_1 = $inactive_prioriteit;}
if($info['prioriteit'] == '2'){$prioriteit_2 = $active_prioriteit;}else{$prioriteit_2 = $inactive_prioriteit;}
if($info['prioriteit'] == '3'){$prioriteit_3 = $active_prioriteit;}else{$prioriteit_3 = $inactive_prioriteit;}
if($info['prioriteit'] == '4'){$prioriteit_4 = $active_prioriteit;}else{$prioriteit_4 = $inactive_prioriteit;}
?>                  
                    <div id="image_rij">
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_1.png" alt="prio 1" <?php echo $prioriteit_1; ?> />
                        </div>
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_2.png" alt="prio 2" <?php echo $prioriteit_2; ?> />                            
                        </div>
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_3.png" alt="prio 3" <?php echo $prioriteit_3; ?> />
                        </div>
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_4.png" alt="prio 4" <?php echo $prioriteit_4; ?> />
                        </div>
                    </div>
                    <div id="beschrijving_rij">
                        <div class="prio_beschrijving"> <b>Hoogste prioriteit</b><br /> Direct een blokkerend probleem. </div>
                        <div class="prio_beschrijving"> <b>Hoge prioriteit</b><br /> Op korte termijn een blokkerend probleem als we nu niets doen. </div>
                        <div class="prio_beschrijving"> <b>Normale prioriteit</b><br /> Belangrijk maar niet blokkerend. </div>
                        <div class="prio_beschrijving"> <b>Lage prioriteit</b><br /> Niet blokkerend, cosmetisch of gewoon mooi. </div>                
                    </div>
                </div>
        	</div>
        	<div id="detail_opties_rechts">
        		<p class="detail_opties_header">Ik wil deze activiteit delen met:</p>
        		<div id="detail_delen_met">
	                <label for="detail_delen_medewerker_field" style="opacity: 1;">Deze medewerker</label>
	                <input type="text" class="textfield" id="detail_delen_medewerker_field" />
	                
	                <div id="detail_delen_gebruikers"></div>
	                
	                <label for="detail_delen_beschrijving_field">Eventuele beschrijving</label>
	                <textarea class="textarea" id="detail_delen_beschrijving_field" cols="65" rows="4"></textarea>
	                
	                <button id="detail_delen_versturen" class="button"  onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Versturen</button>
        		</div>
        	</div>
        </div>
        <div id="detail_center">
        	
            <div id="html_tekst"><?php echo $info['html_detail']; ?></div>
        </div>
        <div id="detail_right">
            <div id="detail_tags">
                <div id="detail_tags_header">
                    <h3>Tags</h3>
                    <button id="add_detail_tag" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Tag toevoegen</button>
                </div>
                <div id="detail_tags_form" style="display:none">
                	<label class="label" for="add_detail_tag_input">Tag</label>
        			<input type="text" id="add_detail_tag_input" class="textfield" />
                </div>
                <div id="detail_tags_lijst">
                    <?php
                        if($aantal_tags > 0){
                            while($row = mysql_fetch_array($result_tags)){
                                $tag_list[] = 
                                '<div class="tag">
                                    <a class="tag_zoeken" href="?function=-zoek&query='.$row['naam'].'" target="_blank" title="zoek naar tag">'.$row['naam'].'</a>
                                    <span class="delete_tag" onmouseover="this.className=\'delete_tag delete_tag_hover\'" onmouseout="this.className=\'delete_tag\'" onclick="deletetag('.$row['id'].')"> X </span>
                                </div>';
                            }
                        }
                        if(count($tag_list) > 0){
                            foreach($tag_list as $tag){
                                echo $tag;
                            }
                        }else{
                            echo 'er zijn nog geen tags voor deze activiteit';
                        }
                    ?>
                </div>
            </div>
            <div id="detail_bestanden">
                <div id="detail_tags_header">
                    <h3>Bestanden</h3> 
                    <button id="add_detail_file" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Bestand toevoegen</button>
                </div>
                <div id="detail_bestanden_form" style="display:none;">
                	<div id="status-message"></div>
        			<ul id="custom-queue"></ul>
        			<div id="upload_file"></div>
        			<div class="qq-upload-extra-drop-area">Drop files here too</div>
                </div>
                <div id="detail_bestanden_lijst">
                    <?php
                        if($aantal_bestanden > 0){
                            while($row = mysql_fetch_array($result_bestanden)){
                                echo '
                                <div class="bestand" onmouseover="this.className='."'bestand_hover'".'" onmouseout="this.className='."'bestand'".'">
                                    <a class="bestand_link" href="/files/planning_documenten/'.$activiteit_id.'/'.$row['bestand'].'" target="_blank" title="bekijk bestand">'.$row['bestand'].'</a>
                                    <span class="verwijder_bestand" onclick="deleteFile('.$row['id'].')">verwijderen</span>
                                </div>';
                            }
                        }else{
                            echo 'Er zijn nog geen bestanden ge&uuml;pload bij deze activiteit';
                        }
                    ?>
                </div>
            </div>
           </div>
        </div>
        <div id="detail_bottom">
        <div id="reacties">
            <div id="reacties_header">
                    <h3>Reacties</h3>
                    <button id="add_detail_reactie" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Reactie toevoegen</button>
                    <!--<a href="javascript:toggle();" id="toon_formulier" title="reactie toevoegen">Reactie toevoegen</a>-->
                    <div id="reactie_formulier" style="display: none">    
                    	<p>Voeg een reactie toe:</p>
						<textarea class="textarea" name="reactie" id="detail_reactie" cols="95" rows="10"></textarea>
						<button id="opslaan_reactie" class="button" onmouseover="this.className = \'button btn_hover\'" onmouseout="this.className = \'button\'">Opslaan</button>                
                    </div>
            </div>
            <div id="reacties_lijst">
		<?php 
			if($aantal_reacties > 0){
				while($reactie = mysql_fetch_array($result_reacties)){
	                //haal hier de foto van de reageerder op.. in het geval van een lege profielfoto het beste
	                if($reactie['profielfoto'] != null){
	                        $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/'.$reactie['album'].'/'.$reactie['profielfoto'].'" alt="de profielfoto" title="bekijk het profiel" />';
	                }else{
	                        $image =  '<img src="'.$etc_root.'lib/slir/'.slirImage(80,80).$etc_root.'files/album/profile-empty.jpg" alt="de profielfoto" title="bekijk het profiel" />';
	                }
					if($i == 0){ $class= 'en_om'; $i = 1; }else{ $class= 'om'; $i = 0; }?>
			<div class="reactie <?php echo $class; ?>" onmouseover="this.className='reactie reactie_hover om'" onmouseout="this.className='reactie om'">
            	<div class="reactie_links">
                	<div class="reactie_foto">
                  		<div class="reactie_profiel_pic">
                    		<a href="/profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel"><?php echo $image; ?></a>
                    		<div class="reactie_profiel_pic_bottom">&nbsp;</div>
                  		</div>
                	</div>
            	</div>
            	<div class="reactie_rechts">
                	<div class="reactie_header">
	                    <p>
                        	<span class="auteur">
                        		<a href="/profiel/<?php echo $reactie['gebruikersnaam']; ?>" title="bekijk het profiel"><?php echo $reactie['voornaam'].' '.$reactie['achternaam'] ?></a>
                        	</span>
                        	<span class="datum"><?php echo $reactie['geschreven_op'] ?></span>                  
                        	<span class="delete"> <img id="delete_reactie" src="/functions/planning/css/images/delete.png" alt="delete" onclick="deleteReactie(<?php echo $reactie['id']; ?>)"> </span>
                        	<!--<span class="wijzig">
                            	<a href="/planning/activiteit-wijzigen/activiteit-id=381/reactie-wijzigen/992#992" name="992" class="tooltip">
                            		<img src="/functions/planning/css/images/edit.png" alt="delete">
                            	</a>
                        	</span>-->                    
                    	</p>
                	</div>                  
                	<div class="reactie_tekst">
                        <p><?php echo $reactie['inhoud']; ?></p>
                    </div>          
                </div>
        	</div>
				<?php }
			}
		?>       
            </div>
           </div>
        </div>
    </div>
</div>