function showStuff(){$(function(){$(".tooltip").tipTip({defaultPosition:"left_top",maxWidth:"450px"});});$(function(){ $("#organisatie_formulier").validate({rules:{email:{email: true}},messages:{email:{email:"Vul een geldig e-mailadres in. Zoals <b>voorbeeld@voorbeeld.nl</b>"}}});});}$(document).ready(showStuff);