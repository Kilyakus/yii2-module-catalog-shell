<?php
namespace kilyakus\shell\directory\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class ParseBehavior extends Behavior
{
	public $attribute;

	public $categoryClass;

	public function events()
	{
		return [
			ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
		];
	}

	public function beforeSave($insert)
	{
		if(!$this->owner->{$this->attribute} || (!is_object($this->owner->{$this->attribute}) && !is_array($this->owner->{$this->attribute}))){
			$this->owner->{$this->attribute} = new \stdClass();
		}

		$this->owner->{$this->attribute} = json_encode($this->owner->{$this->attribute});
	}

	public function afterDelete()
	{
		$className = $this->categoryClass;

		$className::deleteAll(['item_id' => $this->owner->primaryKey]);
	}
}