<?php

// FIX MESSAGES!!!

// Sets the variables coming from FrontlineSMS
$sender_name = 'Patrick';
$sender_number = '+224000000000';
$keyword = 'ASAQ';
$message_content = '25  25 25 23 6';

$chw_phone = substr($sender_number, 1);

// Parse message content
$data = explode(" ",$message_content);
$n = $data[0];
$pe = $data[1];
$e = $data[2];
$a = $data[3];
$tdr = $data[4];

// Prepare first round of validation
$message0 = 'Il y a des informations manquantes. Consultez votre guide d\'utilisateur pour savoir la logique predefinie pour ASAQ.';
$message00 = 'Donnees non-valables. Veuillez ecrire votre SMS en utilisant qu\'un seul espace entre les chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$message000 = 'Donnees non-valables. Veuillez remplir votre SMS en utilisant uniquement des chiffres. Consultez votre guide d\'utilisateur pour en savoir plus.';
$message0000 = 'Vous avez depasse la logique predefinie pour ASAQ. Veuillez consultez votre guide d\'utilisateur pour en savoir plus.';

$result = count($data);

if($result < 5){
	echo $message0;
	echo 'ONE';
	exit();
} elseif(empty($n) and $n !=  0 || empty($pe) and $pe != 0 || empty($e) and $e != 0 || empty($a) and $a != 0 || empty($tdr) and $tdr != 0){
	echo $message00;
	echo 'TWO';
	exit();
}elseif(!is_numeric($n) || !is_numeric($pe) || !is_numeric($e) || !is_numeric($a) || !is_numeric($tdr)){
	echo $message000;
	echo 'THREE';
	exit();
}elseif($result > 5){
	echo $message0000;
	echo 'FOUR';
	exit();
}

// CHW RESPONSES
$message1 = 'Merci, '.$sender_name.'! Vous avez mis a jour votre quantite restante en ASAQ/TDR. Ca vous reste les quantites suivantes: [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']';
$message2 = 'Merci, '.$sender_name.'! Vous avez deja mis a jour votre quantite restante en ASAQ/TDR et votre centre de sante a ete informe.';
$message3 = 'Vous avez deja mis votre quantite restante en ASAQ/TDR a jour. Merci, '.$sender_name.'!';
$message4 = 'Donnees non-valable. Veuillez les soumettre a nouveau selon la logique predefinie. Consultez votre guide d\'utilisateur pour en savoir plus.';

// Connect using PDO
try
{
$db_conn = new PDO('mysql:host=localhost;dbname=sms', 'tester','mypassword');
}
catch (PDOException $e)
{
$output = 'Unable to connect to the database server.';
echo $output;
exit();
}

