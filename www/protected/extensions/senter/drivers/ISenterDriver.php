<?php
/**
 * IEssentialDataDriver class file.
 *
 * @author Stepanoff Alex <stenlex@gmail.com>
 */

/**
 * SenterDriverBase is a base class for providers.
 * @package application.extensions.senter
 */
interface ISenterDriver {
	
	/**
	 * Initialize the component. 
	 * @param EssentialDataServiceBase $component the component instance.
	 * @param array $options properties initialization.
	 */
	public function init($component, $options = array());
	
	/**
	 * Returns driver name(id).
	 * @return string the driver name(id).
	 */
	public function getDriverName();
	
	/**
	 * Returns driver title.
	 * @return string the driver title.
	 */
	public function getDriverTitle();
	
	/**
	 * Sets {@link SenterComponent} application component
	 * @param SenterComponent $component the application component.
	 */
	public function setComponent($component);
	
	/**
	 * Returns the {@link SenterComponent} application component.
	 * @return EAuth the {@link SenterComponent} application component.
	 */
	public function getComponent();
	

}