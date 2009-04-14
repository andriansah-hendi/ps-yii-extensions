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

//********************************************************************************
//* Include Files
//********************************************************************************

require_once( dirname( __FILE__ ) . '/../CPogostickWidget.php' );

/**
* The CPSjqRatingWidgetallows the JQ Rating (@link http://www.fyneworks.com/jquery/star-rating/) to be used in Yii.
*
* @author Jerry Ablan <jablan@pogostick.com>
* @version $Id$
* @package applications.extensions.pogostick.jqRating
* @since 1.0.3
*/
class CPSjqRatingWidget extends CPogostickWidget
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
	protected $m_sUpdateElement;
	protected $m_bReturnString = false;
	protected $m_sHtml = '';
	protected $m_sScript = '';
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
	public function getUpdateElement() { return( $this->m_sUpdateElement ); }
	public function setUpdateElement( $sValue ) { $this->m_sUpdateElement = $sValue; }
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
	* @return CPSjqRatingWidget
	*/
	public function __construct( $arOptions = null )
	{
		$this->m_arValidOptions = array(
			'readOnly' => array( 'type' => 'boolean' ),
			'required' => array( 'type' => 'boolean' ),
		);

		$this->m_arValidCallbacks = array(
			'callback',
			'focus',
			'blur',
		);

		parent::__construct( $arOptions );
	}

	/***
	* Runs this widget
	*
	*/
	public function run()
	{
		//	Validate baseUrl
		if ( empty( $this->m_sBaseUrl ) )
			throw new CHttpException( 500, 'CPSjqRatingWidget: baseUrl is required.');

		//	Register the scripts/css
		$this->registerClientScripts();

		$this->m_sHtml = $this->render( 'CPSjqRatingWidgetView',
				array( "options" => $this->m_arOptions ),
				$this->m_bReturnString
		);

		return( $this->m_sHtml );
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
		$_oCS->registerScriptFile( "{$this->m_sBaseUrl}/jquery.MetaData.js" );
		$_oCS->registerScriptFile( "{$this->m_sBaseUrl}/jquery.rating.js" );

		//	Get the javascript for this widget
		$_sScript = $this->generateJavascript();

		if ( ! $this->m_bSupressScripts && ! $this->m_bReturnString )
				$_oCS->registerScript( 'Yii.' . $this->m_sClassName . '#' . $this->m_sId, $_sScript, CClientScript::POS_READY );

		//	Register css files...
		$_oCS->registerCssFile( "{$this->m_sBaseUrl}/jquery.rating.css", 'screen' );
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
		//	Update an element with value if required...
		if ( ! isset( $this->m_arCallbacks[ 'callback' ] ) && ( $this->m_sUpdateElement != null || ! empty( $this->m_sAjaxCallback ) ) )
		{
			if ( $this->m_sUpdateElement != null )
				$this->m_arCallbacks[ 'callback' ] = 'function(value,link){document.getElementById(\''.$this->m_sUpdateElement.'\').value=value}';
			else if ( ! empty( $this->m_sAjaxCallback ) )
			{
				$_arTemp = array(
					'type' => 'GET',
					'url' => Yii::app()->createUrl( $this->m_sAjaxCallback ),
					'dataType' => 'html'
				);

				$_sCBBody = 'function(value,link){var arTemp = ' . CJavaScript::encode( $_arTemp ) . '; arTemp[\'data\'] = \'value=\'+value+\'&link=\'+link; $.ajax(arTemp);';
				if ( $this->m_sUpdateElement )
					$_sCBBody .= " document.getElementById('{$this->m_sUpdateElement}').value=value;";
				$_sCBBody .= '}';

				$this->m_arCallbacks[ 'callback' ] = $_sCBBody;
			}
		}

		$_arOptions = $this->makeOptions();

		//	Now rating apply...
		$this->m_sScript .= '$(\'.' . $this->m_sStarClass . '\').rating(' . $_arOptions . '); ';

		return( $this->m_sScript );
	}

	/**
	* Generates the javascript code for the widget
	*
	* @return string
	*/
	protected function generateHtml()
	{
		$_iMaxCount = $this->m_iStarCount;

		//	Handle multiple star outputs...
		if ( $this->m_bHalf )
			$this->m_iSplit = 2;

		if ( $this->m_iSplit > 1 )
			$_iMaxCount *= $this->m_iSplit;

		for ( $_i = 0; $_i < $_iMaxCount; $_i++ )
		{
			$_sHtml .= '<input type="radio" class="' . $this->m_sStarClass;

			if ( $this->m_bHalf )
				$_sHtml .= ' {half:true}';
			else if ( $this->m_iSplit > 1 )
				$_sHtml .= ' {split:' . $this->m_iSplit . '}';

			$_sHtml .= '" name="' . $this->m_sName . '" ';

			if ( is_array( $this->m_arStarTitles ) && sizeof( $this->m_arStarTitles ) > 0 )
				$_sHtml .= 'title="' . $this->m_arStarTitles[ $_i ] . '" ';

			if ( is_array( $this->m_arStarValues ) && sizeof( $this->m_arStarValues ) > 0 )
				$_sHtml .= 'value="' . $this->m_arStarValues[ $_i ] . '" ';
			else
				$_sHtml .= 'value="' . ( $_i + 1 ) . '" ';

			if ( $this->m_fSelectValue != 0 && ( $this->m_fSelectValue * $this->m_iSplit ) == ( $_i + 1 ) )
				$_sHtml .= 'checked="checked" ';

			$_sHtml .= ' />';
		}

		return( $_sHtml );
	}
 }