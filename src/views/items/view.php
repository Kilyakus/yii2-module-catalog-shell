<?php
use kilyakus\web\widgets as Widget;

$this->title = Yii::t('easyii', 'Edit'). ' ' .$model->title;

$photos = [];
foreach ($model->photos as $photo) {
	$photos[$photo->image] = [
		'src' => $photo->image,
		'thumb' => $photo->image,
		'title' => $photo->title,
		'description' => $photo->description,
	];
}
?>

<?= $this->render('_menu', ['category' => $model->category, 'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?php Widget\Portlet::begin([
	'options' => ['class' => 'kt-portlet--tabs', 'id' => 'kt_page_portlet'],
	// 'bodyOptions' => ['class' => 'kt-portlet__body--fit'],
	'headerContent' => $this->render('_submenu', compact('model','categories','assign','dataForm','maps'))
]); ?>
	<div class="row">
		<div class="col-xs-12">
			<h1><?= $model->title ?></h1>
		</div>
		<div class="col-xs-12 col-md-6">
			<table class="table table-striped">
				<tr>
					<td>
						<strong><?= Yii::t('easyii', 'Location') ?>:</strong>
					</td>
					<td>
						<?= $model->address ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?= Yii::t('easyii', 'Slug') ?>:</strong>
					</td>
					<td>
						<?= $model->slug ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?= Yii::t('easyii', 'Categories') ?>:</strong>
					</td>
					<td>
						<?= $model->categoriesNames ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="col-xs-12 col-md-6">
			<?= \app\widgets\Slider\Slider::widget(['items' => $photos]); ?>
		</div>

	</div>

	<table class="table table-grid">
		<tr>
			<td width="10%">
				<strong>Описание:</strong>
			</td>
			<td width="90%" colspan="3">
				<div class="overflow-auto pr-2" style="max-height:200px;">
					<?= $model->description ?>
				</div>
			</td>
		</tr>
	</table>
<?php Widget\Portlet::end(); ?>
