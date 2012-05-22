<?php 
$what="id"; $from="planning_iteratie";  $where="huidige_iteratie = 1";
$aantal = countRows($what, $from, $where);
if($aantal == 1){
$huidige_iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where));

$what = "id"; $from= "planning_iteratie"; $where="actief = 1 AND huidige_iteratie = 0 AND actief = 1 AND huidige_iteratie = 0 AND datum > (SELECT a.datum FROM planning_iteratie a WHERE a.huidige_iteratie = 1) LIMIT 5";
    $huidige_iteraties = sqlSelect($what, $from, $where);
   
$iteraties = $huidige_iteratie['id'];
while($overige_iteratie = mysql_fetch_array($huidige_iteraties)){
    $iteraties .=  '_'.$overige_iteratie['id'];
}

//hoe veel iteraties zijn er (al) ?
$what = 'COUNT(a.id) AS aantal, UNIX_TIMESTAMP(MAX(a.datum)) AS nieuwste, b.aantal_iteraties, b.iteratie_duur';
$from =  'planning_iteratie a, planning_instellingen b';
$where = 'a.actief = 1 AND b.actief = 1';
$planning_config = mysql_fetch_assoc(sqlSelect($what, $from, $where));

//zijn er minder iteraties dan aangegeven ?
if($planning_config['aantal'] < $planning_config['aantal_iteraties']){
    //wat is het verschil, ofwel, hoeveel hebben we er nog nodig?
    $verschil =  $planning_config['aantal_iteraties'] - $planning_config['aantal'];
    $i = 1;
    //maak dan zoveel iteraties aan, als nodig is.
    while($verschil > 0){
        //eerst de nieuwe datum instellen:
        //de nieuwe timestamp is 7 dagen na de laatste.
        $nieuwe_datum = ($planning_config['nieuwste'] + ((60*60*24*7) * $i));
        $table = 'planning_iteratie';
        $what = 'datum, toelichting, huidige_iteratie, actief, geldig, gewijzigd_op, gewijzigd_door';
        $with_what = 'FROM_UNIXTIME('.$nieuwe_datum.'), \'\', 0, 1, 1, NOW(), 0';
            $insert_iteratie = sqlInsert($table, $what, $with_what);
        //echo $with_what.'<br />';
        $verschil--;
        $i++;
    }
    //maak ze nu aan:
    
}

$what = "a.id, DATE_FORMAT(a.datum, '%d %M %Y') AS datum"; $from= "planning_iteratie a";
$where="a.actief = 1 AND a.huidige_iteratie = 0 AND datum > (SELECT a.datum FROM planning_iteratie a WHERE a.huidige_iteratie = 1) LIMIT 5";    
$niet_huidige_iteratie = sqlSelect($what, $from, $where); $niet_huidige_iteratie2 = sqlSelect($what, $from, $where);
?>

