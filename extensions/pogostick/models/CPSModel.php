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
class CPSModel extends CActiveRecord implements IPSBase
{
	//********************************************************************************
	//* Private Members
	//********************************************************************************

	/**
	 * @var string Default sort order for data providers
	 */
	protected $_defaultSort;
	public function getDefaultSort() { return $this->_defaultSort; }
	public function setDefaultSort($value) { $this->_defaultSort = $value; }
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
	public function getTablePrefix()
	{
		return ( version_compare( YiiBase::getVersion(), '1.1.0' ) > 0 )
		?
			( $this->getDbConnection() ? $this->getDbConnection()->tablePrefix : null )
		:
			$this->m_sTablePrefix;
	}

	public function setTablePrefix( $sValue )
	{
		if ( version_compare( YiiBase::getVersion(), '1.1.0' ) > 0 )
		{
			if ( $this->getDbConnection() )
				$this->getDbConnection()->tablePrefix = $sValue;
		}
		else
			$this->m_sTablePrefix = $sValue;
	}
	
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
	* @param string $sScenario
	* @return CPSModel
	*/
	public function __construct( $sScenario = 'insert' )
	{
		parent::__construct( $sScenario );
		$this->m_sModelName = ( version_compare( PHP_VERSION, '5.3.0' ) > 0 ) ? get_called_class() : get_class( $this );
	}

	/**
	 * Checks if a component has an attached behavior
	 * @param string $sClass
	 * @returns boolean
	 */
	public function hasBehavior( $sClass )
	{
		//	Look for behaviors
		foreach ( $this->behaviors() as $_sKey => $_arBehavior )
		{
			if ( $_sBehaviorClass = PS::o( $_arBehavior, 'class' ) )
				$_sBehaviorClass = Yii::import( $_sBehaviorClass );
			
			//	Check...
			if ( $sClass == $_sKey || $sClass == $_sBehaviorClass )
				return true;
		}
		
		//	Nope!
		return false;
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
			throw new CDbException( Yii::t( 'pogostick.models', 'Unable to start new transaction. transaction already in progress.' ) );

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
	* Aware of Yii 1.1.0+
	* 
	* @param array $arValues
	*/
	public function setAttributes( $arValues, $bSafeOnly = true )
	{
		if ( version_compare( Yii::getVersion(), '1.1.0', '>=' ) )
		{
			if ( ! is_array( $arValues ) )
				return;
				
			$_arAttributes = array_flip( $bSafeOnly ? $this->getSafeAttributeNames() : $this->attributeNames() );
			
			foreach ( $arValues as $_sName => $_oValue )
			{
				if ( $_bIsAttribute = isset( $_arAttributes[ $_sName ] ) )
					$this->setAttribute( $_sName, $_oValue );
				else if ( $this->hasProperty( $_sName ) && $this->canSetProperty( $_sName ) )
					$this->{$_sName} = $_oValue;
			}
		}
		else
		{
			$sScenario = ( true === $bSafeOnly ? $this->getScenario() : $bSafeOnly );

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

	/**
	 * Executes the SQL statement and returns all rows.
	 * @param mixed $oCriteria The criteria for the query
	 * @param boolean $bFetchAssocArray Whether each row should be returned as an associated array with column names as the keys or the array keys are column indexes (0-based).
	 * @param array $arParams input parameters (name=>value) for the SQL execution. This is an alternative to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing them in this way can improve the performance. Note that you pass parameters in this way, you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa. binding methods and  the input parameters this way can improve the performance. This parameter has been available since version 1.0.10.
	 * @return array All rows of the query result. Each array element is an array representing a row. An empty array is returned if the query results in nothing.
	 * @throws CException execution failed
	 */
	public function queryAll( $oCriteria, $bFetchAssocArray = true, $arParams = array() )
	{
		//	This can all be chained together but I split it up for ease of reading/debugging
		if ( $_oCB = $this->getDbConnection()->getCommandBuilder() )
		{
			if ( $_oFind = $_oCB->createFindCommand( $this->getTableSchema(), $oCriteria ) )
				return $_oFind->queryAll( $bFetchAssocArray, $arParams );
		}
		
		return null;
	}

	/**
	 * Convenience method to get a database connection to a model's database
	 * @returns CDbConnection
	 */
	public static function getDb()
	{
		return self::model()->getDbConnection();
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions. Override for more specific search criteria.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria();

		foreach ( $this->getTableSchema()->columns as $_column )
		{
			if ( $_column->type == 'string' )
				$_criteria->compare( $_column->name, $this->{$_column->name}, true );
		}

		if ( ! $_criteria->order && $this->_defaultSort )
			$_criteria->order = $this->_defaultSort;

		return new CActiveDataProvider( get_class( $this ), array( 'criteria' => $_criteria ) );
	}

	/**
	 * Convenience method to get a database command model's database
	 * @returns CDbCommand
	 */
	public static function createCommand( $sSQL )
	{
		return self::model()->getDbConnection()->createCommand( $sSQL );
	}

	/**
	 * Convenience method to execute a query
	 * @return integer The number of rows affected by the operation
	 * @throws CException Execution failed
	 */
	public function execute( $sSQL, $arParams = array() )
	{
		return $this->getDbConnection()->createCommand( $sSQL )->execute( $arParams );
	}

	//********************************************************************************
	//* REST Methods
	//********************************************************************************

	/**
	 * If a model has a REST mapping, attributes are mapped an returned in an array.
	 * @return array|null The resulting view
	 */
	public function getRestAttributes()
	{
		if ( method_exists( $this, 'attributeRestMap' ) )
		{
			$_resultList = array();
			$_columnList = $this->getSchema();

			foreach ( $this->attributeRestMap() as $_key => $_value )
			{
				$_attributeValue = $this->getAttribute( $_key );

				//	Apply formats
				switch ( $_columnList[$_key]->dbType )
				{
					case 'date':
					case 'datetime':
					case 'timestamp':
						//	Handle blanks
						if ( null !== $_attributeValue && $_attributeValue != '0000-00-00' && $_attributeValue != '0000-00-00 00:00:00' )
							$_attributeValue = date( 'c', strtotime( $_attributeValue ) );
						break;
				}
				
				$_resultList[ $_value ] = $_attributeValue;
			}

			return $_resultList;
		}

		return null;
	}

	/**
	 * Sets the values in the model based on REST attribute names
	 * @param array $attributeList
	 */
	public function setRestAttributes( $attributeList )
	{
		if ( method_exists( $this, 'attributeRestMap' ) )
		{
			CPSLog::trace( __METHOD__, '  - Setting REST attributes' );
			
			$_map = $this->attributeRestMap();

			foreach ( $attributeList as $_key => $_value )
			{
				if ( false !== ( $_mapKey = array_search( $_key, $_map ) ) )
					$this->setAttribute( $_mapKey, $_value );
			}

			CPSLog::trace( __METHOD__, '  - REST attributes set' );
		}
	}

	//********************************************************************************
	//* Event Handlers
	//********************************************************************************
	
	/**
	* Grab our name
	* @param string $sClassName
	*/
	public function afterConstruct()
	{
		$this->m_sModelName = get_class( $this );
		parent::afterConstruct();
	}
	
}
