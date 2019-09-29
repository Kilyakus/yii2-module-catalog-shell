<?php
use bin\admin\widgets\CContacts\CContacts;
use kilyakus\web\widgets as Widget;

$this->title = $model->title . Yii::t('easyii', 'Contacts');
?>

<?= $this->render('@bin/admin/views/category/_menu', ['category' => $model]) ?>

<?php Widget\Portlet::begin([
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs'
	],
]) ?>
	<?= CContacts::widget(['model' => $model])?>
<?php Widget\Portlet::end() ?>