<?php
/*
(17:26:43) Danila Shtan: Ваш маркер API: Xp4IgBmutYY0QzFp6blXLtHQRRtH4j0vANhLoRQD - храните его и никому не сообщайте
(17:27:06) stenlex@gmail.com/9C6D2164: спасибо
(17:27:10) Danila Shtan: только это вроде как мой личный ключ
(17:27:20) Danila Shtan: 
curl -u danila@shtan.ru/token:YOUR_TOKEN https://mediasite.zendesk.com/users/current.json
*/
define("ZDAPIKEY", "Xp4IgBmutYY0QzFp6blXLtHQRRtH4j0vANhLoRQD");
define("ZDUSER", "stenlex@gmail.com");
define("ZDURL", "https://mediasite.zendesk.com/api/v2");
 
/* Note: do not put a trailing slash at the end of v2 */
 
function curlWrap($url, $json, $action)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
	curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);
	switch($action){
		case "POST":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
		case "GET":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			break;
		case "PUT":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
		case "DELETE":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
		default:
			break;
	}
 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($ch);
	var_dump($output);
	curl_close($ch);
	$decoded = json_decode($output);
	return $decoded;
}
echo '!';
$data = curlWrap("/tickets.json", null, "GET");
echo ' ! !';
print_r($data);
?> 
