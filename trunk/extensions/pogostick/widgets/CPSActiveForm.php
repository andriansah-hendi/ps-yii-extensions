<?php
/*
 * CPSActiveForm.php
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
//	Constants
//	Global Settings

/**
 * Enhancements for CActiveForm
 *
 * @package 	psYiiExtensions
 * @subpackage 	widgets
 *
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN $Id$
 * @since 		v1.0.0
 *
 * @filesource
 */
class CPSActiveForm extends CActiveForm
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @var string The class of the form wrapper
	 */
	protected $_formWrapperClass = 'form';
	public function getFormWrapperClass() { return $this->_formWrapperClass; }
	public function setFormWrapperClass( $value ) { $this->_formWrapperClass = $value; }

	/**
	 * @var string The class of the row wrapper
	 */
	protected $_rowWrapperClass = 'row';
	public function getRowWrapperClass() { return $this->_rowWrapperClass; }
	public function setRowWrapperClass( $value ) { $this->_rowWrapperClass = $value; }

	/**
	 * @var CModel The model for this form
	 */
	protected $_formModel = 'row';
	public function getFormModel() { return $this->_formModel; }
	public function setFormModel( $value ) { $this->_formModel = $value; }

	/**
	 * @var array The fields in this form
	 */
	protected $_fieldList = array();
	public function getFieldList() { return $this->_fieldList; }
	public function setFieldList( $value ) { $this->_fieldList = $value; }
}