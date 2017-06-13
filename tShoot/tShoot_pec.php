<?php

/////////////////////////////////////////////
// Prep variables coming from FrontlineSMS //
/////////////////////////////////////////////
$sender_name     = 'Julie';
$sender_number   = '+224000000000';
$keyword         = 'PEC';
$message_content = htmlentities('13 10 10 0');

//////////////////////////
// Prep message content //
//////////////////////////
$data = explode(" ",$message_content);
/** 
  * ASSIGN VALUES: 
  * - # tests performed 
  * - # positive results 
  * - # patients who received treatment 
  * - # patients referred to the health center 
  **/
$realise = (int)$data[0];
$positif = (int)$data[1];
$traite  = (int)$data[2];
$refere  = (int)$data[3];

///////////////////////////////
//Count indexes in the array //
///////////////////////////////
$count = count($data);

//////////////////////////////////////
//Create code for each new data set //
//////////////////////////////////////
$code = date("ymd");

/////////////////////////////////
// Prepare validation messages //
/////////////////////////////////
$validation1 = 'Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique prédefinie pour PEC.';
$validation2 = 'Donnees non-valables. Veuillez ecrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$validation3 = 'Donnees non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$validation4 = 'Vous avez depasse la logique predefinie pour PEC. Veuillez consultez votre guide d\'utilisateur pour en savoir plus.';
$validation5 = 'Le nombre de cas positif ne devrais pas depasser le nombre de TDR realise. Veuillez contacter votre Animateur-Superviseur.';
$validation6 = 'Le nombre de cas traite ne devrais pas depasser le nombre de TDR positif. Veuillez contacter votre Animateur-Superviseur.';
$validation7 = 'Donnees non-valable. Veuillez contacter votre Animateur-Superviseur.';

/////////////////////////
// Perform validations //
/////////////////////////
/** 
  * NOTE: The goal is to eliminate common mistakes during data entry 
  * such as extra spaces, non-numeric characters, and additional array indexes 
  * exceeding the amount allowed by the keyword. We also need to verify that the 
  * data were entered in the correct order and that there are no logical contradictions. 
  * (e.x. One should not have more positive results than tests performed. So...)
  **/
if ($count < 4) {
	// Too few data fields submitted
	echo $validation1; 
	exit();
} elseif ($realise === '' || $positif === '' || $traite === '' || $refere === '') {
	// Additional empty space
	echo $validation2;	
	exit();
} elseif (!is_numeric($realise) || !is_numeric($positif) || !is_numeric($traite) || !is_numeric($refere)) {
	// Character not a number
	echo $validation3;
	exit();
} elseif ($count > 4) {
	// Too many data fields submitted
	echo $validation4;
	exit();
} elseif ($positif > $realise) {
	// Positive test results greater than number of tests performed: Contact supervisor
	echo $validation5;
	exit();
} elseif ($traite > $positif) {
	// Patients treated greater than number of positive test results: Contact supervisor
	echo $validation6;
	exit();
} else {
	// First round validations passed
}

if (($realise >= 0 && $realise <= 101)    && 
	($positif >= 0 && $positif <= 101)    && 
	($traite  >= 0 && $traite  <= 101)    && 
	($refere  >= 0 && $refere  <= 101)) {
		// Second round of validations passed
		
	} else {
		echo $validation7;
    	exit(); 	
	}


///////////////////////
// Connect using PDO //
///////////////////////
try {
$db_conn = new PDO('mysql:host=localhost;dbname=sms', 'stop_mchip','mypassword');
} catch (PDOException $e) {
$output = 'Unable to connect to the database server. '.$e->getMessage();
echo $output;
exit();
}

