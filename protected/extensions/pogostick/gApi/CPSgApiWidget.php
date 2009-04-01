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
class CPSgApiWidget extends CPogostickWidget
{
	protected $m_sApiKey = '';
	protected $m_arApisToLoad = array();

	public function __construct()
	{
		$this->m_arValidOptions = array(
			'apisToLoad' => array( 'type' => 'array', 'valid' => array( 'maps', 'search', 'feeds', 'language', 'gdata', 'earth', 'visualization' ) ),
		);
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
		$_sCode = parent::generateJavascript();

		foreach ( $this->m_arApisToLoad as $_sApi => $_sVersion )
		{
			$_sCode .= "google.load(\"{$_sApi}\", \"{$_sVersion}\");";
		}

		return( $_sCode );
	}

	protected function generateHtml()
	{
		$_sHtml = parent::generateHtml();
		return( $_sHtml );
	}

	protected function registerClientScripts()
	{
		$_oCS = parent::registerClientScripts();

		//	Register scripts necessary
		$_oCS->registerScriptFile( "http://www.google.com/jsapi?key={$this->m_sApiKey}", CClientScript::POS_HEAD );
		$_oCS->registerScript( "Yii.{$this->m_sClassName}.#.{$this->m_sId}", $this->generateJavascript(), CClientScript::POS_HEAD );
	}
}
