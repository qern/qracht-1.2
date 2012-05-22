<?php 
session_start(); setcookie('qern_inlog', false, time()-2678400, '/');
require('check_configuration.php');
$what = 'portal_titel AS titel'; $from = 'portal_instellingen'; $where= 'actief = 1';
	$portal = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>
<!DOCTYPE html>
<html>
<head>
	<title>Inloggen bij <?php echo $portal['titel']; ?></title>
	<meta name="robots" content="noindex">
	
	<link href="http://qern.nl/login/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>

	<div class="login_logo"><img src="/images/logo_qern.gif" alt="Inloggen bij <?php echo $portal['titel']; ?>"/></div>
	<div id="formWrapper">	
		<div id="formCasing">
			<div id="login_error"><?php if($_SESSION['error']){echo $_SESSION['error']; unset($_SESSION['error']);} ?></div>
			<div id="loginForm" <?php if($_GET['lock'] != null){echo 'style="display:none;"';} ?>>
				<form action="index.php" method="post" name="loginForm">
				  	<dl>
						<dd>
							<label for="gebruikersnaam">Gebruikersnaam</label>
							<input type="text" name="gebruikersnaam" id="gebruikersnaam" class="input" tabindex="1" autocomplete="off" />
						</dd>
						<dd>
							<label for="wachtwoord">Wachtwoord</label>
							<input type="password" name="wachtwoord" id="wachtwoord" tabindex="2" class="input" autocomplete="off" />
							<span onclick="forgotPass();" id="wachtwoord_vergeten">&nbsp;&nbsp;Wachtwoord vergeten ?</span>
						</dd>
						<dd><input type="checkbox" name="cookie" id="remember_me" tabindex="3" value="true" class="checkbox"><label for="remember_me" id="rememberme_label">&nbsp;&nbsp;Onthoud mijn gegevens</label></dd>
						<dd><input type="submit" id="btnLogin" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" value="inloggen"></dd>
					</dl>
			  	</form>
			</div>
			<div id="forgotpassForm" style="display:none;">
				
			  	<dl>
					<dd>
						<p>Voer uw e-mailadres in, wij sturen u een e-mail met daarin instructies om uw wachtwoord te herstellen.</p>
					</dd>
					<dd>
						<label for="pass_email">E-mailadres</label>
						<input type="email" name="pass_email" id="pass_email" tabindex="1" class="input" />
					</dd>
					<dd><button id="btnforgotPass" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Wachtwoord herstellen </button></dd>
				</dl>
				
			</div>
			<?php if($_GET['lock'] != null){?>
			<div id="resetPassForm">
			  	<dl>
			  		<dd><p>Vul hier uw gebruikersnaam en e-mailadres in ter verificatie</p></dd>
					<dd>
						<label for="checkgebruikersnaam">Gebruikersnaam</label>
						<input type="text" name="checkgebruikersnaam" id="checkgebruikersnaam" value="" tabindex="1" class="input" />
					</dd>
					<dd>
						<label for="checkemail">E-mailadres</label>
						<input type="email" name="checkemail" id="checkemail" value="" tabindex="2" class="input" />
					</dd>
					<dd><button id="btnCheckCred" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Wachtwoord herstellen </button></dd>
				</dl>
			</div>	
			<?php } ?>
		</div>
		
		<div id="formFooter"></div>
	
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="/js/standaard_js/jquery.infieldlabel.min.js"></script>
	<script>  <?php if($_GET['lock'] != null){ ?> var lock = '<?php echo $_GET['lock']; ?>';  <?php } ?>  </script>
	<script src="http://qern.nl/login/js/login.js"></script>
</body>
</html>