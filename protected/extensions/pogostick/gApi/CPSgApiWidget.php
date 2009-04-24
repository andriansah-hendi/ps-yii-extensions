<?php
/**
 * CPSgApiWidget class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSgApiWidget provides access to the Google AJAX API
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package application.extensions.pogostick.gApi
 * @since 1.0.3
 */
class CPSgApiWidget extends CPSApiWidget
{
	/**
	* Constructor
	*
	*/
	public function __construct()
	{
		parent::__construct();

		//	Our object settings
		$this->settings->widgetOptions = array_merge( ( is_array( $this->settings->widgetOptions ) ? $this->settings->widgetOptions : array() ),
			array(
				'apisToLoad',
			)
		);

		//	Validation
		$this->settings->validWidgetOptions = array_merge( ( is_array( $this->settings->validWidgetOptions ) ? $this->settings->validWidgetOptions : array() ),
			array(
				'apisToLoad' => array( 'type' => 'array', 'valid' => array( 'maps', 'search', 'feeds', 'language', 'gdata', 'earth', 'visualization' ) ),
			)
		);
	}

	public function run()
	{
		parent::run();
		$this->registerClientScripts();
		echo $this->generateHtml();
	}

	protected function generateJavascript()
	{
		foreach ( $this->widgetOptions[ 'apisToLoad' ] as $_sApi => $_sVersion )
		{
//			$this->m_sScript .= "google.load(\"{$_sApi}\", \"{$_sVersion}\");";
		}

		return( $this->script );
	}

	protected function generateHtml()
	{
		return( null );
	}

	public function registerClientScripts()
	{
		$_oCS = parent::registerClientScripts();

		$_sApiKey = $this->apiKey;

		//	Register scripts necessary
		$_oCS->registerScriptFile( "http://www.google.com/jsapi?key={$_sApiKey}", CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( "http://maps.google.com/maps?file=api&v=2&key={$_sApiKey}&sensor=false", CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/1.1/src/markermanager.js', CClientScript::POS_HEAD );
//		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/tabbedmaxcontent/1.0/src/tabbedmaxcontent.js', CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/extinfowindow/release/src/extinfowindow.js', CClientScript::POS_HEAD );

		$_oCS->registerScript( "Yii.{__CLASS__}.#.{$this->id}", $this->generateJavascript(), CClientScript::POS_HEAD );
		$_oCS->registerScript( "Yii.{__CLASS__}.#.{$this->id}.onLoad", "initialize();", CClientScript::POS_READY );
	}
}
