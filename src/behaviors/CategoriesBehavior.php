<?php
namespace kilyakus\shell\directory\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class CategoriesBehavior extends Behavior
{
	public $classCategory;
	public $classCategoryAssign;

	public $_categories;
	public $_categoriesKeys;
	public $_allCategories;
	public $_allCategoriesKeys;

	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_INSERT	=> 'afterSave',
			ActiveRecord::EVENT_AFTER_UPDATE	=> 'afterSave',
			ActiveRecord::EVENT_AFTER_DELETE	=> 'afterDelete',
		];
	}

	public function getCategoriesAssigns()
	{
		$className = $this->classCategoryAssign;

		return $this->owner->hasMany($className::className(), ['item_id' => $this->owner->primaryKey()[0]]);
	}

	public function getCategories()
	{
		$className = $this->classCategory;

		return $this->owner->hasMany($className::className(), ['category_id' => 'category_id'])->via('categoriesAssigns');
	}

	public function getCategoriesNames()
	{
		return implode(', ', $this->getCategoriesArray());
	}

	public function getCategoriesArray()
	{
		if($this->_categories === null){
			$this->_categories = [];
			foreach($this->owner->categories as $category) {
				$this->_categories[$category->primaryKey] = $category->translate->title;
			}
		}
		return $this->_categories;
	}

	public function getCategoriesKeys()
	{
		if($this->_categoriesKeys === null){
			$this->_categoriesKeys = [];
			foreach($this->owner->categories as $category) {
				$this->_categoriesKeys[] = $category->primaryKey;
			}
		}
		return $this->_categoriesKeys;
	}

	public function setCategoriesNames($values)
	{
		$this->_categories = $this->filterCategoriesValues($values);
	}

	public function filterCategoriesValues($values)
	{
		return array_unique(preg_split(
			'/\s*,\s*/u',
			preg_replace('/\s+/u', ' ', is_array($values) ? implode(',', $values) : $values),
			-1,
			PREG_SPLIT_NO_EMPTY
		));
	}

	public function getAllCategories()
	{
		$className = $this->classCategory;

		return $className::find()->all();
	}

	public function getAllCategoriesArray()
	{
		if($this->_allCategories === null){
			$this->_allCategories = [];
			foreach($this->getAllCategories() as $category) {
				$this->_allCategories[$category->primaryKey] = $category->translate->title;
			}
		}
		return $this->_allCategories;
	}

	public function getAllCategoriesTree()
	{
		$Category = $this->classCategory;
		
		$categories = $key = $val = array();
		if(Yii::$app->controller->module->module->id != 'admin'){
			$status = false;
		}else{
			$status = true;
		}
		$trees = $Category::tree($status);
		$categories = $Category::checkCategories($trees);
		$categories = $categories ? $Category::filterCategories($categories) : null;

		return $categories;
	}

	public function getAllCategoriesKeys()
	{
		if($this->_allCategoriesKeys === null){
			$this->_allCategoriesKeys = [];
			foreach($this->getAllCategories() as $category) {
				$this->_allCategoriesKeys[] = $category->primaryKey;
			}
		}
		return $this->_allCategoriesKeys;
	}

	public function getAllCategoriesNames()
	{
		return implode(', ', $this->getAllCategoriesArray());
	}

	public function afterSave($insert)
	{
		$className = $this->classCategoryAssign;

		$className::deleteAll(['item_id' => $this->owner->primaryKey]);

		$this->parseCategories();

		foreach($this->owner->category_id as $name => $value){
			if(!is_array($value) && !is_object($value))
			{
				$this->insertCategoriesValue($value);
			} else {

				foreach($value as $categoryId){
					$this->insertCategoriesValue($categoryId);
				}
			}
		}

		// Дозапись родительских категорий в json, костыль.

		$model = $this->owner;

		$categories = [];

		foreach ($className::findAll(['item_id' => $this->owner->primaryKey]) as $data) {
			$categories[] = (string)$data->category_id;
		}

		Yii::$app->db->createCommand('UPDATE ' . $model::tableName() . ' SET category_id=:categories WHERE ' . $model::primaryKey()[0] . '=:id', ['id' => $model->primaryKey, 'categories' => json_encode($categories)])->execute();
	}

	private function parseCategories(){
		$this->owner->category_id = $this->owner->category_id !== '' ? json_decode($this->owner->category_id) : [];
	}

	public function afterDelete()
	{
		$className = $this->classCategoryAssign;

		$className::deleteAll(['item_id' => $this->owner->primaryKey]);
	}

	private function insertCategoriesValue($category)
	{
		$className = $this->classCategoryAssign;

		$this->insertData($className::tableName(), [
			'item_id' => $this->owner->primaryKey,
			'category_id' => trim($category),
		]);

		$this->insertCategoriesParents($category);
	}

	private function insertCategoriesParents($id)
	{
		$className = $this->classCategory;

		if($parent = $className::parent($id))
		{
			$className = $this->classCategoryAssign;

			if(!$className::findAll(['category_id' => $parent->category_id, 'item_id' => $this->owner->primaryKey]))
			{
				$this->insertData($className::tableName(), [
					'item_id' => $this->owner->primaryKey,
					'category_id' => $parent->category_id,
				]);

				return $this->insertCategoriesParents($parent->category_id);
			}
		}else{
			return false;
		}
	}

	private function insertData($dbname, $data = [])
	{
		return Yii::$app->db->createCommand()->insert($dbname, $data)->execute();
	}

	public function searchByCategory($params)
	{
		return $dataProvider;
	}
}