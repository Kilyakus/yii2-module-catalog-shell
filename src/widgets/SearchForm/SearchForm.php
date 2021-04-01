<?php
namespace kilyakus\shell\directory\widgets\SearchForm;

use Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

class SearchForm extends Widget
{
	public $model;
	public $actionSrc;

	public function run()
	{
		parent::run();

		$form = ActiveForm::begin([
			'method'			=> 'get',
			'action'			=> Url::to([
				$this->actionSrc . '/items/index',
				'class' => Yii::$app->request->get('class'),
				'parent' => Yii::$app->request->get('parent')
			]),
			'options'			=> [
				'data-pjax'			=> true,
				'data-pjax-problem'	=> true,
				'enctype'			=> 'multipart/form-data',
			],
			'enableClientValidation'	=> false
		]);

		ActiveForm::end();
	}

	public function getAdvancedSearch()
	{
		
	}
}
