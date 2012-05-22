var organisatieArray = [], projectArray = [], competentieArray = [], medewerkerArray = [], zoekwoordArray = [], filter = '';

//de functie om de planning te herladen, op basis van kolom, iteratie en eventueel filter.
function reloadPlanning(filter){
    jQuery.ajax({
        type : 'GET',
        url : '/functions/planning/includes/archief-ajax.php',
        dataType : 'html',
        data: {
            action: 'reload',
            filter: filter
        },
        success : function(data){
            jQuery('#hierzo').html(data);
        }
    });return false;
}
function laadOrganisatie(){
    var organisatieDump = jQuery('#organisaties').children('.organisatie_filter');
    organisatieDump.children('#organisatie_te_filteren').each(function() { var organisatieId = jQuery(this).html();if(jQuery.inArray(organisatieId, organisatieArray) < 0){organisatieArray.push(organisatieId);} });    
}//console.log(organisatieArray);

function laadProject(){
    var projectDump = jQuery('#projecten').children('.project_filter');
    projectDump.children('#project_te_filteren').each(function() { var projectId = jQuery(this).html();if(jQuery.inArray(projectId, projectArray) < 0){projectArray.push(projectId);} });
}//console.log(projectArray);

function laadCompetentie(){
    var competentieDump = jQuery('#competenties').children('.competentie_filter');
    competentieDump.children('#competentie_te_filteren').each(function() { var competentieId = jQuery(this).html();if(jQuery.inArray(competentieId, competentieArray) < 0){competentieArray.push(competentieId);} });      
}//console.log(competentieArray);

function laadMedewerker(){
    var medewerkerDump = jQuery('#medewerkers').children('.medewerker_filter');
    medewerkerDump.children('#medewerker_te_filteren').each(function() { var medewerkerId = jQuery(this).html();if(jQuery.inArray(medewerkerId, medewerkerArray) < 0){medewerkerArray.push(medewerkerId);} });     
}//console.log(gebruikerArray);

function laadZoekwoorden(){
    var zoekwoordDump = jQuery('#zoekwoorden').children('.zoekwoord_filter');
    zoekwoordDump.children('#zoekwoord_te_filteren').each(function() { var zoekwoordId = jQuery(this).html();if(jQuery.inArray(zoekwoordId, zoekwoordArray) < 0){zoekwoordArray.push(zoekwoordId);} });
}//console.log(zoekwoordArray)

function deleteFilter(id, filter){
    jQuery('#' + filter + '_' + id).remove();
    console.log(medewerkerArray);
    if(filter === 'organisatie'){laadOrganisatie(); organisatieArray = jQuery.grep(organisatieArray, function(n, i){ return (n != id);}); }
    else if(filter === 'project'){laadProject(); projectArray = jQuery.grep(projectArray, function(n, i){ return (n != id);}); }
    else if(filter === 'competentie'){laadCompetentie(); competentieArray = jQuery.grep(competentieArray, function(n, i){ return (n != id);}); }
    else if(filter === 'medewerker'){laadMedewerker(); medewerkerArray = jQuery.grep(medewerkerArray, function(n, i){ return (n != id);}); }
    else if(filter === 'zoekwoord'){laadZoekwoorden(); zoekwoordArray = jQuery.grep(zoekwoordArray, function(n, i){ return (i != id - 1);}); }
    console.log(medewerkerArray);
    jQuery('#filters_versturen').click();
}

function filtersVersturen(){
	var min_uur = jQuery('#min_uren').val(), max_uur = jQuery('#max_uren').val()
    	van_datum = jQuery('#van_datum').val(), tot_datum = jQuery('#tot_datum').val(),
		prio = jQuery('#filter_prioriteit img.active_prio').attr('data-prio');
    var filterdata = {
                        organisatie : organisatieArray,
                        project : projectArray,
                        competentie : competentieArray,
                        medewerker : medewerkerArray,
                        zoekwoord : zoekwoordArray,
                        min_uren : min_uur,
                        max_uren :  max_uur,
                        van_datum : van_datum,
                        tot_datum : tot_datum,
                        prio : prio
                     };
    reloadPlanning(filterdata);
}
    
