<?php
use kilyakus\web\widgets as Widget;

$this->title = Yii::t('easyii', 'Edit'). ' ' .$model->title;
?>

<?= $this->render('_menu', ['category' => $model->category, 'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?php Widget\Portlet::begin([
    'options' => ['class' => 'kt-portlet--tabs', 'id' => 'kt_page_portlet'],
    'headerContent' => $this->render('_submenu', compact('model','categories','assign','dataForm','maps'))
]); ?>
    <?= $this->render(
		'_form', 
		compact(
			'model',
			'assign',
			'dataForm',
			'maps',
			'link',
			'class',
			'parent'
		)
	) ?>
<?php Widget\Portlet::end(); ?>