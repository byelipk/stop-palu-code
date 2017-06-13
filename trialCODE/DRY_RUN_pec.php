<?php
/**
 * Version 1.02
 * 06-06-2013
 *
 * STOP PALU: STRUCTURED SMS DATA COLLECTION - MALARIA CASE MANAGEMENT
 *
 * This is the latest version of an SMS data collection system, developed to 
 * support the STOP PALU project, whihch works within the scope of the 
 * President's Malaria Initiative in Guinea, and monitors malaria case 
 * management activities of community health workers. 
 *
 * REFERENCE
 *
 * $realise - The number of RDTs performed
 * $positif - The number of positive results
 * $traite - The number of patients treated with ACT
 * $refere -  The number of patients referred to the health center 
 */

//PART I: VARIABLE PREPARATION & VALIDATION 
 

//Prepare variables using information coming from FrontlineSMS
$nom_envoyeur = 'Patrick';
$numero_envoyeur = '+224000000000';
$keyword = 'PEC';
$contenu_message = '12 12 10 2';

//Prepare the CHW's phone number
$chw_phone = substr($numero_envoyeur,1);

//Parse message content
$data = explode(" ",$contenu_message);

/**
 * Assign values to variables: # tests performed, # positive results, # patients who * received treatment, # patients referred to the health center... 
 */ 
$realise = $data[0];
$positif = $data[1];
$traite = $data[2];
$refere = $data[3];

//Count indexes in the array
$result = count($data);

//Create code for each new set of data
$code = date("ymd");

//Prepare validation messages
$validation1 = rawurlencode('Sortie de la logique prédéfinie: Consultez votre guide d\'utilisateur pour savoir la logique prédéfinie pour le mot-clé PEC.');
$validation2 = rawurlencode('Données non-valables: Veuillez composer votre SMS en n\'utilisant qu\'un seul espace entre les chiffres.');
$validation3 = rawurlencode('Données non-valables: Veuillez composer votre SMS en utilisant uniquement des chiffres.');
$validation4 = rawurlencode('Sortie de la logique prédéfinie: Consultez votre guide d\'utilisateur pour savoir la logique prédéfinie pour le mot-clé PEC.');
$validation5 = rawurlencode('Données non-valables: Le nombre de cas positif a dépassé le nombre de TDR réalisé. Veuillez contacter l\'équipe STOP PALU.');
$validation6 = rawurlencode('Données non-valables: Le nombre de cas traité a dépassé le nombre de TDR positif. Veuillez contacter l\'équipe STOP PALU.');
$validation7 = rawurlencode('Données non-valable. Veuillez contacter l\'équipe STOP PALU.');
$validation8 = rawurlencode('Données non-valables: Le nombre de TDR réalisé dépasse le nombre de cas pris en charge. Veuillez contacter l\'equipe STOP PALU.');


/** 
 * VALIDATIONS: The goal is to eliminate common mistakes during data entry such as 
 * extra spaces, non-numeric characters, and insufficient or additional array indexes
 * as required by the keyword. Additionally we need to verify that the data were 
 * entered in the correct order and that there are no logical contradictions. 
 * (e.x. One should not have more positive test results than tests performed.)  
 */
 
