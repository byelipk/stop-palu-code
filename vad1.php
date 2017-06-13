<?php
/*
Version 1.02
06-06-2013

STOP PALU: STRUCTURED SMS DATA COLLECTION - HOME VISITS

This is the latest version of an SMS data collection system, developed to support the STOP PALU project whihch works within the scope of the President's Malaria Initiative in Guinea, and monitors behavior change communication activities of community health workers (CHW). 

REFERENCE

$vad - VISITE A DOMICILE, or the number of home visits performed by the CHW
$hommes - The number of positive results
$femmes - The number of patients trated with ACT
$total -  The number of patients referred to the health center 

PART I: VARIABLE PREPARATION & VALIDATION 
 */

//Prepare variables using information coming from FrontlineSMS 1.6.16.3
$nom_envoyeur = $_REQUEST['sender_name'];
$numero_envoyeur = $_REQUEST['sender_number'];
$keyword = $_REQUEST['keyword'];
$contenu_message = $_REQUEST['message_content'];

//Prepare phone number
$chw_phone = substr($numero_envoyeur,1);

//Parse message content
$data = explode(" ",$contenu_message);

//Assign meaningful values to variables
$vad = $data[0];
$hommes = $data[1];
$femmes = $data[2];
$total = $hommes + $femmes;

//Count indexes in the array
$result = count($data);

//Create code for each new set of data. Limit one set of data per day.
$code = date("ymd");

//Prepare validation messages
$validation1 = rawurlencode('Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique prédefinie pour VAD.');
$validation2 = rawurlencode('Donnees non-valables. Veuillez ecrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.');
$validation3 = rawurlencode('Données non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.');
$validation4 = rawurlencode('Vous avez dépassé la logique prédefinie pour VAD. Veuillez consultez votre fiche d\'utilisateur pour en savoir plus.');
$validation5 = rawurlencode('Données non-valable. Veuillez contacter votre Animateur-Superviseur.');

/* VALIDATIONS: The goal is to eliminate common mistakes during data entry such as extra spaces, non-numeric characters, and additional array indexes exceeding the amount allowed by the keyword.  */
if(isset($keyword)){
  if($result < 3){
	  //Send Message: Not enough data points
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation1."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();
  }if(empty($vad) and $vad === '' or empty($hommes) and $hommes === "" or empty($femmes) and $femmes === ""){
	  //Send Message: Added extra spaces
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation2."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();
  }elseif(!is_numeric($vad) or !is_numeric($hommes) or !is_numeric($femmes)){
	  //Send Message: Entered a non-numeric character	
	  $url="http://localhost:8011/send/sms/".$chw_phone."/".$validation3."/";
	  $curl_handle=curl_init();
	  curl_setopt($curl_handle,CURLOPT_URL,$url);
	  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	  $output=curl_exec($curl_handle);
	  curl_close($curl_handle);
	  exit();	
  }elseif($result > 3){
	  //Send Message: Added too many data points
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


/* PART 2: CHW-SIDE CODING */

//Second round of validation
if ($vad >= 0 and $vad <=50 
and $hommes >= 0 and $hommes <= 251
and $femmes >= 0 and $femmes <=251
and $total >= 0 and $total <= 502) 
try
{
	//Insert data into the database		
	$stmt = $db_conn->query(
	"INSERT INTO vad SET
	  nom_envoyeur = '$nom_envoyeur'
	, numero_envoyeur = '$numero_envoyeur'
	, keyword = '$keyword'
	, vad_total = '$vad'
	, hommes = '$hommes'
	, femmes = '$femmes'
	, total = '$total'
	, code = '$code'");
}
catch (PDOException $e)
{
	$e->getMessage();
	exit();
}
else
{
	//Send Message: Contact supervisor
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation5."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

/* The following try-catch statements determine is the CHW has already submitted data on home visits for the day */ 
try
{
	//Get the ID of the last inserted row
	$lastInsertID = $db_conn->lastInsertId();	
}
catch (PDOException $e)
{
	$e->get_message();
	echo $e;
}	


try
{
	//Select previously inserted phone number and code
	$select1 = $db_conn->query(
	"SELECT code
		FROM vad
			WHERE id = '$lastInsertID'");
	//Fetch code
	while($row = $select1->fetch()){
		$code1 = $row['code'];}
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}


try
{
	//Find matching data from vad_update
	$select2 = $db_conn->query(
	"SELECT vad_total, hommes, femmes, total, code
		FROM vad_update
			WHERE code LIKE '$code1'");
	//Fetch code
	while($row = $select2->fetch()){
		$vad2 = $row['vad_total'];
		$hommes2 = $row['hommes'];
		$femmes2 = $row['femmes'];
		$total2 = $row['total'];
		$code2 = $row['code'];}
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

//Prepare reply messages to validated data
$message_chw_1 = rawurlencode('Merci d\'envoyer vos données en visite à domicile par SMS! [VAD = '.$vad.' Hommes = '.$hommes.' Femmes = '.$femmes.' Total = '.$total.']');
$message_chw_2 = rawurlencode('Vous avez deja soumis vos données en visite à domicile ahourd\'hui. Merci! [VAD = '.$vad2.' Hommes = '.$hommes2.' Femmes = '.$femmes2.' Total = '.$total2.']');


if(isset($code2)){
	//Send Message: Data already submitted
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}
else
{
	try
	{
		//Insert into vad_update.table		
		$stmt = $db_conn->query(
		"INSERT INTO vad_update SET
		  nom_envoyeur = '$nom_envoyeur'
		, numero_envoyeur = '$numero_envoyeur'
		, vad_total = '$vad'
		, hommes = '$hommes'
		, femmes = '$femmes'
		, total = '$total'
		, code = '$code'");
		//Send Message: Data entry successful
		$url="http://localhost:8011/send/sms/".$chw_phone."/".$message_chw_1."/";
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		$output=curl_exec($curl_handle);
		curl_close($curl_handle);
	}
	catch (PDOException $e)
	{
		$e->getMessage();
		exit();
	}
}

?>
