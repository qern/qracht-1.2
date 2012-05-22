<?php session_start();
    require($_SERVER['DOCUMENT_ROOT']."/check_configuration.php");
    require($_SERVER['DOCUMENT_ROOT']."/lib/phpmailer/class.phpmailer.php");
    $iteratie_id = $_GET['id']; 
    //$_SESSION['todo'] = '<h2>De query : </h2>';
    if($_REQUEST['todo'] != null){
        parse_str($_REQUEST['todo'], $sort_todo);
        foreach($sort_todo['activiteit'] as $volgorde => $id){
            list($activiteit_id, $bron) = explode('x', $id);
            $table="planning_activiteit";
            $what = "status = 'to do', iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = 0, acceptatie_door = 0 ";
            $where = "id = '$activiteit_id' AND iteratie = '$iteratie_id' ";
            $update_planning = sqlUpdate($table, $what, $where);
            if($bron != 'todo'){ $todo = $bron; recentMaken($activiteit_id, $login_id); }
    }
    if($todo){echo 'todo,'.$todo;}
    }
    if($_REQUEST['onderhanden']  != null){
        parse_str($_REQUEST['onderhanden'], $sort_onderhanden);
        
        foreach($sort_onderhanden['activiteit'] as $volgorde => $id){
            list($activiteit_id, $bron) = explode('x', $id);
            $table="planning_activiteit";
            if($bron != 'onderhanden'){
                $onderhanden = $bron;  recentMaken($activiteit_id, $login_id);
                $what_onderhanden = "status = 'onderhanden', iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = $login_id, acceptatie_door = 0 ";
             }else{
                $what_onderhanden = "status = 'onderhanden', iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), acceptatie_door = 0 ";
            }
            $where = "id = '$activiteit_id' AND iteratie = '$iteratie_id' ";
            $update_planning = sqlUpdate($table, $what_onderhanden, $where);
        }
        if($onderhanden){echo 'onderhanden,'.$onderhanden;}
    }
    if($_REQUEST['acceptatie']  != null){
        parse_str($_REQUEST['acceptatie'], $sort_acceptatie);
        foreach($sort_acceptatie['activiteit'] as $volgorde => $id){
            list($activiteit_id, $bron) = explode('x', $id);
            $table="planning_activiteit";
            if($bron != 'acceptatie'){
                $acceptatie = $bron;  recentMaken($activiteit_id, $login_id);
                $what_acceptatie = "status = 'acceptatie', iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW(), in_behandeling_door = $login_id";
            }else{
                $what_acceptatie = "status = 'acceptatie', iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW() ";
            }
            $where = "id = '$activiteit_id' AND iteratie = '$iteratie_id' ";
            $update_planning = sqlUpdate($table, $what_acceptatie, $where);
        }
        if($acceptatie){echo 'acceptatie,'.$acceptatie;}
    }
    if($_REQUEST['done']  != null){
        parse_str($_REQUEST['done'], $sort_done);

        foreach($sort_done['activiteit'] as $volgorde => $id){
            list($activiteit_id, $bron) = explode('x', $id);
            $table="planning_activiteit";
            if($bron != 'done'){
                $done = $bron;  recentMaken($activiteit_id, $login_id);
                $what_done = "status = 'done', iteratie_volgorde = $volgorde, status_datum = NOW(), gewijzigd_op = NOW() ";
                
            }else{
                $what_done = "status = 'done', iteratie_volgorde = $volgorde, gewijzigd_op = NOW() ";
            }
            $where = "id = '$activiteit_id' AND iteratie = '$iteratie_id' ";
            $update_planning = sqlUpdate($table, $what_done, $where);
            
        }
        if($done){echo 'done,'.$done;}
    }
?>