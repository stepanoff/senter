<?php
class AdminPriorityController extends VAdminController
{
	public $model = 'Priority';
	
    public $route = '/admin/adminPriority';

    public function appendLayoutFilters($model, $cFilterForm) {
        $model->orderPriority();
        return $model;
    }

    public function getListColumns() {
        return array(
            'name',
            'number',
            array(
                'class'=>'VAdminButtonWidget',
            ),
        );
    }

    public function getFormElements ($model)
    {
        $sources = array();
        $tmp = Yii::app()->senter->getClientDrivers();
        foreach ($tmp as $driver) {
            $sources[] = $driver->getDriverName();
        }

        $tmp = Yii::app()->senter->getDevDriver();
        $devSources = $tmp->getRepos();

        return array(
            'name'=>array(
                'type'=>'text',
            ),
            'number'=>array(
                'type'=>'text',
            ),
            'color'=>array(
                'type'=>'text',
            ),
            'icon'=>array(
                'type'=>'text',
            ),
            'estimate'=>array(
                'type'=>'text',
            ),
            '_clientLabels'=>array(
                'type'=>'PriorityLabelsWidget',
                'sources' => $sources,
                'values' => $model->getClientLabels(),
            ),
            '_devLabels'=>array(
                'type'=>'PriorityLabelsWidget',
                'sources' => $devSources,
                'values' => $model->getDevLabels(),
            ),
        );
    }

}