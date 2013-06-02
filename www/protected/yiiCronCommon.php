<?php 
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('YII_DEBUG') or define('YII_DEBUG',true);
error_reporting(E_ALL | E_STRICT);

// to_do: vendor path надо выносить в настройки
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

mb_internal_encoding("UTF-8");

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__FILE__). DS . '..');
define('BASE_PATH', dirname(__FILE__). DS . '..' . DS . '..');
define('FILES_PATH', dirname(__FILE__). DS . '..' . DS . 'files');
define('LIB_PATH', realpath(dirname(__FILE__). DS . '..' . DS . '..' . DS . 'lib'));
define('VENDOR_PATH', realpath(dirname(__FILE__). DS . '..' . DS . '..' . DS . 'vendor'));

// подключаем файл инициализации Yii
require_once(VENDOR_PATH .'/yiisoft/yii/framework/yii.php');

$configFile=dirname(__FILE__).'/config/console.php';

require(dirname(__FILE__) . '/components/ExtendedConsoleApplication.php');
Yii::createApplication('ExtendedConsoleApplication', $configFile);
//Yii::createConsoleApplication($configFile)->run();
//Yii::import('application.extensions.croncommand.*');
?>