<?php

// Request information coming from FrontlineSMS and assign them to variables
$sender_name     = 'Aissata';
$sender_number   = '+224000000000';
$keyword         = 'ASAQ';
$message_content = htmlentities('25 25 25 25 50');

// Treat phone number
$chw_phone = substr($sender_number, 1);
//$chw_phone = $sender_number;

// Parse message content
$data = explode(" ",$message_content);

/* The variables correspond to the 4 forms of malaria medication available plus the rapid diagnostic test: Nourrisson, Petit Enfant, Enfant, Adult, Test de Diagnostic Rapide */
$n   = (int)$data[0];
$pe  = (int)$data[1];
$e   = (int)$data[2];
$a   = (int)$data[3];
$tdr = (int)$data[4];

// Prepare validation messages
$validation1 = 'Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique predefinie pour ASAQ.';
$validation2 = 'Donnees non-valables. Veuillez ecrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$validation3 = 'Donnees non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$validation4 = 'Vous avez depasse la logique predefinie pour ASAQ. Veuillez consultez votre guide d\'utilisateur pour en savoir plus.';

/* First round of validation. The goal is to eliminate common mistakes during data entry into the mobile handset such as extra spaces, non-numeric characters, and the number of array indexes not corresponding to the amount allowed by the keyword. */
$count = count($data);

if($count < 5){
	// Too few data fields submitted
	echo $validation1; 
	exit();
} elseif($n === '' || $pe === '' || $e === '' || $a === '' || $tdr === '') {
	// Additional empty space
	echo $validation2;	
	exit();
} elseif(!is_numeric($n) || !is_numeric($pe) || !is_numeric($e) || !is_numeric($a) || !is_numeric($tdr)) {
	// Character not a number
	echo $validation3;
	exit();
} elseif($count > 5) {
	// Too many data fields submitted
	echo $validation4;
	exit();
} else {
	// Initial validation passed
}

// Connect using PDO
try {
$db_conn = new PDO('mysql:host=localhost;dbname=sms', 'tester','mypassword');
} catch (PDOException $e) {
$output = 'Unable to connect to the database server.';
echo $output;
exit();
}

// CHW Responses
$message_chw_1 = 'Merci, '.$sender_name.'! Vous avez mis a jour votre votre stock en ASAQ/TDR. Ca vous reste les quantites suivantes: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']';
$message_chw_2 = 'Merci, '.$sender_name.'! Vous avez deja mis a jour votre quantite restante en ASAQ/TDR. Votre centre de sante a ete notifie de vous ravitailler.';
$message_chw_3 = 'Vous avez deja mis a jour votre quantite restante en ASAQ/TDR: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']';
$message_chw_4 = 'Donnees non-valable. Veuillez les soumettre a nouveau selon la logique predefinie. Consultez votre guide d\'utilisateur pour en savoir plus.';
$message_chw_5 = $sender_name.', vous avez atteint le seuil d\'alert. Votre centre de sante a ete notifie. Ca vous reste les quantites suivantes: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']';

// CHW CODING
// Insert data into the database upon final validation
if (($n   >= 0 && $n   <= 101)   && 
	($pe  >= 0 && $pe  <= 101)   && 
	($e   >= 0 && $e   <= 101)   && 
	($a   >= 0 && $a   <= 101)   && 
	($tdr >= 0 && $tdr <= 101)) 
