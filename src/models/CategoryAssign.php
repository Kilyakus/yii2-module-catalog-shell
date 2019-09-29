<?php
namespace kilyakus\shell\directory\models;

use Yii;

class CategoryAssign extends \kilyakus\modules\components\ActiveRecord
{

    public static function tableName()
    {
        return 'catalog_categories_assign';
    }

    public function rules()
    {
        return [
            [['category_id', 'item_id'], 'required'],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

    }
}