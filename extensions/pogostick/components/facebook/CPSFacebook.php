<?php
/*
 * This file is part of the Pogostick Yii Extension library
 *
 * @copyright Copyright &copy; 2009-2010 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSFacebook
 * Provides access to the Facebook Platform.
 *
 * This class is pretty much a complete copy of the Facebook PHP-SDK
 * that has been massaged to work within the framework of Yii.
 *
 * @package 	psYiiExtensions
 * @subpackage	components.facebook
 *
 * @author		Naitik Shah <naitik@facebook.com>
 * @link		http://github.com/facebook/php-sdk
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSFacebook extends CPSApiComponent
{
	//********************************************************************************
	//* Constants
	//********************************************************************************

	/**
	 * Version of this class
	 */
	const	VERSION = '2.1.2';
	const	USER_AGENT = 'pYe-facebook-php-2.1.2';

	/**
	 * Cache constants
	 */
	const	PHOTO_CACHE = '_photoListCache';
	
	//********************************************************************************
	//* Statics
	//********************************************************************************

	/**
	 * @staticvar array Default options for curl.
	 */
	public static $_CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_USERAGENT => self::USER_AGENT,
	);

	/**
	 * @staticvar array List of query parameters that get automatically dropped when rebuilding the current URL.
	 */
	protected static $_DROP_QUERY_PARAMS = array(
		'session',
		'signed_request',
	);

	/**
	 * @staticvar array Maps aliases to Facebook domains.
	 */
	public static $_DOMAIN_MAP = array(
		'api' => 'https://api.facebook.com/',
		'api_read' => 'https://api-read.facebook.com/',
		'graph' => 'https://graph.facebook.com/',
		'www' => 'https://www.facebook.com/',
	);

	/**
	 * @staticvar array A list of read-only API calls
	 */
	protected static $READ_ONLY_CALLS = array(
		'admin.getallocation' => 1,
		'admin.getappproperties' => 1,
		'admin.getbannedusers' => 1,
		'admin.getlivestreamvialink' => 1,
		'admin.getmetrics' => 1,
		'admin.getrestrictioninfo' => 1,
		'application.getpublicinfo' => 1,
		'auth.getapppublickey' => 1,
		'auth.getsession' => 1,
		'auth.getsignedpublicsessiondata' => 1,
		'comments.get' => 1,
		'connect.getunconnectedfriendscount' => 1,
		'dashboard.getactivity' => 1,
		'dashboard.getcount' => 1,
		'dashboard.getglobalnews' => 1,
		'dashboard.getnews' => 1,
		'dashboard.multigetcount' => 1,
		'dashboard.multigetnews' => 1,
		'data.getcookies' => 1,
		'events.get' => 1,
		'events.getmembers' => 1,
		'fbml.getcustomtags' => 1,
		'feed.getappfriendstories' => 1,
		'feed.getregisteredtemplatebundlebyid' => 1,
		'feed.getregisteredtemplatebundles' => 1,
		'fql.multiquery' => 1,
		'fql.query' => 1,
		'friends.arefriends' => 1,
		'friends.get' => 1,
		'friends.getappusers' => 1,
		'friends.getlists' => 1,
		'friends.getmutualfriends' => 1,
		'gifts.get' => 1,
		'groups.get' => 1,
		'groups.getmembers' => 1,
		'intl.gettranslations' => 1,
		'links.get' => 1,
		'notes.get' => 1,
		'notifications.get' => 1,
		'pages.getinfo' => 1,
		'pages.isadmin' => 1,
		'pages.isappadded' => 1,
		'pages.isfan' => 1,
		'permissions.checkavailableapiaccess' => 1,
		'permissions.checkgrantedapiaccess' => 1,
		'photos.get' => 1,
		'photos.getalbums' => 1,
		'photos.gettags' => 1,
		'profile.getinfo' => 1,
		'profile.getinfooptions' => 1,
		'stream.get' => 1,
		'stream.getcomments' => 1,
		'stream.getfilters' => 1,
		'users.getinfo' => 1,
		'users.getloggedinuser' => 1,
		'users.getstandardinfo' => 1,
		'users.hasapppermission' => 1,
		'users.isappuser' => 1,
		'users.isverified' => 1,
		'video.getuploadlimits' => 1,
	);
	
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var string The Application ID.
	 */
	protected $_appId;
	public function getAppId() { return $this->_appId; }
	public function setAppId( $newValue ) { $this->_appId = $newValue; return $this; }

	/**
	 * @var string the application name
	 */
	protected $_appName;
	public function getAppName() { return $this->_appName; }
	public function setAppName( $newValue ) { $this->_appName = $newValue; return $this; }

	/**
	 * @var string the application name
	 */
	protected $_appUrl;
	public function getAppUrl() { return $this->_appUrl; }
	public function setAppUrl( $newValue ) { $this->_appUrl = $newValue; return $this; }

	/**
	 * @var string the application permissions to request
	 */
	protected $_appPermissions = 'publish_stream';
	public function getAppPermissions() { return $this->_appPermissions; }
	public function setAppPermissions( $newValue ) { $this->_appPermissions = $newValue; return $this; }

	/**
	 * @var string The api key.
	 */
	protected $_apiKey;
	public function getApiKey() { return $this->_apiKey; }
	public function setApiKey( $newValue ) { $this->_apiKey = $newValue; return $this; }

	/**
	 * @var string The Application API Secret.
	 */
	protected $_apiSecretKey;
	public function getApiSecretKey() { return $this->_apiSecretKey; }
	public function setApiSecretKey( $newValue ) { $this->_apiSecretKey = $newValue; return $this; }

	/**
	 * @var string The callback url
	 */
	protected $_apiCallbackUrl;
	public function getApiCallbackUrl() { return $this->_apiCallbackUrl; }
	public function setApiCallbackUrl( $newValue ) { $this->_apiCallbackUrl = $newValue; return $this; }

	/**
	 * @var boolean Indicates that we already loaded the session as best as we could.
	 */
	protected $_sessionLoaded = false;
	public function getSessionLoaded() { return $this->_sessionLoaded; }
	public function setSessionLoaded( $newValue ) { $this->_sessionLoaded = $newValue; return $this; }

	/**
	 * @var string The signed request
	 */
	protected $_signedRequest;
	public function getSignedRequest()
	{
		if ( ! $this->_signedRequest )
		{
			if ( null !== ( $_request = PS::o( $_REQUEST, 'signed_request' ) ) )
				$this->_signedRequest = $this->_parseSignedRequest( $_request );
		}

		return $this->_signedRequest;
	}
	public function setSignedRequest( $newValue ) { $this->_signedRequest = $newValue; return $this; }

	/**
	 * @var boolean Indicates if Cookie support should be enabled.
	 */
	protected $_enableCookieSupport = false;
	public function getEnableCookieSupport() { return $this->_enableCookieSupport; }
	public function setEnableCookieSupport( $newValue ) { $this->_enableCookieSupport = $newValue; return $this; }

	/**
	 * @var string Base domain for the Cookie.
	 */
	protected $_baseDomain = '';
	public function getBaseDomain() { return $this->_baseDomain; }
	public function setbaseDomain( $newValue ) { $this->_baseDomain = $newValue; return $this; }

	/**
	 * @var boolean Indicates if the CURL based @ syntax for file uploads is enabled.
	 */
	protected $_fileUploadSupport = false;
	public function getFileUploadSupport() { return $this->_fileUploadSupport; }
	public function setFileUploadSupport( $newValue ) { $this->_fileUploadSupport = $newValue; return $this; }

	/**
	 * @var boolean Indicates if the CURL based @ syntax for file uploads is enabled.
	 */
	protected $_redirectToLoginUrl = true;
	public function getRedirectToLoginUrl() { return $this->_redirectToLoginUrl; }
	public function setRedirectToLoginUrl( $newValue ) { $this->_redirectToLoginUrl = $newValue; return $this; }

	/**
	 * @var array The list of user photos
	 */
	public static $_photoList = null;
	public static function getPhotoList() { return self::$_photoList; }
	public static function setPhotoList( $value ) { self::$_photoList = $value; }

	/**
	 * @var array The active user session, if one is available.
	 */
	protected $_session;

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize a Facebook Application.
	 *
	 * The configuration:
	 * - appId: the application ID
	 * - secret: the application secret
	 * - cookie: (optional) boolean true to enable cookie support
	 * - domain: (optional) domain for the cookie
	 *
	 * @param array $config the application configuration
	 * @throws CPSFacebookApiException
	 */
	public function __construct( $config = array() )
	{
		if ( ! function_exists( 'curl_init' ) )
			throw new CHttpException( 405, 'This class requires the CURL PHP extension.' );

		if ( ! function_exists( 'json_decode' ) )
			throw new CHttpException( 405, 'This class requires the JSON PHP extension.' );

		$this->_appId = PS::o( $config, 'appId' );
		$this->_apiSecretKey = PS::o( $config, 'secret' );
		$this->_enableCookieSupport = PS::o( $config, 'cookie', false );
		$this->_baseDomain = PS::o( $config, 'domain', '' );
		$this->_fileUploadSupport = PS::o( $config, 'fileUploadSupport', false );
	}
	
	/**
	 * Initialize
	 */
	public function init()
	{
		parent::init();
		self::$_photoList = PS::_gs( self::PHOTO_CACHE );
	}
	
	/**
	 * Put it in the cache..
	 */
	public function __destruct()
	{
		if ( ! empty( self::$_photoList ) )
			PS::_ss( self::PHOTO_CACHE, self::$_photoList );
	}

	/**
	 * Set the session.
	 *
	 * @param array $session the session
	 * @param boolean $writeCookie indicate if a cookie should be written. this value is ignored if cookie support has been disabled.
	 */
	public function setSession( $session = null, $writeCookie = true )
	{
		$this->_session = $this->_validateSessionObject( $session );
		$this->_sessionLoaded = true;

		if ( $writeCookie ) $this->_setCookieFromSession( $this->_session );

		return $this;
	}

	/**
	 * Get the session object. This will automatically look for a signed session
	 * sent via the Cookie or Query Parameters if needed.
	 *
	 * @return array the session
	 */
	public function getSession()
	{
		if ( ! $this->_sessionLoaded )
		{
			$_session = null;
			$_writeCookie = true;

			//	Try loading session from signed_request in $_REQUEST
			if ( $_signedRequest = $this->getSignedRequest() )
				$_session = $this->createSessionFromSignedRequest( $_signedRequest );

			//	Try loading session from $_REQUEST
			if ( ! $_session && $_session = PS::o( $_REQUEST, 'session' ) )
			{
				$_session = json_decode( get_magic_quotes_gpc() ? stripslashes( $_session ) : $_session, true );
				$_session = $this->_validateSessionObject( $_session );
			}

			//	Try loading session from cookie if necessary
			if ( ! $_session && $this->_enableCookieSupport )
			{
				$_name = $this->_getSessionCookieName();

				if ( $_cookie = PS::o( $_COOKIE, $_name ) )
				{
					$_session = array();
					parse_str( trim( get_magic_quotes_gpc() ? stripslashes( $_cookie ) : $_cookie, '"' ), $_session );
					$_session = $this->_validateSessionObject( $_session );

					//	Write only if we need to delete a invalid session cookie
					$_writeCookie = empty( $_session );
				}
			}

			$this->setSession( $_session, $_writeCookie );
		}

		return $this->_session;
	}

	/**
	 * Get the UID from the session.
	 * @return string the UID if available
	 */
	public function getUser()
	{
		if ( $_session = $this->getSession() )
			return PS::o( $_session, 'uid' );

		return null;
	}

	/**
	 * Gets a OAuth access token.
	 *
	 * @return string the access token
	 */
	public function getAccessToken()
	{
		$_session = $this->getSession();

		//	Either user session signed, or app signed
		if ( $_session )
			return PS::o( $_session, 'access_token' );

		return $this->_appId . '|' . $this->_apiSecretKey;
	}

	/**
	 * Get a Login URL for use with redirects. By default, full page redirect is
	 * assumed. If you are using the generated URL with a window.open() call in
	 * JavaScript, you can pass in display=popup as part of the $paramList.
	 *
	 * The parameters:
	 * - next: the url to go to after a successful login
	 * - cancel_url: the url to go to after the user cancels
	 * - req_perms: comma separated list of requested extended perms
	 * - display: can be "page" (default, full page) or "popup"
	 *
	 * @param Array $paramList provide custom parameters
	 * @return String the URL for the login flow
	 */
	public function getLoginUrl( $paramList = array() )
	{
		$_currentUrl = $this->_getCurrentUrl();

		return $this->_getUrl(
			'www',
			'login.php',
			array_merge( array(
				'api_key' => $this->_appId,
				'cancel_url' => 'http://www.facebook.com',
				'display' => 'page',
				'fbconnect' => 1,
				'next' => $_currentUrl,
				'return_session' => 1,
				'session_version' => 3,
				'v' => '1.0',
			), $paramList )
		);
	}

	/**
	 * Get a Logout URL suitable for use with redirects.
	 *
	 * The parameters:
	 * - next: the url to go to after a successful logout
	 *
	 * @param array $paramList provide custom parameters
	 * @return string the URL for the logout flow
	 */
	public function getLogoutUrl( $paramList = array() )
	{
		$_session = $this->getSession();

		return $this->_getUrl(
			'www',
			'logout.php',
			array_merge( array(
				'next' => $this->_getCurrentUrl(),
				'access_token' => $this->getAccessToken(),
			), $paramList )
		);
	}

	/**
	 * Get a login status URL to fetch the status from facebook.
	 *
	 * The parameters:
	 * - ok_session: the URL to go to if a session is found
	 * - no_session: the URL to go to if the user is not connected
	 * - no_user: the URL to go to if the user is not signed into facebook
	 *
	 * @param array $paramList provide custom parameters
	 * @return string the URL for the logout flow
	 */
	public function getLoginStatusUrl( $paramList = array() )
	{
		return $this->_getUrl(
			'www',
			'extern/login_status.php',
			array_merge( array(
				'api_key' => $this->_appId,
				'no_session' => $this->_getCurrentUrl(),
				'no_user' => $this->_getCurrentUrl(),
				'ok_session' => $this->_getCurrentUrl(),
				'session_version' => 3,
			), $paramList )
		);
	}

	/**
	 * Make an API call.
	 *
	 * @param array $paramList the API call parameters
	 * @return the decoded response
	 */
	public function api( /* polymorphic */ )
	{
		$_args = func_get_args();

		if ( is_array( $_args ) && count( $_args ) && is_array( $_args[0] ) )
			return $this->_restserver( $_args[0] );

		return call_user_func_array( array( &$this, '_graph' ), $_args );
	}
	
	/**
	 * Fills the album cache 
	 * @param string The album ID to return, null for all
	 */
	public function getAlbums( $id = null )
	{
		static $_inProgress = false;

		if ( null !== ( $_user = User::model()->find( ':pform_user_id_text = pform_user_id_text', array( ':pform_user_id_text' => $this->_session['uid'] ) ) ) )
		{
			CPSLog::trace( __METHOD__, 'Getting photos from user table cache...' );
			self::$_photoList = json_decode( $_user->photo_cache_text, true );
		}

		if ( $_inProgress ) return self::$_photoList;
		
		if ( empty( self::$_photoList ) )
		{		
			CPSLog::trace( __METHOD__, 'Reloading photo cache...' );
			$_inProgress = true;
			self::$_photoList = array();
			
			try
			{
				$_result = $this->api( '/me/albums' );
				if ( null != ( self::$_photoList = PS::o( $_result, 'data' ) ) )
				{
					$_result = array();

					foreach ( self::$_photoList as $_key => $_album )
					{
						self::$_photoList[$_key]['photos'] = $this->getPhotos( $_album['id'] );
						
						if ( ! empty( self::$_photoList[$_key]['photos'] ) )
						{
							foreach ( self::$_photoList[$_key]['photos'] as $_photo )
							{
								if ( isset( $_photo['picture'] ) )
								{
									self::$_photoList[$_key]['picture'] = $_photo['picture'];
									break;
								}
							}
						}
					}
					
					CPSLog::trace( __METHOD__, 'Saving photos to user table cache...' );
					$_user->photo_cache_text = json_encode( self::$_photoList );
					$_user->update( array( 'photo_cache_text' ) );
				}
			}
			catch ( Exception $_ex )
			{
				CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
			}
		}

		$_inProgress = false;
		return self::$_photoList;
	}
	
	/**
	 * Retrieves photos or a photo
	 * @param string The album ID
	 * @param string The photo ID or null for all photos in the album
	 */
	public function getPhotos( $aid, $id = null, $limit = null )
	{
		static $_recursed = false;
		
		if ( null == $aid )
			return null;
		
		if ( null == self::$_photoList )
			$this->getAlbums();
			
		if ( isset( self::$_photoList, self::$_photoList[$aid], self::$_photoList[$aid]['photos'] ) )
		{
			$_photoList = self::$_photoList[$aid]['photos'];
			if ( ! empty( $id ) ) return PS::o( $_photoList, $id );
		}

		//	Not there, grab photos and cache...
		$_parameterList = array();
		if ( null != $limit ) $_parameterList['limit'] = $limit;

		$_url = '/' . $aid . '/photos';
		$_resultList = array();

		while ( true )
		{
			try
			{
				$_tempList = $this->api( $_url, $_parameterList );
			}
			catch ( Exception $_ex )
			{
				break;
			}

			if ( $_tempList )
			{
				$_resultList = array_merge( $_resultList, PS::o( $_tempList, 'data', array() ) );
					
				if ( null != $limit || null === ( $_url = PS::oo( $_tempList, 'paging', 'next' ) ) )
					break;
			}
			else
				break;
		}

		return $_resultList;
	}
	
	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * The name of the cookie that contains the session.
	 * @return string the cookie name
	 */
	protected function _getSessionCookieName()
	{
		return 'pYe_fb_' . $this->_appId;
	}

	/**
	 * Invoke the old restserver.php endpoint.
	 *
	 * @param array $paramList method call object
	 * @return the decoded response object
	 * @throws CPSFacebookApiException
	 */
	protected function _restserver( $paramList )
	{
		//	Generic application level parameters
		$paramList['api_key'] = $this->_appId;
		$paramList['format'] = 'json-strings';

		$_result = json_decode( $this->_oauthRequest( $this->_getApiUrl( PS::o( $paramList, 'method' ) ), $paramList ), true );

		//	Results are returned, errors are thrown
		if ( PS::o( $_result, 'error_code' ) )
			throw new CPSFacebookApiException( $_result );

		return $_result;
	}

	/**
	 * Invoke the Graph API.
	 *
	 * @param string $path the path (required)
	 * @param string $method the http method (default 'GET')
	 * @param array $paramList the query/post data
	 * @return the decoded response object
	 * @throws CPSFacebookApiException
	 */
	protected function _graph( $path, $method = 'GET', $paramList = array() )
	{
		if ( is_array( $method ) && empty( $paramList ) )
		{
			$paramList = $method;
			$method = 'GET';
		}

		//	Method override as we always do a POST
		$paramList['method'] = $method;

		$_result = $this->_oauthRequest( $this->_getUrl( 'graph', $path ), $paramList );
		$_result = json_decode( $_result, true );
		
		//	Results are returned, errors are thrown
		if ( PS::o( $_result, 'error' ) )
		{
			$_ex = new CPSFacebookApiException( $_result );
			
			switch ( $_ex->getType() )
			{
				case 'OAuthException':			//	OAuth 2.0 Draft 00 style
				case 'invalid_token':			//	OAuth 2.0 Draft 10 style
					$this->setSession( null );
					break;
			}
			
			throw $_ex;
		}		

		return $_result;
	}

	/**
	 * Make a OAuth Request
	 *
	 * @param string $path the path (required)
	 * @param array $paramList the query/post data
	 * @return the decoded response object
	 * @throws CPSFacebookApiException
	 */
	protected function _oauthRequest( $url, $paramList )
	{
		if ( ! isset( $paramList['access_token'] ) )
			$paramList['access_token'] = $this->getAccessToken();

		//	json_encode all params values that are not strings
		foreach ( $paramList as $_key => $_value )
		{
			if ( ! is_string( $_value ) )
				$paramList[$_key] = json_encode( $_value );
		}

		return $this->_makeRequest( $url, $paramList );
	}

	/**
	 * Makes an HTTP request. This method can be overriden by subclasses if
	 * developers want to do fancier things or use something other than curl to
	 * make the request.
	 *
	 * @param string $url the URL to make the request to
	 * @param array $paramList the parameters to use for the POST body
	 * @param integer $curl optional initialized curl handle
	 * @return string the response text
	 */
	protected function _makeRequest( $url, $paramList, $curl = null )
	{
		if ( ! $curl ) $curl = curl_init();

		$_options = self::$_CURL_OPTS;

		if ( $this->_fileUploadSupport )
			$_options[CURLOPT_POSTFIELDS] = $paramList;
		else
			$_options[CURLOPT_POSTFIELDS] = http_build_query( $paramList, null, '&' );

		$_options[CURLOPT_URL] = $url;

		/**
		 * Disable the 'Expect: 100-continue' behaviour. This causes CURL
		 * to wait for 2 seconds if the server does not support this header.
		 */
		if ( ! isset( $_options[CURLOPT_HTTPHEADER] ) )
			$_options[CURLOPT_HTTPHEADER] = array( 'Expect:' );
		else
		{
			$_headers = $opts[CURLOPT_HTTPHEADER];
			$_headers[] = 'Expect:';
			$_options[CURLOPT_HTTPHEADER] = $_headers;
		}

		curl_setopt_array( $curl, $_options );

		$_result = curl_exec( $curl );

		if ( false === $_result )
		{
			$_ex = new CPSFacebookApiException(
				array(
					'error_code' => curl_errno( $curl ),
					'error' => array(
						'message' => curl_error( $curl ),
						'type' => 'CurlException',
					),
				)
			);

			curl_close( $curl );

			throw $_ex;
		}

		curl_close( $curl );
		return $_result;
	}

	/**
	 * Set a JS cookie based on the _passed in_ session. It does not use the
	 * currently stored session -- you need to explicitly pass it in.
	 *
	 * @param array $session the session to use for setting the cookie
	 */
	protected function _setCookieFromSession( $session = null )
	{
		if ( ! $this->_enableCookieSupport )
			return;

		$_cookieName = $this->_getSessionCookieName();
		$_value = 'deleted';
		$_expires = time() - 3600;

		$_domain = $this->getBaseDomain();

		if ( $session )
		{
			$_value = '"' . http_build_query( $session, null, '&' ) . '"';

			if ( isset( $session['base_domain'] ) )
				$_domain = $session['base_domain'];

			$_expires = $session['expires'];
		}

		//	prepend dot if a domain is found
		if ( $_domain ) $_domain = '.' . $_domain;

		//	if an existing cookie is not set, we dont need to delete it
		if ( $_value == 'deleted' && empty( $_COOKIE[$_cookieName] ) )
			return;

		if ( headers_sent() )
		{
			// disable error log if we are running in a CLI environment
			// @codeCoverageIgnoreStart
			if ( php_sapi_name() != 'cli' )
				CPSLog::error( __METHOD__, 'Could not set cookie. Headers already sent.' );
			// @codeCoverageIgnoreEnd

		// ignore for code coverage as we will never be able to setcookie in a CLI
		// environment
		// @codeCoverageIgnoreStart
		}
		else
		{
			setcookie( $_cookieName, $_value, $_expires, '/', $_domain );
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Validates a session_version=3 style session object.
	 *
	 * @param array $session the session object
	 * @return array the session object if it validates, null otherwise
	 */
	protected function _validateSessionObject( $session )
	{
		$_session = null;

		//	Make sure some essential fields exist
		if ( is_array( $session ) && isset( $session['uid'], $session['access_token'], $session['sig'] ) )
		{
			//	Validate the signature
			$_noSigSession = $session;
			unset( $_noSigSession['sig'] );

			$_expectedSig = self::_generateSignature(
				$_noSigSession,
				$this->_apiSecretKey
			);

			if ( $session['sig'] != $_expectedSig )
			{
				//	Disable error log if we are running in a CLI environment
				//	@codeCoverageIgnoreStart
				if ( php_sapi_name() != 'cli' )
					CPSLog::error( __METHOD__, 'Got invalid session signature in cookie.' );
				// @codeCoverageIgnoreEnd
				$session = null;
			}

			$_session = $session;
		}

		return $_session;
	}

	/**
	* Returns something that looks like a JS session object from the
	* signed token's data
	*
	* @param Array the output of getSignedRequest
	* @return Array Something that will work as a session
	* @TODO: Nuke this once the login flow uses OAuth2
	*/
	protected function _createSessionFromSignedRequest( $data )
	{
		if ( ! PS::o( $data, 'oauth_token' ) )
			return null;

		$_session = array(
			'uid' => PS::o( $data, 'user_id' ),
			'access_token' => PS::o( $data, 'oauth_token' ),
			'expires' => PS::o( $data, 'expires' ),
		);

		//	Put a real sig, so that validateSignature works
		$_session['sig'] = self::_generateSignature(
			$_session,
			$this->_apiSecretKey
		);

		return $_session;
	}

	/**
	* Parses a signed_request and validates the signature.
	* Then saves it in $this->signed_data
	*
	* @param String A signed token
	* @param Boolean Should we remove the parts of the payload that are used by the algorithm?
	* @return Array the payload inside it or null if the sig is wrong
	*/
	protected function _parseSignedRequest( $_signedRequest )
	{
		list( $_encodedSignature, $_payload ) = explode( '.', $_signedRequest, 2 );

		//	decode the data
		$_signature = self::_base64UrlDecode( $_encodedSignature );
		$data = json_decode( self::_base64UrlDecode( $_payload ), true );

		if ( 'HMAC-SHA256' !== strtoupper( $data['algorithm'] ) )
		{
			//	Disable error log if we are running in a CLI environment
			//	@codeCoverageIgnoreStart
			if ( php_sapi_name() != 'cli' )
				CPSLog::error( __METHOD__, 'Unknown algorithm. Expected HMAC-SHA256' );
			// @codeCoverageIgnoreEnd
			return null;
		}

		//	Check signature
		$_expectedSignature = hash_hmac( 'sha256', $_payload, $this->_apiSecretKey, true );

		CPSLog::trace( __METHOD__, 'Sig:[' . $_signature . '] expect:[' . $_expectedSignature . ']' );
		
		if ( $_signature !== $_expectedSignature )
		{
			//	Disable error log if we are running in a CLI environment
			//	@codeCoverageIgnoreStart
			if ( php_sapi_name() != 'cli' )
				CPSLog::error( __METHOD__, 'Bad Signed JSON signature!' );
			// @codeCoverageIgnoreEnd
			return null;
		}

		return $data;
	}

	/**
	 * Build the URL for api given parameters.
	 *
	 * @param $method string the method name.
	 * @return string the URL for the given parameters
	 */
	protected function _getApiUrl( $method )
	{
		$_name = 'api';

		if ( PS::o( $READ_ONLY_CALLS, strtolower( $method ) ) )
			$_name = 'api_read';

		return self::_getUrl( $_name, 'restserver.php' );
	}

	/**
	 * Build the URL for given domain alias, path and parameters.
	 *
	 * @param $name string the name of the domain
	 * @param $path string optional path (without a leading slash)
	 * @param $paramList array optional query parameters
	 * @return string the URL for the given parameters
	 */
	protected function _getUrl( $name, $path = null, $paramList = array() )
	{
		$_url = self::$_DOMAIN_MAP[$name];

		if ( null != $path )
		{
			if ( $path[0] == '/' ) 
				$path = substr( $path, 1 );
			
			$_url .= $path;
		}

		if ( $paramList ) $_url .= '?' . http_build_query( $paramList );

		return $_url;
	}

	/**
	 * Returns the Current URL, stripping it of known FB parameters that should
	 * not persist.
	 *
	 * @return string the current URL
	 */
	protected function _getCurrentUrl()
	{
		$_proto = PS::o( $_SERVER, 'HTTPS' ) == 'on' ? 'https://' : 'http://';
		$_currentUrl = $_proto . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$_parts = parse_url( $_currentUrl );

		//	Drop known fb params
		$_query = null;

		if ( ! empty( $_parts['query'] ) )
		{
			$_paramList = array();

			parse_str( $_parts['query'], $_paramList );

			foreach ( self::$_DROP_QUERY_PARAMS as $_key )
				unset( $_paramList[$_key] );

			if ( ! empty( $_paramList ) )
				$_query = '?' . http_build_query( $_paramList );
		}

		//	use port if non default
		$_port = isset( $_parts['port'] ) && ( ( $_proto === 'http://' && $_parts['port'] !== 80 ) || ( $_proto === 'https://' && $_parts['port'] !== 443 ) ) ? ':' . $_parts['port'] : '';

		//	Rebuild
		return $_proto . $_parts['host'] . $_port . $_parts['path'] . $_query;
	}

	/**
	 * Generate a signature for the given params and secret.
	 *
	 * @param array $paramList the parameters to sign
	 * @param string $secret the secret to sign with
	 * @return string the generated signature
	 */
	protected static function _generateSignature( $paramList, $secret )
	{
		//	Work with sorted data
		ksort( $paramList );

		//	Generate the base string
		$_baseString = '';

		foreach ( $paramList as $_key => $_value )
			$_baseString .= $_key . '=' . $_value;

		$_baseString .= $secret;

		return md5( $_baseString );
	}

	/**
	* Base64 encoding that doesn't need to be urlencode()ed.
	* Exactly the same as base64_encode except it uses
	*   - instead of +
	*   _ instead of /
	*
	* @param string base64 encoded string
	*/
	protected static function _base64UrlDecode( $source )
	{
		return base64_decode( strtr( $source, '-_', '+/' ) );
	}

}

/**
 * CPSFacebookApiException
 *
 * This class is pretty much a complete copy of the Facebook PHP-SDK
 * that has been massaged to work within the framework of Yii.
 *
 * @package 	psYiiExtensions
 * @subpackage	components.facebook
 *
 * @author		Naitik Shah <naitik@facebook.com>
 * @link		http://github.com/facebook/php-sdk
 *
 * @author		Jerry Ablan <jablan@pogostick.com>
 *
 * @version 	SVN $Id$
 *
 * @filesource
 */
class CPSFacebookApiException extends CPSException
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	/**
	 * @var array The result of the API call
	 */
	protected $_result;
	public function getResult() { return $this->_result; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Make a new API Exception with the given result.
	 * @param array $result the result from the API server
	 */
	public function __construct( $result )
	{
		$this->_result = $result;
		$_code = PS::o( $_result, 'error_code', 0 );
		
		if ( isset( $result['error_description'] ) )
		{
			//	OAuth 2.0 Draft 10 style
			$_message = $result['error_description'];
		}
		else if ( isset( $result['error'] ) && is_array( $result['error'] ) )
		{
			// OAuth 2.0 Draft 00 style
			$_message = $result['error']['message'];
		}
		else if ( isset( $result['error_msg'] ) )
		{
			// Rest server style
			$_message = $result['error_msg'];
		}
		else
		{
			$_message = 'Unknown Error. Check getResult()';
		}
		
		parent::__construct( $_message, $_code );
	}

	/**
	 * Returns the associated type for the error. This will default to
	 * 'Exception' when a type is not available.
	 * @return string
	 */
	public function getType() 
	{	
		if ( isset( $this->_result['error'] ) )
		{
			$_error = $this->_result['error'];
			if ( is_string( $_error ) ) 
			{
				// OAuth 2.0 Draft 10 style
				return $_error;
			}
			else if ( is_array( $_error ) )
			{
				// OAuth 2.0 Draft 00 style
				if ( isset( $_error['type'] ) )
				{
					return $_error['type'];
				}
			}
		}
		
		return 'Exception';	
	}

	/**
	 * A string representation of this exception
	 * @returns string
	 */
	public function __toString()
	{
		$_temp = $this->getType() . ': ' . ( $this->code != 0 ? $this->code . ': ' : '' ) . $this->message;
	}
	
}