<?php
/**
 * CPSYelpBase class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

//********************************************************************************
//* Include Files
//********************************************************************************

//require_once( dirname( __FILE__ ) . '/../CPSComponent.php' );

/**
 * CPSYelpBase provides an easy way to access the Yelp API
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.4
 */
abstract class CPSYelpBase extends CPSComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const YELP_REVIEW_API = 'review';
	const YELP_PHONE_API = 'phone';
	const YELP_NEIGHBORHOOD_API = 'neighborhood';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The valid Yelp APIs to call
	*
	* @var array
	*/
	protected $m_arApiSubUrls = array(
		self::YELP_REVIEW_API => 'business_review_search',
		self::YELP_PHONE_API => 'phone_search',
		self::YELP_NEIGHBORHOOD_API => 'neighborhood_search',
	);

	/**
	* The Yelp API request mapping. This array holds the mapping for the object names to API parameters
	*
	* @var mixed
	*/
	protected $m_arRequestMap = array(
		//	Business Review API
		self::YELP_REVIEW_API => array(
			'boundingBox' => array(
				'searchTerm' => array( 'name' => 'term', 'required' => false ),
				'maxResults' => array( 'name' => 'num_biz_requested', 'required' => false ),
				'topLeftLatitude' => array( 'name' => 'tl_lat', 'required' => true ),
				'topLeftLongitude' => array( 'name' => 'tl_long', 'required' => true ),
				'bottomRightLatitude' => array( 'name' => 'br_lat', 'required' => true ),
				'bottomRightLongitude' => array( 'name' => 'br_long', 'required' => true ),
				'category' => array( 'name' => 'category', 'required' => false ),
			),

			'point' => array(
				'searchTerm' => array( 'name' => 'term', 'required' => false ),
				'maxResults' => array( 'name' => 'num_biz_requested', 'required' => false ),
				'latitude' => array( 'name' => 'lat', 'required' => true ),
				'longitude' => array( 'name' => 'long', 'required' => true ),
				'radius' => array( 'name' => 'radius', 'required' => false ),
				'category' => array( 'name' => 'category', 'required' => false ),
			),

			'location' => array(
				'searchTerm' => array( 'name' => 'term', 'required' => false ),
				'maxResults' => array( 'name' => 'num_biz_requested', 'required' => false ),
				'location' => array( 'name' => 'location', 'required' => true ),
				'countryCode' => array( 'name' => 'cc', 'required' => false ),
				'radius' => array( 'name' => 'radius', 'required' => false ),
				'category' => array( 'name' => 'category', 'required' => false ),
			),
		),

		//	Phone Search API
		self::YELP_PHONE_API => array(
			'number' => array(
				'phoneNumber' => array( 'name' => 'phone', 'required' => true ),
				'countryCode' => array( 'name' => 'cc', 'required' => false ),
				'category' => array( 'name' => 'category', 'required' => false ),
			),
		),

		//	Neighborhood API
		self::YELP_NEIGHBORHOOD_API => array(
			'point' => array(
				'latitude' => array( 'name' => 'lat', 'required' => true ),
				'longitude' => array( 'name' => 'long', 'required' => true ),
				'category' => array( 'name' => 'category', 'required' => false ),
			),

			'location' => array(
				'location' => array( 'name' => 'location', 'required' => true ),
				'countryCode' => array( 'name' => 'cc', 'required' => false ),
				'category' => array( 'name' => 'category', 'required' => false ),
			),
		),
	);

	/**
	* The Yelp API Key
	*
	* @var string
	*/
	protected $m_sApiKey = null;

	/**
	* The user agent string to use
	*
	* @var string
	*/
	protected $m_sUserAgent = 'Pogostick Components for Yii; (+http://www.pogostick.com/yii)';

	/***
	* The way returned data is formatted
	*
	* @var string
	*/
	protected $m_sFormat = 'json';

	/**
	* The Yelp API base url to use
	*/
	protected $m_sApiBaseUrl = 'http://api.yelp.com/';

	/**
	* The Yelp API to call
	*/
	protected $m_sApiToUse = null;

	/**
	* The data to pass to the Yelp API for the request
	*
	* @var array
	*/
	protected $m_arRequestData = array();

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	public function getApiKey() { return( $this->m_sApiKey ); }
	public function setApiKey( $sValue ) { $this->m_sApiKey = $sValue; }
	public function getApiSubUrls() { return( $this->m_arApiSubUrls ); }
	public function setApiSubUrls( $arValue ) { $this->m_arApiSubUrls = $arValue; }
	public function getUserAgent() { return( $this->m_sUserAgent ); }
	public function setUserAgent( $sValue ) { $this->m_sUserAgent = $sValue; }
	public function getFormat() { return( $this->m_sFormat ); }
	public function setFormat( $sValue ) { $this->m_sFormat = $sValue; }
	public function getApiBaseUrl() { return( $this->m_sApiBaseUrl ); }
	public function setApiBaseUrl( $sValue ) { $this->m_sApiBaseUrl = $sValue; }
	public function getApiToUse() { return( $this->m_sApiToUse ); }
	public function setApiToUse( $sValue ) { $this->m_sApiToUse = $sValue; }
	public function getRequestData() { return( $this->m_arRequestData ); }
	public function setRequestData( $arValue ) { $this->m_arRequestData = $arValue; }
	public function getRequestMap() { return( $this->m_arRequestMap ); }
	public function setRequestMap( $arValue ) { $this->m_arRequestMap = $arValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	public function __construct()
	{
		//	Valid options for this component...
		$this->m_arValidOptions = array(
			'apiKey' => array( 'type' => 'string' ),
			'userAgent' => array( 'type' => 'string' ),
			'format' => array( 'type' => 'string', 'valid' => array( 'array', 'xml', 'json' ) ),
			'baseUrl' => array( 'type' => 'string' ),
			'apiToUse' => array( 'type' => 'string', 'valid' => array( 'review', 'phone', 'neighborhood' ), 'required' => true ),
			'requestData' => array( 'type' => 'array' ),
			'requestMap' => array( 'type' => 'array' ),
			'apiSubUrls' => array( 'type' => 'array' ),
		);

		parent::__construct();
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	protected function makeRequest( $sSubType, $arRequestData = null )
	{
		//	Default...
		$_arRequestData = $this->m_arRequestData;

		//	Check data...
		if ( null != $arRequestData )
			$_arRequestData = array_merge( $_arRequestData, $arRequestData );

		//	Check subtype...
		if ( ! array_key_exists( $sSubType, $this->m_arRequestMap[ $this->m_sApiToUse ] ) )
		{
			throw new CException(
				Yii::t(
					__CLASS__,
					'Invalid API SubType specified for {apiToUse}. Valid subtypes are {subTypes}',
					array(
						'{apiToUse}' => $this->m_sApiToUse,
						'{subTypes}' => implode( ', ', $this->m_arRequestMap[ $this->m_sApiToUse ] )
					)
				)
			);
		}

		//	First build the url...
		$_sUrl = $this->m_sApiBaseUrl . ( substr( $this->m_sApiBaseUrl, strlen( $this->m_sApiBaseUrl ) - 1, 1 ) != '/' ? '/' : '' ) . $this->m_arApiSubUrls[ $this->m_sApiToUse ];

		//	Add the API key...
		$_sQuery = 'ywsid=' . $this->m_sApiKey;

		//	Add the request data to the Url...
		foreach ( $this->m_arRequestMap[ $this->m_sApiToUse ][ $sSubType ] as $_sKey => $_arInfo )
		{
			if ( isset( $_arInfo[ 'required' ] ) && $_arInfo[ 'required' ] && ! array_key_exists( $_sKey, $_arRequestData ) )
			{
				throw new CException(
					Yii::t(
						__CLASS__,
						'Required parameter {param} was not included in requestData',
						array(
							'{param}' => $_sKey,
						)
					)
				);
			}

			if ( isset( $_arRequestData[ $_sKey ] ) )
				$_sQuery .= '&' . $_arInfo[ 'name' ] . '=' . urlencode( $_arRequestData[ $_sKey ] );
		}

		//	Ok, we've build our request, now let's get the results...
		$_sResults = CAppHelpers::getRequest( $_sUrl, $_sQuery, $this->m_sUserAgent );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->m_sFormat )
		{
			case 'xml':
				$_sResults = CAppHelpers::arrayToXml( json_decode( $_sResults, true ), 'Results' );
				break;

			case 'array':
				$_sResults = json_decode( $_sResults, true );
				break;
		}

		//	Return results...
		return( $_sResults );
	}
}