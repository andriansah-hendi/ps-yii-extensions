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

	/**
	* The generated HTML
	*
	* @var mixed
	*/
	protected $m_sHtml = '';

	/**
	* The generated script
	*
	* @var string
	*/
	protected $m_sScript = '';

	/**
	* Css file to override default style
	*
	* @var string
	*/
	protected $m_sCssFile = null;

	/**
	* The name of the view for this widget
	*
	* @var string
	*/
	protected $m_sViewName = '';

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	/**
	* Returns the generated Html
	*/
	public function getHtml() { return( $this->m_sHtml ); }

	/**
	* Returns the generated Html
	*/
	protected function setHtml( $sHtml ) { $this->m_sHtml = $sHtml; }

	/**
	* Returns the generated javascript
	*/
	public function getScript() { return( $this->m_sScript ); }

	/**
	* Sets the generated javascript
	*/
	protected function setScript( $sScript ) { $this->m_sScript = $sScript; }

	/***
	* Get the Css File
	*
	*/
	public function getCssFile() { return( $this->m_sCssFile ); }

	/***
	* Set the Css file
	*
	* @param mixed $_sFile
	*/
	public function setCssFile( $_sFile ) { $this->m_sCssFile = $_sFile; }

	/**
	* Get View Name
	*
	*/
	public function getViewName() { return( $this->m_sViewName ); }
	/**
	* Set View Name
	*
	* @param string $sValue
	*/
	public function setViewName( $sValue ) { $this->m_sViewName = $sValue; }

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

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
}