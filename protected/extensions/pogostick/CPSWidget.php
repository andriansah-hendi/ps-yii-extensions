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
		//	Log
		Yii::log( 'constructed psWidget object for [' . get_parent_class() . ']' );

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
	* Handles registration of scripts & css files...
	*
	*/
	public function registerClientScripts()
	{
		//	Get the clientScript
		$_oCS = Yii::app()->getClientScript();

		//	Register a special CSS file if we have one...
		if ( ! empty( $this->cssFile ) )
			$_oCS->registerCssFile( Yii::app()->baseUrl . "{$this->cssFile}", 'screen' );

		//	Send upstream for convenience
		return( $_oCS );
	}
}