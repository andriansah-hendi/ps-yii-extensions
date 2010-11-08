<?php echo "<?php\n"; ?>
/**
 * <?php echo $this->controllerClass . '.php'; ?> controller class file
 *
 * @copyright	<?php echo PS::_gp( '@copyright' ); ?>
 * @link		<?php echo PS::_gp( '@link' ); ?>
 */

//	Include Files
//	Constants
//	Global Settings

/**
 * <?php echo $this->controllerClass; ?> class file
 *
 * @package 	<?php echo PS::_gp( 'package' ); ?>
 * @subpackage	<?php echo PS::_gp( 'subPackage' ); ?>
 *
 * @author		<?php PS::_gp( '@author' ); ?>
 * @version 	SVN $Id$
 *
 * @filesource
 */
class <?php echo $this->controllerClass; ?> extends <?php echo $this->baseClass . PHP_EOL; ?>
{
	//********************************************************************************
	//* Member Variables
	//********************************************************************************

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Initialize our controller
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * Return the filter configuration for this controller
	 */
	public function filters()
	{
		return array(
		);
	}

	/**
	 * External action classes
	 */
	public function actions()
	{
		return array_merge(
			parent::actions(),
			array(
			)
		);
	}

	//********************************************************************************
	//* Actions
	//********************************************************************************
	
	<?php foreach ( $this->getActionIDs() as $_action ) : ?>
	/**
	 * <?php echo ucfirst( $action );?>
	 * @return
	 */
	public function action<?php echo ucfirst($action); ?>()
	{
		$this->render( '<?php echo $action; ?>' );
	}

	<?php endforeach; ?>

	//********************************************************************************
	//* Private Methods
	//********************************************************************************
}
