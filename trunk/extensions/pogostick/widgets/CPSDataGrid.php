<?php
/**
 * CPSDataGrid class file.
 *
 * @filesource
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @author Jerry Ablan <jablan@pogostick.com>
 * @link http://www.pogostick.com Pogostick, LLC.
 * @package psYiiExtensions
 * @subpackage widgets
 * @since v1.0.6
 * @version SVN: $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified  $Date$
 */
class CPSDataGrid extends CPSHelperBase
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param string $sDataName
    * @param array $arModel
    * @param array $arColumns
    * @param array $arActions
    * @param CSort $oSort
    * @param CPagination $oPages
    * @param array $arPagerOptions
    * @param mixed $sLinkView
    */
	public static function create( $sDataName, $arModel, $arColumns = array(), $arActions = array(), $oSort = null, $oPages = null, $arPagerOptions = array(), $sLinkView = 'update', $bEncode = true )
	{
		$_sPK = PS::o( $arPagerOptions, 'pk', null, true );
		$_sGridHeader = PS::o( $arPagerOptions, 'gridHeader', $sDataName, true );
		$_bAccordion = PS::o( $arPagerOptions, 'accordion', false, true );
		$_arDivComment = PS::o( $arPagerOptions, 'divComment', array(), true );

		//	Build pager...
		$_oWidget = Yii::app()->controller->createWidget( 'CPSLinkPager', array_merge( array( 'pages' => $oPages ), $arPagerOptions ) );

		//	Build grid...
		if ( $_oWidget->pagerLocation == CPSLinkPager::TOP_LEFT || $_oWidget->pagerLocation == CPSLinkPager::TOP_RIGHT ) $_oWidget->run();
		
		if ( $_bAccordion ) echo '<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top"><span class="ui-icon ui-icon-triangle-1-s" ></span><a href="#"><strong>' . $_sGridHeader . '</strong></a></h3>';
		
		$_sOut = self::beginDataGrid( $arModel, $oSort, $arColumns, ! empty( $arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $arColumns, $arActions, $sDataName, $sLinkView, $_sPK, $bEncode, $_arDivComment );
		$_sOut .= self::endDataGrid();
		
		if ( $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_LEFT || $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_RIGHT ) $_oWidget->run();

		return $_sOut;
	}

    /**
    * Outputs a data grid with pager on the bottom
    * 
    * @param string $sDataName
    * @param array $arModel
    * @param array $arColumns
    * @param array $arActions
    * @param CSort $oSort
    * @param CPagination $oPages
    * @param array $arPagerOptions
    * @param mixed $sLinkView
    */
	public static function createEx( $arModel, $arOptions = array() )
	{
		$_sPK = PS::o( $arOptions, 'pk', null, true );
		$_sDataName = self::getOption( $arOptions, 'dataItemName', 'Your Data' );
		$_arColumns = self::getOption( $arOptions, 'columns', array() );
		$_arActions = self::getOption( $arOptions, 'actions', array() );
		$_oSort = self::getOption( $arOptions, 'sort', null );
		$_oPages = self::getOption( $arOptions, 'pages', null );
		$_arPagerOptions = self::getOption( $arOptions, 'pagerOptions', array() );
		$_sLinkView = self::getOption( $arOptions, 'linkView', 'update' );
		$_iPagerLocation = self::getOption( $_arPagerOptions, 'location', CPSLinkPager::TOP_RIGHT, true );

		//	Create widget...
		if ( $_oPages ) $_oWidget = Yii::app()->controller->createWidget( 'CPSLinkPager', array_merge( array( 'pages' => $_oPages ), $_arPagerOptions ) );
		if ( $_oWidget ) $_oWidget->pagerLocation = self::nvl( $_iPagerLocation, $_oWidget->pagerLocation );

		//	Where do you want it?
		if ( $_oWidget ) if ( $_oWidget->pagerLocation == CPSLinkPager::TOP_LEFT || $_oWidget->pagerLocation == CPSLinkPager::TOP_RIGHT ) $_oWidget->run();
		
		//	Build our grid
		$_sOut = self::beginDataGrid( $arModel, $_oSort, $_arColumns, ! empty( $_arActions ) );
		$_sOut .= self::getDataGridRows( $arModel, $_arColumns, $_arActions, $_sDataName, $_sLinkView, $_sPK );
		$_sOut .= self::endDataGrid();
		
		//	Display on the bottom...
		if ( $_oWidget ) if ( $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_LEFT || $_oWidget->pagerLocation == CPSLinkPager::BOTTOM_RIGHT ) $_oWidget->run();
		
		return $_sOut;
	}

	/**
	* Creates a data grid
	* 
	* @param CModel $oModel
	* @param CSort $oSort
	* @param array $arColumns
	* @param boolean $bAddActions
	* @return string
	*/
	public static function beginDataGrid( $oModel, $oSort = null, $arColumns = array(), $bAddActions = true )
	{
		$_sHeaders = null;
		
		foreach ( $arColumns as $_sKey => $_oColumn )
		{
			$_sColumn = ( is_array( $_oColumn ) ? $_sKey = array_shift( $_oColumn ) : $_oColumn );
			$_sColumn = CPSTransform::cleanColumn( $_sColumn );
			$_sLabel = PS::o( $_oColumn, 'label', ( $oSort ) ? $oSort->link( $_sColumn ) : ( $oModel ? $oModel[0]->getAttributeLabel( $_sColumn ) : $_sColumn ), true );
			$_sHeaders .= CHtml::tag( 'th', array(), $_sLabel );
		}	

		if ( $bAddActions && ! empty( $oModel ) ) $_sHeaders .= CHtml::tag( 'th', array(), 'Actions' );
			
		return CHtml::tag( 'table', array( 'width' => '100%', 'class' => 'dataGrid' ), CHtml::tag( 'tr', array(), $_sHeaders ), false );
	}
	
	/***
	* Builds all rows for a dataGrid
	* If a column name is prefixed with an '@', it will be stripped and the column will be a link to the 'update' view
	* If a column name is prefixed with an '?', it will be stripped and the column will be treated as a boolean
	* 
	* @param array $arModel
	* @param array $arColumns
	* @param array $arActions
	* @param string $sDataName
	* @param mixed $sLinkView
	* @return string
	*/
	public static function getDataGridRows( $arModel, $arColumns = array(), $arActions = null, $sDataName = 'item', $sLinkView = null, $sPK = null, $bEncode = true, $arDivComment = array() )
	{
		$_sOut = empty( $arModel ) ? '<tr><td style="text-align:center" colspan="' . sizeof( $arColumns ) . '">No Records Found</td></tr>' : null;
		if ( null === $arActions ) $arActions = array( 'edit', 'delete' );
		$_arOptions = CPSHelp::getOption( $arActions, 'options', array(), true );
		$_sLockColumn = CPSHelp::getOption( $_arOptions, 'lockColumn', null, true );

		foreach ( $arModel as $_iIndex => $_oModel )
		{
			$_sActions = null;
			$_sPK = PS::nvl( $sPK, $_oModel->getTableSchema()->primaryKey );
			$_sTD = CPSTransform::column( $_oModel, $arColumns, $sLinkView, 'td', array( 'encode' => $bEncode ) );
				
			//	Build actions...
			if ( $_sPK && ! empty( $arActions ) )
			{
				foreach ( $arActions as $_oParts )
				{
					$_sAction = $_oParts;
					
					//	Our default view (update)
					$_sViewName = PS::nvl( $sLinkView, 'update' );

					//	If action is an array, first element is action, second is view (which can also be an array)
					if ( is_array( $_oParts ) )
					{
						$_sAction = $_oParts[0];
						$_sViewName = $_oParts[1];
					}
					
					//	Skip lock actions on non-lockable columns
					if ( $_sAction == 'lock' && ! $_sLockColumn )
						continue;
						
					//	Fix up link view array...
					$_arLink = array( $_sViewName );
					if ( is_array( $_sViewName ) ) $_arLink = $_sViewName;
					
					//	Stuff in the PK
					$_arLink[ $_sPK ] = $_oModel->{$_sPK};

					//	Add the action
					switch ( $_sAction )
					{
						case 'lock':	//	Special case if model contains lock column
							$_sLockName = ( ! $_oModel->{$_sLockColumn} ) ? 'Lock' : 'Unlock';
							$_sIconName = ( $_oModel->{$_sLockColumn} ) ? 'locked' : 'unlocked';

							//	Lock import file
							$_sActions .= CPSActiveWidgets::jquiButton( $_sLockName, $_arLink,
								array(
									'confirm' => "Do you really want to " . strtolower( $_sLockName ) . " this {$sDataName}?",
									'iconOnly' => true, 
									'icon' => $_sIconName,
									'iconSize' => 'small'
								)
							);
							break;
						
						case 'view':
						case 'edit':
							$_sActions .= CPSActiveWidgets::jquiButton( 'Edit', $_arLink, array( 'iconOnly' => true, 'icon' => $_sAction == 'edit' ? 'pencil' : 'gear', 'iconSize' => 'small' ) );
							break;
							
						case 'delete':
							$_sActions .= CPSActiveWidgets::jquiButton( 'Delete', array( 'delete', $_sPK => $_oModel->{$_sPK} ),
								array(
									'confirm' => "Do you really want to delete this {$sDataName}?",
									'iconOnly' => true, 
									'icon' => 'trash', 
									'iconSize' => 'small'
								)
							);
							break;
							
						default:	//	Catchall for prefab stuff...
							$_sActions .= str_ireplace( '%%PK_VALUE%%', $_oModel->{$_sPK}, $_sAction );
							break;
					}
				}
				
				$_sTD .= CHtml::tag( 'td', array( 'class' => 'grid-actions' ), '<div class="_grid_actions">' . $_sActions . '<hr /></div>' );
			}
			
			$_arRowOpts = array();
			if ( count( $arDivComment ) && ! empty( $_oModel->{$arDivComment[0]} ) )
				$_arRowOpts = array( 'class' => $arDivComment[1], 'title' => $_oModel->{$arDivComment[0]} );
			
			$_sOut .= CHtml::tag( 'tr', $_arRowOpts, $_sTD );
			
			//	Add subrows...
			if ( ! empty( $_oModel->subRows ) )
			{
				foreach ( $_oModel->subRows as $_oRow )
				{
					$_arInnerOptions = CPSHelp::smart_array_merge( PS::o( $_oRow, '_innerHtmlOptions', array(), true ), array( 'encode' => false ) );
					$_arOuterOptions = CPSHelp::smart_array_merge( array( 'class' => 'ps-sub-row' ), PS::o( $_oRow, '_outerHtmlOptions', array(), true ) );
					
					$_sRow = CPSTransform::column( $_oRow, array_keys( $_oRow ), null, 'td', $_arInnerOptions );

					if ( ! empty( $arActions ) )
					{
						$_sRow .= CHtml::tag( 'td', CPSHelp::smart_array_merge( $_arInnerOptions, array( 'class' => 'grid-actions' ) ), '<div class="_grid_actions">&nbsp;<hr /></div>' );
					}
						
					$_sOut .= CHtml::tag( 'tr', $_arOuterOptions, $_sRow );
				}
			}
		}
		
		return $_sOut;
	}
	
	/**
	* Closes a data grid
	* 
	*/
	public static function endDataGrid()
	{
		return '</TABLE>';
	}
	
}