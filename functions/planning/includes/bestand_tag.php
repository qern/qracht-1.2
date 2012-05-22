<?php
session_start();
require ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
require($_SERVER['DOCUMENT_ROOT']."/lib/phpmailer/class.phpmailer.php");
//wordt gelezen door het iframe als er op 'bestand toevoegen' wordt geklikt.
if($_GET['action'] == 'bestand'){
?>
<hmtl>
        <head>
        <style type="text/css">
            #custom-demo .uploadifyQueueItem {
                background-color: #FFFFFF;
                border: none;
                border-bottom: 1px solid #E5E5E;
                font: 11px Verdana, Geneva, sans-serif;
                height: 50px;
                margin-top: 0;
                padding: 10px;
                width: 350px;
            }

            #custom-demo .uploadifyError { background-color: #FDE5DD !important;  border: none !important; border-bottom: 1px solid #FBCBBC !important;}
            #custom-demo .uploadifyQueueItem .cancel  {float: right; }
            #custom-demo .uploadifyQueue .completed { color: #C5C5C5; }
            #custom-demo .uploadifyProgress { background-color: #E5E5E5; margin-top: 10px; width: 100%; }
            #custom-demo .uploadifyProgressBar { background-color: #0099FF; height: 3px; width: 1px; }
            #custom-demo #custom-queue { border: 1px solid #E5E5E5;height: 213px;  margin-bottom: 10px; width: 370px; }                
        </style>
        </head>
        <body>

            <div id="custom-demo" class="demo">
                <h2>Custom Demo</h2>

                    <div><p>Je kan meerdere bestanden tegelijkertijd uploaden door er meerdere tegelijkertijd te selecteren</p></div>

                    <div class="demo-box">

                        <div id="status-message">Selecteer één of meerdere bestanden om te uploaden</div>

                        <div id="custom-queue"></div>
                        
                        <input id="upload_documenten" type="file" name="Filedata" />

                        <p><a href="javascript:jQuery('#upload_documenten').uploadifyClearQueue()">Cancel alle uploads</a></p>
                    
                    </div>
          </div>
        <script type="text/javascript" src="<?php echo $site_name;?>js/standaard_js/jquery-1.5.1.min.js" type="text/javascript"></script> 
        <script type="text/javascript" src="<?php echo $site_name;?>lib/uploadify/swfobject.js"></script>
        <script type="text/javascript" src="<?php echo $site_name;?>lib/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
        <script type="text/javascript">
           $(document).ready(function() {
                $('#upload_documenten').uploadify({
                    'uploader'       : '<?php echo $site_name;?>lib/uploadify/uploadify.swf',
                    'script'         : 'upload_activiteit_bestand.php?ids=<?php echo $_GET['activiteit_id'].'_'.$login_id ?>',
                    'cancelImg'      : '<?php echo $site_name;?>lib/uploadify/cancel.png',
                    'folder'         : '/files/planning_documenten/<?php echo $_GET['activiteit_id'] ?>',
                    'multi'          : true,
                    'auto'           : true,
                    //'fileExt'        : '*.jpg;*.gif;*.png',
                    //'fileDesc'       : 'Image Files (.JPG, .GIF, .PNG)',
                    'queueID'        : 'custom-queue',
                    'queueSizeLimit' : 5,
                    'simUploadLimit' : 5,
                    'removeCompleted': false,
                    'onSelectOnce'   : function(event,data) {
                        $('#status-message').text(data.filesSelected + ' bestanden zijn toegevoegd aan de queue.');
                    },
                    'onAllComplete'  : function(event,data) {
                        $('#status-message').text(data.filesUploaded + ' bestanden geupload, ' + data.errors + ' errors.');
                    }
                });                
           });
 </script>
        </body>

