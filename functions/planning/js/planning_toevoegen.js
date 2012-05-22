IncludeJavaScript('js/standaard_js/jquery.validate.min.js');
$(function(){
	$("#toevoegen_klant").autocomplete({
	source:"/functions/planning/includes/autocomplete_bedrijf.php",
	minLength:2,
	select: function( event, ui ) {
		$.ajax({
			type : 'GET',
			url : '/functions/planning/includes/project_ajax.php',
			dataType : 'html',
			data: {action: 'loadProjecten', bedrijf : ui.item.id},
			success : function(data){$('#project_select').html(data);}
		});//return false;
       }
	});
});
function refreshProjectPosition(bedrijf){
	$.ajax({
			type : 'GET',
			url : '/functions/planning/includes/project_ajax.php',
			dataType : 'html',
			data: {action: 'loadProjecten', bedrijf : bedrijf},
			success : function(data){$('#project_select').html(data);}
		});//return false;
}
$(function(){
	jQuery("#toevoegen_begindatum").datepicker({ dateFormat: 'dd mm yy' });
	jQuery("#toevoegen_einddatum").datepicker({ dateFormat: 'dd mm yy' });
	$("#activiteit_toevoegen_form").bind("invalid-form.validate",function(){
		$("#error_titel").html("Er is een fout opgetreden:");})
		.validate({
			errorContainer:$("#error_titel, #fouttekst"),
			errorLabelContainer:"#errors",
			wrapper:"li", 
			errorElement:"span",
			rules:{toevoegen_klant:"required", toevoegen_project:"required",toevoegen_werkzaamheden:"required",toevoegen_uur:{required:true, digits: true, minlength: 1, min: 1}, toevoegen_competentie:"required"},
			messages:{toevoegen_klant: "Vul de naam van de organisatie in. Als de organisatie nog niet bestaat, maak deze dan eerst aan alstublieft.", toevoegen_project: "Selecteer een project of <a href=\"/planning/project-toevoegen\">voeg deze toe<a>", toevoegen_werkzaamheden: "Vul de te vervullen werkzaamheden in. Wees echter wel kort en krachtig.",toevoegen_uur:{required:"Vul het aantal uur in voor deze werkzaamheden, minstens 1 uur.", digits: "U dient een geldig (volledig) getal in te voeren.", minlength: "Vul het aantal uur in voor deze werkzaamheden, minstens 1 uur.", min: "Vul het aantal uur in voor deze werkzaamheden, minstens 1 uur."}, toevoegen_competentie: "Selecteer een competentie"}
		});
	$("#project_toevoegen_form").bind("invalid-form.validate",function(){
		$("#error_titel").html("Er is een fout opgetreden:");})
		.validate({
			errorContainer:$("#error_titel, #fouttekst"),
			errorLabelContainer:"#errors",
			wrapper:"li", 
			errorElement:"span",
			rules:{toevoegen_klant:"required", toevoegen_titel:"required",toevoegen_beschrijving:"required"},
			messages:{toevoegen_klant: "Vul de naam van de organisatie in. Als de organisatie nog niet bestaat, maak deze dan eerst aan alstublieft.", toevoegen_titel: "Vul een titel in voor dit project", toevoegen_beschrijving: "Vul een beschrijving van het project in. Wees echter wel kort en krachtig."}
		});
});