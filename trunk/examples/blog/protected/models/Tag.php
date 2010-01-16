<?php
/*
 * This file was generated by the psYiiExtensions scaffolding package.
 * 
 * @copyright Copyright &copy; 2009 My Company, LLC.
 * @link http://www.example.com
 */

/**
 * Tag file
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
class Tag extends BaseModel
{
	//********************************************************************************
	//* Code Information
	//********************************************************************************
	
	/**
	* This model was generated from database component 'db'
	*
	* The followings are the available columns in table 'tag_t':
	*
	* @var integer $id
	* @var string $tag_name_text
	* @var string $create_date
	* @var string $lmod_date
	*/
	 
	//********************************************************************************
	//* Public Methods
	//********************************************************************************
	
	/**
	* Returns the static model of the specified AR class.
	* @return CActiveRecord the static model class
	*/
	public static function model( $sClassName = __CLASS__ )
	{
		return parent::model( $sClassName );
	}
	
	/**
	* @return string the associated database table name
	*/
	public function tableName()
	{
		return self::getTablePrefix() . 'tag_t';
	}

	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		return array(
			array( 'tag_name_text', 'length', 'max' => 255 ),
			array( 'tag_name_text', 'required' ),
		);
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(
			'posts' => array( self::MANY_MANY, 'Post', 'post_tag_asgn_t( post_id, tag_id )' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'tag_name_text' => 'Tag',
			'create_date' => 'Create Date',
			'lmod_date' => 'Lmod Date',
		);
	}

	/**
	 * @return array customized tooltips (attribute=>tip)
	 */
	public function attributeTooltips()
	{
		return array(
			'id' => 'Id',
			'tag_name_text' => 'Tag Name Text',
			'create_date' => 'Create Date',
			'lmod_date' => 'Lmod Date',
		);
	}
	
	/**
	 * Returns tag names and their corresponding weights.
	 * Only the tags with the top weights will be returned.
	 * @param integer the maximum number of tags that should be returned
	 * @return array weights indexed by tag names
	 */
	public function findTagWeights( $iLimit = 20 )
	{
		$_iTotalWeight = 0;
		$_arOut = array();
		
		$_oCrit = new CDbCriteria(
			array(
				'select' => 't.tag_name_text, count(post_id) as weight',
				'join' => 'INNER JOIN post_tag_asgn_t on t.id = post_tag_asgn_t.tag_id',
				'group' => 't.tag_name_text',
				'having' => 'count(post_id) > 0',
				'order' => 'weight desc',
				'limit' => $iLimit,
			)
		);

		if ( $_arTags = $this->queryAll( $_oCrit ) )
		{
			foreach ( $_arTags as $_oTag ) 
				$_iTotalWeight += $_oTag['weight'];
				
			if ( $_iTotalWeight )
			{				
				foreach ( $_arTags as $_oTag ) 
					$_arOut[ $_oTag['tag_name_text'] ] = 8 + ( int )( 16 * $_oTag['weight'] / ( $_iTotalWeight + 10 ) );
					
				ksort( $_arOut );
			}
		}

		return $_arOut;
	}
}
