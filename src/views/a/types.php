<?php
use bin\admin\widgets\CTypes\CTypes;
use kilyakus\web\widgets as Widget;

$this->render('@bin/admin/views/category/_breadcrumbs');
?>

<?php Widget\Portlet::begin([
	'title' => Yii::t('easyii', 'Customize sections'),
	'icon' => 'fas fa-layer-group',
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs mt-5'
	],
]) ?>
	<?= CTypes::widget(['model' => $model])?>
<?php Widget\Portlet::end() ?>
