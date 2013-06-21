<?php
class IssueStatusDevLabel extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'issuestatusdevlabel';
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'issueStatusId' => 'Статус',
            'rep' => 'Репозиторий',
            'label' => 'Тег',
            'source' => 'Источник',
        );
    }

    public function rules()
    {
        return array(
            array('issueStatusId', 'required'),
            array('issueStatusId, rep, label, source', 'safe')
        );
    }

    public function byIssueStatusId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.issueStatusId = "'.$id.'"',
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

    public function byRep($rep)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.rep = "'.$rep.'"',
        ));
        return $this;
    }

}