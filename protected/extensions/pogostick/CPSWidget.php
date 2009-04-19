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
	//* Member Variables
	//********************************************************************************

	/**
	* The generated HTML
	*
	* @var mixed
	*/
	protected $m_sHtml = '';

	/**
	* The generated script
	*
	* @var string
	*/
	protected $m_sScript = '';

	/**
	* The base url of the source library, if one is used
	*
	* @var string
	*/
	protected $m_sBaseUrl = '';

	/**
	* Css file to override default style
	*
	* @var string
	*/
	protected $m_sCssFile = null;

	/**
	* The name of the view for this widget
	*
	* @var string
	*/
	protected $m_sViewName = '';

	/**
	* Name of widget
	*
	* @var string
	*/
	protected $m_sName = '';

	/**
	* Id of widget
	*
	* @var mixed
	*/
	protected $m_sId = '';

	/**
	* Indicates whether or not to validate options
	*
	* @var boolean
	*/
	protected $m_bCheckOptions = true;

	/**
	* Indicates whether or not to validate callbacks
	*
	* @var boolean
	*/
	protected $m_bCheckCallbacks = true;

	/**
	* Valid options for this widget
	*
	* @var array
	*/
	protected $m_arValidOptions = array();

	/**
	* Placeholder for widget options
	*
	* @var array
	*/
	public $m_arOptions = array();

	/**
	* The valid callbacks for this widget
	*
	* @var mixed
	*/
	protected $m_arValidCallbacks = array();

	/**
	* Placeholder for callbacks
	*
	* @var array
	*/
	protected $m_arCallbacks = array();

	//********************************************************************************
	//* Methods
	//********************************************************************************

	public function __construct()
	{
		//	Attach this behavior
		$this->attachBehavior( 'psComponent', array( 'class' => 'application.extensions.pogostick.behaviors.CPSComponentBehavior' ) );
	}

	/**
	* Yii widget init
	*
	*/
	public function init()
	{
		//	Get the id/name of this widget
		list( $this->m_sName, $this->m_sId ) = $this->resolveNameID();

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

	//	Read only

	/**
	* Returns the generated Html
	*/
	public function getHtml() { return( $this->m_sHtml ); }

	/**
	* Returns the generated javascript
	*/
	public function getScript() { return( $this->m_sScript ); }

	/**
	* Get the BaseUrl property
	*
	*/
	public function getBaseUrl() { return( $this->m_sBaseUrl ); }

	/**
	* Set the BaseUrl property
	*
	* @param mixed $sUrl
	*/
	public function setBaseUrl( $sUrl ) { $this->m_sBaseUrl = $sUrl; }

	/***
	* Get the Css File
	*
	*/
	public function getCssFile() { return( $this->m_sCssFile ); }

	/***
	* Set the Css file
	*
	* @param mixed $_sFile
	*/
	public function setCssFile( $_sFile ) { $this->m_sCssFile = $_sFile; }

	/**
	* Get View Name
	*
	*/
	public function getViewName() { return( $this->m_sViewName ); }
	/**
	* Set View Name
	*
	* @param string $sValue
	*/
	public function setViewName( $sValue ) { $this->m_sViewName = $sValue; }

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
	public function getOption( $sName, $sDefault = null )
	{
		return( $this->psComponent->getOption( $sName, $sDefault ) );
	}

	/**
    * Setter
    *
    * @var array $value options
    */
	public function setOptions( $arOptions )
	{
		if ( ! is_array( $arOptions ) )
			throw new CException( Yii::t( __CLASS__, 'options must be an array' ) );

		$this->psComponent->checkOptions( $arOptions );
		$this->psComponent->options = $arOptions;
	}

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
	public function setCallbacks( $arCallbacks )
	{
		if ( ! is_array( $arCallbacks ) )
			throw new CException( Yii::t( __CLASS__, 'callbacks must be an associative array' ) );

		$this->psComponent->checkCallbacks( $arCallbacks );
		$this->psComponent->callbacks = $arCallbacks;
	}

	/**
	* Getter
	*
	* @return array
	*/
	public function getCallbacks() { return( $this->psComponent->callbacks ); }

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	protected function registerClientScripts()
	{
		//	Get the clientScript
		$_oCS = Yii::app()->getClientScript();

		//	Register a special CSS file if we have one...
		if ( ! empty( $this->m_sCssFile ) )
			$_oCS->registerCssFile( Yii::app()->baseUrl . "{$this->cssFile}", 'screen' );

		//	Send upstream for convenience
		return( $_oCS );
	}

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
