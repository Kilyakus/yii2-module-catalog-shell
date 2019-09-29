<?php
namespace kilyakus\shell\directory\models;

use Yii;

class Favorite extends \kilyakus\modules\components\ActiveRecord
{

    public static function tableName()
    {
        return 'catalog_favorites';
    }

    public function rules()
    {
        return [
            [['item_id', 'user_id'], 'required'],
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