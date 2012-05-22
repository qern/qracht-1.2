jQuery(function(){
    //filter op organisatie(s) (?)
    jQuery("#periode_org").autocomplete({
        source:'/functions/planning/includes/periode-planning-ajax.php?filter=organisatie',
        minLength:2,
        select: function( event, ui ) { laadProjecten( ui.item.id ); }
    });
});
function laadProjecten ( organisatieId ){
	jQuery.ajax({
		type : 'GET',
        url : '/functions/planning/includes/periode-planning-ajax.php',
        dataType : 'html',
        data: {
            action: 'laadProjecten',
            organisatie : organisatieId
        },
        success : function(data){ jQuery('#periode_proj_input').html(data); }
	});return false;
}

function showActiviteiten ( project ){
	jQuery.ajax({
		type : 'GET',
        url : '/functions/planning/includes/periode-planning-ajax.php',
        dataType : 'html',
        data: {
            action: 'showActiviteiten',
            project : project
        },
        success : function(data){ 
			jQuery('#periode_planning_rechts').html(data);
			jQuery('#periode_planning').attr('data-project', project);
		}
	});return false;
}

function koppelActiviteitIteratie ( activiteit, iteratie ){
	jQuery.ajax({
		type : 'GET',
        url : '/functions/planning/includes/periode-planning-ajax.php',
        dataType : 'html',
        data: {
            action: 'koppelActviteitIteratie',
            activiteit : activiteit,
            iteratie :  iteratie
        },
        success : function(data){ 
			showActiviteiten( jQuery('#periode_planning').attr('data-project') );
		}
	});return false;
}
