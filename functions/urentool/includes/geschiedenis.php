<?php
	session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    list($maand, $jaar) = explode('_', $_GET["q"]); 
	$doel = $_GET['doel'];
	if($doel == 'archief'){
		$what = "d.naam,  sum(b.aantal_uur) uren";
		$from = "urentool_datum a, urentool_registratie b, organisatie d";
		$where="a.id = b.datum_id   AND d.id = b.organisatie
		    	AND a.maand = $maand AND a.jaar = $jaar 
		    	AND a.gebruiker = $login_id GROUP BY 1";

    }elseif($doel == 'overzicht'){
    	$what = "d.naam,  sum(b.aantal_uur) uren";
		$from = "urentool_datum a, urentool_registratie b, organisatie d";
		$where="a.id = b.datum_id   AND d.id = b.organisatie
	    		AND a.maand = $maand AND a.jaar = $jaar GROUP BY 1";
	}
    //echo "SELECT $what FROM $from WHERE $where";
    $count_maand = countRows($what, $from, $where);
	if($maand < 10){ $maand_naam = '0'.$maand;}else{$maand_naam = $maand;}
    $maand_titel = $maandnaam["$maand_naam"].'&nbsp;'.$jaar;
	
	//nodig voor de refresh
	$nu_maand = strftime('%m'); $nu_jaar = strftime('%Y');
    
    //Vorige maand bepalen
    if ($maand == 1) {  $terug = 12;        $jaar_terug = $jaar-1;  }
    else{               $terug = $maand-1;  $jaar_terug = $jaar;    }
    //Volgende maand bepalen
    if ($maand == 12){  $verder = 1;        $jaar_verder = $jaar+1; }
    else{               $verder = $maand+1; $jaar_verder = $jaar;   }?>
    <div id="<?php echo $doel; ?>_titel">
            <?php if($doel == 'archief'){$js_p = 'archief(\''.$terug.'_'.$jaar_terug.'\', \'archief\')'; $js_n = 'archief(\''.$verder.'_'.$jaar_verder.'\', \'archief\')';}
				  elseif($doel == 'overzicht'){$js_p = 'archief(\''.$terug.'_'.$jaar_terug.'\', \'overzicht\')'; $js_n = 'archief(\''.$verder.'_'.$jaar_verder.'\', \'overzicht\')';}?>
            <a id="<?php echo $doel; ?>_terug" href="#" onclick="<?php echo $js_p; ?>"> &laquo;&laquo; </a>
                <h2 id="datum_naam"> <?php echo $maand_titel ?> </h2>
            <div id="archief_nu" style="display:none;"><?php echo $nu_maand.'_'.$jaar; ?></div>
            <a id="<?php echo $doel; ?>_verder" href="#" onclick="<?php echo $js_n; ?>"> &raquo;&raquo; </a>
    </div>
     <div id="<?php echo $doel; ?>_container">
        <div id="<?php echo $doel; ?>_headers">
        <?php if($doel == 'archief'){?>
	        <div id="linker_kolom">
	            <span id="klant_links">Klant</span>
	            <span id="uur_links">Uur</span>
	        </div>
	        <div id="rechter_kolom">
	            <span id="klant_rechts">Klant</span>
	            <span id="uur_rechts">Uur</span>
	        </div>
		<?php }else{?>
			<span id="klant_links">Klant</span>
	        <span id="uur_links">Uur</span>
		<?php } ?>
        </div>
    
        <div id="<?php echo $doel; ?>_rijen">    
        <?php 
if($count_maand > 0){
    //Haal nu alle gegevens op
    $result_maand = sqlSelect($what, $from, $where);
	
    while($row = mysql_fetch_array($result_maand)){
    	if($doel == 'archief'){?>
            <div class="<?php echo $doel; ?>_item">
                <div class="bedrijfsnaam"><?php echo $row['naam'];?></div>
                <div class="aantal_uur"><?php echo $row['uren'];?></div>
            </div>
  <?php }elseif($doel == 'overzicht'){?>
		<div class="<?php echo $doel; ?>_item">
                <div class="bedrijfsnaam"><?php echo $row['naam'];?></div>
                <div class="aantal_uur"><?php echo $row['uren'];?></div>
            </div>
  <?php }
	}//einde while
}else{?>
		<div class="archief_item">
	        <div id="lege_maand">Er zijn gegevens aanwezig van deze maand.</div>
	    </div>
<?php }?>
    </div>
</div>
