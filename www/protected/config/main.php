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
	echo 'Error: params<br>'.implode(',<br>', $emptyKeys).'<br>required';
	die();
}

$mainConfig = array(
	'basePath'=>dirname(__FILE__).DS.'..',
	'runtimePath' => dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'data',
	'name'=>$params['appName'],
	'language' => 'ru',
	'defaultController'=>'site',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model, component and helper classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
        'application.models.*',
		'application.extensions.*',
        'application.extensions.senter.*',
        'application.extensions.senter.models.*',
        'application.extensions.senter.drivers.*',
		'application.helpers.*',
		'application.widgets.*',

        'application.modules.VAdmin.*',
        'application.modules.VAdmin.controllers.*',

    ),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>$params,

	// application components
	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, info',
				),
				array(
					'class'=>'CWebLogRoute',
					'enabled' => YII_DEBUG_LOG,
					'levels'=>'info, error, warning, trace, profile',
					'showInFireBug' => false,
				),
				array(
					'class'=>'CProfileLogRoute',
					'enabled' => YII_DEBUG_LOG,
					'showInFireBug' => false,
					'report' => 'summary',
				),
			),
		),
		'clientScript'=>array(
			'class'=>'application.components.ExtendedClientScript',
			'combineFiles'=>false,
			'compressCss'=>false,
			'compressJs'=>false,
		),
        'urlManager'=>require(dirname(__FILE__).'/urlManager.php'),
        
        'cache' => array(
			'class' => 'CFileCache'
		),
        'db'=>array(
            'connectionString'=>'mysql:host='.$params['dbHost'].';dbname='.$params['dbName'].((isset($params['dbPort'])&&$params['dbPort'])?';port='.$params['dbPort']:''),
            'username'=>$params['dbUser'],
            'password'=>$params['dbPass'],
            'charset' => 'utf8', // PDO изначально не знает от charset'е соединения с СУБД, поэтому приходится указывать ручками. Я в шоке.
        ),
        'senter' => require(dirname(__FILE__).'/senter.php'),

        'errorHandler' => array(
        	'class' => 'application.components.ExtendedErrorHandler'
        ),
		'localConfig' => array(
            'class' => 'application.components.LocalConfigComponent'
        ),
        'VExtension' => array (
            'class' => 'ext.VExtension.VExtensionComponent',
            'staticUrl' => '',
            'components' => array (
            ),
            'modules' => array (
            ),
        ),
    ),
    
    'modules'=>require(dirname(__FILE__).'/modules.php'),
    
);

return $mainConfig;