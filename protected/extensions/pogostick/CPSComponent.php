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
 * @since 1.0.4
 */
class CPSComponent extends CComponent
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Indicates whether or not to validate options
	*
	* @var boolean
	*/
	protected $m_bCheckOptions = true;

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

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constucts a CPSComponent
	*
	* @param array $arOptions
	* @return CPSComponent
	*/
	public function __construct( $arOptions = null )
	{
		//	Store the passed in options...
		if ( is_array( $arOptions ) )
			$this->m_arOptions = $arOptions;

		//	Make sure the options are cool...
		$this->checkOptions( $this->m_arOptions, $this->m_arValidOptions );

		//	Fill my data up...
		foreach ( $arOptions as $_sKey => $_oValue )
			$this->{$_sKey} = $_oValue;
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Options getter
	*
	* @returns array
	*/
	public function getOptions()
	{
		return( $this->m_arOptions );
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
	* @param boolean $bValue
	*/
	public function setCheckOptions( $bValue )
	{
		$this->m_bCheckOptions = $bValue;
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

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
					throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

				$_sType = gettype( $_oValue );

				if ( ( ! is_array( $arValidOptions[ $_sKey ][ 'type' ] ) && ( $_sType != $arValidOptions[ $_sKey ][ 'type' ] ) ) || ( is_array( $arValidOptions[ $_sKey ][ 'type' ] ) && ! in_array( $_sType, $arValidOptions[ $_sKey ][ 'type' ] ) ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $arValidOptions[ $_sKey ][ 'type' ] ) ) ? implode( ', ', $arValidOptions[ $_sKey ][ 'type' ] ) : $arValidOptions[ $_sKey ][ 'type' ] ) ) );

				if ( array_key_exists( 'valid', $arValidOptions[ $_sKey ] ) )
				{
					if ( ! in_array( $_oValue, $arValidOptions[ $_sKey ][ 'valid' ] ) )
						throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $arValidOptions[ $_sKey ][ 'valid' ] ) ) ) );
				}

				if ( ( $_sType == 'array' ) && array_key_exists( 'elements', $arValidOptions[ $_sKey ] ) )
					$this->checkOptions( $_oValue, $arValidOptions[ $_sKey ][ 'elements' ] );
			}

			//	Now validate them...
			$this->validateOptions();
		}
   }

	/**
	* Generates the options for the widget
	*
	* @param array $arOptions
	* @return string
	*/
	protected function makeOptions( $arOptions = null )
	{
		return( CJavaScript::encode( ( $arOptions == null ) ? $this->m_arOptions : array_merge( $arOptions, $this->m_arOptions ) ) );
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
	* Validates that required options have been specified...
	*
	*/
	protected function validateOptions()
	{
		foreach ( $this->m_arOptions as $_sKey => $_oValue )
		{
			//	Is it a valid option?
			if ( ! array_key_exists( $_sKey, $this->m_arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			if ( isset( $this->m_arValidOptions[ $_sKey ][ 'required' ] ) && $this->m_arValidOptions[ $_sKey ][ 'required' ] && ( ! $this->m_arOptions[ $_sKey ] || empty( $this->m_arOptions[ $_sKey ] ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}
	}
}
