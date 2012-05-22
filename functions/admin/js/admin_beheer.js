function toonOrganisatie(str) {
    var xmlhttp;
    if (str.length == 0) {
        document.getElementById("autocomplete_organisatie").innerHTML = "";
        return;
    }
    if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else { // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById("autocomplete_organisatie").innerHTML = xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET", "/functions/admin/includes/organisaties.php?q=" + str, true);
    xmlhttp.send();
}
function toggleReset(){
    var ele = document.getElementById('reset_disclaimer');
    if(ele.style.display =='block'){ele.style.display = 'none';}
    else{ele.style.display='block';}
}
function toggleActive(){
    var ele = document.getElementById('active_disclaimer');
    if(ele.style.display =='block'){ele.style.display = 'none';}
    else{ele.style.display='block';}
}
IncludeJavaScript('/js/standaard_js/jquery.validate.js');
$(function() {
    $("#gebruiker_toevoegen").bind("invalid-form.validate", function() {
        $("#error_titel").html("Er is een fout opgetreden:");
    }).validate({
        errorContainer: $("#error_titel, #fouttekst"),
        errorLabelContainer: "#errors",
        wrapper: "li",
        errorElement: "span",
        rules: {
            organisatie_nummer: "required",
            voorletters: "required",
            achternaam: "required",
            email:{
                required:true,
                email:true
            }
        },
        messages: {
            organisatie_nummer: "U dient een organisatie nummer van de contactpersoon op te geven",
            voorletters: "U dient de voorletters van de contactpersoon op te geven",
            achternaam: "U dient de achternaam van de contactpersoon op te geven",
            email:{
                required: "U dient het e-mailadres van de contactpersoon op te geven",
                email: "U dient een correct e-mailadres op te geven. Een voorbeeld: voorbeeld@voorbeeld.nl"
            }
        }
    });
});