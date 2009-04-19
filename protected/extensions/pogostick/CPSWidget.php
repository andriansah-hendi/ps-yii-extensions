<?php
/**
 * CPSWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 *
 * Install in <yii_app_base>/extensions/pogostick
 */

/**
 * The CPSWidget is the base class for all Pogostick widgets for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package applications.extensions.pogostick
 * @since 1.0.3
 */
abstract class CPSWidget extends CInputWidget
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	public function __construct()
	{
		//	Attach this behavior
		$this->attachBehavior( 'psWidget', array( 'class' => 'application.extensions.pogostick.behaviors.CPSWidgetBehavior' ) );
	}

	/**
	* Yii widget init
	*
	*/
	public function init()
	{
		//	Get the id/name of this widget
		list( $this->name, $this->id ) = $this->resolveNameID();

		//	Call daddy
		parent::init();
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$this->registerClientScripts();
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Returns the generated Html
	*/
	public function getHtml() { return( $this->psWidget->html ); }

	/**
	* Returns the generated Html
	*/
	protected function setHtml( $sValue ) { $this->psWidget->html = $sValue; }

	/**
	* Returns the generated javascript
	*/
	public function getScript() { return( $this->psWidget->script ); }

	/**
	* Sets the generated javascript
	*/
	protected function setScript( $sScript ) { $this->psWidget->script = $sScript; }

	/**
	* Get the BaseUrl property
	*
	*/
	public function getBaseUrl() { return( $this->psWidget->baseUrl ); }

	/**
	* Set the BaseUrl property
	*
	* @param mixed $sUrl
	*/
	public function setBaseUrl( $sUrl ) { $this->psWidget->baseUrl = $sUrl; }

	/***
	* Get the Css File
	*
	*/
	public function getCssFile() { return( $this->psWidget->cssFile ); }

	/***
	* Set the Css file
	*
	* @param mixed $_sFile
	*/
	public function setCssFile( $_sFile ) { $this->psWidget->cssFile = $_sFile; }

	/**
	* Get View Name
	*
	*/
	public function getViewName() { return( $this->psWidget->viewName ); }
	/**
	* Set View Name
	*
	* @param string $sValue
	*/
	public function setViewName( $sValue ) { $this->psWidget->viewName = $sValue; }

	/**
	* Gets the CheckOptions option
	*
	*/
	public function getCheckOptions() { return( $this->psWidget->checkOptions ); }
	/**
	* Sets the CheckOptions option
	*
	* @param boolean $bValue
	*/
	public function setCheckOptions( $sValue ) { $this->psWidget->checkOptions = $sValue; }

	/**
	* Gets the CheckCallbacks option
	*
	*/
	public function getCheckCallbacks() { return( $this->psWidget->checkCallbacks ); }

	/***
	* Sets the CheckCallbacks option
	*
	* @param mixed $_bValue
	*/
	public function setCheckCallbacks( $bValue ) { $this->psWidget->checkCallbacks = $bValue; }

	/**
	* Options getter
	*
	* @returns array
	*/
	public function getOptions() { return( $this->psWidget->options ); }

	/**
	* Returns an element from an array if it exists, otherwise returns $sDefault value
	*
	* @param array $arOptions
	* @param string $sName
	* @return mixed
	*/
	public function getOption( $sName, $sDefault = null ) { return( $this->psWidget->getOption( $sName, $sDefault ) ); }

	/**
    * Setter
    *
    * @var array $value options
    */
	public function setOptions( $arOptions ) { $this->psWidget->setOptions( $arOptions ); }

	/**
	* ValidOptions getter
	*
	*/
	public function getValidOptions() { return( $this->psWidget->validOptions ); }
	/**
	* ValidOptions setter
	*
	*/
	public function setValidOptions( $arValue ) { $this->psWidget->validOptions = $sValue; }

	/**
	* ValidCallbacks getter
	*
	*/
	public function getValidCallbacks() { return( $this->psWidget->validCallbacks ); }
	/**
	* ValidCallbacks setter
	*
	*/
	public function setValidCallbacks( $arValue ) { $this->psWidget->validCallbacks = $arValue; }

	/**
	* Setter
	*
	* @param array $value callbacks
	*/
	public function setCallbacks( $arCallbacks ) { $this->psWidget->setCallbacks( $arCallbacks ); }

	/**
	* Getter
	*
	* @return array
	*/
	public function getCallbacks() { return( $this->psWidget->callbacks ); }

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	abstract protected function generateJavascript();

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	abstract protected function generateHtml();

}