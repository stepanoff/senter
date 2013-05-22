<?php
$ext = Yii::app()->VExtension;
class SiteController extends VController
{
    const MAX_TICKETS = 100;

	public $layout='column1';

	public function actionIndex()
	{
        $newIssues = array();
        $inProcessIssues = array();
        $onReviewIssues = array();
        $solvedIssues = array();

        $allIssues = Issue::model()->notClosed()->inDevelopment()->orderPriority()->findAll();
        foreach ($allIssues as $issue) {
            if ($issue->isNew()) {
                $newIssues[] = $issue;
            }
            elseif ($issue->isInProcess()) {
                $inProcessIssues[] = $issue;
            }
            elseif ($issue->isOnReview()) {
                $onReviewIssues[] = $issue;
            }
            elseif ($issue->isSolved()) {
                $solvedIssues[] = $issue;
            }

        }

        $this->render('main', array(
            'newIssues' => $newIssues,
            'inProcessIssues' => $inProcessIssues,
            'onReviewIssues' => $onReviewIssues,
            'solvedIssues' => $solvedIssues,
        ));

        die();
	}

    public function actionProcess ()
    {
        $senter = Yii::app()->senter;
//        $senter->test();
        $senter->synchronizeIssues ();
        die();
        $senter->markClosedIssues(); // закрываем тикеты, выкаченные на живой сайт
        $senter->markSolvedIssues();// комментируем тикеты, влитые в основную ветку
        $senter->markReviewIssues(); // комментируем тикеты, отданные на проверку
        $senter->markProcessIssues(); // комментируем тикеты, взятые в разработку
        $senter->createNewIssues(); // выгружаем новые тикеты из сервисов техподдержек
        $senter->uploadOpenIssuesToDev (); // отправляем новые тикеты в систему тикетов разработки
        die();

    }


}