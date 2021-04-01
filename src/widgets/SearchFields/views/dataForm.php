<?php
use yii\helpers\Html;
use kilyakus\helper\media\Image;
use kilyakus\web\widgets as Widget;
?>

<?php if(count($fields)) : ?>
	<?php foreach ($fields as $key => $field) : ?>
		<?php if($field->children(1)->andFilterWhere($filter)->orderBy(['order_num' => SORT_DESC])->count()) : ?>


			<?= $this->render('dataForm',['fields' => $field->children(1)->andFilterWhere($filter)->orderBy(['lft' => $field->lft, 'rgt' => $field->rgt])->all(), 'data' => $data, 'filter' => $filter]) ?>


		<?php else: ?>

			<div class="col-xs-12 col-md-6 col-lg-4">

				<?= \kilyakus\shell\directory\widgets\FieldFormatter\FieldFormatter::widget(['field' => $field, 'data' => $data, 'modelName' => 'Item']); ?>

			</div>

		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
