<?php
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

use kartik\daterange\DateRangePicker;

use kilyakus\web\widgets as Widget;

use kilyakus\widget\maps\GoogleMaps;
use bin\admin\components\API;
use kilyakus\imageprocessor\Image;
use bin\admin\helpers\IpHelper;
use bin\admin\widgets\TagsInput;
use kilyakus\package\seo\widgets\SeoForm;
use kilyakus\package\translate\widgets\TranslateForm;
use kilyakus\modules\models\Setting;

use kilyakus\shell\directory\assets\FieldsAsset;
use bin\admin\modules\geo\api\Geo;

// \app\assets\ScrollbarAsset::register($this);

$this->registerAssetBundle(FieldsAsset::className());

$settings = $this->context->module->settings;
$module = $this->context->module->id;
$class = API::getClass($class,'models','Item');

if(count($model->category->types)){
    $types = ArrayHelper::map($model->category->types,'type_id','title');
}
?>
<?php Pjax::begin(['enablePushState' => false,]); ?>
<?php $form = ActiveForm::begin([
    'options' => ['data-pjax' => true, 'data-pjax-problem' => true,'enctype' => 'multipart/form-data', 'class' => 'model-form'],
]); ?>
<?php if($settings['enableSubmodule'] && IS_ROOT && count($link['select'])) : ?>
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <?= $form->field($model, 'parent_class')->dropDownList($link['select'],['value' => ($model->parent_class ? $model->parent_class : $class)]); ?>
        </div>
        <div class="col-xs-12 col-md-6">
            <?= $form->field($model, 'parent_id')->widget(Widget\DepDrop::classname(), [
                'data' => (($parent_class = $model->parent_class) && $model->parent_id) ? [$model->parent_id => $parent_class::findOne($model->parent_id)->title] : ($parent ? [$parent => $class::findOne($parent)->title] : null),
                'type' => Widget\DepDrop::TYPE_SELECT2,
                'select2Options' => [
                    'pluginOptions' => ['value' => $parent, 'allowClear' => true,'multiple' => false,]
                ],
                'pluginOptions' => [
                    'depends' => ['item-parent_class'],
                    'url' => Url::to(['parent-item']),
                    'loadingText' => 'Loading ...',
                    'placeholder' => $link['select'][''],
                ]
            ]); ?>
        </div>
    </div>
<?php endif; ?>

