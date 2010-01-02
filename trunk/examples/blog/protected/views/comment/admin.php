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
	echo CPSForm::formHeader( 'Comment Manager', 
		array( 'new' => 
			array(
				'label' => 'New Comment',
				'url' => array( 'create' ),
				'icon' => 'circle-plus',
			)
		)
	);

	$_arOpts = array(
		'actions' => array( 'edit', 'delete' ),
		'sort' => $sort,
		'pages' => $pages,
		'columns' => array( 'post_id', 'content_text', 'content_display_text', 'status_nbr', 'author_name_text', 'email_addr_text', 'url_text', 'create_date', 'lmod_date' ),
		'pagerOptions' => array( 'header' => '' ),
		'dataItemName' => 'Comment',
	);
	
	echo CPSDataGrid::createEx( $models, $_arOpts );