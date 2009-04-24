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
	//* Methods
	//********************************************************************************

	/**
	* Attach behaviors during construction...
	*
	* @param CBaseController $oOwner
	*/
	public function __construct( $oOwner = null )
	{
		parent::__construct( $oOwner );

		$this->attachBehaviors(
        	array(
        		'psWidget' => 'application.extensions.pogostick.behaviors.CPSWidgetBehavior',
        	)
        );
	}

	/**
	* Yii widget init
	*
	*/
	public function init()
	{
		//	Call daddy
		parent::init();

		//	Get the id/name of this widget
		list( $this->name, $this->id ) = $this->resolveNameID();
	}

	/***
	* Handles registration of scripts
	*
	*/
	public function registerClientScripts()
	{
		//	Register the scripts/css
		return( Yii::app()->clientScript );
	}

	/**
	* Returns a property from an attached behavior or throws an exception which can be caught
	*
	* @param string $sName
	* @returns mixed
	*/
	public function getBehaviorProperty( $sName )
	{
		try
		{
			return( parent::getBehaviorProperty( $sName, $oValue ) );
		}
		catch ( Exception $_ex )
		{
			//	Try setting through "settings" of behaviors...
			$_oBehave = $this->asa( 'psWidget' );
			if ( $_oBehave && $_oBehave->hasMethod( 'getSettings' ) && $_oBehave->getSettings()->contains( $sName ) )
				try { return( $_oBehave->getSettings()->{$sName} ); } catch ( Exception $_ex ) {}

			$_oBehave = $this->asa( 'psApi' );
			if ( $_oBehave && $_oBehave->hasMethod( 'getSettings' ) && $_oBehave->getSettings()->contains( $sName ) )
				try { return( $_oBehave->getSettings()->{$sName} ); } catch ( Exception $_ex ) {}
		}

		//	This exception won't really get seen because it is ignored upstream...
		throw new CException( Yii::t( 'yii', 'Behavior Property "{class}.{property}" is not defined.', array( '{class}' => get_class( $this ), '{property}' => $sName ) ) );
	}

	/**
	* Sets a property in an attached behavior if it exists or throws a catchable exception. Overrides CComponent::setBehaviorProperty
	*
	* @param string $sName
	* @param mixed $oValue
	*/
	public function setBehaviorProperty( $sName, $oValue )
	{
		try
		{
			parent::setBehaviorProperty( $sName, $oValue );
			return;
		}
		catch ( Exception $_ex )
		{
			//	Try setting through "settings" of behaviors...
			$_oBehave = $this->asa( 'psWidget' );
			if ( $_oBehave && $_oBehave->hasMethod( 'getSettings' ) && $_oBehave->getSettings()->contains( $sName ) )
				try { $_oBehave->getSettings()->{$sName} = $oValue; if ( $_oBehave->getSettings()->{$sName} == $oValue ) return; } catch ( Exception $_ex ) {}

			$_oBehave = $this->asa( 'psApi' );
			if ( $_oBehave && $_oBehave->hasMethod( 'getSettings' ) && $_oBehave->getSettings()->contains( $sName ) )
				try { $_oBehave->getSettings()->{$sName} = $oValue; if ( $_oBehave->getSettings()->{$sName} == $oValue ) return; } catch ( Exception $_ex ) {}
		}

		//	This exception won't really get seen because it is ignored upstream...
		throw new CException( Yii::t( 'yii', 'Behavior Property "{class}.{property}" is not defined.', array( '{class}' => get_class( $this ), '{property}' => $sName ) ) );
	}

}