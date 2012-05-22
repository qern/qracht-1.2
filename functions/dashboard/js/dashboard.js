jQuery(function(){
	laadtUren(0);
	jQuery('#select_werknemer').on('change', function(){ laadtUren(jQuery(this).val()); }); 
});
function laadtUren(werknemer){
	jQuery.ajax({
		    type:"GET", dataType : 'html',data:{ q : werknemer },
		    url:'/functions/dashboard/includes/iteratie.php',
		    success:function(data){ jQuery('#iteratie_ajax').html(data); }
	});return false;
}
