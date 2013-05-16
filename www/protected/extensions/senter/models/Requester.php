<?php
class Requester extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'requesters';
    }

    public function relations()
    {
        return array(
            'org' => array(self::BELONGS_TO, 'RequesterOrg', 'orgId'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => 'Имя',
            'orgId' => 'id организации',
            'externalId' => 'Внешний id',
        );
    }

    public function rules()
    {
        return array(
            array('externalId', 'required'),
            array('name, orgId, externalId', 'safe')
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

    public function byOrgId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.orgId = '.$id,
        ));
        return $this;
    }

}