<?php
/**
 * CPogostickWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 *
 * Install in <yii_app_base>/extensions/pogostick
 */

/**
 * The CPogostickWidget is the base class for all Pogostick widgets for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package applications.extensions.pogostick
 * @since 1.0.3
 */
class CPogostickWidget extends CInputWidget
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

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
	* The valid callbacks for this widget
	*
	* @var mixed
	*/
	protected $m_arValidCallbacks = array();

	/**
	* Placeholder for widget options
	*
	* @var array
	*/
	public $m_arOptions = array();

	/**
	* Placeholder for callbacks
	*
	* @var array
	*/
	protected $m_arCallbacks = array();

	/***
	* My name
	*
	* @var string
	*/
	protected $m_sClassName = '';

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

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constucts a CPogostickWidget
	*
	* @param array $arOptions
	* @param array $arCallbacks
	* @return CPogostickWidget
	*/
	public function __construct( $arOptions = null, $arCallbacks = null )
	{
		//	Store the passed in options...
		if ( is_array( $arOptions ) )
			$this->m_arOptions = $arOptions;

		if ( is_array( $arCallbacks ) )
			$this->m_arCallbacks = $arCallbacks;

		//	Save class name for later
		$this->m_sClassName = get_class( $this );
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

	/**
	* Get the BaseUrl property
	*
	*/
	public function getBaseUrl()
	{
		return( $this->m_sBaseUrl );
	}

	/**
	* Set the BaseUrl property
	*
	* @param mixed $sUrl
	*/
	public function setBaseUrl( $sUrl )
	{
		$this->m_sBaseUrl = $sUrl;
	}

	/***
	* Get the Css File
	*
	*/
	public function getCssFile()
	{
		return( $this->m_sCssFile );
	}

	/***
	* Set the Css file
	*
	* @param mixed $_sFile
	*/
	public function setCssFile( $_sFile )
	{
		$this->m_sCssFile = $_sFile;
	}

	/**
    * Setter
    *
    * @var array $value options
    */
	public function setOptions( $arOptions )
	{
		if ( ! is_array( $arOptions ) )
			throw new CException( Yii::t( $this->m_sClassName, 'options must be an array' ) );

		$this->checkOptions( $arOptions, $this->m_arValidOptions );
		$this->m_arOptions = $arOptions;
	}

	/**
	* Gets the CheckOptions option
	*
	*/
	public function getCheckOptions()
	{
		return( $this->m_bCheckOptions );
	}

	/**
	* Sets the CheckOptions option
	*
	* @param mixed $_bValue
	*/
	public function setCheckOptions( $_bValue )
	{
		$this->m_bCheckOptions = $_bValue;
	}

	/**
	* Gets the CheckCallbacks option
	*
	*/
	public function getCheckCallbacks()
	{
		return( $this->m_bCheckCallbacks );
	}

	/***
	* Sets the CheckCallbacks option
	*
	* @param mixed $_bValue
	*/
	public function setCheckCallbacks( $_bValue )
	{
		$this->m_bCheckCallbacks = $_bValue;
	}

	/**
	* Getter
	*
	* @return array
	*/
	public function getOptions()
	{
		return( $this->m_arOptions );
	}

	/**
	* Setter
	*
	* @param array $value callbacks
	*/
	public function setCallbacks( $arCallbacks )
	{
		if ( ! is_array( $arCallbacks ) )
			throw new CException( Yii::t( $this->m_sClassName, 'callbacks must be an associative array' ) );

		$this->checkCallbacks( $arCallbacks, $this->m_arValidCallbacks );
		$this->m_arCallbacks = $arCallbacks;
	}

	/**
	* Getter
	*
	* @return array
	*/
	public function getCallbacks()
	{
		return( $this->m_arCallbacks );
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	protected function registerClientScripts( $arOptions = null )
	{
		//	Get the clientScript
		$_oCS = Yii::app()->getClientScript();

		//	Register a special CSS file if we have one...
		if ( ! empty( $this->m_sCssFile ) )
			$_oCS->registerCssFile( Yii::app()->baseUrl . "{$this->m_sCssFile}", 'screen' );

		//	Send upstream for convenience
		return( $_oCS );
	}

	/**
    * Check the options against the valid ones
    *
    * @param array $value user's options
    * @param array $validOptions valid options
    */
	protected function checkOptions( $arOptions, $arValidOptions )
 	{
		if ( ! empty( $arValidOptions ) && $this->m_bCheckOptions )
		{
			foreach ( $arOptions as $_sKey => $_oValue )
			{
				if ( ! array_key_exists( $_sKey, $arValidOptions ) )
					throw new CException( Yii::t( $this->m_sClassName, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

				$_sType = gettype( $_oValue );

				if ( ( ! is_array( $arValidOptions[ $_sKey ][ 'type' ] ) && ( $_sType != $arValidOptions[ $_sKey ][ 'type' ] ) ) || ( is_array( $arValidOptions[ $_sKey ][ 'type' ] ) && ! in_array( $_sType, $arValidOptions[ $_sKey ][ 'type' ] ) ) )
					throw new CException( Yii::t( $this->m_sClassName, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $arValidOptions[ $_sKey ][ 'type' ] ) ) ? implode( ', ', $arValidOptions[ $_sKey ][ 'type' ] ) : $arValidOptions[ $_sKey ][ 'type' ] ) ) );

				if ( array_key_exists( 'valid', $arValidOptions[ $_sKey ] ) )
				{
					if ( ! in_array( $_oValue, $arValidOptions[ $_sKey ][ 'valid' ] ) )
						throw new CException( Yii::t( $this->m_sClassName, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $arValidOptions[ $_sKey ][ 'valid' ] ) ) ) );
				}

				if ( ( $_sType == 'array' ) && array_key_exists( 'elements', $arValidOptions[ $_sKey ] ) )
					$this->checkOptions( $_oValue, $arValidOptions[ $_sKey ][ 'elements' ] );
			}

			//	Now validate them...
			$this->validateOptions();
		}
   }

   /**
    *
    * @param array $value user's callbacks
    * @param array $validCallbacks valid callbacks
    */
	protected function checkCallbacks( $arCallbacks, $arValidCallbacks )
	{
		if ( ! empty( $arValidCallbacks ) && $this->m_bCheckCallbacks )
		{
			foreach ( $arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $arValidCallbacks ) )
					throw new CException( Yii::t( $this->m_sClassName, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $arValidCallbacks ) ) ) );
			}
		}
	}

	/**
	* Generates the options for the widget
	*
	* @return string
	*/
	protected function makeOptions()
	{
		$_arOptions = array();

		foreach ( $this->m_arCallbacks as $_sKey => $_oValue )
			$_arOptions[ "cb_{$_sKey}" ] = $_sKey;

		$_sEncodedOptions = CJavaScript::encode( array_merge( $_arOptions, $this->m_arOptions ) );

		foreach ( $this->m_arCallbacks as $_sKey => $_oValue )
			$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "'{$_sKey}': {$_oValue}", $_sEncodedOptions );

		return( $_sEncodedOptions );
	}

	/**
	* Gets a single option's value from the option array
	*
	* @param string $sKey
	* @return mixed
	*/
	protected function getOption( $sKey )
	{
		if ( array_key_exists( $sKey, $this->m_arOptions ) )
			return( $this->m_arOptions[ $sKey ] );

		return( null );
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript( $arOptions = null )
	{
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml( $arOptions = null )
	{
	}

	/**
	* Validates that required options have been specified...
	*
	*/
	protected function validateOptions()
	{
		foreach ( $this->m_arOptions as $_sKey => $_oValue )
		{
			//	Is it a valid option?
			if ( ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( $this->m_sClassName, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			if ( isset( $this->m_arValidOptions[ $_sKey ][ 'required' ] ) && $this->m_arValidOptions[ $_sKey ][ 'required' ] && ( ! $this->arOptions[ $_sKey ] || empty( $this->arOptions[ $_sKey ] ) ) )
				throw new CException( Yii::t( $this->m_sClassName, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}
	}
}