if($result < 4){
	//Too few data points
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle); 
	exit();
} elseif($realise === '' or $positif === '' or $traite === '' or $refere === ''){
	//Extra spaces
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);	
	exit();
} elseif(!is_numeric($realise) or !is_numeric($positif) or !is_numeric($traite) or !is_numeric($refere)){
	//Entered non-numeric value
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation3."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($result > 4) {
	//Too many data points
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation4."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($positif > $realise){
	//More positive tests results that tests performed
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation5."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($traite > $positif){
	//More patients treated than positive test results
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation6."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($realise > $traite + $refere){
	//Number of tests performed is greater than the number of cases managed
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation8."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}
else
{

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

/** 
 * We set the maximum level here at 101. There are several reasons why: (1)
 * Community health workers should only be given at most one box of each form
 * of ACT - each box  contains 25 doeses- and 2 boxes of RDTs. (2) If they 
 * report their data at regular  intervals they will not have such a high work 
 * volume, and the data should be well under this limit. If the exceed the 
 * limit, a message is sent asking them to contact their A-S. Future versions 
 * will have an automatic message sent to the Animator-Supervisor.
 */
 
 
if($realise >= 0 and $realise <=101 
and $positif >= 0 and $positif <= 101
and $traite >= 0 and $traite <=101
and $refere >= 0 and $refere <= 101) 
try
{
	//Insert data into the database		
	$stmt = $db_conn->query(
	"INSERT INTO pec SET
	  nom_envoyeur = '$nom_envoyeur'
	, numero_envoyeur = '$numero_envoyeur'
	, keyword = '$keyword'
	, tdr_realise = '$realise'
	, tdr_positif = '$positif'
	, ptnt_traite = '$traite'
	, ptnt_refere = '$refere'
	, code = '$code'");
}
catch (PDOException $e)
{
	$traite->getMessage();
	exit();
}
else
{
	//Message asking CHW to contact their supervisor
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation7."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

try
{
	//Get the ID of the last inserted row
	$lastInsertID = $db_conn->lastInsertId();
	//Select previously inserted code
	$select1 = $db_conn->query(
	"SELECT numero_envoyeur, tdr_realise, tdr_positif, ptnt_traite, ptnt_refere, code
		FROM pec
			WHERE id = '$lastInsertID'");
	//Assign values to variables
	while($row = $select1->fetch()){
		$numero_envoyeur1 = $row['numero_envoyeur'];
		$tdr_r = $row['tdr_realise'];
		$tdr_p = $row['tdr_positif'];
		$ptnt_t = $row['ptnt_traite'];
		$ptnt_r = $row['ptnt_refere'];
		$code1 = $row['code'];}		
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

//Prepare reply messages
$message_chw_1 = rawurlencode('Merci d\'envoyer vos données en prise en charge par SMS! [TDR Réalisé = '.$realise.' TDR Positif = '.$positif.' Patients Traités = '.$traite.' Patients Référés = '.$refere.']');
$message_chw_2 = rawurlencode('Vous avez déjà soumis vos données en prise en charge ahourd\'hui. Contactez l\'equipe STOP PALU en cas de besoin. Merci! [TDR Réalisé = '.$tdr_r.' TDR Positif = '.$tdr_p.' Patients Traités = '.$ptnt_t.' Patients Référés = '.$ptnt_r.']');

try
{
	//Find matching data from pec_update
	$select2 = $db_conn->query(
	"SELECT 
	  tdr_realise  
	, tdr_positif
	, ptnt_traite
	, ptnt_refere
		FROM pec_update
			WHERE numero_envoyeur LIKE '$numero_envoyeur1' 
			AND code LIKE '$code1'");
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

/**
 * In order for this system to maintain a high degree of data integirity, only 
 * one submitted data set per day is allowed. If the check reveals a matching 
 * code number, the dataset is rejected and the CHW informed. 
 */
if($select2 === FALSE){
	echo 'FALSE </br>';
	try
	{
		//If PDO Object $select1 returns FALSE, then asaq_update.table will be updated		
		$stmt = $db_conn->query(
		"INSERT INTO pec_update SET
		  nom_envoyeur = '$nom_envoyeur'
		, numero_envoyeur = '$numero_envoyeur'
		, tdr_realise = '$realise'
		, tdr_positif = '$positif'
		, ptnt_traite = '$traite'
		, ptnt_refere = '$refere'
		, code = '$code'");
		//Message informing CHW the dataset was accepted
		$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_1."/";
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		$output=curl_exec($curl_handle);
		curl_close($curl_handle);
		exit();
	}
	catch (PDOException $e)
	{
		$traite->getMessage();
		exit();
	}
}
else
{
	echo 'TRUE </br>';
	//If PDO Object $select1 returns TRUE, then asaq_update.table will not be updated
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}