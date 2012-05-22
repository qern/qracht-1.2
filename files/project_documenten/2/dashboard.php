<?php 
if($_POST['meegepraat'] != null){
    $_SESSION['meegepraat'] = $_POST['meegepraat'];    
    if($_SERVER['REQUEST_URI'] == '/meepraten'){$meegepraat_vanuit = '/';}
    else{ $meegepraat_vanuit = $_SERVER['REQUEST_URI'];}
    $table='cms_praat_mee'; $what = 'naam, bericht, meegepraat_op, meegepraat_vanuit';
    $with_what = "'".$_POST['naam']."', '".$_POST['meegepraat']."', NOW(), '".$meegepraat_vanuit."'";
    $insert_praat_mee = sqlInsert($table, $what, $with_what);
    
     if( stripos($_SERVER['REQUEST_URI'], 'index.php?') !== false){
        $url = $_SERVER['REQUEST_URI'].'&meepraten_check=1'; 
     }
     elseif( stripos($_SERVER['REQUEST_URI'], 'index.php') !== false){
        $url = $_SERVER['REQUEST_URI'].'?meepraten_check=1'; 
     }elseif($_SERVER['REQUEST_URI'] ==  '/'){
        $url = '/meepraten'; 
     }else{
        $url = $_SERVER['REQUEST_URI'].'/meepraten';
     }

     header('location: http://'.$website_naam.$url);
}                                      
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css; charset=utf-8" />
<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />-->
<title><?php echo $titel; ?></title>
<link href="/templates/<?php echo $template; ?>/css/style_website.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]><link href="/templates/<?php echo $template; ?>/css/style_website_IE7.css" rel="stylesheet" type="text/css"><![endif]-->
<?php 
if($_GET['meepraten_check'] == 1){
    echo '<link href="/templates/'.$template.'/css/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" />';
}
if($_GET['function'] == 'foto' || $_GET['function'] == 'video'){
    echo '<link href="/templates/'.$template.'/css/prettyPhoto.css" rel="stylesheet" type="text/css" />';
}
?>
<script src="/templates/<?php echo $template; ?>/js/jquery-1.5.2.js" type="text/javascript"></script>
<script src="/templates/<?php echo $template; ?>/functions/twitter/js/jquery.tweet.js" type="text/javascript"></script>
<?php echo $script;
if($voorpagina == 1){
    echo '<script type="text/javascript" src="/templates/'.$template.'/js/jquery.sudoSlider.js"></script>';
}?>
</head>

<body>
<div id="wrapper_01_header">
    <div id="wrapper_02_header">
        <div id="header">
            <div id="wrapper_logo">
                <div id="logo"><a class="logo" href="http://www.abcgemeentezuidplas.nl">&nbsp;</a></div>
            </div>
            <div id="header_right">
    <div id="dailybread_title"><a href="http://www.dagelijkswoord.nl" target="_blank">Dagelijks Woord</a></div>
                <div id="dailybread">
                    <script src='http://feed.dagelijkswoord.nl/js?&t=t&v=0'>-- <a href='http://www.dagelijkswoord.nl/'>DagelijksWoord.nl</a></script>
                </div>
            </div>
            <div id="navi">
                <div class="navi_item"><a href="<?php echo paginaUrl(2) ?>">ABC gemeente Zuidplas</a></div>
                <div class="navi_item"><a href="/nieuws">Actueel</a></div>
                <div class="navi_item"><a href="<?php echo paginaUrl(11) ?>">Teams</a></div>
				<?php // <div class="navi_item"><a href=" paginaUrl(3) ">Weekberichten</a></div> ?>
	            <div class="navi_item"><a href="/agenda">Agenda</a></div>
                <div class="navi_item"><a href="/downloads">Media</a></div>
                <div class="navi_item"><a href="<?php echo paginaUrl(6) ?>">Contact</a></div>
            </div>
        </div>
    </div>
</div>
<div id="wrapper_01_whitebar">
    <div id="wrapper_02_whitebar">
        <div id="whitebar">
