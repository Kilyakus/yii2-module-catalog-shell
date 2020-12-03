<?php
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

use kilyakus\web\widgets as Widget;

use bin\admin\helpers\IpHelper;

use kilyakus\widget\maps\GoogleMaps;
use kilyakus\helper\media\Image;
use kilyakus\package\seo\widgets\SeoForm;
use kilyakus\package\translate\widgets\TranslateForm;
use kilyakus\package\taggable\widgets\TagsInput;
use kilyakus\modules\models\Setting;
use kilyakus\widget\daterange\DateRangePicker;

use kilyakus\shell\directory\assets\FieldsAsset;
use bin\admin\modules\geo\api\Geo;


$this->registerAssetBundle(FieldsAsset::className());

$settings = $this->context->module->settings;
$module = $this->context->module->id;

if(count($model->category->types)){
	$types = ArrayHelper::map($model->category->types,'type_id','title');
}
// $model->parent_class = ($model->parent_class ? $model->parent_class : $class);

$submoduleClass = $settings['submoduleClass']; ?>

<?php Pjax::begin(['enablePushState' => false,]); ?>
<?php $form = ActiveForm::begin([
	'options' => ['data-pjax' => true, 'data-pjax-problem' => true, 'enctype' => 'multipart/form-data', 'class' => 'model-form'],
]); ?>

<?php if($submoduleClass && IS_MODER) : ?>

	<?php if($cats = $submoduleClass::find()->all()){
		$dats = [];
		foreach ($cats as $key => $cat) {
			$dats[$cat->primaryKey] = $cat->title;
		}
	} ?>

	<?=	$form->field($model, 'parent_class', ['options' => ['class' => 'hidden']])
		->hiddenInput(['value' => $settings['submoduleClass']])
		->label(false); ?>

	<?=	$form->field($model, 'parent_id')
		->widget(Widget\Select2::classname(), [
		'data'			=> $dats,
		'pluginOptions'	=> [
			'placeholder'	=> Yii::t('easyii', 'Select'),
			'allowClear'	=> true,
		]
	]); ?>
	<!-- <div class="row">
		<div class="col-xs-12 col-md-6">
			<?php
			//  $form->field($model, 'parent_class')->widget(Widget\Select2::classname(), [
			//	 'data' => $model->submodules,
			//	 'pluginOptions' => [
			//		 'placeholder' => Yii::t('easyii', 'Select'),
			//		 'allowClear' => true,
			//	 ]
			// ]);
			?>
		</div>
		<div class="col-xs-12 col-md-6">
			<?php 
			// $form->field($model, 'parent_id')->widget(Widget\DepDrop::classname(), [
			//	 'data' => (($parent_class = $model->parent_class) && $model->parent_id) ? [$model->parent_id => $parent_class::findOne($model->parent_id)->title] : ($parent ? [$parent => $class::findOne($parent)->title] : null),
			//	 'type' => Widget\DepDrop::TYPE_SELECT2,
			//	 'select2Options' => [
			//		 'pluginOptions' => ['value' => $model->parent_id ? $model->parent_id : $parent, 'allowClear' => true,'multiple' => false,]
			//	 ],
			//	 'pluginOptions' => [
			//		 'depends' => ['item-parent_class'],
			//		 'url' => Url::to(['parent-item']),
			//		 'loadingText' => 'Loading ...',
			//		 'placeholder' => $link['select'][''],
			//	 ]
			// ]); 
			?>
		</div>
	</div> -->
<?php endif; ?>

<?php if(count($model->categories) || $settings['enableCategory']) : ?>
	<?= $form->field($model, 'category_id')
		->label(Yii::t('easyii','Categories'))
		->widget(
			Widget\Select2::classname(),
			[
				'data'				=> $model->allCategoriesTree,
				'options'			=> [
					'placeholder'		=> Yii::t('easyii','Change category'),
					'multiple'			=> $settings['categoryMultiple'],
					'value'				=> !empty($assign) ? (count($assign) > 1 ? $assign : (!is_array($assign) ? $assign : $assign[0])) : ($category->category_id ? $category->category_id : Yii::$app->request->get('id')),
				],
				'pluginOptions' => [
					'allowClear' => true,
				],
			]
		);
	?>
<?php endif; ?>

<?= $form->field($model, 'title')
	->label(false)
	->widget(TranslateForm::classname(), []); ?>

<?php if(IS_MODER) : ?>
	<?= $form->field($model, 'slug')
		->input('text',['placeholder' => 'Leave the field blank for automatic URL generation.']); ?>
<?php endif; ?>

<?php if(count($model->category->types)) : ?>
	<?=	$form->field($model, 'type_id')
		->label(Yii::t('easyii','Классификация'))
		->widget(
			Widget\Select2::classname(),
			[
				'data'			=> $types,
				'options'		=> [
					'value'			=> $model->type_id, 'multiple' => false,
				],
				'pluginOptions'	=> [
					'allowClear'	=> true,
				],
			]
		);
	?>
