<?php
class Priority extends CActiveRecord
{
    public $_clientLabels = null;
    public $_devLabels = null;

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'priorities';
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
            $models = PriorityClientLabel::model()->byPriorityId($this->id)->findAll();
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
            $models = PriorityDevLabel::model()->byPriorityId($this->id)->findAll();
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
            'orderPriority' => array(
                'order' => $alias.'.number DESC',
            ),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => 'Название',
            'number' => 'Приоритет (чем выше тем приоритетней)',
            'color' => 'Цвет приоритета',
            'icon' => 'Иконка',
            'estimate' => 'Время на выполнение',
            '_clientLabels' => 'Связка с тегами источников задач',
            '_devLabels' => 'Связка с тегами в репозиториях',
        );
    }

    public function rules()
    {
        return array(
            array('name, number, color, icon, estimate, _clientLabels, _devLabels', 'safe')
        );
    }

    protected function afterSave()
    {
        if ($this->_clientLabels === null)
            $clientLabels = $this->getClientLabels();
        else
            $clientLabels = $this->_clientLabels;
        PriorityClientLabel::model()->byPriorityId($this->id)->delete();
        foreach ($clientLabels as $source => $label) {
            $p = new PriorityClientLabel;
            $p->source = $source;
            $p->label = $label;
            $p->priorityId = $this->id;
            $p->save();
        }

        if ($this->_devLabels === null)
            $devLabels = $this->getDevLabels();
        else
            $devLabels = $this->_devLabels;
        PriorityDevLabel::model()->byPriorityId($this->id)->delete();
        foreach ($devLabels as $source => $label) {
            $p = new PriorityDevLabel();
            $p->rep = $source;
            $p->label = $label;
            $p->priorityId = $this->id;
            $p->save();
        }

        return parent::afterSave();
    }
    protected function afterDelete()
    {
        PriorityClientLabel::model()->byPriorityId($this->id)->delete();
        PriorityDevLabel::model()->byPriorityId($this->id)->delete();

        return parent::afterDelete();
    }

}