//////////////////////////////////////////////////////////////
// INSERT INTO 'SMS'.'PEC' repository upon final validation //
//////////////////////////////////////////////////////////////
try {		
	$stmt = $db_conn->prepare(
	"INSERT INTO sms.pec SET
	  sender_name   = :sender_name
	, sender_number = :sender_number
	, keyword       = :keyword
	, tdr_realise   = :realise
	, tdr_positif   = :positif
	, ptnt_traite   = :traite
	, ptnt_refere   = :refere
	, pec_code      = :code ");
	
	$stmt->bindValue(':sender_name', $sender_name);
	$stmt->bindValue(':sender_number', $sender_number);
	$stmt->bindValue(':keyword', $keyword);
	$stmt->bindValue(':realise', $realise);
	$stmt->bindValue(':positif', $positif);
	$stmt->bindValue(':traite', $traite);
	$stmt->bindValue(':refere', $refere);
	$stmt->bindValue(':code', $code);
	$stmt->execute();
} catch (PDOException $e) {
    $e->getMessage();
    exit();
}
///////////////////////////////////////////////
// Prepare reply messages for validated data //
///////////////////////////////////////////////
$message_chw_1 = 'Merci d\'envoyer vos donnees en prise en charge par SMS!';
$message_chw_2 = 'Vous avez deja soumis vos donnees en prise en charge ahourd\'hui. Vous pouvez soumettre a nouveau des demain. Merci!';

////////////////////////////////////////
// Get last inserted row ID && CHW ID //
////////////////////////////////////////
try {
	$lastInsertID = $db_conn->lastInsertId();
	echo 'This is the ID of the newly inserted row: '.$lastInsertID.'</br>';
	
	$chw = $db_conn->query(
	"SELECT chw_id
	 FROM chw
	 WHERE chw_phone = $sender_number");
	
	while($row = $chw->fetch()) {
		$chw_id = $row['chw_id'];
	    echo 'Community Health Worker ID: '.$row['chw_id'].'</br>';
	}	
} catch (PDOException $e) {
	$e->get_message();
	echo $e;
}	

//////////////////////////////////
// Select SMS code from SMS.PEC //
//////////////////////////////////
try {
	$select_code_from_pec = $db_conn->query(
	"SELECT pec_code
	 FROM pec
	 WHERE pec_id = $lastInsertID");
	
	while($row = $select_code_from_pec->fetch()){
		$pec_code = $row['pec_code'];
	}
} catch (PDOException $e) {
	$e->getMessage();
	echo $e;
}

//////////////////////////////////////////////////
// Find matching SMS code from SMS.PEC_VALIDATE //
//////////////////////////////////////////////////
try {
	// Returns PDO Statement Object
	$stmt = $db_conn->prepare(
	"SELECT pec_val_code
	 FROM pec_validate
	 WHERE sender_number
	 LIKE :sender_number 
	 AND pec_val_code 
	 LIKE :pec_code
	 LIMIT 1 ");
	$stmt->bindValue(':sender_number', $sender_number);
	$stmt->bindValue(':pec_code', $pec_code);
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	
	while ($row = $stmt->fetch()) {
		$pec_val_code = $row['pec_val_code'];
	}

} catch (PDOException $e) {
	$e->getMessage();
	echo $e;
}

///////////////////////////////////////////////
// Compare repository data to validated data //
///////////////////////////////////////////////
if(isset($pec_val_code)){
	// Data previously submitted
	echo $message_chw_2;
	exit();

} else {
	try {		
		$stmt = $db_conn->query(
		"INSERT INTO pec_validate SET
		  sender_name   = '$sender_name'
		, sender_number = '$sender_number'
		, tdr_realise   = '$realise'
		, tdr_positif   = '$positif'
		, ptnt_traite   = '$traite'
		, ptnt_refere   = '$refere'
		, pec_chw_id    = '$chw_id'
		, pec_page_id   = 6
		, pec_val_code  = '$code'");
		
	} catch (PDOException $e) {
		$e->getMessage();
		exit();
	}
	// New, validated data submitted
	echo $message_chw_1;
}

///////////////////////////////
// Update 'SMS'.'PEC_UPDATE' //
///////////////////////////////
try {
	// PEC_VALIDATE SELECT 
	$stmt = $db_conn->prepare(
			"SELECT SUM(tdr_realise), SUM(tdr_positif), SUM(ptnt_traite), SUM(ptnt_refere)
			 FROM pec_validate
			 WHERE pec_chw_id = :chw_id");
	$stmt->bindValue(':chw_id', $chw_id);
	$stmt->execute();
	$stmt->setFetchMode( PDO::FETCH_ASSOC );
	
	while($row = $stmt->fetch()) {
		$realise1  = $row['SUM(tdr_realise)'];
		$positif1  = $row['SUM(tdr_positif)'];
		$traite1   = $row['SUM(ptnt_traite)'];
		$refere1   = $row['SUM(ptnt_refere)'];
	}
	$stmt->closeCursor();
	
	// PEC_UPDATE SELECT
	$sql = "SELECT tdr_realise, tdr_positif, ptnt_traite, ptnt_refere
			FROM pec_update
			WHERE pec_chw_id = :chw_id";
	$stmt = $db_conn->prepare($sql);
	$stmt->bindValue(':chw_id', $chw_id);
	$stmt->execute();
	$stmt->setFetchMode( PDO::FETCH_ASSOC );
	
	while($row = $stmt->fetch()) {
		$realise2  = $row['tdr_realise'];
		$positif2  = $row['tdr_positif'];
		$traite2   = $row['ptnt_traite'];
		$refere2   = $row['ptnt_refere'];
	}
	$stmt->closeCursor();

	// Check to see if CHW has previously made a submission
	if(!isset($realise2)) {
		$realise2  = null;
		$positif2  = null;
		$traite2   = null;
		$refere2   = null;	
	}

	
} catch (PDOException $e) {
	echo $e->getMessage();
	exit();
}

////////////////////////////////////////////////////
// COMPARE PEC_VALIDATE.TABLE && PEC_UPDATE.TABLE //
////////////////////////////////////////////////////
if ($realise2 === null) {
	// First time submission
	// INSERT INTO sms.pec_update
	$sql = "INSERT pec_update SET
			  tdr_realise = {$realise1}
			, tdr_positif = {$positif1}
			, ptnt_traite = {$traite1}
			, ptnt_refere = {$refere1}
			, pec_chw_id  = {$chw_id}";
	try {
		$stmt = $db_conn->prepare($sql);
		$stmt->execute();
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit();
	}
	
} else {
	// Run UPDATE query
	$sql = "UPDATE pec_update SET
			  tdr_realise = {$realise1}
			, tdr_positif = {$positif1}
			, ptnt_traite = {$traite1}
			, ptnt_refere = {$refere1}
			, pec_chw_id  = {$chw_id} ";
	try {
		$stmt = $db_conn->prepare($sql);
		$stmt->execute();
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit();
	}
	
}

