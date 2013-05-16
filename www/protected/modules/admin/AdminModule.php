<?php
class AdminModule extends VAdminModule
{
	public function init()
	{
        parent::init();
		$this->setImport(array(
			'admin.models.*',
			'admin.components.*',
			'admin.controllers.*',
		));
	}
}