<div id="dashboard_menu">
    <div id="filter_prioriteit">
        <img class="active_prio" src="/functions/<?php echo $_GET['function'] ?>/css/images/remove_prio_24px.png" alt="verwijder filter op prioriteit" data-prio="0" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_1_24px.png" alt="filteren op prio 1" data-prio="1" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_2_24px.png" alt="filteren op prio 2" data-prio="2" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_3_24px.png" alt="filteren op prio 3" data-prio="3" /> 
        <img src="/functions/<?php echo $_GET['function'] ?>/css/images/prio_4_24px.png" alt="filteren op prio 4" data-prio="4" /> 
    </div>
    <div id="filters">
	    <div id="organisatie_placeholder">
	        <label for="organisatie_field">Welke organisatie(s)</label>
	        <input type="text" id="organisatie_field" class="textfield" />
	        
	        <div id="organisaties">
	        </div>
	        
	    </div>        
	    
	    <div id="projecten_placeholder">
	        <label for="project_field">Welk(e) project(en)</label>
	        <input type="text" id="project_field" class="textfield" />
	        
	        <div id="projecten">
	        </div>
	        
	    </div>
	   
	     <div id="competentie_placeholder">
	        <label for="competentie_field">Welk(e) competentie(s)</label>
	        <input type="text" id="competentie_field" class="textfield" />
	        
	        <div id="competenties">
	        </div>
	        
	    </div>
	    
	    <div id="medewerker_placeholder">
	        <label for="medewerker_field">Welk(e) medewerker(s)</label>
	        <input type="text" id="medewerker_field" class="textfield" />
	        
	        <div id="medewerkers">
	        </div>
	        
	    </div>
	    
	    <div id="vantot_datum">
	        
	        <p>Datum</p>
	        
	        <div id="datum_van">
	            <label for="van_datum">van</label>
	            <input type="text" class="textfield" id="van_datum" />
	        </div> 
	        
	        <div id="datum_tot">
	            <label for="tot_datum">tot</label>
	            <input type="text" class="textfield" id="tot_datum" />
	        </div> 
	        
	    </div>
	
	    <div id="uren_zoekwoord">
	        
	        <p>Aantal uur</p>
	        
	        <div id="uren_min">
	            <label for="min_uren">min</label>
	            <input type="text" class="textfield" id="min_uren" />
	        </div> 
	        
	        <div id="uren_max">
	            <label for="max_uren">max</label>
	            <input type="text" class="textfield" id="max_uren" />
	        </div> 
	        
	    </div>
	
	    <div id="zoekwoord_placeholder">
	        
	        <label for="zoekwoord_field">zoekwoord</label> 
	        <input type="text" class="textfield" id="zoekwoord_field" name="zoekwoord" />
	        
	        <div id="zoekwoorden">
	        </div>
	        
	    </div>
	
	    <div id="filteren">
	        <button id="filters_versturen" class="button" title="test">filteren</button>
	    </div>
	
	</div>
</div>
<div id="planning_main">
	
       <div id="hierzo">
           <div id="loading_img">
       	    <img alt="Lijst wordt geladen" src="/images/ajax_loader.gif">&nbsp;
           </div>
       </div>
       
</div>