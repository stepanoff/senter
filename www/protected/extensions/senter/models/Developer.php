<?php
class Developer extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'developers';
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'username' => 'Имя',
            'avatarUrl' => 'Аватар',
            'url' => 'Профиль',
            'externalId' => 'Внешний id',
            'source' => 'Система разработки',
        );
    }

    public function rules()
    {
        return array(
            array('externalId', 'required'),
            array('username, avatarUrl, url, externalId, source', 'safe')
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