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
	protected $_loginFormClass = null;
	public function getLoginFormClass() { return PS::nvl( $this->_loginFormClass, 'LoginForm' ); }
	public function setLoginFormClass( $value ) { $this->_loginFormClass = $value; }

	/***
	 * Mimic Gii's breadcrumbs property
	 * @var array
	 */
	protected $_breadcrumbs = array();
	public function getBreadcrumbs() { return $this->_breadcrumbs; }
	public function setBreadcrumbs( $value ) { $this->_breadcrumbs = $value; }

	/***
	 * Mimic Gii's menu property
	 * @var array
	 */
	protected $_menu = array();
	public function getMenu() { return $this->_menu; }
	public function setMenu( $value ) { $this->_menu = $value; }

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
		$this->addCommandToMap( 'delete', array( $this, 'commandDelete' ) );
		$this->addCommandToMap( 'undelete', array( $this, 'commandUndelete' ) );

		//	Set our access rules..
		$this->addUserAction( self::ACCESS_TO_ALL, 'error' );
		$this->addUserActions( self::ACCESS_TO_GUEST, array( 'login', 'show', 'list', 'contact' ) );
		$this->addUserActions( self::ACCESS_TO_AUTH, array( 'login', 'logout', 'admin', 'create', 'delete', 'update', 'index', 'view' ) );
	}

	/**
	* The filters for this controller
	*
	* @returns array Action filters
	*/
	public function filters()
	{
		if ( $_SERVER['HTTP_HOST'] == 'localhost' ) return array();

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
		static $_ruleList;
		static $_isInitialized;

		//	Console apps can bypass this...
		if ( PS::_a() instanceof CConsoleApplication ) return array();

		//	Build access rule array...
		if ( ! isset( $_isInitialized ) )
		{
			$_ruleList = array();

			for ( $_i = 0; $_i <= self::ACCESS_TO_NONE; $_i++ )
			{
				$_theVerb = $_validMatch = null;

				//	Get the user type
				switch ( $_i )
				{
					case self::ACCESS_TO_ALL:
					case self::ACCESS_TO_ANY:
					case self::ACCESS_TO_ANON:
						$_theVerb = 'allow';
						$_validMatch = '*';
						break;

					case self::ACCESS_TO_GUEST:
						$_theVerb = 'allow';
						$_validMatch = '?';
						break;

					case self::ACCESS_TO_AUTH:
						$_theVerb = 'allow';
						$_validMatch = '@';
						break;

					case self::ACCESS_TO_ADMIN:
						$_theVerb = 'allow';
						$_validMatch = 'admin';
						break;

					case self::ACCESS_TO_NONE:
						$_theVerb = 'deny';
						$_validMatch = '*';
						break;
				}

				//	Add to rules array
				if ( $_theVerb && $_validMatch )
				{
					$_tempList = array(
						$_theVerb,
						'actions' => PS::o( $this->m_arUserActionList, $_i ),
						'users' => array( $_validMatch )
					);

					if ( $_tempList['actions'] == null ) unset( $_tempList['actions'] );

					$_ruleList[] = $_tempList;
				}
			}

			$_isInitialized = true;
		}

		//	Return the rules...
		return $_ruleList;
	}

	//********************************************************************************
	//* Default actions
	//********************************************************************************

	/**
	* Default login
	*
	*/
	public function actionLogin()
	{
		if ( ! Yii::app()->user->isGuest )
			return $this->redirect( Yii::app()->user->returnUrl );

		$_sClass = $this->getLoginFormClass();
		$_oLogin = new $_sClass();

		if ( isset( $_POST[ $_sClass ] ) )
		{
			$_oLogin->attributes = $_POST[ $_sClass ];

			//	Validate user input and redirect to previous page if valid
			if ( $_oLogin->validate() )
				return $this->redirect( Yii::app()->user->returnUrl );
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
		$this->redirect( Yii::app()->user->loginUrl );
	}

	/**
	* Creates a new model.
	* If creation is successful, the browser will be redirected to the 'show' page.
	*
	* @param array If specified, also passed to the view.
	*/
	public function actionCreate( $options = array() )
	{
		$_model = new $this->m_sModelName;
		if ( Yii::app()->request->isPostRequest ) $this->saveModel( $_model, $_POST, 'update' );
		$this->genericAction( 'create', $_model, $options );
	}

	/**
	* Update the model
	*
	*/
	public function actionUpdate( $options = array() )
	{
		$_model = $this->loadModel();
		if ( Yii::app()->request->isPostRequest )
		{
			$this->saveModel( $_model, $_POST, 'update' );
		}
		$this->genericAction( 'update', $_model, $options );
	}

	/**
	* View the model
	*
	*/
	public function actionView( $options = array() )
	{
		$_model = $this->loadModel();
		$this->genericAction( 'view', $_model, $options );
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
	public function actionAdmin( $options = array(), $oCriteria = null )
	{
		if ( $this->m_sModelName ) @list( $_arModels, $_oCrit, $_oPage, $_oSort ) = $this->loadPaged( true, $oCriteria );
		$this->render( 'admin', array_merge( $options, array( 'models' => $_arModels, 'pages' => $_oPage, 'sort' => $_oSort ) ) );
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

}
