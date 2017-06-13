<?php

//Connect using PDO

try
{
$db_conn = new PDO('mysql:host=localhost;dbname=sms', 'tester','mypassword');
echo 'CONNECTED! </br>';
}
catch (PDOException $e)
{
$output = 'Unable to connect to the database server. '.$e->getMessage();
echo $output;
exit();
}

// Insert data into the database
if ($n >= 0 and $n <=101 
and $pe >= 0 and $pe <= 101
and $e >= 0 and $e <=101
and $a >= 0 and $a <= 101
and $tdr >= 0 and $tdr <=101) 
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
	
	echo 'DATA INSERTED SUCCESSFULLY </br>';
}
catch (PDOException $e)
{
	$e->getMessage();
	exit();
}
else
{
	echo $message_chw_4;
	exit();
}


// PART II: 

$code = date("ymd");
echo 'Here is the message code: '.$code.'</br>'; 

try
{	
	$select = $db_conn->query(
	"SELECT tdr_realise, tdr_positif, ptnt_traite, ptnt_refere, code
	FROM pec
	WHERE sender_number = '+224000000000'
	AND code = '$code'");
}
catch (PDOException $e)
{
	$e->getmessage();
	echo $e;
	exit();
}

while($row = $select->fetch()){
	$tdr_realise = $row['tdr_realise'];
	$tdr_positif = $row['tdr_positif'];
	$ptnt_traite = $row['ptnt_traite'];
	$ptnt_refere = $row['ptnt_refere'];

}
if(isset($tdr_realise) 
and isset($tdr_positif) 
and isset($tdr_traite) 
and isset($tdr_refere)){
	echo 'HAY';
	exit();
}

//

if(isset($tdr_realise) 
and isset($tdr_positif) 
and isset($tdr_traite) 
and isset($tdr_refere)){
	if($realise == $tdr_realise
	and $positif == $tdr_positif
	and $traite == $ptnt_traite
	and $refere == $ptnt_refere)
	{
		echo 'shit!';
		exit();
	}
	else
	{
		echo 'FUCK!';
		exit();
	}
}

