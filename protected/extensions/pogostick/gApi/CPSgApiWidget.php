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
class CPSgApiWidget extends CPSWidget
{
	protected $m_sApiKey = '';
	protected $m_arApisToLoad = array();

	public function init()
	{
		$this->validOptions = array(
			'apiKey' => array( 'type' => 'string' ),
			'apisToLoad' => array( 'type' => 'array', 'valid' => array( 'maps', 'search', 'feeds', 'language', 'gdata', 'earth', 'visualization' ) ),
		);

		parent::init();
	}

	public function run()
	{
		parent::run();
		echo $this->generateHtml();
	}

	public function getApiKey()
	{
		return( $this->m_sApiKey );
	}

	public function setApiKey( $sKey )
	{
		$this->m_sApiKey = $sKey;
	}

	public function getApisToLoad()
	{
		return( $this->m_arApisToLoad );
	}

	public function setApisToLoad( $arApis )
	{
		$this->m_arApisToLoad = $arApis;
	}

	protected function generateJavascript()
	{
		foreach ( $this->apisToLoad as $_sApi => $_sVersion )
		{
//			$this->m_sScript .= "google.load(\"{$_sApi}\", \"{$_sVersion}\");";
		}

		return( $this->script );
	}

	protected function generateHtml()
	{
		return( null );
	}

	protected function registerClientScripts()
	{
		$_oCS = parent::registerClientScripts();

		//	Register scripts necessary
		$_oCS->registerScriptFile( "http://www.google.com/jsapi?key={$this->apiKey}", CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( "http://maps.google.com/maps?file=api&v=2&key={$this->apiKey}&sensor=false", CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/1.1/src/markermanager.js', CClientScript::POS_HEAD );
//		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/tabbedmaxcontent/1.0/src/tabbedmaxcontent.js', CClientScript::POS_HEAD );
		$_oCS->registerScriptFile( 'http://gmaps-utility-library.googlecode.com/svn/trunk/extinfowindow/release/src/extinfowindow.js', CClientScript::POS_HEAD );

		$_oCS->registerScript( "Yii.{__CLASS__}.#.{$this->id}", $this->generateJavascript(), CClientScript::POS_HEAD );
		$_oCS->registerScript( "Yii.{__CLASS__}.#.{$this->id}.onLoad", "initialize();", CClientScript::POS_READY );
	}
}
