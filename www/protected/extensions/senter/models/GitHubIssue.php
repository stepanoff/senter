<?php
class GitHubIssue extends CActiveRecord
{
    const STATUS_NEW = 10;
    const STATUS_OPEN = 20;
    const STATUS_REVIEW = 30;
    const STATUS_CLOSED = 40;
    const STATUS_PRODUCTION = 50;

    protected $pullRequests = array();

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'githubissues';
    }

    public function relations()
    {
        return array(
        );
    }

    public static function statusTypes ()
    {
        return array (
            self::STATUS_NEW => 'новый',
            self::STATUS_OPEN => 'открытый',
            self::STATUS_REVIEW => 'на тестировании',
            self::STATUS_CLOSED => 'выполнен',
            self::STATUS_PRODUCTION => 'выкачен на сайт',
        );
    }

    public function attributeLabels()
    {
        return array(
            'status' => 'Статус',
            'rep' => 'Репозиторий',
            'repNum' => 'номер тикета в репозитории',
            'asigneeId' => 'Ответственный',
            'pullRequestNum' => 'номер пулл-реквеста',
            'masterCommitSha' => 'sha коммита в мастер',
        );
    }

    public function rules()
    {
        return array(
            array('status, rep, repNum, assigneeId, pullRequestNum, masterCommitSha', 'safe')
        );
    }

    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'onReview' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_REVIEW .') AND `pullRequestNum` > 0',
            ),
            'unassigned' => array(
                'condition' => $alias.'.assigneeId = 0',
            ),

        );
    }

    public function byRepNum($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.repNum = '.$id,
        ));
        return $this;
    }

    public function byRep($rep)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.rep = "'.$rep.'"',
        ));
        return $this;
    }

    public function isOpen ()
    {
        return $this->status < self::STATUS_REVIEW;
    }

    protected function beforeSave()
    {
        if (!$this->status)
            $this->status = self::STATUS_NEW;

        return parent::beforeSave();
    }

    public function getPullRequest ()
    {
        $pulls = $this->pullRequests;
        if ($pulls)
            return $pulls[(count($pulls)-1)];
        return false;
    }

}