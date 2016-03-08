First off, you must have the psYiiExtensions installed on your web server and Yii has to know about it. Please see the [installation instructions](Installation.md) for more information.

Secondly, you must have the PHP oauth extension loaded. Details for installing this are on the [wiki](http://code.google.com/p/ps-yii-extensions/wiki/PHPOAuthSupport)

# OAuth #
The Twitter API uses OAuth as a method of remote authentication. I do not claim to be an OAuth expert, I just coded a wrapper for the excellent PHP oauth extension. However, in doing so I did learn a little which I will impart upon you now.

OAuth is a token based system. That is to say, once a user has been authenticated by the Twitter server, you are issued a token. This token is used to communicate with Twitter on the users' behalf until the user deletes the permissions for your application ON Twitter. For you Facebook app folks, it's like the old infinite keys.

The process is simple:

  1. You obtain a **request** token from Twitter.
  1. Redirect or allow user to click a link to the **authorize** URL provided by Twitter.
  1. User enters his/her Twitter login information and presses the "Allow" or "Deny" button (to allow your application access that is)
  1. Twitter redirects the user back to your site's **callback URL** (discussed below) with a new token.
  1. Your application makes a final call to Twitter to retrieve an **access** token using the URL just received during authentication.

I know, I know, it sounds like a huge mess. And it is. But I've simplified it considerably as you'll see below.

So follow these steps and you'll be tweeting from your website in no time at all.

# Step 1: Create your Twitter application #
Before integrating with Twitter, you'll need to create an application on the Twitter web site.

This is done by [going here](http://twitter.com/oauth_clients/new) and filling out the information. Most of the information asked of you is rudimentary. However, one item deserves attention. This is the **Callback URL**.

## Callback URL ##
The Callback URL is the URL on **YOUR** web site to which Twitter will redirect **after** a
user has authenticated against the Twitter system. In my implementation I used a callback
url of http://www.mysite.com/site/twitter.

## Save and Create ##
Now, press the 'Save' button to create your application.

## Consumer Key & Secret ##
The top two, the Consumer Key and Consumer Secret are the important ones here. These are your API keys.
They allow your application to "talk" to Twitter's API. Copy these down for now, we'll need them in the next step.
You can always revisit the link above to see them again, so no worries if you've already clicked-through.

# Step 2: Configure Yii #
Now that you've told Twitter about your application, we need to tell Yii to create the Twitter component.
This can easily be done in your configuration file, main.php. If you have not yet configured your main.php
for the psYiiExtension library, now is a fantastic time to do so. Please see the [installation and configuration](Installation.md)
wiki page for more info on that.

To enable the Twitter component, add the following code to your 'components' array in main.php:

```
		//	Twitter API
		'twitter' => array(
			'class' => 'pogostick.components.twitter.CPSTwitterApi',
			'apiKey' => '*your_consumer_key*',
			'apiSecretKey' => '*your_consumer_secret*',
			'apiBaseUrl' => 'http://twitter.com',
			'callbackUrl' => '*your_callback_url*',
			'format' => 'array',
		),  
```

Be sure to replace the **bold text** items with the information you entered and obtained when you created your Twitter application in step #1.

# Step 3: Integration #
There are basically two methods of integrating with Twitter:

  * Your site is tweeting updates for itself (or you)

or

  * Your site is tweeting on behalf of a user

In either case, you'll need to obtain an access token from the Twitter OAuth server. This is done by calling the **authorize** URL.
In the code sample below is ALL the code from my views/site/twitter.php (also my callback URL):

```
	<?php 
	$this->pageTitle=Yii::app()->name . ' - Attach to Twitter';
	?>
	
	<? if ( ! Yii::app()->twitter->isAuthorized ) { ?>
	<h1>Link your Twitter Account!</h1>
	
	<div class="yiiForm">
		<?php echo CHtml::form(); ?>
			<div class="simple">
				Click the button below to be taken to <a href="http://www.twitter.com/" target=_blank>Twitter</a> to link your account.
				<br />
				<br />
				<br />
				<a href="<?=Yii::app()->twitter->getAuthorizeUrl()?>"><img src="/images/twitter_sign.png" border="0" alt="Sign in with Twitter" /></a>
			</div>
		</form>
	</div><!-- yiiForm -->
	<? } else { ?>
	<h1>Linked with Twitter!</h1>
	
	<div class="yiiForm">
		<?php echo CHtml::form(); ?>
			<div class="simple">
				Your account is currently linked to your Twitter account *<?= Yii::app()->twitter->screenName ?>.
				<br />
				<br />
				<br />
			</div>
		</form>
	</div><!-- yiiForm -->
	<? } ?>
```

## Capturing the Access Token ##
After the user has authenticated with Twitter, your callback is called and the psYiiExtension library will pull out
the new token and retrieve the permanent access token. It is up to you to store this someplace. The library places
the token in a user session state. You can retrieve as follows:

```
	if ( Yii::app()->twitter->isAuthorized )
		$_arToken = Yii::app()->twitter->getToken();
	else
		echo 'User is not authorized';
```

The token is actually an array with two (or more once authenticated) elements. After authorization, the array contains the following elements:

| **oauth\_token** | The access token |
|:-----------------|:-----------------|
| **oauth\_token\_secret** | The access token secret |
| **user\_id**     | The user's Twitter numeric user ID |
| **screen\_name** | The user's Twitter screen name |

Save these off for later use. You can use a session variable or store them in your database.

### Authorization Event ###
An alternate to this method is to use the **onUserAuthorized** event. This event is generated by the CPSOAuthComponent
when authorization is complete. You must subclass CPSTwitterApi to capture this event and do with it what you please.
Not a big deal, and kinda cool, no?

## Return trips to the site and reloading the token ##
After the session ends, the tokens are lost and you will need to load up your stored access tokens for
the current user (or your site). This is done via the **CPSTwitterApi::loadData()** method. It is up to
you where you do this. Preferably, find a place where you actually need to use the Twitter API and load
it there. Otherwise you'll end up loading it every time a page is loaded from your site and that's not
very fast or Yii-like.

I ended up creating a subclass of CWebUser called CPSWebUser. Feel free to use some or all of it.

Here's the code:

```
<?php
/**
 * CPSWebUser class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://ps-yii-extensions.googlecode.com
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */
class CPSWebUser extends CWebUser
{
	//********************************************************************************
	//* Member variables
	//********************************************************************************
	
	/**
	* An array of currently authorized platform applications
	* 	
	* @var array
	*/
	protected $m_arAuth = array();
	/**
	* Indicates if platform identities have been loaded...
	* 
	* @var boolean
	*/
	protected $m_bLoaded = false;
	
	//********************************************************************************
	//* Private methods
	//********************************************************************************
	
	/**
	* Retrieves the user's attached platform accounts
	*/
	public function loadPlatformIdentities( $oPForm = null )
	{
		$_oUser = Yii::app()->user;
		
		if ( ! $_oUser->isGuest )
		{
			//	Load defaults from DB...
			if ( null == $oPForm )
				$oPForm = UserPlatform::model()->findAll( 'user_uid = :user_uid', array( ':user_uid' => $_oUser->id ) );
			
			if ( $oPForm )
			{
				foreach ( $oPForm as $_arRow )
				{
					if ( ! empty( $_arRow->pform_token_text ) )
					{
						$this->m_arAuth[ $_arRow->pform_type_code ] = true;
						
						switch ( $_arRow->pform_type_code )
						{
							case UserPlatform::PTC_TWITTER:
								Yii::app()->twitter->loadData( $_arRow->pform_user_id_text, $_arRow->pform_user_name_text, true, array( 'oauth_token' => $_arRow->pform_token_text, 'oauth_token_secret' => $_arRow->pform_token_secret_text ) );
								break;
						}
					}
				}
				
				//	Ok, we've loaded them
				$this->m_bLoaded = true;
			}
		}
	}
	
	//********************************************************************************
	//* Yii Overrides
	//********************************************************************************
	
	/**
	* Loads identity states from an array and saves them to persistent storage.
	* 
	* @param array $arStates the identity states
	*/
	protected function loadIdentityStates( $arStates )
	{
		//	Call home...
		parent::loadIdentityStates( $arStates );
		
		//	Load our platform identities
		$this->loadPlatformIdentities();
	}
	
	/**
	* Checks if user is authorized with an external application
	* 
	* @param integer $iPTC The platform type code
	* @return boolean
	*/
	public function isAppAuthorized( $iPTC )
	{
		if ( ! $this->m_bLoaded ) $this->loadPlatformIdentities();
		return isset( $this->m_arAuth[ $iPTC ] ) && $this->m_arAuth[ $iPTC ];
	}
	
	/**
	* Handle an application authorization...
	* 
	* @param integer $iPTC The platform type code
	* @param array $arToken The token
	*/
	public function authorizeApp( $iPTC, $arToken = array() )
	{
		$_oUser = User::model()->findByPk( Yii::app()->user->id );
		if ( $_oUser )
		{
			//	Add or update platform 
			if ( ! ( $_oPForm = $_oPForm = UserPlatform::model()->findByPk( array( 'user_uid' => $_oUser->user_uid, 'pform_type_code' => $iPTC ) ) ) )
			{
				$_oPForm = new UserPlatform();
				$_oPForm->user_uid = $_oUser->user_uid;
				$_oPForm->pform_type_code = $iPTC;
			}
			
			if ( isset( $arToken[ 'user_id' ] ) )
			{
				$_oPForm->pform_token_text = $arToken[ 'oauth_token' ];
				$_oPForm->pform_token_secret_text = $arToken[ 'oauth_token_secret' ];
				$_oPForm->pform_user_id_text = $arToken[ 'user_id' ];
				$_oPForm->pform_user_name_text = $arToken[ 'screen_name' ];
				$this->m_arAuth[ $iPTC ] = true;
			}
			else
			{
				$_oPForm->pform_token_text = null;
				$_oPForm->pform_token_secret_text = null;
				$_oPForm->pform_user_id_text = null;
				$_oPForm->pform_user_name_text = null;
			}
			
			//	Save it...
			$_oPForm->save();
			
			//	Initialize the Twitter object
			$this->loadPlatformIdentities( array( $_oPForm ) );
		}
	}
}
```

Now my site allows multiple identities (i.e. Facebook, Twitter, Flickr, etc.) and I needed a single management point. I have a table that stores the token information for each platform.
When a user authenticates on Yii, the **loadIdentityStates()** method is called. I've overriden this method to also call my **loadPlatformIdentities()** method.
This looks up my tokens from the database and pre-loads them into the various platform objects like Twitter:

```
	Yii::app()->twitter->loadData( 'user_id', 'screen_name', is_authenticated, array( 'oauth_token' => oauth_token, 'oauth_token_secret' => oauth_token_secret ) );
```

Once you make that call, you're all set to use the Twitter API!

# Step 4: Make Twitter API Calls! #
Now you've got it all set up, all you need to do is make a call to the Twitter API. The CPSTwitterApi object
handles all the nasty OAuth token passing and whatnot to let you concentrate and retrieving and using Twitter data.
Almost all of the Twitter API functions have been converted to single methods in the CPSTwitterApi class. Have a
look-see through there and you'll see all the calls you can make. Some of the Twitter API calls have the following
three parameters: an 'id', a user's 'user\_id', or a screen name. Most of these calls require that you supply ONE of
the three values. You already have user\_id and screen\_name. The first 'id' is for use with the Twitter Search API.
This API is actually a second separate API and the 'id' values returned from that API do not jive with the 'user\_id'
values from the non-search API. Confusing, yes. They're working on fixing it. Many calls default to the id of the
authenticating user (i.e. the user who's token you're using).

The way we created our component in the main.php configuration file, all data returned to you from CPSTwitterApi will
be in an associative array. I like it that way. You can change the 'format' parameter in the configuration file to
either 'json', 'xml' or 'array'. The data will be returned to you in that format. I find arrays easiest to deal with in PHP.

So, here's a sample call I made to get mentions of my Twitter account:

```
	$_arResults = Yii::app()->twitter->getMentions();
	echo var_export( $_arResults, true );
```

Produces:

```
array (
  0 => 
  stdClass::__set_state(array(
     'text' => '@jablan Hah, couldn\'t be further from.',
     'in_reply_to_user_id' => 14147388,
     'user' => 
    stdClass::__set_state(array(
       'followers_count' => 81,
       'profile_image_url' => 'http://s3.amazonaws.com/twitter_production/profile_images/53267682/april2008_2_normal.jpg',
       'description' => 'I make interesting things work in interesting ways for the benefit of interesting people.',
       'utc_offset' => -21600,
       'profile_sidebar_fill_color' => 'ffd7bd',
       'created_at' => 'Thu Apr 24 19:07:17 +0000 2008',
       'friends_count' => 92,
       'screen_name' => 'vanadium',
       'statuses_count' => 1272,
       'profile_sidebar_border_color' => '9e9e9e',
       'favourites_count' => 4,
       'url' => 'http://www.op9.net/',
       'name' => 'Gary DuVall',
       'protected' => false,
       'profile_text_color' => '4d4d4d',
       'verified_profile' => false,
       'profile_background_image_url' => 'http://s3.amazonaws.com/twitter_production/profile_background_images/2450081/background.jpg',
       'notifications' => NULL,
       'time_zone' => 'Central Time (US & Canada)',
       'profile_link_color' => '921611',
       'following' => NULL,
       'profile_background_tile' => true,
       'location' => 'Chicago, IL',
       'id' => 14516134,
       'profile_background_color' => 'ffffff',
    )),
     'created_at' => 'Thu Jun 11 22:24:47 +0000 2009',
     'favorited' => false,
     'in_reply_to_screen_name' => 'jablan',
     'truncated' => false,
     'id' => 2123032317,
     'in_reply_to_status_id' => 2122567595,
     'source' => 'web',
  )),
*...*
)
```

# Wrapping it up! #
So you should be well on your way to geeking with Twitter and Yii now. If I've left anything out or if you're confused or
my writing sucks, please let me know. I love feedback! jablan 'at' pogostick.com or PM me here.