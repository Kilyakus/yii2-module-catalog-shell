<?php
use bin\admin\widgets\CContacts\CContacts;
use kilyakus\web\widgets as Widget;

$this->render('@bin/admin/views/category/_breadcrumbs');
?>

<?php Widget\Portlet::begin([
	'title' => Yii::t('easyii', 'Customize socials'),
	'icon' => 'fas fa-share-alt',
	'headerContent' => $this->render('_submenu', ['model' => $model]),
	'options' => [
		'class' => 'kt-portlet--tabs mt-5'
	],
]) ?>
	<?= CContacts::widget(['model' => $model])?>
<?php Widget\Portlet::end() ?>
