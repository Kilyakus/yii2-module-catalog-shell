<?php
namespace kilyakus\shell\directory\models;

class ItemChats extends \kilyakus\modules\components\ActiveRecord
{
    public function rules()
    {
        return [
            [['item_id', 'chat_id'], 'required'],
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