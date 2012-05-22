<div id="periode_planning" data-project="">
    <div id="periode_planning_links">
        <div id="periode_org_proj">
            <div id="periode_org_input">
                <label for="periode_org">Organisatie</label>
                <input type="text" id="periode_org" class="textfield" />
            </div>
            <div id="periode_proj_input">
                <select id="periode_proj" class="textfield" disabled="disabled">
                    <option value>Kies eerst een organisatie</option>
                </select>
            </div>
        </div>
        <div id="periode_iteraties">
            <?php 
                $what = "id, datum d2, DATE_FORMAT(datum, '%d %M %Y') AS datum";  $from= "planning_iteratie";  $where="actief = 1 ORDER BY d2 ASC";
                    $iteraties = sqlSelect($what, $from, $where);
                    
                while($iteratie = mysql_fetch_array($iteraties)){?>
            <div class="periode_iteratie" data-iteratie="<?php echo $iteratie['id']; ?>">
                <div class="periode_iteratie_datum"><?php echo $iteratie['datum']; ?></div>
            </div>
            <br />
            <?php } ?>
        </div>
    </div>
    <div id="periode_planning_rechts"></div>
</div>
