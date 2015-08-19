<?php

// This file is generated by Composer
require_once '../vendor/autoload.php';

$client = new Github\Client();
$client->authenticate('user', 'password', 'http_password');

$commits = $client->api('repo')->commits()->all('organization', 'repo', array('sha' => 'master'));
var_dump($commits);
//$repositories = $client->api('user')->repositories('ornicar');
?>
