<?php
use bin\admin\widgets\CForums\CForums;
use kilyakus\web\widgets as Widget;

$this->title = $model->title . ': ' . Yii::t('easyii', 'Forums');
?>

<?= $this->render('@bin/admin/views/category/_menu', ['category' => $model]) ?>

<?php Widget\Portlet::begin([
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs'
	],
]) ?>
	<?= CForums::widget(['items' => $items, 'model' => $model])?>
<?php Widget\Portlet::end() ?>