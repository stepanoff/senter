<?php
class AdminDeveloperController extends VAdminController
{
	public $model = 'Developer';
	
    public $route = '/admin/adminDeveloper';

    public function layoutsFilters() {
        return array(
            'username' => array(
                'type' => 'text',
                'label' => 'Имя',
            ),
        );
    }

    public function appendLayoutFilters($model, $cFilterForm) {
        if ($cFilterForm->model->username != "") {
            $model->getDbCriteria()->addSearchCondition('username', $cFilterForm->model->username);
        }
        return $model;
    }

    public function getListColumns() {
        return array(
            'username',
            array(
                'name'=>'avatarUrl',
                'type' => 'raw',
                'value'=>'\'<img width="100" src="\'.$data->avatarUrl.\'">\'',
            ),
            array(
                'class'=>'VAdminButtonWidget',
            ),
        );
    }

    public function getFormElements ($model)
    {
        return array(
            'username'=>array(
                'type'=>'text',
            ),

            'avatarUrl'=>array(
                'type'=>'text',
            ),

            'url'=>array(
                'type'=>'text',
            ),
        );
    }

}