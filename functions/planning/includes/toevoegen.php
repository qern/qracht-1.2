<?php 
        if($_GET['toevoegen'] == 'activiteit'){$activiteit = ' active_menu'; $title= 'Activiteit toevoegen';}
        elseif($_GET['toevoegen'] == 'project'){$project =  ' active_menu'; $title= 'project toevoegen';}
        else{$activiteit = ' active_menu';}
?>


<div id="toevoegen_menu">
    <span id="sprite_info_item" class="menuitem<?php echo $activiteit ?>">  <a href="/planning/activiteit-toevoegen/"><h3>Activiteit toevoegen</h3></a></span>
    <span id="foto_item" class="menuitem<?php echo $project ?>">            <a href="/planning/project-toevoegen/"><h3>Project toevoegen</h3></a></span>
    
</div>
<?php if($_GET['toevoegen'] == 'activiteit'){ ?>
<div id="activiteit_toevoegen">
	<div id="activiteit_toevoegen_core">
		<div id="activiteit_toevoegen_titel"><h2>Activiteit toevoegen</h2></div>
        
        <div id="error_container">
            <h2 id="error_titel"></h2>
            <ul id="errors">
                <?php 
                if(isset($_SESSION['error'])){
                //geef per fout aan...wat er fout is.
                foreach($_SESSION['error'] as $error){?>
                    
                <li><?php echo $error ?></li>
                
                <?php }
                    if($klant != null){?>
                    <script>refreshProjectPosition(<?php echo $klant; ?>)</script>
                    <?php }
                }
                ?>
            </ul>
        </div>
        
	    <form id="activiteit_toevoegen_form" name="activiteit_toevoegen_form" action="/functions/planning/includes/check.php" method="post">
		<div id="activiteit_input">
    		<div id="klant_toevoegen">
            	<label for="toevoegen_klant">Klant</label>
            	<input tabindex="1" class="textfield" type="text" id="toevoegen_klant" name="toevoegen_klant" />
            	
            	
            	<div id="project_select">
            		<select id="toevoegen_project" name="toevoegen_project" disabled="disabled" class="textfield required">
            			<option value>Kies eerst een klant</option>
            		</select>
            	</div>
        	</div>
        
        	<div id="werkzaamheden_toevoegen">
            	<label for="toevoegen_werkzaamheden">Korte beschrijving werkzaamheden</label>
            	<textarea tabindex="2" class="textarea" id="toevoegen_werkzaamheden" name="toevoegen_werkzaamheden" cols="40" rows="4"></textarea>
        	</div>
        
        	<div id="uur_aantal_toevoegen">
            	<label for="toevoegen_uur">Uur</label>
            	<input tabindex="3" class="textfield" type="text" id="toevoegen_uur" name="toevoegen_uur" />
            </div>
            <div id="competentie_select">
            	<select id="toevoegen_competentie" name="toevoegen_competentie" class="textfield required">
            		<option value>Kies een competentie</option>
            		<?php
            			$what = 'id, competentie AS naam'; $from = 'competentie'; $where = '1';
               				$competenties = sqlSelect($what, $from, $where);
					
						while($competentie = mysql_fetch_array($competenties)){echo '<option value="'.$competentie['id'].'">'.$competentie['naam'].'</option>';}
            		?>
            	</select>
        	</div>
        	<input type="hidden" name="action" value="activiteit_toevoegen" />
        	<input type="submit" value="opslaan" id="toevoegen_opslaan" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'" />
        </form>
        </div>
    </div>
</div>
<?php
    //unset alle gegevens
    unset($_SESSION['error']);
    $klant =  null; $werkzaamheden =  null; $uur_aantal =  null;
 }// einde activiteit toevoegen
elseif($_GET['toevoegen'] == 'project'){
    if($_GET['organisatie'] != null){//als er een organisatie is meegegeven, haal de naam daarvan op.
        $what = 'naam'; $from = 'organisatie'; $where = 'id = '.$_GET['organisatie'];
            $organisatie = mysql_fetch_assoc(sqlSelect($what, $from, $where));
	}
?>
<div id="project_toevoegen">
    
    <div id="project_toevoegen_core">
        <div id="project_toevoegen_titel"><h2>Project toevoegen</h2></div>
        
        <div id="error_container">
            <h2 id="error_titel"></h2>
            <ul id="errors">
                <?php 
                if(isset($_SESSION['error'])){
                //geef per fout aan...wat er fout is.
                foreach($_SESSION['error'] as $error){?>
                    
                <li><?php echo $error ?></li>
                
                <?php }
                }
                ?>
            </ul>
        </div>
        
        <form id="project_toevoegen_form" name="activiteit_toevoegen_form" action="/functions/planning/includes/check.php" method="post">
            
            <div id="project_toevoegen_links">
                <div id="klant_toevoegen">
                    <label for="toevoegen_klant">Klant</label>
                    <input tabindex="1" class="textfield" type="text" id="toevoegen_klant" name="toevoegen_klant"  />
                </div>
                
                <div id="titel_toevoegen">
                    <label for="toevoegen_titel">Titel</label>
                    <input tabindex="2" class="textfield" type="text" id="toevoegen_titel" name="toevoegen_titel"  />
                </div>
            </div>
            
            <div id="beschrijving_toevoegen">
                <label for="toevoegen_beschrijving">Korte beschrijving project</label>
                <textarea tabindex="3" class="textarea" id="toevoegen_beschrijving" name="toevoegen_beschrijving" cols="40" rows="4"></textarea>
            </div>
            
        	<div id="project_toevoegen_rechts">
        		<div id="begindatum_toevoegen">
		        	<label for="toevoegen_begindatum">Startdatum</label>
	            	<input tabindex="4" class="textfield" type="text" id="toevoegen_begindatum" name="toevoegen_begindatum" />
            	</div>
            	<div id="einddatum_toevoegen">
		        	<label for="toevoegen_einddatum">Einddatum</label>
	            	<input tabindex="5" class="textfield" type="text" id="toevoegen_einddatum" name="toevoegen_einddatum" />
            	</div>
            </div>
            
            <input type="hidden" name="action" value="project_toevoegen" />
            <input type="submit" value="opslaan" id="toevoegen_opslaan" class="button" onmouseover="this.className = 'button btn_hover'" onmouseout="this.className = 'button'" />
        </form>
    </div>
</div>
<?php 
    //unset alle gegevens
    unset($_SESSION['error']);
    $klant =  null; $titel =  null; $beschrijving =  null;
}//einde project toevoegen ?>