<?php
/**
 * CPSFacebookConnect class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSFacebookConnect provides an interface to Facebook Connect
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$

 * @since 1.0.0
 */
class CPSFacebookConnect extends CPogostickWidget
{
	/**
	* Our constructor
	*
	*/
	public function __construct( $arOptions = null )
	{
		$this->m_arValidOptions = array(
			'appId' => array( 'type' => 'string' ),
			'apiKey' => array( 'type' => 'string', 'required' => true ),
			'secretKey' => array( 'type' => 'string', 'required' => true ),
			'callbackUrl' => array( 'type' => 'string', 'required' => true ),
			'xdrUrl' => array( 'type' => 'string', 'required' => true ),
		);

		parent::__construct( $arOptions );
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Register the scripts/css
		$this->registerClientScripts();
	}

	protected function generateJavascript()
	{
		$_sOut =<<<JSCRIPT
FB.init('{$this->getOption( 'apiKey' )}', '{$this->getOption( 'xdrUrl' )}' );
FB.ensureInit(
	function()
	{
//    	FB.Connect.showPermissionDialog( "email" );
	}
);
JSCRIPT;

		return( $_sOut );
  	}

	/**
	* Register the necessary Facebook Connect scripts...
	*
	*/
	public function registerClientScripts()
	{
		$_oCS = parent::registerClientScripts();
		$_oCS->registerScriptFile( 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php', CClientScript::POS_HEAD );

		$_oCS->registerScript( 'Yii.' . $this->m_sClassName . '#' . $this->m_sId, $this->generateJavascript(), CClientScript::POS_READY );
	}
}
