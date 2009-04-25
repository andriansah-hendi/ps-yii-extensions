<?php
/**
 * CPSApiComponent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSApiComponent provides a convenient base class for APIs
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package application.extensions.pogostick.CPSApiComponent
 * @since 1.0.4
 */
class CPSApiComponent extends CPSComponent
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructors
	*
	*/
	public function __constructor()
	{
		//	Log
		Yii::log( 'constructed psApiComponent object for [' . get_parent_class() . ']' );

		//	Call daddy
		parent::__construct();

		$this->attachBehaviors(
			array(
        		'psApi' => 'application.extensions.pogostick.behaviors.CPSApiBehavior',
        	)
		);
	}

	/**
	* Makes the actual HTTP request based on settings
	*
	* @param string $sSubType
	* @param array $arRequestData
	* @return string
	*/
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

		//	Handle events...
		$_oEvent = new CPSApiEvent( $_sUrl, $_sQuery, null, $this );
		$this->beforeApiCall( $_oEvent );

		//	Ok, we've build our request, now let's get the results...
		$_sResults = $this->makeHttpRequest( $_sUrl, $_sQuery, 'GET', $this->userAgent );

		//	Handle events...
		$_oEvent->urlResults = $_sResults;
		$this->afterApiCall( $_oEvent );

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

		//	Raise our completion event...
		$_oEvent->setUrlResults( $_sResults );
		$this->requestComplete( $_oEvent );

		//	Return results...
		return( $_sResults );
	}

}