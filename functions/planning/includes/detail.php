<?php
    //dit gaan we nog veel nodig hebben.
    $detail = $_GET['detail'];
    if($detail == 'activiteit'){ $activiteit_id = $_GET['activiteit_id']; }
    elseif($detail == 'project'){ $project_id = $_GET['project_id']; }
?>
<script>
var toegevoegde_gebruikers = [], detail = '<?php echo $detail; ?>', id = '<?php if($detail == 'activiteit'){ echo $activiteit_id; } elseif($detail == 'project'){ echo $project_id; }; ?>';
jQuery(function(){
        var uploader = new qq.FileUploader({
                            element: document.getElementById('upload_file'),
                            action: '/functions/planning/includes/upload_bestand.php',
                            debug: true,
                            <?php 
                                if($detail == 'activiteit'){echo "params: { hoofdmap: 'planning_documenten', map: '$activiteit_id'},";}
                                elseif($detail == 'project'){echo "params: { hoofdmap: 'project_documenten', map: '$project_id'},";}
                            ?>
                            listElement: document.getElementById('custom-queue'),
                            //extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]],
                            onComplete: function(id, fileName, responseJSON){
                                jQuery.ajax({
                                type : 'GET',
                                url : ' /functions/planning/includes/check.php',
                                dataType : 'html',
                                data: {
                                    action : 'refreshFiles',
                                    <?php 
                                        if($detail == 'activiteit'){echo "activiteit: '$activiteit_id'";}
                                        else{echo "project: '$project_id'";}
                                    ?>
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
                        url : ' /functions/planning/includes/check.php',
                        dataType : 'html',
                        data: {
                            action : 'tag',
                            tag : jQuery('#add_detail_tag_input').val(),
                            <?php 
                                if($detail == 'activiteit'){echo "activiteit: '$activiteit_id'";}
                                else{echo "project: '$project_id'";}
                            ?>
                        },
                        success : function(data){
                            jQuery('#add_detail_tag_input').val("");
                            jQuery('#detail_tags_lijst').html(data);
                            jQuery(".wijzig_info_success").fadeOut(5000);
                        }
                    });return false;
                }
            });
        
        jQuery('#opslaan_reactie').on('click', function(){
            jQuery.ajax({
                        type : 'POST',
                        url : ' /functions/planning/includes/check.php',
                        dataType : 'html',
                        data: {
                            action : 'reactie',
                            reactie : jQuery('#detail_reactie').val(),
                            <?php 
                                if($detail == 'activiteit'){echo "activiteit_id: '$activiteit_id'";}
                                else{echo "project_id: '$project_id'";}
                            ?>
                        },
                        success : function(data){
                            jQuery('#detail_reactie').val("");
                            jQuery('#reactie_formulier').hide();
                            jQuery('#reacties_lijst').html(data);
                        }
                    });return false;
        });
        <?php if($detail == 'activiteit'){ //een project heeft geen prio ?>
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
                                                data: {
                                                    action: 'prioWijzigen', 
                                                    activiteit_id : '<?php echo $activiteit_id; ?>', 
                                                    prioriteit : prio, 
                                                    reload : 1
                                                },
                                                success : function(data){ jQuery('#prioriteit').html(data); }
                                    })
                            });
                        }
            })
       });
       <?php } ?>
       
       jQuery('#detail_delen_medewerker_field').on('click', function(){ laadGebruikers(); });
       
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
                url : ' /functions/planning/includes/check.php',
                dataType : 'html',
                data: {
                    action: 'delen_check',
                    <?php 
                        if($detail == 'activiteit'){echo "activiteit: '$activiteit_id',";}
                        else{echo "project: '$project_id',";}
                    ?> 
                    gebruikers : delen_met,
                    toelichting : jQuery('#detail_delen_beschrijving_field').val()
                },
                success : function(data){
                    jQuery('#detail_delen_beschrijving_field').val('');
                    jQuery('#detail_delen_gebruikers').empty();
                    laadGebruikers(); toegevoegde_gebruikers = jQuery.grep(toegevoegde_gebruikers, function(n, i){ return (n != 0);});
                    jQuery('#detail_opties_callback').html(data).fadeOut(8000);
           }});return false;
       }
   });
    
   jQuery('#detail_opties').on('click', function(){        jQuery('#detail_opties_overzicht').slideToggle(); });
   jQuery('#add_detail_tag').on('click', function(){       jQuery('#detail_tags_form').fadeToggle(); });
   jQuery('#add_detail_file').on('click', function(){      jQuery('#detail_bestanden_form').fadeToggle(); });
   jQuery('#add_detail_reactie').on('click', function(){   jQuery('#reactie_formulier').fadeToggle(); });
   
   jQuery('#activiteit_verplaatsen li').on('click', function(){ detail_acties(jQuery(this).attr('data-action')); }) 
   
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
            }else{ jQuery("#detail_delen_medewerker_field").val(''); }
            laadGebruikers();
            return false;
        }
   }); 
   re_loadReactie();
});//einde Self Invoking Annonymous Function
function laadGebruikers(){
var gebruikerDump = jQuery('#detail_delen_gebruikers').children('.gebruiker_filter');
gebruikerDump.children('#gebruiker_te_filteren').each(function() { var gebruikerId = jQuery(this).html();if(jQuery.inArray(gebruikerId, toegevoegde_gebruikers) < 0){toegevoegde_gebruikers.push(gebruikerId);} });
}//console.log(toegevoegde_gebruikers);

