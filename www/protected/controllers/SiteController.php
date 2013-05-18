<?php
$ext = Yii::app()->VExtension;
class SiteController extends VController
{
    const MAX_TICKETS = 100;

	public $layout='column1';

	public function actionIndex()
	{
        $senter = Yii::app()->senter;
        //$senter->test(); // закрываем тикеты, выкаченные на живой сайт
        //die();
        //$this->render('main', array());

        //$senter->createIssues(); // выгружаем все тикеты из сервисов техподдержек

        $senter->markClosedIssues(); // закрываем тикеты, выкаченные на живой сайт
        $senter->markSolvedIssues();// комментируем тикеты, влитые в основную ветку
        $senter->markReviewIssues(); // комментируем тикеты, отданные на проверку
        $senter->markProcessIssues(); // комментируем тикеты, взятые в разработку
        $senter->createNewIssues(); // выгружаем новые тикеты из сервисов техподдержек
        $senter->uploadOpenIssuesToDev (); // отправляем новые тикеты в систему тикетов разработки
        die();

	}

}