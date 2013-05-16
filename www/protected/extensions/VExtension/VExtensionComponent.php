<?php
class VExtensionComponent extends CComponent {

    public $extensionAlias = 'ext.VExtension';
    public $modules = array ();
    public $components;
    public $staticUrl = '/';
    public $useBootstrap = true;

    protected $assetsPath = '';
    protected $assetsUrl = '';

    public function init () {
        Yii::import($this->extensionAlias.'.*');
        Yii::import($this->extensionAlias.'.models.*');
        Yii::import($this->extensionAlias.'.widgets.*');
        Yii::import($this->extensionAlias.'.validators.*');
        Yii::import($this->extensionAlias.'.controllers.*');
        Yii::import($this->extensionAlias.'.widgets.htmlextended.*');
        Yii::import($this->extensionAlias.'.helpers.*');

        if ($this->components) {
            foreach ($this->components as $componentName => $comp) {
                Yii::app()->setComponents(array(
                    $comp['name']=>$comp['options']
                ));
            }
        }

        $this->assetsPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';
        $this->assetsUrl = $this->staticUrl.Yii::app()->assetManager->publish($this->assetsPath, false, -1, YII_DEBUG);
    }

    public function getAssetsPath () {
        return $this->assetsPath;
    }

    public function getAssetsUrl () {
        return $this->assetsUrl;
    }

    public function getViewsAlias () {
        return $this->extensionAlias . '.views';
    }

    public function registerBootstrap () {
        if ($this->useBootstrap) {
            $assetsPath = VENDOR_PATH . DIRECTORY_SEPARATOR . 'twitter' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'assets';
            $url = Yii::app()->assetManager->publish($assetsPath, false, -1, YII_DEBUG);
            $cs = Yii::app()->clientScript;

            $cs->registerCssFile($url.'/css/bootstrap.css');
            $cs->registerCssFile($url.'/css/bootstrap-responsive.css');
            $cs->registerCssFile($url.'/css/docs.css');
            $cs->registerScriptFile($url.'/js/bootstrap.min.js', CClientScript::POS_HEAD);
        }
    }

}
?>
