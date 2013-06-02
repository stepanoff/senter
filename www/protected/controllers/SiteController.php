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

    public function actionCommonStat ()
    {
        $this->render ('commonStat', array());
    }

}