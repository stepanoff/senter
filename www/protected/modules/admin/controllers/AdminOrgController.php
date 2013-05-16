<?php
class AdminOrgController extends VAdminController
{
	public $model = 'RequesterOrg';
	
    public $route = '/admin/adminOrg';

    public function layoutsFilters() {
        return array(
            'name' => array(
                'type' => 'text',
                'label' => 'Название',
            ),
        );
    }

    // todo: вытащить обновлялку в крон
    /*
    public function actionList () {
        Yii::app()->senter->updateOrganizations ();
        Yii::app()->senter->updateRequesters ();
        die();
    }
    */

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