<?php
    if($functie_get == 'home'){
        $met_submenu = '';
        $submenu = '&nbsp;';
        $info_titel = 'Home';
        $info_subtitel = '&nbsp;';
    }
    elseif($functie_get == 'urentool'){
        $met_submenu = 'submenu_present';
        $submenu = '
        <center>
            <table cellpadding="0" cellspacing="0" border="0">
                <td>
                    
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item"><div class="img_icon"><img src="/images/add_event.png" alt="invoer" /></div> <a href="/urentool/">Invoer</a></div>
                    </div>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item last"><div class="img_icon"><img src="/images/planning_rapportage.png" alt="Rapportage" /></div> <a href="/urentool/rapportage">Rapportage</a></div>
                    </div>
                </td>
            </table>
        </center>';
        $info_titel = 'Urentool';
        if($_GET['page']){
        	
        }else{
            $info_subtitel = '&nbsp;';
        }
    }
    elseif($functie_get == 'crm'){
		$met_submenu = '';
        $submenu = '&nbsp;';
        $info_titel = 'CRM';
        $info_subtitel = '&nbsp;';
    }
    elseif($functie_get == 'planning'){
        $met_submenu = 'submenu_present';
        $submenu = '
        <center>
            <table cellpadding="0" cellspacing="0" border="0">
                <td>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_header"><div class="img_icon"><img src="/images/planning_dashboard.png" alt="Dashboard" /></div> <a href="/planning/dashboard">Dashboard</a></div>
                    </div>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item"><div class="img_icon"><img src="/images/add_event.png" alt="Activiteit toevoegen" /></div> <a href="/planning/activiteit-toevoegen">Toevoegen</a></div>
                    </div>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item"><div class="img_icon"><img src="/images/iteraties.png" alt="Iteraties" /></div> <a href="/planning/iteraties">Iteraties</a></div>
                    </div>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item"><div class="img_icon"><img src="/images/planning_nogteplannen.png" alt="Nog te plannen" /></div> <a href="/planning/nog-te-plannen">Nog te plannen</a></div>
                    </div>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item"><div class="img_icon"><img src="/images/planning_archief.png" alt="Archief" /></div> <a href="/planning/archief">Archief</a></div>
                    </div>
                    <div class="wrapper_submenu_header">
                        <div class="submenu_item last"><div class="img_icon"><img src="/images/planning_rapportage.png" alt="Rapportage" /></div> <a href="/planning/rapportage">Rapportage</a></div>
                    </div>
                </td>
            </table>
        </center>';
        $info_titel = 'Planning';
        if($_GET['page']){
            if($_GET['page'] == 'dashboard'){$info_subtitel = 'Dashboard';}
            elseif($_GET['page'] == 'detail'){
                if($_GET['detail'] == 'activiteit'){ $info_subtitel = 'Activiteit detail'; }                    
                elseif($_GET['detail'] == 'project'){ $info_subtitel = 'Project detail'; }                    
               
            }
            elseif($_GET['page'] == 'iteraties_bewerken'){$info_subtitel = 'Nog te plannen';}
            elseif($_GET['page'] == 'archief'){$info_subtitel = 'Archief';}
            elseif($_GET['page'] == 'iteraties'){$info_subtitel = 'Iteraties';}
            elseif($_GET['page'] == 'activiteit-toevoegen'){$info_subtitel = 'Activiteit toevoegen';}
            elseif($_GET['page'] == 'project-toevoegen'){$info_subtitel = 'Activiteit toevoegen';}
        }else{
            $info_subtitel = 'Dashboard';
        }
    }
    elseif($functie_get == 'profiel'){
        $met_submenu = '';
        $submenu = null;
        $info_titel = 'Profiel';
    }
    if($info_subtitel){$titel = $info_titel.' - '.$info_subtitel;}
    else{$titel = $info_titel;}
?>
