<?php
class IssueTypeClientLabel extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'issuetypeclientlabel';
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'issueTypeId' => 'Тип',
            'source' => 'Клиент',
            'label' => 'Тег',
        );
    }

    public function rules()
    {
        return array(
            array('issueTypeId', 'required'),
            array('issueTypeId, source, label', 'safe')
        );
    }

    public function byIssueTypeId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.issueTypeId = "'.$id.'"',
        ));
        return $this;
    }

    public function byLabel($label)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.label = "'.$label.'"',
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