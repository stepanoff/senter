<?php
class I_ssue extends CActiveRecord
{
    const STATUS_NEW = 10;
    const STATUS_OPEN = 20;
    const STATUS_PENDING = 30;
    const STATUS_HOLD = 40;
    const STATUS_SOLVED = 50;
    const STATUS_CLOSED = 60;

    const ACTION_NEW = 10;
    const ACTION_PROCESS = 20;
    const ACTION_REVIEW = 30;
    const ACTION_SOLVED = 40;
    const ACTION_CLOSED = 50;

    const SOURCE_ZENDESK = 10;
    const SOURCE_GITHUB = 20;

    protected $_tasks = null;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'issues';
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
                self::STATUS_PENDING => 'в ожидании',
                self::STATUS_HOLD => 'заморожен',
                self::STATUS_SOLVED => 'сделан',
                self::STATUS_CLOSED => 'закрыт',
            );
        }
        elseif ($source == self::SOURCE_ZENDESK) {
            return array (
                self::STATUS_NEW => 'new',
                self::STATUS_OPEN => 'open',
                self::STATUS_PENDING => 'pending',
                self::STATUS_HOLD => 'hold',
                self::STATUS_SOLVED => 'solved',
                self::STATUS_CLOSED => 'closed',
            );
        }
        elseif ($source == self::SOURCE_GITHUB) {
            return array (
                self::STATUS_NEW => 'open',
                self::STATUS_OPEN => 'open',
                self::STATUS_PENDING => 'open',
                self::STATUS_HOLD => 'closed',
                self::STATUS_SOLVED => 'closed',
                self::STATUS_CLOSED => 'closed',
            );
        }
        return array();
    }

    public function attributeLabels()
    {
        return array(
            'externalId' => 'id в источнике',
            'status' => 'Статус',
            'source' => 'Источник',
        );
    }

    public function rules()
    {
        return array(
            array('externalId', 'required'),
            array('status, source', 'safe')
        );
    }

    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'inProcess' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_OPEN .', '. self::STATUS_NEW .')',
            ),
        );
    }

    public function byExternalId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.externalId = '.$id,
        ));
        return $this;
    }

    public function bySource($source)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.source = "'.$source.'"',
        ));
        return $this;
    }

    protected function beforeSave()
    {
        if (!$this->status)
            $this->status = self::STATUS_NEW;

        return parent::beforeSave();
    }

    public function getTasks () {
        if ($this->_tasks === null) {
            $this->_tasks = Task::model()->byIssueId($this->id)->findAll();
        }
        return $this->_tasks;
    }

    public static function convertStatus ($status, $source) {
        $statuses = self::statusTypes($source);
        foreach ($statuses as $k=>$v) {
            if ($v == $status)
                return $k;
        }
        return false;
    }

}