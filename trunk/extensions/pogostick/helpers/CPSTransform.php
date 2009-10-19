<?php
/**
 * CPSTransform class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage helpers
 * @since v1.0.5
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */

//	Need this 
Yii::import('pogostick.helpers.CPSActiveWidgets'); 

/**
 * CPSTransform provides form helper functions
 */
class CPSTransform
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************
	
	/**
	* Transformation mapping
	* 
	* @var mixed
	*/
	protected static $m_arTransform = array(
		'@' => 'linkTransform',
		'?' => 'boolTransform',
		'#' => 'timeTransform',
	);

	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public static function cleanColumn( $sColumn )
	{
		if ( in_array( $sColumn{0}, array_keys( self::$m_arTransform ) ) ) $sColumn = substr( $sColumn, 1 );
		return $sColumn;
	}
	
	public static function value( $sType, $oValue )
	{
		foreach ( self::$m_arTransform as $_sChar => $_sMethod )
		{
			if ( $sType == $_sChar )
			{
				list( $_sColumn, $oValue, $_bLink ) = self::$_sMethod( $_sColumn, $oValue );
				break;
			}
		}
		
		return $oValue;
	}
	
	public static function column( $oModel, $arColumns = array(), $sLinkView = 'update', $sWrapTag = 'td', $arWrapOptions = array() )
	{
		$_bValue = $_sOut = null;
		$_sPK = $oModel->getTableSchema()->primaryKey;
		
		//	Build columns
		foreach ( $arColumns as $_sColumn )
		{
			$_bLink = false;
			$_oValue = null;

			foreach ( self::$m_arTransform as $_sChar => $_sMethod )
			{
				if ( $_sColumn{0} == $_sChar )
				{
					$_sRealCol = self::cleanColumn( $_sColumn );
					list( $_sColumn, $_oValue, $_bLink ) = self::$_sMethod( $_sColumn, $oModel->$_sRealCol );
					break;
				}
			}

			if ( ! $_oValue ) $_oValue = $oModel->{$_sColumn};
			
			$_sColumn = ( $_bLink || $_sPK == $_sColumn ) ?
				CHtml::link( $_oValue, array( $sLinkView, $_sPK => $oModel->{$_sPK} ) ) 
				:
				CHtml::encode( $_oValue );

			$_sOut .= ( $sWrapTag ) ? CHtml::tag( $sWrapTag, $arWrapOptions, $_sColumn ) : $_sColumn;
		}

		return $_sOut;
	}

	//********************************************************************************
	//* Private Methods 
	//********************************************************************************
	
	protected static function linkTransform( $sColumn, $oValue = null )
	{
		return array( self::cleanColumn( $sColumn ), $oValue, true );
	}
	
	protected static function boolTransform( $sColumn, $oValue )
	{
		$_oValue = ( empty( $oValue ) || $oValue === 'N' || $oValue === 'n' || $oValue === 0 ) ? 'No' : 'Yes';
		return array( self::cleanColumn( $sColumn ), $_oValue, false );
	}
	
	protected static function timeTransform( $sColumn, $oValue, $sFormat = 'F d, Y' )
	{
		return array( self::cleanColumn( $sColumn ), date( $sFormat, $oValue ), false );
	}
	
}