function deleteFilter(id, filter){
    jQuery('#' + filter + '_' + id).remove();
    laadGebruikers(); toegevoegde_gebruikers = jQuery.grep(toegevoegde_gebruikers, function(n, i){ return (n != id);});
}

function detail_acties( actie ){
    if( actie === 'activiteit_wijzigen' || actie === 'project_wijzigen'){
        var url = '/functions/planning/includes/detail_dialog.php?action=' + actie + '&id=' + id;
        <?php if($detail == 'activiteit'){ $minWidth = 610; } elseif($detail == 'project'){ $minWidth = 610; } ?>
        jQuery('#dialog').load(url).dialog({ minWidth: <?php echo $minWidth; ?>, minHeight: 200, height:250, title: 'Wijzig ' + detail }); 
    }else{
        jQuery.ajax({
                type : 'POST',
                url : ' /functions/planning/includes/check.php',
                dataType : 'html',
                data: {
                    action: actie,
                    <?php 
                        if($detail == 'activiteit'){echo "id: '$activiteit_id'";}
                        else{echo "id: '$project_id'";}
                    ?> 
                },
                success : function(data){
                    if(actie === 'activiteit_delete' || actie === 'project_delete'){
                        jQuery('#dialog').html(data).dialog({ 
                            minWidth: 200, 
                            minHeight: 200, 
                            height:200, 
                            title: 'Succesvol verwijderd',
                            create: function(event, ui){
                                setTimeout("window.location.replace('<?php echo $site_name ?>planning/dashboard')",2000)
                            } 
                        });
                    }
                    else{
                        jQuery('#dialog').html(data).dialog({ 
                            minWidth: 200, 
                            minHeight: 200, 
                            height:200, 
                            title: 'Succesvol gewijzigd',
                            create: function(event, ui){
                                setTimeout("window.location.reload()",2000)
                            } 
                        });
                    }
           }});return false
    }
}

function re_loadReactie(){
    jQuery.ajax({
        type : 'POST',
        url : ' /functions/planning/includes/check.php',
        dataType : 'html',
        data: { 
            action : 'refreshReacties', 
            <?php 
                if($detail == 'activiteit'){echo "activiteit_id: '$activiteit_id'";}
                else{echo "project_id: '$project_id'";}
            ?> 
         },
        success : function(data){ 
			jQuery('#loading_img').hide(); jQuery('#reacties_lijst').html(data);
		    jQuery('#reacties_lijst .reactie span.wijzig').on('click', function(){
	   			var id = jQuery(this).attr('data-id'), url = '/functions/planning/includes/detail_dialog.php?action=reactie_wijzigen&id=' + id;
		        jQuery('#dialog').load(url).dialog({ minWidth: 610, minHeight: 200, height:200, title: 'Wijzig reactie'}); 
	   		});
        }
    });return false;
}
function deleteReactie(reactieId){
    jQuery.ajax({
        type : 'POST',
        url : ' /functions/planning/includes/check.php',
        dataType : 'html',
        data: { 
            action : 'delete_reactie', 
            reactie: reactieId,
            <?php 
                if($detail == 'activiteit'){echo "activiteit_id: '$activiteit_id'";}
                else{echo "project_id: '$project_id'";}
            ?> 
         },
        success : function(data){ jQuery('#reacties_lijst').html(data); }
    });return false;
};

