<?php
/**
 * SenterDriverBase class file.
 *
 * @author Stepanoff Alex <stenlex@gmail.com>
 */

/**
 * SenterComponent is a base class for providers.
 * @package application.extensions.senter
 *
 * @property SenterComponent $component
 */
abstract class SenterDriverBase extends CComponent implements ISenterDriver {
	
	/**
	 * @var string the driver name.
	 */
	protected $name;
	
	/**
	 *
	 * @var string the driver title to display in views. 
	 */
	protected $title;
	
	/**
	 * @var array attributes.
	 * @see getAttribute
	 * @see getItem
	 */
	protected $attributes = array();
	
	/**
	 * @var SenterServiceBase the {@link SenterComponent} application component.
	 */
	private $component;

    /**
     * @var array errors
     */
    protected $errors = array();

	/**
	 * Initialize the component. 
	 * @param SenterComponent $component the component instance.
	 * @param array $options properties initialization.
	 */
	public function init($component, $options = array()) {
		if (isset($component))
			$this->setComponent($component);


		foreach ($options as $key => $val)
			$this->$key = $val;
	}
	
	/**
	 * Returns driver name(id).
	 * @return string the driver name(id).
	 */
	public function getDriverName() {
		return $this->name;
	}
	
	/**
	 * Returns driver title.
	 * @return string the driver title.
	 */
	public function getDriverTitle() {
		return $this->title;
	}
	
	/**
	 * Sets {@link SenterComponent} application component
	 * @param SenterComponent $component the application component.
	 */
	public function setComponent($component) {
		$this->component = $component;
	}
	
	/**
	 * Returns the {@link SenterComponent} application component.
	 * @return SenterComponent the {@link EssentialDataServiceBase} application component.
	 */
	public function getComponent() {
		return $this->component;
	}

    public function addError ($code, $msg) {
        $this->errors[] = array (
            'code' => $code,
            'message' => $msg,
        );
    }

    public function getErrors () {
        return $this->errors;
    }

    public function convertStatus ($status) {
        $statuses = $this->statusTypes();
        foreach ($statuses as $k=>$v) {
            if ($v == $status)
                return $k;
        }
        return false;
    }

    public function getIssueStatus ($status) {
        $statuses = $this->statusTypes('Issue');
        if (isset($statuses[$status])) {
            return $statuses[$status];
        }
        return false;
    }

    public function statusTypes ($source = false)
    {
       return array ();
    }

    public function markIssue ($issue, $action)
    {
        return true;
    }


	
}