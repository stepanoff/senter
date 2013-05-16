<?php
$params = array();
$localConfigFile = dirname(__FILE__).DS.'../../localConfig/params.php';
$localDistConfigFile = dirname(__FILE__).DS.'../../localConfig/params-dist.php';
if (file_exists($localDistConfigFile))
	$localDistConfig = require($localDistConfigFile);
else
	die('local config-dist doesn`t exists at '.$localDistConfigFile."\n");
if (file_exists($localConfigFile))
	$localConfig = require($localConfigFile);
else
	die('local config doesn`t exists at '.$localConfigFile."\n");
$params = array_replace_recursive ($localDistConfig, $localConfig);
$emptyKeys = array();
foreach ($params as $k=>$v)
{
	if (is_string($v) && empty($v))
		$emptyKeys[] = $k;
}
if (sizeof($emptyKeys))
{
	echo "Error: params\n".implode("\n", $emptyKeys)."\nrequired";
	die();
}

return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>$params['appName'],
    'runtimePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data',
    'language' => 'ru',
    'commandMap' => array(
//        'mailsend'                  => $extDir . DS . 'mailer' . DS . 'MailSendCommand.php',
    ),

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
        'application.models.*',
		'application.extensions.*',
    	'application.helpers.*',
		'application.widgets.*',
    ),

	// application components
	'components'=>array(
        'urlManager'=>require(dirname(__FILE__).'/urlManager.php'),
        
        'cache' => array(
			'class' => 'CFileCache',
			'cachePath' => ROOT_PATH. DS . 'protected' . DS . 'runtime' . DS . 'cache',
		),
        
        'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, notice',
//					'levels'=>'error, warning',
				),
			),
		),
        'errorHandler' => array(
        	'class' => 'application.components.ExtendedErrorHandler'
        ),
        'db'=>array(
            'connectionString'=>'mysql:host='.$params['dbHost'].';dbname='.$params['dbName'].((isset($params['dbPort'])&&$params['dbPort'])?';port='.$params['dbPort']:''),
            'username'=>$params['dbUser'],
            'password'=>$params['dbPass'],
            'charset' => 'utf8', // PDO изначально не знает от charset'е соединения с СУБД, поэтому приходится указывать ручками. Я в шоке.
        ),

	),
	'params'=>$params,
	'modules'=>require(dirname(__FILE__).'/modules.php'),
);