<?php
use bin\admin\widgets\CTypes\CTypes;
use kilyakus\web\widgets as Widget;

$this->title = $model->title . ': ' . Yii::t('easyii', 'Sections');
?>

<?= $this->render('@bin/admin/views/category/_menu', ['category' => $model]) ?>

<?php Widget\Portlet::begin([
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs'
	],
]) ?>
	<?= CTypes::widget(['model' => $model])?>
<?php Widget\Portlet::end() ?>