<?php

/* PART I: VARIABLE PREPARATION */

//Prepare variables using information coming from FrontlineSMS
$sender_name = 'Patrick';
$sender_number = '+224000000000';
$keyword = 'PEC';
$message_content = '12 9 9 4';

$chw_phone = substr($sender_number,1);

//Parse message content
$data = explode(" ",$message_content);

/* Assign values to variables: # tests performed, # positive results, # patients who received treatment, # patients referred to the health center */
$realise = $data[0];
$positif = $data[1];
$traite = $data[2];
$refere = $data[3];

//Count indexes in the array
$result = count($data);

//Create code for each new set of data
$code = date("ymd");

// Prepare validation messages
$validation1 = rawurlencode('Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique prédefinie pour PEC.');
$validation2 = rawurlencode('Données non-valables. Veuillez composer votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.');
$validation3 = rawurlencode('Données non-valables. Veuillez composer votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.');
$validation4 = rawurlencode('Vous avez dépassé la logique prédefinie pour PEC. Veuillez consultez votre guide d\'utilisateur pour en savoir plus.');
$validation5 = rawurlencode('Le nombre de cas positif ne devrais pas dépasser le nombre de TDR réalisé. Veuillez contacter votre Animateur-Superviseur.');
$validation6 = rawurlencode('Le nombre de cas traité ne devrais pas dépasser le nombre de TDR positif. Veuillez contacter votre Animateur-Superviseur.');
$validation7 = rawurlencode('Données non-valable. Veuillez contacter votre Animateur-Superviseur.');


/* VALIDATIONS: The goal is to eliminate common mistakes during data entry such as extra spaces, non-numeric characters, and additional array indexes exceeding the amount allowed by the keyword. Additionally we need to verify that the data were entered in the correct order and that there are no logical contradictions. (e.x. One should not have more positive results than tests performed. So...) */


if($result < 4){
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle); 
	exit();
} elseif($realise === '' or $positif === '' or $traite === '' or $refere === ''){
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);	
	exit();
} elseif(!is_numeric($realise) or !is_numeric($positif) or !is_numeric($traite) or !is_numeric($refere)){
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation3."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($result > 4) {
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation4."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($positif > $realise){
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation5."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
} elseif($traite > $positif){
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation6."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}
else
{
	if($sender_name == 'Patrick'){

// Connect using PDO
		try
		{
		$db_conn = new PDO('mysql:host=localhost;dbname=sms', 'tester','mypassword');
		}
		catch (PDOException $e)
		{
		$output = 'Unable to connect to the database server. '.$e->getMessage();
		echo $output;
		exit();
		}
	}
}

// Insert data into the database
if ($realise >= 0 and $realise <=101 
and $positif >= 0 and $positif <= 101
and $traite >= 0 and $traite <=101
and $refere >= 0 and $refere <= 101) 
try
{		
	$stmt = $db_conn->query(
	"INSERT INTO pec SET
	sender_name = '$sender_name',
	sender_number = '$sender_number',
	keyword = '$keyword',
	tdr_realise = '$realise',
	tdr_positif = '$positif',
	ptnt_traite = '$traite',
	ptnt_refere = '$refere',
	code = '$code'");
}
catch (PDOException $e)
{
	$traite->getMessage();
	exit();
}
else
{
	$url="http://localhost:8011/send/sms/".$chw_phone."/".$validation7."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

// Prepare reply messages
$message_chw_1 = rawurlencode('Merci d\'envoyer vos données en prise en charge par SMS!');
$message_chw_2 = rawurlencode('Vous avez déjà soumis vos données en prise en charge ahourd\'hui. Merci!');

// Get the ID of the last inserted row 
try
{
	$lastInsertID = $db_conn->lastInsertId();	
}
catch (PDOException $e)
{
	$e->get_message();
	echo $e;
}	

// Select previously inserted code
try
{
	$select1 = $db_conn->query(
	"SELECT sender_number, code
	FROM pec
	WHERE id = '$lastInsertID'");
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

while($row = $select1->fetch()){
	$s_n1 = $row['sender_number'];
	$code1 = $row['code'];}

// Find matching data from pec_update
try
{
	$select2 = $db_conn->query(
	"SELECT code
	FROM pec_update
	WHERE sender_number LIKE '$s_n1'
	AND code LIKE '$code1'");

}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}
  
  while($row = $select2->fetch()){
	  $code2 = $row['code'];}

if(isset($code2)){
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
		$stmt = $db_conn->query(
		"INSERT INTO pec_update SET
		sender_name = '$sender_name',
		sender_number = '$sender_number',
		tdr_realise = '$realise',
		tdr_positif = '$positif',
		ptnt_traite = '$traite',
		ptnt_refere = '$refere',
		code = '$code'");
		
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



	
	
	

		



