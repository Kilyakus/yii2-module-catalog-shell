<?php
namespace kilyakus\shell\directory\models;

use Yii;
use bin\admin\models\User;

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
            ['type', 'safe'],
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

    public function getUser()
    {
        $this->user = $this->hasOne(User::className(), ['id' => 'user_id']);

        return $this->user;
    }
}