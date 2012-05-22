IncludeJavaScript('js/standaard_js/jquery.validate.js');
IncludeJavaScript('js/standaard_js/jquery.infieldlabel.min.js');

function afbeeldingOmschrijving(fotoId, str){
            var ajaxRequest;  // The variable that makes Ajax possible!
            
            try{// Opera 8.0+, Firefox, Safari
                ajaxRequest = new XMLHttpRequest();
            } catch (e){
                try{// Internet Explorer Browsers
                    ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    try{
                        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch (e){// Something went wrong
                        alert("Your browser broke!");
                        return false;
                        }
                    }
                }
            
            ajaxRequest.open("GET", domain+"functions/nieuws/includes/album_check.php?foto_id="+fotoId +"&caption="+str, true);
            ajaxRequest.send(null);
}

function albumNaam(albumId, str){
            var ajaxRequest;  // The variable that makes Ajax possible!
            
            try{// Opera 8.0+, Firefox, Safari
                ajaxRequest = new XMLHttpRequest();
            } catch (e){
                try{// Internet Explorer Browsers
                    ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    try{
                        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch (e){// Something went wrong
                        alert("Your browser broke!");
                        return false;
                        }
                    }
                }
            
            ajaxRequest.open("GET", domain+"functions/nieuws/includes/album_check.php?album="+albumId +"&naam="+str, true);
            ajaxRequest.send(null);
}

//$(function(){$(".tooltip").tipTip({defaultPosition:"left_top"});});
function toggle_upload(){var ele = document.getElementById('add_file');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_tags(){var ele = document.getElementById('new_tag');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_album(){var ele = document.getElementById('add_photo');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}

jQuery('#beschrijving').keyup(function(){
    var text = jQuery('#beschrijving').val();
    var counter;
    var maxLength = 200;
    if(text.length < 140){
        counter = maxLength - text.length;
        text = text.substr(0,maxLength);        
        jQuery('#overgebleven_beschrijving').removeClass('code_red code_orange');
    }else if(text.length < maxLength){
        counter = maxLength - text.length;
        text = text.substr(0,maxLength);        
        jQuery('#overgebleven_beschrijving').removeClass('code_red').addClass('code_orange');
    }else if(text.length == maxLength){
        text = text.substr(0,maxLength);
        counter = 0;
        jQuery('#overgebleven_beschrijving').removeClass('code_orange').addClass('code_red');
    }else{
        text = text.substr(0,maxLength);
        counter = 0;
        jQuery('#overgebleven_beschrijving').removeClass('code_orange').addClass('code_red');
    }
    jQuery('#beschrijving').val(text);
    jQuery('#overgebleven_aantal').html(counter);
    
});
    
jQuery(function(){
	jQuery("label").inFieldLabels();
    jQuery(".datum_kiezen").datepicker();
    jQuery("#mededeling_invoer").validate({
        rules:{
            titel:{required:true},
            beschrijving:{required:true},
            inhoud:{required:true},
            publicatiedatum:{required:true}
        },
        messages:{
            titel:{required:"Vul de titel van de mededeling in."},
            beschrijving:{required:"Vul de beschrijving van de mededeling in."},
            inhoud:{required:"vul de inhoud van de mededeling in."},
            publicatiedatum:{required:"Vul de publicatiedatum van de mededeling in."}
        }
    });
    jQuery('.check:button').toggle(function(){
        jQuery('input:checkbox').attr('checked','checked');
        jQuery(this).val('Deselecteer alle')
    },function(){
        jQuery('input:checkbox').removeAttr('checked'); 
        jQuery(this).val('Selecteer alle');        
    });
    jQuery("a.iframe").fancybox({
	    'overlayShow': true,
	    'overlayColor': '#000',
	    'overlayOpacity': 0.7,
	    'hideOnContentClick': true,  
	    'titleShow': false,
	    'transitionIn': 'elastic',
	    'transitionOut': 'elastic',
	    'speedIn': 600,
	    'speedOut': 200,
	    'centerOnScroll': true,
	    'scrolling': 'auto',
	    'cyclic': true,
	    'width': 750,
	    'height': 750
    });
});