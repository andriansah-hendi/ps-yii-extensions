<?php

class UserSaveController extends CController
{
	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction = 'togglestar';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $m_oUserSave = null;

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
			array( 'allow',
				'actions' => array( 'togglestar' ),
				'users' => array( '*' ),
//				'ips' => array( '127.0.0.*', '192.168.66.*' ),
			),
			array( 'deny',  // deny all users
				'users' => array( '*' ),
			),
		);
	}

	/**
	* Toggles a popup star...
	*
	*/
	public function actionToggleStar()
	{
		$_sReturn = 'on';
		$_iId = intval( $_GET[ 'id' ] );

		if ( Yii::app()->user->isGuest )
		{
			echo "mustlogin";
			return;
		}

		try
		{
			//	Look up the old rating...
			$_oUS = UserSave::model()->findByPk( array( 'user_uid' => Yii::app()->user->id, 'place_uid' => $_iId ) );

			if ( $_oUS )
			{
				$_sReturn = 'off';
				UserSave::model()->deleteByPk( array( 'user_uid' => Yii::app()->user->id, 'place_uid' => $_iId ) );
			}
			else
			{
				$_oUS = new UserSave();
				$_oUS->user_uid = Yii::app()->user->id;
				$_oUS->place_uid = $_iId;
				$_oUS->save_type_code = 10200;
				$_oUS->save();
			}
		}
		catch ( Exception $_ex )
		{
			Yii::log( "Error deleting star {$_iId}/" . Yii::app()->user->id . "/" . $_ex->getMessage(), 'error' );
		}

		echo $_sReturn;
	}
}