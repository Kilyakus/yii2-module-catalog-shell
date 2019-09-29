<?php
use bin\admin\widgets\CEngine\CEngine;

$this->title = Yii::t('easyii', 'Search engine');
?>
<?= $this->render('@bin/admin/views/category/_menu', ['category' => $model]) ?>
<div class="card">
	<?= $this->render('_submenu', ['model' => $model]) ?>
	<?= CEngine::widget(['model' => $model])?>
</div>