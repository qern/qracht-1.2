var organisatieArray = [], projectArray = [], competentieArray = [], medewerkerArray = [], zoekwoordArray = [], iteraties = jQuery('#iteraties').text();
setInterval(function(){reloadPlanning('alles', iteratie_Id, overallFilter, 'slider', 'on')}, 30000); //dit zorgt voor automatische refresh elke x milliseconde
reloadPlanning('alles', iteratie_Id, overallFilter, 'slider', 'on');

function laadOrganisatie(){
    var organisatieDump = $('#organisaties').children('.organisatie_filter');
    organisatieDump.children('#organisatie_te_filteren').each(function() { var organisatieId = $(this).html();if(jQuery.inArray(organisatieId, organisatieArray) < 0){organisatieArray.push(organisatieId);} });    
}//console.log(organisatieArray);

function laadProject(){
    var projectDump = $('#projecten').children('.project_filter');
    projectDump.children('#project_te_filteren').each(function() { var projectId = $(this).html();if(jQuery.inArray(projectId, projectArray) < 0){projectArray.push(projectId);} });
}//console.log(projectArray);

function laadCompetentie(){
    var competentieDump = $('#competenties').children('.competentie_filter');
    competentieDump.children('#competentie_te_filteren').each(function() { var competentieId = $(this).html();if(jQuery.inArray(competentieId, competentieArray) < 0){competentieArray.push(competentieId);} });      
}//console.log(competentieArray);

function laadMedewerker(){
    var medewerkerDump = $('#medewerkers').children('.medewerker_filter');
    medewerkerDump.children('#medewerker_te_filteren').each(function() { var medewerkerId = $(this).html();if(jQuery.inArray(medewerkerId, medewerkerArray) < 0){medewerkerArray.push(medewerkerId);} });     
}//console.log(gebruikerArray);

function laadZoekwoorden(){
    var zoekwoordDump = $('#zoekwoorden').children('.zoekwoord_filter');
    zoekwoordDump.children('#zoekwoord_te_filteren').each(function() { var zoekwoordId = $(this).html();if(jQuery.inArray(zoekwoordId, zoekwoordArray) < 0){zoekwoordArray.push(zoekwoordId);} });
}//console.log(zoekwoordArray)

function deleteFilter(id, filter){
    $('#' + filter + '_' + id).remove();
    
    if(filter === 'organisatie'){laadOrganisatie(); organisatieArray = $.grep(organisatieArray, function(n, i){ return (n != id);}); }
    else if(filter === 'project'){laadProject(); projectArray = $.grep(projectArray, function(n, i){ return (n != id);}); }
    else if(filter === 'competentie'){laadCompetentie(); competentieArray = $.grep(competentieArray, function(n, i){ return (n != id);}); }
    else if(filter === 'medewerker'){laadMedewerker(); medewerkerArray = $.grep(medewerkerArray, function(n, i){ return (n != id);}); }
    else if(filter === 'zoekwoord'){laadZoekwoorden(); zoekwoordArray = $.grep(zoekwoordArray, function(n, i){ return (i != id - 1);}); }
    
    $('#filters_versturen').click();
}

function slideIteratie ( direction ){
    
    //wat is het rijnummer van de voorgaande en/of komende rij
    var iteraties = jQuery('#prev_next').text().split('_'), prev_it = parseInt(iteraties[0]), cur_it = parseInt(iteraties[1]),  next_it = parseInt(iteraties[2]);
       
    //aan we achteruit of vooruit ?        
    if(direction == 'prev'){ var newiteratie_rows = (prev_it - 1) + '_' + (cur_it - 1)+ '_' + (next_it - 1), toonRij = prev_it; }
    else if(direction == 'next'){ var newiteratie_rows = (prev_it + 1) + '_' + (cur_it + 1) + '_' + (next_it + 1), toonRij = next_it; } 
    jQuery('#prev_next').text(newiteratie_rows);
    jQuery('#iteratie_container_' + cur_it).fadeToggle();
    jQuery('#iteratie_container_' + toonRij).fadeToggle();
    
    //wijzig de controls aan de hand van de huidige rij
    if(toonRij == 1){ jQuery('#overige_iteratie_prev').hide();}
    else if(toonRij > 1 && toonRij < 3){ jQuery('#overige_iteratie_prev').show(); jQuery('#overige_iteratie_next').show(); }
    else if(toonRij == 3){ jQuery('#overige_iteratie_next').hide(); }
}