function deletetag(tagId) {
    jQuery.ajax({
        type : 'GET',
        url : ' /functions/planning/includes/check.php',
        dataType : 'html',
        data: {
            action : 'delete_tag',
            tag : tagId,
            <?php 
                if($detail == 'activiteit'){echo "activiteit: '$activiteit_id'";}
                else{echo "project: '$project_id'";}
            ?>
        },
        success : function(data){
            jQuery('#detail_tags_lijst').html(data);
            jQuery(".wijzig_info_success").fadeOut(5000);
        }
    });return false;
};

function deleteFile(fileId) {
    jQuery.ajax({
        type : 'GET',
        url : ' /functions/planning/includes/check.php',
        dataType : 'html',
        data: {
            action : 'delete_file', 
            <?php 
                if($detail == 'activiteit'){echo "activiteit: '$activiteit_id',";}
                else{echo "project: '$project_id',";}
            ?>
            bestand_id : fileId
        },
        success : function(data){
            jQuery('#detail_bestanden_lijst').html(data);
            jQuery("#custom-queue").html('');
        }
    });return false;
};
</script>

<?php
if($detail == 'activiteit'){
    //haal alles op
    $what=" a.werkzaamheden,
            a.uur_aantal,
            a.status,
            a.project AS project_id,
            DATE_FORMAT(a.status_datum, '%d %M %Y') status_datum,
            a.in_behandeling_door,
            a.acceptatie_door,
            a.html_detail,
            a.prioriteit,
            b.titel AS project_titel,
            c.naam,
            DATE_FORMAT(d.datum, '%d %M %Y') iteratie_einddatum,
            e.competentie";
    $from=" planning_activiteit a
            LEFT JOIN project AS b ON (b.id = a.project)
            LEFT JOIN organisatie AS c ON (c.id = b.organisatie AND c.actief = 1)
            LEFT JOIN planning_iteratie AS d ON (d.id = a.iteratie)
            LEFT JOIN competentie AS e ON (e.id = a.competentie)";
    $where ="a.id= '$activiteit_id' AND a.actief = 1 ";
    $wijzigen_result = sqlSelect($what,$from,$where);
    $info = mysql_fetch_assoc($wijzigen_result);
    
    //echo "SELECT $what FROM $from WHERE $where" ;
    $table='planning_recent'; $what='gezien = 1'; $where="planning_activiteit_id = '$activiteit_id' AND gebruiker_id = $login_id";
        $update_recentmelding_voor_deze_activiteit = sqlUpdate($table, $what, $where);


 	//voor de navigatie vooruit en terug:
	$sql = 'SELECT
				MIN(a.id) AS eerste,
				MAX(a.id) AS laatste,
				MAX(b.id) AS vorige,
				MIN(c.id) AS volgende
			FROM  
	        	planning_activiteit a
				LEFT JOIN planning_activiteit AS b ON ( b.id < '.$activiteit_id.' AND b.project = '.$info['project_id'].' AND b.actief = 1)
				LEFT JOIN planning_activiteit AS c ON ( c.id > '.$activiteit_id.' AND c.project = '.$info['project_id'].' AND c.actief = 1)
			WHERE
				a.actief = 1 AND a.project = '.$info['project_id'].' LIMIT 1 ';

	$nav_res = mysql_query($sql) or die(mysql_error());
	$nav = mysql_fetch_assoc($nav_res);
	
	// de navigatie.
	$min = $nav['eerste']; $max = $nav['laatste'];
	$prev = $nav['vorige']; $next = $nav['volgende'];
	
	if($min  == null){$min  = "''";}  if($max  == null){$max  = "''";}
	if($prev == null){$prev = "''";}  if($next == null){$next = "''";}		
					
	$sql = 'SELECT
				a.werkzaamheden AS eerste_titel,
				b.werkzaamheden AS vorige_titel,
				c.werkzaamheden AS volgende_titel,
				d.werkzaamheden AS laatste_titel
			FROM  
	        	planning_activiteit a
	        	LEFT JOIN planning_activiteit AS b ON ( b.id = '.$prev.' AND b.actief = 1)
				LEFT JOIN planning_activiteit AS c ON ( c.id = '.$next.' AND c.actief = 1)
				LEFT JOIN planning_activiteit AS d ON ( d.id = '.$max.' AND d.actief = 1)
			WHERE a.id = '.$min.'  AND a.actief = 1 LIMIT 1';
	$nav_res = mysql_query($sql) or die(mysql_error());
	$nav2 = mysql_fetch_assoc($nav_res);
			
    //voor de tabs wil ik weten hoeveel reacties, bestanden en tags er zijn. Haal dus alles al hier op en doe later de while-loop.
    
    //echo "SELECT $what_reacties FROM $from_reacties WHERE $where_reacties" ;
    //bestaat de bestanden map al voor deze activiteit ?
    $path = $_SERVER['DOCUMENT_ROOT'].$etc_root.'files/planning_documenten/'.$activiteit_id.'/';
    if(is_dir($path)){}else{mkdir($path);}
    
    //bestand(en)
    $what_bestanden = 'id, bestand';
    $from_bestanden = 'planning_bestand';
    $where_bestanden = "activiteit = '$activiteit_id'";
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
    
	// ZET DEZE ACTIVITEIT OP RECENT VOOR DEZE GEBRUIKER
	
	$table = 'planning_recent';
    // Wanneer een activiteit op gezien = 0 staat... is het recent ;
    // En voor iedereen, behalve de wijzigaar zelf, is het nu recent ;)	
    $what = "id"; $where = 'activiteit = '.$activiteit_id.' AND gebruiker = '.$login_id;

    	$aantal = countRows($what, $table, $where);
	if($aantal <= 0){
		/*
		 * DE BEKIJKENDE GEBRUIKER, IS NOG NIET OPGENOMEN ALS ACTIEVE GEBRUIKER. DOE DAT NU
		 * */
    	$what = 'activiteit, gebruiker, gezien, gezien_op';
		$with_what = "$activiteit_id, $login_id, 1, NOW()";
			$voor_het_eerst_gezien = sqlInsert($table, $what, $with_what);
	}else{
		/*
		 * DE BEKIJKENDE GEBRUIKER, IS OPGENOMEN ALS ACTIEVE GEBRUIKER, UPDATE DAT HIJ ER NU IN IS GEWEEST (+ ZET GEZIEN VOOR DE ZEKERHEID OP 1)
		 * */
		$what = 'gezien = 1, gezien_op = NOW()';
	  	$where = 'activiteit = '.$activiteit_id.' AND gebruiker = '.$login_id; 
	  		$update_het = sqlUpdate($table, $what, $where);	  		
	}
	
}//einde van activiteiten detail
elseif($_GET['detail'] == 'project'){
    //haal alles op
    $what=" a.titel,
            a.beschrijving,
            a.html_detail,
            a.afgerond,
            DATE_FORMAT(a.startdatum, '%d %M %Y') AS startdatum,
            DATE_FORMAT(a.einddatum, '%d %M %Y') AS einddatum,
            b.naam";
    $from=" project a
            LEFT JOIN organisatie AS b ON (b.id = a.organisatie AND b.actief = 1)";
    $where ="a.id= '".$_GET['project_id']."' AND a.actief = 1 ";
    $wijzigen_result = sqlSelect($what,$from,$where);
    $info = mysql_fetch_assoc($wijzigen_result);
    //echo "SELECT $what FROM $from WHERE $where" ;
    //$table='planning_recent'; $what='gezien = 1'; $where="planning_activiteit_id = '$activiteit_id' AND gebruiker_id = $login_id";
        //$update_recentmelding_voor_deze_activiteit = sqlUpdate($table, $what, $where);
        
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
    
   
    //bestaat de bestanden map al voor dit project ?
    $path = $_SERVER['DOCUMENT_ROOT'].$etc_root.'files/project_documenten/'.$project_id.'/';
    if(is_dir($path)){}else{mkdir($path);}
    //bestand(en)
    $what_bestanden = 'id, bestand';
    $from_bestanden = 'project_bestand';
    $where_bestanden = "project = $project_id";
    $aantal_bestanden = countRows($what_bestanden, $from_bestanden, $where_bestanden); $result_bestanden = sqlSelect($what_bestanden, $from_bestanden, $where_bestanden);
    
    //tag(s)
    $what_tags = 'a.id, b.naam';
    $from_tags = 'project_tag a, portal_tag b';
    $where_tags = "a.project = $project_id AND b.id = a.tag";
    $aantal_tags = countRows($what_tags, $from_tags, $where_tags); $result_tags = sqlSelect($what_tags, $from_tags ,$where_tags);
    }
