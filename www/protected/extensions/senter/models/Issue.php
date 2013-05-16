<?php
class Issue extends CActiveRecord
{
    const STATUS_NEW = 10;
    const STATUS_PROCESS = 20;
    const STATUS_REVIEW = 30;
    const STATUS_SOLVED = 40;
    const STATUS_PRODUCTION = 50;

    const ACTION_NEW = 10;
    const ACTION_PROCESS = 20;
    const ACTION_REVIEW = 30;
    const ACTION_SOLVED = 40;
    const ACTION_CLOSED = 50;

    public $labels = null;

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
        return array (
            self::STATUS_NEW => 'New',
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
            'clientSource' => 'Источник постановщика задач',
            'clientSourceId' => 'id в источнике постановщика задач',
            'devSource' => 'Источник в системе разработке',
            'devSourceId' => 'id в в системе разработки',
        );
    }

    public function rules()
    {
        return array(
            array('title', 'required'),
            array('title, body, status, clientSource, clientSourceId, devSource, devSourceId, requesterId, assigneeId, deadlineDate, typeId, priority, priorityId', 'safe')
        );
    }

    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'inProcess' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_PROCESS .', '. self::STATUS_NEW .')',
            ),
        );
    }

    public function byClientSource($source)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.clientSource = "'.$source.'"',
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

    protected function beforeSave()
    {
        if (!$this->status)
            $this->status = self::STATUS_NEW;

        return parent::beforeSave();
    }

}