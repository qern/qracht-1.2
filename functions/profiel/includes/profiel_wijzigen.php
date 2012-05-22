<?php
    $what = "
    a.id            profiel_id,
    a.leidinggevende_id,
    a.profieltekst,
    a.twitter,
    a.skype,
    a.linkedin,
    a.facebook,
    a.youtube,
    a.hyves,
    b.relatie_id,
    b.id            gebruiker_id,
    b.qwetternaam,
    c.voornaam      profiel_voornaam,
    c.achternaam    profiel_achternaam,
    c.email,
    c.mobiel,
    c.plaats,
    d.titel         functie,
    e.relatie_id,
    f.voornaam      leidinggevende_voornaam,
    f.achternaam    leidinggevende_achternaam
    ";
    $from="
    profiel a
    LEFT JOIN gebruiker AS b ON(b.id = a.gebruiker_id)
    LEFT JOIN relaties AS c ON (c.id = b.relatie_id)
    LEFT JOIN functie AS d ON (d.id = c.functie_id)
    LEFT JOIN gebruiker AS e ON (e.id = a.leidinggevende_id)
    LEFT JOIN relaties AS f ON (f.id = e.relatie_id)
    ";
    $where="
    a.gebruiker_id = $login_id
    AND c.actief = 1
    AND f.actief = 1
    ";
    $result =  sqlSelect($what,$from,$where);
    $row = mysql_fetch_assoc($result);
    $profiel_id = $row['profiel_id'];
?>
<div id="profiel_wijzigen">
    <div id="profiel_wijzigen_titel">
        <h2>Profiel Wijzigen</h2>

    </div>
    <div id="informatie_wijzigen">
    <form name="informatie_wijzigen" id="informatie_form" action="/functions/<?php echo $_GET['function']; ?>/includes/profiel_check.php?action=info" method="post">
        <div id="info_wijzigen_titel">
        <h2 id="wijzigen_per_info">Persoonlijke informatie</h2>
<?php
 if($_GET["gewijzigd"] == 'info'){ echo '<h2 class="gewijzigd">U hebt met succes uw informatie gewijzigd</h2>';}
?>        
        </div>
            <div class="informatie_wijzigen_row">
                <label id="qwetter_label" class="tooltip" title="wijzig hier uw qwetternaam">qwetternaam : </label>
                <div id="qwetternaam_input"> <input class="textfield" type="text" name="qwetternaam" id="qwetternaam" value="<?php echo $row['qwetternaam']; ?>" /> </div>
            </div>
            <div class="informatie_wijzigen_row">
                <label id="email_label" class="tooltip" title="Vul hier uw e-mailadres in ">e-mailadres : </label>
                <div id="email_input"> <input class="textfield"type="text" id="email" name="email" value="<?php echo $row['email']; ?>"/> </div>
            </div>

            <div class="informatie_wijzigen_row">
                <label id="mobiel_label" class="tooltip" title="Voer hier uw mobiel telefoonnummer in">mobiel : </label>
                <div id="mobiel_input"> <input class="textfield" type="text" id="mobiel" name="mobiel" value="<?php echo $row['mobiel']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="plaats_label" class="tooltip" title="Voer hier uw woonplaats in">plaats : </label>
                <div id="plaats_input"> <input class="textfield" type="text" id="plaats" name="plaats" value="<?php echo $row['plaats']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="twitter_label" class="tooltip" title="Voer hier twitter gebruikersnaam in">twitter gebruikersnaam : </label>
                <div id="twitter_input"> <input class="textfield" type="text" id="twitter" name="twitter" value="<?php echo $row['twitter']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="facebook_label" class="tooltip" title="Voer hier de url van uw facebookprofiel in ">facebook profielpagina (url) : </label>
                <div id="facebook_input"> <input class="textfield" type="text" id="facebook" name="facebook" value="<?php echo $row['facebook']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="linkedin_label" class="tooltip" title="Voer hier de url van uw linkedinprofiel in ">linkedin profielpagina (url) : </label>
                <div id="linkedin_input"> <input class="textfield" type="text" id="linkedin" name="linkedin" value="<?php echo $row['linkedin']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="skype_label" class="tooltip" title="Voer hier uw skype gebruikersnaam in">skype gebruikersnaam : </label>
                <div id="skype_input"> <input class="textfield" type="text" id="skype" name="skype" value="<?php echo $row['skype']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="hyves_label" class="tooltip" title="Voer de link naar uw hyvespagina in">hyves profielpagina (url) : </label>
                <div id="hyves_input"> <input class="textfield" type="text" id="hyves" name="hyves" value="<?php echo $row['hyves']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="youtube_label" class="tooltip" title="Voer hier uw youtube gebruikersnaam in">youtube gebruikersnaam : </label>
                <div id="youtube_input"> <input class="textfield" type="text" id="youtube" name="youtube" value="<?php echo $row['youtube']; ?>"/> </div>
            </div>
            
            <div class="informatie_wijzigen_row">
                <label id="profieltekst_label" class="tooltip" title="wijzig hier uw profieltekst">profieltekst : </label>
                <div id="profieltekst_input"> <textarea class="textarea" name="profieltekst" id="profieltekst"><?php echo $row['profieltekst']; ?> </textarea> </div>
            </div>
            <input type="hidden" name="profiel_id" value="<?php echo $profiel_id; ?>" />
            <input type="submit" value="opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" />
    </form>
    </div>
    
    <div id="links_wijzigen">
    <form name="informatie_wijzigen" id="informatie_form" action="/functions/<?php echo $_GET['function']; ?>/includes/profiel_check.php?action=links" method="post">
        <div id="links_titel_row">
            <h2 id="links_wijzigen_titel">Links wijzigen en toevoegen</h2> 
