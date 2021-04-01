<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;

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
?>

	<div class="row">

		<div class="col-md-3">


			<?php
			// $model->time = date(Yii::$app->formatter->datetimeFormat, $model->minTime);
			$model->time = $model->time ? $model->time : $model->minTime;
			// $model->time_to = date(Yii::$app->formatter->datetimeFormat, $model->maxTime);
			$model->time_to = $model->time_to ? $model->time_to : $model->maxTime;
			?>

			<?=	$form->field($model, 'date_range')
					->widget(DateRangePicker::classname(), [
					'useWithAddon'			=> true,
					'convertFormat'			=> true,
					'startAttribute'		=> 'time',
					'endAttribute'			=> 'time_to',
					'pluginOptions'			=> [
						'timePicker'			=> true,
						'timePickerIncrement'	=> 15,
						'showDropdowns'			=> true,
						'locale'				=> [
							'format'				=> Yii::$app->formatter->datetimeFormat,
							// 'cancelLabel'			=> Yii::t('easyii', 'Clear'),
						],
					],
					'options'				=> [
						'value'					=> $model->time . ' - ' . $model->time_to,
					],
					'presetDropdown'		=> true,
					'hideInput'				=> true,
				]); ?>

			<?php if($settings['itemSale']) : ?>

				<?= $form->field($model, 'price')
					->widget(Range::classname(), [
						'min' => 'priceFrom',
						'max' => 'priceTo',
						'range' => [$model->minPrice, $model->maxPrice],
						'value' => [$get['priceFrom'], $get['priceTo']],
						'step' => 1000
					]); ?>

			<?php endif; ?>

		</div>

		<div class="col-md-3">
			<?= $form->field($model, 'country_id')
				->widget(Widget\Select2::classname(), [
					'data'					=> !empty($model->country_id) ? ArrayHelper::map(MapsCountry::find()->where(['id' => $model->country_id])->all(), 'id', 'name_ru') : null,
					'options'				=> [
						'placeholder'			=> Yii::t('easyii', 'Select') . ' ...',
						'multiple'				=> true,
					],
					'pluginOptions'			=> [
						'allowClear'			=> true,
						'minimumInputLength'	=> 3,
						'ajax'				=> [
							'url'				=> Url::to(['/maps/country-list']),
							'dataType'			=> 'json',
							'data'				=> new JsExpression('function(params) { return {q:params.term}; }')
						],
						'escapeMarkup'		=> new JsExpression('function (markup) { return markup; }'),
						'templateResult'	=> new JsExpression('function(country) { return country.text; }'),
						'templateSelection'	=> new JsExpression('function (country) { return country.text; }'),
					],
				])
				->label(Yii::t('easyii', 'Country')); ?>

			<?= $form->field($model, 'region_id')
				->widget(Widget\Select2::classname(), [
					'data'					=> !empty($model->region_id) ? ArrayHelper::map(MapsRegion::find()->where(['id' => $model->region_id])->all(), 'id', 'name_ru') : null,
					'options'				=> [
						'placeholder'			=> Yii::t('easyii', 'Select') . ' ...',
						'multiple'				=> true,
					],
					'pluginOptions'			=> [
						'allowClear'			=> true,
						'minimumInputLength'	=> 3,
						'ajax'				=> [
							'url'				=> Url::to(['/maps/region-list']),
							'dataType'			=> 'json',
							'data'				=> new JsExpression('function(params) { return {q:params.term}; }')
						],
						'escapeMarkup'		=> new JsExpression('function (markup) { return markup; }'),
						'templateResult'	=> new JsExpression('function(region) { return region.text; }'),
						'templateSelection'	=> new JsExpression('function (region) { return region.text; }'),
					]
				])
				->label(Yii::t('easyii', 'Region')); ?>

			<?= $form->field($model, 'city_id')
				->widget(Widget\Select2::classname(), [
					'data'					=> !empty($model->city_id) ? ArrayHelper::map(MapsCity::find()->where(['id' => $model->city_id])->all(), 'id', 'name_ru') : null,
					'options'				=> [
						'placeholder'			=> Yii::t('easyii', 'Select') . ' ...',
						'multiple'				=> true,
					],
					'pluginOptions'			=> [
						'allowClear'			=> true,
						'minimumInputLength'	=> 3,
						'ajax'				=> [
							'url'				=> Url::to(['/maps/city-list']),
							'dataType'			=> 'json',
							'data'				=> new JsExpression('function(params) { return {q:params.term}; }')
						],
						'escapeMarkup'		=> new JsExpression('function (markup) { return markup; }'),
						'templateResult'	=> new JsExpression('function(city) { return city.text; }'),
						'templateSelection'	=> new JsExpression('function (city) { return city.text; }'),
					],
				])
				->label(Yii::t('easyii', 'City')); ?>

		</div>

		<div class="col-md-3">

			<?= $form->field($model, 'created_by')
				->widget(Widget\Select2::classname(), [
					'data'					=> ArrayHelper::map(\bin\user\models\User::find()->all(),'id','username'),
					'options'				=> [
						'value'					=> $get['created_by']
					],
					'pluginOptions'			=> [
						'placeholder'			=> '',
						'multiple'				=> false,
						'allowClear'			=> true,
						'minimumInputLength'	=> 2,
						'ajax'					=> [
							'url'					=> Url::to(['/user/data/user-list']),
							'dataType'				=> 'json',
							'data'					=> new JsExpression('function(params) { return {q:params.term}; }')
						],
						'escapeMarkup'		=> new JsExpression('function (item) { return item; }'),
						'templateResult'	=> new JsExpression('function(item) { return item.text; }'),
						'templateSelection'	=> new JsExpression('function (item) { return item.text; }'),
					],
				]); ?>

			<?= $form->field($model, 'updated_by')
				->widget(Widget\Select2::classname(), [
					'data'					=> ArrayHelper::map(\bin\user\models\User::find()->all(),'id','username'),
					'options'				=> [
						'value'					=> $get['updated_by']
					],
					'pluginOptions'			=> [
						'placeholder'			=> '',
						'multiple'				=> false,
						'allowClear'			=> true,
						'minimumInputLength'	=> 2,
						'ajax'					=> [
							'url'					=> Url::to(['/user/data/user-list']),
							'dataType'				=> 'json',
							'data'					=> new JsExpression('function(params) { return {q:params.term}; }')
						],
						'escapeMarkup'		=> new JsExpression('function (item) { return item; }'),
						'templateResult'	=> new JsExpression('function(item) { return item.text; }'),
						'templateSelection'	=> new JsExpression('function (item) { return item.text; }'),
					],
				]); ?>

			<?= $form->field($model, 'owner')
				->widget(Widget\Select2::classname(), [
					'data'					=> ArrayHelper::map(\bin\user\models\User::find()->all(),'id','username'),
					'options'				=> [
						'value'					=> $get['owner']
					],
					'pluginOptions'			=> [
						'placeholder'			=> '',
						'multiple'				=> false,
						'allowClear'			=> true,
						'minimumInputLength'	=> 2,
						'ajax'					=> [
							'url'					=> Url::to(['/user/data/user-list']),
							'dataType'				=> 'json',
							'data'					=> new JsExpression('function(params) { return {q:params.term}; }')
						],
						'escapeMarkup'		=> new JsExpression('function (item) { return item; }'),
						'templateResult'	=> new JsExpression('function(item) { return item.text; }'),
						'templateSelection'	=> new JsExpression('function (item) { return item.text; }'),
					],
				]); ?>

		</div>

		<div class="col-md-3">

			<div class="row">
				<?= $form->field($model, 'latitude', ['options' => ['class' => 'form-group col-6']])->textInput(['value' => $get['latitude']]); ?>
				<?= $form->field($model, 'longitude', ['options' => ['class' => 'form-group col-6']])->textInput(['value' => $get['longitude']]); ?>
			</div>

			<?= $form->field($model, 'radius'); ?>

		</div>

	</div>

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
