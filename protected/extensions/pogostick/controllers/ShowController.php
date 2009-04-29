<?php

class ShowController extends CController
{
	/**
	* Constants
	*/
	const PAGE_SIZE = 15;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction = 'list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $m_oShow;

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array( 'allow',  // allow all users to perform 'list' and 'show' actions
				'actions' => array( 'xmldata', 'show' ),
				'users' => array( '*' ),
			),
			array( 'allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions' => array( 'create', 'update' ),
				'users' => array( '@' ),
			),
			array( 'allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions' => array( 'admin', 'delete' ),
				'users' => array( '@' ),
			),
			array( 'deny',  // deny all users
				'users' => array( '*' ),
			),
		);
	}

	/**
	 * Shows a particular show.
	 */
	public function actionShow()
	{
		$this->render( 'show', array( 'show' => $this->loadShow() ) );
	}

	/**
	 * Creates a new show.
	 * If creation is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionCreate()
	{
		$show = new Show;
		if ( isset( $_POST[ 'Show' ] ) )
		{
			$show->attributes = $_POST[ 'Show' ];
			if ( $show->save() )
				$this->redirect( array( 'show', 'id' => $show->show_uid ) );
		}
		$this->render( 'tab_create', array( 'show' => $show ) );
	}

	/**
	 * Updates a particular show.
	 * If update is successful, the browser will be redirected to the 'show' page.
	 */
	public function actionUpdate()
	{
		$show = $this->loadShow();
		if ( isset( $_POST[ 'Show' ] ) )
		{
			$show->attributes=$_POST['Show'];
			if($show->save())
				$this->redirect(array('show','id'=>$show->inv_uid));
		}
		$this->render('update',array('show'=>$show));
	}

	/**
	 * Deletes a particular show.
	 * If deletion is successful, the browser will be redirected to the 'list' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadShow()->delete();
			$this->redirect(array('list'));
		}
		else
			throw new CHttpException(500,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all shows.
	 */
	public function actionList()
	{
		$criteria=new CDbCriteria;

		$pages = new CPagination( Show::model()->count( $criteria ) );
		$pages->pageSize = self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$showList=Show::model()->findAll($criteria);

		$this->render('list',array(
			'showList'=>$showList,
			'pages'=>$pages,
		));
	}

	/**
	 * Manages all shows.
	 */
	public function actionAdmin()
	{
		$this->processAdminCommand();

		$criteria=new CDbCriteria;

		$pages=new CPagination(Show::model()->count($criteria));
		$pages->pageSize=self::PAGE_SIZE;
		$pages->applyLimit($criteria);

		$sort=new CSort('Show');
		$sort->applyOrder($criteria);

		$showList=Show::model()->findAll($criteria);

		$this->render('admin',array(
			'showList'=>$showList,
			'pages'=>$pages,
			'sort'=>$sort,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadShow($id=null)
	{
		if ( $this->m_oShow === null )
		{
			if($id!==null || isset($_GET['id']))
				$this->m_oShow = Show::model()->findbyPk( $id !== null ? $id : $_GET[ 'id' ], 'user_uid = :user_uid', array( ':user_uid' => Yii::app()->user->id ) );

			if ( $this->m_oShow === null)
				throw new CHttpException(500,'The requested show does not exist.');
		}
		return $this->m_oShow;
	}

	/**
	 * Executes any command triggered on the admin page.
	 */
	protected function processAdminCommand()
	{
		if(isset($_POST['command'], $_POST['id']) && $_POST['command']==='delete')
		{
			$this->loadShow($_POST['id'])->delete();
			// reload the current page to avoid duplicated delete actions
			$this->refresh();
		}
	}

	/**
	* Returns Xml data suitable for jqGrid
	*
	*/
	public function actionXmlData()
	{
		return( CAppHelpers::asjqGridXmlData( Show::model(), 'show_uid, name_text, network_name_text, host_name_text, url_text' ) );
	}
}
