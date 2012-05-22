<?php 
if($_SESSION['admin'] == 1){
    if($_GET['gebruiker_id'] != null){
        $action = 'beheren';
        $what = 'a.loginnaam, a.voorletters, a.achternaam, a.email, a.telefoon, a.organisatie_nummer, a.actief, b.naam organisatie';
        $from = 'gebruiker a, organisatie b';
        $where = 'b.id = a.organisatie_nummer AND a.id = '.$_GET['gebruiker_id'];
        $gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    }else{
        $action = 'toevoegen';
    }
?>
<div id="admin_menu">
    <a href="/admin/">Terug naar het overzicht</a>
</div>
<div id="admin_titel">
    <h2>Gebruiker <?php echo $action; ?>
    <?php 
    if($action == 'beheren'){?>
    <span id="actions">
        <?php
            if($row['actief'] == 1){echo'
                <a href="javascript:toggleActive();" title="zet gebruiker op non-actief. OPGELET: De gebruiker kan dan niet meer inloggen!">Deactiveren</a>
            ';}else{echo'
                <a href="javascript:toggleActive();" title="zet gebruiker op actief. OPGELET: De gebruiker kan dan weer inloggen met het oude wachtwoord en gebruikernaam">Activeren</a>
            ';}
?>
        <a href="javascript:toggleReset();" title="Maak het wachtwoord voor deze gebruiker opnieuw aan. De gebruiker ontvangt een e-mail met het nieuwe wachtwoord">Nieuw wachtwoord</a>
    </span>
    </h2>
</div>
        <div id="active_disclaimer" style="display:none;">
            <div class="disclaimer_inhoud">
                <?php 
                    if($row['actief'] == 1){echo'
                    <h2>Gebruiker deactiveren</h2>
                    <p>Als u verder gaat, deactiveert u deze gebruiker. Hierdoor kan deze niet meer inloggen en bestanden inzien.<br />
                    Alle instellingen worden onthouden, het wachtwoord en gebruikersnaam blijven hetzelfde.<br />
                    U kunt deze gebruiker wanneer u maar wilt weer actief maken.<br />
                    N.B. De gebruiker ontvangt hier geen automatische e-mail over.
                    </p>
                    
                    <div id="activate_trigger">
                        <a class="doorgaan" href="/functions/admin/includes/admin_check.php?action=deactivate_user&gebruiker_id='.$_GET['gebruiker_id'].'">Deactiveer gebruiker</a>
                        <a class="annuleren" href="javascript:toggleActive();">Annuleer</a>
                    </div>
                    ';}else{echo'
                    <h2>Gebruiker activeren</h2>
                    <p>Als u verder gaat, activeert u deze gebruiker. Hierdoor kan deze weer inloggen en bestanden inzien.<br />
                    Alle instellingen zijn onthouden, het wachtwoord en gebruikersnaam zijn hetzelfde gebleven.<br />
                    U kunt deze gebruiker wanneer u maar wilt weer deactiveren.<br />
                    N.B. De gebruiker ontvangt hier geen automatische e-mail over.
                    </p>
                    
                    <div id="activate_trigger">
                        <a class="doorgaan" href="/functions/admin/includes/admin_check.php?action=activate_user&gebruiker_id='.$_GET['gebruiker_id'].'">Activeer gebruiker</a>
                        <a class="annuleren" href="javascript:toggleActive();">Annuleer</a>
                    </div>
                    ';
                        
                    }
                ?>
            </div>
        </div>
        <div id="reset_disclaimer" style="display:none;">
            <div class="disclaimer_inhoud">
                <h2>Wachtwoord resetten</h2>
                <p>Als u verder gaat, maakt u een nieuw wachtwoord aan voordeze gebruiker.<br />
                    Hierdoor kan de gebruiker met het huidige wachtwoord niet meer inloggen.<br />
                    De gebruikersnaam blijft wel hetzelfde.<br />
                    N.B. De gebruiker ontvangt hier een automatische e-mail over, waar het nieuwe wachtwoord in staat.<br />
                    Tevens dienst de gebruiker het wachtwoord te wijzigen na de eerste keer inloggen.
                </p>
                    
                <div id="reset_trigger">
                    <a class="doorgaan" href="/functions/admin/includes/admin_check.php?action=reset_pass&gebruiker_id=<?php echo $_GET['gebruiker_id']; ?>">Nieuw wachtwoord</a>
                    <a class="annuleren" href="javascript:toggleReset();">Annuleer</a>
                </div>
            </div>
        </div>
        
        <?php
    }//einde van de acties en disclaimers...
            
            if($_SESSION['succes'] != null){
                echo '<p id="succes">'.$_SESSION['succes'].'</p>';
                unset($_SESSION['succes']);
            }
        ?>
        <form name="gebruiker_toevoegen" id="gebruiker_toevoegen" action="/functions/admin/includes/admin_check.php" method="post" >
            <div id="form_left">
                <?php
                    if($action == 'beheren'){echo '
                        <div id="gebruikersnaam_input">
                            <label for="gebruikersnaam">Gebruikersnaam</label>
                            <input type="text" class="textfield" name="loginnaam" id="loginnaam" value="'.$gebruiker['loginnaam'].'" />
                        </div>
                    ';}
                ?>
                <div id="organisatie_nummer_input">
                    <label for="organisatie_nummer">Organisatie nummer</label>
                    <input type="text" class="textfield" name="organisatie_nummer" id="organisatie_nummer" value="<?php echo $gebruiker['organisatie_nummer'] ?>" onkeyup="toonOrganisatie(this.value)" onblur="toonOrganisatie(this.value)" />
                </div>
                <div id="autocomplete_organisatie">
                    <label>Organisatie naam:</label>
                    <p><?php echo $gebruiker['organisatie'] ?></p>
                </div>
            </div>
            <div id="form_right">
                
                    <div id="voorletters_input">
                        <label for="voorletters">Voorletters</label>
                        <input type="text" class="textfield" name="voorletters" id="voorletters" value="<?php echo $gebruiker['voorletters'] ?>" />
                    </div>
                    <div id="achternaam_input">
                        <label for="achternaam">Achternaam</label>
                        <input type="text" class="textfield" name="achternaam" id="achternaam" value="<?php echo $gebruiker['achternaam'] ?>" />
                    </div>
                
                    <div id="email_input">
                        <label for="email">E-mailadres</label>
                        <input type="text" class="textfield" name="email" id="email" value="<?php echo $gebruiker['email'] ?>" />
                    </div>
                    
                    <div id="email_input">
                        <label for="telefoon">Telefoonnummer</label>
                        <input type="text" class="textfield" name="telefoon" id="telefoon" value="<?php echo $gebruiker['telefoon'] ?>" />
                    </div>
                
                    <div id="form_send">
                        <input type="hidden" name="action" value="<?php echo $action ?>" />
                        <?php 
                        if($action == 'beheren'){echo'
                        <input type="hidden" name="gebruiker_id" value="'.$_GET['gebruiker_id'].'" />';
                        }
                        ?>
                        <input type="submit" value="opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" />
                    </div>
            
            </div>        
        </form>
        
<div id="error_container">
    <h2 id="error_titel"></h2>
        <ul id="errors">
<?php
//Geef fouten hieronder weer, net als succesmeldingen.
    if(isset($_SESSION['error'])){
        foreach($_SESSION['error'] as $error){ 
            echo '<li> <span class="error" style="display: block;">'.$error.'</span> </li>'; 
        }
        unset($_SESSION['error']);
    }elseif(isset($_GET['gewijzigd'])){
        if($_GET['gewijzigd'] == 'gebruikersnaam'){ echo '<li><span class="success" style="display: block;">De gebruikersnaam is gewijzigd</span></li>';}
        if($_GET['gewijzigd'] == 'wachtwoord'){     echo '<li><span class="success" style="display: block;">Het wachtwoord is gewijzigd</span></li>';}
        if($_GET['gewijzigd'] == 'qwetternaam'){    echo '<li><span class="success" style="display: block;">De qwetternaam is gewijzigd</span></li>';}
        if($_GET['gewijzigd'] == 'profielnaam'){    echo '<li><span class="success" style="display: block;">De profielnaam is gewijzigd</span></li>';}
    }
?>        
        </ul>
    </div>
<?php }else{
    echo '<a href="/account/" style="float:left; margin:30px 0 0 495px"><h2>Klik hier om uw account te wijzigen</h2></a>';
} ?>