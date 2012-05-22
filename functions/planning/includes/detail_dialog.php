<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $id = $_GET['id'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Prioriteit Wijzigen</title>
    </head>
    <body>
<div id="error_container">
    <div id="succes_titel"></div>
    <h2 id="error_titel" style="display: none;"></h2>
    <ul id="errors">
    </ul>
</div>
<?php
    if($_GET['action'] == 'activiteit_wijzigen'){
        $what = 'werkzaamheden, competentie, uur_aantal'; $from = 'planning_activiteit'; $where = 'id = '.$_GET['id'];
            $activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>

<form method="post" action="/functions/planning/includes/check.php" name="activiteit_toevoegen_form" id="activiteit_wijzigen_form">
    <div id="activiteit_wijzig">
        <div id="werkzaamheden_toevoegen">
            <label for="toevoegen_werkzaamheden" style="opacity: 1;">Korte beschrijving werkzaamheden</label>
            <textarea rows="4" cols="40" name="werkzaamheden" id="toevoegen_werkzaamheden" class="textarea" tabindex="2"><?php echo $activiteit['werkzaamheden']; ?></textarea>
        </div>
    
        <div id="uur_aantal_toevoegen">
            <label for="toevoegen_uur">Uur</label>
            <input type="text" value="<?php echo $activiteit['uur_aantal']; ?>" name="uur" id="toevoegen_uur" class="textfield" tabindex="3" />
        </div>
        <div id="competentie_select">
            <select class="textfield required" name="competentie" id="toevoegen_competentie">
                <option value="">Kies een competentie</option>
                <?php
                    $what = 'id, competentie AS naam'; $from = 'competentie'; $where = '1';
                        $competenties = sqlSelect($what, $from, $where);
                
                    while($competentie = mysql_fetch_array($competenties)){
                        if($competentie['id'] == $activiteit['competentie']){$selected = ' selected="selected" ';}else{$selected = '';}
                        echo '<option value="'.$competentie['id'].'"'.$selected.'>'.$competentie['naam'].'</option>';
                    }
                ?>
            </select>
        </div>
        <input type="hidden" value="activiteit_wijzigen" name="action" />
        <input type="hidden" value="<?php echo $_GET['id']; ?>"name="activiteit_id" />
        <input type="submit" onmouseout="this.className = 'button'" onmouseover="this.className = 'button btn_hover'" class="button" id="toevoegen_opslaan" value="opslaan">
    
    </div>
</form>
<script>
jQuery(function(){
    jQuery("label").inFieldLabels();
    var wijzig_options = {
        target: '#succes_titel', 
        url:        '/functions/planning/includes/check.php', 
        data: {},
        success: function() { } 
    };

    var v2 = jQuery("#activiteit_wijzigen_form").bind("invalid-form.validate",function(){
    jQuery("#error_titel").html("Er is een fout opgetreden:");})
    .validate({
    errorContainer:jQuery("#error_titel, #fouttekst"),
    errorLabelContainer:"#errors",
    wrapper:"li",
    errorElement:"span",
    rules:{werkzaamheden:"required",uur:{required:true, number: true}, competentie:"required"},
    messages:{werkzaamheden: "Vul de te vervullen werkzaamheden in. Wees echter wel kort en krachtig.",uur:{required:"Vul het aantal uur in voor deze werkzaamheden, minstens 1 uur.", number: "U dient een geldig (volledig) getal in te voeren."}, competentie: "Selecteer een competentie"},
        submitHandler: function(form) { jQuery(form).ajaxSubmit(wijzig_options); jQuery('#succes_titel').show().fadeOut(3000);}
    }); return false;
});
</script>
<?php }elseif($_GET['action'] == 'project_wijzigen'){
        $what = 'titel, beschrijving'; $from = 'project'; $where = 'id = '.$_GET['id'];
            $project = mysql_fetch_assoc(sqlSelect($what, $from, $where)); ?>
            
<form method="post" action="/functions/planning/includes/check.php" name="activiteit_toevoegen_form" id="project_wijzigen_form">
    <div id="project_wijzig">
		<div id="project_wijzig_links">
	        <div id="beschrijving_wijzigen">
	            <label for="toevoegen_werkzaamheden" style="opacity: 1;">Korte beschrijving project</label>
	            <textarea rows="4" cols="40" name="beschrijving" id="toevoegen_werkzaamheden" class="textarea" tabindex="1"><?php echo $project['beschrijving']; ?></textarea>
	        </div>
        </div>
        <div id="project_wijzig_rechts">
			<div id="titel_wijzigen">
	            <label for="titel">Titel</label>
	            <input type="text" value="<?php echo $project['titel']; ?>" name="titel" id="titel" class="textfield" tabindex="2" />
	        </div>
	        <div id="begindatum_wijzigen">
	        	<label for="begindatum">Startdatum</label>
            	<input tabindex="3" class="textfield" type="text" id="begindatum" name="begindatum" />
        	</div>
        	<div id="einddatum_wijzigen">
	        	<label for="einddatum">Einddatum</label>
            	<input tabindex="4" class="textfield" type="text" id="einddatum" name="einddatum" />
        	</div>
        </div>
        <input type="hidden" value="project_wijzigen" name="action" />
        <input type="hidden" value="<?php echo $_GET['id']; ?>"name="project_id" />
        <input type="submit" onmouseout="this.className = 'button'" onmouseover="this.className = 'button btn_hover'" class="button" id="toevoegen_opslaan" value="opslaan">
    
    </div>
</form>
<script>
jQuery(function(){
    jQuery("label").inFieldLabels();
    jQuery("#begindatum").datepicker({ dateFormat: 'dd mm yy' });
	jQuery("#einddatum").datepicker({ dateFormat: 'dd mm yy' });
    var wijzig_options = {
        target: '#succes_titel', 
        url:        '/functions/planning/includes/check.php', 
        data: {},
        success: function() { } 
    };

    var v2 = jQuery("#project_wijzigen_form").bind("invalid-form.validate",function(){
    jQuery("#error_titel").html("Er is een fout opgetreden:");})
    .validate({
    errorContainer:jQuery("#error_titel, #fouttekst"),
    errorLabelContainer:"#errors",
    wrapper:"li",
    errorElement:"span",
    rules:{titel:"required", beschrijving:"required"},
    messages:{ competentie: "Voer de titel van het project in.", beschrijving: "Vul de beschrijving van het project in. Wees echter wel kort en krachtig." },
        submitHandler: function(form) { jQuery(form).ajaxSubmit(wijzig_options); jQuery('#succes_titel').show().fadeOut(3000);}
    }); return false;
});
</script>
<?php }elseif($_GET['action'] == 'reactie_wijzigen'){
	$what = 'inhoud'; $from = 'portal_reactie'; $where = ' id ='.$_GET['id'];
		$reactie = mysql_fetch_assoc(sqlSelect($what, $from, $where)); ?>
<form method="post" action="/functions/planning/includes/check.php" name="activiteit_toevoegen_form" id="reactie_wijzigen_form">
	<div id="reactie_wijzig">
        
        <div id="tekst_wijzigen">
            <label for="reactie">Korte beschrijving project</label>
            <textarea rows="5" cols="100" name="reactie" id="reactie" class="textarea" tabindex="1"><?php echo $reactie['inhoud']; ?></textarea>
        </div>
        <input type="hidden" value="reactie_wijzigen" name="action" />
        <input type="hidden" value="<?php echo $_GET['id']; ?>"name="reactie_id" />
        <input type="submit" onmouseout="this.className = 'button'" onmouseover="this.className = 'button btn_hover'" class="button" id="reactie_wijzigen" value="opslaan">
    
    </div>
</form>
<script>
jQuery(function(){
    jQuery("label").inFieldLabels();
    var wijzig_options = {
        target: '#succes_titel', 
        url:        '/functions/planning/includes/check.php', 
        data: {},
        success: function() { } 
    };

    var v2 = jQuery("#reactie_wijzigen_form").bind("invalid-form.validate",function(){
    jQuery("#error_titel").html("Er is een fout opgetreden:");})
    .validate({
    errorContainer:jQuery("#error_titel, #fouttekst"),
    errorLabelContainer:"#errors",
    wrapper:"li",
    errorElement:"span",
    rules:{reactie:"required"},
    messages:{ reactie: "U dient tekst in te voeren" },
        submitHandler: function(form) { jQuery(form).ajaxSubmit(wijzig_options); jQuery('#succes_titel').show().fadeOut(3000);}
    }); return false;
});
</script>
<?php } ?>
    </body>
</html>