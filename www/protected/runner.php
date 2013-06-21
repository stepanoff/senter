<?php
date_default_timezone_set('Asia/Yekaterinburg');
/**
 * Файл для фонового запуска консольных команд
 * Вызов - php имя_этого_файла имя_команды
 *
 * @author Степанов Алексей
 * @since 28.01.2012
 */
if ( empty($_SERVER['argv'][1]) )
{
	return;
}
$name = $_SERVER['argv'][1];

require('yiiCronCommon.php');

$noinit = false;
if (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2]=='noinit')
	$noinit = true;
	

if ( $noinit && $name == 'senter' /* || Yii::app()->senter->initService($name)*/ )
{
	Yii::app()->senter->processIssues();
}
else if ( $noinit && $name == 'createIssues' /* || Yii::app()->senter->initService($name)*/ )
{
	Yii::app()->senter->createIssues();
}
else {
    echo date('Y-m-d H:i:s'), " - $name. Not initalized.\n";
}

?>
