<?php
class AdminMilestoneController extends VAdminController
{
	public $model = 'Milestone';

    public $route = '/admin/adminMilestone';

    public function layoutsFilters() {
        return array(
            'status' => array(
                'type' => 'dropdownlist',
                'label' => 'Статус',
                'items' => Milestone::statusTypes(),
                'empty' => 'Выбрать',
            ),
            'id' => array(
                'type' => 'text',
                'label' => 'id',
            ),
            'title' => array(
                'type' => 'text',
                'label' => 'Заголовок',
            ),
        );
    }

    public function appendLayoutFilters($model, $cFilterForm) {
        if ($cFilterForm->model->status != "") {
            $model->byStatus($cFilterForm->model->status);
        }
        if ($cFilterForm->model->title != "") {
            $model->getDbCriteria()->addSearchCondition('title', $cFilterForm->model->title);
        }
        if ($cFilterForm->model->id != "") {
            $model->getDbCriteria()->mergeWith(array('condition' => $model->getTableAlias().'.id = '.$cFilterForm->model->id));
        }
        $model->orderPriority();
        return $model;
    }

    public function getListColumns() {
        return array(
            'title',
            array(
                'name'=>'devLink',
                'type' => 'raw',
                'value'=>'$data->getDevMilestone() ? CHtml::link($data->getDevMilestone()->getNumber(), $data->getDevMilestone()->getUrl()) : \'\'',
            ),
            array(
                'name'=>'repo',
                'type' => 'raw',
                'value'=>'$data->getDevMilestone() ? $data->getDevMilestone()->rep : \'\'',
            ),
            array(
                'class'=>'VAdminButtonWidget',
            ),
        );
    }

    public function getFormElements ($model)
    {
        $priorities = array();
        $tmp = Priority::model()->findAll();
        foreach ($tmp as $r) {
            $priorities[$r->id] = $r->name;
        }

        return array(
            'title'=>array(
                'type'=>'text',
            ),
            'body'=>array(
                'type'=>'textarea',
            ),
            'status'=>array(
                'type'=>'dropdownlist',
                'items'=>Issue::statusTypes(),
                'empty'=>'Выбрать',
            ),
            'priorityId'=>array(
                'type'=>'dropdownlist',
                'items'=>$priorities,
                'empty'=>'Выбрать',
            ),
            'deadlineDate'=>array(
                'type'=>'text',
            ),
        );
    }

}