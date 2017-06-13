<?php
/* 
Version 1.02
06-06-2013

STOP PALU 

STRUCTURED SMS DATA COLLECTION - ELECTRONIC STOCK CHART 

This is the latest version of an SMS data collection system, developed to support the STOP PALU project whihch works within the scope of the President's Malaria Initiative in Guinea, and monitors rapid diagnostic tests and arteminisin combination therapy medication stock levels of community health workers. An alert notification system is built into the overall framework, which notifies the appropriate health center if a CHW needs to be re-supplied. 
 
REFERENCE

$n - NOURRISSON/Nursing form of ACT
$pe - PETIT ENFANT/Small Child form of ACT
$e - ENFANT/Child form of ACT
$a - ADULTE/Adult form of ACT
$tdr - TEST DE DIAGNOSTIC RAPID/Rapid Diagnostic test 

 */

//PART I: DATA PREPARATION & VALIDATION 

//Sets the variables coming from FrontlineSMS
$nom_envoyeur = 'Patrick';
$numero_envoyeur = '+224000000000';
$keyword = 'ASAQ';
$contenu_message = '25 25 25 25 25';

//Prepare the phone number
$chw_phone = substr($numero_envoyeur, 1);

//Parse message content
$data = explode(" ",$contenu_message);
$result = count($data);

//Assign values to variables. 
$n = $data[0];
$pe = $data[1];
$e = $data[2];
$a = $data[3];
$tdr = $data[4];


//Prepare validation messages
$validation1 = rawurlencode('Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique prédefinie pour ASAQ. Merci, '.$nom_envoyeur.'! STOP PALU');
$validation2 = rawurlencode('Données non-valables. Veuillez écrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus. Merci, '.$nom_envoyeur.'! STOP PALU');
$validation3 = rawurlencode('Données non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.  Merci, '.$nom_envoyeur.'! STOP PALU');
$validation4 = rawurlencode('Vous avez dépassé la logique prédefinie pour ASAQ. Veuillez consultez votre guide d\'utilisateur pour en savoir plus. Merci, '.$nom_envoyeur.'! STOP PALU');
$validation5 = rawurlencode('Veuillez recomposer le mot-cle et re-envoyer. Merci! STOP PALU');


