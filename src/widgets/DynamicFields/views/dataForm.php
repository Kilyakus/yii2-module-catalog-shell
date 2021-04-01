<?php
use yii\helpers\Html;
use kilyakus\helper\media\Image;
use kilyakus\web\widgets as Widget;

$this->registerCss("
.select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {width:100%!important;}
", ["type" => "text/css"], 'eto_kostil_dataform');
?>

<div class="row">
	<?php if(count($fields)) : ?>
		<?php foreach ($fields as $key => $field) : ?>
			<?php if($field->children(1)->andFilterWhere($filter)->orderBy(['order_num' => SORT_DESC])->count()) : ?>
				<?php if(!$field->parents(1)->one()) : ?>

					<?php Widget\Portlet::begin([
						'title' => Yii::t('easyii', $field->title),
						'options' => [
							'class' => 'kt-portlet--bordered w-100',
						],
					]); ?>

				<?php else: ?>

					<div class="col-xs-12">
						<?php Widget\Section::begin([
							'title' => Yii::t('easyii', $field->title) . ':',
							'separator' => [
								'class' => 'kt-separator kt-separator--border-dashed kt-separator--space-lg',
							],
						]); ?>

				<?php endif; ?>

					<!-- <div class="row"> -->
						<?= $this->render('dataForm',['fields' => $field->children(1)->andFilterWhere($filter)->orderBy(['lft' => $field->lft, 'rgt' => $field->rgt])->all(), 'data' => $data, 'filter' => $filter]) ?>
					<!-- </div> -->

				<?php if(!$field->parents(1)->one()) : ?>

					<?php Widget\Portlet::end(); ?>

				<?php else: ?>

						<?php Widget\Section::end(); ?>

					</div>

				<?php endif; ?>

			<?php else: ?>

				<?php if(!$field->parents(1)->one()) : ?>

					<!-- <div class="row"> -->

				<?php endif; ?>

				<div class="col-xs-12 col-md-6 col-lg-4">

					<?= \kilyakus\shell\directory\widgets\FieldFormatter\FieldFormatter::widget(['field' => $field, 'data' => $data]); ?>

				</div>

				<?php if(!$field->parents(1)->one()) : ?>

					<!-- </div> -->

				<?php endif; ?>

			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
