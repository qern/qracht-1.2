IncludeJavaScript('js/standaard_js/jquery.infieldlabel.min.js');
jQuery(function(){
	jQuery("label").inFieldLabels();
	jQuery('#activators').on('click', 'img', function(){
	    //i want it to hide the other element, show the correct one and add the class 'active'
	    var hide = jQuery('#activators img.active_activator').attr('alt');
	    var show = jQuery(this).attr('alt');
	    if(hide === show){
	        // do nothing
	    }else{
	        jQuery('#'+hide).slideToggle(300);
	        jQuery('#activators img.active_activator').removeClass('active_activator');
	        jQuery(this).addClass('active_activator');
	        jQuery('#'+show).slideToggle(300);
	    }
	});
	jQuery('#filter_prioriteit img').on('click', function(){
		
		jQuery('#filter_prioriteit img').each( function( intIndex ){ if(jQuery(this).hasClass('active_prio')){ jQuery(this).removeClass('active_prio'); } } );
		jQuery(this).addClass('active_prio');
		
		filtersVersturen(); 
	});
	
	jQuery('#filter_recent').on('click', function(){
		jQuery.ajax({
            type:"GET", dataType : 'html',
            url:'/functions/planning/includes/check.php',
            data:{ action : 'alleActiviteitenGezien' },
            success:function(data){ reloadPlanning('alles', iteratie_Id, ''); displayAlleActiviteitenGezien(iteratie_Id); }
       }); 
	});
	
	jQuery('#uren').on('click', '.iteratie_uren', function(){
	    //i want it to hide the other element, show the correct one and add the class 'active'
	    var hide = jQuery('#uren div.active_uren').attr('id').split('_')[1];
	    var show = jQuery(this).attr('id').split('_')[1];
	    console.log(hide); console.log(show);
	    if(hide === show){
	        // do nothing
	    }else{
	        jQuery('#iteratie_' + hide + ' .ajax_response').slideUp(300);
	        jQuery('#uren div.active_uren').removeClass('active_uren');
	        jQuery(this).addClass('active_uren');
	        jQuery.ajax({type : 'GET',url : '/functions/planning/includes/competentie_ajax.php',dataType : 'html',data: {action: 'loadIteratie', id : show},success : function(data){ jQuery('#iteratie_' + show + ' .ajax_response').html(data);}});
	        jQuery('#iteratie_' + show + ' .ajax_response').slideDown(300);
	    }
	});	
});
function changePrioriteit( id, kolom ){
    var url = '/functions/planning/includes/prio.php?id=' + id + '&kolom=' + kolom;
    jQuery('#dialog').load(url).dialog({ minWidth: 353, minHeight: 420, title: 'Wijzig prioriteit' }); 
}
function displayAlleActiviteitenGezien(iteratieId){
	jQuery.ajax({
            type:"GET", dataType : 'html',
            url:'/functions/planning/includes/check.php',
            data:{ action : 'displayAlleActiviteitenGezien', iteratie_id : iteratieId },
            success:function(data){ jQuery('#filter_recent').html(data); }
   });
}

