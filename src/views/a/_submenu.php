<?php
use yii\helpers\Url;
use kilyakus\web\widgets as Widget;

$action = $this->context->action->id;
$module = $this->context->module->id;
?>
<?php if(IS_ADMIN && $model->primaryKey) : ?>
	<?= Widget\Nav::widget([
		'options' => [
			'class' => 'nav-tabs nav-tabs-line nav-tabs-line-brand nav-tabs-line-2x',
		],
		'encodeLabels' => false,
		'items' => [
			[
				'label' => '<span class="fa fa-edit"></span>&nbsp; ' . Yii::t('easyii', 'Edit'),
				'url' => Url::to(['/admin/'.$module.'/a/edit', 'id' => $model->primaryKey]),
				'active' => ($action === 'edit'),
			],
			[
				'label' => '<span class="fa fa-cog"></span>&nbsp; ' . Yii::t('easyii', 'Additional fields'),
				'url' => Url::to(['/admin/'.$module.'/a/fields', 'id' => $model->primaryKey]),
				'active' => ($action === 'fields'),
			],
			[
				'label' => '<span class="fa fa-flag"></span>&nbsp; ' . Yii::t('easyii', 'Sections'),
				'url' => Url::to(['/admin/'.$module.'/a/types', 'id' => $model->primaryKey]),
				'active' => ($action === 'types'),
			],
			[
				'label' => '<span class="fa fa-phone"></span>&nbsp; ' . Yii::t('easyii', 'Contacts'),
				'url' => Url::to(['/admin/'.$module.'/a/contacts', 'id' => $model->primaryKey]),
				'active' => ($action === 'contacts'),
			],
			[
				'label' => '<span class="fa fa-comment"></span>&nbsp; ' . Yii::t('easyii', 'Forums'),
				'url' => Url::to(['/admin/'.$module.'/a/forums', 'id' => $model->primaryKey]),
				'active' => ($action === 'forums'),
				'visible' => Yii::$app->getModule('forum'),
			],
		],
	]) ?>
<?php endif;?>