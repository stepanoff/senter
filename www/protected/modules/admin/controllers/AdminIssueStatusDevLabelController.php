<?php
class AdminIssueStatusDevLabelController extends VAdminController
{
	public $model = 'IssueStatusDevLabel';
	
    public $route = '/admin/adminIssueStatusDevLabel';

    public function appendLayoutFilters($model, $cFilterForm) {
        return $model;
    }

    public function getListColumns() {
        return array(
            'issueStatusId',
            'label',
            'rep',
            'source',
            array(
                'class'=>'VAdminButtonWidget',
            ),
        );
    }

    public function getFormElements ($model)
    {
        $driver = Yii::app()->senter->getDevDriver();

        $devSources = array();
        $tmp = $driver->getRepos();
        foreach ($tmp as $s) {
            $devSources[$s] = $s;
        }

        $modelName = $driver->getModelName();
        $statuses = $modelName::statusTypes();


        $currentSource = $driver->getDriverName();
        if ($model->source && $currentSource != $model->source) {
            throw CHttpException (500, 'Нельзя редактировать эту модель. Переключите драйвер разработки');
        }
        $model->source = $driver->getDriverName();

        return array(
            'issueStatusId'=>array(
                'type'=>'dropdownlist',
                'items' => $statuses,
                'empty' => 'Выбрать',
            ),
            'source'=>array(
                'type'=>'hidden',
            ),
            'rep'=>array(
                'type'=>'dropdownlist',
                'items' => $devSources,
                'empty' => 'Выбрать',
            ),
            'label'=>array(
                'type'=>'text',
            ),
        );
    }

}