<?php
/* 
    if($voorpagina == 1){
    
*/  
    if($voorpagina == 1){
        $what = 'id pagina_id, titel pagina_titel, beschrijving pagina_beschrijving, afbeelding pagina_afbeelding'; $from='cms_pagina';
    $where='teaser = 1 AND actief = 1';
    $result_pagina = sqlSelect($what, $from, $where);
    while($row = mysql_fetch_array($result_pagina)){
        $visuals[] = '
            <li class="slide teaser">
                <div class="teaser_container other_visual">
                        <div class="teaser_corner_LT">&nbsp;</div>
                        <div class="teaser_corner_LB">&nbsp;</div>
                        <div class="teaser_corner_RT">&nbsp;</div>
                        <div class="teaser_corner_RB">&nbsp;</div>
                        <a href="'.paginaUrl($row['pagina_id']).'">
                            <img src="/templates/'.$template.'/lib/slir/w730-h230-c730.230/templates/'.$template.'/functions/pagina/images/'.$row['pagina_afbeelding'].'" border="0" />
                        </a>
                </div>
                <div class="welcome other_visual">
                    <a href="'.paginaUrl($row['pagina_id']).'"><h2>'.$row['pagina_titel'].'</h2></a>
                    <p>'.$row['pagina_beschrijving'].'</p>
                </div>
            </li>';
    }
    
    $what = 'id nieuws_id, titel nieuws_titel, beschrijving nieuws_beschrijving, afbeelding nieuws_afbeelding'; $from='cms_nieuws_bericht';
    $where="publicatiedatum <= CURDATE() AND (archiveerdatum >= CURDATE() OR archiveerdatum = '0000-00-00')
            AND teaser = 1 AND actief = 1";
    $result_project = sqlSelect($what, $from, $where);
    while($row = mysql_fetch_array($result_project)){ 
        $visuals[] = '
            <li class="slide teaser">
                <div class="teaser_container other_visual">
                    <div class="teaser_corner_LT">&nbsp;</div>
                    <div class="teaser_corner_LB">&nbsp;</div>
                    <div class="teaser_corner_RT">&nbsp;</div>
                    <div class="teaser_corner_RB">&nbsp;</div>
                    <a href="'.nieuwsUrl($row['nieuws_id']).'">
                        <img src="/templates/'.$template.'/lib/slir/w730-h230-c730.230/templates/'.$template.'/functions/nieuws/images/'.$row['nieuws_afbeelding'].'" border="0" />
                    </a>
                </div>
                <div class="welcome other_visual">
                    <a href="'.nieuwsUrl($row['nieuws_id']).'"><h2>'.$row['nieuws_titel'].'</h2></a>
                    <p>'.$row['nieuws_beschrijving'].'</p>
                </div>
            </li>';
    }
    
    shuffle($visuals);
?>

 <div id="visual_content">
    <div id="visual_content_cover_top"></div>
    <div id="visual_content_cover_right"></div>
    <div id="visual_content_cover_bottom"></div>
    <div id="visual_content_cover_left"></div>
    <div id="visuals">
        <ul>
        <!--
        <li class="slide teaser">
            <div class="teaser_container">
                <div class="teaser_corner_LT">&nbsp;</div>
                <div class="teaser_corner_LB">&nbsp;</div>
                <div class="teaser_corner_RT">&nbsp;</div>
                <div class="teaser_corner_RB">&nbsp;</div>
                <a href="#">
                    <img src="/templates/<?php echo $template ?>/lib/slir/w730-h230-c730.230/templates/<?php echo $template ?>/images/img_teaser_dummy.gif" border="0" />
                </a> 
            </div>
            <div class="welcome">
                <h2>Van harte welkom!</h2>
                <p>ABC Gemeente Zuidplas heet u van harte welkom op de nieuwe locatie in Waddinxveen.</p>
                <p>Elke zondag verzorgen wij om 10.00 uur een dienst waarvoor u van harte bent uitgenodigd. Voelt u zich vrij en maak gerust eens kennis!</p>
                <p><strong>Arie Davidse voorganger</strong></p>
            </div>
        </li>
        -->
<?php
        foreach($visuals as $visual){
            echo $visual;
        } 
?>

        </ul>
    </div>
</div>               
 <?php  }elseif($pagina_id == 2 || $pagina_id >= 7 && $pagina_id <= 10){
        echo '  <div id="subnavi">
                    <div class="subnavi_item"><a href="'. paginaUrl(7).'">Algemeen</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(8).'">Activiteiten</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(10).'">Bestuur</a></div>
                </div>';
        }elseif($_GET['function'] == 'nieuws'){
        echo '  <div id="subnavi">
                    <div class="subnavi_item"><a href="/nieuws">Nieuws</a></div>
                </div>';

		}elseif($_GET['function'] == 'agenda'){
        echo '  <div id="subnavi">
                    <div class="subnavi_item"><a href="/agenda">Agenda</a></div>
                </div>';
        }elseif($pagina_id == 11 || $pagina_id >= 15 && $pagina_id <= 22){
        echo '  <div id="subnavi">
                    <div class="subnavi_item"><a href="'. paginaUrl(15).'">Eredienst</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(16).'">King Kids</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(17).'">Kringen</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(18).'">Ondersteuning</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(19).'">Onderwijs</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(20).'">Facilitair</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(21).'">Pastoraat</a></div>
                    <div class="subnavi_item"><a href="'. paginaUrl(22).'">Zending &#38; Evangelisatie</a></div>
                </div>';
        }elseif($pagina_id == 5 || $_GET['function'] == 'downloads' || $_GET['function'] == 'foto' || $_GET['function'] == 'video'){
        echo '  <div id="subnavi">
                    <div class="subnavi_item"><a href="/downloads">Preken</a></div>
                    <div class="subnavi_item"><a href="/foto">Foto\'s</a></div>
                    <div class="subnavi_item"><a href="/video">Video\'s</a></div>
                </div>';    
    }
