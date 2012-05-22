<div id="inloggen">
<?php

    if(isset($_SESSION['error'])){
        echo '<div id="error">'.$_SESSION['error']. '</div>';
        unset($_SESSION['error']);
    }elseif(isset($_SESSION['uitgelogd'])){
        echo '<div id="uitgelogd">'.$_SESSION['uitgelogd']. '</div>';
        unset($_SESSION['uitgelogd']);
    }
?>
<form name="form1" method="post" action="index.php" id="inlog_formulier">
<div id="login_gebruikersnaam">
    <label for="gebruikersnaam" id="gebruiker_label">Gebruikersnaam:</label>
    <input name="gebruikersnaam" type="text" id="gebruikersnaam" class="textfield" />
</div>

<div id="login_wachtwoord">
    <label for="wachtwoord" id="wachtwoord_label">Wachtwoord:</label>
    <input name="wachtwoord" type="password" id="wachtwoord" class="textfield" />
</div>

<div id="login_onthoud">
    <input type="checkbox" name="cookie" value="onthouden" id="cookie" /><label for="cookie" id="cookie_label">Onthoud mijn gegevens!</label>
</div>

<div id="login_verstuur">
    <input type="submit" name="Submit" value="Login" class="button" id="inloggen_knop" onmouseout="this.className='button'" onmouseover="this.className='button btn_hover'" />
</div>

</form>
</div>