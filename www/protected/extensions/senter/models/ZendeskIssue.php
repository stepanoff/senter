<?php
class ZendeskIssue extends CActiveRecord
{
    const STATUS_NEW = 10;
    const STATUS_OPEN = 20;
    const STATUS_PENDING = 30;
    const STATUS_HOLD = 40;
    const STATUS_SOLVED = 50;
    const STATUS_CLOSED = 60;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'zendeskissues';
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'externalId' => 'id в источнике',
            'status' => 'Статус',
        );
    }

    public function rules()
    {
        return array(
            array('externalId', 'required'),
            array('status', 'safe')
        );
    }

    public function scopes()
    {
        $alias = $this->getTableAlias();
        return array(
            'inProcess' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_OPEN .', '. self::STATUS_NEW .')',
            ),
            'notClosed' => array(
                'condition' => $alias.'.status IN ('. self::STATUS_OPEN .', '. self::STATUS_NEW .', ' . self::STATUS_PENDING . ' )',
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

    protected function beforeSave()
    {
        if (!$this->status)
            $this->status = self::STATUS_NEW;

        return parent::beforeSave();
    }

}