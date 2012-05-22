IncludeJavaScript('js/standaard_js/jquery.bxSlider.min.js');

huidigeDatum = Math.round(new Date().getTime() / 1000 ) ;
startDatum = 1322719200;

function zoekNieuws(str){
var xmlhttp;
if (str.length==0)
  { 
  document.getElementById("overig_nieuwslijst").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("overig_nieuwslijst").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","/functions/home/includes/ajax.php?action=zoek_nieuws&q="+str,true);
xmlhttp.send();
}

function filterHome(str){
var xmlhttp;
if (str.length==0)
  { 
  document.getElementById("overig_nieuws").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("overig_nieuws").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","/functions/home/includes/ajax.php?action=filter_nieuws&q="+str,true);
xmlhttp.send();
}


/*
function herlaadNieuwslijst(filter, interval) {
        $('#waiting').show(600);
        $('#overig_nieuwslijst').html('');
        $.ajax({
            type : 'POST',
            url : '/portal/functions/home/includes/nieuwslijst.php',
            dataType : 'html',
            data: { filter : filter, interval: interval },
            success : function(data){
                $('#waiting').hide(600);
                $('#overig_nieuwslijst').html(data);
                if(((huidigeDatum - startDatum) - ((60*60*24*10) * interval) ) > 0){
                    $('#overige_nieuws_meer_lezen').html('<span class="next_batch" onclick="leesVerderNieuwslijst(\''+ filter +'\', ' + (interval + 1) + ')">Meer laden...</span>');
                }
            }
        });

        return false;
};*/



function toggle_bestanden(){var ele = document.getElementById('bestanden');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_fotos(){var ele = document.getElementById('fotos');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_form(){var ele = document.getElementById('formulier');if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
//function toggle_reacties(id, target){var ele = document.getElementById(target + '_reactielijst_' + id);if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_reacties(id, target){var ele = document.getElementById(target + '_reactielijst_' + id);if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}
function toggle_reageren(id, target){var ele = document.getElementById(target + '_reageren_' + id);if(ele.style.display =='block'){ele.style.display = 'none';}else{ele.style.display='block';}}

$(document).ready(leesVerderNieuwslijst('alles', 1));
$(function(){$('#foto_lijst').bxSlider();});
$(function(){$("a.iframe").fancybox({'titleShow': false,'overlayShow': true,'overlayColor': '#000','overlayOpacity': 0.7,'hideOnContentClick': true, 'centerOnScroll': true,'scrolling': 'auto', 'transitionIn': 'elastic','transitionOut': 'elastic','speedIn': 600,'speedOut': 200,'width': 750,'height': 750});});   