<script>
var iteratie_Id = '<?php echo $huidige_iteratie['id']; ?>', overallFilter = '';
//de functie om de planning te herladen, op basis van kolom, iteratie en eventueel filter.
function reloadPlanning(kolom, iteratieId, filter, display, pinned){
    if(display == null){display = jQuery('#overzicht_opties span.active_display').attr('data-display');}
    if(pinned == null){pinned = jQuery('#overzicht_opties span.active_pinned').attr('data-pinned');}
    if(kolom !== 'alles'){$('#'+kolom+'_container').stop().animate({"opacity": "0.5"}, "fast");}
    else{$('#planning_kolommen').stop().animate({"opacity": "0.5"}, "fast");}
    $.ajax({
        type : 'GET',
        url : '/functions/planning/includes/nogteplannen_ajax.php',
        dataType : 'html',
        data: {
            action: 'reload',
            kolom: kolom,
            iteratie_id : iteratieId,
            filter: filter,
            display : display, 
            pinned : pinned
        },
        success : function(data){
            jQuery('#loading_img').slideToggle();
            if(kolom !== 'alles'){$('#'+kolom+'_container').stop().animate({"opacity": "1"}, "fast").html(data);}
            else{$('#planning_kolommen').stop().animate({"opacity": "1"}, "fast").html(data);}
            //herinitialiseer het drag-drop systeem
            $(function(){
                $(
                <?php 
                echo '"#nog_te_plannen, ';
				$iteratie_line = $huidige_iteratie['id'];
                while($overige_iteratie = mysql_fetch_array($niet_huidige_iteratie)){
                	$iteratie_line .= '+'.$overige_iteratie['id'];
                    $iteratie_id = $overige_iteratie['id'];
                    echo '#iteratie_id_'.$iteratie_id.', ';
                }
                echo '#huidige_iteratie"';?>
                ).sortable({
                //$("#todo, #onderhanden, #acceptatie, #done").sortable({
                    connectWith:'.activiteiten',
                    update:function(e,ui){
                        if (this === ui.item.parent()[0]) {
                            $.ajax({
                                type:"POST",
                                dataType : 'html',
                                url:'/functions/planning/includes/nogteplannen_ajax.php',
                                data:{
                                    <?php echo "
                                    action: 'dragDrop',
                                    id : iteratieId,
                                    huidig_iteratie_id : '".$huidige_iteratie['id']."',
                                    nog_te_plannen:$('#nog_te_plannen').sortable('serialize'),";
                                    
                                    while($overige_iteratie = mysql_fetch_array($niet_huidige_iteratie2)){
                                    $iteratie_id = $overige_iteratie['id'];  
                                    echo"
                                    iteratie_id_$iteratie_id :$('#iteratie_id_$iteratie_id').sortable('serialize'),";
                                    
                                     }
                                    echo"
                                    huidige_iteratie:$('#huidige_iteratie').sortable('serialize')";
                                    ?>
                                },
                                success:function(data2){
                                    var kolommen = data2.split(','), display = jQuery('#overzicht_opties span.active_display').attr('data-display'),
                                        pinned = jQuery('#overzicht_opties span.active_pinned').attr('data-pinned');
                                    for(var naam in kolommen){
                                        reloadPlanning(kolommen[naam], iteratieId, overallFilter, display, pinned);
                                    }
                                }
                            });
                        }
                    }
                }).disableSelection();
                displayAlleActiviteitenGezien('<?php echo $iteratie_line; ?>');
                jQuery('img.recentmelding').on('click', function(){
                    var activiteit = jQuery(this).attr('data-activiteit'), kolom = jQuery(this).attr('data-kolom'), display = jQuery('#overzicht_opties span.active_display').attr('data-display'),
                        pinned = jQuery('#overzicht_opties span.active_pinned').attr('data-pinned');;
                    jQuery.ajax({
                        type:"GET", dataType : 'html',
                        url:'/functions/planning/includes/check.php',
                        data:{ action : 'activiteitGezien', activiteit: activiteit },
                        success:function(data){ reloadPlanning(kolom, iteratie_Id, "");  displayAlleActiviteitenGezien(iteratie_Id); }
                    });return false;
                });
                jQuery('span.recentmelding_container').hover(
                    function(){
                            var activiteit = jQuery(this).children('img.recentmelding').attr('data-activiteit'), target = jQuery(this).children('span.recente_meldingen');
                            if(target.attr('data-shown') == 'false'){
                                    target.attr('data-shown', 'true').show().css({ opacity: 0.7 });
                                    jQuery.ajax({
                                        type:"GET", dataType : 'html',
                                        url:'/functions/planning/includes/check.php',
                                        data:{ action : 'getRecenteMeldingen', activiteit: activiteit },
                                        success:function(data){ target.html(data); }
                                    });
                            }else{ target.show().css({ opacity: 0.7 });}
                    }, 
                    function(){
                            jQuery(this).children('span.recente_meldingen').hide();
                    }
                );
                jQuery('div.commentaar_container').hover(
                    function(){
                            var activiteit = jQuery(this).attr('data-activiteit'), dit = jQuery(this);
                            if(jQuery(this).attr('data-shown') == 'false'){
                                    jQuery(this).attr('data-shown', 'true').addClass('commentaar_container_hover');
                                    jQuery.ajax({
                                type:"GET", dataType : 'html',
                                url:'/functions/planning/includes/check.php',
                                data:{ action : 'showReactie', activiteit: activiteit },
                                success:function(data){ dit.children('div.laatste_comment').html(data); }
                            });
                            }else{ jQuery(this).addClass('commentaar_container_hover');}
                    }, 
                    function(){
                            jQuery(this).removeClass('commentaar_container_hover');
                    }
                );
            });
        }
    });
    return false;
}
jQuery(function(){
    jQuery('#overzicht_opties span').on('click', function(){
        var display = jQuery(this).attr('data-display'), pinned = jQuery('#overzicht_opties img.active_pinned').attr('data-pinned');
        reloadPlanning('alles', iteratie_Id, overallFilter, display, pinned);
        jQuery('#overzicht_opties span').each( function( intIndex ){ jQuery(this).toggle().toggleClass('active_display') } );
    });
    jQuery('#overzicht_opties img').on('click', function(){
        var pinned = jQuery(this).attr('data-pinned'), display = jQuery('#overzicht_opties span.active_display').attr('data-display');
        reloadPlanning('alles', iteratie_Id, overallFilter, display, pinned);
        jQuery('#overzicht_opties img').each( function( intIndex ){ jQuery(this).toggle().toggleClass('active_pinned') } );
    });
});
</script>

