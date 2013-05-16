<?php
class AdminIssueTypeController extends VAdminController
{
	public $model = 'IssueType';
	
    public $route = '/admin/adminIssueType';

    public function appendLayoutFilters($model, $cFilterForm) {
        return $model;
    }

    public function getListColumns() {
        return array(
            'name',
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
            'color'=>array(
                'type'=>'text',
            ),
            'icon'=>array(
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