<?php
use bin\admin\widgets\Webcams;
use kilyakus\web\widgets as Widget;

$this->title = $model->title . ': ' . Yii::t('easyii', 'Webcams');
?>

<?= $this->render('_menu', ['category' => $model->category]) ?>

<?php Widget\Portlet::begin([
    'options' => ['class' => 'kt-portlet--tabs', 'id' => 'kt_page_portlet'],
    'headerContent' => $this->render('_submenu', ['model' => $model])
]); ?>
	<?= Webcams::widget(['model' => $model])?>
<?php Widget\Portlet::end(); ?>