<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
$_nameColumn = $this->guessNameColumn( $this->tableSchema->columns );
$_label = $this->pluralize( $this->class2name( $this->modelClass ) );

echo <<<HTML
$this->setViewNavigation(
	array(
		'$_label' => array( 'index' ),
		\$model->{$_nameColumn},
	),
	array(
		array( 'label' => 'List {$this->modelClass}', 'url' => array( 'index' ) ),
		array( 'label' => 'Create {$this->modelClass}', 'url' => array( 'create' ) ),
		array( 'label' => 'Update {$this->modelClass}', 'url' => array( 'update', 'id' => \$model->{$this->tableSchema->primaryKey} ) ),
		array( 'label' => 'Delete {$this->modelClass}', 'url' => '#', 'linkOptions' => array( 'submit' => array( 'delete', 'id' => \$model->{$this->tableSchema->primaryKey} ), 'confirm' => 'Are you sure you want to delete this item?' ) ),
		array( 'label' => 'Manage {$this->modelClass}', 'url' => array( 'admin' ) ),
	)
);
HTML;
?>
<h1>View <?php echo $this->modelClass ." #<?php echo \$model->{$this->tableSchema->primaryKey}; ?>"; ?></h1>

$this->widget(
	'zii.widgets.CDetailView',
	array(
		'data' => $model,
		'attributes' => array(
<?php
	foreach( $this->tableSchema->columns as $_column )
		echo "\t\t\t'" . $_column->name . "'," . PHP_EOL;
?>
		),
	)
);
