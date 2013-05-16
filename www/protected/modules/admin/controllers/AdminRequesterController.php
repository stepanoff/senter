<?php
class AdminRequesterController extends VAdminController
{
	public $model = 'Requester';
	
    public $route = '/admin/adminRequester';

    public function layoutsFilters() {
        return array(
            'name' => array(
                'type' => 'text',
                'label' => 'Название',
            ),
        );
    }

    public function appendLayoutFilters($model, $cFilterForm) {
        if ($cFilterForm->model->name != "") {
            $model->getDbCriteria()->addSearchCondition('name', $cFilterForm->model->username);
        }
        return $model;
    }

    public function getListColumns() {
        return array(
            'name',
            array(
                'name' => 'orgId',
                'value'=>'$data->org->name',
            ),
            array(
                'class'=>'VAdminButtonWidget',
            ),
        );
    }

    public function getFormElements ($model)
    {
        return array(
            'name'=>array(
                'type'=>'text',
            ),
        );
    }

}