?>

        </div>
    </div>
</div>

<div id="wrapper_content_and_footer">
    <div id="wrapper_01_content">    
        <div id="wrapper_02_content">
                <div id="content">
<?php
if($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/meepraten' || $_SERVER['REQUEST_URI'] == '/templates/ABCgemeentezuidplas/index.php'){
    echo '<div id="home">';
}else{
    echo '                
    <div id="follow_up">
        <div class="column_left">';
}
                         //bepaal wat er op de pagina getoond moet worden.
                        echo $content;
if($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/meepraten' || $_SERVER['REQUEST_URI'] == '/templates/ABCgemeentezuidplas/index.php'){
}else{
    echo '</div>';
}
                    ?>
<?php
if($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/meepraten' || $_SERVER['REQUEST_URI'] == '/templates/ABCgemeentezuidplas/index.php'){
}else{ echo '<div class="column_right">';} 
if($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/meepraten' || stripos($_SERVER['REQUEST_URI'], 'index.php?') === false){$twitter_url = 'http://'.$website_naam.$_SERVER['REQUEST_URI'];
}else{ $twitter_url = $_SERVER['PHP_SELF'];}   
 
?>
                        <div id="twitter">
                        	<div id="wrapper_twitter_top">
	                            <div class="header">Praat mee!</div>
	                            <div id="praat_mee_invoer">
	                                <form id="meepraten" action="<?php  echo $twitter_url ?>" method="post">
	                                <table cellpadding="0" cellspacing="0" border="0">
	                                <tr>
	                                    <td height="48" valign="top">
	                                        <label class="meepraten_label" for="meegepraat_text">Laat hier jouw bericht achter en klik<br />
	                                        	op de link 'plaatsen' hieronder ...</label>
	                                        <textarea name="meegepraat" id="meegepraat_text" cols="25" rows="2" onKeyDown="textCounter(this.form.meegepraat,this.form.remmeegepraat,123);" onKeyUp="textCounter(this.form.meegepraat,this.form.remmeegepraat,123);"></textarea>
	                                    </td>
	                                </tr>
	                                <tr>
	                                    <td>
	                                        <label class="meepraten_label" for="meegepraat_naam">Naam</label>
	                                        <input type="text" name="naam" id="meegepraat_naam" onKeyDown="textCounter(this.form.naam,this.form.remname,15);" onKeyUp="textCounter(this.form.naam,this.form.remname,15);" />
	                                    </td>
	                                </tr>                                   
	                                <tr>
	                                    <td>
	                                        <input type="hidden" name="remname" value="15" />
	                                        <input type="hidden" name="remmeegepraat" value="125" />
	                                        <input type="submit" value="plaatsen" id="button_praat_mee_invoer" />
	                                    </td>
	                                </tr>
	                                </table>
	                                </form>
	                                <?php 
	                                //als de sessie voor meepraten is gezet, laad dan de fancybox.
	                                if($_GET['meepraten_check'] == 1){
	                                    echo '
	                                    <a href="/templates/'.$template.'/functions/twitter/includes/meepraten.php" class="iframe" id="meepraten_check" style="display:none;">
	                                        Check of er wel meegepraat mag worden.
	                                    </a>';
	                                }
	                                ?>
	                            </div>
	                        </div>
	                        <div id="wrapper_twitter_bottom">
                            	<div id="slider"></div>
                           	</div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
