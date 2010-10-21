<?php
/*
 * CSFacebookAppController.php
 * 
 * Copyright (c) 2010 Jerry Ablan <jablan@pogostick.com>.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 * 
 * This file is part of Pogostick : Yii Extensions.
 * 
 * We share the same open source ideals as does the jQuery team, and
 * we love them so much we like to quote their license statement:
 * 
 * You may use our open source libraries under the terms of either the MIT
 * License or the Gnu General Public License (GPL) Version 2.
 * 
 * The MIT License is recommended for most projects. It is simple and easy to
 * understand, and it places almost no restrictions on what you can do with
 * our code.
 * 
 * If the GPL suits your project better, you are also free to use our code
 * under that license.
 * 
 * You don’t have to do anything special to choose one license or the other,
 * and you don’t have to notify anyone which license you are using.
 */

//	Include Files
Yii::import( 'pogostick.components.facebook.CPSFacebook' );

/**
 * CPSFacebookAppController class file.
 *
 * @package 	psYiiExtensions
 * @subpackage	controllers
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSFacebookAppController extends CPSController
{
	//********************************************************************************
	//* Configuration Parameters
	//********************************************************************************

	/**
	 * Maximum number of photos to show
	 */
	const MAX_FRIENDS_TO_SHOW = 7;
	const DEBUG = false;
	const USE_CACHE = false;
	const IS_CANVAS = true;
	const IS_CONNECT = false;
	const THUMB_CACHE = 'thumb_cache';
	const FRIEND_CACHE = 'friend_cache';
	const APP_FRIEND_CACHE = 'app_friend_cache';
	const PROFILE_CACHE = 'profile_cache';
	const ME_CACHE = 'me_cache';
	const FL_CACHE = 'fl_cache';

	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * The Facebook API
	 * @var Facebook
	 */
	protected $_facebookApi;
	public function getFacebookApi() { return $this->_facebookApi; }

	protected $_session;
	public function getSession() { return $this->_session; }

	protected $_accessToken;
	public function getAccessToken() { return $this->_accessToken; }

	protected $_fbUserId;
	public function getFBUserId() { return $this->_fbUserId; }

	protected $_me;
	public function getMe() { return $this->_me; }

	protected $_firstName;
	public function getFirstName() { return $this->_firstName; }

	/**
	 * Login Url
	 */
	protected $_loginUrl = '';
	public function getLoginUrl() { return $this->_loginUrl; }

	/**
	 * Logout Url
	 */
	protected $_logoutUrl = '';
	public function getLogoutUrl() { return $this->_logoutUrl; }

	protected $_user = null;
	public function getUser() { return $this->_user; }

	protected $_chosenProfiles = null;
	public function getChosenProfiles() { return $this->_chosenProfiles; }

	protected $_friendList = null;
	public function getFriendList() { return $this->_friendList; }

	protected $_appFriendList = null;
	public function getAppFriendList() { return $this->_appFriendList; }

	protected $_redirectToLoginUrl = true;
	public function getRedirectToLoginUrl() { return $this->_redirectToLoginUrl; }
	public function setRedirectToLoginUrl( $value ) { $this->_redirectToLoginUrl = $value; return $this; }
	
	protected $_autoLoadPictures = true;
	public function getAutoLoadPictures() { return $this->_autoLoadPictures; }
	public function setAutoLoadPictures( $value ) { $this->_autoLoadPictures = $value; return $this; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize
	 */
	public function init()
	{
		parent::init();

		 //	Set proper ini settings for FBC
        ini_set( 'zend.ze1_compatibility_mode', 0 );

        //	Handle an rss feed
        if ( isset( $_REQUEST[ 'rss' ] ) )
        {
            header( "Content-Type: application/xml; charset=ISO-8859-1" );
            echo $this->_getRssFeed();
            die();
        }

		//	No facebook?
		if ( PS::_gs( 'standalone' ) || PS::o( $_REQUEST, 'standalone' ) )
			PS::_ss( 'standalone', true );

		//	Set up events...
		$this->onFacebookLogin = array( $this, 'facebookLogin' );

		//	Set up the session for the page
		if ( ! PS::_gs( 'standalone' ) )
			$this->_initializeFacebook();
	}

	//********************************************************************************
	//* Public Actions
	//********************************************************************************

	/**
	 * Set our custom game shell layout
	 */
	public function actionIndex()
	{
		$this->render( 'index' );
	}

	/**
	 * Admin page
	 */
	public function actionAdmin()
	{
		$this->layout = 'admin';
		$this->render( ( $this->_user && $this->_user->admin_level_nbr != 0 ) ? 'admin' : 'index' );
	}

	/**
	 * Called after invitations have been sent.
	 */
	public function actionInviteComplete()
	{
		//	$_POST['ids'] is an array of invited friends...
		$this->_user->invite_count_nbr += count( PS::o( $_POST, 'ids', array() ) );
		$this->_user->update( array('invite_count_nbr') );
		$this->redirect( PS::_gp( 'appUrl' ) . '/?noSplash' );
	}

	/**
	 * Called when user removes app and/or permissions
	 */
	public function actionDeauthorize()
	{
		$this->layout = false;

		//	Reset!
		if ( $this->_user )
		{
			$this->_user->app_del_date = date( 'Y-m-d H:i:s' );
			$this->_user->access_token_text = null;
			$this->_user->session_key_text = null;
			$this->_user->save();
		}
	}

	public function actionAbout()
	{
		$this->render( 'about' );
	}

	public function actionInviteFriends()
	{
		$this->render( 'inviteFriends' );
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		$this->layout = 'blankShell';

	    if ( $error = Yii::app()->errorHandler->error )
	    {
	    	if ( Yii::app()->request->isAjaxRequest )
	    		echo $error['message'];
	    	else
	        	$this->render( 'error', $error );
	    }
	}

	//********************************************************************************
	//* Events
	//********************************************************************************

	/**
	 * Event: facebookLogin
	 * @param CEvent $event
	 */
	public function onFacebookLogin( CEvent $event )
	{
		$this->raiseEvent( 'onFacebookLogin', $event );
	}

	/**
	 * facebookLogin event handler stub
	 * @param CEvent $event
	 * @return boolean
	 */
	public function facebookLogin( CEvent $event )
	{
		$this->_getAllFriends();
		$this->_getAppFriends();
		return true;
	}

	//********************************************************************************
	//* Private Methods
	//********************************************************************************

	/**
	 * Get all friend data
	 */
	protected function _getAllFriends()
	{
		$this->_friendList = PS::_gs( self::FRIEND_CACHE );

		try
		{
			if ( empty( $this->_friendList ) )
			{
				$_fql = "SELECT uid, name, first_name, pic_big, pic_square FROM user WHERE uid IN ( SELECT uid2 FROM friend where uid1 = '{$this->_fbUserId}' ) order by name";
				$this->_friendList = $this->_facebookApi->api( array( 'method' => 'fql.query', 'query' => $_fql ) );
				PS::_ss( self::FRIEND_CACHE, $this->_friendList );
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
		}
		
		return $this->_friendList;
	}

	/**
	 * Get friend data who have this app
	 */
	protected function _getAppFriends()
	{
		$this->_appFriendList = PS::_gs( self::APP_FRIEND_CACHE );

		try
		{
			if ( empty( $this->_appFriendList ) )
			{
				$_fql = "select uid from user where is_app_user = '1' and uid in ( select uid2 from friend where uid1 = '{$this->_fbUserId}' ) order by name";
				$_list = $this->_facebookApi->api( array( 'method' => 'fql.query', 'query' => $_fql ) );

				//	Make into a list of uids...
				foreach ( $_list as $_friend )
				{
					if ( ! empty( $_friend['uid'] ) )
						$this->_appFriendList[] = '\'' . $_friend['uid'] . '\'';
				}

				PS::_ss( self::APP_FRIEND_CACHE, $this->_appFriendList );
			}
		}
		catch ( Exception $_ex )
		{
			CPSLog::error( __METHOD__, 'Exception: ' . $_ex->getMessage() );
		}
		
		return $this->_appFriendList;
	}

	/**
	 * Initialize the Facebook stuff
	 */
	protected function _initializeFacebook()
	{
		//	Create the api object
		$this->_facebookApi = PS::_a()->getComponent( 'facebook' );

		//	Get the login url
		$this->_loginUrl = $this->_facebookApi->getLoginUrl(
			array(
				'canvas' => self::IS_CANVAS,
				'fbconnect' => self::IS_CONNECT,
				'req_perms' => $this->_facebookApi->getAppPermissions(),
			)
		);

		//	Now, is it a good session?
		if ( $this->_session = $this->_facebookApi->getSession() )
		{
			if ( PS::o( $_REQUEST, 'installed' ) == '1' )
			{
				//	User added the app...
			}

			try
			{
				$this->_fbUserId = $this->_session['uid'];
				$this->_accessToken = $this->_session['access_token'];
				PS::_ss( 'accessToken', $this->_accessToken );

				//	Get our info...
				if ( null === ( $this->_me = PS::_gs( self::ME_CACHE ) ) )
				{
					$this->_me = $this->_facebookApi->api( '/me' );
					PS::_ss( self::ME_CACHE, $this->_me );
				}

				$this->_firstName = PS::o( $this->_me, 'first_name' );
				$this->_loadUser();

				if ( $this->_autoLoadPictures )
				{
					$_photoList = CPSFacebook::getPhotoList();
					if ( empty( $_photoList ) )
						PS::_rs( '_psAutoLoadPictures', '$(function(){$.get("/app/photos",function(){});});' );
				}
				
				return true;
			}
			catch ( FacebookApiException $_ex )
			{
				CPSLog::error( __METHOD__, 'FB Exception: ' . $_ex->getMessage() );
				return false;
			}
		}
		else
		{
			CPSLog::info( __METHOD__, 'No session found for this user.' );
		}

		//	If we get here, then we need to reload...
		if ( $this->_redirectToLoginUrl )
			echo '<script type="text/javascript">window.top.location.href = "' . $this->_loginUrl . '";</script>';
		else
		{
			echo $this->_loginUrl;
			flush();
		}

		return false;
	}

	/**
	 * Loads the user from the database. If the user is not found, a new row is added.
	 */
	protected function _loadUser()
	{
		$_user = null;
		
		

		//	Is this a new app user?
		if ( ! empty( $this->_fbUserId ) )
		{
			$_user = User::model()->find( array(
				'condition' => 'pform_user_id_text = :pform_user_id_text and pform_type_code = :pform_type_code',
				'params' => array(
					':pform_user_id_text' => $this->_fbUserId,
					':pform_type_code' => 1000,
				)
			));

			//	Not found, assume new...
			if ( ! $_user )
			{
				//	New user...
				$_user = new User();
				$_user->pform_user_id_text = $this->_fbUserId;
				$_user->pform_type_code = 1000;
				$_user->app_add_date = date( 'Y-m-d h:i:s' );
				$_user->app_del_date = null;
			}

			//	Set new stuff
			$_user->session_key_text = $this->_accessToken;
			$_user->last_visit_date = date( 'Y-m-d h:i:s' );

			if ( $this->_me )
			{
				$_user->first_name_text = $this->_firstName;
				$_user->last_name_text = PS::o( $this->_me, 'last_name' );
				$_user->email_addr_text = PS::o( $this->_me, 'email' );
				$_user->full_name_text = $this->_firstName . ' ' . strtoupper( substr( PS::o( $this->_me, 'last_name' ), 0, 1 ) . '.' );
			}

			//	Load app friends
			$this->_getAppFriends();
			
			//	Save info...
			$_user->save();

			//	Set our current user...
			PS::_ss( 'currentUser', $this->_user = $_user );

			//	Raise the facebook login event
			$this->onFacebookLogin( new CEvent( $_user ) );

			return true;
		}
		else
			CPSLog::error(__METHOD__,'FBUID EMPTY!' . $this->_fbUserId );

		return false;
	}

	/**
	 * Returns an RSS feed of the top x scorers
	 * @param integer items to return
	 */
	protected function _getRssFeed( $items = 5 )
	{
		$_baseUrl = $_url = PS::_gp('appUrl');
		$_rssDataUrl = $_baseUrl . '/?rss';

		$_rssData = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
	<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
		<channel>
			<title>Generation Station: Recent Posts</title>
			<link target="_top" rel="_top"><![CDATA[{$_rssDataUrl}]]></link>
		    <atom:link type="application/rss+xml" href="{$_rssDataUrl}" rel="self"/>
			<description>Generation Station: Recent Posts</description>
			<language>en-us</language>
HTML;

 		$_users = User::model()->findAll(
			array(
				'select' => 'pform_user_id_text, high_score_nbr, full_name_text',
				'condition' => 'pform_type_code = 1000',
				'limit' => $items,
				'order' => 'high_score_nbr desc'
			)
		);

		$_count = 1;

		foreach ( $_users as $_user )
		{
			$_displayName = PS::nvl( $_user->full_name_text, 'Mystery Player' );
			$_score = number_format( $_user->high_score_nbr, 0);
			$_details =<<<HTML
		<div style="text-align:center!important;width:150px!important;border:2px solid #ccc!important;-moz-border-radius:8px!important;-webkit-border-radius:8px!important;">
			#{$_count}<br />
			<img src="http://graph.facebook.com/{$_user->pform_user_id_text}/picture" /><br />
			{$_displayName}<br />
			{$_score}
		</div>
HTML;

			$_rssData .=<<<HTML
			<item>
				<title><![CDATA[{$_displayName}]]></title>
				<link target="_top" rel="_top"><![CDATA[{$_url}]]></link>
				<description><![CDATA[{$_details}]]></description>
			</item>
HTML;

			$_count++;
		}

		$_rssData .= '</channel></rss>';

		return $_rssData;
	}

}