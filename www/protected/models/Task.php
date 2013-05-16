<?php
class Task extends CActiveRecord
{
    const STATUS_NEW = 10;
    const STATUS_OPEN = 20;
    const STATUS_REVIEW = 30;
    const STATUS_CLOSED = 40;
    const STATUS_PRODUCTION = 50;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'tasks';
    }

    public function relations()
    {
        return array(
        );
    }

    public static function statusTypes ($source = false)
    {
        if ($source == false) {
            return array (
                self::STATUS_NEW => 'новый',
                self::STATUS_OPEN => 'открытый',
                self::STATUS_REVIEW => 'на тестировании',
                self::STATUS_CLOSED => 'выполнен',
                self::STATUS_PRODUCTION => 'выкачен на сайт',
            );
        }
        return array();
    }

    public function attributeLabels()
    {
        return array(
            'issueId' => 'id задачи-источника',
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
            array('issueId', 'required'),
            array('status, rep, repNum, asigneeId, pullRequestNum, masterCommitSha', 'safe')
        );
    }

    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'onReview' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_REVIEW .') AND `pullRequestNum` > 0',
            ),
        );
    }

    public function byIssueId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.issueId = '.$id,
        ));
        return $this;
    }

    public function byRepNum($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.repNum = '.$id,
        ));
        return $this;
    }

    protected function beforeSave()
    {
        if (!$this->status)
            $this->status = self::STATUS_NEW;

        return parent::beforeSave();
    }

}