<div id="wrapper_01_footer">
    <div id="wrapper_02_footer">
        <div id="footer">
            <div id="footer_header">
                <div class="footer_header_item">Bezoekadres</div>
                <div class="footer_header_item">Postadres</div>
                <div class="footer_header_item">Links</div>
                <div class="footer_header_item">Contact</div>
            </div>
            <div id="footer_content">
            	<div class="footer_content_item">
<?php 
    $what="id, organisatie, org_telefoon, org_email, bezoek_adres, bezoek_postcode, bezoek_plaats, post_adres, post_postcode, post_plaats"; $from="cms_instellingen_contact"; $where="actief = 1";
    $contact = mysql_fetch_assoc(sqlSelect($what, $from, $where));

if($contact['bezoek_adres'] != null){
echo                    $contact['organisatie'].'<br />'.
                        $contact['bezoek_adres'].'<br />'.
                        $contact['bezoek_postcode'].' '.$contact['bezoek_plaats'].'<br />
                        Aanvang: zondag 16.oo uur';
 }?>
<br /><br />
 						<span style="font-size: 14px; font-weight: bold;">Giften</span><br />
 						Bankrelatie:  Rabobank Zevenhuizen<br />
 						Rek. nr. 1557.31.572<br />
 						T.n.v. ABC gemeente Zuidplas
                </div>
                <div class="footer_content_item">
