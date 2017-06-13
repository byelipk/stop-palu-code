<?php

// Sets the variables coming from FrontlineSMS
$sender_name = 'Patrick';
$sender_number = '+224000000000';
$keyword = 'ASAQ';
$message_content = '1 9 9 9 9';
$chw_phone = substr($sender_number, 1);

// Parse message content
$data = explode(" ",$message_content);
$n1 = $data[0];
$pe1 = $data[1];
$e1 = $data[2];
$a1 = $data[3];
$tdr1 = $data[4];

$n2 = '4';
$pe2 = '9';
$e2 = '9';
$a2 = '9';
$tdr2 = '9';

$message1 = 'Already updated';
$message2 = 'Already updated; health center has already been notified!';
$message3 = 'Health center has been notified';
$message4 = 'Porto';
$message5 = 'DanTheMan';

// Compare asaq and asaq_update tables


// If newly submitted dataset equals hosted dataset AND the entire dataset is above the alert level
if(($n1 == $n2) and ($pe1 == $pe2) and ($e1 == $e2) and ($a1 == $a2) and ($tdr1 == $tdr2)){
	if(($n1 > 5) and ($pe1 > 5) and ($e1 > 5) and ($a1 > 5) and ($tdr1 > 5))
	{
		echo $message1;
		exit();
	}
// If the same dataset is re-sent AND the dataset warranted an alert, then we notify the CHW that an alert was already sent
	else
	{
		echo $message2;
		exit();
	}
}

// If at least one of the data points is below the alert level, then we notify the CHW that the alert has been sent
elseif(($n1 != $n2) or ($pe1 != $pe2) or ($e1 != $e2) or ($a1 != $a2) or ($tdr1 != $tdr2)){
	if(($n1 <= 5) or ($pe1 <= 5) or ($e1 <= 5) or ($a1 <= 5) or ($tdr1 <= 5))
	{
		echo $message3;
		exit();
	}
	else
	{
		echo $message4;
	    exit();
	}
}