<?php endif; ?>

<?php if($settings['enablePhotos']) : ?>
	<?= \bin\admin\widgets\ModulePhotos\ModulePhotos::widget(['model' => $model])?>
<?php endif; ?>

<?php if($settings['enableMaps']) : ?>
	<div class="row">
		<div class="col-xs-12 col-md-4">
			<?php 
			// echo $form->field($model, 'region_id')->widget(Widget\Select2::classname(), [
			//	 'data' => $model->region_id ? (Geo::region($model->region_id) ? ArrayHelper::map(Geo::region($model->region_id), 'id', 'name_ru') : null) : null,
			//	 'options' => [
			//		 'id' => 'item-region_id',
			//		 'value' => $model->region_id,
			//		 'placeholder' => Yii::t('easyii','Search for a region ...')
			//	 ],
			//	 'pluginOptions' => [
			//		 'allowClear' => false,
			//		 'minimumInputLength' => 3,
			//		 'ajax' => [
			//			 'url' => Url::to(['/maps/region-list']),
			//			 'dataType' => 'json',
			//			 'data' => new JsExpression('function(params) { return {q:params.term}; }')
			//		 ],
			//		 'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			//		 'templateResult' => new JsExpression('function(region) { return region.text; }'),
			//		 'templateSelection' => new JsExpression('function (region) { return region.text; }'),
			//	 ],
			// ])->label(Yii::t('easyii/' . $module, 'Region'));
			?>
			<?=	$form->field($model, 'city_id')
				->widget(Widget\Select2::classname(), [
				'data'					=> $model->city_id ? (Geo::city($model->city_id) ? ArrayHelper::map(Geo::city($model->city_id), 'id', 'name_ru') : null) : null,
				'options'				=> [
					'id'					=> 'item-locality_id',
					'value'					=> $model->city_id,
					'placeholder'			=> Yii::t('easyii','Search for a city ...')
				],
				'pluginOptions'			=> [
					'allowClear'			=> false,
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
			->label(Yii::t('easyii/' . $module, 'City')); ?>
			<div class="row">
				<?=	$form->field($model, 'latitude',['options' => ['class' => 'col-xs-12 col-md-6']])
					->input('text',['readonly' => true])
					->label(false); ?>
				<?=	$form->field($model, 'longitude',['options' => ['class' => 'col-xs-12 col-md-6']])
					->input('text',['readonly' => true])
					->label(false); ?>
			</div>
		</div>

		<div class="col-xs-12 col-md-8">
			<?php
			$htmlContent = Html::tag('h4',$model->title);
			$htmlContent .= Html::img(Image::thumb($model->image, 340,120),['class' => 'w-100 img-rounded mb-2']);
			$htmlContent .= Widget\Button::widget([
				'type' => Widget\Button::TYPE_SECONDARY,
				'title' => Yii::t('easyii', 'Open in new tab'),
				'icon' => 'fa fa-external-link-alt',
				'url' => Url::toRoute(['/catalog/view','slug' => $model->slug]),
				'block' => true,
				'options' => [
					'data-pjax' => 0,
					'target' => '_blank'
				]
			]);
			?>
			<?= GoogleMaps::widget([
				'geocode'			=> true,
				'userLocation'		=> [
					'location'			=> [
						'title'				=> $model->title,
						'icon'				=> ('/bin/media/img/spotlight-' . $model->category->slug . '.png'),
						'latitude'			=> $model->latitude,
						'longitude'			=> $model->longitude,
						'address'			=> $model->latitude . ', ' . $model->longitude,
					],
					'htmlContent'		=> $htmlContent,
				],
				'googleMapsOptions'	=> [
					'zoom'				=> 9,
					'center'			=> [
						'lat'				=> (float)($model->latitude ? $model->latitude : IpHelper::getClient('geoplugin_latitude')),
						'lng'				=> (float)($model->longitude ? $model->longitude : IpHelper::getClient('geoplugin_longitude'))
					],
				],
				'wrapperHeight'		=> '250px',
				'wrapperClass'		=> 'img-rounded overflow-hide',
			]); ?>
		</div>
	</div>
<?php endif; ?>

<?=	$form->field($model, 'description')
	->widget(TranslateForm::classname(), [])
	->label(false); ?>

<?php if(count($dataForm) == 1) : ?>
	<div class="row" style="margin:0;">
		<?= $dataForm; ?>
	</div>
<?php endif; ?>

<?php if($settings['itemSale']) : ?>
	<?= $form->field($model, 'available') ?>
	<?= $form->field($model, 'price') ?>
	<?= $form->field($model, 'discount') ?>
<?php endif; ?>


<?php 
$model->time = date(Yii::$app->formatter->datetimeFormat, ($model->time ? $model->time : time()));
$model->time_to = date(Yii::$app->formatter->datetimeFormat, ($model->time_to ? $model->time_to : time()));
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
			'cancelLabel'			=> Yii::t('easyii', 'Clear'),
		]
	],
	'options'				=> [
		'value'					=> $model->time . ' - ' . $model->time_to,
	],
	'presetDropdown'		=> false,
	'hideInput'				=> true
]); ?>

