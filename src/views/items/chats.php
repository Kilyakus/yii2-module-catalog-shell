<?php
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kilyakus\widget\grid\GridView;

use kilyakus\web\widgets as Widget;
use kilyakus\web\templates\UserCard\UserCard;

$this->title = $model->title . ': ' . Yii::t('easyii', 'Comments');

$gridColumns = [
	// ['class' => 'kilyakus\widget\grid\SerialColumn'],
	[
		'class' => 'kilyakus\widget\grid\ExpandRowColumn',
		'width' => '50px',
		'value' => function ($model, $key, $index, $column)
		{
			return GridView::ROW_COLLAPSED;
		},
		'detail' => function ($model, $key, $index, $column)
		{
			return $this->render('chats_form', ['model' => $model]);
		},
		'headerOptions' => ['class' => 'kartik-sheet-style'],
		'expandOneOnly' => false
	],
	[
		'attribute' => 'title',
		'vAlign' => 'middle',
		'headerOptions' => ['style' => 'width:70%;'],
		'contentOptions' => ['class'=>'kv-sticky-column'],
		'format' => 'raw',
		'value' => function ($model, $key, $index, $column)
		{
			return Html::tag('div', $model->title, ['class' => 'text-left']);
		},
	],
	[
		'headerOptions' => ['style' => 'width:240px;'],
		'attribute' => 'adminId',
		'format' => 'raw',
		'vAlign' => 'middle',
		'filterType' => GridView::FILTER_SELECT2,
		'filterWidgetOptions' => [
			'model'	 => $searchModel,
			'attribute' => 'adminId',
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
			return UserCard::widget(['id' => $model->adminId, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $model->adminId])]);
		},
	],
	[
		'class' => 'kilyakus\widget\grid\CheckboxColumn',
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'checkboxOptions' => function($model) {
            $options = ['value' => $model->id];
            // if($model->confirmed_at){
                // $options['disabled'] = 'true';
                // $options['class'] = 'hidden';
            // }
            return $options;
        },
	],
];
?>

<?= $this->render('_menu', ['category' => $model->category, 'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?= Html::beginForm(); ?>

	<?= GridView::widget([
		'dataProvider' => $model->allChats,
		'filterModel' => $searchModel,
		'columns' => $gridColumns,
		'rowOptions' => function ($model) {
			if($className = $model->class)
			{
				$parent = $className::findOne(Yii::$app->request->get('id'));
				$status = is_array($parent->chatsKeys) && in_array($model->primaryKey, $parent->chatsKeys) ? 'kt-bg-light-success' : '';
			}
			return ['class' => $status];
		},
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
		'portlet' => [
	        'options' => ['class' => 'kt-portlet--tabs', 'id' => 'kt_page_portlet'],
		    'headerContent' => $this->render('_submenu', ['model' => $model]),
		    'bodyOptions' => [
		    	'class' => 'kt-portlet__body--fit',
		    ]
	    ],
	]); ?>

	<?= Widget\Button::widget([
        'submit' => true,
        'type' => Widget\Button::TYPE_DANGER,
        'title' => 'Переназначить выбранные',
        'icon' => 'fas fa-toggle-off',
        'options' => [
            'name' => 'delete-button'
        ]
    ]) ?>

<?= Html::endForm(); ?>