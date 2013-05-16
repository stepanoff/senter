<?php
class RequesterOrg extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'requesterorgs';
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => 'Название организации',
            'site' => 'Сайт',
            'source' => 'Источник',
            'logoUrl' => 'Урл логотипа',
            'apiUrl' => 'Урл API',
            'apiKey' => 'Ключ для API',
            'externalId' => 'Внешний id',
        );
    }

    public function rules()
    {
        return array(
            array('externalId', 'required'),
            array('name, site, logoUrl, apiUrl, source, apiKey, externalId', 'safe')
        );
    }

    public function byExternalId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.externalId = "'.$id.'"',
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

}