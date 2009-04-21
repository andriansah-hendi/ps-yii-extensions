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
abstract class CPSComponent extends CApplicationComponent
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	public function preinit()
	{
		//	Attach this behavior before init is called...
		$this->attachBehavior( 'psComponent', array( 'class' => 'application.extensions.pogostick.behaviors.CPSComponentBehavior' ) );
	}
}
