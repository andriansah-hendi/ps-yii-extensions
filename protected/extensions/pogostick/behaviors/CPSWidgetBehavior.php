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
		//	Log
		Yii::log( 'constructed CPSWidgetBehavior object for [' . get_parent_class() . ']' );

		parent::__construct();

		//	Add our settings to this
		$this->addOptions( self::getBaseOptions() );
	}

	/**
	* Allows for single behaviors
	*
	*/
	protected function getBaseOptions()
	{
		return(
			array(
				'html' => array( 'value' => '', 'type' => 'string' ),
				'script' => array( 'value' => '', 'type' => 'string' ),
				'cssFile' => array( 'value' => '', 'type' => 'string' ),
				'viewName' => array( 'value' => '', 'type' => 'string' ),
			)
		);
	}

}