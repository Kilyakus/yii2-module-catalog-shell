<?php
namespace kilyakus\shell\directory\models;

use Yii;
use bin\admin\models\CField;
use bin\admin\models\CType;
use bin\admin\models\CContact;
use bin\admin\models\Photo;
use bin\admin\models\Video;
use kilyakus\helper\media\Image;

class Category extends \bin\admin\models\CategoryModel
{
    public $transferClasses = [];

    public $_fields = [];

    public $_contacts = [];

    static $fieldTypes = [
        'string' => 'String',
        'text' => 'Text',
        'integer' => 'Integer',
        'boolean' => 'Boolean',
        'select' => 'Select',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio',
        'table' => 'Table',
    ];

    static $formatTypes = [];

    public function init()
    {
        parent::init();

        static::$formatTypes = [
            0 => Yii::t('easyii','Default') . ' - ' . Yii::t('easyii','Text'),
            1 => Yii::t('easyii','Slider'),
        ];
    }

    public static function tableName()
    {
        return 'catalog_categories';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert && ($parent = $this->parents(1)->one())){
                $this->fields = $parent->fields;
            }

            if(!$this->fields || !is_array($this->fields)){
                $this->fields = [];
            }
            $this->fields = json_encode($this->fields);

            return true;
        } else {
            return false;
        }
    }

    public function getImage($width = null, $height = null)
    {
        if($width != null || $height != null){
            return Image::thumb($this->image, $width, $height);
        }
        
        return $this->image;
    }

    public function afterSave($insert, $attributes)
    {
        parent::afterSave($insert, $attributes);
        $this->parseFields();
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->parseFields();
    }

    public function getItemsAssigns($categories = [])
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        return $this->hasMany($CategoryAssign::className(), ['category_id' => $this->primaryKey()[0]]);
    }

    public function getItems($categories = [])
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        return $this->hasMany($Item::className(), ['category_id' => $this->primaryKey()[0]])->via('itemsAssigns');
    }

    public function getFieldList()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if($this->parent){
            $parents = $this->getParents($this->category_id);

            foreach ($parents as $parent) {
                $fields = CField::find()->where(['or',['category_id' => $parent->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $Category])->andFilterWhere(['status' => 1])->all();
                foreach ($fields as $field) {
                    $this->_fields[$field->field_id] = $field;
                }
            }
        }

        $fields = CField::find()->where(['or',['category_id' => $this->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $Category])->andFilterWhere(['status' => 1])->all();
        foreach ($fields as $field) {
            $this->_fields[$field->field_id] = $field;
        }
        
        usort($this->_fields, function($a, $b){
            return ($a['category_id'] - $b['category_id']);
        });

        return $this->_fields;
    }

    public function getTypes()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        return $this->hasMany(CType::className(), ['category_id' => 'category_id'])->where(['class' => $Category]);
    }

    public function getContacts()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if($this->parent){
            $parents = $this->getParents($this->category_id);

            foreach ($parents as $parent) {
                $fields = CContact::find()->where(['or',['category_id' => $parent->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $Category])->andFilterWhere(['status' => 1])->all();
                foreach ($fields as $field) {
                    $this->_contacts[$field->contact_id] = $field;
                }
            }
        }

        $fields = CContact::find()->where(['or',['category_id' => $this->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $Category])->andFilterWhere(['status' => 1])->all();
        foreach ($fields as $field) {
            $this->_contacts[$field->contact_id] = $field;
        }
        
        usort($this->_contacts, function($a, $b){
            return ($a['category_id'] - $b['category_id']);
        });

        return $this->_contacts;
    }

    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['item_id' => 'category_id'])->where(['class' => self::className()]);
    }

    public function getVideos()
    {
        return $this->hasMany(Video::className(), ['item_id' => 'category_id'])->where(['class' => self::className()]);
    }

    public function getCategories($context = null)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

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


    public function afterDelete()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        parent::afterDelete();

        foreach($this->getItems()->all() as $item){
            if(count($CategoryAssign::find()->where(['item_id' => $item->item_id])->all()) <= 1){
                $item->delete();
            }
        }
    }

    private function parseFields(){
        $this->fields = $this->fields !== '' ? json_decode($this->fields) : [];
    }
}