<?php
 if($_GET["gewijzigd"] == 'links'){ echo '<h2 class="gewijzigd">U hebt met succes uw links gewijzigd</h2>';}
?>
            <button id="voeg_links_toe" type="button" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Voeg een link toe </button>
        </div>
<?
$what="url,omschrijving";
$from="profiel_links";
$where="profiel_id = $profiel_id";
$links_resultaat = sqlSelect($what,$from,$where);
while($links = mysql_fetch_array($links_resultaat)){
echo '
<div class="links_input">
    <label for="link-input" class="tooltip" title="Voer hier de url in van de link">Link url:</label>
    <input type="text" class="textfield" id="link-input" name="link_url[]" value="'.$links['url'].'"/>
    <label for="link-omschrijving" class="tooltip" title="voer hier een beschrijving van de link in.">Link omschrijving:</label>
    <input type="text" class="textfield" id="omschrijving-input" name="link_omschrijving[]" value="'.$links['omschrijving'].'" />
</div>';
}
?>
                
                
                <div id="links_container">
                
                </div>
                <input type="hidden" name="profiel_id" value="<?php echo $profiel_id; ?>" />
                <input type="submit" value="opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" />
    </form>
    </div>
    
    <div id="fotos_wijzigen">
    <form name="foto_wijzigen" id="informatie_form" action="/functions/<?php echo $_GET['function']; ?>/includes/profiel_check.php?action=foto" method="post" enctype="multipart/form-data"> 
        <div id="foto_titel_row">
            <h2 id="foto_wijzigen_titel">Foto's wijzigen en uploaden</h2>
<?php
 if($_GET["gewijzigd"] == 'foto'){ echo '<h2 class="gewijzigd">U hebt met succes uw foto'."'s".' gewijzigd</h2>';}
?>
            <button id="voeg_foto_toe" type="button" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Voeg een foto toe </button>
        </div>
<?php
$what="id, foto, caption";
$from="profiel_fotoalbum";
$where="profiel_id = $profiel_id";
$foto_resultaat = sqlSelect($what,$from,$where);
while($row = mysql_fetch_array($foto_resultaat)){
echo '
<div class="foto_aanpassen" onmouseover="this.className='."'foto_aanpassen_hover'".'" onmouseout="this.className='."'foto_aanpassen'".'">
        <span class="foto_options">
            <a class="maak_profielfoto" href="/functions/'.$_GET['function'].'/includes/profiel_check.php?action=foto&id='.$row['id'].'&consequence=profielfoto&profiel_id='.$profiel_id.'" title="maak deze foto uw profielfoto">Maak profielfoto</a>
            <a class="verwijder_foto" href="/functions/'.$_GET['function'].'/includes/profiel_check.php?action=foto&id='.$row['id'].'&consequence=deletion&profiel_id='.$profiel_id.'" title="verwijder deze foto">X</a>
        </span>
        <div class="foto_holder">
            <a href="/files/profiel_foto/'.$row['foto'].'" title="'.$row['caption'].'" class="foto_tonen">
                <img src="/lib/slir/w130-h100-h100-c98.68/files/profiel_foto/'.$row['foto'].'" alt="'.$row['caption'].'" title="'.$row['caption'].'" />
            </a>
        </div>
        <textarea cols="14" rows="3" name="caption['.$row['id'].']" class="foto_caption">'.$row['caption'].'</textarea>
</div>';
}
?>      
        <div id="foto_container">
                
        </div>
        <input type="hidden" name="profiel_id" value="<?php echo $profiel_id; ?>" />
        <input type="submit" value="opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" />
    </form>
    </div> 
    
    <div id="kennis_wijzigen">
    <form name="kennis_wijzigen" id="informatie_form" action="/functions/<?php echo $_GET['function']; ?>/includes/profiel_check.php?action=kennis" method="post">
        <div id="kennis_titel_row">
            <h2 id="kennis_wijzigen_titel">Kenniskaart wijzigen en kennis toevoegen</h2>
<?php
 if($_GET['gewijzigd'] == 'kennis'){ echo '<h2 class="gewijzigd_kennis">U hebt met succes uw kenniskaart gewijzigd</h2>';}
?>
            <button id="voeg_kennis_toe" type="button" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Voeg kennis toe </button>
        </div>
<?php
$what="term";
$from="kenniskaart";
$where="profiel_id = $profiel_id";
$kennis_resultaat = sqlSelect($what,$from,$where);
while($kennis = mysql_fetch_array($kennis_resultaat)){
echo '
<div class="kennis_input">
    <label for="link-input" class="tooltip" title="Voer hier de kennisterm in">Term:</label>
    <input type="text" class="textfield term_input" name="term[]" value="'.$kennis['term'].'"/>
</div>';
}
?>
                <div id="kennis_container">
                
                </div>
                <input type="hidden" name="profiel_id" value="<?php echo $profiel_id; ?>" />
                <input type="submit" value="opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" />
    </form>
    </div>
</div>
