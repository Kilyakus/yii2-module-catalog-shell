<?php
namespace kilyakus\shell\directory\widgets\FieldFormatter;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use kilyakus\helper\media\Image;

class FieldContainer extends Widget
{
	public $field;
	public $content;
	public $label = true;
	public $image = true;

	public function run()
	{
		if($this->label != false)
		{
			$label = Html::tag('label', Yii::t('easyii', $this->field->title), [
				'for' => 'data-' . $this->field->name,
			]);
		}

		if($this->image != false)
		{
			$image = Html::img(
				Image::thumb($this->field->image, 42,42),[
					'class' => 'img-responsive btn btn-icon',
					'style' => 'border:0;'
				]
			);

			$image = Html::tag('label', $image, [
				'for' => 'data-' . $this->field->name,
				'class' => 'input-group-prepend form-control overflow-hidden p-0',
				'style' => 'max-width:45px;'
			]);

			$html = $image . $this->content;
		}else{
			$html = $this->content;
		}

		$html = Html::tag('div', $html, [
			'class' => 'form-group input-group',
			'data-toggle' => 'kt-tooltip',
			'data-skin' => 'dark',
			'data-placement' => 'bottom',
			'data-html' => 'true',
			'data-original-title' => $this->field->text,
		]);

		return $label . $html;
	}
}
