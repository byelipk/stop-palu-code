<?php

/* PART I */

 
$sender_name = $_REQUEST['sender_name'];
$sender_number = $_REQUEST['sender_number'];
$keyword = $_REQUEST['keyword'];
$message_content = $_REQUEST['message_content'];


$chw_phone = substr($sender_number,1);


$data = explode(" ",$message_content);
$n = $data[0];
$pe = $data[1];
$e = $data[2];
$a = $data[3];
$tdr = $data[4];


$message1 = rawurlencode('Merci, '.$sender_name.'! Vous avez mis a jour votre stock en ASAQ/TDR.');
$message2 = rawurlencode('Donnees non-valable. Veuillez les verifier et soumettre a nouveau.');
$message3 = rawurlencode('Ravitaillement necessaire. Veuillez contacter '.$sender_name.' a '.$chw_phone.'. [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']');
$message4 = rawurlencode('Stock en ASAQ/TDR mis a jour par '.$sender_name.'. [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']');

 
if (isset($_REQUEST['sender_name']))

	try
	{
		$db_conn = new PDO('mysql:host=localhost;dbname=sms','tester','mypassword');
	}
	catch (PDOException $e)
	{
		$e->getMessage();
		
		$url="http://localhost:8011/send/sms/".$phoneNum."/".$e."/";
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		$output=curl_exec($curl_handle);
		curl_close($curl_handle); 
		
		exit();
	}


/* PART II */


if (is_numeric($n) and $n >= 0 and $n <=101 
and is_numeric($pe) and $pe >= 0 and $pe <= 101
and is_numeric($e) and $e >= 0 and $e <=101
and is_numeric($a) and $a >= 0 and $a <= 101
and is_numeric($tdr) and $tdr >= 0 and $tdr <=101) 
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
	
	$id = $db_conn->lastInsertId();

    $url="http://localhost:8011/send/sms/".$chw_phone."/".$message1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
}
else
{
    $url="http://localhost:8011/send/sms/".$chw_phone."/".$message2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}


/* PART III */  

	$stmt = $db_conn->query(
	"SELECT hcPhone
	FROM health_center
	INNER JOIN chw
	ON centerID = health_center.id
	INNER JOIN asaq
	ON sender_number = chw_phone
	WHERE asaq.id = $id");
	
	while($row = $stmt->fetch()) 
    $hc_phone = substr($row['hcPhone'],1);
 
if ((
is_numeric($n) and $n >= 0 and $n <=5) 
or (is_numeric($pe) and $pe >= 0 and $pe <=5)
or (is_numeric($e) and $e >= 0 and $e <= 5)
or (is_numeric($a) and $a >= 0 and $a <= 5)
or (is_numeric($tdr) and $tdr >= 0 and $tdr <= 5))
{
	$url="http://localhost:8011/send/sms/".$hc_phone."/".$message3."/";
    $curl_handle=curl_init();
    curl_setopt($curl_handle,CURLOPT_URL,$url);
    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
    $output=curl_exec($curl_handle);
    curl_close($curl_handle);
}
else
{
	$url="http://localhost:8011/send/sms/".$hc_phone."/".$message4."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
}

?>
