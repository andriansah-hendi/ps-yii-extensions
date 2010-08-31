<?php
/*
 * This file is part of the psYiiExtensions package.
 *
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSController provides filtered access to resources
 *
 * @package 	psYiiExtensions
 * @subpackage 	controllers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.4
 *
 * @filesource
 */
abstract class CPSController extends CController implements IPSBase
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* @var integer The number of items to display per page
	*/
	const PAGE_SIZE = 10;

	/**
	* @var string Indexes into {@link CPSController:m_arUserActionMap}
	*/
	const ACCESS_TO_ALL = 0;
	const ACCESS_TO_ANY = 0;
	const ACCESS_TO_ANON = 0;
	const ACCESS_TO_GUEST = 1;
	const ACCESS_TO_AUTH = 2;
	const ACCESS_TO_ADMIN = 3;
	const ACCESS_TO_SUPERADMIN = 5;

	//	Last...
	const ACCESS_TO_NONE = 6;

	/**
	 * The name of our command form field
	 */
	const COMMAND_FIELD_NAME = '__psCommand';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	protected $_menu = array();
	public function getMenu() { return $this->_menu; }
	public function setMenu( $value ) { $this->_menu = $value; }

	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	protected $_breadcrumbs = array();
	public function getBreadcrumbs() { return $this->_breadcrumbs; }
	public function setBreadcrumbs( $value ) { $this->_breadcrumbs = $value; }

	/**
	 * An optional, additional page heading
	 * @var string
	 */
	protected $m_sPageHeading;
	public function getPageHeading() { return $this->m_sPageHeading; }
	public function setPageHeading( $sValue ) { $this->m_sPageHeading = $sValue; }

	/***
	* Allows you to change your action prefix
	*
	* @var string
	*/
	protected $m_sMethodPrefix = 'action';
	public function getMethodPrefix() { return $this->m_sMethodPrefix; }
	public function setMethodPrefix( $sValue ) { $this->m_sMethodPrefix = $sValue; }

	/**
	* @var CActiveRecord The currently loaded data model instance.
	* @access protected
	*/
	protected $m_oModel = null;
	public function getModel() { return $this->m_oModel; }
	protected function setModel( $oValue ) { $this->m_oModel = $oValue; }

	/**
	* @var string The name of the model for this controller
	* @access protected
	*/
	protected $m_sModelName = null;
	protected $_modelName = null;
	public function getModelName() { return $this->m_sModelName; }
	protected function setModelName( $value )
	{
		$this->_modelName = $this->m_sModelName = $value;
		$this->m_sSearchStateId = 'PS_' . strtoupper( $value ) . '_SEARCH_CRIT';
		$this->m_arCurrentSearchCriteria = PS::_gs( $this->m_sSearchStateId );
	}

	/**
	* Convenience access to isPostRequest
	* @returns boolean
	*/
	public function getIsPostRequest() { return Yii::app()->getRequest()->isPostRequest; }

	/**
	* Convenience access to isAjaxRequest
	* @returns boolean
	*/
	public function getIsAjaxRequest() { return Yii::app()->getRequest()->isAjaxRequest; }

	/**
	 * Returns the base url of the current app
	 * @returns string
	 */
	public function getAppBaseUrl() { return Yii::app()->getBaseUrl(); }

	/**
	* The id in the state of our current filter/search criteria
	*
	* @var string
	*/
	protected $m_sSearchStateId = null;

	/**
	* Stores the current search criteria
	*
	* @var array
	*/
	protected $m_arCurrentSearchCriteria = null;
	public function getSearchCriteria() { return $this->m_arCurrentSearchCriteria; }
	public function setSearchCriteria( $arValue )
	{
		$this->m_arCurrentSearchCriteria = $arValue;
		Yii::app()->user->setState( $this->m_sSearchStateId, $arValue );
	}

	/**
	 * @var string the default layout for the controller view. Defaults to 'application.views.layouts.column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $_pageLayout = 'main';
	public function getPageLayout() { return $this->_pageLayout = $this->layout; }
	public function setPageLayout( $value ) { $this->_pageLayout = $this->layout = $value; }

	/**
	 * @var string the layout of the content portion of this controller. If specified,
	 * content is passed through this layout before it is sent to your main page layout.
	 */
	protected $_contentLayout = null;
	public function getContentLayout() { return $this->_contentLayout; }
	public function setContentLayout( $value ) { $this->_contentLayout = $value; }

	/**
	* @var boolean Try to find proper layout to use
	* @access protected
	*/
	protected $m_bAutoLayout = true;
	public function getAutoLayout() { return $this->m_bAutoLayout; }
	public function setAutoLayout( $bValue ) { $this->m_bAutoLayout = $bValue; }

	/**
	* @var boolean Try to find missing action
	* @access protected
	*/
	protected $m_bAutoMissing = true;
	public function getAutoMissing() { return $this->m_bAutoMissing; }
	public function setAutoMissing( $bValue ) { $this->m_bAutoMissing = $sValue; }

	/**
	* @var array An associative array of POST commands and their applicable methods
	* @access protected
	*/
	protected $m_arCommandMap = array();
	public function getCommandMap() { return $this->m_arCommandMap; }
	public function setCommandMap( $oValue ) { $this->m_arCommandMap = $oValue; }
	public function addCommandToMap( $sKey, $oValue = null, $eWhich = null ) { $this->m_arCommandMap[ $sKey ] = $oValue; if ( $eWhich ) $this->addUserActions( $eWhich, array( $sKey ) ); }

	/**
	* Action queue for keeping track of where we are...
	* @var array
	*/
	protected $m_arActionQueue = array();

	/**
	 * A list of actions registered by our portlets
	 * @var array
	 */
	protected $m_arPortletActions = array();
	public function getPortletActions() { return $this->m_arPortletActions; }
	public function setPortletActions( $arValue ) { $this->m_arPortletActions = $arValue; }
	public function addPortletAction( $sName, $arCallback ) { $this->m_arPortletActions[ $sName ] = $arCallback; }

	/**
	* @var array An array of actions permitted by any user
	* @access protected
	*/
	protected $m_arUserActionList = array();
	protected function resetUserActionList() { $this->m_arUserActionList = array(); $this->addUserAction( self::ACCESS_TO_ANY, 'error' ); }
	protected function setUserActionList( $eWhich, $arValue ) { $this->m_arUserActionList[ $eWhich ] = null; $this->addUserActions( $eWhich, $arValue ); }
	public function getUserActionList( $eWhich ) { return PS::o( $this->m_arUserActionList, $eWhich ); }
	public function addUserActionRole( $eWhich, $sRole, $sAction ) { $this->m_arUserActionList[ $eWhich ]['roles'][] = $arValue; }

	public function removeUserAction( $eWhich, $sAction )
	{
		if ( ! isset( $this->m_arUserActionList[ $eWhich ] ) || ! is_array( $this->m_arUserActionList[ $eWhich ] ) )
			return;

		if ( in_array( $sAction, $this->m_arUserActionList[ $eWhich ] ) )
			unset( $this->m_arUserActionList[ $eWhich ][ $sAction ] );
	}

	public function addUserAction( $eWhich, $sAction )
	{
		if ( ! isset( $this->m_arUserActionList[ $eWhich ] ) || ! is_array( $this->m_arUserActionList[ $eWhich ] ) )
			$this->m_arUserActionList[ $eWhich ] = array();

		if ( ! in_array( $sAction, $this->m_arUserActionList[ $eWhich ] ) )
			$this->m_arUserActionList[ $eWhich ][] = $sAction;

		//	Make sure we don't lose our error handler...
		if ( $eWhich == self::ACCESS_TO_ANY )
		{
			if ( ! in_array( 'error', $this->m_arUserActionList[ $eWhich ] ) )
				$this->addUserAction( self::ACCESS_TO_ANY, 'error' );
		}
	}

	public function addUserActions( $eWhich, $arActions = array() )
	{
		if ( ! is_array( PS::o( $this->m_arUserActionList, $eWhich ) ) )
			$this->m_arUserActionList[ $eWhich ] = array();

		foreach ( $arActions as $_sAction )
			$this->addUserAction( $eWhich, $_sAction );
	}

	protected $_displayName;
	protected function setDisplayName( $value ) { $this->_displayName = $value; }
	protected function getDisplayName() { return $this->_displayName; }

	protected $_cleanTrail;
	protected function setCleanTrail( $value ) { $this->_cleanTrail = $value; }
	protected function getCleanTrail() { return $this->_cleanTrail; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Initialize the controller
	*
	*/
	public function init()
	{
		//	Phone home...
		parent::init();

		//	Find layout...
		if ( PHP_SAPI != 'cli' && $this->m_bAutoLayout && ! Yii::app() instanceof CConsoleApplication ) if ( file_exists( Yii::app()->getBasePath() . '/views/layouts/' . $this->getId() . '.php' ) ) $this->_pageLayout = $this->getId();

		//	Allow errors
		$this->addUserAction( self::ACCESS_TO_ANY, 'error' );

		//	Pull any search criteria we've stored...
		if ( $this->getModelName() ) $this->m_arCurrentSearchCriteria = Yii::app()->user->getState( $this->m_sSearchStateId );

		//	And some defaults...
		$this->_cleanTrail = $this->_displayName;
		$this->defaultAction = 'index';
	}

	/**
	 * How about a default action that displays static pages? Huh? Huh?
	 *
	 * In your configuration file, configure the urlManager as follows:
	 *
	 *	'urlManager' => array(
	 *		'urlFormat' => 'path',
	 *		'showScriptName' => false,
	 *		'rules' => array(
	 *			... all your rules should be first ...
	 *			//	Add this as the last line in your rules.
	 *			'<view:\w+>' => 'default/_static',
	 *		),
	 *
	 * The above assumes your default controller is DefaultController. If is different
	 * simply change the route above (default/_static) to your default route.
	 *
	 * Finally, create a directory under your default controller's view path:
	 *
	 *		/path/to/your/app/protected/views/default/_static
	 *
	 * Place your static files in there, for example:
	 *
	 *		/path/to/your/app/protected/views/default/_static/aboutUs.php
	 *		/path/to/your/app/protected/views/default/_static/contactUs.php
	 *		/path/to/your/app/protected/views/default/_static/help.php
	 *
	 * @return array
	 */
	public function actions()
	{
		return array_merge(
			array(
				'_static' => array(
					'class' => 'CViewAction',
					'basePath' => '_static',
				),
			),
			parent::actions()
		);
	}

	/**
	* A generic action that renders a page and passes in the model
	*
	* @param string The action id
	* @param CModel The model
	* @param array Extra parameters to pass to the view
	* @param string The name of the variable to pass to the view. Defaults to 'model'
	*/
	public function genericAction( $sActionId, $oModel = null, $arExtraParams = array(), $sModelVarName = 'model', $sFlashKey = null, $sFlashValue = null, $sFlashDefaultValue = null )
	{
		if ( $sFlashKey ) Yii::app()->user->setFlash( $sFlashKey, $sFlashValue, $sFlashDefaultValue );
		$this->render( $sActionId, array_merge( $arExtraParams, array( $sModelVarName => ( $oModel ) ? $oModel : $this->loadModel() ) ) );
	}

	/**
	* Returns the data model based on the primary key given in the GET variable.
	* If the data model is not found, an HTTP exception will be raised.
	*
	* @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	* @throws CHttpException
	*/
	public function loadModel( $iId = null )
	{
		if ( null === $this->m_oModel )
		{
			$_iId = PS::o( $_GET, 'id', $iId );
			$this->m_oModel = $this->load( $_iId );

			//	No data? bug out
			if ( null === $this->m_oModel ) $this->redirect( array( $this->defaultAction ) );

			//	Get the name of this model...
			$this->setModelName( get_class( $this->m_oModel ) );
		}

		//	Return our model...
		return $this->m_oModel;
	}

	/**
	* Provide automatic missing action mapping...
	* Also handles a theme change request from any portlets
	*
	* @param string $sActionId
	*/
	public function missingAction( $sActionId = null )
	{
		if ( $this->m_bAutoMissing )
		{
			if ( empty( $sActionId ) ) $sActionId = $this->defaultAction;

			if ( $this->getViewFile( $sActionId ) )
			{
				$this->render( $sActionId );
				return;
			}
		}
		
		parent::missingAction( $sActionId );
	}

	/**
	* Our error handler...
	*
	*/
	public function actionError()
	{
		if ( ! $_arError = Yii::app()->errorHandler->error )
		{
			if ( $this->isAjaxRequest )
				echo $_arError['message'];
			else
				throw new CHttpException( 404, 'Page not found.' );
		}

		$this->render( 'error', array( 'error' => $_arError ) );
	}

	/**
	* Convenience access to Yii request
	*
	*/
	public function getRequest()
	{
		return Yii::app()->getRequest();
	}

	/**
	 * See if there are any commands that need processing
	 * @param CAction $oAction
	 * @return boolean
	 */
	public function beforeAction( $oAction )
	{
		//	If we have commands, give it a shot...
		if ( count( $this->m_arCommandMap ) && parent::beforeAction( $oAction ) )
			$this->processCommand();

		return true;
	}

	/**
	 * @return CWebModule the module that this controller belongs to. It returns Yii::app() if the controller does not belong to any module
	 * @since 1.0.6
	 */
	public function getModule()
	{
		if ( ! $_oModule = parent::getModule() )
			$_oModule = Yii::app();

		return $_oModule;
	}

	/**
	 * Renders a view with a layout.
	 *
	 * This method first calls {@link renderPartial} to render the view (called content view).
	 * It then renders the layout view which may embed the content view at appropriate place.
	 * In the layout view, the content view rendering result can be accessed via variable
	 * <code>$content</code>. At the end, it calls {@link processOutput} to insert scripts
	 * and dynamic contents if they are available.
	 *
	 * By default, the layout view script is "protected/views/layouts/main.php".
	 * This may be customized by changing {@link layout}.
	 *
	 * @param string name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array data to be extracted into PHP variables and made available to the view script
	 * @param boolean whether the rendering result should be returned instead of being displayed to end users.
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @see renderPartial
	 * @see getLayoutFile
	 */
	public function newRender( $viewName, $viewData = null, $returnString = false )
	{
		//	make sure we're all on the same page...
		$this->_pageLayout = $this->layout;
		
		$_output = $this->renderPartial( $viewName, $viewData, true );
		
		if ( $this->_pageLayout && false !== ( $_layoutFile = $this->getLayoutFile( $this->_pageLayout ) ) )
		{
			//	Process content layout if required
			if ( $this->_contentLayout && false !== ( $_contentLayoutFile = $this->getLayoutFile( $this->_contentLayout ) ) )
				$_output = $this->renderPartial( $_contentLayoutFile, $viewData, true );

			$_output = $this->renderFile( $_layoutFile, array( 'content' => $_output ), true );
			$_output = $this->processOutput( $_output );
		}

		if ( $returnString )
			return $_output;
		
		echo $_output;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	* Executes any commands
	* Maps to {@link CPSController::commandMap} and calls the appropriate method.
	*
	* @returns mixed
	*/
	protected function processCommand( $arData = array(), $sIndexName = self::COMMAND_FIELD_NAME )
	{
		//	Our return variable
		$_oResults = null;

		//	Get command's method...
		$_sCmd = PS::o( $_REQUEST, $sIndexName );

		//	Do we have a command mapping?
		if ( null !== ( $_arCmd = PS::o( $this->m_arCommandMap, $_sCmd ) ) )
		{
			//	Get any miscellaneous data into the appropriate array
			if ( count( $arData ) )
			{
				if ( $this->getIsPostRequest() )
					$_POST = array_merge( $_POST, $arData );
				else
					$_GET = array_merge( $_GET, $arData );
			}

			$_oResults = call_user_func( $_arCmd[1] );
		}

		//	Return the results
		return $_oResults;
	}

	/**
	* Saves the data in the model
	*
	* @param CModel $oModel The model to save
	* @param array $arData The array of data to merge with the model
	* @param string $sRedirectAction Where to redirect after a successful save
	* @param boolean $bAttributesSet If true, attributes will not be set from $arData
	* @param string $sModelName Optional model name
	* @param string $sSuccessMessage Flash message to set if successful
	* @param boolean $bNoCommit If true, transaction will not be committed
	* @returns boolean
	*/
	protected function saveModel( &$oModel, $arData = array(), $sRedirectAction = 'show', $bAttributesSet = false, $sModelName = null, $sSuccessMessage = null, $bNoCommit = false, $bSafeOnly = false )
	{
		$_sMessage = PS::nvl( $sSuccessMessage, 'Your changes have been saved.' );
		$_sModelName = PS::nvl( $sModelName, PS::nvl( $oModel->modelName, $this->m_sModelName ) );

		if ( isset( $arData, $arData[ $_sModelName ] ) )
		{
			if ( ! $bAttributesSet ) $oModel->setAttributes( $arData[ $_sModelName ], $bSafeOnly );

			if ( $oModel->save() )
			{
				if ( ! $bNoCommit && $oModel instanceof CPSModel && $oModel->hasTransaction() ) $oModel->commitTransaction();

				Yii::app()->user->setFlash( 'success', $_sMessage );

				if ( $sRedirectAction )
					$this->redirect( array( $sRedirectAction, 'id' => $oModel->id ) );

				return true;
			}
		}

		return false;
	}

	/***
	* Just like saveModel, but doesn't commit, and never redirects.
	*
	* @param CPSModel $oModel
	* @param array $arData
	* @param boolean $bAttributesSet
	* @param string $sSuccessMessage
	* @return boolean
	* @see saveModel
	*/
	protected function saveTransactionModel( &$oModel, $arData = array(), $bAttributesSet = false, $sSuccessMessage = null )
	{
		return $this->saveModel( $oModel, $arData, false, $bAttributesSet, null, $sSuccessMessage, true );
	}

	/**
	* Loads a page of models
	* @param boolean Whether or not to apply a sort. Defaults to false
	*
	* @returns array Element 0 is the results of the find. Element 1 is the pagination object
	*/
	protected function loadPaged( $bSort = false, $oCriteria = null )
	{
		$_oSort = $_oCrit = $_oPage = null;

		//	Make criteria
		$_oCrit = PS::nvl( $oCriteria, new CDbCriteria() );
		$_oPage = new CPagination( $this->loadCount( $_oCrit ) );
		$_oPage->pageSize = PS::o( $_REQUEST, 'perPage', self::PAGE_SIZE );
		if ( isset( $_REQUEST, $_REQUEST['page'] ) ) $_oPage->setCurrentPage( intval( $_REQUEST['page'] ) - 1 );
		$_oPage->applyLimit( $_oCrit );

		//	Sort...
		if ( $bSort )
		{
			$_oSort = new CPSSort( $this->m_sModelName );
			$_oSort->applyOrder( $_oCrit );
		}

		//	Return an array of what we've build...
		return array( $this->loadAll( $_oCrit ), $_oCrit, $_oPage, $_oSort );
	}

	/**
	* Loads a model(s) based on criteria and scopes.
	*
	* @param string The method to append
	* @param CDbCriteria The criteria for the lookup
	* @param array Scopes to apply to this request
	* @param array Options for the data load
	* @returns CActiveRecord|array
	*/
	protected function genericModelLoad( $sMethod, &$oCrit = null, $arScope = array(), $arOptions = array() )
	{
		$_sMethod = $this->getModelLoadString( $arScope, $arOptions ) . $sMethod;
		return eval( "return (" . $_sMethod . ");" );
	}

	/**
	* This method reads the data from the database and returns the row.
	* Must override in subclasses.
	* @var integer $iId The primary key to look up
	* @returns CActiveRecord
	*/
	protected function load( $iId = null )
	{
		return $this->genericModelLoad( 'findByPk(' . $iId . ')' );
	}

	/**
	* Loads all data using supplied criteria
	* @param CDbCriteria $oCrit
	* @return array Array of CActiveRecord
	* @todo When using PHP v5.3, {@link eval} will no longer be needed
	*/
	protected function loadAll( &$oCrit = null )
	{
		return $this->genericModelLoad( 'findAll(' . ( null !== $oCrit ? '$oCrit' : '' ) . ')', $oCrit );
	}

	/**
	* Returns the count of rows that match the supplied criteria
	*
	* @param CDbCriteria $oCrit
	* @return integer The number of rows
	*/
	protected function loadCount( &$oCrit = null )
	{
		$_sCrit = ( $oCrit ) ? '$oCrit' : null;
		return $this->genericModelLoad( 'count(' . $_sCrit. ')', $oCrit );
	}

	/**
	* Builds a string suitable for {@link eval}. The verb is intentionally not appeneded.
	*
	* @param array $arScope
	* @returns string
	* @todo Will be deprecated after upgrade to PHP v5.3
	*/
	protected function getModelLoadString( $arScope = array(), $arOptions = array() )
	{
		$_sScopes = ( count( $arScope ) ) ? implode( '->', $arScope ) . '->' : null;
		return $this->m_sModelName . '::model()->' . $_sScopes;
	}

	/**
	* Pushes an action onto the action queue
	*
	* @param CAction $oAction
	*/
	protected function pushAction( $oAction )
	{
		array_push( $this->m_arActionQueue, $oAction );
	}

	/**
	* Retrieves the latest pushed action
	* @return CAction
	*/
	protected function popAction()
	{
		return array_pop( $this->m_arActionQueue );
	}

	/**
	* Clears the current search criteria
	* @returns null
	*/
	protected function clearSearchCriteria()
	{
		$this->m_arCurrentSearchCriteria = null;
		Yii::app()->user->clearState( $this->m_sSearchStateId );

		return null;
	}

}