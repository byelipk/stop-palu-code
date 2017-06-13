<?php

/* PART I */

$sender_name = $_REQUEST['sender_name'];
$sender_number = $_REQUEST['sender_number'];
$keyword = $_REQUEST['keyword'];
$message_content = $_REQUEST['message_content'];


$phoneNum = substr($sender_number,1);


$data = explode(" ",$message_content);
$vad = $data[0];
$hommes = $data[1];
$femmes = $data[2];
$total = $hommes + $femmes;


$message1 = rawurlencode('Merci d\'avoir soumis vos donnees en visite a domicile par SMS!');
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

/* PART II */

if (is_numeric($vad) and $vad >= 0 and $vad <=101 
and is_numeric($hommes) and $hommes >= 0 and $hommes <= 101
and is_numeric($femmes) and $femmes >= 0 and $femmes <=101) 
{
	$stmt = $db_conn->query(
	"INSERT INTO vad SET
	sender_name = '$sender_name',
	sender_number = '$sender_number',
	keyword = '$keyword',
	vad_total = '$vad',
	hommes = '$hommes',
	femmes = '$femmes',
	total = '$total'");
	
        $url="http://localhost:8011/send/sms/".$sender_number."/".$message1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
}
else
{
        $url="http://localhost:8011/send/sms/".$sender_number."/".$message2."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
	exit();
}

?>
