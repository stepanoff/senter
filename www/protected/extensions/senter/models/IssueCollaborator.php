<?php
class IssueCollaborator extends CActiveRecord
{
    const TYPE_EXECUTOR = 10;
    const TYPE_REVIEWER = 20;
    const TYPE_OTHER = 30;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'issuecollaborators';
    }

    public function relations()
    {
        return array(
        );
    }

    public function scopes ()
    {
        $alias = $this->getTableAlias();
        return array (
        );
    }

    public function attributeLabels()
    {
        return array(
            'developerId' => 'Разработчик',
            'issueId' => 'Тикет',
            'collaborationType' => 'Тип участия',
        );
    }

    public function rules()
    {
        return array(
            array('developerId, issueId, collaborationType', 'safe')
        );
    }

}