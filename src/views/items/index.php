<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use bin\user\models\User;
use bin\admin\modules\catalog\models\Item;
use bin\admin\modules\chat\models\Group;
use yii\web\JsExpression;

use kilyakus\web\widgets as Widget;
use kilyakus\web\templates\UserCard\UserCard;
use kilyakus\helper\media\Image;
use kilyakus\widget\grid\GridView;

$settings = $this->context->module->settings;
$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
if($module == 'app'){
	$module = 'admin';
}
$chatClassAssign = $this->context->chatClassAssign;

$this->render('@bin/admin/views/category/_breadcrumbs');

$gridColumns = [
	// ['class' => 'kilyakus\widget\grid\SerialColumn'],
	[
		'class'				=> 'kilyakus\widget\grid\ExpandRowColumn',
		'width'				=> '50px',
		'value'				=> function ($model, $key, $index, $column)
		{
			return GridView::ROW_COLLAPSED;
		},
		'detail'			=> function ($model, $key, $index, $column)
		{
			return '<table class="table">
				<tbody>
					<tr>
						<td>' . $model->description . Html::tag('div', 'Address: ' . $model->address) . '</td>
					</tr>
				</tbody>
			</table>';
		},
		'headerOptions'		=> ['class' => 'kartik-sheet-style'],
		'detailRowCssClass' => 'p-0',
		'expandOneOnly'		=> true
	],
	[
		'headerOptions'		=> ['style' => 'width:60px;min-width:60px;'],
		'format'			=> 'raw',
		'vAlign'			=> 'middle',
		'value'				=> function ($model)
		{
			return Html::img($model->getImage(40,40),['class' => 'img-circle']);
		},
	],
	[
		'class'				=> 'kilyakus\widget\grid\EditableColumn',
		'attribute'			=> 'title',
		'vAlign'			=> 'middle',
		'headerOptions'		=> ['style' => 'width:40%;'],
		'contentOptions'	=> ['class'=>'kv-sticky-column'],
		'editableOptions'	=> [
			'header'			=> Yii::t('easyii', 'Title'),
			'size'				=> 'md'
		],
		'format'			=> 'raw',
		'value'				=> function ($model)
		{
			return Html::tag('div', $model->translate->title . (($model->title != $model->translate->title) ? '<br>' . Yii::t('easyii', 'Original') . ': ' . $model->title : ''), ['class' => 'text-left']);
		},
	],
	[
		'headerOptions'		=> ['style' => 'width:200px;'],
		'attribute'			=> 'category_id',
		// 'header'			=> Yii::t('easyii', 'Categories'),
		'format'			=> 'raw',
		'vAlign'			=> 'middle',
		'value'				=> function ($model)
		{
			foreach ($model->categories as $category) {
				$categories[] = [
					'label'		=> $category->translate->title,
					'image'		=> Image::thumb($category->icon ? $category->icon : $category->image,30,30),
					'url'		=> Url::toRoute(['/' . $this->context->module->module->id . '/'. $this->context->module->id .'/a/edit', 'id' => $category->primaryKey]),
				];
			}

			return Widget\Peafowl::widget([
				'items' => $categories
			]);
		},
	],
	[
		'headerOptions'		=> ['style' => 'width:60px;min-width:60px;'],
		// 'attribute'			=> 'parent_id',
		'header'			=> Yii::t('easyii', 'Parent Record'),
		'format'			=> 'raw',
		'vAlign'			=> 'middle',
		'value'				=> function ($model)
		{
			if($model->parent){

				return Html::a(
					Html::img(Image::thumb($model->parent->image,40,40),['class' => 'img-circle', 'data-toggle' => 'kt-tooltip', 'data-skin' => 'dark', 'data-placement' => 'top', 'data-original-title' => $model->parent->translate->title]),
					Url::toRoute(['/' . $this->context->module->module->id . '/' . $model->parent->module->name . '/items/edit', 'id' => $model->parent->primaryKey]),
					[
						'target' => '_blank',
						'data-pjax' => '0'
					]
				);

			}else{
				return '';
			}
		},
		'visible'			=> $this->context->module->settings['parentSubmodule']
	],
	[
		'headerOptions'			=> ['style' => 'width:160px;'],
		'attribute'				=> 'created_by',
		'format'				=> 'raw',
		'vAlign'				=> 'middle',
		// 'filterType'			=> GridView::FILTER_SELECT2,
		// 'filterWidgetOptions'	=> [
		// 	'model'					=> $searchModel,
		// 	'attribute'				=> 'created_by',
		// 	'data'					=> ArrayHelper::map(User::find()->all(),'id','username'),
		// 	'pluginOptions'			=> [
		// 		'placeholder'			=> '',
		// 		'multiple'				=> false,
		// 		'allowClear'			=> true,
		// 		'minimumInputLength'	=> 2,
		// 		'ajax'					=> [
		// 			'url'					=> Url::to(['/user/data/user-list']),
		// 			'dataType'				=> 'json',
		// 			'data'					=> new JsExpression('function(params) { return {q:params.term}; }')
		// 		],
		// 		'escapeMarkup'			=> new JsExpression('function (item) { return item; }'),
		// 		'templateResult'		=> new JsExpression('function(item) { return item.text; }'),
		// 		'templateSelection'		=> new JsExpression('function (item) { return item.text; }'),
		// 	],
		// ],
		'value'					=> function ($model)
		{
			return UserCard::widget(['id' => $model->created_by, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $model->created_by])]);
		},
	],
	[
		'headerOptions'			=> ['style' => 'width:160px;'],
		'attribute'				=> 'updated_by',
		'format'				=> 'raw',
		'vAlign'				=> 'middle',
		// 'filterType'			=> GridView::FILTER_SELECT2,
		// 'filterWidgetOptions'	=> [
		// 	'model'					=> $searchModel,
		// 	'attribute'				=> 'created_by',
		// 	'data'					=> ArrayHelper::map(\bin\user\models\User::find()->all(),'id','username'),
		// 	'pluginOptions'			=> [
		// 		'placeholder'			=> '',
		// 		'multiple'				=> false,
		// 		'allowClear'			=> true,
		// 		'minimumInputLength'	=> 2,
		// 		'ajax'					=> [
		// 			'url'					=> Url::to(['/user/data/user-list']),
		// 			'dataType'				=> 'json',
		// 			'data'					=> new JsExpression('function(params) { return {q:params.term}; }')
		// 		],
		// 		'escapeMarkup'		=> new JsExpression('function (item) { return item; }'),
		// 		'templateResult'	=> new JsExpression('function(item) { return item.text; }'),
		// 		'templateSelection'	=> new JsExpression('function (item) { return item.text; }'),
		// 	],
		// ],
		'value'					=> function ($model)
		{
			return UserCard::widget(['id' => $model->updated_by, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $model->updated_by])]);
		},
	],
	// [
	// 	'headerOptions' => ['style' => 'width:120px;'],
	// 	// 'attribute' => 'views',
	// 	'vAlign' => 'middle',
	// 	'value' => function ($model) {
	// 		return $model->views;
	// 	},
	// ],
	[
		'class'					=> 'kilyakus\widget\grid\ActionColumn',
		'header'				=> '',
  		'template'				=> '{actions}',
		'buttons'				=> [
			'actions' => function ($url, $model) {
				$module = $this->context->module->module->id;
				$moduleName = $this->context->module->id;
				$chatClassAssign = $this->context->chatClassAssign;
				$chat = $chatClassAssign::find()->where(['item_id' => $model['item_id']])->one();
				if(IS_MODER) {
					return Widget\Dropdown::widget([
						'button'			=> [
							'icon'				=> 'fa fa-cog',
							'iconPosition'		=> Widget\Button::ICON_POSITION_LEFT,
							'type'				=> Widget\Button::TYPE_PRIMARY,
							// 'size' => Widget\Button::SIZE_SMALL,
							'disabled'			=> false,
							'block'				=> false,
							'outline'			=> true,
							'hover'				=> false,
							'circle'			=> true,
							'options'			=> ['title' => Yii::t('easyii', 'Actions')]
						],
						'options'			=> ['class' => 'dropdown-menu-right'],
						'items'				=> [
							[
								'label'			=> Yii::t('easyii', 'View'),
								'icon'			=> 'glyphicon glyphicon-eye-open',
								'url'			=> Url::to(['/' . $moduleName . '/view/', 'slug' => $model->slug]),
								'linkOptions'	=> ['target' => '_blank', 'data-pjax' => '0'],
							],
							[
								'divider'		=> true,
							],
							!$chat ? [
								'label'			=> Yii::t('easyii/chat', 'Создать чат'),
								'icon'			=> 'fa fa-comment',
								'url'			=> 'javascript://',
								'options'		=> ['data-toggle' => 'modal', 'data-target' => '#modal-form' . $model->primaryKey]
							] : [
								'label' => Yii::t('easyii/chat', 'Открыть чат'),
								'icon' => 'fa fa-external-link-alt',
								'url' => Url::to(['/' . $module . '/chat/message/groups/', 'id' => $chat->chat_id]),
								'linkOptions' => ['target' => '_blank', 'data-pjax' => '0'],
							],
							[
								'label' => Yii::t('easyii', 'Edit'),
								'icon' => 'fa fa-edit',
								'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/edit', 'id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
							],
							[
								'label' => Yii::t('easyii', 'Copy'),
								'icon' => 'glyphicon glyphicon-link',
								'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/copy', 'id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
								'visible' => IS_ADMIN,
							],
							[
								'divider' => true,
								'visible' => IS_ROOT,
							],
							[
								'label' => Yii::t('easyii', 'Move up'),
								'icon' => 'fa fa-arrow-up',
								'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/up', 'id' => $model->primaryKey, 'category_id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
								'visible' => IS_ROOT,
							],
							[
								'label' => Yii::t('easyii', 'Move down'),
								'icon' => 'fa fa-arrow-down',
								'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/down', 'id' => $model->primaryKey, 'category_id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
								'visible' => IS_ROOT,
							],
							[
								'divider' => true,
							],
							[
								'label' => $model->status == Item::STATUS_ON ? Yii::t('easyii', 'Turn Off') : Yii::t('easyii', 'Turn On'),
								'icon' => $model->status == Item::STATUS_ON ? 'fa fa-eye-slash' : 'fa fa-eye',
								'url' => $model->status == Item::STATUS_ON ?
									Url::to(['/' . $module . '/'.$moduleName.'/items/off', 'id' => $model->primaryKey]) :
									Url::to(['/' . $module . '/'.$moduleName.'/items/on', 'id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
							],
							[
								'label' => $model->status == !Item::STATUS_ARCHIVE ? Yii::t('easyii', 'Archive item') : Yii::t('easyii', 'Archive item'),
								'icon' => $model->status == !Item::STATUS_ARCHIVE ? 'fa fa-eye-slash' : 'fa fa-eye',
								'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/archive', 'id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
							],
							[
								'label' => Yii::t('easyii', 'Delete item'),
								'icon' => 'fa fa-times',
								'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/delete', 'id' => $model->primaryKey]),
								'linkOptions' => ['class' => 'confirm-delete', 'data-reload' => '0'],
							],
						],
					]);
				}
			}
		]
	],
];

