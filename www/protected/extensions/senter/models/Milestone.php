<?php
class Milestone extends CActiveRecord
{
    const STATUS_NEW = 10;
    const STATUS_IGNORE = 12;
    const STATUS_HOLD = 15;
    const STATUS_PROCESS = 20;
    const STATUS_REVIEW = 30;
    const STATUS_SOLVED = 40;
    const STATUS_PRODUCTION = 50;

    const ACTION_NEW = 10;
    const ACTION_PROCESS = 20;
    const ACTION_REVIEW = 30;
    const ACTION_SOLVED = 40;
    const ACTION_CLOSED = 50;
    const ACTION_REOPEN = 60;

    public $labels = null;
    public $_collaborators = null;

    private $_issues = null;
    private $_devMilestone = null;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'milestones';
    }

    public static function statusTypes ($source = false)
    {
        return array (
            self::STATUS_NEW => 'New',
            self::STATUS_HOLD => 'Hold',
            self::STATUS_PROCESS => 'Process',
            self::STATUS_REVIEW => 'Review',
            self::STATUS_SOLVED => 'Solved',
            self::STATUS_PRODUCTION => 'Production',
        );
    }

    public function attributeLabels()
    {
        return array(
            'title' => 'Заголовок',
            'body' => 'Описание',
            'status' => 'Статус',
            'devSource' => 'Источник в системе разработке',
            'devSourceId' => 'id в в системе разработки',
        );
    }

    public function rules()
    {
        return array(
            array('title', 'required'),
            array('title, body, status, devSource, devSourceId, deadlineDate, priority, priorityId, createDate, closedDate', 'safe')
        );
    }

    public function relations()
    {
        return array(
            'priorityObj' => array(self::BELONGS_TO, 'Priority', 'priorityId'),
        );
    }

    public function getIssues()
    {
        if ($this->_issues === null) {
            $this->_issues = Issue::model()->byMilestoneId($this->id)->findAll();
        }
        return $this->_issues;
    }

    public function getDevMilestone()
    {
        if ($this->_devMilestone === null) {
            // todo: настроить на раоту с несколькими драйверами
            $driver = Yii::app()->senter->getDevDriver();
            $this->_devMilestone = $driver->getMilestoneById($this->devSourceId);
        }
        return $this->_devMilestone;
    }

    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'inProcess' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_PROCESS .', '. self::STATUS_NEW .')',
            ),
            'notClosed' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_PROCESS .', '. self::STATUS_NEW .', '. self::STATUS_REVIEW .')',
            ),
            'solved' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_SOLVED .', '. self::STATUS_PRODUCTION .')',
            ),
            'orderSolvedDate' => array(
                'order' => $alias.'.solvedDate DESC',
            ),
        );
    }

    public function isNew ()
    {
        return $this->status == self::STATUS_NEW;
    }

    public function isInProcess ()
    {
        return $this->status == self::STATUS_PROCESS;
    }

    public function isOnReview ()
    {
        return $this->status == self::STATUS_REVIEW;
    }

    public function isSolved ()
    {
        return $this->status == self::STATUS_SOLVED;
    }

    public function isOnProduction ()
    {
        return $this->staus == self::STATUS_PRODUCTION;
    }

    public function orderPriority()
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'select' => $alias.'.*, if ('.$alias.'.deadlineDate = "0000-00-00 00:00:00", 0, 1) as `hasPriority` ',
            'order' => 'hasPriority DESC, '.$alias.'.deadlineDate, '.$alias.'.priority DESC',
        ));
        return $this;
    }

    public function byStatus($status)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.status = "'.$status.'"',
        ));
        return $this;
    }

    public function byDevSource($source)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.devSource = "'.$source.'"',
        ));
        return $this;
    }

    public function byDevId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.devSourceId = "'.$id.'"',
        ));
        return $this;
    }

    public function byClientId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.clientSourceId = "'.$id.'"',
        ));
        return $this;
    }

    public function bySolvedMonth($month, $year)
    {
        $toDate = ($month == 12 ? ($year+1).'-01-03' : $year.'-'.($month+1).'-03').' 00:00:00';
        $fromDate = $year.'-'.($month).'-01'.' 00:00:00';
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.solvedDate >= "'.$fromDate.'" AND '.$alias.'.solvedDate <= "'.$toDate.'"',
        ));
        return $this;
    }

    protected function beforeSave()
    {
        if (!$this->status)
            $this->status = self::STATUS_NEW;
        if (!$this->createDate)
            $this->createDate = date('Y-m-d G:i:s', time());

        if ($this->priorityId) {
            $priority = Priority::model()->findByPk($this->priorityId);
            if ($priority) {
                $this->priority = $priority->number;
            }
            else {
                $this->priority = 0;
            }
        }

        return parent::beforeSave();
    }

    protected function afterDelete()
    {
        $senter = Yii::app()->getComponent('senter');
        if ($this->devSourceId && $senter) {
            // todo: удалить все тикеты
        }

        return parent::afterDelete();
    }

    protected function afterSave()
    {
        return parent::afterSave();
    }

}