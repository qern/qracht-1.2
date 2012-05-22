<?php
if(isset($login_id) && $login_id > 0){
    if($functie_get == 'account' || $functie_get == 'nieuws'){
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Pragma: no-cache"); //no cache
    }elseif($functie_get == 'home'){
        header("Cache-Control: max-age = 600, must-revalidate"); // HTTP/1.1
        // calc an offset of 10 minutes
        $offset = 600;
        // calc the string in GMT not localtime and add the offset
        $expire = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        //output the HTTP header
        header($expire);
    }else{
        header("Cache-Control: max-age = 3600, must-revalidate"); // HTTP/1.1
        // calc an offset of 1 hour
        $offset = 3600;
        // calc the string in GMT not localtime and add the offset
        $expire = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        //output the HTTP header
        header($expire);
    }
require('/includes/menu.php'); ?>

<!DOCTYPE html>
<head>
<title><?php 
if ($titel != null){echo $titel;}
else{echo 'Welkom in qracht';}

?></title>
<meta charset="utf-8">

<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Open+Sans|Muli' />
<link rel="stylesheet" type="text/css" href="<?php echo $etc_root ?>css/standaard_css/main.css" />
<!--[if IE]> 	<link rel="stylesheet" type="text/css" href="<?php echo $etc_root ?>css/standaard_css/main_IE.css" /> 	<![endif]-->
<!--[if IE 7]>	<link rel="stylesheet" type="text/css" href="<?php echo $etc_root ?>css/standaard_css/main_IE7.css" />	<![endif]-->
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.19/themes/ui-lightness/jquery-ui.css" />
<?php 
    if(file_exists('functions/'.$functie_get.'/css/'.$functie_get.'.css')){
        echo'<link rel="stylesheet" type="text/css" href="'.$etc_root.'functions/'.$functie_get.'/css/'.$functie_get.'.css" />';
    }elseif(file_exists('functions/dashboard/css/dashboard.css')){
        echo'<link rel="stylesheet" type="text/css" href="'.$etc_root.'functions/dashboard/css/dashboard.css" />';
    }
	
	if($functie_get == 'crm' && ($_GET['page'] == 'relatie-toevoegen' || $_GET['page'] == 'organisatie-toevoegen')){
		echo'<link rel="stylesheet" type="text/css" href="'.$etc_root.'functions/'.$functie_get.'/css/rel_org_wijzigen.css" />';
	}
    
    if($functie_get == 'planning' && ($_GET['page'] == 'rapportage')){
        echo'<link rel="stylesheet" type="text/css" href="'.$etc_root.'functions/'.$functie_get.'/css/rapportage.css" />';
    }
?>
<link rel="stylesheet" type="text/css" href="<?php echo $etc_root ?>css/standaard_css/prettyPhoto.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $etc_root ?>css/standaard_css/jquery.fancybox-1.3.4.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $etc_root ?>functions/account/css/fileuploader.css" />
<link rel="shortcut icon" href="<?php echo $etc_root ?>favicon.ico" type="image/x-icon" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<?php if($functie_get == 'account' && $_GET['actie'] == 'foto'){ ?> <script src="<?php echo $etc_root ?>functions/account/js/fileuploader.js"></script> <?php } ?>
<script src="<?php echo $etc_root ?>js/main.js"></script>
</head>
<body id="body_<?php echo $functie_get; ?>">
<div id="wrapper_section_top_01">
    <div id="wrapper_section_top_02">
        <div id="wrapper_section_top_03">
            <div id="section_top" class="<?php echo $met_submenu; ?>">
            
                <div id="datum"><b><?php echo translateDate(time(), 'weekdag dag maand'); ?></b></div>
                <div id="logo_abc"><a href="#" target="_blank"><img src="<?php echo $etc_root ?>images/logo_qern.gif" border="0" alt="Uw organisatie" title="Uw organisatie" /></a></div>
                <div id="logo_qracht"><a href="http://www.qern.nl" target="_blank"><img src="<?php echo $etc_root ?>images/qracht_logo_klein.gif" border="0" alt="www.qern.nl" title="www.qern.nl" /></a></div> 
				<?php    if(isset($naam)){?>
					
				    <div id="profiel_container">
				        <span id="profiel_containernaam"><?php echo $naam; ?></span>
				        <?php echo $_SESSION['profielfoto']; ?>
				    </div>
				    <div id="profiel_instellingen" style="display:none;">
				        <ul id="profiel_instellingen_menu">				            
				            <li class="profiel_instelling"><a href="profiel" style="display:none; visibility: hidden"></a> Profiel bekijken</a></li>
				            <li class="profiel_instelling"><a href="account" style="display:none; visibility: hidden"></a> Accountinstellingen</li>
				            <?php  if($_SESSION['admin'] == 1) {?><li class="profiel_instelling"> <a href="admin" style="display:none; visibility: hidden"></a> Intranetinstellingen </li> <?php } ?>
				            <li class="profiel_instelling"><a href="uitloggen" style="display:none; visibility: hidden"></a> Uitloggen</li>
				        </ul>
				    </div>
				    
				    <?php
							$sociale_pagina = array ('home', 'publicaties', 'profiel', 'bestanden', 'account');
							$zakelijke_pagina = array ('dashboard', 'crm', 'urentool', 'planning', 'admin');
							$servicedesk_pagina = array ('servicedesk', 'overzicht', 'inzage', 'invoer');
							$cms_pagina = array ('cms', 'checklist');
							$wiki_pagina = array ('wiki', 'kennis', 'kennis-toevoegen');
							if(in_array($functie_get, $sociale_pagina)){
								$huidig_product = 'Social'; $huidig_product_array = $sociale_pagina;
								$nummer2 = 'Werkplek';$nummer2Url = 'dashboard';
							 	$nummer3 = 'Servicedesk'; $nummer3Url = 'servicedesk';
							 	$nummer4 = 'CMS'; $nummer4Url = 'cms';
							 	$nummer5 = 'Wiki'; $nummer5Url = 'wiki';
							}
							elseif(in_array($functie_get, $zakelijke_pagina)){
								$huidig_product = 'Werkplek'; $huidig_product_array = $zakelijke_pagina;
								$nummer2 = 'Social'; $nummer2Url = 'home'; 
								$nummer3 = 'Servicedesk';  $nummer3Url = 'servicedesk';
								$nummer4 = 'CMS';  $nummer4Url = 'cms';
								$nummer5 = 'Wiki';  $nummer5Url = 'wiki';
							}
							elseif(in_array($functie_get, $servicedesk_pagina)){
								$huidig_product = 'Servicedesk'; $huidig_product_array = $servicedesk_pagina;
								$nummer2 = 'Social'; $nummer2Url = 'home'; 
								$nummer3 = 'Werkplek';  $nummer3Url = 'dashboard';
								$nummer4 = 'CMS';  $nummer4Url = 'cms';
								$nummer5 = 'Wiki';  $nummer5Url = 'wiki';
							}
							elseif(in_array($functie_get, $cms_pagina)){
								$huidig_product = 'CMS'; $huidig_product_array = $cms_pagina;
								$nummer2 = 'Social'; $nummer2Url = 'home'; 
								$nummer3 = 'Werkplek';  $nummer3Url = 'dashboard';
								$nummer4 = 'Servicedesk';  $nummer4Url = 'servicedesk';
								$nummer5 = 'Wiki';  $nummer5Url = 'wiki';
							}
							elseif(in_array($functie_get, $wiki_pagina)){
								$huidig_product = 'Wiki'; $huidig_product_array = $wiki_pagina;
								$nummer2 = 'Social'; $nummer2Url = 'home'; 
								$nummer3 = 'Werkplek';  $nummer3Url = 'dashboard';
								$nummer4 = 'Servicedesk';  $nummer4Url = 'dashboard';
								$nummer5 = 'CMS';  $nummer5Url = 'cms';
							}
					?> 
					<div id="product_container">
				       <a href="/<?php echo $huidig_product_array[0]; ?>/" title="<?php echo $huidig_product; ?>"><img src="<?php echo $etc_root; ?>images/<?php echo strtolower($huidig_product); ?>.png" class="product_image active_product" /></a>
				        <a href="/<?php echo $nummer2Url;?>/" title="<?php echo $nummer2; ?>"><img src="<?php echo $etc_root; ?>images/<?php echo strtolower($nummer2); ?>.png" class="product_image" /></a>
				     </div>
				<?php } ?>      
								 
				 <div id="icon">
					<div id="icon_home" class="hidden">&nbsp;</div>
					<div id="icon_publicaties" class="hidden">&nbsp;</div>
					<div id="icon_bestanden" class="hidden">&nbsp;</div>
					<div id="icon_profiel" class="hidden">&nbsp;</div>
					<div id="icon_urentool" class="hidden">&nbsp;</div>
					<div id="icon_crm" class="hidden">&nbsp;</div>
					<div id="icon_planning" class="hidden">&nbsp;</div>
					<div id="icon_admin" class="hidden">&nbsp;</div>
				</div>
				<div id="wrapper_navi_main">
					<table width="100%" cellpadding="0" cellspacing="0" border="0">
		            <tr>
		            	<td width="100%" align="center">
		            		
		            		<table cellpadding="0" cellspacing="0" border="0">
				            <tr>
				            	<td align="center">
									<?php 
									//het sociale menu
									if(in_array($functie_get, $sociale_pagina)){ ?>
									<a class="navi_main_item home" href="<?php echo $etc_root ?>home">Home</a>
									<?php  if($_SESSION['admin'] == 1) {?> <a class="navi_main_item publicaties" href="<?php echo $etc_root ?>publicaties">publicaties</a>  <?php } ?>
									<a class="navi_main_item bestanden" href="<?php echo $etc_root ?>bestanden">Bestanden</a>
									<a class="navi_main_item profiel" href="<?php echo $etc_root ?>profiel">Profiel</a>
									<?php } 
									
									//het zakelijke menu
									elseif(in_array($functie_get, $zakelijke_pagina)){ ?>
									<a class="navi_main_item item_home" href="<?php echo $etc_root ?>dashboard">Dashboard</a>
									<a class="navi_main_item item_crm" href="<?php echo $etc_root ?>crm">CRM</a>
									<a class="navi_main_item item_urentool" href="<?php echo $etc_root ?>urentool">Urentool</a>
									<a class="navi_main_item item_planning" href="<?php echo $etc_root ?>planning">Planning</a>
									<?php }
									
									//het menu voor de servicedesk
									elseif(in_array($functie_get, $servicedesk_pagina)){ ?>
									<a class="navi_main_item item_home" href="<?php echo $etc_root ?>servicedesk">Landingspagina</a>
									<a class="navi_main_item item_crm" href="<?php echo $etc_root ?>overzicht">Overzicht meldingen</a>
									<?php } ?>
								</td>
						     </tr>
						     </table>
						     
			            </td>
			        </tr>
		            </table>
				</div>
				
				<div id="wrapper_navi_submenu">
					<div id="navi_submenu">
						<?php echo $submenu; ?>
	                 </div>
				</div>
				 
            </div>
        </div>
    </div>
    
    <!--<div id="section_top_bkg"></div>-->
</div>

<!-- INFO -->
<div id="wrapper_info_01">
	<div id="wrapper_info_02">
		<div id="info">
			<div id="info_title"><h1><?php if($info_titel){ echo $info_titel; } else{ echo $functie_get; } ?></h1></div>
			<div id="info_subtitle"><?php echo $info_subtitel; ?></div>
		</div>
	</div>
</div>

<div id="wrapper_section_bottom_01">
    <div id="section_bottom_bkg"></div>

    <div id="wrapper_section_bottom_02">
        <div id="wrapper_section_bottom_03">
            <div id="section_bottom">Bezoekadres: Timmerfabriekstraat 16 | 2861 GV Bergambacht | T (085) 8785171 | <a href="mailto:info@qern.nl">info@qern.nl</a> | <a href="http://www.qern.nl" target="_blank">www.qern.nl</a> | &#169; <?php echo strftime('%Y'); ?> qern internet professionals</a></div>
        </div>
    </div>
</div>

<div id="wrapper_content_01">
    <div id="wrapper_content_02">
        <div id="content">
        
			<?php
			    if(isset($pagina)){ include $pagina; }
			    else{ echo 'hier komt de content'; }
			?>
              
        </div>
    </div>
</div>
<div id="dialog"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<?php 
if($functie_get == 'account' || $functie_get == 'home' || $functie_get == 'publicaties'){?>
	<script src="<?php echo $etc_root ?>js/standaard_js/jquery.fancybox-1.3.4.js"></script>	 
<?php }
if($functie_get == 'account' || $functie_get == 'publicaties' || $_GET['function'] == 'inzage' ){
    if($functie_get == 'publicaties' && $_GET['page'] != null){?>
    <script src="<?php echo $etc_root ?>lib/ckeip/ckeditor/ckeditor.js"></script>
    <script src="<?php echo $etc_root ?>lib/ckeip/ckeditor/adapters/jquery.js"></script>
    <script>
        function laadEditor(){
            $('#inhoud').ckeditor();
        };
        window.onload = laadEditor;
    </script>
<?php }
    if($geen_uploader == null){ ?>
    <script src="<?php echo $etc_root ?>js/standaard_js/fileuploader.js"></script>
<?php }
}
require ($siteroot."/js/js-includes.php"); 
?>

</body>
</html>
<?php }else{header('location: '.$site_name.'inloggen.php');} ?>