<?php 
//is er een id gezet, ofwel een specifieke pagina ? Zo nee, laad dan  '1'
//als de bezoeker geen admin is, laat dan alleen mededelingen zien die horen bij het bedrijf.
$what = 'id, titel, DATE_FORMAT(publicatiedatum, \'%e %b %Y\') publicatie'; $from='portal_nieuws'; 
$where='actief = 1 ORDER BY publicatiedatum ASC';
$count = countRows($what, $from, $where);

?>
<script>
function getdata(pageno, order, dir){
    $('#nieuws_overzicht').html( '' );
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/zoek_resultaten.php',
            dataType : 'html',
            data:{page : pageno, order: order, direction: dir},
            success : function(data){ $('#nieuws_overzicht').html( data ).hide().fadeIn('slow');}
        });
        return false;
}
jQuery(function(){
	getdata(1, 'publicatiedatum', 'DESC');
	jQuery('#nieuws_headers div.sortable').on('click', function(){
		var order = jQuery(this).attr('data-column'), dir = jQuery(this).attr('data-direction');
		if(dir === 'ASC'){jQuery(this).attr('data-direction', 'DESC');}
		else if(dir === 'DESC'){jQuery(this).attr('data-direction', 'ASC');}
		getdata(1, order, dir);
	});
});
function veranderBelangrijk(nieuws, belangrijk){
    $.ajax({
            type : 'GET',
            url : '<?php echo $etc_root; ?>functions/publicaties/includes/overzicht_ajax.php',
            dataType : 'html',
            data:{action: 'veranderBelangrijkheid', nieuws_id : nieuws, belangrijk : belangrijk},
            success : function(data){ $('#nieuws_' + nieuws).toggleClass('belangrijk').html(data);}
        });
        return false;
}
</script>
<div id="nieuws_overzichtpagina">
    <div id="overzicht_header"><h2>Bekijk hier alle publicaties</h2> <a class="btn_voeg_nieuws_toe" href="<?php echo $etc_root; ?>publicaties/toevoegen" title="voeg publicatie toe">Voeg publicatie toe</a></div>
    <div id="nieuwsoverzicht">
        <div id="nieuws_headers">
            <div class="sortable" id="is_belangrijk_header" data-column="is_belangrijk" data-direction="ASC">Prio</div>
            <div class="sortable" id="titel_header" data-column="titel" data-direction="ASC">Titel</div>
            <div class="sortable" id="publicatie_header" data-column="publicatiedatum" data-direction="ASC">Publicatie</div>
            <div id="bedoeld_voor_header">Bedoeld voor</div>
            <div id="bestanden_header">Bestanden</div>
            <div id="foto_header">Foto's</div>
            <div id="reactie_header">Reacties</div>
            <div class="sortable" id="laatste_wijziging_header" data-column="laatste_wijziging" data-direction="ASC">Laatste wijziging</div>
        </div>
        <div id="nieuws_overzicht">
        <img src="<?php echo $etc_root; ?>images/loading.gif" alt="loading" />
        </div>
    </div>
</div>