</html>
<?php } 
//wordt gelezen door het iframe als er op 'tag toevoegen' wordt geklikt.
//er zit ook een autocomplete op. Op basis van Alle bekende tags
/*if($_GET['action'] == 'tag'){

<hmtl>
        <head>
            <link rel="stylesheet" type="text/css" href="/css/standaard_css/main.css" />
            <link rel="stylesheet" type="text/css" href="/css/standaard_css/jquery-ui-1.8.15.custom.css" />
            <link rel="stylesheet" type="text/css" href="<?php echo $site_name; ?>/functions/planning/css/planning.css" />
        </head>
        <body>
            <form id="tag_toevoegen_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                <div id="tag_titel_row"> 
                    <label for="tag_input">Voeg een tag toe</label> 
                </div>
                <div id="tag_toevoegen_row">
                    <input type="hidden" name="activiteit_id" value="<?php echo $_GET['activiteit_id'] ?>" />
                    <input type="hidden" name="login_id" value="<?php echo $login_id ?>" />
                    <input type="hidden" name="action" value="tag_toevoegen" />
                    <input type="text" name="tag" id="tag_input" class="textfield" />
                    <input type="submit" id="tag_submit" value="toevoegen" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" />
                </div>
            </form>
            <script src="/js/standaard_js/jquery-1.5.1.min.js" type="text/javascript"></script> 
            <script src="/js/standaard_js/jquery-ui-1.8.15.custom.min.js" type="text/javascript"></script> 
            <script type="text/javascript">
            $(function() {
                $( "#tag_input" ).autocomplete({
                        source: "autocomplete_tag.php",
                        minLength: 2,
                });           
            });
            </script>
        </body>

</html>
    
 }*/ 
//de code als er een tag toegevoegd moet worden. Aan het einde is een stukje script om de onderliggende pagina te laten herladen (en dus iframe af te sluiten)
if($_POST['action'] == 'tag'){
    
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_POST['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];
    
    $tags = explode(',', $_POST['tags']);
    $bestaande_tags = explode(',', $_POST['bekende_tags']);
    $what = 'DISTINCT tag, id'; $from = 'tag'; $where='actief = 1';
    $aantal_tags = countRows($what, $from, $where);
    $tag_result = sqlSelect($what, $from, $where);
    if($aantal_tags > 0){
        foreach($tags as $tag){
            //hoeveel tags zijn er met deze tagnaam, tel ze
            $what = 'DISTINCT a.id, b.planning_activiteit_id'; $from = 'tag a, planning_tag b'; $where="a.tag = '$tag' AND b.tag_id = a.id";
            $aantal_tags = countRows($what, $from, $where);
            
            //is het er 1 of meer, dan kijken of het een bekende is. zo niet: toevoegen
            //niet bekend ? toevoegen.
            if($aantal_tags > 0){
                $tag = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
                if($_POST['activiteit_id'] == $tag['planning_activiteit_id']){
                    //doe niets... want de tag is al bekend bij deze activiteit
                    $geen_tags = 1;
                }else{
                    //aangezien de ingevoerde tag wel bestaat, maar (nog) niet is gekoppeld aan deze activiteit.
                    //doen we dat nu !
                    $table = 'planning_tag'; $what = 'tag_id, planning_activiteit_id, gewijzigd_op, gewijzigd_door';
                    $with_what = $tag['id'].', '.$_POST['activiteit_id'].', NOW(), '.$_POST['user'];
                        $tag_koppelen = sqlInsert($table,$what,$with_what);
                }
            }else{
                    //aangezien de ingevoerde tag (nog) niet bestaat, maken we deze aan.
                    $table = 'tag'; $what = 'tag, toegevoegd_op, toegevoegd_door';
                    $with_what = "'$tag', NOW(), ".$_POST['user'];
                    $tag_toevoegen = sqlInsert($table,$what,$with_what);
                    
                    //ook koppelen we deze nieuwe tag aan de activiteit.
                    $table = 'planning_tag'; $what = 'tag_id, planning_activiteit_id, gewijzigd_op, gewijzigd_door';
                    $with_what = '(SELECT MAX(a.id) FROM tag a WHERE actief = 1), '.$_POST['activiteit_id'].', NOW(), '.$_POST['user'];
                    $tag_koppelen = sqlInsert($table,$what,$with_what);        
            }
        }
        $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "De tags bij de activiteit '$werkzaamheden' zijn gewijzigd";
                    $htmlbody = "
                    <p><b>De tags bij de activiteit '$werkzaamheden' zijn gewijzigd</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu zijn er door $gewijzigd_door tag(s) gewijzigd bij de activiteit '$werkzaamheden'.<br />
                    Klik op de volgende link om de tag(s) te bekijken:<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu zijn er door $gewijzigd_door tag(s) gewijzigd bij de activiteit '$werkzaamheden'
                    Klik op de volgende link om de tag(s) te bekijken:
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id'].'#tab3';
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
                
            }
        }//afsluiting voor de waarschuwingen
    }else{
        //er bestaan nog geen tags.. dan doen we dit
        foreach($tags as $tag){
            
            //aangezien de ingevoerde tag (nog) niet bestaat, maken we deze aan.
            $table = 'tag'; $what = 'tag, toegevoegd_op, toegevoegd_door';
            $with_what = "'$tag', NOW(), ".$_POST['user'];
            $tag_toevoegen = sqlInsert($table,$what,$with_what);
                                
            //ook koppelen we deze nieuwe tag aan de activiteit.
            $table = 'planning_tag'; $what = 'tag_id, planning_activiteit_id, gewijzigd_op, gewijzigd_door';                       
            $with_what = '(SELECT MAX(a.id) FROM tag a WHERE actief = 1), '.$_POST['activiteit_id'].', NOW(), '.$_POST['user'];
            $tag_koppelen = sqlInsert($table,$what,$with_what);
        }
    
        
        $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "Tag(s) toegevoegd bij de activiteit '$werkzaamheden'";
                    $htmlbody = "
                    <p><b>Er zijn tag(s) toegevoegd bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu zijn er door $gewijzigd_door tag(s) toegevoegd bij de activiteit '$werkzaamheden'.<br />
                    Klik op de volgende link om de tag(s) te bekijken:<br />
                    <a href=\"http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu zijn er door $gewijzigd_door tag(s) toegevoegd bij de activiteit '$werkzaamheden'.
                    Klik op de volgende link om de tag(s) te bekijken:
                    http://".$site_name."planning/".$_POST['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3";
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
                
            }
        }//afsluiting voor de waarschuwingen
    }            
    
    //echo '<p id="succes_tag"> U hebt met succes de tag '."'".$_POST['tag']."'".' toegevoegd.<br />Klik buiten dit venster om verder te gaan.</p>';
    header('location: '.$site_name.'planning/'.$_POST['refer'].'/activiteit-wijzigen/activiteit-id='.$_POST['activiteit_id'].'#tab3');
}