$get = Yii::$app->request->get('Item');
?>

<?= $this->render('_menu', ['category' => $model,'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?= $this->render('_searchForm', ['model' => new $itemClass]); ?>

<?= GridView::widget([
	'dataProvider'			=> $dataProvider,
	// 'filterModel'			=> $searchModel,
	'columns'				=> $gridColumns,
	'rowOptions'			=> function($model) {
		$status = $model->status == Item::STATUS_ON ? '' : (Item::STATUS_OFF ? 'kt-bg-light-info' : 'kt-bg-light-warning');
		return ['class' => $status];
	},
	// 'containerOptions'		=> ['style' => 'overflow: auto'], // only set when $responsive = false
	'panelBeforeTemplate'	=> false,
	'pjax'					=> true,
	'bordered'				=> false,
	'striped'				=> false,
	'condensed'				=> false,
	'responsive'			=> false,
	'hover'					=> true,
	'floatHeader'			=> true,
	'floatHeaderOptions'	=> ['top' => 60],
	'portlet'				=> [
		'title'					=> Yii::t('easyii','Entries management'),
		'icon'					=> 'fas fa-edit',
		'bodyOptions'			=> [
			'class'					=> ($dataProvider->query->count() <= 0) ?: 'kt-portlet__body--fit',
		],
	],
	'pager'					=> [
        'options'				=> ['class' => 'pagination pull-right'],
        'firstPageLabel'		=> Html::tag('i', null, ['class' => 'fa fa-step-backward']),
        'lastPageLabel'			=> Html::tag('i', null, ['class' => 'fa fa-step-forward']),
        'maxButtonCount'		=> 4,
    ]
]); ?>

<?php foreach($dataProvider->getModels() as $item) : ?>
<?php
$itemClass = get_class($item);
$selectItems = ArrayHelper::map($itemClass::findAll(['item_id' => $item->primaryKey]), 'item_id', 'title');
$selectItems = ArrayHelper::merge($selectItems,ArrayHelper::map($itemClass::find()->where(['and',['!=', 'item_id', $item->primaryKey],['category_id' => array_keys($item->categories)]])->limit(50)->all(), 'item_id', 'title'));
foreach ($selectItems as $item_id => $selectItem) {
	if($chatClassAssign::find()->where(['item_id' => $item_id])->one()){
		unset($selectItems[$item_id]);
	}
}
?>
	<div class="modal fade" id="modal-form<?= $item->primaryKey ?>">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button class="close" type="button" data-dismiss="modal">
						<i class="fa fa-close"></i>
					</button>
					<h4 class="modal-title">Создание новой комнаты</h4>
				</div>
				<div class="modal-body">
					<?php $form = ActiveForm::begin(['id'=>'form-group-item']); ?>
						<?= $form->field($item, 'item_id[]')->widget(Widget\Select2::classname(), [
							'data' => $item->primaryKey ? $selectItems : null,
							'theme' => 'default',
							'options' => [
								'id' => 'item-id'.$item->primaryKey,
								'value' => $item->primaryKey,
								'multiple' => true,
							],
							'pluginOptions' => [
								// 'allowClear' => true,
								// 'minimumInputLength' => 1,
								// 'ajax' => [
								//	 'url' => \yii\helpers\Url::to(['items-list']),
								//	 'dataType' => 'json',
								//	 'data' => new JsExpression('function(params) { return {q:params.term}; }')
								// ],
								// 'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
								// 'templateResult' => new JsExpression('function(items) { return items.text; }'),
								// 'templateSelection' => new JsExpression('function (items) { return items.text; }'),
							],
						])->label(false); ?>
						<?= $form->field(new Group(), 'title')->textInput(['value' => $item->title]) ?>
						<?= $form->field(new Group(), 'description')->textarea() ?>
						<?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
					<?php ActiveForm::end(); ?>
				</div>
			</div>
		</div>
	</div>
<?php endforeach; ?>
