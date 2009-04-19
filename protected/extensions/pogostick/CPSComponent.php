<?php
/**
 * CPSComponent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 *
 * Install in <yii_app_base>/extensions/pogostick
 */

/**
 * The CPSComponent is the base class for all Pogostick components for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package applications.extensions.pogostick
 * @since 1.0.3
 */
abstract class CPSComponent extends CApplicationComponent
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	public function __construct()
	{
		//	Attach this behavior before init is called...
		$this->attachBehavior( 'psComponent', array( 'class' => 'application.extensions.pogostick.behaviors.CPSComponentBehavior' ) );
	}

	//********************************************************************************
	//* Property Accessor Methods
	//********************************************************************************

	/**
	* Get the BaseUrl property
	*
	*/
	public function getBaseUrl() { return( $this->psComponent->baseUrl ); }

	/**
	* Set the BaseUrl property
	*
	* @param mixed $sUrl
	*/
	public function setBaseUrl( $sUrl ) { $this->psComponent->baseUrl = $sUrl; }

	/**
	* Gets the CheckOptions option
	*
	*/
	public function getCheckOptions() { return( $this->psComponent->checkOptions ); }
	/**
	* Sets the CheckOptions option
	*
	* @param boolean $bValue
	*/
	public function setCheckOptions( $sValue ) { $this->psComponent->checkOptions = $sValue; }

	/**
	* Gets the CheckCallbacks option
	*
	*/
	public function getCheckCallbacks() { return( $this->psComponent->checkCallbacks ); }

	/***
	* Sets the CheckCallbacks option
	*
	* @param mixed $_bValue
	*/
	public function setCheckCallbacks( $bValue ) { $this->psComponent->checkCallbacks = $bValue; }

	/**
	* Options getter
	*
	* @returns array
	*/
	public function getOptions() { return( $this->psComponent->options ); }

	/**
	* Returns an element from an array if it exists, otherwise returns $sDefault value
	*
	* @param array $arOptions
	* @param string $sName
	* @return mixed
	*/
	public function getOption( $sName, $sDefault = null ) { return( $this->psComponent->getOption( $sName, $sDefault ) ); }

	/**
    * Setter
    *
    * @var array $value options
    */
	public function setOptions( $arOptions ) { $this->psComponent->setOptions( $arOptions ); }

	/**
	* ValidOptions getter
	*
	*/
	public function getValidOptions() { return( $this->psComponent->validOptions ); }
	/**
	* ValidOptions setter
	*
	*/
	public function setValidOptions( $arValue ) { $this->psComponent->validOptions = $sValue; }

	/**
	* ValidCallbacks getter
	*
	*/
	public function getValidCallbacks() { return( $this->psComponent->validCallbacks ); }
	/**
	* ValidCallbacks setter
	*
	*/
	public function setValidCallbacks( $arValue ) { $this->psComponent->validCallbacks = $arValue; }

	/**
	* Setter
	*
	* @param array $value callbacks
	*/
	public function setCallbacks( $arCallbacks ) { $this->psComponent->setCallbacks( $arCallbacks ); }

	/**
	* Getter
	*
	* @return array
	*/
	public function getCallbacks() { return( $this->psComponent->callbacks ); }

}