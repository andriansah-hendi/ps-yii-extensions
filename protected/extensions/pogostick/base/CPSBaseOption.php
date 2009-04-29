<?php
/**
 * CPSBaseOption class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSBaseOption provides a "smart" option class
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @filesource
 * @package psYiiExtensions
 * @subpackage base
 * @since 1.0.4
 */
class CPSBaseOption
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The key to this option
	*
	* @var string
	*/
	protected $m_sKey;
	/**
	* The value of this option
	*
	* @var mixed
	*/
	protected $m_oValue;
	/**
	* The delimiter for multi-part keys, defaults to '.'
	*
	* @var string
	*/
	protected $m_sDelimiter = '.';
	/**
	* Will add new options upon 'setValue' if the key does not exist. Defaults to 'true'
	*
	* @var bool
	*/
	protected $m_bAddIfNotFound = true;

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Getters
	*
	*/
	public function getKey() { return( $this->m_sKey ); }
	public function getValue( $sKey ) { return $this->getSubOption( $sKey ); }

	/**
	* Setters
	*
	* @param mixed $sValue
	*/
	protected function setKey( $sValue ) { $this->m_sKey = $sValue; }
	public function setValue( $sKey, $oValue )
	{
		$_oObject = $this->walkOptionChain( $sKey );

		if ( null !== $_oObject )
			$_oObject = $oValue;
		else
			throw new CException( Yii::t( 'psBaseOption', 'Option Value not found for key "{key}"', array( '{key}' => $sKey ) ) );
	}

	//********************************************************************************
	//* Magic Methods
	//********************************************************************************

	/**
	* Getter
	*
	* @param string $sKey
	*/
	public function __get( $sKey )
	{
		//	Call our property accessor if there...
		$_sFuncName = 'get' . $sKey;
		if ( method_exists( $this, $_sFuncName ) )
			return $this->{$_sFuncName}( $sKey );

		//	Try local members...
		if ( false !== ( $_sMemberVar = key( preg_grep( '/^m_', array_keys( get_class_vars( get_class( $this ) ) ), $sKey ) ) ) )
			return $this->{$_sMemberVar};

		//	No luck? Evil!
		throw new CException( Yii::t( 'psBaseOption', 'Option key "{key}" not found', array( '{key}' => $sKey ) ) );
	}

	/**
	* Setter
	*
	* @param string $sKey
	* @param mixed $oValue
	*/
	public function __set( $sKey, $oValue )
	{
		//	Call our property accessor if there...
		$_sFuncName = 'set' . $sKey;
		if ( method_exists( $this, $_sFuncName ) )
			return $this->{$_sFuncName}( $sKey );

		//	Try local members...
		if ( false !== ( $_sMemberVar = key( preg_grep( '/^m_', array_keys( get_class_vars( get_class( $this ) ) ), $sKey ) ) ) )
			return $this->{$_sMemberVar};

		//	No luck? Evil!
		throw new CException( Yii::t( 'psBaseOption', 'Option key "{key}" not found', array( '{key}' => $sKey ) ) );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Construct a CPSBaseOption
	*
	* @param string $sName
	* @param mixed $oValue
	* @param string $sDelimiter
	* @param bool $bAddIfNotFound
	* @return CPSBaseOption
	*/
	public function __construct( $sName, $oValue = null, $sDelimiter = '.', $bAddIfNotFound = true )
	{
		$this->m_sName = $sName;
		$this->m_oValue = $oValue;
		$this->m_sDelimiter = $sDelimiter;
		$this->m_bAddIfNotFound = $bAddIfNotFound;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Finds the option value using the global delimiter (@link CPSBaseOption::$m_sDelimiter).
	*
	* Example:
	*
	*	$this->getSubOption( 'baseUrl' );
	*	$this->getSubOption( 'us.ga.atlanta.businesses' );
	*
	* @param string $sKey
	* @return mixed If (@link CPSBaseOption::$m_bAddIfNotFound) is set to false, null will be returned.
	* Otherwise a new array() will be created at that spot in the array and returend.
	*/
	protected function &getSubOption( $sKey )
	{
		//	Not an array? Boogie...
		if ( ! is_array( $this->m_oValue ) )
			return null;

		//	Start at the top...
		$_oObject =& $this->m_oValue;

		foreach( explode( $this->m_sDelimiter, $_oObject ) as $_sKey => $_oValue )
		{
			if ( ! array_key_exists( $_sKey, $_oObject ) )
			{
				if ( $this->m_bAddIfNotFound )
				{
					//	Add a new array and return it...
					$_oObject[ $_sKey ] = array();
					return $_oObject[ $_sKey ];
				}

				//	Not there... bail
				return null;
			}

			//	Lather, rinse, repeat
			$_oObject =& $_oObject[ $_sKey ];
		}

		return $_oObject;
	}

}