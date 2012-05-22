<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $what = 'werkzaamheden, aantal_uur, competentie'; $from = 'urentool_registratie'; $where = 'id = '.$_GET['id'];
        $uren = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Prioriteit Wijzigen</title>
    </head>
    <body>
        <div id="wijzig_error_container">
            <h2 id="wijzig_succes_titel"></h2>
            <h2 id="wijzig_error_titel"></h2>
            <ul id="wijzig_errors"> </ul>
        </div>
        <form method="post" action="<?php echo $etc_root.'functions/'.$functie_get.'/includes/ajax.php' ?>" id="uren_wijzigen">
            <div id="form_left">
                <textarea class="werkzaamheden_text textarea" name="werkzaamheden" rows="3" cols="30"><?php echo $uren['werkzaamheden']; ?></textarea>
            </div>
            <div id="form_right">
                <select id="wijzig_competentie" name="competentie" class="textfield">
                    <?php 
                    $what = 'id, competentie'; $from = 'competentie'; $where = '1';
                        $competenties = sqlSelect($what, $from, $where);
                    
                    while($competentie = mysql_fetch_array($competenties)){
                        if( $competentie['id'] == $uren['competentie'] ){?>
                    <option value="<?php echo $competentie['id'] ?>" selected="selected"><?php echo $competentie['competentie']; ?></option>        
                    <?php }else{ ?>
                    <option value="<?php echo $competentie['id'] ?>"><?php echo $competentie['competentie']; ?></option>
                    <?php }//einde else
                    }?>
                </select>
                <input class="aantal_uur_text textfield" type="text" value="<?php echo $uren['aantal_uur'];?>" name="aantal_uur" />
                <input type="hidden" id="uren_id" name="uren_id" value="<?php echo $_GET['id']; ?>" />
                <input type="submit" class="button" value="Wijzigen" />
            </div>
        </form>
        <script>
        jQuery(function(){
            var wijzig_options = { 
                target:     '#wijzig_succes_titel',
                url:        '/functions/urentool/includes/ajax.php', 
                data: { action: 'updateUren' },
                success:    function() { 
                    var opt = jQuery('#laadUren').val().split('_'); laadUren( opt[0], opt[1], opt[2], opt[3] ); 
                    var archief_opt = jQuery('#archief_nu').text(); archief(archief_opt, 'archief');
                } 
            };
            
            var v2 = jQuery("#uren_wijzigen").bind("invalid-form.validate",function(){
            $("#wijzig_error_titel").html("Er is een fout opgetreden:");})
            .validate({
                errorContainer:$("#wijzig_error_titel, #fouttekst"),
                errorLabelContainer:"#wijzig_errors",
                wrapper:"li",
                errorElement:"span",
                rules:{werkzaamheden:"required", aantal_uur:{required:true, number: true}},
                messages:{werkzaamheden:"U dient een korte beschrijving van de gemaakte uren in te voeren.", aantal_uur:{required:"U dient het aantal gemaakte uren in te voeren", number: "Het aantal gemaakte uren dient een (decimaal) getal te zijn, met een .  in plaats van een ,"}},
                submitHandler: function(form) { jQuery(form).ajaxSubmit(wijzig_options); jQuery('#succes_titel').show().fadeOut(3000);}
            }); return false;
        });
        </script>
    </body>
</html>