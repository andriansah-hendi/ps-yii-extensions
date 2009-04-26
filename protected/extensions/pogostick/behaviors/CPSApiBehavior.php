<?php
/**
 * CPSApiBehavior class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSApiBehavior provides a behavior to classes for making API calls
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package
 * @since 1.0.5
 */
class CPSApiBehavior extends CPSOptionsBehavior
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	* 'GET' Http method
	*/
	const HTTP_GET = 'GET';
	/**
	* 'PUT' Http method
	*/
	const HTTP_POST = 'POST';

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/***
	* Constructor
	*
	*/
	public function __construct()
	{
		//	Log
		Yii::log( 'constructed psApiBehavior object for [' . get_parent_class() . ']' );

		//	Add ours...
		$this->addOptions( self::getBaseOptions() );
    }

	/**
	* Allows for single behaviors
	*
	*/
	private function getBaseOptions()
	{
		return(
			array(
				'altApiKey' => array( 'value' => '', 'type' => 'string' ),
				'apiBaseUrl' => array( 'value' => '', 'type' => 'string' ),
				'apiKey' => array( 'value' => '', 'type' => 'string' ),
				'apiQueryName' => array( 'value' => '', 'type' => 'string' ),
				'apiToUse' => array( 'value' => '', 'type' => 'string' ),
				'apiSubUrls' => array( 'value' => array(), 'type' => 'array' ),
				'format' => array( 'value' => 'array', 'type' => 'string' ),
				'httpMethod' => array( 'value' => self::HTTP_GET, 'type' => 'string' ),
				'requestData' => array( 'value' => array(), 'type' => 'array' ),
				'requestMap' => array( 'value' => array(), 'type' => 'array' ),
				'userAgent' => array( 'value' => 'Pogostick Components for Yii; (+http://www.pogostick.com/yii)', 'type' => 'string' ),
			)
		);
	}

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Declares events and the corresponding event handler methods.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return(
			array_merge(
				parent::events(),
				array(
					'onBeforeApiCall' => 'beforeApiCall',
					'onAfterApiCall' => 'afterApiCall',
					'onRequestComplete' => 'requestComplete',
				)
			)
		);
	}

	/**
	* beforeApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function beforeApiCall( $oEvent )
	{
	}

	/**
	* afterApiCall event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function afterApiCall( $oEvent )
	{
	}

	/**
	* requestComplete event
	*
	* @param CPSApiEvent $oEvent
	*/
	public function requestComplete( $oEvent )
	{
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	 /**
	 * Make an HTTP request
	 *
	 * @param string $sUrl The URL to call
	 * @param string $sQueryString The query string to attach
	 * @param string $sMethod The HTTP method to use. Can be 'GET' or 'SET'
	 * @param integer $iTimeOut The number of seconds to wait for a response. Defaults to 60 seconds
	 * @param function|array $oHeaderCallback The callback function to call after the header has been read. Accepts function reference or array( object, method )
	 * @param function|array $oReadCallback The callback function to call after the body has been read. Accepts function reference or array( object, method )
	 * @return mixed The data returned from the HTTP request or null for no data
	 */
	protected function makeHttpRequest( $sUrl, $sQueryString = null, $sMethod = 'GET', $sUserAgent = null, $iTimeOut = 60, $oHeaderCallback = null, $oReaderCallback = null )
	{
		//	Our user-agent string
		$_sAgent = ( null != $sUserAgent ) ? $sUserAgent : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506; InfoPath.3)';

		//	Our return results
		$_sResult = null;

		// Use cURL
		if ( function_exists( 'curl_init' ) )
		{
			$_oCurl = curl_init();

			curl_setopt( $_oCurl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $_oCurl, CURLOPT_FAILONERROR, true );
			curl_setopt( $_oCurl, CURLOPT_USERAGENT, $_sAgent );
			curl_setopt( $_oCurl, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $_oCurl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $_oCurl, CURLOPT_URL, $sUrl . ( 'GET' == $sMethod  ? ( ! empty( $sQueryString ) ? '?' . $sQueryString : '' ) : '' ) );

			if ( null != $oHeaderCallback )
				cur_setopt( $_oCurl, CURLOPT_HEADERFUNCTION, $oHeaderCallback );

			if ( null != $oHeaderCallback )
				cur_setopt( $_oCurl, CURLOPT_READFUNCTION, $oReadCallback );

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
			throw new CException( '"libcurl" is required to use this functionality. Please reconfigure your php.ini to include "libcurl".' );

		return( $_sResult );
	}

}