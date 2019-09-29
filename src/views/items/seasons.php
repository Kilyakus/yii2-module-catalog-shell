<?php
use bin\admin\widgets\ModuleSeasons\ModuleSeasons;
use kilyakus\web\widgets as Widget;

$this->title = $model->title . ': ' . Yii::t('easyii', 'Seasons');
?>

<?= $this->render('_menu', ['category' => $model->category]) ?>

<?php Widget\Portlet::begin([
    'options' => ['class' => 'kt-portlet--tabs', 'id' => 'kt_page_portlet'],
    'headerContent' => $this->render('_submenu', ['model' => $model])
]); ?>
	<?= ModuleSeasons::widget(['model' => $model])?>
<?php Widget\Portlet::end(); ?>