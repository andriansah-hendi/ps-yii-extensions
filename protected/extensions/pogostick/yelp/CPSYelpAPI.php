<?php
/**
 * CPSYelpApi class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSYelpApi provides access to the Yelp Business Reviews API
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.4
 */
class CPSYelpApi extends CPSApiComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const YELP_REVIEW_API = 'review';
	const YELP_PHONE_API = 'phone';
	const YELP_NEIGHBORHOOD_API = 'neighborhood';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	function __construct()
	{
		Yii::log( 'construct PSYelpApi object' );

		//	Call daddy...
		parent::__construct();

		//	The valid Yelp APIs to call
		$this->apiSubUrls =
			array(
				self::YELP_REVIEW_API => 'business_review_search',
				self::YELP_PHONE_API => 'phone_search',
				self::YELP_NEIGHBORHOOD_API => 'neighborhood_search',
			);

		//	The Yelp API request mapping. This array holds the mapping for the object names to API parameters
		$this->requestMap = array(

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
	}

	/**
	* Calls the Yelp API and retrieves reviews by bounding rectancle.
	*
	* @param mixed $fTLLat
	* @param mixed $fTLLong
	* @param mixed $fBRLat
	* @param mixed $fBRLong
	* @param mixed $sSearchTerm
	* @param mixed $iMaxResults
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getReviewsByBounds( $fTLLat, $fTLLong, $fBRLat, $fBRLong, $sSearchTerm = null, $iMaxResults = 10, $sCategories = null )
	{
		$this->apiToUse = self::YELP_REVIEW_API;

		$this->requestData = array(
			'topLeftLatitude' => $fTLLat,
			'topLeftLongitude' => $fTLLong,
			'bottomRightLatitude' => $fBRLat,
			'bottomRightLongitude' => $fBRLong,
			'maxResults' => $iMaxResults,
		);

		if ( $sSearchTerm != null )
			$this->requestData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'boundingBox' ) );
	}

	/**
	* Calls the Yelp API and retrieves reviews by geo-point
	*
	* @param mixed $fLat
	* @param mixed $fLong
	* @param mixed $iRadius
	* @param mixed $sSearchTerm
	* @param mixed $iMaxResults
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getReviewsByPoint( $fLat, $fLong, $iRadius = null, $sSearchTerm = null, $iMaxResults = 10, $sCategories = null )
	{
		$this->apiToUse = self::YELP_REVIEW_API;

		$this->requestData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
			'maxResults' => $iMaxResults,
		);

		if ( $iRadius != null )
			$this->requestData[ 'radius' ] = $iRadius;

		if ( $sSearchTerm != null )
			$this->requestData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'point' ) );
	}

	/**
	* Calls the Yelp API and retrieves reviews by location
	*
	* @param mixed $sLocation
	* @param mixed $iRadius
	* @param mixed $sSearchTerm
	* @param mixed $iMaxResults
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getReviewsByNeighborhood( $sLocation, $iRadius = null, $sSearchTerm = null, $iMaxResults = 10, $sCountryCode = null, $sCategories = null )
	{
		$this->apiToUse = self::YELP_REVIEW_API;

		$this->requestData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
			'maxResults' => $iMaxResults,
			'location' => $sLocation,
		);

		if ( $iRadius != null )
			$this->requestData[ 'radius' ] = $iRadius;

		if ( $sSearchTerm != null )
			$this->requestData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'location' ) );
	}

	/***
	* Calls the Yelp Phone API and returns results
	*
	* @param mixed $sPhone
	* @param mixed $sCountryCode
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getPhoneByNumber( $sPhone, $sCountryCode = null, $sCategories = null )
	{
 		$this->apiToUse = self::YELP_PHONE_API;

		$this->requestData = array(
			'phone' => $sPhone,
		);

		if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'number' ) );
	}

	/**
	* Gets data from the Yelp Neighborhood API by geo-point
	*
	* @param mixed $fLat
	* @param mixed $fLong
	* @param mixed $sCountryCode
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getNeighborhoodByPoint( $fLat, $fLong, $sCountryCode = null, $sCategories = null )
	{
 		$this->apiToUse = self::YELP_NEIGHBORHOOD_API;

		$this->requestData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
		);

    	if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'phone' ) );
	}

	/**
	* Gets data from the Yelp Neighborhood API
	*
	* @param mixed $sLocation
	* @param mixed $sCountryCode
	* @param mixed $sCategories
	* @return mixed
	*/
	public function getNeighborhoodByLocation( $sLocation, $sCountryCode = null, $sCategories = null )
	{
 		$this->apiToUse = self::YELP_NEIGHBORHOOD_API;

		$this->requestData = array(
			'location' => $sLocation,
		);

    	if ( $sCountryCode != null )
			$this->requestData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$this->requestData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'location' ) );
	}

}