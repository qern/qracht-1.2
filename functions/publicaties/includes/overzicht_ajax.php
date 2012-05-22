<?php
    session_start();
    require ($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
    if($_GET['action'] == 'veranderBelangrijkheid'){
        $table = 'portal_nieuws'; $what = 'is_belangrijk = '.$_GET['belangrijk'].', laatste_wijziging = NOW(), gewijzigd_door = '.$login_id;
        $where = 'id = '.$_GET['nieuws_id'];
            $update_nieuws = sqlUpdate($table, $what, $where);
            
        if($_GET['belangrijk'] == '1'){?>
        <img src="<?php echo $etc_root; ?>functions/nieuws/css/images/belangrijk.png" alt="hoge prioriteit" title="hoge prioriteit" onclick="veranderBelangrijk('<?php echo $_GET['nieuws_id'] ?>', 0)" />
        <?php }else{?>
        <img src="<?php echo $etc_root; ?>functions/nieuws/css/images/onbelangrijk.png" alt="lage prioriteit" title="lage prioriteit" onclick="veranderBelangrijk('<?php echo $_GET['nieuws_id'] ?>', 1)" />
        <?php }
    }
?>