<?php if(!empty($model->categories)) {
	foreach ($model->categories as $key => $category) {
		$contacts = $category->contacts;
		foreach ($contacts as $contact) : ?>
			<div class="form-group field-item-<?= $contact->name ?>">
				<label class="control-label" for="item-<?= $contact->name ?>"><?= $contact->title ?></label>
				<input type="text" id="contacts-<?= $contact->name ?>" class="form-control" name="Contacts[<?= $contact->name ?>]" value="<?= $model->contacts ? $model->contacts->{$contact->name} : '' ?>" placeholder="<?= $contact->text ?>">
			</div>
		<?php endforeach;
	}
} ?>

<?php if($settings['enableTags']) : ?>
	<?= $form->field($model, 'tagNames')
	->widget(TagsInput::className()); ?>
<?php endif; ?>

<?php if(!IS_MODER) : ?>
	<?=	$form->field($model, 'owner',['template' => '{input}{label}'])
		->checkbox([
			'id' => 'item-owner', 
			'class' => 'switch',
			'checked' => ($model->owner == Yii::$app->user->identity->id ? true : false)
		]); ?>
<?php else : ?>
	<?= $form->field($model, 'owner')->widget(Widget\Select2::className(), [
		'data'					=> ArrayHelper::map(\bin\user\models\User::find()->all(), 'id', 'name'),
		'options'				=> [
			'placeholder'			=> Yii::t('easyii/' . $module, 'Choose the owner from the users list or skip this field'),
		],
		'pluginOptions'			=> [
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
	])->label('Владелец'); ?>
<?php endif; ?>

<?php if(IS_ROOT) : ?>
<?php if($settings['enablePermissions']) : ?>
<?php $formatJs = <<< 'JS'
var formatRepo = function (repo) {
	if (repo.loading) {return repo.text;}
	var markup =
'<div class="row">' + 
	'<div class="col-sm-9 col-md-10"> ' + repo.text + '</div>' +
	'<div class="col-sm-3 col-md-2 text-right">' + repo.id + '</div>' +
'</div>';
	if (repo.description) {
	  markup += '<p>' + repo.description + '</p>';
	}
	return '<div style="overflow:hidden;">' + markup + '</div>';
};
var formatRepoSelection = function (repo) {return repo.id || repo.text;}
JS;

$this->registerJs($formatJs, \yii\web\view::POS_HEAD);
?>
	<?php $permissions=['' => Yii::t('easyii/infrastructure','Select')];foreach (Yii::$app->authManager->getTariffs() as $permission => $data) {$permissions[$permission] = $data->description;} ?>
	<?=$form->field($model, 'permission')->widget(Widget\Select2::classname(),
	[
		'data' => $permissions,
		'options' => [
			'multiple' => false,
			'options' => [
				// $model->category_id => ['disabled' => true],
			]
		],
		'pluginOptions' => [
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('formatRepo'),
			'templateSelection' => new JsExpression('formatRepoSelection'),
		],
	])->label(Yii::t('user','Permissions')); ?>
<?php endif; ?>
<?php endif; ?>

<?php if(IS_MODER) : ?>
	<?= SeoForm::widget(['model' => $model]) ?>
<?php endif; ?>

<?= Widget\Button::widget([
	'title' => Yii::t('easyii', 'Save'),
	'icon' => 'fa fa-check',
	'iconPosition' => Widget\Button::ICON_POSITION_LEFT,
	'type' => Widget\Button::TYPE_SUCCESS,
	'disabled' => false,
	'block' => true,
	'options' => ['type' => 'submit'],
]) ?>

<?php ActiveForm::end(); ?>
<?php $js = <<< JS
$('.file-drop-zone *').on('click',function(event){
	event.preventDefault();
	var stop = $($(this).parents('button'));
	if(!(stop.attr('type') || $(this).attr('type')) && !(stop.attr('type') != button || $(this).attr('type') != button)){
		var button = $($(this).parents('.file-input')).find('input[type="file"]');
		$(button).click();
	}else if(stop.attr('type') != button){
		stop.click();
	}
	event.stopPropagation();
})
JS;
$this->registerJs($js, yii\web\View::POS_READY); ?>
<?php Pjax::end(); ?>