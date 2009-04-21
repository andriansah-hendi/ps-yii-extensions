<?php
/**
 * CPSAPIBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSAPIBehavior provides a behavior to classes for making API calls
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.4
 */
class CPSAPIBehavior extends CPSComponentBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	const HTTP_GET = 'GET';
	const HTTP_POST = 'POST';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	* The remote API key (if applicable)
	*
	* @var string
	*/
	protected $m_sApiKey = null;
	/**
	* The remote API query string name of name=value pair (if applicable). Value will be included in query string automatically with requestData
	*
	* @var string
	*/
	protected $m_sApiQueryName = null;
	/**
	* The secondary remote API key (if applicable) (i.e. Facebook's Secret Key
	*
	* @var string
	*/
	protected $m_sAltApiKey = null;
	/**
	* The API user name ( if applicatble )
	*
	* @var string
	*/
	protected $m_sApiUserName = null;
	/**
	* The API password name ( if applicatble )
	*
	* @var string
	*/
	protected $m_sApiPassword = null;
	/**
	* The user agent string to use
	*
	* @var string
	*/
	protected $m_sUserAgent = 'Pogostick Components for Yii; (+http://www.pogostick.com/yii)';
	/***
	* The way returned data is formatted if appropriate. Valid options should be array, json, or xml.
	*
	* @var string
	*/
	protected $m_sFormat = 'array';
	/**
	* The API base url to use
	*/
	protected $m_sApiBaseUrl = null;
	/**
	* The sub API call to make (if applicable)
	*/
	protected $m_sApiToUse = null;
	/**
	* The map of API calls. This is an optional
	*
	* @var array
	*/
	protected $m_arRequestData = array();
	/**
	* The data to pass to the API for the request
	*
	* @var array
	*/
	protected $m_arRequestData = array();
	/**
	* The HTTP method to use when making calls...
	*
	* @var string Use the CPSBaseAPI constants (i.e. CPSBaseAPI::GET)
	*/
	protected $m_eHttpMethod = self::HTTP_fGET;

	//********************************************************************************
	//* Property Access Methods
	//********************************************************************************

	public function getAltApiKey() { return( $this->m_sAltApiKey ); }
	public function setAltApiKey( $sValue ) { $this->m_sAltApiKey = $sValue; }
	public function getApiBaseUrl() { return( $this->m_sApiBaseUrl ); }
	public function setApiBaseUrl( $sValue ) { $this->m_sApiBaseUrl = $sValue; }
	public function getApiKey() { return( $this->m_sApiKey ); }
	public function setApiKey( $sValue ) { $this->m_sApiKey = $sValue; }
	public function getApiQueryName() { return( $this->m_sApiQueryName ); }
	public function setApiQueryName( $sValue ) { $this->m_sApiQueryName = $sValue; }
	public function getApiUserName() { return( $this->m_sApiUserName ); }
	public function setApiUserName( $sValue ) { $this->m_sApiUserName = $sValue; }
	public function getApiPassword() { return( $this->m_sApiPassword ); }
	public function setApiPassword( $sValue ) { $this->m_sApiPassword = $sValue; }
	public function getApiToUse() { return( $this->m_sApiToUse ); }
	public function setApiToUse( $sValue ) { $this->m_sApiToUse = $sValue; }
	public function getApiSubUrls() { return( $this->m_arApiSubUrls ); }
	public function setApiSubUrls( $arValue ) { $this->m_arApiSubUrls = $arValue; }
	public function getFormat() { return( $this->m_sFormat ); }
	public function setFormat( $sValue ) { $this->m_sFormat = $sValue; }
	public function getHttpMethod() { return( $this->m_eHttpMethod ); }
	public function setHttpMethod( $eValue ) { $this->m_eHttpMethod = $eValue; }
	public function getRequestData() { return( $this->m_arRequestData ); }
	public function setRequestData( $arValue ) { $this->m_arRequestData = $arValue; }
	public function getRequestMap() { return( $this->m_arRequestMap ); }
	public function setRequestMap( $arValue ) { $this->m_arRequestMap = $arValue; }
	public function getUserAgent() { return( $this->m_sUserAgent ); }
	public function setUserAgent( $sValue ) { $this->m_sUserAgent = $sValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Declares events and the corresponding event handler methods.
	 * @see CBehavior::events
	 */
	public function events()
	{
		return(
			array(
				'onBeforeAPICall' => 'beforeAPICall',
				'onAfterAPICall' => 'afterAPICall',
			)
		);
	}

	/**
	* Called before the API call has been made
	*
	* @param string $sUrl The url called
	* @param string $sQuery The query string used
	*/
	public function beforeAPICall( $sUrl, $sQuery )
	{
	}

	/**
	* Called after the API call has been made
	*
	* @param string $sUrl The url called
	* @param string $sQuery The query string used
	* @param string $sResults The raw results of the call
	*/
	public function afterAPICall( $sUrl, $sQuery, $sResults )
	{
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	protected function makeRequest( $sSubType = null, $arRequestData = null )
	{
		//	Default...
		$_arRequestData = $this->requestData;

		//	Check data...
		if ( null != $arRequestData )
			$_arRequestData = array_merge( $_arRequestData, $arRequestData );

		//	Check subtype...
		if ( ! empty( $sSubType ) && is_array( $this->requestMap ) )
		{
			if ( ! array_key_exists( $sSubType, $this->requestMap[ $this->apiToUse ] ) )
			{
				throw new CException(
					Yii::t(
						__CLASS__,
						'Invalid API SubType specified for "{apiToUse}". Valid subtypes are "{subTypes}"',
						array(
							'{apiToUse}' => $this->apiToUse,
							'{subTypes}' => implode( ', ', array_keys( $this->requestMap[ $this->apiToUse ] ) )
						)
					)
				);
			}
		}

		//	First build the url...
		$_sUrl = $this->apiBaseUrl .
			( substr( $this->apiBaseUrl, strlen( $this->apiBaseUrl ) - 1, 1 ) != '/' ? '/' : '' ) .
			( isset( $this->apiSubUrls[ $this->apiToUse ] ) ? $this->apiSubUrls[ $this->apiToUse ] : '' );

		//	Add the API key...
		if ( ! empty( $this->apiQueryName ) )
			$_sQuery = $this->apiQueryName . '=' . $this->apiKey;

		//	Add the request data to the Url...
		if ( is_array( $this->requestMap ) && ! empty( $sSubType ) )
		{
			foreach ( $this->requestMap[ $this->apiToUse ][ $sSubType ] as $_sKey => $_arInfo )
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
		}
		//	Handle non-requestMap call...
		else if ( is_array( $_arRequestData ) )
		{
			foreach ( $this->requestData as $_sKey => $_oValue )
			{
				if ( isset( $_arRequestData[ $_sKey ] ) )
					$_sQuery .= '&' . $_sKey . '=' . urlencode( $_arRequestData[ $_sKey ] );
			}
		}

		//	Honor events...
		$this->beforeAPICall( $_sUrl, $_sQuery );

		//	Ok, we've build our request, now let's get the results...
		$_sResults = self::makeHttpRequest( $_sUrl, $_sQuery, 'GET', $this->userAgent );

		//	Honor events...
		$this->afterAPICall( $_sUrl, $_sQuery, $_sResults );

		//	If user doesn't want JSON output, then reformat
		switch ( $this->format )
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

	 /**
	 * Make an HTTP request
	 *
	 * @param string $sUrl The URL to call
	 * @param string $sQueryString The query string to attach
	 * @param string $sMethod The HTTP method to use. Can be 'GET' or 'SET'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	public static function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sUserAgent = null, $iTimeOut = 60 )
	{
		//	Our user-agent string
		$_sAgent = ( null != $sUserAgent ) ? $sUserAgent : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)';

		//	Our return results
		$_sResult = null;

		// Use CURL if installed...
		if ( function_exists( 'curl_init' ) )
		{
			$_oCurl = curl_init();
			curl_setopt( $_oCurl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $_oCurl, CURLOPT_FAILONERROR, true );
			curl_setopt( $_oCurl, CURLOPT_USERAGENT, $_sAgent );
			curl_setopt( $_oCurl, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $sUrl . ( 'GET' == $sMethod  ? ( $sQueryString != '' ? "?" . $sQueryString : '' ) ) );

			//	If this is a post, we have to put the post data in another field...
			if ( 'POST' == $sMethod )
			{
				curl_setopt( $_oCurl, CURLOPT_URL, $sUrl );
				curl_setopt( $_oCurl, CURLOPT_POST, true );
				curl_setopt( $_oCurl, CURLOPT_POSTFIELDS, $sQueryString );
			}

			$_sResult = curl_exec( $_oCurl );
			curl_close( $_oCurl );
		}
		else
			throw new Exception( '"libcurl" is required to use this functionality. Please reconfigure your php.ini to include "libcurl".' );

		return( $_sResult );
	}
}