?> 
    <div id="activiteit_detail">
    	<div id="detail_top">
    		<div id="detail_status">
		    <?php if($detail == 'activiteit'){ ?>
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
              }elseif($detail == 'project'){ 
    	    	    echo '<h2>Project</h2>';
                    if($info['afgerond'] == 1){echo '<div id="detail_gebruiker"><span id="afgerond-project">Afgerond</span></div>'; } 
              } ?>		    	
	    	</div>
		    <div id="detail_titel">
		        <h2>
	            <?php if($detail == 'activiteit'){ ?>
		            <span id="detail_werkzaamheden"><?php echo $info['werkzaamheden']; ?></span>
		            <span id="detail_project"><a href="/planning/detail/project-id=<?php echo $info['project_id']; ?>"><?php echo $info['project_titel']; ?></a></span>
	            <?php }elseif($detail == 'project'){ ?>
		            <span id="detail_werkzaamheden"><?php echo $info['titel']; ?></span>
		            <span id="detail_project"><?php echo $info['beschrijving']; ?></span>
                <?php }?>
		        </h2>
		        <button id="detail_opties" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Opties</button>
		        <?php
		        	if($detail == 'activiteit'){
					$href = $site_name.'planning/detail/activiteit-id=';
				?>
				<div id="activiteit_navigatie">					
					<div id="product_navigatie_prev">
					<?php 
						if($activiteit_id > $min){ $vorige =  $href.$prev; $terug_titel = $nav2['vorige_titel']; $title = 'Vorige serie bekijken'; }
						elseif($activiteit_id == $min){ $vorige = $href.$max;  $terug_titel = $nav2['laatste_titel']; $title = 'Laatste serie bekijken'; } 
					?>
					<a href="<?php echo $vorige; ?>" title="<?php echo $title; ?>"> &#171;  <?php echo $terug_titel; ?> </a>
					</div>
					<div id="product_navigatie_next">
					<?php 
						if($activiteit_id < $max){ $volgende = $href.$next; $volgende_titel = $nav2['volgende_titel']; $title = 'Volgende serie bekijken'; }
						elseif($activiteit_id == $max){ $volgende = $href.$min; $volgende_titel = $nav2['eerste_titel']; $title = 'Eerste serie bekijken'; } 
					?>
					<a href="<?php echo $volgende; ?>" title="<?php echo $title; ?>"><?php echo $volgende_titel; ?> &#187; </a>
					</div>
				</div>
				<?php 
		        }
		        ?>
				</div>
		        
		    </div>
	    </div>
        
        <div id="detail_left">
        <?php if($detail == 'activiteit'){?>
            <div id="detail_klant"><?php echo $info['naam']; ?></div>
            <ul id="detail_tabel">
                <li><span id="detail_competentie_header" class="detail_list_header">Competentie</span><span id="detail_competentie"><?php echo $info['competentie'] ?></span></li>
                <li><span id="detail_aantal_uur_header" class="detail_list_header">Uren</span><span id="detail_aantal_uur"><?php echo $info['uur_aantal'] ?></span></li>
                <li><span id="detail_deadline_header" class="detail_list_header">Deadline</span><span id="detail_deadline"><?php echo $info['iteratie_einddatum'] ?></span></li>
            </ul>
        <?php }elseif($detail == 'project'){ 
        
        $what = "DATE_FORMAT(startdatum, '%e %b %Y') AS startdatum, DATE_FORMAT(einddatum, '%e %b %Y') AS einddatum";
$from = 'project';
$where = "id = $project_id";
  $project_datum = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
        ?>
            <div id="project_datum">
                <?php if($info['startdatum'] != null){ ?>
                <span id="project_datum_vanaf"><?php echo $project_datum['startdatum']; ?></span>
                <?php }
                	  if($info['startdatum'] != null && $info['einddatum'] != null){ ?>
                <span id="project_datum_divider"> - </span>
                <?php }
                	  if($info['einddatum'] != null){ ?>
                <span id="project_datum_tot"><?php echo $project_datum['einddatum']; ?></span>
                <?php } ?>
            </div>
            <div id="project_competentie_overzicht">
            	<div id="project_competentie_header">
            		<div id="project_competentie_titel_header">Competentie</div>
            		<div id="project_competentie_uren_header">Gepland</div>
            	</div>
                <?php
                    $what = 'b.competentie,
                             b.id,
                             sum(a.uur_aantal) gepland_aant_uur';
                    $from = '
                             planning_activiteit a,
                             competentie b';
                    $where = '
                             a.project = '.$project_id.'
                             AND a.actief = 1
                             AND a.competentie = b.id
                             GROUP BY 1';
                    $competenties =  sqlSelect($what, $from, $where);
                    //echo  "SELECT $what FROM $from WHERE $where  <br />";
                    while($competentie = mysql_fetch_array($competenties)){?>
                    <div class="project_competentie">
                        <div class="project_competentie_titel"><?php echo $competentie['competentie']; ?></div>
                        <div class="project_competentie_uren"><?php echo $competentie['gepland_aant_uur']; ?></div>
                        
                    </div>    
                    <?php } ?>
            </div>
        <?php } ?>
             &nbsp;           
        </div>
        
        <div id="detail_opties_overzicht" style="display:none;">
            <div id="detail_opties_callback"></div>                
        	<div id="detail_opties_links">
        	<?php if($detail == 'activiteit'){?>
        	   <p class="detail_opties_header">Ik wil deze activiteit...:</p>
               <ul id="activiteit_verplaatsen">
                    <li data-action="activiteit_wijzigen"><span id="activiteit_wijzigen">Wijzigen</span></li>
                    <?php 
                    if($info['status'] != 'to do'){
                        // als de activiteit net is aangemaakt, al klaar is, of ergens anders in de planning staat.
                        // brengt de activiteit naar de huidige iteratie
                        $what = 'DATE_FORMAT(datum, \'%d %M %Y\') datum'; $from = 'planning_iteratie'; $where = 'huidige_iteratie = 1';
                            $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                        echo '<li data-action="activiteit_todo"><span id="activiteit_todo">In to do zetten<span id="todo_datum">('.$iteratie['datum'].')</span></span></li>';
                    }
                    if($info['status'] != 'onderhanden' && $info['in_acceptatie_door'] == 0){
                        // als de activiteit niet onderhanden is en niet in acceptatie bij iemand loopt.
                        echo '<li data-action="activiteit_onderhanden"><span id="activiteit_onderhanden">Onderhanden nemen</span></li>';
                    }
                    if(($info['status'] == 'onderhanden' || $info['status'] == 'to do') && $info['in_acceptatie_door'] == 0){
                        // als de activiteit wordt uitgevoerd, of nog niet in acceptatie is bij iemand.
                        echo '<li data-action="activiteit_acceptatie"><span id="activiteit_acceptatie">In acceptatie nemen</span></li>';
                    }
                    if($info['status'] != 'done'){
                        // als de activiteit al klaar is, hoef je 'm niet weer af te ronden.
                        // anders: kan dat altijd.
                        // wordt dan verplaatst naar de done van de huidige iteratie.
                        echo '<li data-action="activiteit_afronden"><span id="activiteit_afronden">Afronden</span></li>';
                    }
                    ?>
                    <li data-action="activiteit_delete"><span id="activiteit_verwijderen">Verwijderen</span></li>
               </ul>     
        	<?php }elseif($detail == 'project'){ ?>
        	   <p class="detail_opties_header">Ik wil dit project...:</p>
        	   <ul id="activiteit_verplaatsen">
                    <li data-action="project_wijzigen"><span id="project_wijzigen">Wijzigen</span></li>
        			<?php 
        			if($info['afgerond'] != 1){
        				// Als het project nog niet is afgerond, kan dat nog. 
        				//Maar dan werken ook direct alle activiteiten niet meer, gekoppeld aan dit project.
        				echo '<li data-action="project_afronden"><span id="project_afronden">Afronden</span></li>';
					}else{ echo '<li data-action="project_activeren"><span id="project_activeren">Actief maken</span></li>'; }
        			?>
        			<li data-action="project_verwijderen"><span id="project_verwijderen">Verwijderen</span></li>
        	   </ul>
        	<?php } ?>
        	</div>
        	<div id="detail_opties_center">
    		<?php if($detail == 'activiteit'){?>
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
            <?php }elseif($detail == 'project'){echo '&nbsp;';}?>
        	</div>
        	<div id="detail_opties_rechts">
        		<p class="detail_opties_header">Ik wil dit project delen met:</p>
        		<div id="detail_delen_met">
	                <label for="detail_delen_medewerker_field" style="opacity: 1;">Deze medewerker</label>
	                <input type="text" class="textfield" id="detail_delen_medewerker_field" />
	                
	                <div id="detail_delen_gebruikers"></div>
	                
	                <div class="wrapper_beschrijving">
		                <label for="detail_delen_beschrijving_field">Eventuele beschrijving</label>
		                <textarea class="textarea" id="detail_delen_beschrijving_field" cols="65" rows="4"></textarea>
	                </div>
	                
	                <button id="detail_delen_versturen" class="button"  onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Versturen</button>
        		</div>
        	</div>
        </div>

        <div id="detail_center">
            <div id="html_tekst">
            <?php
            if($info['html_detail'] != null){ echo $info['html_detail']; }
			else{
                if($detail == 'activiteit'){ echo 'Dubbelklik hier om een uitgebreide beschrijving te schrijven.'; }
    			elseif($detail == 'project'){
					echo '
					<p> <em>dubbelklik om te wijzigen</em></p>
					<h1> <strong>Businesscase</strong></h1>
					<p> Hier kan je de businesscase kwijt. Beschrijf de meerwaarde die het project moet opleveren.</p>
					<p> &nbsp;</p>
					<h1> Doelstellingen van het project</h1>
					<p> Beschrijf de concrete doelstellingen en oplossingen van het project.</p>
					<ol>
						<li> Doelstelling 1</li>
						<li> Doelstelling 2</li>
						<li> Doelstelling 3</li>
					</ol>
					<p> &nbsp;</p>
					<h1> Aanpak</h1>
					<p> Geef een beschrijving van de aanpak</p>
					<p> &nbsp;</p>
					<h1> Risico&#39;s</h1>
					<p> Benoem eventuele risico&#39;s</p>
					<p> &nbsp;</p>
					<h1> Aannames en uitgangspunten</h1>
					<p> Benoem de aannames en uitgangspunten</p>
					<ul>
						<li> Aanname x</li>
						<li> Uitgangspunt y</li>
						<li> Uitgangspunt z</li>
					</ul>
					<p> &nbsp;</p>';
				}
			}
            ?>
            </div>
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
                            echo 'er zijn nog geen tags';
                        }
                    ?>
                </div>
            </div>
            <div id="detail_bestanden">
                <div id="detail_bestanden_header">
                    <h3>Bestanden</h3> 
                    <button id="add_detail_file" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Bestand toevoegen</button>
                </div>
                <div id="detail_bestanden_form" style="display:none;">
                	<div id="status-message"></div>
        			<ul id="custom-queue"></ul>
        			<div id="upload_file"></div>
                </div>
                <div id="detail_bestanden_lijst">
                    <?php
                        if($aantal_bestanden > 0){
                            while($row = mysql_fetch_array($result_bestanden)){
                                if($detail == 'activiteit'){$path = 'planning_documenten/'.$activiteit_id.'/';}
                                elseif($detail == 'project'){$path = 'project_documenten/'.$project_id.'/';}
                                echo '
                                <div class="bestand" onmouseover="this.className='."'bestand_hover'".'" onmouseout="this.className='."'bestand'".'">
                                    <a class="bestand_link" href="/files/'.$path.$row['bestand'].'" target="_blank" title="bekijk bestand">'.$row['bestand'].'</a>
                                    <span class="verwijder_bestand" onclick="deleteFile('.$row['id'].')">verwijderen</span>
                                </div>';
                            }
                        }else{
                            echo 'Er zijn nog geen bestanden ge&uuml;pload';
                        }
                    ?>
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
                <div id="loading_img"><img src="<?php echo $etc_root ?>images/ajax_loader.gif" alt="Lijst wordt geladen" />&nbsp;</div>
                <div id="reacties_lijst"> &nbsp; </div>
           </div>
           <?php if($detail == 'project'){ ?>
           <div id="project_activiteiten">
	           <div id="detail_activiteiten">
	                <div id="detail_activiteiten_header">
	                    <h3>Activiteiten</h3> 
	                </div>
	                <div id="detail_activiteiten_lijst">
	                <?php
	                	$what = '	a.id,
	                				a.werkzaamheden,
									a.status,
									a.uur_aantal,
									b.competentie,
									DATE_FORMAT(c.datum, \'%d %M %Y\') deadline,
									DATE_FORMAT(a.status_datum, \'%d %M %Y\') status_datum
									';
						$from = '	planning_activiteit a
									LEFT JOIN competentie AS b ON (b.id = a.competentie)
									LEFT JOIN planning_iteratie AS c ON (c.id = a.iteratie)';
						$where ='	a.actief = 1
								 	AND a.project = '.$project_id.'
								 	AND a.status != \'nog te plannen\'
								 	ORDER BY c.datum ASC, 
                                    CASE
                                        WHEN a.status = \'done\' THEN 1
                                        WHEN a.status= \'acceptatie\' THEN 2
                                        WHEN a.status= \'onderhanden\' THEN 3
                                        WHEN a.status= \'to do\' THEN 4
                                        ELSE 5
                                     END';
						//echo "SELECT $what FROM $from WHERE $where";
						$aantal_activiteiten = countRows($what, $from, $where);
                        if($aantal_activiteiten > 0){
						$project_activiteiten = sqlSelect($what, $from, $where);
						
						while($project_activiteit = mysql_fetch_array($project_activiteiten)){
							if($i == 1){ $class = 'en_om'; $i = 0; }else{ $class = 'om'; $i = 1; }
							?>
						<div class="detail_activiteit <?php echo $class; ?>">
							<span class="activiteit_top">
								<a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
									<span class="activiteit_werkzaamheden"> <?php echo $project_activiteit['werkzaamheden'] ?></span>
								</a> 
							</span>

							<span class="activiteit_bottom">
								<a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
									<span class="activiteit_uren"><?php echo $project_activiteit['uur_aantal'] ?></span>
								</a>
								<span class="activiteit_status"><?php echo $project_activiteit['status'] ?>&nbsp;/</span>
								<span class="activiteit_competentie">&nbsp;<?php echo $project_activiteit['competentie'] ?></span>
			                	<span class="activiteit_deadline"><?php  echo $project_activiteit['deadline'] ?></span>
							</span>
			                
						</div>
					<?php }
					}
                        $what = '   a.id,
                                    a.werkzaamheden,
                                    a.status,
                                    a.uur_aantal,
                                    b.competentie,
                                    DATE_FORMAT(a.status_datum, \'%d %M %Y\') status_datum,
                                    DATE_FORMAT(a.toegevoegd_op, \'%d %M %Y\') toegevoegd_op';
                        $from = '   planning_activiteit a
                                    LEFT JOIN competentie AS b ON (b.id = a.competentie)';
                        $where ='   a.actief = 1
                                    AND a.project = '.$project_id.'
                                    AND a.status = \'nog te plannen\'
                                    ORDER BY a.toegevoegd_op ASC'; 
                        $aantal_activiteiten = countRows($what, $from, $where);
                        if($aantal_activiteiten > 0){
                        $project_activiteiten = sqlSelect($what, $from, $where);
                        
                        while($project_activiteit = mysql_fetch_array($project_activiteiten)){
                            if($i == 1){ $class = 'en_om'; $i = 0; }else{ $class = 'om'; $i = 1; }
                            ?>
                        <div class="detail_activiteit <?php echo $class; ?>">
                            <span class="activiteit_top">
                                <a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
                                    <span class="activiteit_werkzaamheden"> <?php echo $project_activiteit['werkzaamheden'] ?></span>
                                </a> 
                            </span>

                            <span class="activiteit_bottom">
                                <a href="/planning/detail/activiteit-id=<?php echo $project_activiteit['id']; ?>">
                                    <span class="activiteit_uren"><?php echo $project_activiteit['uur_aantal'] ?></span>
                                </a>
                                <span class="activiteit_status"><?php echo $project_activiteit['status'] ?>&nbsp;/</span>
                                <span class="activiteit_competentie">&nbsp;<?php echo $project_activiteit['competentie'] ?></span>
                                <span class="activiteit_deadline"><?php  echo $project_activiteit['toegevoegd_op'] ?></span>
                            </span>
                            
                        </div>
                    <?php }
                    }?>
					</div>
	           </div>
           </div>
           <?php } ?>
        </div>
</div>
