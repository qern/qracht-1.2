function IncludeJavaScript(jsFile){document.write('<script type="text/javascript" src="'+jsFile+'"></script>');}IncludeJavaScript('/lib/ckeip/ckeip.js');IncludeJavaScript('/lib/ckeip/ckeditor/ckeditor.js');IncludeJavaScript('/lib/ckeip/ckeditor/adapters/jquery.js');IncludeJavaScript('/lib/uploadify/jquery.uploadify.v2.1.4.min.js');IncludeJavaScript('/functions/meldingen/js/jquery.tagsinput.js');IncludeJavaScript('/lib/uploadify/swfobject.js');
function toggle(){var ele = document.getElementById('reactie_formulier');var text = document.getElementById('toon_formulier');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
//dit hoeft pas te worden geladen als de rest van de pagina klaar is (php + ckeditor)
$(window).load(function(showStuff){
//wordt meerdere malen gebruikt: tooltip
$(function(){$(".tooltip").tipTip({defaultPosition:"left_top",maxWidth:"450px"});});    

//begin dan boven aan.. je kunt acties activeren
$(function(){var showText='Acties';var hideText='Acties verbergen';var is_visible=false;$('#activiteit_acties').prev().append('<a href="#" class="acties_activate tooltip" title="Voer hier enkele acties uit voor deze activiteit">'+showText+'</a>');$('#activiteit_acties').hide();$('a.acties_activate').click(function(){is_visible=!is_visible;$(this).html((!is_visible)?showText:hideText);$(this).parent().next('#activiteit_acties').toggle('fast');return false;});});

//de verschillende meldingen bovenaan
$(function(){ $('#succesvol_verstuurd_een').fadeOut(7000); });$(function(){ $('#succesvol_verstuurd_meer').fadeOut(8000); });$(function(){ $('#gewijzigde_prio').fadeOut(8000); });

//daarna de tabs
$('#gegevens_toevoegen').tabs();$('#gegevens_toevoegen').css('display','block');

//als er op 'reactie toevoegen' wordt geklikt,komt het formulier


//laadt aan laatste de verschillende scripts in om te kunnen uploaden (tab 2)

//en als laatste het script om te kunnen uploaden
$(function(){$("a#bestand_upload").fancybox({'overlayShow':true,'overlayColor':'#000','overlayOpacity':0.7,'hideOnContentClick':true,'transitionIn':'elastic','transitionOut':'elastic','speedIn':600,'speedOut':200,'width':500,'height':500,'onClosed':function(){parent.location.reload(true);;}}); });

$(function(){$("a.prioriteit_wijzigen").fancybox({'overlayShow': true,'overlayColor': '#000','overlayOpacity': 0.7,'hideOnContentClick': true,'transitionIn': 'elastic','transitionOut': 'elastic','speedIn': 600,'speedOut': 200,'width': 400,'height': 370,'onClosed':function(){parent.location.reload(true);;}});});

 $(function(){$("a.type_wijzigen").fancybox({'overlayShow': true,'overlayColor': '#000','overlayOpacity': 0.7,'hideOnContentClick': true,'transitionIn': 'elastic','transitionOut': 'elastic','speedIn': 600,'speedOut': 200,'width': 400,'height': 370,'onClosed':function(){parent.location.reload(true);;}});});

$(function(){$('#custom_file_upload').uploadify({'uploader':'uploadify/uploadify.swf','script':'uploadify/uploadify.php','cancelImg':'uploadify/cancel.png','folder':'uploads','multi':true,'auto':true,'queueID':'custom-queue','queueSizeLimit':5,'simUploadLimit':5,'removeCompleted':false,'onSelectOnce':function(event,data){$('#status-message').text(data.filesSelected+' bestanden zijn toegevoegd aan de queue.');},'onAllComplete':function(event,data){$('#status-message').text(data.filesUploaded+' bestanden geupload, '+data.errors+' errors.');}});});

//laadt dan de laatste tab (3) voor tags
$(function(){ $('#tag_input').tagsInput({ autocomplete_url:'/functions/planning/includes/autocomplete_tag.php' /*jquery ui autocomplete requires a json endpoint*/    }); });
});