<?php
class VMenuWidget extends CWidget {

    public $items = false;
    public $uri = false;

    public function run() {
		parent::run();

        $items = $this->items;

		$this->render('menu', array(
            'items' => $items,
		));
    }

}
