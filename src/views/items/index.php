<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use bin\user\models\User;
use bin\admin\modules\catalog\models\Item;
use bin\admin\helpers\Image;
use bin\admin\modules\chat\models\Group;
use kilyakus\web\widgets as Widget;
use kilyakus\web\templates\UserCard\UserCard;
use yii\web\JsExpression;
use kilyakus\widget\grid\GridView;

$settings = $this->context->module->settings;
$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
if($module == 'app'){
	$module = 'admin';
}
$chatClass = $this->context->chatClass;
$this->title = Yii::t('easyii/' . $moduleName, ucfirst($moduleName)) . (!$model->title ?: ': ' . $model->title);

$gridColumns = [
	['class' => 'kilyakus\widget\grid\SerialColumn'],
	[
		'class' => 'kilyakus\widget\grid\ExpandRowColumn',
		'width' => '50px',
		'value' => function ($model, $key, $index, $column) {
			return GridView::ROW_COLLAPSED;
		},
		'detail' => function ($model, $key, $index, $column) {
			return $model->description;
		},
		'headerOptions' => ['class' => 'kartik-sheet-style'],
		'expandOneOnly' => true
	],
	[
		'headerOptions' => ['style' => 'width:60px;'],
		'format' => 'raw',
		'vAlign' => 'middle',
		'value' => function ($model) {
			return Html::img(Image::thumb($item->preview, 40, 40),['class' => 'img-circle']);
		},
	],
	[
		'class' => 'kilyakus\widget\grid\EditableColumn',
		'attribute' => 'title',
		'pageSummary' => 'Page Total',
		'vAlign' => 'middle',
		'headerOptions' => ['style' => 'width:40%;'],
		'contentOptions' => ['class'=>'kv-sticky-column'],
		'editableOptions'=>[
			'header' => Yii::t('easyii', 'Title'),
			'size' => 'md'
		],
		'format' => 'raw',
		'value' => function ($model) {
			return Html::tag('div', $model->translate->title . (($model->title != $model->translate->title) ? '<br>' . Yii::t('easyii', 'Original') . ': ' . $model->title : ''), ['class' => 'text-left']);
		},
	],
	[
		'headerOptions' => ['style' => 'width:200px;'],
		// 'attribute' => 'category_id',
		'header' => Yii::t('easyii', 'Categories'),
		'format' => 'raw',
		'vAlign' => 'middle',
		'value' => function ($model) {

			foreach ($model->_categories as $category) {
				$categories[] = [
					'label' => $category->title,
					'image' => Image::thumb($category->icon ? $category->icon : $category->image,30,30),
					'url' => Url::toRoute(['/' . $module . '/'.$moduleName.'/a/edit', 'id' => $category->primaryKey]),
				];
			}

			return Widget\Peafowl::widget([
				'items' => $categories
			]);
		},
	],
	[
		'headerOptions' => ['style' => 'width:160px;'],
		'attribute' => 'created_by',
		'format' => 'raw',
		'vAlign' => 'middle',
		'filterType' => GridView::FILTER_SELECT2,
		'filterWidgetOptions' => [
			'model'	 => $searchModel,
			'attribute' => 'created_by',
			'data'	  => ArrayHelper::map(\bin\user\models\User::find()->all(),'id','username'),
			'pluginOptions' => [
				'placeholder' => '',
				'multiple' => false,
				'allowClear' => true,
				'minimumInputLength' => 2,
				'ajax' => [
					'url' => Url::to(['/user/data/user-list']),
					'dataType' => 'json',
					'data' => new JsExpression('function(params) { return {q:params.term}; }')
				],
				'escapeMarkup' => new JsExpression('function (item) { return item; }'),
				'templateResult' => new JsExpression('function(item) { return item.text; }'),
				'templateSelection' => new JsExpression('function (item) { return item.text; }'),
			],
		],
		'value' => function ($model) {
			return UserCard::widget(['id' => $model->created_by, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $model->created_by])]);
		},
	],
	[
		'headerOptions' => ['style' => 'width:160px;'],
		'attribute' => 'updated_by',
		'format' => 'raw',
		'vAlign' => 'middle',
		'filterType' => GridView::FILTER_SELECT2,
		'filterWidgetOptions' => [
			'model'	 => $searchModel,
			'attribute' => 'created_by',
			'data'	  => ArrayHelper::map(\bin\user\models\User::find()->all(),'id','username'),
			'pluginOptions' => [
				'placeholder' => '',
				'multiple' => false,
				'allowClear' => true,
				'minimumInputLength' => 2,
				'ajax' => [
					'url' => Url::to(['/user/data/user-list']),
					'dataType' => 'json',
					'data' => new JsExpression('function(params) { return {q:params.term}; }')
				],
				'escapeMarkup' => new JsExpression('function (item) { return item; }'),
				'templateResult' => new JsExpression('function(item) { return item.text; }'),
				'templateSelection' => new JsExpression('function (item) { return item.text; }'),
			],
		],
		'value' => function ($model) {
			return UserCard::widget(['id' => $model->updated_by, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $model->updated_by])]);
		},
	],
	[
		'headerOptions' => ['style' => 'width:120px;'],
		// 'attribute' => 'views',
		'vAlign' => 'middle',
		'value' => function ($model) {
			return $model->views;
		},
	],
	[
		'class' => 'kilyakus\widget\grid\ActionColumn',
		'header' => '',
  		'template' => '{actions}',
		'buttons' => [
			'actions' => function ($url, $model) {
				$module = $this->context->module->module->id;
				$moduleName = $this->context->module->id;
				$chatClass = $this->context->chatClass;
				$chat = $chatClass::find()->where(['item_id' => $model['item_id']])->one();
				if(IS_ROOT) {
					return Widget\Dropdown::widget([
						'button' => [
							'icon' => 'fa fa-cog',
							'iconPosition' => Widget\Button::ICON_POSITION_LEFT,
							'type' => Widget\Button::TYPE_PRIMARY,
							// 'size' => Widget\Button::SIZE_SMALL,
							'disabled' => false,
							'block' => false,
							'outline' => true,
							'hover' => false,
							'circle' => true,
							'options' => ['title' => Yii::t('easyii', 'Actions')]
						],
						'options' => ['class' => 'dropdown-menu-right'],
						'items' => [
							[
								'label' => Yii::t('easyii', 'View'),
								'icon' => 'glyphicon glyphicon-eye-open',
								'url' => Url::to(['/'.$moduleName.'/view/', 'slug' => $model->slug]),
								'linkOptions' => ['target' => '_blank', 'data-pjax' => '0'],
							],
							[
								'divider' => true,
							],
							!$chat ? [
								'label' => Yii::t('easyii/chat', 'Создать чат'),
								'icon' => 'fa fa-comment',
								'url' => 'javascript://',
								'options' => ['data-toggle' => 'modal', 'data-target' => '#modal-form' . $model->primaryKey]
							] : [
								'label' => Yii::t('easyii/chat', 'Открыть чат'),
								'icon' => 'fa fa-external-link-alt',
								'url' => Url::to(['/' . $module . '/chat/message/groups/', 'id' => $chat->chat_id]),
								'linkOptions' => ['target' => '_blank', 'data-pjax' => '0'],
							],
							[
								'label' => Yii::t('easyii', 'Edit'),
								'icon' => 'fa fa-edit',
								'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/edit', 'id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
							],
							[
								'label' => Yii::t('easyii', 'Copy'),
								'icon' => 'glyphicon glyphicon-link',
								'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/copy', 'id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
							],
							[
								'divider' => true,
							],
							[
								'label' => Yii::t('easyii', 'Move up'),
								'icon' => 'fa fa-arrow-up',
								'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/up', 'id' => $model->primaryKey, 'category_id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
							],
							[
								'label' => Yii::t('easyii', 'Move down'),
								'icon' => 'fa fa-arrow-down',
								'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/down', 'id' => $model->primaryKey, 'category_id' => $model->primaryKey]),
								'linkOptions' => ['data-pjax' => '0'],
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
								'label' => Yii::t('easyii', 'Delete item'),
								'icon' => 'fa fa-times',
								'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/delete', 'id' => $model->primaryKey]),
								'linkOptions' => ['class' => 'confirm-delete', 'data-reload' => '0'],
							],
						],
					]);
				}
			}
		]
	],
];
?>

