<?php 
    session_start();
    //include de benodigde configuration
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    if($_POST['action'] == 'update_goed_in'){
       $goed_in = mysql_real_escape_string($_POST['goed_in']);
       
       $table = 'portal_gebruiker';
       $what = "is_goed_in = '".$goed_in."'";
       $where = 'id ='.$login_id;
        $update_gebruiker = sqlUpdate($table, $what, $where);
       
       $what = 'id AS goedin'; $from = 'portal_gebruiker_recent'; 
       $where = 'gebruiker = '.$login_id.' AND wat = \'goedin\'';
        $recent = mysql_fetch_assoc(sqlSelect($what, $from, $where));
        
        if($recent['goedin'] != null){
               $table = 'portal_gebruiker_recent'; $what = 'gewijzigd_op = NOW()'; $where = 'gebruiker = '.$login_id.' AND wat = \'goedin\'';
                $update_recent = sqlUpdate($table, $what, $where);
        }else{
               $table = 'portal_gebruiker_recent'; $what = 'gebruiker, wat, gewijzigd_op'; 
               $with_what = $login_id.', \'goedin\', NOW()';
                $insert_recent = sqlInsert($table, $what, $with_what);
        }
    }
    elseif($_POST['action'] == 'refreshAll'){
        $refresh_all = 1;
    }
    elseif($_POST['action'] == 'refreshTop10'){
        $refresh_top10 = 1;
    }
    elseif($_POST['action'] == 'createkennis'){
        $kennis = $_POST['kennis'];
        $what = 'DISTINCT kennis, id'; $from = 'portal_kenniskaart'; $where='id > 1';
        $aantal_kennis = countRows($what, $from, $where);
        $kennis_result = sqlSelect($what, $from, $where);
        if($aantal_kennis > 0){
                //hoeveel kennis zijn er met deze kennisnaam, tel ze
                $what = 'DISTINCT a.id, b.gebruiker'; $from = 'portal_kenniskaart a, portal_gebruiker_kenniskaart b'; $where="a.kennis = '$kennis' AND b.kennis = a.id";
                $aantal_kennis = countRows($what, $from, $where);
                
                //is het er 1 of meer, dan kijken of het een bekende is. zo niet: toevoegen
                //niet bekend ? toevoegen.
                if($aantal_kennis > 0){
                    $kennis = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                
                    if($login_id == $kennis['gebruiker']){
                        //doe niets... want de kennis is al bekend bij deze gebruiker
                    }else{
                        //aangezien de ingevoerde kennis wel bestaat, maar (nog) niet is gekoppeld aan deze gebruiker.
                        //doen we dat nu !
                        $table = 'portal_gebruiker_kenniskaart'; $what = 'kennis, gebruiker';
                        $with_what = $kennis['id'].', '.$kennis['gebruiker'];
                            $kennis_koppelen = sqlInsert($table,$what,$with_what);
                            $gewijzgid = 1;
                    }
                }else{
                    //aangezien de ingevoerde kennis (nog) niet bestaat, maken we deze aan.
                    $table = 'portal_kenniskaart'; $what = 'kennis';
                    $with_what = "'$kennis'";
                        $kennis_toevoegen = sqlInsert($table,$what,$with_what);
                    
                    //ook koppelen we deze nieuwe kennis aan de activiteit.
                    $table = 'portal_gebruiker_kenniskaart'; $what = 'kennis, gebruiker';
                    $with_what = '(SELECT MAX(a.id) FROM portal_kenniskaart a WHERE id > 0), '.$login_id;
                        $kennis_koppelen = sqlInsert($table,$what,$with_what);
                        $gewijzgid = 1;        
                }
        }else{
            //er bestaan nog geen tags.. dan doen we dit
            //aangezien de ingevoerde kennis (nog) niet bestaat, maken we deze aan.
            $table = 'portal_kenniskaart'; $what = 'kennis';
            $with_what = "'$kennis'";
                $kennis_toevoegen = sqlInsert($table,$what,$with_what);
                    
            //ook koppelen we deze nieuwe kennis aan de activiteit.
            $table = 'portal_gebruiker_kenniskaart'; $what = 'kennis, gebruiker';
            $with_what = '(SELECT MAX(a.id) FROM portal_kenniskaart a WHERE id > 0), '.$login_id;
                $kennis_koppelen = sqlInsert($table,$what,$with_what);
                $gewijzgid = 1; 
        }
        if($gewijzgid != null){
            $what = 'id AS kennis'; $from = 'portal_gebruiker_recent'; $where = 'gebruiker = '.$login_id.' AND wat = \'kennis\'';
                $recent = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            if($recent['kennis'] != null){
               $table = 'portal_gebruiker_recent'; $what = 'gewijzigd_op = NOW()'; $where = 'gebruiker = '.$login_id.' AND wat = \'kennis\'';
                $update_recent = sqlUpdate($table, $what, $where);
           }else{
               $table = 'portal_gebruiker_recent'; $what = 'gebruiker, wat, gewijzigd_op'; 
               $with_what = $login_id.', \'kennis\', NOW()';
                $insert_recent = sqlInsert($table, $what, $with_what);
           }
        }
        echo '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
        $refresh_all = 1;
        
    }
    elseif($_POST['action'] == 'deletekennis'){
        $table = 'portal_gebruiker_kenniskaart'; $where = 'kennis = '.$_POST['kennis_id'].' AND gebruiker = '.$login_id;
            $delete_gebruiker_kennis_koppeling = sqlDelete($table, $where);

        echo '<div class="wijzig_info_success">Uw gegevens zijn aangepast</div>';
        $refresh_all = 1;
        
    }
    elseif($_POST['action'] == 'uitTop10'){
        //haal de kennis uit te lijst, door het uitzetten van de positie
        $table = 'portal_gebruiker_kenniskaart';
        $what = 'top_positie = 0';
        $where = 'kennis = '.$_POST['kennis_id'];
            $update_kennis_volgorde = sqlUpdate($table, $what, $where);        
            
        $refresh_top10 = 1;
    }
    elseif($_POST['action'] == 'sort_kennis'){
        parse_str($_REQUEST['kennis'], $sort_afbeelding);
        foreach($sort_afbeelding['kennis'] as $positie => $id){
            $positie++;
            if($positie > 10){$db_positie = 0;}
            else{$db_positie = $positie;}
            $table="portal_gebruiker_kenniskaart";
            $what = "top_positie = $db_positie";
            $where = "kennis = $id AND gebruiker = $login_id";
                $update_kennis_volgorde = sqlUpdate($table, $what, $where);        
        }
        $refresh_top10 = 1;
        
    }
    else{echo 'Er is iets fout gegaan !';}
    
    //doe dit alleen als de variabele 1 is.
    if($refresh_top10 == 1){
        $what = 'a.top_positie, b.id, b.kennis';
        $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_kenniskaart AS b ON (b.id = a.kennis)';
        $where = 'a.gebruiker = '.$login_id.' AND a.top_positie > 0 ORDER BY a.top_positie ASC';
            $kennis_res = sqlSelect($what, $from, $where);
   
        $volgnummer = 1;
    while($topkennis = mysql_fetch_array($kennis_res)){
        if($volgnummer < 11){
            ?>
            <li id="kennis_<?php echo $topkennis['id']; ?>" class="top_kennis" onmouseover="this.className='top_kennis kennis_hover'" onmouseout="this.className='top_kennis'">
                <div class="kennis_left">
                    <span class="kennis_volgnummer"><?php echo $volgnummer; ?></span>
                    &nbsp;<?php echo $topkennis['kennis']; ?>
                </div>
                <div class="kennis_right">
                    <div class="kennis_uit_top_10" onmouseover="this.className='kennis_uit_top_10 kennis_uit_top_10_hover'" onmouseout="this.className='kennis_uit_top_10'" 
                         onclick="uitTop10(<?php echo $topkennis['id'] ?>)" >
                         Uit top 10
                    </div>
                    <div class="kennis_delete">
                        <img class="kennis_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder kennis" title="Verwijder kennis" onclick="kennisVerwijderen(<?php echo $topkennis['id'] ?>)" />
                    </div>
                    <div class="kennis_social">
                    <?php
                        $what = 'COUNT(gebruiker) AS aantal'; $from = 'portal_gebruiker_kenniskaart'; $where = 'kennis = '.$topkennis['id'].' AND gebruiker != '.$login_id;
                            $collega_kennis = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                        if($collega_kennis['aantal'] > 0){
                            if($collega_kennis['aantal'] == 1){$title = $collega_kennis['aantal'].' collega kan dit ook';}
                            else{$title = $collega_kennis['aantal'].' collega\'s kunnen dit ook';}?>
                            <img class="collega_kennis" src="<?php echo $etc_root ?>functions/account/css/images/collega_kennis.png" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
                    <?php   $what = 'b.gebruikersnaam, b.voornaam, b.achternaam'; $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)'; 
                            $where = 'a.kennis = '.$topkennis['id'].' AND a.gebruiker != '.$login_id;
                            $kennis_collegas = sqlSelect($what, $from, $where);
                            //dit is om later een sociale functie toe te voegen                            
                        }
                    ?>
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
            <li class="top_kennis top_kennis_leeg">
                <span class="kennis_volgnummer"><?php echo $i; ?></span>
                &nbsp;leeg
            </li>
        <?php }
        }
    }
    if($refresh_all == 1){
        $what = 'a.top_positie, b.id, b.kennis';
        $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_kenniskaart AS b ON (b.id = a.kennis)';
        $where = 'a.gebruiker = '.$login_id.' ORDER BY b.kennis ASC';
            $kennis_res = sqlSelect($what, $from, $where);
        while($kennis = mysql_fetch_array($kennis_res)){?>
            <li id="kennis_<?php echo $kennis['id']; ?>" class="kennis" onmouseover="this.className='kennis kennis_hover'" onmouseout="this.className='kennis'">
                <div class="kennis_left"><?php echo $kennis['kennis']; ?></div>
                <div class="kennis_right">
                    <div class="kennis_delete">
                        <img class="kennis_delete_img" src="<?php echo $etc_root ?>functions/account/css/images/delete.png" alt="Verwijder kennis" title="Verwijder kennis" onclick="kennisVerwijderen(<?php echo $kennis['id'] ?>)" />
                    </div>
                    <div class="kennis_social">
                    <?php
                        $what = 'COUNT(gebruiker) AS aantal'; $from = 'portal_gebruiker_kenniskaart'; $where = 'kennis = '.$kennis['id'].' AND gebruiker != '.$login_id;
                            $collega_kennis = mysql_fetch_assoc(sqlSelect($what, $from, $where));
                        if($collega_kennis['aantal'] > 0){
                            if($collega_kennis['aantal'] == 1){$title = $collega_kennis['aantal'].' collega kan dit ook';}
                            else{$title = $collega_kennis['aantal'].' collega\'s kunnen dit ook';}?>
                            <img class="collega_kennis" src="<?php echo $etc_root ?>functions/account/css/images/collega_kennis.png" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
                    <?php   $what = 'b.gebruikersnaam, b.voornaam, b.achternaam'; $from = 'portal_gebruiker_kenniskaart a LEFT JOIN portal_gebruiker AS b ON (b.id = a.gebruiker)'; 
                            $where = 'a.kennis = '.$kennis['id'].' AND a.gebruiker != '.$login_id;
                            $kennis_collegas = sqlSelect($what, $from, $where);
                            //dit is om later een sociale functie toe te voegen                            
                        }
                    ?>
                    </div>
                </div>
            </li>
        <?php }
    }
?>