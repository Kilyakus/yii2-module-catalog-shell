<?php
use bin\admin\widgets\Webcams;

$this->title = $model->title . ': ' . Yii::t('easyii', 'Video');
?>

<?= $this->render('_menu', ['category' => $model->category]) ?>
<div class="card">
	<?= $this->render('_submenu', ['model' => $model]) ?>
	<?= Webcams::widget(['model' => $model])?>
</div>