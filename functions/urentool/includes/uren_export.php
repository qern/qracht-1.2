<?php
  require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $sql = "SELECT 
 CONCAT (gebr.voornaam, ' ', gebr.achternaam) naam,
 dat.datum,
 org.naam organisatie,
 prj.titel project,
 act.werkzaamheden geplande_activiteit,
 comp.competentie,
 reg.werkzaamheden,
 reg.aantal_uur
FROM
 portal_gebruiker gebr,
 urentool_datum dat,
 project prj,
 planning_activiteit act,
 competentie comp,
 urentool_registratie reg,
 organisatie org
WHERE 
 gebr.id = dat.gebruiker
 AND dat.id = reg.datum_id
 AND reg.organisatie = org.id 
 AND reg.activiteit = act.id
 AND act.project = prj.id
 AND reg.competentie = comp.id
-- NU ALLE UREN DIE NIET GEKOPPELD ZIJN AAN EEN ACTIVITEIT
UNION
SELECT 
 CONCAT (gebr.voornaam, ' ', gebr.achternaam) naam,
 dat.datum,
 org.naam organisatie,
 '' project,
 '' geplande_activiteit,
 comp.competentie,
 reg.werkzaamheden,
 reg.aantal_uur
FROM
 portal_gebruiker gebr,
 urentool_datum dat,
 competentie comp,
 urentool_registratie reg,
 organisatie org
WHERE 
 gebr.id = dat.gebruiker
 AND dat.id = reg.datum_id
 AND reg.competentie = comp.id
 AND reg.organisatie = org.id
 AND reg.activiteit NOT IN (SELECT act.id FROM planning_activiteit act WHERE act.actief = 1 AND act.geldig = 1)
ORDER BY 1,2,3";
    $rec = mysql_query($sql) or die (mysql_error());
   
    $num_fields = mysql_num_fields($rec);
   
    for($i = 0; $i < $num_fields; $i++ )
    {
        $header .= ucfirst(mysql_field_name($rec,$i))."\t";
    }
   
    while($row = mysql_fetch_row($rec))
    {
        $line = '';
        foreach($row as $value)
        {                                           
            if((!isset($value)) || ($value == ""))
            {
                $value = "\t";
            }
            else
            {
                $value = str_replace( '"' , '""' , $value );
                $value = '"' . $value . '"' . "\t";
            }
            $line .= $value;
        }
        $data .= trim( $line ) . "\n";
    }
   
    $data = str_replace("\r" , "" , $data);
   
    if ($data == "")
    {
        $data = "\n No Record Found!n";                       
    }
   
    $datum = strftime('%d-%m-%Y');
    header("Content-Disposition: attachment; filename=Urentool-export-$datum.xls");
    header("Content-type: application/vnd.ms-excel");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";

?>
