<?php 
    session_start();
    //include de benodigde configuration
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    if($_GET['action'] == 'refreshForm'){$refresh_form = 1;}
    elseif($_GET['action'] == 'refreshAll'){$refresh_all = 1;}
    elseif($_GET['action'] == 'refreshTop10'){$refresh_top10 = 1;}
    elseif($_POST['action'] == 'createlink'){
        
        if($_POST['url'] == null){$error['url_leeg'] = 'U dient een correcte url in te voeren. <br /> Dit begint met: http://. Bijvoorbeeld: http://google.com of http://www.nu.nl';} //is de link gevuld ?
        else{ //is de link een goede link ?
            if(checkUrl($_POST['url'])){$url = checkUrl($_POST['url']);}
            else{$_SESSION['url'] = $_POST['url']; $error['url_onjuist'] = 'U dient een correcte url in te voeren. <br /> Dit begint met: http://. Bijvoorbeeld: http://google.com of http://www.nu.nl';}
            /*
            if(filter_var($url, FILTER_VALIDATE_URL , FILTER_FLAG_HOST_REQUIRED)){
                $lru = strrev($url); $laatste_punt = strpos($lru, '.');
                if($laatste_punt > 3 || $laatste_punt <= 1){
                    $error['url_onjuist'] = 'U dient een correcte url in te voeren. <br /> Dit begint met: http://. Bijvoorbeeld: http://google.com of http://www.nu.nl';
                    $_SESSION['url'] = $url;
                }
            }else{
                $error['url_onjuist'] = 'U dient een correcte url in te voeren. <br /> Dit begint met: http://. Bijvoorbeeld: http://google.com of http://www.nu.nl';
                $_SESSION['url'] = $url;
            }
            */
        }
        if($_POST['beschrijving'] == null){$error['lege_beschrijving'] = 'U dient een beschrijving voor de url in te voeren. <br />';}//is de link gevuld ? nee : fout
        elseif(strlen($_POST['beschrijving']) > 80){$beschrijving = substr($_POST['beschrijving'], 0, 80);}//is de beschrijving groter dan 80 ? nee: laat maar 80 tekens zien
        else{
            $find = array('\"', "\\'"); $replace = array('"', '\''); $beschrijving = str_ireplace($find, $replace, $_POST['beschrijving']);
            $beschrijving = htmlentities($beschrijving, ENT_NOQUOTES, "UTF-8"); $_SESSION['beschrijving'] = $beschrijving;
        }// alles is goed
        
        if($_POST['categorie'] == 'zakelijk'){$_SESSION['zakelijk'] = 'selected = "selected"';}
        elseif($_POST['categorie'] == 'prive'){$_SESSION['prive'] = 'selected = "selected"';}
        elseif($_POST['categorie'] == 'hobby'){$_SESSION['hobby'] = 'selected = "selected"';}
        elseif($_POST['categorie'] == 'overig'){$_SESSION['overig'] = 'selected = "selected"';}
        else{$_SESSION['zakelijk'] = 'selected = "selected"';}
        
        if(!$error){ //zijn er geen fouten, wijzig de database dan
           $table = 'portal_link';
           $what = "link, omschrijving, categorie, toegevoegd_door, toegevoegd_op";
           $with_what = '\''.mysql_real_escape_string($url).'\', \''.mysql_real_escape_string($beschrijving).'\', \''.mysql_real_escape_string($_POST['categorie']).'\', '.$login_id.', NOW()';
                $nieuwe_link = sqlInsert($table, $what, $with_what);
           
           $table = 'portal_gebruiker_link';
           $what = 'gebruiker, link';
           $with_what = $login_id.', (SELECT MAX(id) FROM portal_link WHERE actief = 1)';
                $nieuwe_gebruiker_link_koppeling = sqlInsert($table, $what, $with_what);
           
           //laat de gebruiker weten dat het goed is gegaan
           $message = '<div class="wijzig_info_success">Uw link is toegevoegd</div>';
           
           //het formulier moet herladen worden, maar moet wel worden geladen zonder inhoud (anders gaan ze het wellicht nogmaals);
           if(isset($_SESSION['url']))unset($_SESSION['url']);           if(isset($_SESSION['beschrijving']))unset($_SESSION['beschrijving']);
           if(isset($_SESSION['zakelijk']))unset($_SESSION['zakelijk']); if(isset($_SESSION['prive']))unset($_SESSION['prive']);
           if(isset($_SESSION['hobby']))unset($_SESSION['hobby']);       if(isset($_SESSION['overig']))unset($_SESSION['overig']);
           $refresh_form = 1;
           echo '<script>refreshAll();</script>';
       }else{   //zijn er fouten, geef dan aan welke
         if(count($error) > 1){
                $message = '<div class="wijzig_info_error"> 
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }else{
                $message = '<div class="wijzig_info_error">
                                <span class="error_header">Er zijn fouten opgetreden:</span>
                                <ul>';
           }
           foreach($error as $fout){
               $message .= '<li class="info_error">'.$fout.'</li>';
           }
           $message .= '</ul><span class="error_footer">Uw gewijzigde informatie is nog niet opgeslagen!</span>
                        </div>';
           //$message .= $_SESSION['url'];
           $refresh_form = 1;
       }
       //echo $message;        
        
    }
    elseif($_POST['action'] == 'deletelink'){
        $table = 'portal_gebruiker_link'; $where = 'link = '.$_POST['link_id'].' AND gebruiker = '.$login_id;
            $delete_gebruiker_kennis_koppeling = sqlDelete($table, $where);
            
        $table = 'portal_link'; $where = 'actief = 0'; $where = 'id = '.$_POST['link_id'];
            $link_non_actief = sqlUpdate($table, $what, $where);

        echo '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';        
    }
    elseif($_POST['action'] == 'uitTop10'){
        //haal de kennis uit te lijst, door het uitzetten van de positie
        $table = 'portal_gebruiker_link';
        $what = 'top_positie = 0';
        $where = 'link = '.$_POST['link_id'];
            $update_kennis_volgorde = sqlUpdate($table, $what, $where);        
            
        
    }
    elseif($_POST['action'] == 'sort_link'){
        parse_str($_REQUEST['link'], $sort_afbeelding);
        foreach($sort_afbeelding['link'] as $positie => $id){
            $positie++;
            if($positie > 10){$db_positie = 0;}
            else{$db_positie = $positie;}
            $table="portal_gebruiker_link";
            $what = "top_positie = $db_positie";
            $where = "link = $id AND gebruiker = $login_id";
                $update_link_volgorde = sqlUpdate($table, $what, $where);        
        }
        
        
    }
    else{echo 'Er is iets fout gegaan !';}
    
    //doe dit alleen als de variabele 1 is.
    if($refresh_top10 == 1){
        $what = 'a.top_positie, b.id, b.link, b.omschrijving, b.categorie';
        $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
        $where = 'a.gebruiker = '.$login_id.' AND a.top_positie > 0 ORDER BY a.top_positie ASC';
            $links_res = sqlSelect($what, $from, $where);
   
        $volgnummer = 1;
        while($link = mysql_fetch_array($links_res)){
            if($volgnummer < 11){?>
            <li id="link_<?php echo $link['id']; ?>" class="top_link" onmouseover="this.className='top_link link_hover'" onmouseout="this.className='top_link'">
                <div class="link_left">
                    <span class="link_volgnummer"><?php echo $volgnummer; ?></span>
                    <a href="<?php echo $link['link']; ?>" title="<?php echo $link['omschrijving'] ?>" target="_blank"><?php echo $link['omschrijving'] ?></a>
                    <span class="categorie">Categorie:  <?php echo $link['categorie']; ?></span>
                </div>
                <div class="link_right">
                    <div class="link_uit_top_10" onmouseover="this.className='link_uit_top_10 link_uit_top_10_hover'" onmouseout="this.className='link_uit_top_10'" 
                         onclick="uitTop10(<?php echo $link['id'] ?>)" >
                         Uit top 10
                    </div>
                    <div class="link_delete">
                        <img class="link_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder link" title="Verwijder link" onclick="linkVerwijderen(<?php echo $link['id'] ?>)" />
                    </div>
                </div>
            </li>
        <?php 
            $volgnummer++;
            }
        }
        //als er minder dan 10 zijn, vul de rest op met lege plekken
        if($volgnummer < 11){
            for($i = $volgnummer; $i < 11; $i++){?>
            <li class="top_link top_link_leeg">
                <span class="link_volgnummer"><?php echo $i; ?></span>
                &nbsp;leeg
            </li>
        <?php }
        }
    }
    if($refresh_all == 1){
        $what = 'a.top_positie, b.id, b.link, b.omschrijving, b.categorie';
        $from = 'portal_gebruiker_link a LEFT JOIN portal_link AS b ON (b.id = a.link)';
        $where = 'a.gebruiker = '.$login_id.' ORDER BY b.omschrijving ASC';
            $links_res = sqlSelect($what, $from, $where);
        while($link = mysql_fetch_array($links_res)){?>
            <li id="link_<?php echo $link['id']; ?>" class="link" onmouseover="this.className='link link_hover'" onmouseout="this.className='link'">
                <div class="link_left">
                    <a href="<?php echo $link['link']; ?>" title="<?php echo $link['omschrijving'] ?>" target="_blank"><?php echo $link['omschrijving'] ?></a>
                    <span class="categorie">Categorie:  <?php echo $link['categorie']; ?></span>
                </div>
                <div class="link_right">
                    <div class="link_delete">
                        <img class="link_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder link" title="Verwijder link" onclick="linkVerwijderen(<?php echo $link['id'] ?>)" />
                    </div>
                </div>
            </li>
        <?php }
    }
    if($refresh_form == 1){
        echo $message;?>
            <label for="url">Voer hier het hele webadres in (dit begint met http://)</label>
            <div class="link_input">
                <input type="text" id="url_invoer" name="url" class="textfield" value="<?php echo $_SESSION['url']; ?>"/>
            </div>
            
            <label for="url_beschrijving">Schrijf een korte beschrijving van de link</label>
            <div class="link_input">
                <input type="text" id="url_beschrijving_invoer" name="beschrijving" class="textfield" value="<?php echo $_SESSION['beschrijving']; ?>" />
                
                <div id="overgebleven_beschrijving">
                    <span id="overgebleven_tekst">Overgebleven tekens:</span>
                    <span id="overgebleven_aantal">80</span>
                </div>
            </div>
            
            <label for="url_categorie">Wat voor soort link is het ?</label>
            <div class="link_input">
                <select name="categorie" id="url_categorie_select" class="textfield">
                    <option value="zakelijk" <?php echo $_SESSION['zakelijk']; ?> >Zakelijk
                    <option value="prive"    <?php echo $_SESSION['prive']; ?> >Priv&eacute;
                    <option value="hobby"    <?php echo $_SESSION['hobby']; ?> >Hobby
                    <option value="overig"   <?php echo $_SESSION['overig']; ?> >Overig
                </select>
            </div>
            
            <button id="link_opslaan" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'" onclick="linkOpslaan()">
                Opslaan
            </button>
        <?php } ?>