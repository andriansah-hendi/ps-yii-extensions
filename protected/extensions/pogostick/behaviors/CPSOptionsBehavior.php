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
 * CPSOptionsBehavior provides generic options settings for use with widgets and components. Avoids the need for declaring member variables.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package application.extensions.pogostick.behaviors
 * @since 1.0.4
 */
class CPSOptionsBehavior extends CBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* Holds the settings for this object
	*
	* @var array
	*/
	protected $m_arOptions = array();
	/**
	* The delimiter to use for sub-options. Defaults to '.'
	*
	* @var string
	*/
	protected $m_sDelimiter = '.';

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	/**
	* Options getter
	*
	*/
	public function getOptions() { return( $this->m_arOptions ); }
	/**
	* Options setter
	* @var $oValue The value to override the default options type (array)
	*/
	public function setOptions( $oValue ) { $this->m_arOptions = $oValue; }
	/**
	* Delimiter getter
	* @returns The currently set delimiter. Defaults to '.'
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

	public function addOption( $sKey, $oValue )
	{
		//	Split up the input string
		$_arArgs = explode( $this->m_sDelimiter, $sKey );
		$_oObject =& $this->m_arOptions;

		//	Start at the top...
		try { $_oArray = $this->getOption( $sKey ); $_oObject = $_oArray; } catch ( Exception $_ex ) {}

		//	Loop through sub-elements
		if ( ! is_array( $_arArgs ) || ! isset( $_oObject ) )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Property "{class}"."{property}" is not defined.', array( '{class}' => get_class( $this ), '{property}' => $sKey ) ) );

		foreach ( $_arArgs as $_sName )
			$_oObject =& $_oObject[ $_sName ];

		//	Set the reference...
		$_oObject = $oValue;
	}

	/**
	* Get the value of the supplied option key
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function &getOption( $sKey, $bOnlyPublic = false )
	{
		//	Will be set to true if we find a private item...
		$_bPrivate = false;

		//	Split up the input string
		$_arArgs = explode( $this->m_sDelimiter, $sKey );

		//	Start at the top...
		$_oObject =& $this->m_arOptions;

		//	Loop through sub-elements
		if ( ! is_array( $_arArgs ) || ! isset( $_oObject ) )
			throw new CException( Yii::t( 'psOptionsBehavior', 'Invalid property requested: "{class}"."{property}"', array( '{class}' => get_class( $this ), '{property}' => $sKey ) ) );

		//	Load up the keys...
		foreach ( $_arArgs as $_sName )
		{
			//	Not in options array? Bail...
			if ( ! array_key_exists( $_sName, $_oObject ) )
				throw new CException( Yii::t( 'psOptionsBehavior', 'Property "{class}"."{property}" is not defined.', array( '{class}' => get_class( $this ), '{property}' => $sKey ) ) );

			//	Refresh the $_oObject variable
			$_oObject =& $_oObject[ $_sName ];

			if ( $bOnlyPublic && isset( $_oObject[ 'private' ] ) && $_oObject[ 'private' ] )
				$_bPrivate = true;
		}

		return( ( $_bPrivate ) ? null : $_oObject );
	}

	/***
	* Set the value of the supplied option key
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function setOption( $sKey, $oValue )
	{
		$_oObject = $this->getOption( $sKey );

		if ( null != $_oObject )
			$_oObject = $oValue;
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	//********************************************************************************
	//* Magic Functions
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
		try
		{
			if ( null != ( $_oObject = $this->getOption( $sArgs ) ) )
				return( $_oObject->clone() );
		}
		catch ( Exception $_ex ) { /* Ignore and try local */ }

		//	Look for member variables with that name
		if ( false === strpos( $sArgs, $this->m_sDelimiter ) )
			return( parent::__get( $sArgs ) );
	}

	/**
	 * Sets value of a component property. You can access sub-option settings via using the delimiter between options.
	 * The default delimiter is '.'. For example, you can set $this->validOptions.baseUrl = '/mybaseurl' instead of making two calls. There is no limit to the depth.
	 * @param string the property name or event name
	 * @param mixed the property value or event handler
	 * @throws CException If the property is not defined or read-only.
	 */
	public function __set( $sArgs, $oValue )
	{
		//	Look for member variables to set...
		try
		{
			if ( false === strpos( $sArgs, $this->m_sDelimiter ) && ! isset( $this->{$sArgs} ) )
			{
				parent::__set( $sArgs, $oValue );
				return;
			}
		}
		catch ( Exception $_ex ) { /* Ignore and try local */ }

		//	Look in options array...
		try
		{
			$this->setOption( $sArgs, $oValue );
			return;
		} catch ( Exception $_ex ) { /* Ignore for passthru */ }

		//	Default, add as new option...
		$this->addOption( $sArgs, $oValue );
	}

	/**
	 * Checks if a property value is null.
	 * @param string the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset( $sName )
	{
		//	Check locally...
		try
		{
			return( $this->__get( $sName ) !== null );
		}
		catch ( Exception $_ex ) {}

		return( false );
	}

	/**
	 * Sets a component property to be null.
	 * @param string the property name or the event name
	 * @since 1.0.1
	 */
	public function __unset( $sName )
	{
		if ( null != ( $_oObject = $this->getOption( $sName ) ) )
			$_oObject = null;
		else
			parent::__unset( $sName );
	}
}