<?php
class PriorityLabelsWidget extends CWidget
{
    public $sources = array();
    public $values = array();
    public $model;
    public $attribute;

    protected $_template = 'priorityLabels';

    public function run()
    {
        $this->render($this->_template, array(
            'inputName' => CHtml::activeName($this->model, $this->attribute),
            'inputId' => CHtml::activeId($this->model, $this->attribute),
            'values' => $this->values,
            'sources' => $this->sources,
        ));
    }

}