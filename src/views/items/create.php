<?php
use kilyakus\web\widgets as Widget;

$this->title = Yii::t('easyii/infrastructure', 'Create item');
?>

<?= $this->render('_menu', ['category' => $category, 'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?php Widget\Portlet::begin([
    'title' => $this->title,
    'icon' => 'fa fa-pen',
]); ?>
    <?= $this->render(
		'_form', 
		compact(
			'model',
			'categories',
			'assign',
			'dataForm',
			'maps',
			'link',
			'class',
			'parent'
		)
	) ?>
<?php Widget\Portlet::end(); ?>