//de code om een tag te verwijderen en dan terug te gaan naar 'activiteit wijzigen'
if($_GET['action'] == 'delete_tag'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_POST['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];
    
    $table='planning_tag';
    $where='id='.$_GET['tag_id'];
    $delete_tag = sqlDelete($table, $where);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
    $aantal_waarschuwingen = countRows($what,$from,$where);
    if($aantal_waarschuwingen > 0){
        $result = sqlSelect($what,$from,$where);
        while($row = mysql_fetch_array($result)){
        
            $gebruikerid = $row['gebruiker_id'];
            if($gebruikerid != $login_id){
                $what="voornaam, achternaam, email";
                $from="relaties";
                $where="login_id = '$gebruikerid' AND actief=1 ";
                $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                $gewijzigd_door = $_SESSION['naam'];
                $nu = strftime("%d %B %Y");
                    
                $subject = "Tag verwijderd bij de activiteit '$werkzaamheden'";
                $htmlbody = "
                    <p><b>Er is een tag verwijderd bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu is er door $gewijzigd_door een tag verwijderd bij de activiteit '$werkzaamheden'.<br />
                    Klik op de volgende link om de andere tag(s) te bekijken:<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                $textbody = " Beste $voornaam $achternaam, 
                    Op $nu is er door $gewijzigd_door een tag verwijderd bij de activiteit '$werkzaamheden'.
                    Klik op de volgende link om de andere tag(s) te bekijken:
                    http://".$site_name."planning/".$_GET['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3";
                                
                $mail = new PHPMailer();
                $mail->SetFrom($admin_email,$admin_email_naam);
                $mail->AddReplyTo($admin_email,$admin_email_naam); 
                $mail->AddAddress($row1['email']);
                
                $mail->Subject = $subject;
                $mail->Body = $htmlbody;
                $mail->AltBody= $textbody;
                $mail->WordWrap = 50; 
                if(!$mail->Send()){
                    $error = 'mailer error'.$mail->ErrorInfo;
                }                           
            }
                
        }
    }//afsluiting voor de waarschuwingen

    $_SESSION['tag_verwijderd'] = 'u hebt met succes een tag verwijderd.'; //succesboodschap
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/activiteit-wijzigen/activiteit-id='.$_GET['activiteit_id'].'#tab3');
}

//de code om een bestand te verwijderen en dan terug te gaan naar 'activiteit wijzigen'
if($_GET['action'] == 'delete_file'){
    //haal de werkzaamheden op die in de mail gebruikt moeten worden
    $what = 'werkzaamheden'; $from = 'planning_activiteit';
    $where= 'id ='.$_POST['activiteit_id'];
    $info = mysql_fetch_assoc(sqlSelect($what, $from, $where));   
    $werkzaamheden = $info['werkzaamheden'];
    
    $what='bestand'; $from='planning_bestand'; $where='id='.$_GET['bestand_id'];
    $bestand = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $bestand = $bestand['bestand'];
    
    
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/files/planning_documenten/".$_GET['activiteit_id']."/$bestand")){
        unlink($_SERVER['DOCUMENT_ROOT']."/files/planning_documenten/".$_GET['activiteit_id']."/$bestand");
    }
    
    $_SESSION['tag_verwijderd'] = 'u hebt met succes het bestand verwijderd verwijderd.';//succesboodschap
    $table="planning_bestand";
    $where="id=".$_GET['bestand_id'];
    $delete_file = sqlDelete($table, $where);
    
    $what= "gebruiker_id";        $from = "planning_waarschuw_mij";        $where = "planning_activiteit_id =".$_POST['activiteit_id']."";
        $aantal_waarschuwingen = countRows($what,$from,$where);
        if($aantal_waarschuwingen > 0){
            $result = sqlSelect($what,$from,$where);
            while($row = mysql_fetch_array($result)){
                $gebruikerid = $row['gebruiker_id'];
                if($gebruikerid != $login_id){
                    $what="voornaam, achternaam, email";
                    $from="relaties";
                    $where="login_id = '$gebruikerid' AND actief=1 ";
                    $result1 = sqlSelect($what, $from, $where); $row1 = mysql_fetch_array($result1);
                    
                    $voornaam = $row1['voornaam']; $achternaam = $row1['achternaam'];
                    $gewijzigd_door = $_SESSION['naam'];
                    $nu = strftime("%d %B %Y");
                    
                    $subject = "Bestand verwijderd bij de activiteit '$werkzaamheden'";
                    $htmlbody = "
                    <p><b>Er is een bestand verwijderd bij de activiteit '$werkzaamheden'</b></p>
                    <p>Beste $voornaam $achternaam,<br />
                    Op $nu is er door $gewijzigd_door een bestand verwijderd bij de activiteit '$werkzaamheden'.<br />
                    Klik op de volgende link om de eventuele andere bestanden te bekijken:<br />
                    <a href=\"http://".$site_name."planning/".$_GET['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3\" title=\"ga naar de activiteit\">Bekijk de wijzigingen</a></p>";
                    
                    $textbody = " Beste $voornaam $achternaam, 
                    Op $nu is er door $gewijzigd_door een bestand verwijderd bij de activiteit '$werkzaamheden'.
                    Klik op de volgende link om de eventuele andere bestanden te bekijken:
                    http://".$site_name."planning/".$_GET['refer']."/activiteit-wijzigen/activiteit-id=".$_POST['activiteit_id']."#tab3";
                                
                    $mail = new PHPMailer();
                    $mail->SetFrom($admin_email,$admin_email_naam);
                    $mail->AddReplyTo($admin_email,$admin_email_naam); 
                    $mail->AddAddress($row1['email']);
                
                    $mail->Subject = $subject;
                    $mail->Body = $htmlbody;
                    $mail->AltBody= $textbody;
                    $mail->WordWrap = 50; 
                    if(!$mail->Send()){
                        $error = 'mailer error'.$mail->ErrorInfo;
                    }                           
                }
                
            }
        }//afsluiting voor de waarschuwingen
    
    header('location: '.$site_name.'planning/'.$_GET['refer'].'/activiteit-wijzigen/activiteit-id='.$_GET['activiteit_id']).'#tab2';
    
}
?>
