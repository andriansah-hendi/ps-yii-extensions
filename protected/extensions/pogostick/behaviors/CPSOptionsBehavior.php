<?php
/**
 * CPSOptionsBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionsBehavior provides base class for generic options settings for use with any class.
 * Avoids the need for declaring member variables and provides convenience magic functions to search the options.
 *
 * addOptions array format:
 *
 * array(
 * 		'optionName' = array(
 * 			'value' => default value,
 * 			'type' => 'typename' (i.e. string, array, integer, etc.),
 * 			'valid' => array( 'v1', 'v2', 'v3', etc.) // An array of valid values for the option
 * 		)
 * )
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package application.extensions.pogostick.behaviors
 * @since 1.0.4
 */
abstract class CPSOptionsBehavior extends CBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Holds the settings for this object
	*
	* @var array
	*/
	private static $m_arOptions = array();
	/**
	* The delimiter to use for sub-options. Defaults to '.'
	*
	* @var string
	*/
	private static $m_sDelimiter = '.';

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Delimiter getter
	* @returns string The currently set delimiter. Defaults to '.'
	*/
	public function getDelimiter() { return( $this->m_sDelimiter ); }
	/**
	* Delimiter setter
	* @var $sValue The string to use as a delimiter
	*/
	public function setDelimiter( $sValue ) { $this->m_sDelimiter = $sValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Adds an option to the behavior
	*
	* @param array $arOptions The array (key=>value pairs) of options to set
	* @param mixed $oValue
	*/
	public function addOptions( array $arOptions )
	{
		foreach ( $arOptions as $_sKey => $_oValue )
			$_oObject =& $this->addOption( $_sKey, $_oValue, $arOptions );

		//	Add final 'value' if not set in array...
		if ( ! isset( $_oObject[ 'value' ] ) )
			$_oObject[ 'value' ] = null;
	}

	/**
	* Adds a single option to the behavior
	*
	* @param string $sKey
	* @param mixed $oValue
	* @param array $arOrigArgs
	* @return array The last option processed
	*/
	public function &addOption( $sKey, $oValue = null, &$arOrigArgs = null )
	{
		//	Is the key bogus?
		if ( null == $sKey || '' == trim( $sKey ) )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Invalid property name "{property}".', array( '{property}' => $sKey ) ) );

		//	Get the object...
		$_arTemp = $this->walkOptionChain( $sKey );
		$_oObject =& $_arTemp[ 'object' ];

		//	Set the value...
		return( $_oObject[ ( ! $_arTemp[ 'status' ] ) ? $_arTemp[ 'missingKey' ] : $_arTemp[ 'lastKey' ] ] = $oValue );
	}

	/**
	* Get the value of the supplied option key
	*
	* @param string $sKey
	* @param boolean $bOnlyPublic Returns only options that are not marked as 'private' => true
	* @returns array|null
	*/
	public function &getOption( $sKey, $bOnlyPublic = false )
	{
		//	Find options object
		if ( ! ( $_arTemp = $this->walkOptionChain( $sKey ) ) )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Property "{class}"."{property}" is not defined.', array( '{class}' => get_class( $this ), '{property}' => $sKey ) ) );

		//	Key not found...
		if ( ! $_arTemp[ 'status' ] )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Property "{class}"."{property}" is not defined.', array( '{class}' => get_class( $this ), '{property}' => $sKey ) ) );

		return( ( $_arTemp[ 'containsPrivate' ] ) ? null : $_arTemp[ 'object' ] );
	}

	/**
	* Get the non-referenced 'value' property of the supplied option key
	*
	* @param string $sKey
	* @param boolean $bOnlyPublic Returns only options that are not marked as 'private' => true
	* @returns mixed|null
	*/
	public function getOptionValue( $sKey, $bOnlyPublic = false )
	{
		$_oObject =& $this->getOption( $sKey, $bOnlyPublic );
		return( ( is_array( $_oObject ) && array_key_exists( 'value', $_oObject ) ) ? $_oObject[ 'value' ] : null );
	}

	/***
	* Set the value of the supplied option key
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function setOption( $sKey, $oValue )
	{
		$_arTemp = $this->walkOptionChain( $sKey );

		if ( null == $_arTemp[ 'object' ] || ! $_arTemp[ 'status' ] )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Options key "{key}" from "{masterKey}" not found.', array( '{key}' => $_arTemp[ 'lastKey' ], '{masterKey}' => $sKey  ) ) );

		//	Set the object's value
		$_oObject = $oValue;
	}

	/***
	* Set the value of the supplied option key
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function setOptionValue( $sKey, $oValue )
	{
		//	Take off extranious .value key as we are adding that below.
		if ( '.value' == substr( $sKey, strlen( $sKey ) - 6 ) )
			$sKey = substr( $sKey, strlen( $sKey ) - 6 );

		$_arTemp = $this->walkOptionChain( $sKey );
		$_oObject =& $_arTemp[ 'object' ];

		if ( null == $_oObject || ! $_arTemp[ 'status' ] )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Options key "{key}" from "{masterKey}" not found.', array( '{key}' => $_arTemp[ 'lastKey' ], '{masterKey}' => $sKey  ) ) );

		//	Set the object's value
		$_oObject[ 'value' ] = $oValue;
	}

	/**
	* Walks the options chain and returns the final object...
	*
	* @param string $sOptionKey Key to start at
	* @return array Returns an array with various information about the call
	*/
	public function &walkOptionChain( $sOptionKey )
	{
		//	Start with reference to the options array
		$_oObject =& self::$m_arOptions;

		//	Local vars...
		$_bPrivate = false;
		$_bContainsPrivate = false;

		//	Our return object...
		$_arReturn = array( 'status' => true, 'object' => null, 'lastKey' => null, 'keyArray' => array(), 'containsPrivate' => false, 'missingKey' => null );

		//	If start key given, scoot up to it...
		foreach ( explode( self::$m_sDelimiter, $sOptionKey ) as $_sKeyName )
		{
			//	Set return parameters
			$_arReturn[ 'keyArray' ][ 'name' ] = $_arReturn[ 'lastKey' ] = $_sKeyName;
			$_bPrivate = ( isset( $_oObject[ 'private' ] ) && $_oObject[ 'private' ] );
			$_arReturn[ 'keyArray' ][ 'private' ] = $_bPrivate;
			$_arReturn[ 'containsPrivate' ] = ( bool )( $_bContainsPrivate |= $_bPrivate );

			//	Is this key contained within array?
			if ( is_array( $_oObject ) && ! array_key_exists( $_sKeyName, $_oObject ) )
			{
				$_arReturn[ 'object' ] =& $_oObject;
				$_arReturn[ 'status' ] = false;
				$_arReturn[ 'missingKey' ] = $_sKeyName;
				break;
			}

			//	Move our reference
			$_oObject =& $_oObject[ $_sKeyName ];

			//	Set our object reference...
			$_arReturn[ 'object' ] =& $_oObject;
		}

		//	Return our object
		return( $_arReturn );
	}

	//********************************************************************************
	//* Magic Function Overrides
	//********************************************************************************

	/**
	 * Returns a property value or an event handler list by property or event name. You can access sub-option settings via using the delimiter between options.
	 * The default delimiter is '.'. For example, you can get $this->validOptions.baseUrl instead of making two calls. There is no limit to the depth.
	 * @param string the property name or the event name
	 * @return mixed the property value or the event handler list
	 * @throws CException if the property/event is not defined.
	 */
	public function __get( $sArgs )
	{
		//	Try local options first...
		try { $oObject =& $this->getOptionValue( $sArgs ); return( $oObject ); } catch ( Exception $_ex ) { /* Ignore for passthru */ }

		//	Look for member variables with that name
		return( parent::__get( $sArgs ) );
	}

	/**
	 * Sets value of a component property. You can access sub-option settings via using the delimiter between options.
	 * The default delimiter is '.'. For example, you can set $this->validOptions.baseUrl = '/mybaseurl' instead of making two calls. There is no limit to the depth.
	 * @param string the property name or event name
	 * @param mixed the property value or event handler
	 * @throws CException If the property is not defined or read-only.
	 */
	public function __set( $sArgs, $oValue = null )
	{
		//	Look in options array...
		try { $this->setOptionValue( $sArgs, $oValue ); return; } catch ( Exception $_ex ) { /* Ignore for passthru */ }

		//	Look for member variables to set...
		return( parent::__set( $sArgs, $oValue ) );

		//	Default, add as new option...
//		$this->addOption( ( string )$sArgs, $oValue );
	}

	/**
	 * Checks if a property value is null.
	 * @param string the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset( $sName )
	{
		return( null != $this->__get( $sName ) );
	}

	/**
	 * Sets a component property to be null.
	 * @param string the property name or the event name
	 * @since 1.0.1
	 */
	public function __unset( $sName )
	{
		$_oObject =& $this->__get( $sName );

		if ( null != $_oObject )
			$_oObject = null;
	}
}