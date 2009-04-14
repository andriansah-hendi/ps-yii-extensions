<?php
/**
 * name class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * name provides stuff
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.0
 */
class CPSgMapsWidget extends CPSgApiWidget
{
	public function __construct()
	{
		$this->m_arValidOptions = array(
			//	GMapOptions
			'size' => array( 'type' => 'array' ),
			'mapTypes' => array( 'type' => 'array' ),
			'draggableCursor' => array( 'type' => 'string' ),
			'draggingCursor' => array( 'type' => 'string' ),
			'googleBarOptions' => array( 'type' => 'array' ),
			'backgroundColor' => array( 'type' => 'string' ),
			//	Method Options
			'mapCenter' => array( 'type' => 'array' ),
			'mapType' => array( 'type' => 'string' ),
		);
	}

	public function generateJavascript()
	{
		$_sCode = "var map = new GMap2(document.getElementById(\"{$this->m_sId}\"));";
	}
}
