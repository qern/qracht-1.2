<script></script>
<h2>Bekijk hier de uren per organisatie van de afgelopen maand</h2>
<div id="archief">
	<div id="overzicht_titel">
		<?php
		    $maand_nummer = strftime('%m'); $maand_naam = $maandnaam["$maand_nummer"];
		    $jaar = strftime('%Y'); 
		    
		    //Vorige maand bepalen
		    if ($maand_nummer == 1) {  $terug = 12;        $jaar_terug = $jaar-1;  }
		    else{               $terug = $maand_nummer-1;  $jaar_terug = $jaar;    }
		    //Volgende maand bepalen
		    if ($maand_nummer == 12){  $verder = 1;        $jaar_verder = $jaar+1; }
		    else{               $verder = $maand_nummer+1; $jaar_verder = $jaar;   } 
		?>
	    <a id="overzicht_terug" href="#" onclick="archief('<?php echo $terug.'_'.$jaar; ?>',  'overzicht')"> &laquo;&laquo; </a>
	    <h2 id="datum_naam"> <?php echo strftime('%B').'&nbsp;'.$jaar; ?> </h2>
	    <a id="overzicht_verder" href="#" onclick="archief('<?php echo $verder.'_'.$jaar; ?>', 'overzicht')"> &raquo;&raquo; </a>
	</div>
	<div id="overzicht_container">
	    <div id="overzicht_headers">
	        <span id="bedrijfsnaam_header">Klant</span>
	        <span id="aantal_uur_header">Uur</span>
	    </div>
	
	    <div id="overzicht_rijen">
	    <?php
		$maand_nummer = strftime('%m'); $maand_naam = $maandnaam["$maand_nummer"];
	            $jaar = strftime('%Y'); 
		$what = "d.naam,  sum(b.aantal_uur) uren";
		$from = "urentool_datum a, urentool_registratie b, organisatie d";
		$where="a.id = b.datum_id   AND d.id = b.organisatie
		    AND a.maand = $maand_nummer AND a.jaar = $jaar GROUP BY 1";
		$result = sqlSelect($what,$from,$where);
		while($row = mysql_fetch_array($result)){
			?>
		    <div class="overzicht_item">
	            <div class="bedrijfsnaam"><?php echo $row['naam'] ?></div>
	            <div class="aantal_uur"><?php echo $row['uren'] ?></div>
	    	</div>    
		<?php } ?>
	    </div>
	</div>
</div>
