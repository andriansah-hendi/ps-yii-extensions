<?php

class CPSFormModel extends CFormModel
{
	/**
	* Fixup attribute labels for my funky naming conventions...
	*
	* @param string $sName
	* @return mixed
	*/
	public function generateAttributeLabel( $sName )
	{
		if ( substr( $sName, 0, 2 ) == 'm_' )
			$sName = substr( $sName, 3 );

		return( parent::generateAttributeLabel( $sName ) );
	}
}