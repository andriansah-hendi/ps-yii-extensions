<?php
/**
 * CPSComponent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.gnu.org/licenses/gpl.html
 *
 * Install in <yii_app_base>/extensions/pogostick
 */

/**
 * The CPSComponent is the base class for all Pogostick components for Yii
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package applications.extensions.pogostick
 * @since 1.0.3
 */
class CPSComponent extends CApplicationComponent
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Log
		Yii::log( 'constructed psComponent object' );

		$this->attachBehaviors(
        	array(
        		'psComponent' => 'application.extensions.pogostick.behaviors.CPSComponentBehavior',
        	)
        );
	}

}
