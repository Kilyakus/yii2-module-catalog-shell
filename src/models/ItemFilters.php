<?php
namespace kilyakus\shell\directory\models;

use Yii;

class ItemFilters extends \kilyakus\modules\components\ActiveRecord
{
    public static function tableName()
    {
        return 'catalog_item_filters';
    }


    public function rules()
    {
        $rules = [
            ['user_id', 'required'],
            [['text','data'], 'safe'],
            [['category_id','available','price','discount','time','time_to','views','permission','country_id','region_id','city_id','street_id','street_number_id',], 'integer'],
        ];

        if($this->module->settings['enableCategory']){
            $rules = array_merge($rules,[['category_id', 'required', 'message' => Yii::t('easyii', 'Select category')]]);
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'title' => Yii::t('easyii', 'Title'),
            'text' => Yii::t('easyii', 'Text'),
            'available' => Yii::t('easyii/catalog', 'Available'),
            'price' => Yii::t('easyii/catalog', 'Price'),
            'discount' => Yii::t('easyii/catalog', 'Discount'),
            'time' => Yii::t('easyii', 'Date'),
            'time_to' => Yii::t('easyii', 'Date'),
            'country' => Yii::t('easyii/catalog','Country'),
            'region' => Yii::t('easyii/catalog','Region'),
            'city' => Yii::t('easyii/catalog','City'),
            'street' => Yii::t('easyii/catalog','Street'),
            'number' => Yii::t('easyii/catalog','Number'),
        ];
    }
}