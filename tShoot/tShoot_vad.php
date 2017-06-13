<?php

/* PART I: VARIABLE PREPARATION */

//Prepare variables using information coming from FrontlineSMS
$sender_name     = 'Aissata';
$sender_number   = '+224000000000';
$keyword         = 'VAD';
$message_content = htmlentities('1 6 3');

//Parse message content
$data = explode(" ",$message_content);

/* Assign values to variables: # tests performed, # positive results, # patients who received treatment, # patients referred to the health center */
$vad    = (int)$data[0];
$hommes = (int)$data[1];
$femmes = (int)$data[2];
$total  = $hommes + $femmes;

//Count indexes in the array
$count = count($data);

//Create code for each new set of data
$code = date("ymd");

// Prepare validation messages
$validation1 = 'Il y a des informations manquantes. Consultez votre fiche d\'utilisateur pour savoir la logique predefinie pour VAD.';
$validation2 = 'Donnees non-valables. Veuillez ecrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre fiche d\'utilisateur pour en savoir plus.';
$validation3 = 'Donnees non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre fiche d\'utilisateur pour en savoir plus.';
$validation4 = 'Vous avez depasse la logique predefinie pour VAD. Veuillez consultez votre fiche d\'utilisateur pour en savoir plus.';
$validation5 = 'Le nombre de cas positif ne devrais pas depasser le nombre de TDR realise. Veuillez contacter votre Animateur-Superviseur.';
$validation6 = 'Le nombre de cas traite ne devrais pas depasser le nombre de TDR positif. Veuillez contacter votre Animateur-Superviseur.';
$validation7 = 'Donnees non-valable. Veuillez contacter votre Animateur-Superviseur.';


/* VALIDATIONS: The goal is to eliminate common mistakes during data entry such as extra spaces, non-numeric characters, and additional array indexes exceeding the amount allowed by the keyword. Additionally we need to verify that the data were entered in the correct order and that there are no logical contradictions. (e.x. One should not have more positive results than tests performed. So...) */


if($count < 3) {
	// Too few data fields submitted
	echo $validation1; 
	exit();
} elseif($vad === '' or $hommes === '' or $femmes === '' or $total === '') {
	// Additional empty space
	echo $validation2;	
	exit();
} elseif(!is_numeric($vad) or !is_numeric($hommes) or !is_numeric($femmes) or !is_numeric($total)) {
	// Character not a number
	echo $validation3;
	exit();
} elseif($count > 3) {
	// Too many data fields submitted
	echo $validation4;
	exit();
} else {

}
// Connect using PDO
try {
$db_conn = new PDO('mysql:host=localhost;dbname=sms', 'tester','mypassword');
} catch (PDOException $e) {
$output = 'Unable to connect to the database server. '.$e->getMessage();
echo $output;
exit();
}

// PART 2: CHW-SIDE CODING
// Insert data into the database
if (($vad    >= 0 && $vad    <= 101)    && 
	($hommes >= 0 && $hommes <= 301)    && 
	($femmes >= 0 && $femmes <= 301)    && 
	($total  >= 0 && $total  <= 401)) {
	try {		
	$stmt = $db_conn->query(
	"INSERT INTO vad SET
	  sender_name   = '$sender_name'
	, sender_number = '$sender_number'
	, keyword       = '$keyword'
	, vad_total     = '$vad'
	, hommes 		= '$hommes'
	, femmes        = '$femmes'
	, total         = '$total'
	, code          = '$code'");
	} catch (PDOException $e) {
		$e->getMessage();
		exit();
	}
	echo "<strong>1. DATA INSERTED SUCCESSFULLY!</strong><br><br>";
} else {
	echo "<strong>DATA INSERT FAILED</strong><br><br>";
	echo $validation7;
	exit();
}

// Prepare reply messages
$message_chw_1 = 'Merci d\'envoyer vos donnees en visite a domicile par SMS! Vous avez soumis les resultats suivants: [VAD = '.$vad.' Hommes = '.$hommes.' Femmes = '.$femmes.' Total = '.$total.']';
$message_chw_2 = 'Vous avez deja soumis vos donnees en visite a domicile ahourd\'hui. Vous pouvez soumettre a nouveau des demain. Merci!';

// Find matching data from pec_update
try {
	$select2 = $db_conn->query(
	"SELECT code
	 FROM vad_update
	 WHERE sender_number
	 LIKE '$sender_number' 
	 AND code 
	 LIKE '$code'");
	while($row = $select2->fetch()){
		$code2 = $row['code'];}
} catch (PDOException $e) {
	$e->getMessage();
	echo $e;
}

if(isset($code2)){
	// USE FOR DEBUGGIN
	echo "<strong>2A. CHW resubmission NORMAL</strong><br><br>";
	echo "[SMS 1]";
	echo "<br>";
	echo "<br>";
	echo $message_chw_2;
	exit();
} else {
	try {		
		$stmt = $db_conn->query(
		"INSERT INTO vad_update SET
		  sender_name   = '$sender_name'
		, sender_number = '$sender_number'
		, vad_total     = '$vad'
		, hommes        = '$hommes'
		, femmes        = '$femmes'
		, total         = '$total'
		, code          = '$code'");
	} catch (PDOException $e) {
		$e->getMessage();
		exit();
	}
	echo "<strong>2A. CHW submission NORMAL</strong><br><br>";
	echo "[SMS 1]";
	echo "<br>";
	echo "<br>";
	echo $message_chw_1;
}

?>