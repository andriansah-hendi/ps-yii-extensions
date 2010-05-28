<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * admin view file
 * 
 * @package 	blog
 * @subpackage 	
 * 
 * @author 		Web Master <webmaster@example.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 *                                                                   
 */
 	
 	//	Load our grid css
 	PS::_rcf( $this->getAppBaseUrl() . '/css/grid.css' );
 
 	//	Build the header
	echo CPSForm::formHeaderEx( 'Post Manager', array( 'menuButtons' => array( 'new' ) ) );

	//	Build the grid
	$_arOpts = array(
		'actions' => array( 
			PS::ACTION_GENERIC => array(
				'label' => 'Publish',
				'url' => array( 'publish', 'id' => '%%id%%' ),
				'confirm' => 'Publish this post?',
				'icon' => 'check',
			),
			PS::ACTION_EDIT,
			PS::ACTION_DELETE,
		),
		'sort' => $sort,
		'pages' => $pages,
		'columns' => array( 'title_text', 'statusText', ',comment_count_nbr' ),
		'pagerOptions' => array( 'header' => '' ),
		'dataItemName' => 'Post',
	);
	
	echo CPSDataGrid::createEx( $models, $_arOpts );