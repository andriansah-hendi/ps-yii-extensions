<?php
/**
 * <name> class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * <name> provides
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.4
 */
class CPSWidgetBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	public function __construct()
	{
		//	Call daddy...
		parent::__construct();

		//	Add our settings to this
		$this->getSettings()->add( 'html', '' );
		$this->getSettings()->add( 'script', '' );
		$this->getSettings()->add( 'cssFile', '' );
		$this->getSettings()->add( 'viewName', '' );
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Registers the needed CSS and JavaScript.
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