<div id="dashboard_menu">
    <div id="activators">
        <img src="/functions/<?php echo $_GET['function']; ?>/css/images/filter.png" alt="filters" id="filter_activator" class="active_activator" />
        <img src="/functions/<?php echo $_GET['function']; ?>/css/images/clock.png" alt="uren" id="tijd_activator" />
    </div>
    <div id="filter_prioriteit">
        <img class="active_prio" src="/functions/<?php echo $_GET['function'] ?>/css/images/remove_prio_24px.png" alt="verwijder filter op prioriteit" data-prio="0" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_1_24px.png" alt="filteren op prio 1" data-prio="1" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_2_24px.png" alt="filteren op prio 2" data-prio="2" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_3_24px.png" alt="filteren op prio 3" data-prio="3" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_4_24px.png" alt="filteren op prio 4" data-prio="4" /> 
    </div>
    <div id="filters">
            <div id="filter_data" style="display: none;"></div>
            <div id="organisatie_placeholder">
                <label for="organisatie_field">Welke organisatie(s)</label>
                <input type="text" id="organisatie_field" class="textfield" />
                
                <div id="organisaties">
                </div>
                
            </div>        
            
            <div id="projecten_placeholder">
                <label for="project_field">Welk(e) project(en)</label>
                <input type="text" id="project_field" class="textfield" />
                
                <div id="projecten">
                </div>
                
            </div>
           
             <div id="competentie_placeholder">
                <label for="competentie_field">Welk(e) competentie(s)</label>
                <input type="text" id="competentie_field" class="textfield" />
                
                <div id="competenties">
                </div>
                
            </div>

            <div id="uren_zoekwoord">
                
                <p>Aantal uur</p>
                
                <div id="uren_min">
                    <label for="min_uren">min</label>
                    <input type="text" class="textfield" id="min_uren" />
                </div> 
                
                <div id="uren_max">
                    <label for="max_uren">max</label>
                    <input type="text" class="textfield" id="max_uren" />
                </div> 
                
            </div>
        
            <div id="zoekwoord_placeholder">
                
                <label for="zoekwoord_field">zoekwoord</label> 
                <input type="text" class="textfield" id="zoekwoord_field" name="zoekwoord" />
                
                <div id="zoekwoorden">
                </div>
                
            </div>
        
            <div id="filteren">
                <!--<button id="filters_verwijderen" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">Filter verwijderen</button>-->
                <button id="filters_versturen" class="button" title="test" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'">filteren</button>
            </div>
            
        </div>
        
        <div id="uren" style="display:none;">
        <?php
        
            $what = 'a.id, DATE_FORMAT(a.datum, \'%d %M %Y\') AS datum, a.huidige_iteratie, SUM(b.uur_aantal) AS geplande_uren, b.iteratie'; 
            $from = 'planning_iteratie a LEFT JOIN planning_activiteit AS b ON (b.iteratie = a.id AND b.actief = 1 AND b.status = \'to do\') '; 
            $where = 'a.actief = 1 GROUP BY (a.id) ORDER BY a.huidige_iteratie DESC, a.datum ASC LIMIT 6';
                $iteraties_uren = sqlSelect($what, $from, $where); 
                //echo "SELECT $what FROM $from WHERE $where";
                $what = 'SUM( a.uren ) AS uren'; $from = 'portal_gebruiker_competentie a LEFT JOIN portal_gebruiker AS b ON ( b.id = a.gebruiker )'; $where = 'b.actief =1';
                $competentie_totaal = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
                //hoe veel iteraties zijn er (al) ?
                $what = 'iteratie_duur'; $from =  'planning_instellingen'; $where = 'actief = 1';
                    $planning_config = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                    
               //echo "SELECT $what FROM $from WHERE $where" ;
        while($iteratie_uren = mysql_fetch_array($iteraties_uren)){?>
            <div class="iteratie_uren<?php if($iteratie_uren['huidige_iteratie'] == 1){echo  ' active_uren';} ?>" id="iteratie_<?php echo $iteratie_uren['id']; ?>">
                <div class="iteratie_top">
                    <?php
                        $overgebleven_uren = ( ( $competentie_totaal['uren'] * $planning_config['iteratie_duur'] ) - $iteratie_uren['geplande_uren'] );
                        if($overgebleven_uren > 0){ $iterate_totaal = '<span class="positief">'.$overgebleven_uren.' uur</span>'; }
                        elseif($overgebleven_uren == 0){ $iterate_totaal ='<span class="breakeven">'.$overgebleven_uren.' uur</span>';}
                        else{$iterate_totaal = '<span class="negatief">'.$overgebleven_uren.' uur</span>';}
                    ?>
                    <h2 class="iteratie_datum"><?php echo $iteratie_uren['datum'] ?></h2> <h2 class="iteratie_uur"><?php echo $iterate_totaal  ?></h2>
                </div>
                <div class="ajax_response">
                <?php 
                if($iteratie_uren['huidige_iteratie'] == 1){
                    $what = 'a.id, a.competentie AS naam, SUM(b.uren) AS totaal_uren'; $from = 'competentie a LEFT JOIN portal_gebruiker_competentie AS b ON (b.competentie = a.id)'; 
                    $where = '1 GROUP BY (a.id)';
                        $competenties = sqlSelect($what, $from, $where);
               
                    while($competentie = mysql_fetch_array($competenties)){?>
                    <div class="competentie">
                        <div class="competentie_naam"><?php echo ucfirst($competentie['naam']); ?></div>
                        <div class="competentie_uren">
                        <?php 
                        
                        //hoeveel uur is er gebruikt van deze competentie, binnen deze iteratie
                        $what = 'SUM(uur_aantal) AS totaal'; $from = 'planning_activiteit'; $where = 'status =  \'to do\' AND competentie = '.$competentie['id'].' AND iteratie = '.$iteratie_uren['id'];
                            $competentie_uren = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                    
                        //meer dan 0 ? dan is het beschikbare uren - gebruikte uren
                        if($competentie_uren['totaal'] > 0){
                            $overgebleven_uren = ( ( $competentie['totaal_uren'] * $planning_config['iteratie_duur'] ) - $competentie_uren['totaal'] );
                            if($overgebleven_uren > 0){?>
                                <span class="positief"><?php echo $overgebleven_uren; ?></span>
                            <?php }elseif($overgebleven_uren == 0){?>
                                <span class="breakeven"><?php echo $overgebleven_uren; ?></span>
                            <?php }else{?>
                                <span class="negatief"><?php echo $overgebleven_uren; ?></span>
                            <?php }
                        }else{
                            $overgebleven_uren = ($competentie['totaal_uren'] * $planning_config['iteratie_duur']);
                            if($overgebleven_uren > 0){?>
                                <span class="positief"><?php echo $overgebleven_uren; ?></span>
                            <?php }elseif($overgebleven_uren == 0){?>
                                <span class="breakeven"><?php echo $overgebleven_uren; ?></span>
                            <?php }
                        }?>
                        </div>
                    </div>
                <?php }
                } ?>
                </div>
            </div>
        <?php } ?>
        </div>
</div>
<div id="planning_main">
	<div id="overzicht_opties">
		<span id="hele_overzicht" data-display="alles">Toon volledig overzicht</span>
        <span id="slider_overzicht" class="active_display" data-display="slider" style="display:none;">Toon slider overzicht</span>
        <img src="/functions/planning/css/images/pin.png" title="pinned ON" id="pinned_on" class="active_pinned" data-pinned="on" style="display:none;" />
        <img src="/functions/planning/css/images/pin.png" title="pinned OFF" id="pinned_off" data-pinned="off" />             
    </div>
	
	   <div id="iteraties" style="display:none;"><?php echo $iteraties ?></div>
	   <span id="prev_next" style="display:none;">0_1_2</span>
       <div id="planning_kolommen">
           	<div id="loading_img">
       	    	<img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;
           	</div>
       </div>
       
</div>
<?php }else{?>
<div id="planning">
    <h2>Er is (nog) geen actieve iteratie. <a href="/<?php echo $_GET['function'] ?>/iteraties" title="iteratie aanmaken">Maak deze aan.</a></h2>
</div>
<?php } ?>