<?php


$sender_name = $_REQUEST['sender_name'];
$sender_number = $_REQUEST['sender_number'];
$keyword = $_REQUEST['keyword'];
$message_content = $_REQUEST['message_content'];


$phoneNum = substr($sender_number,1);

$data = explode(" ",$message_content);
$realise = $data[0];
$positif = $data[1];
$traite = $data[2];
$refere = $data[3];


$message1 = rawurlencode('Merci d\'avoir soumis vos donnees en prise en charge par SMS!');
$message2 = rawurlencode('Donnees non-valable. Veuillez les verifier et soumettre a nouveau.');



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



if (is_numeric($n) and $n >= 0 and $n <=101 
and is_numeric($pe) and $pe >= 0 and $pe <= 101
and is_numeric($e) and $e >= 0 and $e <=101
and is_numeric($a) and $a >= 0 and $a <= 101
and is_numeric($tdr) and $tdr >= 0 and $tdr <=101) 
{
	$send = "INSERT INTO pec SET
	sender_name = '$sender_name',
	sender_number = '$sender_number',
	keyword = '$keyword',
	tdr_realise = '$realise',
	tdr_positif = '$positif',
	ptnt_traite = '$traite',
	ptnt_refere = '$refere'";
	
    $url="http://localhost:8011/send/sms/".$phoneNum."/".$message1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
}
else
{
    $url="http://localhost:8011/send/sms/".$phoneNum."/".$message2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

?>
