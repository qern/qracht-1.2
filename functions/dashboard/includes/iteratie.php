 <?php
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
	//wat is het id van de huidige iteratie. Die kunnen we gebruiken.
    $what="id, DATE_FORMAT( datum,  '%d %M %Y' ) AS datum";$from="planning_iteratie"; $where="actief = 1 AND huidige_iteratie = 1";
    $iteratie = mysql_fetch_assoc(sqlSelect($what, $from, $where)); $iteratie_id = $iteratie['id'];
	
    if($_GET['q'] == 0){
        $what = 'DISTINCT b.organisatie, c.naam';
        $from = 'planning_activiteit a
				 LEFT JOIN project AS b ON ( b.id = a.project ) 
				 LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
				 LEFT JOIN planning_iteratie AS d ON ( d.id = a.iteratie )';
        $where = 'a.actief = 1 AND b.actief = 1 AND c.actief = 1 AND d.huidige_iteratie = 1 ';
        $result = sqlSelect($what, $from, $where);
    }else{
        $what = 'DISTINCT b.organisatie, c.naam';
        $from = 'planning_activiteit a
				 LEFT JOIN project AS b ON ( b.id = a.project ) 
				 LEFT JOIN organisatie AS c ON ( c.id = b.organisatie ) 
				 LEFT JOIN planning_iteratie AS d ON ( d.id = a.iteratie )';
        $where = 'a.actief = 1 AND b.actief = 1 AND c.actief = 1 AND d.huidige_iteratie = 1 AND (a.in_behandeling_door = '.$_GET['q'].' OR a.acceptatie_door = '.$_GET['q'].')';
        $result = sqlSelect($what, $from, $where);
    }
    
    $todo_totaal = 0;   $acceptatie_totaal = 0;
    $done_totaal = 0;   $onderhanden_totaal = 0;
    $grand_totaal = 0;
    
    unset($uren_status['to do']); unset($uren_status['onderhanden']);
    unset($uren_status['done']); unset($uren_status['acceptatie']);
    // als je nog niet klaar bent met typen... laat dan ook onderstaand niet zien !
    // echo "SELECT $what FROM $from WHERE $where";
    ?>
    <div id="iteratie_overzicht">
    <?php 
    while($row = mysql_fetch_array($result)){
        
        if($_GET['q'] != 0){
        	//haal per status (met uitzondering van nog te plannen), alle uren op.
            $what = 'a.status, SUM(a.uur_aantal) uren'; $from = 'planning_activiteit a LEFT JOIN project AS b ON (b.id = a.project)';
           	$where = "a.actief = 1 AND a.status != 'nog te plannen' AND b.organisatie = ".$row['organisatie'].' AND a.iteratie = '.$iteratie_id.' AND (a.in_behandeling_door = '.$_GET['q'].' OR a.acceptatie_door = '.$_GET['q'].') GROUP BY(a.status)';
            	$sub_result = sqlSelect($what, $from, $where);
        }else{
            //haal per status (met uitzondering van nog te plannen), alle uren op.
            $what = 'a.status, SUM(a.uur_aantal) uren'; $from = 'planning_activiteit a LEFT JOIN project AS b ON (b.id = a.project)';
            $where = "a.actief = 1 AND a.status != 'nog te plannen' AND b.organisatie = ".$row['organisatie'].' AND a.iteratie = '.$iteratie_id.' GROUP BY(a.status)';
            	$sub_result = sqlSelect($what, $from, $where);
        }
        //zet per (ingevulde) status de uren in een variabele.
        while($sub_row = mysql_fetch_array($sub_result)){
            $status = $sub_row['status'];
            $uren_status["$status"] = $sub_row['uren'];
        }
        unset($totaal);
                    
        //is een status leeg, geef dan 0 terug
        if($uren_status['to do'] == null){$todo = 0; $todo_totaal = $todo_totaal + 0;}
        else{$todo = $uren_status['to do']; $todo_totaal = $todo_totaal + $uren_status['to do'];}
                    
        if($uren_status['onderhanden'] == null){$onderhanden = 0; $onderhanden_totaal = $onderhanden_totaal + 0;}
        else{$onderhanden = $uren_status['onderhanden']; $onderhanden_totaal = $onderhanden_totaal + $uren_status['onderhanden'];}
                    
        if($uren_status['acceptatie'] == null){$acceptatie = 0; $acceptatie_totaal = $acceptatie_totaal + 0;}
        else{$acceptatie = $uren_status['acceptatie']; $acceptatie_totaal = $acceptatie_totaal + $uren_status['acceptatie'];}
                    
        if($uren_status['done'] == null){$done = 0; $done_totaal = $done_totaal + 0;}
        else{$done = $uren_status['done']; $done_totaal = $done_totaal + $uren_status['done'];}
                    
        //bereken het totaal... als som der delen
        $totaal = $todo + $onderhanden + $acceptatie + $done;
                    
        //maak de variabelen leeg, zodat ze niet opnieuw gebruikt kunnen worden. Anders komen ze terug bij andere regels.
        unset($uren_status['to do']); unset($uren_status['onderhanden']);
        unset($uren_status['done']); unset($uren_status['acceptatie']);
                    
        echo '
            <div class="organisatie_row">
                <div class="organisatie">'.$row['naam'].'</div>
                <div class="todo">'.$todo.'</div>
                <div class="onderhanden">'.$onderhanden.'</div>
                <div class="acceptatie">'.$acceptatie.'</div>
                <div class="done">'.$done.'</div>
                <div class="totaal">'.$totaal.'</div>
            </div>';
        $grand_totaal = $grand_totaal + $totaal;
    }
    ?>
    </div>
    <div id="iteratie_footer">
                <div id="iteratie_naam">Einddatum <?php echo $iteratie['datum']; ?></div>
                <div id="totaal_todo"><?php echo $todo_totaal  ?></div>
                <div id="totaal_onderhanden"><?php echo $onderhanden_totaal ?></div>
                <div id="totaal_acceptatie"><?php echo $acceptatie_totaal ?></div>
                <div id="totaal_done"><?php echo $done_totaal  ?></div>
                <div id="grand_totaal"><?php echo $grand_totaal  ?></div>
    </div>
            