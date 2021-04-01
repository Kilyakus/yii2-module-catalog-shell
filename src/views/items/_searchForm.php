<?php
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

use kilyakus\web\widgets as Widget;
use kilyakus\widget\daterange\DateRangePicker;
use kilyakus\widget\range\Range;
use kilyakus\web\templates\UserCard\UserCard;

use bin\admin\modules\geo\api\Geo;
use bin\admin\modules\geo\models\MapsCountry;
use bin\admin\modules\geo\models\MapsRegion;
use bin\admin\modules\geo\models\MapsCity;

$settings = $this->context->module->settings;
$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
if($module == 'app'){
	$module = 'admin';
}

$model->load(Yii::$app->request->get());
// $get = Yii::$app->request->get('Item');
$model->setCategories($model->category_id ?? explode(',', Yii::$app->request->get('id')));
?>

<?php $form = ActiveForm::begin([
	'method'			=> 'get',
	'action'			=> Url::to([
		'/' . $module . '/' . $moduleName . '/items/index',
		'class' => Yii::$app->request->get('class'),
		'parent' => Yii::$app->request->get('parent')
	]),
	'options'			=> [
		'data-pjax'			=> true,
		'data-pjax-problem'	=> true,
		'enctype'			=> 'multipart/form-data',
	],
	'enableClientValidation'	=> false
]); ?>

	<?php Widget\Portlet::begin([
		'title' => $this->title . ': ' . Yii::t('easyii', 'Search'),
		'icon' => 'fa fa-sliders-h',
		'footerContent' => Widget\Button::widget([
			'type' => Widget\Button::TYPE_SUCCESS,
			'title' => Yii::t('easyii', 'Apply'),
			'icon' => 'fa fa-check',
			'block' => true,
			'submit' => true
		])
	]); ?>


	<?= $form->field($model, 'title')
		->textInput();
	?>

	<div class="row">
		<div class="col-xs-12 col-md-6">
			<?= $form->field($model, 'category_id')
				->label(Yii::t('easyii','Categories'))
				->widget(
					Widget\Select2::classname(),
					[
						'data'				=> $model->allCategoriesArray,
						'options'			=> [
							'placeholder'		=> Yii::t('easyii','Select category'),
							'multiple'			=> $settings['categoryMultiple'],
							'value'				=> $model->category_id ?? explode(',', Yii::$app->request->get('id')),
						],
						'pluginOptions' => [
							'allowClear' => true,
						],
					]
				);
			?>
		</div>
		<div class="col-xs-12 col-md-6">
			<?= $form->field($model, 'status')
				// ->label(Yii::t('easyii','Categories'))
				->widget(
					Widget\Select2::classname(),
					[
						'data'				=> $model->allStatusesArray,
						'options'			=> [
							'placeholder'		=> Yii::t('easyii','Select status'),
							'multiple'			=> true,
							'value'				=> $get['status'],
						],
						'pluginOptions' => [
							'allowClear' => true,
						],
					]
				);
			?>
		</div>
	</div>

	<?= \yii\bootstrap\Collapse::widget([
	    'items' => [
			[
	            'label' => Yii::t('easyii', 'Advanced Search'),
	            'content' => $this->render('_AdvancedSearch', ['model' => $model, 'form' => $form]),
	            'contentOptions' => ['class' => 'overflow-hidden']
	        ],
	        [
	            'label' => 'Поиск по дополнительным полям',
	            'content' => \kilyakus\shell\directory\widgets\SearchFields\SearchFields::widget(['model' => $model]),
	            'contentOptions' => ['class' => 'overflow-hidden']
	        ],
	    ]
	]); ?>

	<hr>

		<?php if($settings['parentSubmodule']) : ?>
			<fieldset>
				<legend>Поиск закрепленных мест</legend>

				<?= $form->field($model, 'parent_id', [
					'template' => "{input}{label}", 'options' => ['class' => 'form-group mb-1 mt-3'
				]])->checkbox(['checked' => $get['parent_id'] == 1 ? true : false, 'class' => 'switch ml-0'],false)->label('Отобразить только закрепленные места'); ?>

				<?= $form->field($model, 'nearby', [
					'template' => "{input}{label}", 'options' => ['class' => 'form-group mb-0'
				]])->checkbox(['checked' => $get['nearby'] == 1 ? true : false, 'class' => 'switch ml-0'],false)->label('Отобразить места поблизости'); ?>

			</fieldset>
		<?php endif; ?>

	<?php Widget\Portlet::end(); ?>

<?php ActiveForm::end(); ?>