// Insert data into the database
if (is_numeric($n) && $n >= 0 && $n <=101 
&& is_numeric($pe) && $pe >= 0 && $pe <= 101
&& is_numeric($e) && $e >= 0 && $e <=101
&& is_numeric($a) && $a >= 0 && $a <= 101
&& is_numeric($tdr) && $tdr >= 0 && $tdr <=101) 
try
{		
	$stmt = $db_conn->query(
	"INSERT INTO asaq SET
	sender_name = '$sender_name',
	sender_number = '$sender_number',
	keyword = '$keyword',
	nourrisson = '$n',
	petit_enfant = '$pe',
	enfant = '$e',
	adulte = '$a',
	tdr = '$tdr'");
}
catch (PDOException $e)
{
	$e->getMessage();
	exit();
}
else
{
	echo $message4;
	exit();
}


// Get the ID of the last inserted row && the CHW ID 
try
{
	$lastInsertID = $db_conn->lastInsertId();
	echo 'This is the ID of the newly inserted row: '.$lastInsertID.'</br>';
	
	$chw = $db_conn->query(
	"SELECT chw.id
	FROM chw
	WHERE chw_phone = $sender_number");
	
	while($row = $chw->fetch()) {
		$chwID = $row['id'];
	    echo 'This is the ID of the CHW: '.$row['id'].'</br>';}
		
}
catch (PDOException $e)
{
	$e->get_message();
	echo $e;
}
	
//	Pull out data from asaq && from asaq_update tables
try
{	
	$select1 = $db_conn->query(
	"SELECT nourrisson, petit_enfant, enfant, adulte, tdr
	FROM asaq
	WHERE id = $lastInsertID");
	
	while($row = $select1->fetch()) {
		$n1 = $row['nourrisson'];
		$pe1 = $row['petit_enfant'];
		$e1 = $row['enfant'];
		$a1 = $row['adulte'];
		$tdr1 = $row['tdr'];}
		
	echo 'Results from asaq: [Nourrisson = '.$n1.' Petit Enfant = '.$pe1.' Enfant = '.$e1.' Adulte = '.$a1.' TDR = '.$tdr1.']</br>';
		
	$select2 = $db_conn->query(
	"SELECT nourrisson, petit_enfant, enfant, adulte, tdr
	FROM asaq_update
	WHERE chwID = '$chwID'");
	
	while($row = $select2->fetch()) {
		$n2 = $row['nourrisson'];
		$pe2 = $row['petit_enfant'];
		$e2 = $row['enfant'];
		$a2 = $row['adulte'];
		$tdr2 = $row['tdr'];}	
	
	echo 'Results from asaq_update: [Nourrisson = '.$n2.' Petit Enfant = '.$pe2.' Enfant = '.$e2.' Adulte = '.$a2.' TDR = '.$tdr2.']</br>';			
}
catch (PDOException $e)
{
	$e->getMessage();
	echo $e;
}

// Compare asaq && asaq_update tables
if($n1 == $n2 && $n2 >=0 && $n2 <=5
|| $pe1 == $pe2 && $pe2 >=0 && $pe2 <=5
|| $e1 == $e2 && $e2 >=0 && $e2 <=5
|| $a1 == $a2 && $a2 >=0 && $a2 <=5
|| $tdr1 == $tdr2 && $tdr2 >=0 && $tdr2 <=5)
{
	echo $message2;
	exit();
}
elseif($n1 == $n2
&& $pe1 == $pe2
&& $e1 == $e2
&& $a1 == $a2
&& $tdr1 == $tdr2)
{
	echo $message3;
	exit();
}
else 
{
 try
  {
	  $update = $db_conn->query(
	  "UPDATE asaq_update SET
	  nourrisson = '$n1',
	  petit_enfant = '$pe1',
	  enfant = '$e1',
	  adulte = '$a1',
	  tdr = '$tdr1'
	  WHERE chwID = '$chwID'");
	  
	  $select3 = $db_conn->query(
	  "SELECT nourrisson, petit_enfant, enfant, adulte, tdr
	  FROM asaq_update
	  WHERE chwID = '$chwID'");
	
	  while($row = $select3->fetch()) {
		$n3 = $row['nourrisson'];
		$pe3 = $row['petit_enfant'];
		$e3 = $row['enfant'];
		$a3 = $row['adulte'];
		$tdr3 = $row['tdr'];}
	  
	  echo $message1;
  }
  catch (PDOEXception $e)
  {
	  $e->getMessage();
	  echo $e;
}}


// HEALTH CENTER RESPONSES
$message6 = 'Ravitaillement necessaire. Veuillez contacter '.$sender_name.' a '.$chw_phone.'. [Nourrisson = '.$n3.' Petit Enfant = '.$pe3.' Enfant = '.$e3.' Adulte = '.$a3.' TDR = '.$tdr3.']';
$message7 = 'Stock en ASAQ/TDR mis a jour par '.$sender_name.'. Ca lui reste les quantites suivantes: [Nourrisson = '.$n3.' Petit Enfant = '.$pe3.' Enfant = '.$e3.' Adulte = '.$a3.' TDR = '.$tdr3.']';


// PART III: Health Center Side Coding

$stmt = $db_conn->query(
"SELECT hcPhone
FROM health_center
INNER JOIN chw
ON centerID = health_center.id
INNER JOIN asaq
ON sender_number = chw_phone
WHERE asaq.id = $lastInsertID"); 

while($row = $stmt->fetch()) {
$hcPhone = substr($row['hcPhone'],1);


if ((
is_numeric($n3) && $n3 >= 0 && $n3 <=5) 
|| (is_numeric($pe3) && $pe3 >= 0 && $pe3 <=5)
|| (is_numeric($e3) && $e3 >= 0 && $e3 <= 5)
|| (is_numeric($a3) && $a3 >= 0 && $a3 <= 5)
|| (is_numeric($tdr3) && $tdr3 >= 0 && $tdr3 <= 5)) 
{
	echo '[SMS 2] </br>';
	echo '</br>';
	echo $message6.'</br>';
	
	echo 'A notification will be sent to this phone number: '.$hcPhone.'</br>';
}
else
{
	echo '[SMS 2] </br>';
	echo '</br>';
	echo $message7.'</br>';
	echo 'A notification will be sent to this phone number: '.$hcPhone.'</br>';
}}
?>