try {		
	$stmt = $db_conn->query(
	"INSERT INTO asaq SET
	  sender_name   = '$sender_name'
	, sender_number = '$sender_number'
	, keyword       = '$keyword'
	, nourrisson    = '$n'
	, petit_enfant  = '$pe'
	, enfant        = '$e'
	, adulte        = '$a'
	, tdr           = '$tdr'");
	
	// USE FOR DEBUGGING
	echo '<strong>1. DATA INSERTED SUCCESSFULLY!</strong><br></br>';
} catch (PDOException $e) {
	$e->getMessage();
	exit();
} else {
	// Error message: CHW's should have 25-50 units of TDR/ASAQ. 
	// If CHW has more than 101 in stock, CHW should contact supervisor
	echo $message_chw_4;
	exit();
}

// Get the ID of the last inserted row and the CHW ID 
try {
	$lastInsertID = $db_conn->lastInsertId();
	// USE FOR DEBUGGING
	echo '<strong>2. This is the ID of the newly inserted row:</strong> '.$lastInsertID.'<br></br>';
	
	$chw = $db_conn->query(
	"SELECT chw_id
	 FROM chw
	 WHERE chw_phone = $sender_number");
	
	while($row = $chw->fetch()) {
		$chwID = $row['chw_id'];
		
		// USE FOR DEBUGGING
	    echo '<strong>3. This is the CHWID:</strong> '.$chwID.'<br></br>';
	}	
} catch (PDOException $e) {
	$e->get_message();
	echo $e;
}
	
//	Pull out data from asaq.table and from asaq_update.table
try {	
	$select1 = $db_conn->query(
	"SELECT nourrisson, petit_enfant, enfant, adulte, tdr
	 FROM asaq
	 WHERE id = $lastInsertID");
	
	while($row = $select1->fetch()) {
		$n1   = $row['nourrisson'];
		$pe1  = $row['petit_enfant'];
		$e1   = $row['enfant'];
		$a1   = $row['adulte'];
		$tdr1 = $row['tdr'];
	}
	
	// USE FOR DEBUGGING	
	echo '<strong>4. Results from asaq.table:</strong> [Nourrisson = '.$n1.' Petit Enfant = '.$pe1.' Enfant = '.$e1.' Adulte = '.$a1.' TDR = '.$tdr1.']<br></br>';
		
	$select2 = $db_conn->query(
	"SELECT nourrisson, petit_enfant, enfant, adulte, tdr
	 FROM asaq_update
	 WHERE chwID = '$chwID'");
	
	while($row = $select2->fetch()) {
		$n2   = $row['nourrisson'];
		$pe2  = $row['petit_enfant'];
		$e2   = $row['enfant'];
		$a2   = $row['adulte'];
		$tdr2 = $row['tdr'];
	}
	
	// Check to see if CHW has previously made a submission
	if(!$n2) {
		$n2   = null;
		$pe2  = null;
		$e2   = null;
		$a2   = null;
		$tdr2 = null;	
	}
	
	// USE FOR DEBUGGING
	echo '<strong>5. Results from asaq_update.table:</strong> [Nourrisson = '.$n2.' Petit Enfant = '.$pe2.' Enfant = '.$e2.' Adulte = '.$a2.' TDR = '.$tdr2.']</br><br>';			
} catch (PDOException $e) {
	$e->getMessage();
	echo $e;
}

// COMPARE asaq.table AND asaq_update.table
// If this is a first-time submission...
if ($n2 === null) {
	try {
		// Insert dataset into asaq_update.table	
		$update = $db_conn->query(
		"INSERT INTO asaq_update SET
		  nourrisson   = '$n1'
		, petit_enfant = '$pe1'
		, enfant       = '$e1'
		, adulte       = '$a1'
		, tdr          = '$tdr1'
		, chwID        = '$chwID'");
	} catch (PDOException $e) {
		$e->getMessage();
		echo $e;
	}
	// AND If at least one of the data points is below the alert level...
	if (($n1 <= 5) || ($pe1 <= 5) || ($e1 <= 5) || ($a1 <= 5) || ($tdr1 <= 5)) {
		// ... notify CHW that an alert has been sent to the health center
		echo '[SMS 1] </br>';
		echo '</br>';  
		echo "<strong>7B. CHW submit ALERT:</strong> ". $message_chw_5 . "<br><br>";
		
	} else {
		// ... notify CHW of a valid, normal submission
		echo '[SMS 1] </br>';
		echo '</br>';  
		echo "<strong>7B. CHW submit NORMAL:</strong> ". $message_chw_1 . "<br><br>";
	}

// If newly submitted dataset equals hosted dataset... 		
} elseif (($n1 == $n2) && ($pe1 == $pe2) && ($e1 == $e2) && ($a1 == $a2) && ($tdr1 == $tdr2)) {
	// ... AND IF the entire dataset is above the alert level...
	if (($n1 > 5) && ($pe1 > 5) && ($e1 > 5) && ($a1 > 5) && ($tdr1 > 5)) {
		// ... notify CHW that stock levels were already updated
		echo '[SMS 1] </br>';
		echo '</br>';		
		echo "<strong>6A. CHW resubmission NORMAL</strong>: " . $message_chw_3 . "<br><br>";
		exit();	
	} else {
		// ... notify CHW that a re-supply alert has already been sent to health center
		echo '[SMS 1] </br>';
	    echo '</br>';
		echo "<strong>6B. CHW resubmission ALERT</strong>: " . $message_chw_2 . "<br><br>";
		exit();	
	}

// If CHW submitted a new dataset... 
} elseif (($n1 !== $n2) || ($pe1 !== $pe2) || ($e1 !== $e2) || ($a1 !== $a2) || ($tdr1 !== $tdr2)) {
	try {
		// Update asaq_update.table	
		$update = $db_conn->query(
		"UPDATE asaq_update SET
		  nourrisson   = '$n1'
		, petit_enfant = '$pe1'
		, enfant       = '$e1'
		, adulte       = '$a1'
		, tdr          = '$tdr1'
		WHERE chwID    = '$chwID'");		
	} catch (PDOException $e) {
	  $e->getMessage();
	  echo $e;
	}
	// AND IF at least one of the data points is below the alert level...
	if (($n1 <= 5) || ($pe1 <= 5) || ($e1 <= 5) || ($a1 <= 5) || ($tdr1 <= 5)) {
		// ... notify CHW that an alert has been sent to the health center
		echo '[SMS 1] </br>';
		echo '</br>';  
		echo "<strong>7B. CHW submit ALERT:</strong> ". $message_chw_5 . "<br><br>";
	} else {
		// ... notify CHW of a valid, normal submission
		echo '[SMS 1] </br>';
		echo '</br>';  
		echo "<strong>7B. CHW submit Normal:</strong> ". $message_chw_1 . "<br><br>";
	}
} else {
	
}

// PART III: Health Center Coding
// HEALTH CENTER RESPONSES
$message_hc_1 = 'Ravitaillement necessaire. Veuillez contacter '.$sender_name.' a '.$chw_phone.'. [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']';
$message_hc_2 = 'Stock en ASAQ/TDR mis a jour par '.$sender_name.'. Ca lui reste les quantites suivantes: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']';

$stmt = $db_conn->query(
"SELECT hc_phone
 FROM health_center
 INNER JOIN chw
 ON chw_hc_id = health_center.hc_id
 INNER JOIN asaq
 ON sender_number = chw.chw_phone
 WHERE asaq.id = $lastInsertID"); 

while($row = $stmt->fetch()) {
	// Select health center director phone number	
	$hcPhone = substr($row['hc_phone'],1);
}	

// If at least one of the data points is below the alert level...
if (($n1 <= 5) || ($pe1 <= 5) || ($e1 <= 5) || ($a1 <= 5) || ($tdr1 <= 5)) {
	echo '[SMS 2] </br>';
	echo '</br>';
	echo "<strong>8A. Health Center notification ALERT:</strong> " . $message_hc_1 . "<br></br>";
	echo '<strong>A notification will be sent to this phone number:</strong> '.$hcPhone.'</br><br>';
} else {
	echo '[SMS 2] </br>';
	echo '</br>';
	echo "<strong>8B. Health Center notification NORMAL:</strong> " . $message_hc_2 . "</br><br>";
	echo '<strong>A notification will be sent to this phone number:</strong> '.$hcPhone.'</br><br>';
}