<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * CommentController class file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */
class CommentController extends CPSCRUDController
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	public function init()
	{
		//	Phone home...
		parent::init();
		
		//	Set model name...
		$this->setModelName( 'Comment' );
		$this->addUserAction( self::ACCESS_TO_AUTH, 'list' );
		$this->addUserAction( self::ACCESS_TO_AUTH, 'approve' );
		
		//	Some form defaults
		PS::$afterRequiredLabel = null;
		PS::$errorCss = 'ui-state-error';
	}
 
	//********************************************************************************
	//* Actions
	//********************************************************************************
	
	/**
	 * put your comment there...
	 * 
	 */
	public function actionList( $arExtraParams = array(), $oCriteria = null )
	{
		@list( $_arModels, $_oCrit, $_oPage, $_oSort ) = $this->loadPaged( true, $oCriteria );
		$this->render( '_list', array_merge( $arExtraParams, array( 'comments' => $_arModels, 'pages' => $_oPage, 'sort' => $_oSort ) ) );
	}
	
	/**
	 * Approves a particular comment.
	 * If approval is successful, the browser will be redirected to the post page.
	 */
	public function actionApprove()
	{
		if ( $this->isPostRequest )
		{
			$_oComment = $this->loadModel();
			$_oComment->approve();
			$this->redirect( array( 'post/show', 'id' => $_oComment->post_id, '#' => 'c' . $_oComment->id ) );
		}
		else
			throw new CHttpException( 400, 'Invalid request. Please do not repeat this request again.' );
	}

}