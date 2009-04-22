<?php
/**
 * CPSAPIComponent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSAPIComponent provides a convenient base class for APIs
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package application.extensions.pogostick.CPSAPIComponent
 * @since 1.0.4
 */
class CPSAPIComponent extends CPSComponent
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* The behaviors for this class. Only return this classes behaviors.
	*
	*/
	public function __construct()
	{
		parent::__construct();
		
		$this->attachBehaviors(
			array(
        		'psApi' => 'application.extensions.pogostick.behaviors.CPSAPIBehavior',
        	)
		);
	}
}