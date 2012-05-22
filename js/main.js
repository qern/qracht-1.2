function IncludeJavaScript(jsFile) { document.write('<script src="' + domain + jsFile + '"></script>'); }
jQuery(function(){
    	jQuery('#content .button').hover( function(){ jQuery(this).addClass('btn_hover'); }, function(){ jQuery(this).removeClass('btn_hover')} ); 
        jQuery('#profiel_container').hover( function(){jQuery(this).addClass('profiel_container_hover');}, function(){jQuery(this).removeClass('profiel_container_hover'); });
        jQuery('#profiel_container').on('click', function(){ jQuery('#profiel_instellingen').slideToggle(); });
        jQuery('#profiel_instellingen_menu li').hover( function(){jQuery(this).addClass('profiel_instelling_hover');}, function(){jQuery(this).removeClass('profiel_instelling_hover'); });
        jQuery('#profiel_instellingen_menu li').on('click', function(){ window.location = '<?php echo $site_name; ?>' + jQuery(this).find('a').attr('href'); });
        jQuery('#product_container').hover( function(){jQuery(this).addClass('product_container_hover');}, function(){jQuery(this).removeClass('product_container_hover'); });
        jQuery('#product_container').on('click', function(){ jQuery('#product_switch').slideToggle(); });
        jQuery('#product_container img').hover( function(){jQuery(this).addClass('product_item_hover');}, function(){jQuery(this).removeClass('product_item_hover'); });
        jQuery('#icon div').each(function(i){jQuery(this).css('opacity', '0').removeClass('hidden');})
        //dit zet de datepicker op nederlands
jQuery.datepicker.setDefaults(
	jQuery.regional = {
		closeText:"Klaar",
		prevText:"Vorige",
		nextText:"Volgende",
		currentText:"Vandaag",
		monthNames:["januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december"],
		monthNamesShort:["Jan","Feb","Mrt","Apr","Mei","Jun","Jul","Aug","Sep","Okt","Nov","Dec"],
		dayNames:["Zondag","Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag"],
		dayNamesShort:["Zon","Maa","Din","Woe","Don","Vrij","Zat"],
		dayNamesMin:["Zo","Ma","Di","Wo","Do","Vr","Za"],
		weekHeader:"Wk",
		dateFormat:"dd-mm-yy",
		firstDay:0,
		isRTL:false,
		showMonthAfterYear:false,
		yearSuffix:""
	}
)
});
jQuery('#wrapper_navi_main a').hover(
	function(){ jQuery("#icon_" + (jQuery(this).attr('class').split(' ')[1])).stop().animate({"opacity": "1"}, "fast") }, 
	function() { jQuery("#icon_" + (jQuery(this).attr('class').split(' ')[1])).stop().animate({"opacity": "0"}, "fast") }
);

