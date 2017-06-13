<?php

/* PART I - C'est ici a ce niveau ou on fait les preparatifs tels que
le traitment de contenu du message, la preparation de chaqune des reponses,
et la connection a la base de donnees. */

// On fixe les variables en utilisant les valeurs suivantes vennant de FrontlineSMS
$sender_name = $_REQUEST['sender_name'];
$sender_number = $_REQUEST['sender_number'];
$keyword = $_REQUEST['keyword'];
$message_content = $_REQUEST['message_content'];

// On traite le numero de telephone d'AC
$chw_phone = substr($sender_number,1);

// On prepare le contenu de $message_content et mets chaqune des valeurs dans une variable unique
$data = explode(" ",$message_content);
$n = $data[0];
$pe = $data[1];
$e = $data[2];
$a = $data[3];
$tdr = $data[4];

// Preparer les reponses
$message1 = rawurlencode('Merci, '.$sender_name.'! Vous avez mis a jour votre stock en ASAQ/TDR.');
$message2 = rawurlencode('Donnees non-valable. Veuillez les verifier et soumettre a nouveau.');
$message3 = rawurlencode('Ravitaillement necessaire. Veuillez contacter '.$sender_name.' a '.$chw_phone.'. [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']');
$message4 = rawurlencode('Stock en ASAQ/TDR mis a jour par '.$sender_name.'. [Nourrisson = '.$n.' Petit Enfant = '.$pe.' Enfant = '.$e.' Adulte = '.$a.' TDR = '.$tdr.']');

// Pour se connecter a la base de donnees
if (isset($_REQUEST['sender_name']))

	try
	{
		$db_conn = new PDO('mysql:host=localhost;dbname=sms','tester','mypassword');
	}
	catch (PDOException $e)
	{
		echo 'Error connecting to the database: '. $e->getMessage();
		exit();
	}


/* PART II - C'est a la deuxieme partie ou on declenche 
le SQL pour mettre le contenue du message dans la base de
donnees apres avoir fiabilise les valeurs. Ensuite, on envoie 
une reponse au envoyeur */

// Pour fiabiliser les valeurs avant de les mettre dans la base de donnees.
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
	
/* On prend la cle primaire de cette nouvelle entree.
On aura besoin de ca plus tard. */
	$id = $db_conn->lastInsertId();

// Un message de bonne reception
	
        $url="http://localhost:8011/send/sms/".$chw_phone."/".$message1."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
}

// Au cas ou les donnees etaient invalides
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


/* PART III - A ce niveau on prepare un message d'alert
pour le chef de centre de sante */  

// Selectionner le numero de telephone de CS qui correspond au numero de telephone d'AC
	$stmt = $db_conn->query(
	"SELECT hcPhone
	FROM health_center
	INNER JOIN chw
	ON centerID = health_center.id
	INNER JOIN asaq
	ON sender_number = chw_phone
	WHERE asaq.id = $id");
	while($row = $stmt->fetch()) {
        $hc_phone = substr($row['hcPhone'],1);
 
if ((
is_numeric($n) and $n >= 0 and $n <=5) 
or (is_numeric($pe) and $pe >= 0 and $pe <=5)
or (is_numeric($e) and $e >= 0 and $e <= 5)
or (is_numeric($a) and $a >= 0 and $a <= 5)
or (is_numeric($tdr) and $tdr >= 0 and $tdr <= 5)) 

// Ravitaillement necessaire
{
	$url="http://localhost:8011/send/sms/".$hc_phone."/".$message3."/";
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,$url);
        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
        $output=curl_exec($curl_handle);
        curl_close($curl_handle);
}

// Mis a jour normal
else
{
	$url="http://localhost:8011/send/sms/".$hc_phone."/".$message4."/";
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
	$output=curl_exec($curl_handle);
	curl_close($curl_handle);
}}

?>
