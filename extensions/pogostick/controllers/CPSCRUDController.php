<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSCRUDController provides standard filtered access to CRUD resources
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
abstract class CPSCRUDController extends CPSController
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The name of the Login Form class. Defaults to 'LoginForm'
	* 	
	* @var string
	*/
	protected $m_sLoginFormClass = null;
	public function getLoginFormClass() { return PS::nvl( $this->m_sLoginFormClass, 'LoginForm' ); }
	public function setLoginFormClass( $sValue ) { $this->m_sLoginFormClass = $sValue; }
	
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
		
		$this->defaultAction = 'admin';

		//	Add command mappings...
		$this->addCommandToMap( 'delete' );
		$this->addCommandToMap( 'undelete' );

		//	Set our access rules..
		$this->setUserActionList( self::ACCESS_TO_ALL, array( 'index', 'error' ) );
		$this->setUserActionList( self::ACCESS_TO_GUEST, array( 'login', 'show', 'list', 'contact' ) );
		$this->setUserActionList( self::ACCESS_TO_AUTH, array( 'logout', 'admin', 'create', 'delete', 'update' ) );
	}

	/**
	* The filters for this controller
	* 
	* @returns array Action filters
	*/
	public function filters()
	{
		if ( isset( $_SERVER['argv'] ) ) return array();
		
		//	Perform access control for CRUD operations
		return array(
			'accessControl',
		);
	}

	/**
	* The base access rules for our CRUD controller
	* 
	* @returns array Access control rules
	*/
	public function accessRules()
	{
		static $_arRules;
		static $_bInit;
		
		if ( isset( $_SERVER['argv'] ) ) return array();
		
		//	Build access rule array...
		if ( ! isset( $_bInit ) )
		{
			$_arRules = array();
			
			for ( $_i = 0; $_i <= self::ACCESS_TO_NONE; $_i++ )
			{                                                                                                                   
				$_sVerb = $_sValid = null;
				
				//	Get the user type
				switch ( $_i )
				{
					case self::ACCESS_TO_ALL:
					case self::ACCESS_TO_ANY:
					case self::ACCESS_TO_ANON:
						$_sVerb = 'allow';
						$_sValid = '*'; 	
						break;
						
					case self::ACCESS_TO_GUEST:
						$_sVerb = 'allow';
						$_sValid = '?';
						break;

					case self::ACCESS_TO_AUTH: 	
						$_sVerb = 'allow'; 	
						$_sValid = '@';
						break;

					case self::ACCESS_TO_ADMIN:	
						$_sVerb = 'allow';
						$_sValid = 'admin';
						break;

					case self::ACCESS_TO_NONE: 	
						$_sVerb = 'deny';
						$_sValid = '*';
						break;
				}
				
				//	Add to rules array
				if ( $_sVerb && $_sValid )
				{
					$_arTemp = array( 
						$_sVerb, 
						'actions' => PS::o( $this->m_arUserActionList, $_i ),
						'users' => array( $_sValid )
					);
					
					if ( $_arTemp['actions'] == null ) unset( $_arTemp['actions'] );
					
					$_arRules[] = $_arTemp;
				}
			}
			
			$_bInit = true;
		}

		//	Return the rules...
		return $_arRules;
	}
	
	//********************************************************************************
	//* Default actions
	//********************************************************************************

	/**
	* Shows a model
	*/
	public function actionShow( $arExtraParams = array() )
	{ 
		$_oModel = $this->loadModel();
		$this->genericAction( 'show', $_oModel, $arExtraParams ); 
	}

	/**
	* Creates a new model.
	* If creation is successful, the browser will be redirected to the 'show' page.
	* 
	* @param array If specified, also passed to the view. 
	*/
	public function actionCreate( $arExtraParams = array() )
	{
		$_oModel = new $this->m_sModelName;
		if ( Yii::app()->request->isPostRequest ) $this->saveModel( $_oModel, $_POST, 'update' );
		$this->genericAction( 'create', $_oModel, $arExtraParams );
	}
	
	/**
	* Update the model
	* 
	*/
	public function actionUpdate( $arExtraParams = array() )
	{
		$_oModel = $this->loadModel();
		if ( Yii::app()->request->isPostRequest ) $this->saveModel( $_oModel, $_POST, 'update' );
		$this->genericAction( 'update', $_oModel, $arExtraParams );
	}

	/**
	* Deletes a particular model. 
	* Only allowed via POST
	* 
	* @throws CHttpException
	*/
	public function actionDelete( $sRedirectAction = 'admin' )
	{
		if ( Yii::app()->request->isPostRequest )
		{
			if ( $this->loadModel() )
			{
				$this->loadModel()->delete();
				$this->redirect( array( $sRedirectAction ) );
			}
		}
		else
		{
			if ( isset( $_GET['id'] ) )
			{
				$this->loadModel( $_GET['id'] )->delete();
				$this->redirect( array( $sRedirectAction ) );
			}
		}
		
		throw new CHttpException( 404, 'Invalid request. Please do not repeat this request again.' );
	}

	/**
	* Manages all models.
	*/
	public function actionAdmin( $arExtraParams = array(), $oCriteria = null )
	{
		$_oResult = $this->processCommand();
		if ( $this->m_sModelName ) @list($_arModels, $_oCrit, $_oPage, $_oSort ) = $this->loadPaged( true, $oCriteria );
		$this->render( 'admin', array_merge( $arExtraParams, array( 'models' => $_arModels, 'pages' => $_oPage, 'sort' => $_oSort, 'result' => $_oResult ) ) );
	}

	//********************************************************************************
	//* Command Methods
	//********************************************************************************
	
	/**
	* Delete a model
	* 
	*/
	protected function commandDelete()
	{
		$this->loadModel( $_POST[ 'id' ] )->delete();
		$this->refresh();
	}
	
	/**
	* Undelete a model
	* 
	*/
	protected function commandUndelete()
	{
		$this->loadModel( $_POST[ 'id' ] )->delete( true );
		$this->refresh();
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************
	
	/**
	* Executes any command triggered on the admin page. 
	* Maps to {@link CPSController::adminCommandMap} and calls the appropriate method.
	* 
	* @returns mixed
	*/
	protected function processCommand( $arData = array(), $bPOSTSource = true, $sIndexName = 'command' )
	{
		//	Our return variable
		$_oResults = null;
		
		//	Get command's method...
		$_sCmd = strtolower( PS::o( $arData, $sIndexName ) );
		
		//	Do we have a command mapping?
		if ( in_array( $_sCmd, array_keys( $this->m_arCommandMap ) ) )
		{
			//	Get the method name to call...
			$_sMethod = PS::o( $this->m_arCommandMap, $_sCmd );
			
			//	No method set? Look for methods named command<Command> to process request
			if ( null === $_sMethod && method_exists( $this, 'command' . ucwords( $_sCmd ) ) )
				$_sMethod = 'command' . ucwords( $_sCmd );

			//	Do we have a winner?
			if ( null !== $_sMethod )
			{
				//	Get any miscellaneous data into the appropriate array
				if ( count( $arData ) ) 
				{
					if ( $bPOSTSource ) 
						$_POST = array_merge( $_POST, $arData );
					else
						$_GET = array_merge( $_GET, $arData );
				}

				$_oResults = $this->{$_sMethod}();
			}
		}

		//	Return the results
		return $_oResults;
	}

	/**
	* Index page
	* 
	*/
	public function actionIndex()
	{
		$this->render( 'index' );
	}
	
	/**
	* Default login
	* 
	*/
	public function actionLogin()
	{
		$_sClass = PS::nvl( $this->m_sLoginFormClass, 'LoginForm' );
		$_oLogin = new $_sClass();
		
		if ( isset( $_POST[ $_sClass ] ) )
		{
			$_oLogin->attributes = $_POST[ $_sClass ];

			//	Validate user input and redirect to previous page if valid
			if ( $_oLogin->validate() ) $this->redirect( Yii::app()->user->returnUrl );
		}
		
		//	Display the login form
		$this->render( 'login', array( 'form' => $_oLogin ) );
	}
	
	/**
	* Logout the user
	* 
	*/
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->actionLogin();
	}

}