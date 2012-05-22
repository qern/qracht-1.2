<?php
    session_start();
    include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    $what = 'iteratie'; $from = 'planning_activiteit'; $where = 'id = '.$_GET['id'];
        $activiteit = mysql_fetch_assoc(sqlSelect($what, $from, $where));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Prioriteit Wijzigen</title>
        <link href="/css/standaard_css/main.css" rel="stylesheet" type="text/css" />
        <link href="/functions/planning/css/planning.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
          <div id="prioriteit">
                    <p style="font-weight:700;">Wat voor prioriteit wilt u geven aan deze activiteit ?</p>
<?php
$active_prioriteit = 'class="active_prio"';
$inactive_prioriteit = 'class="inactive_prio" onmouseover="this.className='."'hover_prio'".'" onmouseout="this.className='."'inactive_prio'".'"';
if($activiteit['prioriteit'] == '1'){$prioriteit_1 = $active_prioriteit;}else{$prioriteit_1 = $inactive_prioriteit;}
if($activiteit['prioriteit'] == '2'){$prioriteit_2 = $active_prioriteit;}else{$prioriteit_2 = $inactive_prioriteit;}
if($activiteit['prioriteit'] == '3'){$prioriteit_3 = $active_prioriteit;}else{$prioriteit_3 = $inactive_prioriteit;}
if($activiteit['prioriteit'] == '4'){$prioriteit_4 = $active_prioriteit;}else{$prioriteit_4 = $inactive_prioriteit;}
?>                  
                    <div id="callback"></div>
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
                </div>
    <script>
        (function(){
            jQuery('#image_rij img').on('click', function(){
               var prio = jQuery(this).attr('alt').split(' ')[1];
               jQuery.ajax({type : 'GET',url : '/functions/planning/includes/prio_ajax.php',dataType : 'html',data: {action: 'prioWijzigen', activiteit_id : '<?php echo $_GET['id']; ?>', prioriteit : prio},
                    success : function(data){
                        jQuery('#callback').html(data);
                        reloadPlanning('<?php echo $_GET['kolom'] ?>', '<?php echo $activiteit['iteratie'] ?>');
                    }
               });return false;
            })
        })();
        //;
    </script>
    </body>
</html>