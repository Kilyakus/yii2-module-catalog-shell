<?php
namespace kilyakus\shell\directory\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;

class CategoriesBehavior extends Behavior
{
    public $classCategory;
    public $classCategoryAssign;

    public $_categories;
    public $_categoriesKeys;
    public $_allCategories;
    public $_allCategoriesKeys;

    // public function events()
    // {
    //     return [
    //         ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
    //         ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
    //         ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
    //     ];
    // }

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

    // public function afterSave()
    // {
    //     if(!$this->owner->isNewRecord && $this->owner->categoryNames || !$this->owner->categoryNames) {
    //         $this->beforeDelete();
    //     }

    //     if(count($this->_categories)) {
    //         $categoryAssigns = [];
    //         $modelClass = get_class($this->owner);

    //         foreach ($this->_categories as $name) {
    //             if (!($category = Favorite::findOne(['name' => $name]))) {
    //                 $category = new Favorite(['name' => $name]);
    //             }
    //             $category->frequency++;

    //             if ($category->save()) {
    //                 $updatedFavorites[] = $category;
    //                 $categoryAssigns[] = [$modelClass, $this->owner->primaryKey, $category->category_id];
    //             }
    //         }

    //         if(count($categoryAssigns)) {
    //             Yii::$app->db->createCommand()->batchInsert(FavoriteAssign::tableName(), ['class', 'item_id', 'category_id'], $categoryAssigns)->execute();
    //             $this->owner->populateRelation('categories', $updatedFavorites);
    //         }
    //     }
    // }

    // public function beforeDelete()
    // {
    //     $pks = [];

    //     foreach($this->owner->categories as $category){
    //         $pks[] = $category->primaryKey;
    //     }

    //     if (count($pks)) {
    //         Favorite::updateAllCounters(['frequency' => -1], ['in', 'category_id', $pks]);
    //     }
    //     Favorite::deleteAll(['frequency' => 0]);
    //     FavoriteAssign::deleteAll(['class' => get_class($this->owner), 'item_id' => $this->owner->primaryKey]);
    // }
}