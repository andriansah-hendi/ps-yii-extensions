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
class CPSComponentBehavior extends CPSOptionsBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	//********************************************************************************
	//* Constructor
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Set up our base settings
		$this->addOption( 'baseUrl', array( 'value' => '', 'check' => array( 'type' => 'string' ) ) );
		$this->addOption( 'checkOptions', array( 'value' => true, 'check' => array( 'type' => 'boolean' ) ) );
		$this->addOption( 'validOptions', array( 'value' => array(), 'check' => array( 'type' => 'array' ) ) );
		$this->addOption( 'options', array( 'value' => array(), 'check' => array( 'type' => 'array' ) ) );
		$this->addOption( 'checkCallbacks', array( 'value' => true, 'check' => array( 'type' => 'boolean' ) ) );
		$this->addOption( 'validCallbacks', array( 'value' => array(), 'check' => array( 'type' => 'array' ) ) );
		$this->addOption( 'callbacks', array( 'value' => array(), 'check' => array( 'type' => 'array' ) ) );
	}

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
    * Check the options against the valid ones
    *
    * @param array $value user's options
    * @param array $validOptions valid options
    */
	protected function checkOptions( $arOptions = null, $arValidOptions = null )
 	{
		if ( ! isset( $arValidOptions ) )
			$arValidOptions = $this->getOption( 'validOptions.value' );

		if ( ! isset( $arOptions ) )
			$arOptions = $this->getOption( 'options.value' );

		foreach ( $arOptions as $_sKey => $_oValue )
		{
			if ( is_array( $arValidOptions ) && ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			$_sType = gettype( $_oValue );
			$_oVOType = $arValidOptions[ $_sKey ][ 'type' ];

			if ( ( ! is_array( $_oVOType ) && ( $_sType != $_oVOType ) ) || ( is_array( $_oVOType ) && ! in_array( $_sType, $_oVOType ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" must be of type "{y}"', array( '{x}' => $_sKey, '{y}' => ( is_array( $_oVOType ) ) ? implode( ', ', $_oVOType ) : $_oVOType ) ) );

			if ( array_key_exists( 'valid', $arValidOptions[ $_sKey ] ) )
			{
				$_arValid = $arValidOptions[ $_sKey ][ 'valid' ];

				if ( is_array( $_arValid[ 'valid' ] ) && ! in_array( $_oValue, $_arValid ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: "{y}"', array( '{x}' => $_sKey, '{y}' => implode( ', ', $_arValid ) ) ) );
			}

			if ( ( $_sType == 'array' ) && array_key_exists( 'elements', $arValidOptions[ $_sKey ] ) )
				$this->checkOptions( $_oValue, $arValidOptions[ $_sKey ][ 'elements' ] );
		}

		//	Now validate them...
		$this->validateOptions( $arOptions, $arValidOptions );
	}

	/**
	* Generates the options for the widget
	*
	* @param array $arOptions
	* @return string
	*/
	protected function makeOptions( $arOptions = null )
	{
		$_arOptions = ( $arOptions == null ) ? $this->getOption( 'options.value' ) : $arOptions;

		foreach ( $this->getOption( 'callbacks.value' ) as $_sKey => $_oValue )
		{
			if ( ! empty( $_oValue ) )
				$_arOptions[ "cb_{$_sKey}" ] = $_sKey;
		}

		//	Get all the options merged...
		$_arToEncode = array();

		foreach( $_arOptions as $_oOption )
		{
			if ( isset( $_oOption[ 'private' ] ) && true == $_oOption[ 'private' ] )
				continue;

			$_arToEncode[ $_oOption[ 'name' ] ] = $_oOption[ 'value' ];
		}

		$_sEncodedOptions = CJavaScript::encode( $_arToEncode );

		//	Fix up the callbacks...
		foreach ( $this->getOption( 'callbacks.value' ) as $_sKey => $_oValue )
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
	protected function validateOptions( $arOptions , $arValidOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
		{
			//	Is it a valid option?
			if ( ! array_key_exists( $_sKey, $arValidOptions ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is not a valid option', array( '{x}' => $_sKey ) ) );

			if ( isset( $arValidOptions[ $_sKey ][ 'required' ] ) && $arValidOptions[ $_sKey ][ 'required' ] && ( ! $arOptions[ $_sKey ] || empty( $arOptions[ $_sKey ] ) ) )
				throw new CException( Yii::t( __CLASS__, '"{x}" is a required option', array( '{x}' => $_sKey ) ) );
		}
	}

   /**
    *
    * @param array $value user's callbacks
    * @param array $validCallbacks valid callbacks
    */
	protected function checkCallbacks( $arCallbacks = null, $arValidCallbacks = null )
	{
		if ( ! empty( $arValidCallbacks ) && is_array( $arValidCallbacks ) )
		{
			foreach ( $arCallbacks as $_sKey => $_oValue )
			{
				if ( ! in_array( $_sKey, $arValidWidgetCallbacks ) )
					throw new CException( Yii::t( __CLASS__, '"{x}" must be one of: {y}', array( '{x}' => $_sKey, '{y}' => implode( ', ', $arValidCallbacks ) ) ) );
			}
		}
	}

}