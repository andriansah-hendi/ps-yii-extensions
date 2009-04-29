<?php 
/**
 * CPSOptionCollection class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSOptionCollection provides a collection of "smart" option objects
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version SVN: $Id$
 * @package application.extensions.pogostick
 * @since 1.0.4
 */
class CPSBaseOption extends CMap
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	protected $m_sName;
	protected $m_oValue;
	protected $m_sDelimiter = '.';
	protected $m_bAddIfNotFound = true;

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	public function getName() { return( $this->m_sName ); }
	public function setName( $sValue ) { $this->m_sName = $sValue; }
	public function getValue( $sKey ) { return $this->walkOptionChain( $sKey, $this->m_sDelimiter, $this->m_bAddIfNotFound ); }

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

	public function __get( $sKey )
	{
		//	Call our property accessor if there...
		$_sFuncName = 'get' . $sKey;
		if ( method_exists( $this, $_sFuncName ) )
			return $this->{$_sFuncName}( $sKey );

		//	Try local members...
		$_sMemberVar = key( preg_grep( '/^m_', array_keys( get_class_vars( get_class( $this ) ) ) ) );
		if ( false !== $_sMemberVar )
			return $this->{$_sMemberVar};

		//	No luck? Evil!
		throw new CException( Yii::t( 'psBaseOption', 'Option key "{key}" not found', array( '{key}' => $sKey ) ) );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

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

	protected function &walkOptionChain( $sKey )
	{
		if ( ! is_array( $this->m_oValue ) )
			return null;

		$_oObject =& $m_oValue;

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