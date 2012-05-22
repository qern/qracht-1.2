<div id="crm">    
<div id="zoeken_container">
<div id="zoek_relatie">
<h2>Zoek een relatie</h2>
    <form  action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
                    <input type="hidden" name="function" value="crm" />
                    <input type="hidden" name="page" value="zoekresultaat" />
                    <input type="hidden" name="zoek_view" value="relaties" />
                <div id="relatienaam_regel"><a href="#" class="tooltip" title="Zoeken op voor- en/of achternaam"><label for="zoek_naam">voor- en/of achternaam:</label></a>
                    <input type="text" name="naam" id="zoek_naam" class="textfield" /> </div> 
                
                <div id="relatieemail_regel"><a href="#" class="tooltip" title="Zoeken op de persoonlijke email"><label for="zoek_email">Email:</label></a>
                    <input type="text" name="email" id="zoek_email" class="textfield" /> </div> 
                
                <div id="woonplaats_regel"><a href="#" class="tooltip" title="Zoeken op de woonplaats"><label for="zoek_plaats">Woonplaats:</label></a>
                    <input type="text" name="plaats" id="zoek_plaats" class="textfield" /> </div>
                
                <input type="submit" value="zoeken" class="button zoeken" onmouseout="this.className='button zoeken'" onmouseover="this.className='button btn_hover zoeken'" />
    </form>
</div>
<div id="zoek_organisatie">
<h2>Zoek een organisatie</h2>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
                    <input type="hidden" name="function" value="crm" />
                    <input type="hidden" name="page" value="zoekresultaat" />
                    <input type="hidden" name="zoek_view" value="organisaties" />
                <div id="bedrijfsnaam_regel"><a href="#" class="tooltip" title="Zoeken op de organisatienaam"><label for="zoek_naam">Organisatienaam:</label></a>
                    <input type="text" name="naam" id="zoek_naam" class="textfield" /> </div> 
                
                <div id="bedrijfsemail_regel"><a href="#" class="tooltip" title="Zoeken op het emailadres van de organisatie"><label for="zoek_email">Email:</label></a>
                    <input type="text" name="email" id="zoek_email" class="textfield" /> </div> 
                
                <div id="contactpersoon_regel"><a href="#" class="tooltip" title="Zoeken op de geregistreerde contactpersoon van de organisatie"><label for="zoek_contactpersoon">Contactpersoon:</label></a>
                    <input type="text" name="contactpersoon" id="zoek_contactpersoon" class="textfield" /> </div>
                
                <div id="standplaats_regel"><a href="#" class="tooltip" title="Zoek op de standplaats van de organisatie"><label for="zoek_plaats">Standplaats:</label></a>
                    <input type="text" name="plaats" id="zoek_plaats" class="textfield" /> </div>
                
                <div id="branche_regel"><a href="#" class="tooltip" title="Zoek op de specifieke branche van de organisatie"><label for="zoek_provincie">Branche:</label></a>
                    <select name="branche" class="textfield" id="zoek_branche">
                    <option value="" selected="selected">Onbekend</option>
                    <?php 
                        $what = 'id, naam'; $from = 'branche'; $where = 'actief = 1';
                        $result = sqlSelect($what, $from, $where);
                        while($branche = mysql_fetch_array($result)){
                            echo '<option value="'.$branche['id'].'">'.$branche['naam'].'</option>';
                        }
                    ?>
                    </select>
                </div>
                
                    <input type="submit" value="zoeken" class="button zoeken" onmouseout="this.className='button zoeken'" onmouseover="this.className='button btn_hover zoeken'" />
    </form>
</div>
</div>
</div>
