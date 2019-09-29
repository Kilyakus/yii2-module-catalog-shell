<?php
use bin\admin\widgets\CFields\CFields;
use kilyakus\web\widgets as Widget;

$this->title = ($model->title ? $model->title . ': ' . Yii::t('easyii', 'Fields') : Yii::t('easyii', 'Additional fields'));
?>

<?= $this->render('@bin/admin/views/category/_menu', ['category' => $model]) ?>

<?php Widget\Portlet::begin([
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs'
	],
]) ?>
	<?= CFields::widget(['model' => $model])?>
<?php Widget\Portlet::end() ?>