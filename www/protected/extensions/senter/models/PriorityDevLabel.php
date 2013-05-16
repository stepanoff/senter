<?php
class PriorityDevLabel extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'prioritydevlabel';
    }

    public function relations()
    {
        return array(
        );
    }

    public function attributeLabels()
    {
        return array(
            'priorityId' => 'Приоритет',
            'rep' => 'Репозиторий',
            'label' => 'Тег',
        );
    }

    public function rules()
    {
        return array(
            array('priorityId', 'required'),
            array('priorityId, rep, label', 'safe')
        );
    }

    public function byPriorityId($id)
    {
        $alias = $this->getTableAlias();
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias.'.priorityId = "'.$id.'"',
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