<?php 
if($contact['post_adres'] != null){
echo                    $contact['organisatie'].'<br />'.
                        $contact['post_adres'].'<br />'.
                        $contact['post_postcode'].' '.$contact['post_plaats'].'<br />
                        E <a href="mailto:'.$contact['org_email'].'">'.$contact['org_email'].'</a><br />
                        T '.$contact['org_telefoon'];
}
?>
                </div>
                <div class="footer_content_item">
                    <a href="http://www.abcgemeenten.nl" target="_blank">www.ABCGEMEENTEN.nl</a><br />
                    <a href="http://www.parousia.nl" target="_blank">www.PAROUSIA.nl</a><br />
                    <a href="http://www.ariedavidse.nl" target="_blank">www.ARIEDAVIDSE.nl</a><br />
                    <br />
                  	<a class="qern" target="_blank" href="http://www.qern.nl/"><img border="0" title="Design &amp; Development by qern internet professionals - Powered by qrachtCMS" alt="Design &amp; Development by qern internet professionals - Powered by qrachtCMS" src="/templates/ABCgemeentezuidplas/images/img_logo_qern.png"></a>
                </div>
                <div id="footer_contact">
                    <form id="contactformulier" action="/templates/<?php echo $template; ?>/functions/contact/includes/contact_check.php" method="post">
                        <input type="hidden" name="location" value="bottom" />
                        <input type="hidden" name="refer" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
                        <div id="contact_form_name">
                            <label class="label lb_name" for="naam">Uw naam</label>
                            <input type="text" maxlength="80" tabindex="1" name="naam" id="naam" class="textfield tf_name"></div>
                        <div id="contact_form_email">
                            <label class="label lb_email" for="email">Uw e-mailadres</label>
                            <input type="text" maxlength="80" tabindex="2" name="email" id="email" class="textfield tf_email"></div>
                        <div id="contact_form_remark">
                            <label class="label lb_comments" for="comments">Uw vraag of opmerking</label>
                            <textarea maxlength="1000" tabindex="3" name="comments" rows="3" id="comments" class="textarea ta_remark"></textarea></div>
                        <div id="contact_form_send"><input type="submit" value="Verzenden" tabindex="4" class="btn_footer_send"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="/templates/<?php echo $template; ?>/functions/twitter/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
<script src="/templates/<?php echo $template; ?>/js/jquery.validate.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function() { $( '.label').inFieldLabels(); });
    $(function(){ $("#contactformulier").validate({
                rules: { naam:"required", email: {email: true, required: true}, comments:"required"  },
                messages: {  naam: "",email:{ email: "", required:"" },  comments:""} 
                }); 
    });
</script>
<?php 
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/templates/'.$template.'/functions/'.$_GET['function'].'/js/'.$_GET['function'].'.js')){
    echo '<script src="/templates/'.$template.'/functions/'.$_GET['function'].'/js/'.$_GET['function'].'.js" type="text/javascript"></script>';
}
if($_GET['function'] == 'foto' || $_GET['function'] == 'video'){
    echo '
        <script type="text/javascript" src="/templates/'.$template.'/js/jquery.prettyPhoto.js"></script>';
}
if($voorpagina == 1){
    echo '
    
    <script type="text/javascript">
    function slider(){    
        $("#visuals").sudoSlider({
            fade: false,
            vertical:false,
            auto:true,
            controlsShow:false,
            pause:10000,
            speed:2000
        });
        $(".other_visual").css("display","block");
    };
    </script>';
}
//als de sessie voor meepraten is gezet, laad dan de fancybox javascript
if($_GET['meepraten_check'] == 1){
echo '<script src="/templates/'.$template.'/js/jquery.fancybox-1.3.4.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){$("#meepraten_check").fancybox({';
echo"
    'overlayShow':true,
    'overlayColor':'#000',
    'overlayOpacity':0.7,
    'hideOnContentClick':false,
    'transitionIn':'elastic',
    'transitionOut':'elastic',
    'speedIn':600,
    'speedOut':200,
    'width':500,
    'height':500,
    'onClosed':function(){
        parent.location.reload(true);
    }
    });
});
</script>";
}
?>
<script src="/templates/<?php echo $template; ?>/js/activate.js" type="text/javascript"></script>
<?php 
/* deze functie genereert een lighbox wanneer er een meepraat-tweet uitgaat. */

//als de sessie voor meepraten is gezet, laad dan de fancybox op de link.
if($_GET['meepraten_check'] == 1){
    echo '
    <script type="text/javascript">
    function LaunchFancyBox() { $("#meepraten_check").fancybox().trigger('."'click'".'); };
        $(document).ready(LaunchFancyBox);
    </script>';
}
if($_GET['function'] == 'foto' || $_GET['function'] == 'video'){?>
    <script type="text/javascript" charset="utf-8">
          $(document).ready(function(){
            $("a[rel^='prettyPhoto']").prettyPhoto();
          });
        </script>
<?php }
?>
<script type="text/javascript">
//call after page loaded
window.onload=slider ;  
</script>
</body>
</html>