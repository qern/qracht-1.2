//IncludeJavaScript('/functions/urentool/js/geschiedenis.js');
IncludeJavaScript('js/standaard_js/jquery.infieldlabel.min.js');
IncludeJavaScript('js/standaard_js/jquery.validate.min.js');
IncludeJavaScript('js/standaard_js/jquery.form.js');
jQuery(function(){
	jQuery("#datum_kiezen").datepicker({ dateFormat: 'd MM yy' });
	jQuery('#datum_verzend').on('click', function(){
		var opt = jQuery('#invoer').attr('data-laadUren').split('_'), datum = jQuery("#datum_kiezen").val(); 
		
		laadUren( opt[0], opt[1], datum, 'onb'); 
		jQuery("#datum_kiezen").val('');		
	});
	jQuery("#bedrijf").autocomplete({
		source:"/functions/urentool/includes/autocomplete.php",
		minLength:2,
		select: function( event, ui ) { laadActiviteiten( ui.item.id ); }
	});
	
	jQuery('#dag_gereed').on('click', function(){ dagGereed(); });
	jQuery('table#urentable img.edit').on('click', function(){ wijzigUren( jQuery(this).attr('id').split('_')[1] ); });
	jQuery('table#urentable img.delete').on('click', function(){ deleteUren( jQuery(this).attr('id').split('_')[1] ); });
	
	jQuery("label").inFieldLabels();
	jQuery('#activiteit').on('change', function(){ console.log(this.val()); });
	jQuery('#in_te_vullen_dagen li').hover( function(){jQuery(this).addClass('selecteer_dag_hover');}, function(){jQuery(this).removeClass('selecteer_dag_hover');} );
	
});
function laadUren( gebruiker, gereed, datum, datum_id ){
	if(datum_id == null){datum_id = 'onb';}
	jQuery('#dag_gereed').off();
	jQuery.ajax({
		type: 'GET',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: {
			gebruiker: gebruiker,
			gereed: gereed,
			datum: datum,
			datum_id : datum_id
		},
		success: function(data){
			jQuery('#invoer_titel h2').text(datum);
			jQuery('#datum_id_val').val(datum_id);
			jQuery('#datum_val').val(datum);
			if(gereed == 1){ gereed = 0; }//we willen niet in de gereed blijven zitten !
			jQuery('#invoer').attr('data-laadUren', gebruiker + '_' + gereed + '_' + datum + '_' + datum_id);
			jQuery('#invoer_tabel').html(data);
			jQuery('table#urentable img.edit').on('click', function(){ wijzigUren( jQuery(this).attr('id').split('_')[1] ); });
			jQuery('table#urentable img.delete').on('click', function(){ deleteUren( jQuery(this).attr('id').split('_')[1] ); });
		}
	});return false;
}
function wijzigUren( id ){
    var url = '/functions/urentool/includes/wijzig-uren.php?id=' + id;
    $('#dialog').load(url).dialog({ minWidth: 610, minHeight: 200, height:250, title: 'Wijzig uren' }); 
}

function deleteUren( id ){
	jQuery.ajax({
		type: 'POST',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: { action: 'deleteUren', id: id },
		success: function(data){ 
			jQuery('#succes_titel').html(data).show(); 
			var opt = jQuery('#invoer').attr('data-laadUren').split('_'); laadUren( opt[0], opt[1], opt[2], opt[3] );
			var archief_opt = jQuery('#archief_nu').text(); archief(archief_opt, 'archief');
			//jQuery('#succes_titel').show().fadeOut(3000);
			setTimeout("jQuery('#succes_titel').fadeOut(3000)",2000);
		}
	});return false;
}

function laadActiviteiten( organisatie ){
	jQuery.ajax({
		type: 'GET',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: { action: 'getActiviteiten', organisatie: organisatie },
		success: function(data){ jQuery('#activiteit_container').html(data); jQuery('#activiteit').on('change', function(){ console.log( jQuery(this).val() ); }); }
	});return false;
}

function selectCompetentie( activiteit ){
	jQuery.ajax({
		type: 'GET',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: { action: 'selectCompetentie', activiteit: activiteit },
		success: function(data){ jQuery('#competentie').html(data);}
	});return false;
}

function archief( str, doel ){
	if (str=="")  jQuery("archief").html("");
	else{
		jQuery.ajax({
			type: 'GET',
			url: '/functions/urentool/includes/geschiedenis.php',
			dataType: 'html',
			data: { q: str, doel:doel},
			success: function(data){ jQuery('#archief').html(data);}
		});return false;
	}
}
function dagGereed(){
	var opt = jQuery('#invoer').attr('data-laadUren');
	jQuery.ajax({
		type: 'GET',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: { action: 'dagGereed', data: opt },
		success: function(data){  
			jQuery('#invoer_tabel').html(data); 
			
		}
	});return false;
}
function reloadDagenLijst(){
	jQuery.ajax({
		type: 'GET',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: { action: 'reloadDagenLijst'},
		success: function(data){  jQuery('#in_te_vullen_dagen').html(data); }
	});return false;
}
function reloadForm(){
	jQuery.ajax({
		type: 'GET',
		url: '/functions/urentool/includes/ajax.php',
		dataType: 'html',
		data: { action: 'reloadForm', laadUren : jQuery('#laadUren').val(), datumId : jQuery('#datum_id').val()},
		success: function(data){ 
			jQuery('#uren_toevoegen_form').html(data);
			jQuery("label").inFieldLabels();
			jQuery("#bedrijf").autocomplete({
				source:"/functions/urentool/includes/autocomplete.php",
				minLength:2,
				select: function( event, ui ) { laadActiviteiten( ui.item.id ); }
			});
		}
	});return false;
}
