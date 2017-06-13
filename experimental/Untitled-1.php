<?php

/* PART I: VARIABLE PREPARATION */

//Prepare variables using information coming from FrontlineSMS
$sender_name = 'Patrick';
$sender_number = '+224000000000';
$keyword = 'PEC';
$message_content = '12 10 10 13';

//Parse message content
$data = explode(" ",$message_content);

/* Assign values to variables: # tests performed, # positive results, # patients who received treatment, # patients referred to the health center */
$realise = $data[0];
$positif = $data[1];
$traite = $data[2];
$refere = $data[3];

//Count indexes in the array
$result = count($data);

//Create code for each net set of data
$code = date("ymd");

// Prepare validation messages
$validation1 = 'Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique prédefinie pour PEC.';
$validation2 = 'Donnees non-valables. Veuillez ecrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$validation3 = 'Donnees non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$validation4 = 'Vous avez depasse la logique predefinie pour PEC. Veuillez consultez votre guide d\'utilisateur pour en savoir plus.';
$validation5 = 'Le nombre de cas positif ne devrais pas depasser le nombre de TDR realise. Veuillez contacter votre Animateur-Superviseur.';
$validation6 = 'Le nombre de cas traite ne devrais pas depasser le nombre de TDR positif. Veuillez contacter votre Animateur-Superviseur.';


/* VALIDATIONS: The goal is to eliminate common mistakes during data entry such as extra spaces, non-numeric characters, and additional array indexes exceeding the amount allowed by the keyword. Additionally we need to verify that the data were entered in the correct order and that there are no logical contradictions. (e.x. One should not have more positive results than tests performed. So...) */


if($result < 4){
	echo $validation1; 
	exit();
} elseif($realise === '' or $positif === '' or $traite === '' or $refere === ''){
	echo $validation2;	
	exit();
} elseif(!is_numeric($realise) or !is_numeric($positif) or !is_numeric($traite) or !is_numeric($refere)){
	echo $validation3;
	exit();
} elseif($result > 4) {
	echo $validation4;
	exit();
} elseif($positif > $realise){
	echo $validation5;
	exit();
} elseif($traite > $positif){
	echo $validation6;
	exit();
}
else
{
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
	echo 'SHIT';
	exit();
}

// Prepare reply messages
$message_chw_1 = 'Merci d\'envoyer vos donnees en prise en charge par SMS!';
$message_chw_2 = 'Vous avez deja soumis vos donnees en prise en charge ahourd\'hui. Merci!';

// Get the ID of the last inserted row 
try
{
	$lastInsertID = $db_conn->lastInsertId();
	echo 'This is the ID of the newly inserted row: '.$lastInsertID.'</br>';
	
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
	"SELECT code
	FROM pec
	WHERE id = '$lastInsertID'");
	
	while($row = $select1->fetch()){
		$code1 = $row['code'];}
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

// Find matching data from pec_update
try
{
	$select2 = $db_conn->query(
	"SELECT code
	FROM pec_update
	WHERE code 
	LIKE '$code1'");
	
	while($row = $select2->fetch()){
		$code2 = $row['code'];}
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

if(isset($code2)){
	echo $message_chw_2;
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
		
		echo $message_chw_1;
	}
	catch (PDOException $e)
	{
		$traite->getMessage();
		exit();
	}
}


	
	
	

		



