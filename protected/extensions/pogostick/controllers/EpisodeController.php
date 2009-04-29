<?php

class EpisodeController extends CController
{
	/**
	* Constants
	*/
	const PAGE_SIZE = 15;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction = 'xmldata';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $m_oEpisode;

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
			array( 'allow',  // allow all users to perform 'list' and 'episode' actions
				'actions' => array( 'xmldata' ),
				'users' => array( '*' ),
			),
			array( 'deny',  // deny all users
				'users' => array( '*' ),
			),
		);
	}

	/**
	* Returns Xml data suitable for jqGrid
	*
	*/
	public function actionXmlData()
	{
		$_sId = $_GET[ 'id' ];

		if ( empty( $_sId ) )
			$_sId = 0;

		$_dbc = new CDbCriteria();
		$_dbc->select = 'episode_uid, name_text, episode_text, season_nbr, episode_nbr, url_text, show_uid';
		$_dbc->condition = 'show_uid = ' . $_sId;

		return( CAppHelpers::asjqGridXmlData( Episode::model(), $_dbc ) );
	}
}