function filtersVersturen(){
	var min_uur = $('#min_uren').val(), max_uur = $('#max_uren').val(),
		prio = jQuery('#filter_prioriteit img.active_prio').attr('data-prio'),
		display = jQuery('#overzicht_opties span.active_display').attr('data-display'),
		pinned = jQuery('#overzicht_opties span.active_pinned').attr('data-pinned');
    	overallFilter = {
                        iteraties : iteraties,
                        organisatie : organisatieArray,
                        project : projectArray,
                        competentie : competentieArray,
                        medewerker : medewerkerArray,
                        zoekwoord : zoekwoordArray,
                        min_uren : min_uur,
                        max_uren :  max_uur,
                        prio : prio
                     };
	//jQuery('#filter_data').text(filterdata);
    reloadPlanning('alles', iteratie_Id, overallFilter, display, pinned);
    
    jQuery('#overige_iteratie_nav span').on('click', function(){  var direction = jQuery(this).attr('data-dir'); slideIteratie( direction ); });
}

$(function(){
    //filter op organisatie(s) (?)
    $("#organisatie_field").autocomplete({
        source:'/functions/planning/includes/nogteplannen_filter_ajax.php?filter=organisatie&iteraties='+iteraties,
        minLength:2,
        select: function( event, ui ) {
                $('#organisaties').append(
                '<div id="organisatie_'+ ui.item.id  +'" class="organisatie_filter">'+
                    '<span id="organisatie_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="organisatie_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'organisatie\')">X</span></div>'+
                '</div>');
                $("#organisatie_field").val('');
                laadOrganisatie();
                return false;
        }
    });
    
    //filter op project(en) (?)
    $('#project_field').on('click', function(){
       laadOrganisatie();   
        //0 of meer dan 1 ? dan alleen op gebruiker zoeken.
        //is er maar 1 ? zoek dan op gebruiker, binnen een organisatie.
        // maak hier de source aan
        source = '/functions/planning/includes/nogteplannen_filter_ajax.php?filter=project&iteraties='+iteraties;
        if(organisatieArray.length === 0){}
        else if(organisatieArray.length === 1){source += '&organisatie='+organisatieArray[0];}
        else{
            var i = 0, organisaties = '';
            while(i < organisatieArray.length){ 
                if(i === 0){organisaties += organisatieArray[i];}
                else{organisaties += '_' + organisatieArray[i] ;}
                i++;
            }    
            source += '&organisatie='+organisaties;
        }
 
        $("#project_field").autocomplete({
            source: source,
            minLength:2,
            select: function( event, ui ) {
                $('#projecten').append(
                '<div id="project_'+ ui.item.id  +'" class="project_filter">'+
                    '<span id="project_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="project_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'project\')">X</span></div>'+
                '</div>');
                $("#project_field").val('');
                laadOrganisatie();
                laadProject();
                return false;
            }
            
        });       
    });
    
    //filter op competentie(s) (?)
    $('#competentie_field').on('click', function(){
        laadOrganisatie();
        laadProject();   
        //0 of meer dan 1 ? dan alleen op gebruiker zoeken.
        //is er maar 1 ? zoek dan op gebruiker, binnen een organisatie.
        // maak hier de source aan
        source = '/functions/planning/includes/nogteplannen_filter_ajax.php?filter=competentie&iteraties='+iteraties;
        
        //binnen welke organisaties moet er gezocht worden ?
        if(organisatieArray.length === 0){}
        else if(organisatieArray.length === 1){source += '&organisatie='+organisatieArray[0];}
        else{
            var i = 0, organisaties = '';
            while(i < organisatieArray.length){ 
                if(i === 0){organisaties += organisatieArray[i];}
                else{organisaties += '_' + organisatieArray[i] ;}
                i++;
            }    
            source += '&organisatie='+organisaties;
        }  
        
        //binnen welke projecten moet er gezocht worden ?
        if(projectArray.length === 0){}
        else if(projectArray.length === 1){source += '&project='+projectArray[0];}
        else{
            var i = 0, projecten = '';
            while(i < projectArray.length){
                if(i === 0){projecten += projectArray[i];}
                else{projecten += '_' + projectArray[i] ;}
                i++; 
            }    
            source += '&project='+projecten;
        }
        
        $("#competentie_field").autocomplete({
            source: source,
            minLength:2,
            select: function( event, ui ) {
                $('#competenties').append(
                '<div id="competentie_'+ ui.item.id  +'" class="competentie_filter">'+
                    '<span id="competentie_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="competentie_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'competentie\')">X</span></div>'+
                '</div>');
                $("#competentie_field").val('');
                laadProject();
                laadCompetentie();
                return false;
            }
            
        });       
    });
    
    //filter op medewerker(s) (?)
    $('#medewerker_field').on('click', function(){
        laadCompetentie();   
        //0 of meer dan 1 ? dan alleen op gebruiker zoeken.
        //is er maar 1 ? zoek dan op gebruiker, binnen een organisatie.
        // maak hier de source aan
        source = '/functions/planning/includes/nogteplannen_filter_ajax.php?filter=medewerker&iteraties='+iteraties;
        
        //binnen welke organisaties moet er gezocht worden ?
        if(organisatieArray.length === 0){}
        else if(organisatieArray.length === 1){source += '&organisatie='+organisatieArray[0];}
        else{
            var i = 0, organisaties = '';
            while(i < organisatieArray.length){ 
                if(i === 0){organisaties += organisatieArray[i];}
                else{organisaties += '_' + organisatieArray[i] ;}
                i++;
            }    
            source += '&organisatie='+organisaties;
        }  
        
        //binnen welke projecten moet er gezocht worden ?
        if(projectArray.length === 0){}
        else if(projectArray.length === 1){source += '&project='+projectArray[0];}
        else{
            var i = 0, projecten = '';
            while(i < projectArray.length){
                if(i === 0){projecten += projectArray[i];}
                else{projecten += '_' + projectArray[i] ;}
                i++; 
            }    
            source += '&project='+projecten;
        }
        //binnen welke competentie moet er gezocht worden ?
        if(competentieArray.length === 0){}
        else if(competentieArray.length === 1){source +=  '&competentie='+competentieArray[0];}
        else{
            var i = 0, competenties = '';
            while(i < competentieArray.length){
                if(i === 0){competenties += competentieArray[i];}
                else{competenties += '_' + competentieArray[i];}
                i++;                        
            }    
            source += '&competentie='+competenties;
        }
        
        $("#medewerker_field").autocomplete({
            source: source,
            minLength:2,
            select: function( event, ui ) {
                $('#medewerkers').append(
                '<div id="medewerker_'+ ui.item.id  +'" class="medewerker_filter">'+
                    '<span id="medewerker_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="medewerker_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'medewerker\')">X</span></div>'+
                '</div>');
                $("#medewerker_field").val('');
                laadCompetentie();
                laadMedewerker();
                return false;
            }
        });       
    });
    
    $('#zoekwoord_field').on('keydown', function(event){   
        if (event.keyCode == '13' || event.keyCode == '188') {
            
            var i = zoekwoordArray.length + 1, zoekwoord = $('#zoekwoord_field').val(); 
            jQuery('#zoekwoorden').append(
                '<div id="zoekwoord_'+ i  +'" class="zoekwoord_filter">'+
                    '<span id="zoekwoord_te_filteren" style="display:none;">'+ zoekwoord+ '</span>'+
                    '<div id="zoekwoord_naam">'+ zoekwoord + ' <span onclick="deleteFilter(' + i+ ', \'zoekwoord\')">X</span></div>'+
                '</div>');
            jQuery("#zoekwoord_field").val("");
            laadZoekwoorden();
            return false;
       }
    });
    
    $('#filters_versturen').on('click', function(){ filtersVersturen(); });
});