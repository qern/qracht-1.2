<?php 
	session_start();
	
	require($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');

	if($_POST['action'] == 'forgotPass'){
	//  is er een e-mail door gestuurd ?
	if($_POST['email'] != null){

		$email = $_POST['email'];
		//  is er iemand bij ons bekend met dat e-mailadres
		$what = 'id, voornaam, achternaam, gebruikersnaam'; $from = 'portal_gebruiker'; $where= "email = '".$email."'";
			$aant_gebruikers = countRows($what, $from, $where);
			
		//als het er 1 is ga verder
		if($aant_gebruikers == 1){
			$gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
			$url = $site_name;
			
			$data = $gebruiker['gebruikersnaam'].' '.$email; // note the spaces
			$encrypted = encrypt($data, $key);
			//$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($data), $key, MCRYPT_MODE_CBC, md5(md5($data))));
			$url .= 'inloggen.php?lock='.$encrypted;
			
			$naam = $gebruiker['voornaam'].' '.$gebruiker['achternaam'];
			$nu = strftime("%d %B %Y");
			
			$subject = 'U wilt uw wachtwoord herstellen';
            $htmlbody = "
            <p>
                Beste $naam,<br />
                Op $nu heeft u een herstel van uw wachtwoord aangevraagd.<br /><br />
            </p>
            <p>
                Om uw wachtwoord te herstellen, dient u naar het onderstaande webadres te gaan.<br />
                Als u er niet op kunt klikken, plak het hele adres dan in de adresbalk in uw internetbrowser.<br />
                U zal gevraagd worden om uw gebruikersnaam en e-mailadres in te voeren. Dit is ter controlle.<br />
                Hierna dient u tweemaal een nieuw wachtwoord in te voeren, waarna deze hersteld is.<br />
                <a href=\"$url\" title=\"herstel wachtwoord\">$url</a>
            </p>
            
            <p>
                Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.<br />
                Wij nemen dan zo snel mogelijk contact met u op.
            </p>
            ";
            
            $textbody = " 
        
            Beste $naam,<br />
            Op $nu heeft u een herstel van uw wachtwoord aangevraagd.
        
            Om uw wachtwoord te herstellen, dient u naar het onderstaande webadres te gaan.
            Als u er niet op kunt klikken, plak het hele adres dan in de adresbalk in uw internetbrowser.
            U zal gevraagd worden om uw gebruikersnaam en e-mailadres in te voeren. Dit is ter controlle.
            Hierna dient u tweemaal een nieuw wachtwoord in te voeren, waarna deze hersteld is.
            
            
            $url
        
            Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
            Wij nemen dan zo snel mogelijk contact met u op.";
            ?>
        <dl>
			<dd>
	        	<p class="login_succes">Wij hebben u een e-mail gestuurd met instructies</p>
	        </dd>
        </dl>    
		<?php
		}
		 // anders: opnieuw het formulier
		else{ ?>
		
		<dl>
			<dd><p class="login_error">Dit e-mailadres is niet bij ons bekend. Probeer het opnieuw</p></dd>
			<dd>
				<p>Voer uw e-mailadres in, wij sturen u een e-mail met daarin instructies om uw wachtwoord te herstellen.</p>
			</dd>
			<dd>
				<label for="email">E-mailadres</label>
				<input type="email" name="email" id="email" value="" tabindex="1" class="input" />
			</dd>
			<dd><button id="btnforgotPass" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Wachtwoord herstellen </button></dd>
		</dl>			
		<?php }
		
	}
	}elseif($_POST['action'] == 'newPass'){
		$email = $_POST['email']; $gebruikersnaam = $_POST['gebruikersnaam'];
		$lock = $_POST['lock'];
		$unlocked = decrypt($lock, $key);
		//echo $unlocked;
		//$unlocked = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($lock), base64_decode($key), MCRYPT_MODE_CBC, md5(md5($lock))), "\0");
		if($unlocked == ($gebruikersnaam.' '.$email)){
			$what = 'id'; $from= 'portal_gebruiker'; $where= "email = '$email' AND gebruikersnaam = '$gebruikersnaam'";
				$gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));	
		?>
		<dl>
			<dd><p class="login_error" id="wachtwoord_fout"></p></dd>
			<dd>
				<p>Voer tweemaal uw nieuwe wachtwoord in.</p>
			</dd>
			<dd>
				<label for="new_wachtwoord">wachtwoord</label>
				<input type="password" name="new_wachtwoord" id="new_wachtwoord" value="" tabindex="1" class="input" />
			</dd>
			<dd> 
				<label for="new_wachtwoord_opnieuw">wachtwoord opnieuw</label>
				<input type="password" name="new_wachtwoord_opnieuw" id="new_wachtwoord_opnieuw" value="" tabindex="1" class="input" />
				<input type="hidden" id="user" name="user" value="<?php echo $gebruiker['id'] ?>" />
			</dd>
			<dd><button id="btnNewPass" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Wachtwoord herstellen </button></dd>
		</dl>
		<?php }else{?>
		  	<dl>
		  		<dd><p class="login_error">De door u ingevulde gegevens zijn niet bij ons bekend. Probeer het alstublieft opnieuw.</p></dd>
		  	
		  		<dd><p>Vul hier uw gebruikersnaam en e-mailadres in ter verificatie</p></dd>
				<dd>
					<label for="checkgebruikersnaam">Gebruikersnaam</label>
					<input type="text" name="checkgebruikersnaam" id="checkgebruikersnaam" value="" tabindex="1" class="input" />
				</dd>
				<dd>
					<label for="checkemail">E-mailadres</label>
					<input type="email" name="checkemail" id="checkemail" value="" tabindex="2" class="input" />
				</dd>
				<dd><button id="btnCheckCred" class="button" onmouseover="this.className='button btn_hover'" onmouseout="this.className='button'"> Wachtwoord herstellen </button></dd>
			</dl>
		<?php }
	}elseif($_POST['action'] == 'checkPass'){
		$gebruiker_id = $_POST['user'];
		
		$what = 'a.voornaam, a.achternaam, a.email, b.startpagina'; 
		$from = 'portal_gebruiker a LEFT JOIN portal_gebruiker_instellingen AS b ON (b.gebruiker = a.id)'; 
		$where= 'a.id = '.$gebruiker_id;
			$gebruiker = mysql_fetch_assoc(sqlSelect($what, $from, $where));
		
		$wachtwoord = $_POST['wachtwoord'];
		
		$table = 'portal_gebruiker'; $what = 'wachtwoord = \''.md5($wachtwoord).'\''; $where = 'id = '.$gebruiker_id;
			$update_gebruiker = sqlUpdate($table, $what, $where);
			
		$naam = $gebruiker['voornaam'].' '.$gebruiker['achternaam'];
		$nu = strftime("%d %B %Y"); $email = $gebruiker['email'];
		
		$subject = 'U hebt uw wachtwoord hersteld';
        $htmlbody = "
        <p>
            Beste $naam,<br />
            Op $nu heeft u uw wachtwoord opnieuw ingesteld.
        </p>
        <p> 
	        Uw nieuwe wachtwoord is: $wachtwoord <br />
	        U kunt hiermee vanaf nu inloggen
        </p>
        
        <p>
            Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.<br />
            Wij nemen dan zo snel mogelijk contact met u op.
        </p>
        ";
        
        $textbody = " 
    
        Beste $naam,<br />
        Op $nu heeft u uw wachtwoord opnieuw ingesteld.
    
        Uw nieuwe wachtwoord is: $wachtwoord <br />
        U kunt hiermee vanaf nu inloggen
	        
        Heeft u naar aanleiding van deze e-mail nog vragen of opmerking, reageer dan op deze e-mail.
        Wij nemen dan zo snel mogelijk contact met u op.";	
		$_SESSION['login_id'] = $gebruiker_id;
		?>
		U hebt met succes uw wachtwoord gewijzigd !<br />
		U wordt automatisch doorgestuurd naar de applicatie of <a href="/<?php echo $gebruiker['startpagina']; ?>">klik hier</a>
		<script> setTimeout("window.location.replace('<?php echo $site_name.$gebruiker['startpagina']; ?>')",2000); </script>
	<?php 
	}else{}
	
	if($htmlbody != null){
		require($_SERVER['DOCUMENT_ROOT'].$etc_root.'lib/phpmailer/class.phpmailer.php');
        $what = 'portal_titel AS titel, admin_email'; $from = 'portal_instellingen'; $where= 'actief = 1';
			$portal = mysql_fetch_assoc(sqlSelect($what, $from, $where));
		
        $mail = new PHPMailer();
        $mail->SetFrom($portal['admin_email'], $portal['titel']);
        $mail->AddReplyTo($portal['admin_email'], $portal['titel']); 
        $mail->AddAddress($email);
                    
        $mail->Subject = $subject;
        $mail->Body = $htmlbody;
        $mail->AltBody= $textbody;
        $mail->WordWrap = 50; 
        if(!$mail->Send()){
            $error = 'mailer error'.$mail->ErrorInfo;
        }
	}
?>