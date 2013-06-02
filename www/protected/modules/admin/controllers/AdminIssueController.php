<?php
class AdminIssueController extends VAdminController
{
	public $model = 'Issue';

    public $route = '/admin/adminIssue';

    public function layoutsFilters() {
        return array(
            'status' => array(
                'type' => 'dropdownlist',
                'label' => 'Статус',
                'items' => Issue::statusTypes(),
                'empty' => 'Выбрать',
            ),
            'type' => array(
                'type' => 'dropdownlist',
                'label' => 'Тип',
                'items' => self::typesList (),
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

    public static function typesList ()
    {
        return array(
            '1' => 'Переданные в разработку',
            '2' => 'Не переданные в разработку',
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
        if ($cFilterForm->model->type != "") {
            $type = $cFilterForm->model->type;
            switch ($type) {
                case '1':
                    $model->getDbCriteria()->mergeWith(array('condition' => $model->getTableAlias().'.devSourceId > 0'));
                    break;

                case '2':
                    $model->getDbCriteria()->mergeWith(array('condition' => $model->getTableAlias().'.devSourceId = 0'));
                    break;

            }
            $model->getDbCriteria()->addSearchCondition('title', $cFilterForm->model->title);
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
                'value'=>'$data->getDevIssue() ? CHtml::link($data->getDevIssue()->getNumber(), $data->getDevIssue()->getUrl()) : \'\'',
            ),
            array(
                'name'=>'repo',
                'type' => 'raw',
                'value'=>'$data->getDevIssue() ? $data->getDevIssue()->rep : \'\'',
            ),
            array(
                'class'=>'VAdminButtonWidget',
            ),
        );
    }

    public function getFormElements ($model)
    {
        $types = array();
        $tmp = IssueType::model()->findAll();
        foreach ($tmp as $r) {
            $types[$r->id] = $r->name;
        }

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
            'typeId'=>array(
                'type'=>'dropdownlist',
                'items'=>$types,
                'empty'=>'Выбрать',
            ),
        );
    }

}