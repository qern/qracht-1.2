<?php
session_start();
require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');

if($_GET['action'] == 'prioWijzigen'){
    
    $table = 'planning_activiteit'; $what = 'prioriteit = '.$_GET['prioriteit']; $where= 'id = '.$_GET['activiteit_id'];
        $update_prio = sqlUpdate($table, $what, $where);
        
    if($_GET['reload'] != null){?>
        <p style="font-weight:700;">Wat voor prioriteit wilt u geven aan deze activiteit ?</p>
<?php
$active_prioriteit = 'class="active_prio"';
$inactive_prioriteit = 'class="inactive_prio" onmouseover="this.className='."'hover_prio'".'" onmouseout="this.className='."'inactive_prio'".'"';
if($_GET['prioriteit'] == '1'){$prioriteit_1 = $active_prioriteit;}else{$prioriteit_1 = $inactive_prioriteit;}
if($_GET['prioriteit'] == '2'){$prioriteit_2 = $active_prioriteit;}else{$prioriteit_2 = $inactive_prioriteit;}
if($_GET['prioriteit'] == '3'){$prioriteit_3 = $active_prioriteit;}else{$prioriteit_3 = $inactive_prioriteit;}
if($_GET['prioriteit'] == '4'){$prioriteit_4 = $active_prioriteit;}else{$prioriteit_4 = $inactive_prioriteit;}
?>                  
                    <div id="image_rij">
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_1.png" alt="prio 1" <?php echo $prioriteit_1; ?> />
                        </div>
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_2.png" alt="prio 2" <?php echo $prioriteit_2; ?> />                            
                        </div>
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_3.png" alt="prio 3" <?php echo $prioriteit_3; ?> />
                        </div>
                        <div class="prio_image">
                            <img src="/functions/planning/css/images/prio_4.png" alt="prio 4" <?php echo $prioriteit_4; ?> />
                        </div>
                    </div>
                    <div id="beschrijving_rij">
                        <div class="prio_beschrijving"> <b>Hoogste prioriteit</b><br /> Direct een blokkerend probleem. </div>
                        <div class="prio_beschrijving"> <b>Hoge prioriteit</b><br /> Op korte termijn een blokkerend probleem als we nu niets doen. </div>
                        <div class="prio_beschrijving"> <b>Normale prioriteit</b><br /> Belangrijk maar niet blokkerend. </div>
                        <div class="prio_beschrijving"> <b>Lage prioriteit</b><br /> Niet blokkerend, cosmetisch of gewoon mooi. </div>                
                    </div>
    <?php }else{?>U hebt de prioriteit gewijzigd <script>jQuery(function(){setTimeout("$('#dialog').dialog('close')",3000);})</script><?php }
}

?>