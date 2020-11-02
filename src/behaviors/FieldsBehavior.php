<?php
namespace kilyakus\shell\directory\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class FieldsBehavior extends Behavior
{
    public $attribute;

    public $fieldsClass;
    public $categoryClass;

    // public function events()
    // {
    //     return [
    //         ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
    //         ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
    //         ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
    //     ];
    // }

    // public function getFieldsAssigns()
    // {
    //     return $this->owner->hasMany(FieldsAssign::className(), ['item_id' => $this->owner->primaryKey()[0]])->where(['class' => get_class($this->owner)]);
    // }

    public function getFields()
    {
        $fieldsClass = $this->fieldsClass;
        $categoryClass = $this->categoryClass;

        $filter = ['category_id' => $this->owner->categoriesKeys, 'class' => $categoryClass];
        
        return $fieldsClass::find()->where(['and',$filter, ['depth' => 0]])->orderBy(['order_num' => SORT_DESC])->all();

        // return $this->owner->hasMany($fieldsClass::className(), ['favorite_id' => 'favorite_id'])->via('fieldsAssigns');
    }

    // public function afterSave($insert, $attributes)
    // {
    //     if(parent::afterSave($insert, $attributes))
    //     {

    //     }else{
    //         return false;
    //     }
    // }

    // public function beforeDelete()
    // {
    //     if(parent::beforeDelete())
    //     {

    //     }else{
    //         return false;
    //     }
    // }

    // private function parseFields(){
    //     $this->data = $this->data !== '' ? json_decode($this->data) : [];
    // }
}