<?php if(count($model->categories) || $settings['enableCategory']) : ?>
    <?=$form->field($model, 'category_id')->label(Yii::t('easyii','Categories'))->widget(Widget\Select2::classname(),
        [
            'data' => $model->categories,
            'options' => [
                'placeholder' => Yii::t('easyii','Change category'), 'value' => $assign ? $assign : $category->category_id, 'multiple' => $settings['categoryMultiple'],
                'options' => [
                    // $model->category_id => ['disabled' => true],
                ]
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>
<?php endif; ?>
<?= TranslateForm::widget(['form' => $form, 'model' => $model, 'attribute' => 'title']) ?>
<div class="row">
<?php $form->field($model, 'title', ['options' => ['class' => 'col-xs-12 ' . IS_MODER ? 'col-md-6' : 'col-md-12']]) ?>
    <?php if(IS_MODER) : ?>
        <?= $form->field($model, 'slug', ['options' => ['class' => 'col-xs-12 col-md-6']])->input('text',['placeholder' => 'Leave the field blank for automatic URL generation.']) ?>
    <?php endif; ?>
</div>

<?php if(count($model->category->types)) : ?>
    <?=$form->field($model, 'type_id')->label(Yii::t('easyii','Классификация'))->widget(Widget\Select2::classname(),
        [
            'data' => $types,
            'options' => [
                'value' => $model->type_id, 'multiple' => false,
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>
<?php endif; ?>

<?php if($settings['itemThumb']) : ?>
    <div class="row">
        <div class="col-xs-12 col-sm-3 col-md-five">
            <?= $form->field($model, 'image')->widget(Widget\Cutter::className(), []) ?>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-five">
            <?= $form->field($model, 'preview')->widget(Widget\Cutter::className(), []) ?>
        </div>
    </div>
<?php endif; ?>

<?php if($settings['enablePhotos']) : ?>
    <?= \bin\admin\widgets\ModulePhotos\ModulePhotos::widget(['model' => $model])?>
<?php endif; ?>

<?php if($settings['enableMaps']) : ?>
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <?= $form->field($model, 'city_id')->widget(Widget\Select2::classname(), [
                'data' => $model->city_id ? (Geo::city($model->city_id) ? ArrayHelper::map(Geo::city($model->city_id), 'id', 'name_ru') : null) : null,
                'options' => [
                    'id' => 'item-locality_id',
                    'value' => $model->city_id,
                    'placeholder' => Yii::t('easyii','Search for a city ...')
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/maps/city-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(city) { return city.text; }'),
                    'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                ],
            ])->label(Yii::t('easyii/' . $module,'City')); ?>
            <div class="row">
                <?= $form->field($model, 'latitude',['options' => ['class' => 'col-xs-12 col-md-6']])->input('text',['readonly' => true])->label(false) ?>
                <?= $form->field($model, 'longitude',['options' => ['class' => 'col-xs-12 col-md-6']])->input('text',['readonly' => true])->label(false) ?>
            </div>
        </div>

        <div class="col-xs-12 col-md-8">
            <?php $address = \app\controllers\MapsController::genAddress($model); ?>
            <?= GoogleMaps::widget([
                'geocode' => true,
                'userLocation' => [
                    'location' => [
                        'title' => $model->title,
                        'icon' => ('/bin/media/img/spotlight-' . $model->category->slug . '.png'),
                        'latitude' => $model->latitude,
                        'longitude' => $model->longitude,
                        'address' => $model->latitude . ', ' . $model->longitude,
                        // 'address' => $address,
                    ],
                    'htmlContent' => '<h4 class="m-0 mb-10">'.$model->title.'</h4><p class="text-muted">Адрес: '.$address.'</p><div class="h-align"><a href="'.Url::toRoute(['/catalog/view','slug' => $model->slug]).'" class="col-xs-12 btn btn-default btn-block text-center" target="_blank">Просмотреть карточку места</a></div>',
                ],
                'googleMapsOptions' => [
                    'zoom' => 9,
                    'center' => [
                        'lat' => (float)($model->latitude ? $model->latitude : IpHelper::getClient('geoplugin_latitude')),
                        'lng' => (float)($model->longitude ? $model->longitude : IpHelper::getClient('geoplugin_longitude'))
                    ],
                ],
                'wrapperHeight' => '250px',
                'wrapperClass' => 'img-rounded overflow-hide',
            ]); ?>
        </div>
    </div>
<?php endif; ?>

<?= TranslateForm::widget(['form' => $form, 'model' => $model, 'attribute' => 'description']) ?>

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
$model->time = date('Y-m-d h:i',($model->time ? $model->time : time()));
$model->time_to = date('Y-m-d h:i',($model->time_to ? $model->time_to : time()));
?>
<?= $form->field($model, 'time_to')->widget(DateRangePicker::classname(), [
    'useWithAddon'=>true,
    'convertFormat'=>true,
    'startAttribute' => 'time',
    'endAttribute' => 'time_to',
    'pluginOptions'=>[
        'timePicker'=>true,
        'timePickerIncrement'=>15,
        'showDropdowns'=>true,
        'locale'=>['format'=>'Y-m-d h:i']
    ],
    'presetDropdown'=>false,
    'hideInput'=>true
]); ?>

<?php if($model->category && count($contacts = $model->category->contacts)) : ?>
    <?php foreach ($contacts as $contact) : ?>
        <div class="form-group field-item-<?= $contact->name ?>">
            <label class="control-label" for="item-<?= $contact->name ?>"><?= $contact->title ?></label>
            <input type="text" id="contacts-<?= $contact->name ?>" class="form-control" name="Contacts[<?= $contact->name ?>]" value="<?= $model->contacts ? $model->contacts->{$contact->name} : '' ?>" placeholder="<?= $contact->text ?>">
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if($settings['enableTags']) : ?>
    <?= $form->field($model, 'tagNames')->widget(TagsInput::className()) ?>
<?php endif; ?>

<?php if($model->owner != Yii::$app->user->identity->id) : ?>
    <?= $form->field($model, 'owner',['template' => '{input}{label}'])->checkbox([
        'id' => 'item-owner', 
        'class' => 'switch',
        'checked' => ($model->owner == Yii::$app->user->identity->id ? true : false)
    ]); ?>
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