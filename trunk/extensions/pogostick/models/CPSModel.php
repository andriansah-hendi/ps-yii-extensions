<?php
/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * CPSModel provides base functionality for models
 * 
 * @package 	psYiiExtensions
 * @subpackage 	models
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 * @property-read string $modelName The class name of the model
 */
class CPSModel extends CActiveRecord implements IPogostick
{
	//********************************************************************************
	//* Members
	//********************************************************************************

	/**
	* Our metadata, cached for speed
	* @var CDbMetaData
	*/
	protected $m_oMetaData;
	public function getMetaData() { return $this->m_arMetaData ? $this->m_arMetaData : $this->m_arMetaData = $this->getMetaData(); }
		
	/**
	* Our schema, cached for speed
	* @var array
	*/
	protected $m_arSchema;
	public function getSchema() { return $this->m_arSchema ? $this->m_arSchema : $this->m_arSchema = $this->getMetaData()->columns; }

	/**
	 * The associated database table name prefix.
	 * If Yii version is greater than 1.0, the dbConnection's table prefix for this model will be set.
	 * @var string
	 */
	protected $m_sTablePrefix = null;
	public function getTablePrefix() { return ( version_compare( YiiBase::getVersion(), '1.1.0' ) > 0 ) ? $this->getDbConnection()->getTablePrefix() : $this->m_sTablePrefix; }
	public function setTablePrefix( $sValue ) { ( version_compare( YiiBase::getVersion(), '1.1.0' ) > 0 ) ? $this->getDbConnection()->setTablePrefix( $sValue ) : $this->m_sTablePrefix = $sValue; }
	
	/***
	* Current transaction if any
	* @var CDbTransaction
	*/
	protected $m_oTransaction = null;
	public function getTransaction() { return $this->m_oTransaction; }
	public function setTransaction( $oValue ) { $this->m_oTransaction = $oValue; }
	public function hasTransaction() { return isset( $this->m_oTransaction ) ? $this->m_oTransaction->active : false; }
	
	/**
	* Attribute labels cache
	* @var array
	*/
	protected $m_arAttributeLabels = array();

	/**
	* Returns all attribute latbels and populates cache
	* @returns array
	* @see CPSModel::attributeLabels
	*/
	public function getAttributeLabels() { return $this->m_arAttributeLabels ? $this->m_arAttributeLabels : $this->m_arAttributeLabels = $this->attributeLabels(); }

	/**
	* Returns the text label for the specified attribute.
	* @param string $sAttribute The attribute name
	* @return string the attribute label
	* @see generateAttributeLabel
	* @see getAttributeLabels
	*/
	public function getAttributeLabel( $sAttribute )
	{
		return PS::o( $this->getAttributeLabels(), $sAttribute, $this->generateAttributeLabel( $sAttribute ) );
	}

	/**
	* Get's the name of the model class
	* 
	* @var string
	*/
	protected $m_sModelName = null;
	/**
	* Get this model's name.
	* @returns string
	*/
	public function getModelName() { return $this->m_sModelName; }
	/**
	* Set this model's name
	* @param string $sValue
	*/
	public function setModelName( $sValue ) { $this->m_sModelName = $sValue; }
	
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/***
	* Builds a CPSModel and sets the model name
	* 
	* @param array $arAttributes
	* @param string $sScenario
	* @return CPSModel
	*/
	public function __construct( $arAttributes = array(), $sScenario = '' )
	{
		parent::__construct( $arAttributes, $sScenario );
		$this->m_sModelName = ( version_compare( PHP_VERSION, '5.3.0' ) > 0 ) ? get_called_class() : get_class( $this );
	}

	/***
	* Sets our default behaviors. 
	* All CPSModel's have the DataFormat and Utility behaviors added by default.
	* @returns array
	* @see CModel::behaviors
	*/
	public function behaviors()
	{
		return array_merge(
			parent::behaviors(),
			array(
				//	Date/time formatter
				'psDataFormat' => array(
					'class' => 'pogostick.behaviors.CPSDataFormatBehavior',
				),
				
				//	Utilities
				'psUtility' => array(
					'class' => 'pogostick.behaviors.CPSUtilityBehavior',
				),
			)
		);
	}

	/***
	* Begins a database transaction
	* @throws CDbException
	*/
	public function beginTransaction()
	{
		//	Already in a transaction?
		if ( $this->m_oTransaction )
			throw new CDbException( Yii::t( 'psYiiExtensions', 'Unable to start new transaction. transaction already in progress.' ) );

		$this->m_oTransaction = $this->dbConnection->beginTransaction();
	}
	
	/**
	* Commits the current transaction if any
	*/
	public function commitTransaction()
	{
		if ( $this->m_oTransaction ) 
		{
			$this->m_oTransaction->commit();
			$this->m_oTransaction = null;
		}
	}

	/**
	* Rolls back the current transaction, if any...
	*/
	public function rollbackTransaction()
	{
		if ( $this->m_oTransaction ) 
		{
			$this->m_oTransaction->rollBack();
			$this->m_oTransaction = null;
		}
	}
	
	/***
	* Returns the errors on this model in a single string suitable for logging.
	* @param string $sAttribute Attribute name. Use null to retrieve errors for all attributes.
	* @returns string
	*/
	public function getErrorsForLogging( $sAttribute = null )
	{
		$_sOut = null;
		$_i = 1;
		
		if ( $_arErrors = $this->getErrors( $sAttribute ) )
		{
			foreach ( $_arErrors as $_sAttribute => $_arError )
				$_sOut .= $_i++ . '. [' . $_sAttribute . '] : ' . implode( '|', $_arError ) . '; ';
		}
		
		return $_sOut;
	}
	
	/**
	* Override of CModel::setAttributes
	* Populates member variables as well.
	* @param array $arValues
	* @param string $sScenario
	*/
	public function setAttributes( $arValues = array(), $sScenario = '' )
	{
		if ( '' === $sScenario ) $sScenario = $this->getScenario();
		
		if ( is_array( $arValues ) )
		{
			$_arAttributes = array_flip( $this->getSafeAttributeNames( $sScenario ) );
			
			foreach ( $arValues as $_sKey => $_oValue )
			{
				$_bIsAttribute = isset( $_arAttributes[ $_sKey ] );

				if ( $_bIsAttribute || ( $this->hasProperty( $_sKey ) && $this->canSetProperty( $_sKey ) ) )
					$this->setAttribute( $_sKey, $_oValue );
			}
		}
	}

	/**
	 * PHP sleep magic method.
	 * Take opportunity to flush schema cache...
	 * @returns array
	 */
	public function __sleep()
	{
		//	Clean up and phone home...
		$this->m_arSchema = null;
		return parent::__sleep();
	}

}