/* VALIDATIONS: The goal is to eliminate common mistakes during data entry such as adding extra spaces, non-numeric characters, and array indexes that exceed the amount allowed for this keyword. */
if($keyword == 'ASAQ'){
  if($result < 5){
	  // Not enough data points
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation1."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();
  }if(empty($n) and $n === '' or empty($pe) and $pe === "" or empty($e) and $e === "" or empty($a) and $a === "" or empty($tdr) and $tdr === ""){
	  // Added extra spaces
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation2."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();
  }elseif(!is_numeric($n) or !is_numeric($pe) or !is_numeric($e) or !is_numeric($a) or !is_numeric($tdr)){
	  //Entered a non-numeric character	
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation3."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();	
  }elseif($result > 5){
	  //Added too many data points
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation4."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();
  }
}
else
{
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation5."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}
	
/* CONNECTION */

if(isset($nom_envoyeur)) {
	// Connect using PDO
	try
	{
		$db_conn = new PDO('mysql:host=localhost;dbname=stop', 'stop_tester','mypassword');
		echo 'CONNECTED</br>';
	}
	catch (PDOException $e)
	{
		$output = 'Unable to connect to the database server.';
		echo $output;
		exit();
	}
}
else
{
	echo 'Error: No sender name.';
	exit();
}

/* Fetch data on community health worker */
try
{
	//Get the community health worker's ID number and health center ID number from chw.table
	$chw = $db_conn->query(
	"SELECT 
	  id
	, centreID
		FROM ac
			WHERE ac_phone = $numero_envoyeur");	
	//Fetch, then declare the CHW and health center ID numbers	
	while($row = $chw->fetch()) {
		$chwID = $row['id'];
		$centreID = $row['centreID'];}
	//Fetch the health center name	
	$hc = $db_conn->query(
	"SELECT
	  centre
	  FROM centre_sante
	  	WHERE centre_sante.id = '$centreID'");
	//Assign value to variable	
	while($row = $hc->fetch()){
		$center = $row['centre'];}
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}


// CHW Responses
$message_chw_1 = rawurlencode('Merci, '.$nom_envoyeur.'! Vous avez mis a jour votre votre stock en ASAQ/TDR; ça vous reste les quantites suivantes: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.'] STOP PALU');
$message_chw_2 = rawurlencode($nom_envoyeur.', vous avez déjà mis a jour votre quantité restante en ASAQ/TDR. CS '.$center.' a été notifié de vous ravitailler. Veuillez contacter le chef de CS pour assurer votre ravitaillement. STOP PALU');
$message_chw_3 = rawurlencode('Vous avez déjà mis a jour votre quantité restante en ASAQ/TDR: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.'] STOP PALU');
$message_chw_4 = rawurlencode('Données non-valabe. Veuillez contacter l\'equipe de STOP PALU');
$message_chw_5 = rawurlencode($nom_envoyeur.', vous avez atteint le seuil d\'alert. Le centre de sante de '.$center.' a été notifié que ça vous reste les quantites suivantes: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.'] STOP PALU');


/* We assume that no community health worker will have more than 100 units of any one form of medication or rapid diagnostic test. If units exceed 100, a message notifying them to contact their A-S is sent. */

if ($n >= 0 and $n <=101 
and $pe >= 0 and $pe <= 101
and $e >= 0 and $e <=101
and $a >= 0 and $a <= 101
and $tdr >= 0 and $tdr <=101) 
try
{
	//Insert into database		
	$stmt = $db_conn->query(
	"INSERT INTO asaq SET
	  nom_envoyeur = '$nom_envoyeur'
	, numero_envoyeur = '$numero_envoyeur'
	, keyword = '$keyword'
	, nourrisson = '$n'
	, petit_enfant = '$pe'
	, enfant = '$e'
	, adulte = '$a'
	, tdr = '$tdr'");
}
catch (PDOException $e)
{
	$e->getMessage();
	
	exit();
}
else
{
	//Message asking CHW to contact supervisor
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_4."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

 
try
{
	//Retrieve ID of the last inserted row
	$lastInsertID = $db_conn->lastInsertId();
}
catch (PDOException $e)
{
	$e->get_message();
	echo $e;
}


try
{
	//Pull out data from asaq.table using $lastInsertID	
	$select1 = $db_conn->query(
	"SELECT 
	  nourrisson 
	, petit_enfant 
	, enfant 
	, adulte 
	, tdr
		FROM asaq
			WHERE id = $lastInsertID");
	
	//Assign the values we just retrieved from the asaq.table to variables
	while($row = $select1->fetch()) {
		$n1 = $row['nourrisson'];
		$pe1 = $row['petit_enfant'];
		$e1 = $row['enfant'];
		$a1 = $row['adulte'];
		$tdr1 = $row['tdr'];}
	}
catch (PDOException $e)
{	
	$e->getMessage();
	echo $e;
}


/* We need to either pull out data from, or insert a dummy set into asaq_update.table so we can compare it with newly submitted valaues later on.
 */

try
{
	//Pull out values from asaq_update.table	
	$select2 = $db_conn->query(
	"SELECT 
	  nourrisson 
	, petit_enfant 
	, enfant 
	, adulte 
	, tdr
		FROM asaq_update
			WHERE chwID = '$chwID'");
	//Assign the values we just retrieved from the asaq.table to variables	
	while($row = $select2->fetch()) {
		$n2 = $row['nourrisson'];
		$pe2 = $row['petit_enfant'];
		$e2 = $row['enfant'];
		$a2 = $row['adulte'];
		$tdr2 = $row['tdr'];}
		
		echo 'asaq_update.table SUCCESSFULLY SELECTED </br>';
}
catch (PDOException $e)
{	
	$e->getMessage();
	echo $e;
}

	
/* If there are no record for the CHW in asaq_update.table, then we can automate the process by just inserting a new row.
 */	
if(!isset($n2))
{
	try
	{
		//If there are no records for the CHW, then insert a new row
		$stmt1 = $db_conn->query(
		"INSERT INTO asaq_update SET
		  nourrisson = '0'
		, petit_enfant = '0'
		, enfant = '0'
		, adulte = '0'
		, tdr = '0'
		, chwID = '$chwID'");
		//Fetch the ID of the last inserted row
		$lastInsertID1 = $db_conn->lastInsertId();
		echo $lastInsertID1.'</br>';
		//Fetch data for the newest inserted row
		$stmt2 = $db_conn->query(
		"SELECT 
		  nourrisson 
		, petit_enfant 
		, enfant
		, adulte 
		, tdr
			FROM asaq_update
				WHERE id = '$lastInsertID1'");
		//Assign to variables
		while($row = $select2->fetch()) {
			$n2 = $row['nourrisson'];
			$pe2 = $row['petit_enfant'];
			$e2 = $row['enfant'];
			$a2 = $row['adulte'];
			$tdr2 = $row['tdr'];}
	}
	catch (PDOException $e)
	{
		$e->getMessage();
		echo $e;
	}
}	
else
{

}

// Declare the quantity at which a community health worker must make a request for re-supply
$alert = '5';

		
/* COMPARE asaq.table AND asaq_update.table 

If a new dataset that has just been entered into asaq.table equals a dataset already hosted in asaq_update.table AND the entire dataset is above the alert level, then we inform the CHW that they already updated their stock level. */
if(($n1 == $n2) 
and ($pe1 == $pe2) 
and ($e1 == $e2) 
and ($a1 == $a2) 
and ($tdr1 == $tdr2)){
	if(($n1 > $alert) 
	and ($pe1 > $alert) 
	and ($e1 > $alert) 
	and ($a1 > $alert) 
	and ($tdr1 > $alert))
	{
		//Inform CHW that stock levels have already been updated
		$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_3."/";
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		$output=curl_exec($curl_handle);
		curl_close($curl_handle);
		exit();
	}
/* If a new dataset that has just been entered into asaq.table equals a dataset already hosted in asaq_update.table AND the hosted dataset warranted an alert being sent to the health center, then we notify the CHW that the health center has already been contacted and ask the CHW to contact the health center to plan for re-supply. */
	else
	{
		//Inform CHW that health center has already been notified
		$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_2."/";
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		$output=curl_exec($curl_handle);
		curl_close($curl_handle);
		exit();
	}
}
/* If a new dataset that has just been entered into asaq.table DOES NOT EQUAL a dataset already hosted in asaq_update.table, AND if at least one of the data points is below the alert level, then we (1) run an update query, (2) inform the health center, and (3) inform the CHW. */
elseif(($n1 != $n2) 
or ($pe1 != $pe2) 
or ($e1 != $e2) 
or ($a1 != $a2) 
or ($tdr1 != $tdr2)){
	if(($n1 <= $alert) 
	or ($pe1 <= $alert) 
	or ($e1 <= $alert) 
	or ($a1 <= $alert) 
	or ($tdr1 <= $alert))
	{
		try
		{
			//Run update query	
			$update = $db_conn->query(
			"UPDATE asaq_update SET
			  nourrisson = '$n1'
			, petit_enfant = '$pe1'
			, enfant = '$e1'
			, adulte = '$a1'
			, tdr = '$tdr1'
				WHERE chwID = '$chwID'");
			//Select the data we just entered into asaq_update.table
			$select3 = $db_conn->query(
			"SELECT 
			  nourrisson 
			, petit_enfant 
			, enfant
			, adulte 
			, tdr
				FROM asaq_update
					WHERE chwID = '$chwID'");
		    //Assign values to variables
			while($row = $select3->fetch()) {
				$n3 = $row['nourrisson'];
				$pe3 = $row['petit_enfant'];
				$e3 = $row['enfant'];
				$a3 = $row['adulte'];
				$tdr3 = $row['tdr'];}
			//Inform CHW
			$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_5."/";
			$curl_handle=curl_init();
			curl_setopt($curl_handle,CURLOPT_URL,$url);
			curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
			$output=curl_exec($curl_handle);
			curl_close($curl_handle);
		}
		catch (PDOException $e)
		{
		  $e->getMessage();
		  echo $e;
		}
	}
	else 
/* If the CHW sent a new dataset, and if stock levels are above the alert trigger, then we (1) update the table, and (2) inform the CHW that the new dataset was received, and (3) inform the health center that the CHW's ACT/RDT stock has changed. */
	{
	 try
	  {
		  //Update asaq_update.table
		  $update = $db_conn->query(
		  "UPDATE asaq_update SET
		    nourrisson = '$n1'
		  , petit_enfant = '$pe1'
		  , enfant = '$e1'
		  , adulte = '$a1'
		  , tdr = '$tdr1'
		  	WHERE chwID = '$chwID'");
		  //Retrieve the data we just inserted into asaq_update.table
		  $select3 = $db_conn->query(
		  "SELECT 
		    nourrisson
		  , petit_enfant 
		  , enfant 
		  , adulte 
		  , tdr
		  	FROM asaq_update
		  		WHERE chwID = '$chwID'");
		  //Declare variables 
		  while($row = $select3->fetch()) {
			  $n3 = $row['nourrisson'];
			  $pe3 = $row['petit_enfant'];
			  $e3 = $row['enfant'];
			  $a3 = $row['adulte'];
			  $tdr3 = $row['tdr'];}
		  //Inform CHW
		  $url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_1."/";
		  $curl_handle=curl_init();
		  curl_setopt($curl_handle,CURLOPT_URL,$url);
		  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		  $output=curl_exec($curl_handle);
		  curl_close($curl_handle);
	  }
	  catch (PDOEXception $e)
	  {
		  $e->getMessage();
		  echo $e;
	  }
	}
}

// PART III: Health Center Side Coding

//HEALTH CENTER RESPONSES
$message_hc_1 = rawurlencode('Ravitaillement nécessaire. Veuillez contacter '.$nom_envoyeur.' à +'.$chw_phone.'. [Nourrisson = '.$n3.' Petit Enfant = '.$pe3.' Enfant = '.$e3.' Adulte = '.$a3.' TDR = '.$tdr3.']');
$message_hc_2 = rawurlencode('Stock en ASAQ/TDR mis a jour par '.$nom_envoyeur.'. Il lui reste les quantités suivantes: [Nourrisson = '.$n3.' Petit Enfant = '.$pe3.' Enfant = '.$e3.' Adulte = '.$a3.' TDR = '.$tdr3.']');


try
{
	//Retrieve the health center director's mobile phone number
	$stmt = $db_conn->query(
	"SELECT numero
		FROM centre_sante
		INNER JOIN ac
		ON centreID = centre_sante.id
		INNER JOIN asaq
		ON numero_envoyeur = ac_phone
			WHERE asaq.id = $lastInsertID"); 
		//Declare $hcPhone
	while($row = $stmt->fetch()) {
		$hcPhone = substr($row['numero'],1);}
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}


//Determine if either and alert or a normal response will be sent
if ((
is_numeric($n3) and $n3 >= 0 and $n3 <=5) 
or (is_numeric($pe3) and $pe3 >= 0 and $pe3 <=5)
or (is_numeric($e3) and $e3 >= 0 and $e3 <= 5)
or (is_numeric($a3) and $a3 >= 0 and $a3 <= 5)
or (is_numeric($tdr3) and $tdr3 >= 0 and $tdr3 <= 5)) {
	//Re-supply needed
    $url="http://localhost:8011/send/sms/".$hcPhone."/".$message_hc_1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}else{
	//Normal update
	$url="http://localhost:8011/send/sms/".$hcPhone."/".$message_hc_2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

?>