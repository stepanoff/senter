<?php
class IssueTypeDevLabel extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'issuetypedevlabel';
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
            'rep' => 'Репозиторий',
            'label' => 'Тег',
        );
    }

    public function rules()
    {
        return array(
            array('issueTypeId', 'required'),
            array('issueTypeId, rep, label', 'safe')
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