<?php
namespace kilyakus\shell\directory\widgets\SearchFields;

use Yii;
use yii\web\JsExpression;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use bin\admin\models\CField;

class SearchFields extends Widget
{
	public $model;

	public function run()
	{
		parent::run();

		$filter = ['category_id' => $this->model->categoriesKeys, 'class' => $this->model->categoryClass, 'status' => 1];

		$fields = [];

		foreach (CField::find()->where(['and',$filter,['depth' => 0]])->orderBy(['order_num' => SORT_DESC])->all() as $field) {
			$fields[] = $field;
		}

		usort($fields, function($a, $b){
			return ($a['category_id'] - $b['category_id']);
		});

		foreach ($fields as $key => $field) {
			$fields[$key] = $field;
		}

		$data = new \stdClass();
		if(Yii::$app->request->get('Item')){
			foreach (Yii::$app->request->get('Item') as $key => $value)
			{
			    $data->$key = $value;
			}
		}

		return Html::tag('div', $this->render('dataForm', ['fields' => $fields, 'filter' => $filter, 'data' => $data]), ['class' => 'row']);
	}
}
