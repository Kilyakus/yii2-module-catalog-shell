<?php
namespace kilyakus\shell\directory\models;

class ItemUpdate extends \kilyakus\modules\components\ActiveRecord
{

    // public static function tableName()
    // {
    //     return 'catalog_items_update';
    // }

    public function rules()
    {
        return [
            [['item_id', 'time'], 'required'],
            ['time', 'default', 'value' => time()],
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