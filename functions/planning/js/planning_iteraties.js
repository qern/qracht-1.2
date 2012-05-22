jQuery(function(){
	jQuery('#iteraties div.iteratie_rij').hover(
		function(){ jQuery(this).addClass('iteratie_rij_hover') },
		function(){ jQuery(this).removeClass('iteratie_rij_hover') }
	)
	jQuery('#iteraties div.iteratie_rij').on('click', function(){
		var show = jQuery(this).attr('data-show');
		jQuery(this).find('div.iteratie_sub').show();
		jQuery('div.iteratie_sub').each(function(index){
			if(jQuery(this).attr('data-iteratie') === show){}
			else{ jQuery(this).hide(); }
		});
	});
})
