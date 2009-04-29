<?php

class PlaceController extends CController
{
	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction = 'xmldata';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	protected $m_oPlace = null;

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
				'actions' => array( 'xmldata', 'jqxmldata', 'popup', 'popuphtml', 'updaterating', 'show' ),
				'users' => array( '*' ),
//				'ips' => array( '127.0.0.*', '192.168.66.*' ),
			),
			array( 'deny',  // deny all users
				'users' => array( '*' ),
			),
		);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadPlace( $id = null )
	{
		if ( $this->m_oPlace === null )
		{
			if ( $id !== null || isset( $_GET[ 'id' ] ) )
				$this->m_oPlace = Place::model()->findbyPk( $id !== null ? $id : $_GET[ 'id' ] );

			if ( $this->m_oPlace === null)
				throw new CHttpException( 500, 'The requested "place" does not exist.' );
		}

		return( $this->m_oPlace );
	}

	/**
	* Returns Xml data suitable for jqGrid
	*/
	public function actionXmlData()
	{
		$_fSWLat = 0;
		$_fSWLng = 0;
		$_fNELat = 0;
		$_fNELng = 0;
		$_sWhere = '';

		$_sId = $_GET['id'];

		if ( empty( $_sId ) )
			$_sId = 0;

		//	Default lookup by show_uid...
		$_sWhere = 'lat_nbr not in (-1,0) and long_nbr not in (-1,0) and place_uid in ( select place_uid from es_place_t where episode_uid in ( select episode_uid from es_episode_t where show_uid = ' . $_sId . ' ) )';

		//	Do we need a bounding lookup?
		if ( isset( $_GET[ 'sw_lat' ] ) )
		{
			$_fSWLat = floatval( $_GET[ 'sw_lat' ] );
			$_fSWLng = floatval( $_GET[ 'sw_lng' ] );
			$_fNELat = floatval( $_GET[ 'ne_lat' ] );
			$_fNELng = floatval( $_GET[ 'ne_lng' ] );

			$_sWhere = "mbrwithin( loc_point, GeomFromText( 'polygon(({$_fNELat} {$_fNELng}),({$_fSWLat} {$_fSWLng}))' ) )";
		}

		//	Get a count of rows for this result set
		$_iRowCount = Place::model()->count( $_sWhere );

		//	Adjust the criteria for the actual query...
		$_arRows = Place::model()->findAll( $_sWhere );

		//	Set appropriate content type
		if ( stristr( $_SERVER[ 'HTTP_ACCEPT' ], "application/xhtml+xml" ) )
			header( "Content-type: application/xhtml+xml;charset=utf-8" );
		else
			header( "Content-type: text/xml;charset=utf-8" );

		//	Now create the Xml...
		$_sOut = CAppHelpers::asXml( $_arRows,
			array(
				'encloseResults' => true,
				'encloseTag' => 'rows',
				'innerElements' => CHtml::tag( 'records', array(), $_iRowCount ),
				'useColumnElementNames' => true,
				'useCDataForStrings' => true,
				'ignoreColumns' => array( 'loc_point' ),
				'addElements' => array(
					array( 'name' => 'show_uid', 'value' => '**rowdata**', 'column' => 'episode->show_uid', 'type' => 'integer' ),
				),
			)
		);

		//	Spit it out...
 		echo "<?xml version='1.0' encoding='utf-8'?>" . $_sOut;
	}

	/**
	* A view to format the popup for the map...
	*
	* @param mixed $sId
	*/
	public function actionPopup( $sId = null )
	{
		if ( null != $sId )
			$this->loadPlace( $sId );

		if ( null == $this->m_oPlace )
			return( null );
	}

	/**
	* A view to format the popup for the map...
	*
	* @param mixed $sId
	*/
	public function actionPopupHtml( $sId = null )
	{
		$_sId = $sId;

		if ( $_sId == null )
		{
			$_sId = $_GET['id'];

			if ( empty( $_sId ) )
				$_sId = $sId;
		}

		if ( ! empty( $_sId ) )
		{
			$this->loadPlace( $_sId );

			if ( null != $this->m_oPlace )
				return( $this->m_oPlace->getPopupHtml( true ) );
		}

		return( null );
	}

	/**
	* Returns Xml data suitable for jqGrid
	*
	*/
	public function actionJqXmlData()
	{
		$_sId = $_GET[ 'episode_uid' ];

		if ( empty( $_sId ) )
			$_sId = 0;

		$_dbc = new CDbCriteria();
		$_dbc->select = 'place_uid, name_text, city_text, state_code, country_code, phone_1_nbr_text';
		$_dbc->condition = 'episode_uid = ' . $_sId;

		return( CPSjqGridWidget::asjqGridXmlData( Place::model(), $_dbc ) );
	}

	/**
	* Update a rating. This is a callback function. Expects "id" = place_uid and "type" = rate_type_code and "value" = {type}rating_nbr
	*
	*/
	public function actionUpdateRating()
	{
		$_bDeleteRating = false;
		$_iId = intval( $_GET[ 'id' ] );
		$_iType = intval( $_GET[ 'type' ] );

		if ( ! empty( $_GET[ 'value' ] ) )
			$_fValue = floatval( $_GET[ 'value' ] ) / 4;
		else
		{
			try
			{
				//	Start a transaction
				$_oTrans = UserRatingAssignment::model()->dbConnection->beginTransaction();

				//	Look up the old rating...
				$_oURA = UserRatingAssignment::model()->findByPk( array( 'user_uid' => Yii::app()->user->id, 'place_uid' => $_iId, 'rate_type_code' => $_iType ) );

				if ( $_oURA )
				{
					$_fOldRating = $_oURA->rate_nbr;

					//	Delete this rating...
					UserRatingAssignment::model()->deleteByPk( array( 'user_uid' => Yii::app()->user->id, 'place_uid' => $_iId, 'rate_type_code' => $_iType ) );
					$_oPlace = Place::model()->findByPk( $_iId );
					if ( $_oPlace )
					{
						//	Add to place ratings and recalc...
						switch ( $_iType )
						{
							case 10100:
								$_oPlace->rate_count_nbr -= 1;
								$_oPlace->rating_nbr -= $_fOldRating;
								break;

							case 10101:
								$_oPlace->atmos_rate_count_nbr -= 1;
								$_oPlace->atmos_rating_nbr -= $_fOldRating;
								break;

							case 10102:
								$_oPlace->price_rate_count_nbr -= 1;
								$_oPlace->price_rating_nbr -= $_fOldRating;
								break;
						}

						$_oPlace->update();
					}
				}

				$_oTrans->commit();
			}
			catch ( Exception $_ex )
			{
				$_oTrans->rollback();
				Yii::log( "Error deleting rating {$_iId}/{$_iType}/{$_fValue}/" . Yii::app()->id . "/" . $_ex->getMessage(), 'error' );
			}
		}

		$_bNewRating = false;

		$_oURA = UserRatingAssignment::model()->findByPk( array( 'user_uid' => Yii::app()->user->id, 'place_uid' => $_iId, 'rate_type_code' => $_iType ) );

		//	Start a transaction
		$_oTrans = UserRatingAssignment::model()->dbConnection->beginTransaction();

		try
		{
			if ( ! $_oURA )
			{
				$_oURA = new UserRatingAssignment();
				$_oURA->user_uid = Yii::app()->user->id;
				$_oURA->place_uid = $_iId;
				$_bNewRating = true;
			}

			$_fOldRating = $_oURA->rate_nbr;

			$_oURA->rate_nbr = $_fValue;
			$_oURA->rate_type_code = $_iType;
			$_oURL->lmod_date = date('c');
			$_oURA->save();

			//	Get place...
			$_oPlace = Place::model()->findByPk( $_iId );

			if ( ! $_oPlace )
				throw new CDbException( 'Place not found' );

			//	Add to place ratings and recalc...
			switch ( $_iType )
			{
				case 10100:
					if ( $_bNewRating )
						$_oPlace->rate_count_nbr++;

					$_oPlace->rating_nbr += ( $_bNewRating ? $_fValue : ( $_fValue - $_fOldRating ) );
					break;

				case 10101:
					if ( $_bNewRating )
						$_oPlace->atmos_rate_count_nbr++;

					$_oPlace->atmos_rating_nbr += ( $_bNewRating ? $_fValue : ( $_fValue - $_fOldRating ) );
					break;

				case 10102:
					if ( $_bNewRating )
						$_oPlace->price_rate_count_nbr++;

					$_oPlace->price_rating_nbr += ( $_bNewRating ? $_fValue : ( $_fValue - $_fOldRating ) );
					break;
			}

			$_oPlace->update();
			$_oTrans->commit();

			echo 'Saved';
		}
		catch ( Exception $_ex )
		{
			Yii::log( "Error saving rating {$_iId}/{$_iType}/{$_fValue}/" . Yii::app()->id . "/" . $_ex->getMessage(), 'error' );
			$_oTrans->rollback();
		}
	}

	/**
	* Shows the Place
	*
	*/
	public function actionShow()
	{
		$_iCountryCode = 2224;
		$_iStateCode = -1;
		$_sCity = '';
		$_sName = '';

		if ( isset( $_GET[ 'cc'] ) && $_GET[ 'cc'] != '*' )
			$_iCountryCode = Code::getUID( $_GET[ 'cc' ], 'COUNTRY' );

		if ( $_iCountryCode == 2224 && isset( $_GET[ 'sc'] ) && $_GET[ 'sc'] != '*' )
			$_iStateCode = Code::getUID( $_GET[ 'sc' ], 'STATE' );
		else
			$_iStateCode = -1;

		if ( isset( $_GET[ 'city'] ) && $_GET[ 'city'] != '*' )
			$_sCity = $_GET[ 'city' ];

		if ( isset( $_GET[ 'pname'] ) )
			$_sName = $_GET[ 'pname' ];

		//	Build or query criteria...
		$_dbc = new CDbCriteria();
		$_dbc->condition = 'country_code = ' . $_iCountryCode;

		if ( $_iStateCode != -1 )
			$_dbc->condition .= ' and state_code = ' . $_iStateCode;

		if ( $_sCity != '' )
			$_dbc->condition .= ' and city_text = ' . Yii::app()->db->quoteValue( $_sCity );

		if ( $_sName != '' )
			$_dbc->condition .= ' and seo_name_text = ' . Yii::app()->db->quoteValue( $_sName );

		$_oRS = Place::model()->find( $_dbc );

		$this->render( 'showPlaceView', array( 'oPlaceRS' => $_oRS ) );
	}
}