<?= $this->render('_menu', ['category' => $model,'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?= GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'columns' => $gridColumns,
	'rowOptions' => function($model) {
		$status = $model->status == Item::STATUS_ON ? '' : 'kt-bg-light-warning';
		return ['class' => $status];
	},
	// 'containerOptions' => ['style'=>'overflow: auto'], // only set when $responsive = false
	'toolbar' =>  [
		['content'=>
			Html::button('<i class="glyphicon glyphicon-plus"></i>', ['type'=>'button', 'title'=>Yii::t('kvgrid', 'Add Book'), 'class'=>'btn btn-success', 'onclick'=>'alert("This will launch the book creation form.\n\nDisabled for this demo!");']) . ' '.
			Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''], ['data-pjax'=>0, 'class' => 'btn btn-default', 'title'=>Yii::t('kvgrid', 'Reset Grid')])
		],
		'{export}',
		'{toggleData}'
	],
	'pjax' => true,
	'bordered' => false,
	'striped' => false,
	'condensed' => false,
	'responsive' => false,
	'hover' => true,
	'floatHeader' => true,
	'floatHeaderOptions' => ['top' => 60],
	'portlet' => [
		'title' => $this->title,
		'icon' => $this->context->module->icon,
		'bodyOptions' => [
			'class' => ($dataProvider->query->count() <= 0) ?: 'kt-portlet__body--fit',
		],
		// 'footerContent' => \yii\widgets\LinkPager::widget([
		//	 'pagination' => $data->pagination
		// ]),
		'pluginSupport' => false,
	],
]); ?>

<?php foreach($items as $item) : ?>
<?php
$itemClass = get_class($item);
$selectItems = ArrayHelper::map($itemClass::findAll(['item_id' => $item->primaryKey]), 'item_id', 'title');
$selectItems = ArrayHelper::merge($selectItems,ArrayHelper::map($itemClass::find()->where(['and',['!=', 'item_id', $item->primaryKey],['category_id' => array_keys($item->categories)]])->limit(50)->all(), 'item_id', 'title'));
foreach ($selectItems as $item_id => $selectItem) {
	if($chatClass::find()->where(['item_id' => $item_id])->one()){
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