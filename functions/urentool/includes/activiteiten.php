 <?php
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $what ="b.id, b.werkzaamheden"; 
    $from = "organisatie a
             LEFT JOIN planning_activiteit AS b ON ( b.organisatie_id = a.id)
             LEFT JOIN planning_iteratie AS c ON (c.id = b.planning_iteratie_id)";
    $where="a.naam LIKE '".$_GET['q']."' AND (b.status != 'nog te plannen' AND b.status != 'done') AND c.actief = 1 AND c.huidige_iteratie = 1";
    $aantal_werkaamheden = countRows($what, $from, $where);
    // als je nog niet klaar bent met typen... laat dan ook onderstaand niet zien !
    if($aantal_werkaamheden > 0){
        $result = sqlSelect($what, $from, $where);
        echo '<label>Activiteit:</label>
              <select name="activiteit" class="textfield">';
        while($row = mysql_fetch_array($result)){
            echo '<option value="'.$row['id'].'">'.$row['werkzaamheden'].'</option>';
        }
        echo '<option value="0" selected="selected">Anders...</option>        
        </select>';
    }else{
        echo '<label>Activiteit:</label>
              <select name="activiteit" disabled="disabled" class="textfield">
                <option value="">Vul eerst bedrijf in.</option>
              </select>';
    }
?>