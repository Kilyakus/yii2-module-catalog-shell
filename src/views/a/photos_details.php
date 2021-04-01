<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\StringHelper;
use kilyakus\widget\grid\GridView;
use kilyakus\helper\media\Image;
use kilyakus\widget\fancybox\Fancybox;
use kilyakus\web\widgets as Widget;
use yii\web\JsExpression;
use bin\admin\models\Photo;
use bin\admin\components\API;


$this->title = Yii::t('easyii', 'Photos');

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
			return $model->image;
		},
		'headerOptions'		=>	['class'	=>	'kartik-sheet-style'],
		'expandOneOnly'		=>	true
	],
	[
		'attribute'			=>	'image',
		'headerOptions'		=>	['style'	=>	'width:170px;'],
		'format'			=>	'raw',
		'value'				=>	function ($model)
		{
			Fancybox::widget([
				'selector'	=>	'.fancybox-' . $model->parent->primaryKey,
				'group'	=>	'photos' . $model->parent->primaryKey
			]);

			return Html::a(Html::img(Image::thumb($model->image, 150, 80), ['class'	=>	'img-rounded']), $model->image, ['class'	=>	'fancybox-' . $model->parent->primaryKey]);
		},
	],
	[
		'class'				=>	'kilyakus\widget\grid\EditableColumn',
		'attribute'			=>	'title',
		'pageSummary'		=>	'Page Total',
		'vAlign'			=>	'middle',
		'headerOptions'		=>	['style'	=>	'width:20%;'],
		'contentOptions'	=>	['class'=>'kv-sticky-column'],
		'editableOptions'	=>[
			'header'			=>	Yii::t('easyii', 'Title'),
			'size'				=>	'md'
		]
	],
	[
		'class'				=>	'kilyakus\widget\grid\EditableColumn',
		'attribute'			=>	'description',
		'pageSummary'		=>	'Page Total',
		'vAlign'			=>	'middle',
		'headerOptions'		=>	['style'	=>	'width:20%;'],
		'contentOptions'	=>	['class'=>'kv-sticky-column'],
		'editableOptions'	=>	[
			'header'			=>	Yii::t('easyii', 'Description'),
			'size'				=>	'md'
		],
		'value'				=>	function ($model)
		{
			return StringHelper::truncateWords($model->description, 10);
		},
	],
	[
		'attribute'			=>	'parent',
		'vAlign'			=>	'middle',
		'headerOptions'		=>	['style'	=>	'width:20%;'],
		'value'				=>	function ($model)
		{
			if(isset($model->parent->translate)){
				$title = $model->parent->translate->title;
			}elseif(isset($model->parent->title)){
				$title = $model->parent->title;
			}else{
				$title = $model->parent->primaryKey;
			}
			return $title;
		},
	],
	[
		// 'attribute'			=>	'status',
		'headerOptions'		=>	['style'	=>	'width:60px;'],
		'format'			=>	'raw',
		'vAlign'			=>	'middle',
		'value'				=>	function ($model)
		{
			return Html::checkbox('', $model->status == Photo::STATUS_ON, [
				'class'	=>	'switch',
				'data-id'	=>	$model->primaryKey,
				'data-link'	=>	Url::to(['/admin/photos/']),
				'data-reload'	=>	'1'
			]);
		},
	],
	[
		'class'				=>	'kilyakus\widget\grid\ActionColumn',
		'header'			=>	'',
		'template'			=>	'{actions}',
		'buttons'			=>	[
			'actions'			=>	function ($url, $model)
			{
				$module = $this->context->module->module->id;
				if(IS_MODER) {
					return Widget\Dropdown::widget([
						'button'	=>	[
							'icon'	=>	'fa fa-cog',
							'iconPosition'	=>	Widget\Button::ICON_POSITION_LEFT,
							'type'	=>	Widget\Button::TYPE_PRIMARY,
							'disabled'	=>	false,
							'block'	=>	false,
							'outline'	=>	true,
							'hover'	=>	true,
							'circle'	=>	true,
							'options'	=>	[
								'title'	=>	Yii::t('easyii', 'Actions'),
								'class'	=>	'btn-icon'
							]
						],
						'options'	=>	['class'	=>	'dropdown-menu-right'],
						'items'	=>	[
							[
								'label'	=>	Yii::t('easyii', 'Move up'),
								'icon'	=>	'fa fa-arrow-up',
								'url'	=>	Url::to(['/' . $module . '/photos/up', 'class'	=>	$model->class, 'item_id'	=>	$model->item_id, 'id'	=>	$model->primaryKey]),
								'linkOptions'	=>	[
									'data-pjax'	=>	'0',
								]
							],
							[
								'label'	=>	Yii::t('easyii', 'Move down'),
								'icon'	=>	'fa fa-arrow-down',
								'url'	=>	Url::to(['/' . $module . '/photos/down', 'class'	=>	$model->class, 'item_id'	=>	$model->item_id, 'id'	=>	$model->primaryKey]),
								'linkOptions'	=>	[
									'data-pjax'	=>	'0',
								]
							],
							[
								'divider'	=>	true,
							],
							[
								'label'	=>	Yii::t('easyii', 'Delete item'),
								'icon'	=>	'fa fa-times',
								'url'	=>	Url::to(['/' . $module . '/photos/delete', 'id'	=>	$model->primaryKey]),
								'linkOptions'	=>	[
									'class'	=>	'confirm-delete',
									'data-reload'	=>	'0',
									'data-pjax'	=>	'0',
								],
							],
						],
					]);
				}
			}
		]
	],
];

Pjax::begin([]);

echo \bin\admin\widgets\ModulePhotos\ModulePhotos::widget(['model' => $model]);

Pjax::end(); ?>