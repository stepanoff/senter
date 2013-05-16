<?php
class VAdminModule extends CWebModule
{
    public $viewsAlias = 'application.modules.VAdmin.views'; // путь до шаблонов форума (для кастомизации шаблонов)
    public $defaultLayout = 'application.modules.VAdmin.views.layouts.base';
    public $adminRoles = array();

    public $staticUrl = '/';
    protected $assetsPath = '';
    protected $assetsUrl = '';

	public function init()
	{
        $ext = Yii::app()->VExtension;
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(	
            'VAdmin.components.*',
			'VAdmin.models.*',
			'VAdmin.models.forms.*',
			'VAdmin.components.*',
			'VAdmin.controllers.*',
            'VAdmin.widgets.*',
			'VAdmin.views.*',

            'zii.widgets.grid.*',
		));
	}

    public function getAdminRoles()
    {
        return $this->adminRoles;
    }

    public function getAssetsPath () {
        return $this->assetsPath;
    }

    public function getAssetsUrl () {
        return $this->assetsUrl;
    }

    public function getViewsAlias($viewName)
    {
        return $this->viewsAlias.'.'.$viewName;
    }

    public function getLayout ()
    {
        return $this->defaultLayout;
    }
}