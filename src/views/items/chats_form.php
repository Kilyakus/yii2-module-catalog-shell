<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kilyakus\web\widgets as Widget;

$chatClass = $this->context->chatClass;
$chatClassAssign = $this->context->chatClassAssign;

$items = [];
$values = [];

if($ownerClass = $model->class)
{
	$allItems = $ownerClass::find()->all();
	foreach ($allItems as $item)
	{
	    $items[$item->primaryKey] = $item->title;
	}

	$currentItems = [];

	foreach ($chatClassAssign::find()->where(['chat_id' => $model->primaryKey])->all() as $chat)
	{
		$currentItems[] = $chat->item_id;
	}
}

$form = ActiveForm::begin(['id'=>'form-group-item'.$model->primaryKey]); ?>
    <?= $form->field($model, 'item_id[]')->widget(Widget\Select2::classname(), [
        'data' => $items,
        'theme' => 'default',
        'options' => [
            'id' => 'item-id'.$model->primaryKey,
            'value' => $currentItems,
            'multiple' => true,
        ],
        'pluginOptions' => [
        ],
    ])->label(false); ?>
    <?= $form->field($model, 'title')->textInput(); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']); ?>
<?php ActiveForm::end(); ?>