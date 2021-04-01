<?php
namespace kilyakus\shell\directory\widgets\FieldFormatter;

use Yii;
use yii\helpers\Html;
use kilyakus\web\widgets as Widget;

class FieldFormatter extends \yii\base\Widget
{
	public $field;
	public $data;
	public $modelName = 'Data';
	public $columns = 3;

	protected $dataPrefix = 'data-';

	protected $labelClass = 'form-control input-lg';

	public function run()
	{
		$data = $this->data;

		$value = !empty($data->{$this->field->name}) ? $data->{$this->field->name} : null;

		if ($this->field->type === 'string')
		{
			return FieldContainer::widget([
				'content'	=> $this->string($value),
				'field'		=> $this->field,
				'label'		=> true,
				'image'		=> $this->field->image,
			]);
		}
		elseif ($this->field->type === 'integer')
		{
			return FieldContainer::widget([
				'content'	=> $this->integer($value),
				'field'		=> $this->field,
				'label'		=> true,
				'image'		=> $this->field->image,
			]);
		}
		elseif ($this->field->type === 'text')
		{
			return FieldContainer::widget([
				'content'	=> $this->text($value),
				'field'		=> $this->field,
				'label'		=> true,
				'image'		=> $this->field->image,
			]);
		}
		elseif ($this->field->type === 'boolean')
		{
			return FieldContainer::widget([
				'content'	=> $this->boolean($value),
				'field'		=> $this->field,
				'label'		=> true,
				'image'		=> $this->field->image,
			]);
		}
		elseif ($this->field->type === 'select') {

			return FieldContainer::widget([
				'content'	=> $this->select($value),
				'field'		=> $this->field,
				'label'		=> true,
				'image'		=> $this->field->image,
			]);
		}
		elseif ($this->field->type === 'checkbox') {

			return FieldContainer::widget([
				'content'	=> $this->checkbox($value),
				'field'		=> $this->field,
				'label'		=> true,
				'image'		=> $this->field->image,
			]);

		}
		elseif ($this->field->type === 'radio')
		{
			if(!empty($this->field->optionsArray) && !empty($this->field->options))
			{
				foreach($this->field->optionsArray as $key => $option)
				{
					$checked = $value && (is_array($value) ? in_array($option, $value) : \yii\helpers\Inflector::slug($option) == $value);
					$options .= $this->labelRadio($checked, $option, Yii::t('easyii', $option));
				}

				return FieldContainer::widget([
					'content'	=> $options,
					'field'		=> $this->field,
					'label'		=> true,
					'image'		=> $this->field->image,
				]);

			}else{
				return FieldContainer::widget([
					'content'	=> $this->labelCheckbox($value, $value, Yii::t('easyii', 'Select')),
					'field'		=> $this->field,
					'label'		=> true,
					'image'		=> $this->field->image,
				]);
			}
		}
	}

	protected function string($value)
	{
		$settings = [
			'id'	=> $this->dataPrefix . $this->field->name,
			'class'	=> 'form-control'
		];

		if($this->field->required == true)
		{
			$settings = array_merge($settings,['required' => true]);
		}

		if($this->field->append == true)
		{
			$settings = array_merge($settings,['data-append' => true]);
		}

		return Html::input('text', $this->modelName . "[{$this->field->name}]", $value, $settings);
	}

	protected function integer($value)
	{
		$settings = [
			'id'	=> $this->dataPrefix . $this->field->name,
			'class'	=> $this->labelClass,
			'min'	=> $this->field->min,
			'max'	=> $this->field->max,
			'step'	=> ($this->field->step == 1 ? 'any' : $this->field->step)
		];

		if($this->field->required == true)
		{
			$settings = array_merge($settings,['required' => true]);
		}

		if($this->field->append == true)
		{
			$settings = array_merge($settings,['data-append' => 'true']);
		}

		return Html::input('number', $this->modelName . "[{$this->field->name}]", $value, $settings);
	}

	protected function text($value)
	{
		$settings = [
			'id'	=> $this->dataPrefix . $this->field->name,
			'class'	=> 'form-control'
		];
		if($this->field->required == true)
		{
			$settings = array_merge($settings,['required' => true]);
		}

		return Html::textarea($this->modelName . "[{$this->field->name}]", $value, $settings);
	}

	protected function boolean($value)
	{
		return $this->labelCheckbox($value, $value, Yii::t('easyii', 'Select'));
	}

	protected function checkbox($value)
	{
		if(!empty($this->field->optionsArray))
		{
			return Widget\Select2::widget([
				'name'			=> $this->modelName . "[{$this->field->name}]",
				'theme'			=> 'default',
				'data'			=> $this->field->optionsArray ?? [
					'on' => Yii::t('easyii', 'On'),
					'off' => Yii::t('easyii', 'Off'),
				],
				'value'			=> $value,
				'options'		=> [
					'placeholder'	=> Yii::t('easyii', 'Select'),
					'multiple'		=> $this->field->optionsArray ?? false
				],
				'pluginOptions'	=> [
					'class'			=> 'form-control',
					'closeOnSelect'	=> false,
					'allowClear'	=> true
				]
			]);
		}else{
			return $this->labelCheckbox($value, $value, Yii::t('easyii', 'Select'));
		}
	}

	protected function select($value)
	{
		return Widget\Select2::widget([
			'name'			=> $this->modelName . "[{$this->field->name}]",
			'theme'			=> 'default',
			'data'			=> $this->field->optionsArray ?? [
				'on' => Yii::t('easyii', 'On'),
				'off' => Yii::t('easyii', 'Off'),
			],
			'value'			=> $value,
			'options'		=> [
				'placeholder'	=> Yii::t('easyii', 'Select'),
				'multiple'		=> false
			],
			'pluginOptions'	=> [
				'class'			=> 'form-control',
				'closeOnSelect'	=> true,
				'allowClear'	=> true
			]
		]);
	}

	protected function labelCheckbox($checked, $value, $name)
	{
		$checkbox = Html::checkbox($this->modelName . "[{$this->field->name}][]", $checked, ['class' => 'switch','value' => $value,]) . Html::tag('span', $name);

		return Html::tag('label', $checkbox, ['class' => $this->labelClass]);
	}

	protected function labelRadio($checked, $value, $name)
	{
		$radio = Html::radio($this->modelName . "[{$this->field->name}][]", $checked, ['class' => 'switch','value' => $value,]) . Html::tag('span', $name);

		return Html::tag('label', $radio, ['class' => $this->labelClass]);
	}
}