jQuery(function(){
	reloadPlanning(filter);
    //filter op organisatie(s) (?) 
    jQuery("#organisatie_field").autocomplete({
        source:'/functions/planning/includes/archief_filter_ajax.php?filter=organisatie',
        minLength:2,
        select: function( event, ui ) {
                jQuery('#organisaties').append(
                '<div id="organisatie_'+ ui.item.id  +'" class="organisatie_filter">'+
                    '<span id="organisatie_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="organisatie_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'organisatie\')">X</span></div>'+
                '</div>');
                jQuery("#organisatie_field").val('');
                laadOrganisatie();
                return false;
        }
    });
    
    //filter op project(en) (?)
    jQuery('#project_field').on('click', function(){
       laadOrganisatie();   
        //0 of meer dan 1 ? dan alleen op gebruiker zoeken.
        //is er maar 1 ? zoek dan op gebruiker, binnen een organisatie.
        // maak hier de source aan
        source = '/functions/planning/includes/archief_filter_ajax.php?filter=project';
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
 
        jQuery("#project_field").autocomplete({
            source: source,
            minLength:2,
            select: function( event, ui ) {
                jQuery('#projecten').append(
                '<div id="project_'+ ui.item.id  +'" class="project_filter">'+
                    '<span id="project_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="project_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'project\')">X</span></div>'+
                '</div>');
                jQuery("#project_field").val('');
                laadOrganisatie();
                laadProject();
                return false;
            }
            
        });       
    });
    
    //filter op competentie(s) (?)
    jQuery('#competentie_field').on('click', function(){
        laadOrganisatie();
        laadProject();   
        //0 of meer dan 1 ? dan alleen op gebruiker zoeken.
        //is er maar 1 ? zoek dan op gebruiker, binnen een organisatie.
        // maak hier de source aan
        source = '/functions/planning/includes/archief_filter_ajax.php?filter=competentie';
        
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
        console.log(organisatieArray);
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
        console.log(projectArray);
        jQuery("#competentie_field").autocomplete({
            source: source,
            minLength:2,
            select: function( event, ui ) {
                jQuery('#competenties').append(
                '<div id="competentie_'+ ui.item.id  +'" class="competentie_filter">'+
                    '<span id="competentie_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="competentie_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'competentie\')">X</span></div>'+
                '</div>');
                jQuery("#competentie_field").val('');
                laadProject();
                laadCompetentie();
                return false;
            }
            
        });       
    });
    
    //filter op medewerker(s) (?)
    jQuery('#medewerker_field').on('click', function(){
        laadCompetentie();   
        //0 of meer dan 1 ? dan alleen op gebruiker zoeken.
        //is er maar 1 ? zoek dan op gebruiker, binnen een organisatie.
        // maak hier de source aan
        source = '/functions/planning/includes/archief_filter_ajax.php?filter=medewerker';
        
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
        
        jQuery("#medewerker_field").autocomplete({
            source: source,
            minLength:2,
            select: function( event, ui ) {
                jQuery('#medewerkers').append(
                '<div id="medewerker_'+ ui.item.id  +'" class="medewerker_filter">'+
                    '<span id="medewerker_te_filteren" style="display:none;">'+ ui.item.id + '</span>'+
                    '<div id="medewerker_naam">'+ ui.item.value + ' <span onclick="deleteFilter(' + ui.item.id + ', \'medewerker\')">X</span></div>'+
                '</div>');
                jQuery("#medewerker_field").val('');
                laadCompetentie();
                laadMedewerker();
                return false;
            }
        });       
    });
    
    jQuery('#zoekwoord_field').on('keydown', function(event){   
        if (event.keyCode == '13' || event.keyCode == '188') {
            
            var i = zoekwoordArray.length + 1, zoekwoord = jQuery('#zoekwoord_field').val(); 
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

    jQuery('#filters_versturen').on('click', function(){ filtersVersturen();  });
    jQuery("#van_datum").datepicker();
    jQuery("#tot_datum").datepicker();
});