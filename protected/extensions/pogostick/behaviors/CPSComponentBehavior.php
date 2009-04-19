<?php
/**
 * CPSComponentBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSComponentBehavior provides base component behaviors to other classes
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.4
 */
class CPSComponentBehavior extends CBehavior
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

	/**
	* Indicates whether or not to validate callbacks
	*
	* @var boolean
	*/
	protected $m_bCheckCallbacks = true;

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
	//* Property Accessors
	//********************************************************************************

	/**
	* Options getter
	*
	* @returns array
	*/
	public function getOptions() { return( $this->m_arOptions ); }

	/**
	* Returns an element from an array if it exists, otherwise returns $sDefault value
	*
	* @param array $arOptions
	* @param string $sName
	* @return mixed
	*/
	public function getOption( $sName, $sDefault = null )
	{
		if ( isset( $this->m_arOptions[ $sName ] ) )
			return( $this->m_arOptions[ $sName ] );

		return( $sDefault );
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
	public function getCheckOptions() { return( $this->m_bCheckOptions ); }
	/**
	* Sets the CheckOptions option
	*
	* @param boolean $bValue
	*/
	public function setCheckOptions( $sValue ) { $this->m_bCheckOptions = $sValue; }

	/**
	* ValidOptions getter
	*
	*/
	public function getValidOptions() { return( $this->m_arValidOptions ); }
	/**
	* ValidOptions setter
	*
	*/
	public function setValidOptions( $arValue ) { $this->m_arValidOptions = $sValue; }

	/**
	* ValidCallbacks getter
	*
	*/
	public function getValidCallbacks() { return( $this->m_arValidCallbacks ); }
	/**
	* ValidCallbacks setter
	*
	*/
	public function setValidCallbacks( $arValue ) { $this->m_arValidCallbacks = $arValue; }

	/**
	* Setter
	*
	* @param array $value callbacks
	*/
	public function setCallbacks( $arCallbacks )
	{
		if ( ! is_array( $arCallbacks ) )
			throw new CException( Yii::t( __CLASS__, 'callbacks must be an associative array' ) );

		$this->checkCallbacks( $arCallbacks );
		$this->callbacks = $arCallbacks;
	}

	/**
	* Getter
	*
	* @return array
	*/
	public function getCallbacks() { return( $this->m_arCallbacks ); }

	/**
    * Check the options against the valid ones
    *
    * @param array $value user's options
    * @param array $validOptions valid options
    */
	public function checkOptions( $arOptions = null )
 	{
 		$_arOptions = ( $arOptions == null ) ? $this->options : $arOptions;

		if ( ! empty( $this->validOptions ) && $this->checkOptions )
		{
			foreach ( $_arOptions as $_sKey => $_oValue )
			{
				if ( ! array_key_exists( $_sKey, $this->validOptions ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

				$_sType = gettype( $_oValue );

				if ( ( ! is_array( $this->validOptions[ $_sKey ][ 'type' ] ) && ( $_sType != $this->validOptions[ $_sKey ][ 'type' ] ) ) || ( is_array( $this->validOptions[ $_sKey ][ 'type' ] ) && ! in_array( $_sType, $this->validOptions[ $_sKey ][ 'type' ] ) ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $this->validOptions[ $_sKey ][ 'type' ] ) ) ? implode( ', ', $this->validOptions[ $_sKey ][ 'type' ] ) : $this->validOptions[ $_sKey ][ 'type' ] ) ) );

				if ( array_key_exists( 'valid', $this->validOptions[ $_sKey ] ) )
				{
					if ( ! in_array( $_oValue, $this->validOptions[ $_sKey ][ 'valid' ] ) )
						throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $this->validOptions[ $_sKey ][ 'valid' ] ) ) ) );
				}

				if ( ( $_sType == 'array' ) && array_key_exists( 'elements', $this->validOptions[ $_sKey ] ) )
					$this->checkOptions( $_oValue, $this->validOptions[ $_sKey ][ 'elements' ] );
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
	public function makeOptions( $arOptions = null )
	{
		$_arOptions = ( $arOptions == null ) ? array() : $arOptions;

		foreach ( $this->m_arCallbacks as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
				$_arOptions[ "cb_{$_sKey}" ] = $_sKey;
		}

		//	Get all the options merged...
		$_sEncodedOptions = CJavaScript::encode( array_merge( $_arOptions, $this->m_arOptions ) );

		//	Fix up the callbacks...
		foreach ( $this->m_arCallbacks as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
			{
				if ( 0 == strncasecmp( $_oValue, 'function(', 9 ) )
					$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "{$_sKey}:{$_oValue}", $_sEncodedOptions );
				else
					$_sEncodedOptions = str_replace( "'cb_{$_sKey}':'{$_sKey}'", "{$_sKey}:'{$_oValue}'", $_sEncodedOptions );
			}
		}

		return( $_sEncodedOptions );
	}

	/**
	* Validates that required options have been specified...
	*
	*/
	public function validateOptions()
	{
		foreach ( $this->options as $_sKey => $_oValue )
		{
			//	Is it a valid option?
			if ( ! array_key_exists( $_sKey, $this->validOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			if ( isset( $this->validOptions[ $_sKey ][ 'required' ] ) && $this->validOptions[ $_sKey ][ 'required' ] && ( ! $this->options[ $_sKey ] || empty( $this->options[ $_sKey ] ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}
	}

   /**
    *
    * @param array $value user's callbacks
    * @param array $validCallbacks valid callbacks
    */
	public function checkCallbacks( $arCallbacks = null )
	{
		$_arCallbacks = ( $arCallbacks == null ) ? $this->callbacks : $arCallbacks;

		if ( ! empty( $this->validCallbacks ) && $this->checkCallbacks )
		{
			foreach ( $_arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $this->validCallbacks ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $this->validCallbacks ) ) ) );
			}
		}
	}

}