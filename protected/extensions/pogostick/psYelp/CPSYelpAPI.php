<?php
/**
 * CPSYelpAPI class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

//********************************************************************************
//* Include Files
//********************************************************************************

/**
 * CPSYelpAPI provides access to the Yelp Business Reviews API
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.4
 */
class CPSYelpAPI extends CPSYelpBase
{
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

		$_arReqData = array(
			'topLeftLatitude' => $fTLLat,
			'topLeftLongitude' => $fTLLong,
			'bottomRightLatitude' => $fBRLat,
			'bottomRightLongitude' => $fBRLong,
			'maxResults' => $iMaxResults,
		);

		if ( $sSearchTerm != null )
			$_arReqData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCategories != null )
			$_arReqData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'boundingBox', $_arReqData ) );
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

		$_arReqData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
			'maxResults' => $iMaxResults,
		);

		if ( $iRadius != null )
			$_arReqData[ 'radius' ] = $iRadius;

		if ( $sSearchTerm != null )
			$_arReqData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCategories != null )
			$_arReqData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'point', $_arReqData ) );
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

		$_arReqData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
			'maxResults' => $iMaxResults,
			'location' => $sLocation,
		);

		if ( $iRadius != null )
			$_arReqData[ 'radius' ] = $iRadius;

		if ( $sSearchTerm != null )
			$_arReqData[ 'searchTerm' ] = $sSearchTerm;

		if ( $sCountryCode != null )
			$_arReqData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$_arReqData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'location', $_arReqData ) );
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

		$_arReqData = array(
			'phone' => $sPhone,
		);

		if ( $sCountryCode != null )
			$_arReqData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$_arReqData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'number', $_arReqData ) );
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

		$_arReqData = array(
			'latitude' => $fLat,
			'longitude' => $fLong,
		);

    	if ( $sCountryCode != null )
			$_arReqData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$_arReqData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'phone', $_arReqData ) );
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

		$_arReqData = array(
			'location' => $sLocation,
		);

    	if ( $sCountryCode != null )
			$_arReqData[ 'countryCode' ] = $sCountryCode;

		if ( $sCategories != null )
			$_arReqData[ 'category' ] = $sCategories;

		return( $this->makeRequest( 'location', $_arReqData ) );
	}

}