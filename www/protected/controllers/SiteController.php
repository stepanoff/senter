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

        $allIssues = Issue::model()->notClosed()->inDevelopment()->inSupport()->orderPriority()->findAll();
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

        if (Yii::app()->request->isAjaxRequest) {
            $res = $this->renderPartial('main', array(
                'newIssues' => $newIssues,
                'inProcessIssues' => $inProcessIssues,
                'onReviewIssues' => $onReviewIssues,
                'solvedIssues' => $solvedIssues,
                'isAjax' => true,
            ), true);
            echo CJSON::encode(array('issuesList' => $res));
            die();
        }

        $this->render('main', array(
            'newIssues' => $newIssues,
            'inProcessIssues' => $inProcessIssues,
            'onReviewIssues' => $onReviewIssues,
            'solvedIssues' => $solvedIssues,
            'isAjax' => false,
        ));
	}

    public function actionMilestones ()
    {
        $milestones = Milestone::model()->notClosed()->orderPriority()->findAll();
        $this->render ('milestones', array('milestones' => $milestones));
    }

    public function actionBacklog ()
    {
        $allIssues = Issue::model()->byStatus(Issue::STATUS_HOLD)->inDevelopment()->orderPriority()->findAll();
        $this->render ('backlog', array('issues' => $allIssues));
    }

    public function actionClosedIssues ()
    {
        $month = date('m');
        $year = date('Y');
        $allIssues = Issue::model()->solved()->inDevelopment()->orderSolvedDate()->bySolvedMonth($month, $year)->findAll();
        $this->render ('closedStat', array('issues' => $allIssues));
    }

    public function actionCommonStat ()
    {
        $this->render ('commonStat', array());
    }

}