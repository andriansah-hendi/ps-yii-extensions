<?php
/**
* CPSjqRatingWidget class file.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @link http://www.pogostick.com/
* @copyright Copyright &copy; 2009 Pogostick, LLC
* @license http://www.gnu.org/licenses/gpl.html
*
* Install in <yii_app_base>/extensions/pogostick/jqRating
*/

/**
* The CPSjqRatingWidgetallows the JQ Rating (@link http://www.fyneworks.com/jquery/star-rating/) to be used in Yii.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version $Id$
* @package applications.extensions.pogostick.jqRating
* @since 1.0.3
*/
class CPSjqRatingWidget extends CPSWidget
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	protected $m_iStarCount = 5;
	protected $m_arStarTitles = array();
	protected $m_arStarValues = array();
	protected $m_sStarClass = 'star';
	protected $m_bHalf = false;
	protected $m_iSplit = 1;
	protected $m_arHoverTips = array();
	protected $m_bReturnString = false;
	protected $m_sAjaxCallback = null;
	protected $m_bSupressScripts = false;
	protected $m_fSelectValue = 0;

	//********************************************************************************
	//* Properties
	//********************************************************************************

	public function getStarCount() { return( $this->m_iStarCount ); }
	public function setStarCount( $sValue ) { $this->m_iStarCount = $sValue; }
	public function getStarTitles() { return( $this->m_arStarTitles ); }
	public function setStarTitles( $sValue ) { $this->m_arStarTitles = $sValue; }
	public function getStarValues() { return( $this->m_arStarValues ); }
	public function setStarValues( $sValue ) { $this->m_arStarValues = $sValue; }
	public function getStarClass() { return( $this->m_sStarClass ); }
	public function setStarClass( $sValue ) { $this->m_sStarClass = $sValue; }
	public function getHalf() { return( $this->m_bHalf ); }
	public function setHalf( $sValue ) { $this->m_bHalf = $sValue; }
	public function getSplit() { return( $this->m_iSplit ); }
	public function setSplit( $sValue ) { $this->m_iSplit = $sValue; }
	public function getHoverTips() { return( $this->m_arHoverTips ); }
	public function setHoverTips( $sValue ) { $this->m_arHoverTips = $sValue; }
	public function getReturnString() { return( $this->m_bReturnString ); }
	public function setReturnString( $sValue ) { $this->m_bReturnString = $sValue; }
	public function getAjaxCallback() { return( $this->m_sAjaxCallback ); }
	public function setAjaxCallback( $sValue ) { $this->m_sAjaxCallback = $sValue; }
	public function getSupressScripts() { return( $this->m_bSupressScripts ); }
	public function setSupressScripts( $sValue ) { $this->m_bSupressScripts = $sValue; }
	public function getSelectValue() { return( $this->m_fSelectValue ); }
	public function setSelectValue( $sValue ) { $this->m_fSelectValue = $sValue; }

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	* Constructs a CPSjqRatingWidget
	*
	* @param array $arOptions
	*/
	public function init()
	{
		$this->validOptions = array(
			'cancel' => array( 'type' => 'string' ),
			'cancelValue' => array( 'type' => 'string' ),
			'readOnly' => array( 'type' => 'boolean' ),
			'required' => array( 'type' => 'boolean' ),
			'resetAll' => array( 'type' => 'boolean' ),
		);

		$this->validCallbacks = array(
			'callback',
			'focus',
			'blur',
		);

		//	Set our view name...
		$this->viewName = __CLASS__ . 'View';

		//	Call daddy...
		parent::init();
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( empty( $this->baseUrl ) )
			throw new CHttpException( 500, __CLASS__ . ': baseUrl is required.');

		//	Register the scripts/css
		$this->registerClientScripts();

		$this->html = $this->render( $this->viewName,
				array( "options" => $this->options ),
				$this->returnString
		);

		return( $this->html );
	}

	/**
	* Registers the needed CSS and JavaScript.
	*
	* @param string $sId
	*/
	public function registerClientScripts()
	{
		//	Daddy...
		$_oCS = parent::registerClientScripts();

		//	Register scripts necessary
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.MetaData.js" );
		$_oCS->registerScriptFile( "{$this->baseUrl}/jquery.rating.js" );

		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript();

		if ( ! $this->supressScripts && ! $this->returnString )
				$_oCS->registerScript( 'PS.' . __CLASS__ . '#' . $this->id, $_sScript, CClientScript::POS_READY );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->baseUrl}/jquery.rating.css", 'screen' );
	}

	//********************************************************************************
	//* Private methods
	//********************************************************************************

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateJavascript()
	{
		//	No callback set? then make the ajax callback
		if ( ! isset( $this->callbacks[ 'callback' ] ) && ! empty( $this->ajaxCallback ) )
		{
			$_arTemp = array(
				'type' => 'GET',
				'url' => Yii::app()->createUrl( $this->ajaxCallback ),
				'dataType' => 'html'
			);

			$_sCBBody = 'function(value,link){var arTemp = ' . CJavaScript::encode( $_arTemp ) . '; arTemp[\'data\'] = \'value=\'+value+\'&link=\'+link; $.ajax(arTemp);}';

			$this->callbacks[ 'callback' ] = $_sCBBody;
		}

		$_arOptions = $this->makeOptions();

		//	Now rating apply...
		$this->script .= '$(\'.' . $this->starClass . '\').rating(' . $_arOptions . '); ';

		return( $this->script );
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
		$_iMaxCount = $this->starCount;

		//	Handle multiple star outputs...
		if ( $this->half )
			$this->split = 2;

		if ( $this->split > 1 )
			$_iMaxCount *= $this->split;

		for ( $_i = 0; $_i < $_iMaxCount; $_i++ )
		{
			$_sHtml .= '<input type="radio" class="' . $this->starClass;

			if ( $this->half )
				$_sHtml .= ' {half:true}';
			else if ( $this->split > 1 )
				$_sHtml .= ' {split:' . $this->split . '}';

			$_sHtml .= '" name="' . $this->name . '" ';

			if ( is_array( $this->starTitles ) && sizeof( $this->starTitles ) > 0 )
				$_sHtml .= 'title="' . $this->starTitles[ $_i ] . '" ';

			if ( is_array( $this->starValues ) && sizeof( $this->starValues ) > 0 )
				$_sHtml .= 'value="' . $this->starValues[ $_i ] . '" ';
			else
				$_sHtml .= 'value="' . ( $_i + 1 ) . '" ';

			if ( $this->selectValue != 0 && ( $this->selectValue * $this->split ) == ( $_i + 1 ) )
				$_sHtml .= 'checked="checked" ';

			$_sHtml .= ' />';
		}

		return( $_sHtml );
	}

 	/**
	* Convenience function to create a star rating widget
	*
	* Available options:
	*
	* suppressScripts	boolean		If true, scripts will be stored in the member variable 'scripts' and not output
	* returnString		boolean		If true, the output of this widget will be stored in a string and not echo'd. It is available through the member variable 'html'
	* baseUrl			string		The location of the jqRating installation
	* id				string		The HTML id of the widget. Defaults to null
	* name				string		The HTML name of the widget. Defaults to rating{x}, x is incremented with each use.
	* starClass			string		The HTML class name of the widget's output
	* split				integer		The number of times to split each star. Allows for 1/2 and 1/4 ratings, etc. Default 0
	* starCount			integer		The number of stars to display. Default 5
	* selectValue		integer		The value to mark as 'preselected' when displaying
	* readOnly			boolean		Makes the widget read-only, no input allowed.
	* required			boolean		Disables the 'cancel' button so user can only select one of the specified values
	* cancel			string		The tooltip text for the cancel button, defaults to 'Cancel Rating'
	* cancelValue		string		The value assigned to the widget when the cancel button is selected
	* ajaxCallback		function	The URL to call when a star is clicked. This URL is called via AJAX. Will be overriden by a value in 'callback' below...
	*
	* Available Callbacks
	*
	* callback			function	The Javascript function executed when a star is clicked
	* blur				function	The Javascript function executed when stars are blurred
	* focus				function	The Javascript function executed when stars are focused
	*
	* @param array $arOptions
	* @returns CPSjqRatingWidget
	*/
	public static function createRating( $arOptions )
	{
		static $_iIdCount = 0;

		$sBaseUrl = CAppHelpers::getOption( $arOptions, 'baseUrl' );
		$sId = CAppHelpers::getOption( $arOptions, 'id', null );
		$sName = CAppHelpers::getOption( $arOptions, 'name' );

		//	Build the options...
		$_arOptions = array(
			'supressScripts' => CAppHelpers::getOption( $arOptions, 'supressScripts', false ),
			'returnString' => CAppHelpers::getOption( $arOptions, 'returnString', false ),
			'baseUrl' => Yii::app()->baseUrl . ( $sBaseUrl == null ? '/extra/jqRating' : $sBaseUrl ),
			'name' => ( $sName == null ? 'rating' . $_iIdCount : $sName ),
			'starClass' => CAppHelpers::getOption( $arOptions, 'starClass', 'star' ),
			'split' => CAppHelpers::getOption( $arOptions, 'split', 1 ),
			'starCount' => CAppHelpers::getOption( $arOptions, 'starCount', 5 ),
			'selectValue' => CAppHelpers::getOption( $arOptions, 'selectValue', 0 ),
			'ajaxCallback' => CAppHelpers::getOption( $arOptions, 'ajaxCallback' ),
			'starTitles' => CAppHelpers::getOption( $arOptions, 'starTitles' ),
			'starValues' => CAppHelpers::getOption( $arOptions, 'starValues' ),
			'hoverTips' => CAppHelpers::getOption( $arOptions, 'hoverTips' ),
			'options' => array(
				'cancel' => CAppHelpers::getOption( $arOptions, 'cancel', 'Cancel Rating' ),
				'cancelValue' => CAppHelpers::getOption( $arOptions, 'cancelValue', '' ),
				'readOnly' => CAppHelpers::getOption( $arOptions, 'readOnly', Yii::app()->user->isGuest ),
				'required' => CAppHelpers::getOption( $arOptions, 'required', false ),
				'resetAll' => CAppHelpers::getOption( $arOptions, 'resetAll', false ),
			),
			'callbacks' => array(
				'callback' => CAppHelpers::getOption( $arOptions, 'callback', null ),
				'focus' => CAppHelpers::getOption( $arOptions, 'focus', null ),
				'blur' => CAppHelpers::getOption( $arOptions, 'blur', null ),
			),
		);

		//	Not logged in? No ratings for you!
		if ( Yii::app()->user->isGuest )
			unset( $_arOptions[ 'ajaxCallback' ] );

		$_oWidget = Yii::app()->controller->widget(
			'application.extensions.pogostick.jqRating.CPSjqRatingWidget',
			$_arOptions
		);

		//	Return my created widget
		return( $_oWidget );
 	}
}