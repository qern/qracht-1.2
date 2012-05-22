function showStuff(){
function openFilters() {var ele = document.getElementById("filters");var text = document.getElementById("toon_filters");if(ele.style.display == "block")
 {ele.style.display = "none"; document.getElementById('filter_activator').src="/functions/planning/css/images/filter.png";}
 else {ele.style.display = "block"; document.getElementById('filter_activator').src="/functions/planning/css/images/remove_filter.png";}}
 $(function(){$("a.prioriteit_wijzigen").fancybox({'overlayShow': true,'overlayColor': '#000','overlayOpacity': 0.7,'hideOnContentClick': true,'transitionIn': 'elastic','transitionOut': 'elastic','speedIn': 600,'speedOut': 200,'width': 400,'height': 370,'onClosed':function(){parent.location.reload(true);;}});});
 $(function(){$("a.type_wijzigen").fancybox({'overlayShow': true,'overlayColor': '#000','overlayOpacity': 0.7,'hideOnContentClick': true,'transitionIn': 'elastic','transitionOut': 'elastic','speedIn': 600,'speedOut': 200,'width': 400,'height': 370,'onClosed':function(){parent.location.reload(true);;}});});
 $(function(){$(".tooltip").tipTip({defaultPosition:"left_top",maxWidth:"450px"});});
}
$(document).ready(showStuff);