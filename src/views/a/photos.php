<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\StringHelper;
use yii\widgets\ActiveForm;
use kilyakus\widget\grid\GridView;
use kilyakus\helper\media\Image;
use kilyakus\widget\fancybox\Fancybox;
use kilyakus\web\widgets as Widget;
use yii\web\JsExpression;
use bin\admin\models\Photo;
use bin\admin\components\API;

$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
$activeModule = Yii::$app->getModule('admin')->activeModules[$moduleName];

$this->render('@bin/admin/views/category/_breadcrumbs');

$gridColumns = [
	[
		'class'				=>	'kilyakus\widget\grid\SerialColumn'
	],
	[
		'class'				=>	'kilyakus\widget\grid\ExpandRowColumn',
		'width'				=>	'50px',
		'value'				=>	function ($model, $key, $index, $column)
		{
			return GridView::ROW_COLLAPSED;
		},
		'detail'			=>	function ($model, $key, $index, $column)
		{
			$searchModel  = \Yii::createObject(Photo::className());
            $dataProvider = $searchModel->search(\Yii::$app->request->get());
            $dataProvider->query
            ->andFilterWhere([
                'and',
                ['class'	=>	$model->class],
                ['item_id'	=>	$model->item_id],
                ['not', ['status'	=>	Photo::STATUS_UPLOADED]]
            ]);

			echo \pendalf89\filemanager\widgets\FileInput::widget([
			    'name' => 'mediafile',
			    'buttonTag' => 'button',
			    'buttonName' => 'Browse',
			    'buttonOptions' => ['class' => 'btn btn-default'],
			    'options' => ['class' => 'form-control'],
			    // Widget template
			    'template' => '<div class="input-group">{input}<span class="input-group-btn">{button}</span></div>',
			    // Optional, if set, only this image can be selected by user
			    'thumb' => 'original',
			    // Optional, if set, in container will be inserted selected image
			    'imageContainer' => '.img',
			    // Default to FileInput::DATA_IDL. This data will be inserted in input field
			    'pasteData' => FileInput::DATA_ID,
			    // JavaScript function, which will be called before insert file data to input.
			    // Argument data contains file data.
			    // data example: [alt: "Ведьма с кошкой", description: "123", url: "/uploads/2014/12/vedma-100x100.jpeg", id: "45"]
			    'callbackBeforeInsert' => 'function(e, data) {
			        console.log( data );
			    }',
			]);

			return $this->render('photos_details', ['dataProvider'	=>	$dataProvider, 'model' => $model->parent]);
		},
		'headerOptions'	=>	['class'	=>	'kartik-sheet-style'],
		'detailRowCssClass' => '',
		'expandOneOnly'	=>	true
	],
	[
		'vAlign'			=>	'middle',
		'headerOptions'		=>	['style'	=>	'width:80%;'],
		'format'			=>	'raw',
		'value'				=>	function ($model)
		{
			if(isset($model->parent->translate)){
				$title = $model->parent->translate->title;
			}elseif(isset($model->parent->title)){
				$title = $model->parent->title;
			}else{
				$title = $model->parent->primaryKey;
			}
			return Html::a($title, '/' . $this->context->module->module->id . '/' . $this->context->module->id . '/items/edit/' . $model->parent->primaryKey, ['target' => '_blank']);
		},
	],
	[
		'vAlign'			=>	'middle',
		// 'headerOptions'		=>	['style'	=>	'width:80%;'],
		'format'			=>	'raw',
		'value'				=>	function ($model)
		{
			$searchModel  = \Yii::createObject(Photo::className());
            $dataProvider = $searchModel->search(\Yii::$app->request->get());
            $dataProvider->query->andFilterWhere([
                'and',
                ['class'	=>	$model->class],
                ['item_id'	=>	$model->item_id],
                ['status'	=>	Photo::STATUS_OFF]
            ]);

			return Widget\Badge::widget([
				'label' => Yii::t('easyii', 'Ожидают проверки') . ': ' . $dataProvider->query->count(),
				'type' => $dataProvider->query->count() ? Widget\Badge::TYPE_DANGER : Widget\Badge::TYPE_INFO,

			]);
		},
	],
];

$form = ActiveForm::begin([
	'method' => 'get',
	'options' => [
		'data-pjax' => true, 'data-pjax-problem' => true,'enctype' => 'multipart/form-data', 'class' => 'model-form',
		'action' => Url::to(['/' . $this->context->module->module->id . '/' . $this->context->module->id . '/a/photos'])
	],
]);

$get = Yii::$app->request->get('Photo');

Widget\Portlet::begin([
	'title'					=>	Yii::t('easyii', 'Search'),
	'icon'					=>	'fa fa-search',
	'options'				=> [
		'class'					=> 'mt-5'
	],
	// 'bodyOptions'			=> [
	// 	'class'					=>	($dataProvider->query->count() <= 0) ?: 'kt-portlet__body--fit',
	// ],
	'footerContent' => Widget\Button::widget([
		'type' => Widget\Button::TYPE_SUCCESS,
		'title' => Yii::t('easyii', 'Search'),
		'icon' => 'fa fa-check',
		'block' => true,
		'submit' => true
	]),
	'pluginSupport'			=>	false,
]); ?>

	<?php $model = new Photo ?>

		<div class="row">
			<div class="col-sm-12 col-md-4">
				<?= $form->field($model, 'type')->widget(Widget\Select2::classname(), [
					'data' => [
						1 => Yii::t('easyii', 'Photo'),
						2 => Yii::t('easyii', 'Video'),
					],
					'options' => [
						'value' => $get['type'],
						'placeholder' => Yii::t('easyii','Search by type')
					],
				])->label(); ?>
			</div>
			<div class="col-sm-12 col-md-4">
				<?= $form->field($model, 'ownerName')->textInput(['value' => $get['ownerName']]); ?>
			</div>
		</div>

<?php

Widget\Portlet::end();

ActiveForm::end();


echo GridView::widget([
	'dataProvider'			=>	$dataProvider,
	'filterModel'			=>	$searchModel,
	'columns'				=>	$gridColumns,
	// 'containerOptions'		=>	['style'=>'overflow: auto'], // only set when $responsive = false
	'rowOptions'			=>	function($model)
	{
		return [
			'class'				=>	Photo::status($model)
		];
	},
	'panelBeforeTemplate'	=>	false,
	'pjax'					=>	false,
	'bordered'				=>	false,
	'striped'				=>	false,
	'condensed'				=>	false,
	'responsive'			=>	false,
	'hover'					=>	true,
	'floatHeader'			=>	true,
	'floatHeaderOptions'	=>	['top'	=>	60],
	'portlet'				=>	[
		'title'					=>	Yii::t('easyii','Media management'),
		'icon'					=>	'fas fa-images',
		'bodyOptions'			=> [
			'class'					=>	($dataProvider->query->count() <= 0) ?: 'kt-portlet__body--fit',
		],
		// 'footerContent'				=>	\yii\widgets\LinkPager::widget([
		// 	 'pagination'				=>	$data->pagination
		// ]),
		'pluginSupport'			=>	false,
	],
]); ?>
