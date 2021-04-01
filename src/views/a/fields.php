<?php
use bin\admin\widgets\CFields\CFields;
use kilyakus\web\widgets as Widget;

$this->render('@bin/admin/views/category/_breadcrumbs');
?>

<?php Widget\Portlet::begin([
	'title' => Yii::t('easyii', 'Customize filters'),
	'icon' => 'fas fa-filter',
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs mt-5'
	],
]) ?>
	<?= CFields::widget(['model' => $model])?>
<?php Widget\Portlet::end() ?>
