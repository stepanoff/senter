<?php
class IssueType extends CActiveRecord
{
    public $_clientLabels = null;
    public $_devLabels = null;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'issuetypes';
    }

    public function relations()
    {
        return array(
        );
    }

    public function getClientLabels()
    {
        if ($this->_clientLabels === null) {
            $this->_clientLabels = array();
            $models = IssueTypeClientLabel::model()->byIssueTypeId($this->id)->findAll();
            foreach ($models as $model) {
                $this->_clientLabels[$model->source] = $model->label;
            }
        }
        return $this->_clientLabels;
    }

    public function getDevLabels()
    {
        if ($this->_devLabels === null) {
            $this->_devLabels = array();
            $models = IssueTypeDevLabel::model()->byIssueTypeId($this->id)->findAll();
            foreach ($models as $model) {
                $this->_devLabels[$model->rep] = $model->label;
            }
        }
        return $this->_devLabels;
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
            'name' => 'Название',
            'color' => 'Цвет',
            'icon' => 'Иконка',
            '_clientLabels' => 'Связка с тегами источников задач',
            '_devLabels' => 'Связка с тегами в репозиториях',
        );
    }

    public function rules()
    {
        return array(
            array('name, color, icon, _clientLabels, _devLabels', 'safe')
        );
    }

    protected function afterSave()
    {
        if ($this->_clientLabels === null)
            $clientLabels = $this->getClientLabels();
        else
            $clientLabels = $this->_clientLabels;
        IssueTypeClientLabel::model()->byIssueTypeId($this->id)->delete();
        foreach ($clientLabels as $source => $label) {
            $p = new IssueTypeClientLabel;
            $p->source = $source;
            $p->label = $label;
            $p->issueTypeId = $this->id;
            $p->save();
        }

        if ($this->_devLabels === null)
            $devLabels = $this->getDevLabels();
        else
            $devLabels = $this->_devLabels;
        IssueTypeDevLabel::model()->byIssueTypeId($this->id)->delete();
        foreach ($devLabels as $source => $label) {
            $p = new IssueTypeDevLabel();
            $p->rep = $source;
            $p->label = $label;
            $p->issueTypeId = $this->id;
            $p->save();
        }

        return parent::afterSave();
    }
    protected function afterDelete()
    {
        IssueTypeClientLabel::model()->byIssueTypeId($this->id)->delete();
        IssueTypeDevLabel::model()->byIssueTypeId($this->id)->delete();

        return parent::afterDelete();
    }

}