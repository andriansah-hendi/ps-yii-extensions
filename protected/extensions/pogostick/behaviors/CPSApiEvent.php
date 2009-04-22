<?php
/**
 * CPSAPIEvent class file.
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com/
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @license http://www.pogostick.com/license/
 */

/**
 * CPSAPIEvent provides specialized events for CPSAPIBehavior
 *
 * @author Jerry Ablan <jablan@pogostick.com>
 * @version $Id$
 * @package application.extensions.pogostick.behaviors
 * @since 1.0.4
 */
class CPSAPIEvent extends CEvent
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* The URL being called
	*
	* @var string
	*/
	protected $m_sUrl = null;
	/**
	* The query string or post data of the call
	*
	* @var string
	*/
	protected $m_sQuery = null;
	/**
	* The results of the call
	*
	* @var mixed
	*/
	protected $m_sResults = null;

	//********************************************************************************
	//* Property Accessors
	//********************************************************************************

	//	These are all read-only

	public function getUrl() { return( $this->m_sUrl ); }
	public function getQuery() { return( $this->m_sQuery ); }
	public function getUrlResults() { return( $this->m_sResults ); }
	public function setUrlResults( $sValue ) { $this->m_sResults = $sValue; }

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	* Constructor
	*
	* @param mixed $sender
	* @return CPSAPIEvent
	*/
	public function __construct( $sUrl = null, $sQuery = null, $sResults = null, $oSender = null )
	{
		parent::__construct( $oSender );

		$this->m_sUrl = $sUrl;
		$this->m_sQuery = $sQuery;
		$this->m_sResults = $sResults;
	}
}