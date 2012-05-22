jQuery(function(){
	jQuery('#projectoverzicht_rijen div.projectoverzicht_project').on('click', function(){
		var show = jQuery(this).attr('data-show');
		jQuery(this).find('div.projectoverzicht_competenties').show();
		jQuery('div.projectoverzicht_competenties').each(function(index){
			if(jQuery(this).attr('data-project') === show){}
			else{ jQuery(this).hide(); }
		});
	});
})
