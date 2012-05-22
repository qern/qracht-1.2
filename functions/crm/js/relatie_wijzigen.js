function showStuff(){$(function(){$(".tooltip").tipTip({defaultPosition:"left_top",maxWidth:"450px"});});
$(function(){ $("#bedrijf").autocomplete({source: "/functions/crm/includes/autocomplete_bedrijf.php",minLength:2});});
);}$(document).ready(showStuff);