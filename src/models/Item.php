<?php
namespace kilyakus\shell\directory\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\behaviors\SluggableBehavior;
use bin\admin\behaviors\SeoBehavior;
use bin\admin\behaviors\Taggable;
use kilyakus\cutter\behaviors\CutterBehavior;
use bin\admin\models\Photo;
use bin\admin\models\Video;
use bin\admin\models\Comment;
use bin\admin\models\CType;
use bin\admin\models\User;

class Item extends \kilyakus\modules\components\ActiveRecord
{
    public $transferClasses = [];

    const STATUS_OFF = 0;
    const STATUS_ON = 1;
    const STATUS_COPY = 2;
    const STATUS_ARCHIVE = 2;

    public static function tableName()
    {
        return 'catalog_items';
    }

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        $rules = [
            ['title', 'required'],
            ['title', 'trim'],
            [['parent_class','title','permission','gradient','gradient_to','latitude','longitude'], 'string', 'max' => 255],
            ['parent_id', 'default'],
            [['preview','image',], 'image'],
            ['description', 'safe'],
            ['price', 'number'],
            ['discount', 'integer', 'max' => 100],
            [['type_id','views','country_id','region_id','city_id','street_id','street_number_id', 'available', 'time', 'created_by', 'updated_by', 'owner', 'status'], 'integer'],
            [['time','time_to'], 'default', 'value' => time()],
            ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')],
            ['slug', 'default', 'value' => null],
            ['status', 'default', 'value' => self::STATUS_ON],
            ['tagNames', 'safe'],
            ['owner', 'integer'],
            ['webcams', 'safe'],
            [['latitude','longitude'], 'required', 
                'when' => function ($model) {
                     return !empty($model->city_id);
                 },
                'whenClient' => 'function(attribute,value){
                    if($("#' . Html::getInputId($this, 'latitude') . '").val()==""){
                        $(".field-item-locality_id .alert.alert-danger").remove()
                        $(".field-item-locality_id").append("<div class=\"alert alert-danger mt-15 mb-0\">' . Yii::t('easyii', 'You must enter the marker on the map') . '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">×</span></button></div>");
                    }
                    return $("#' . Html::getInputId($this, 'city_id') . '").val()!=="";
                }','message' => ''// Yii::t('easyii', 'You must enter the marker on the map')
            ],
        ];

        if($this->module->settings['enableCategory']){
            $rules = array_merge($rules,[['category_id', 'required', 'message' => Yii::t('easyii', 'Select category')]]);
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'parent_class' => Yii::t('easyii', 'Module'),

            'title' => Yii::t('easyii', 'Title'),
            'preview' => Yii::t('easyii', 'Change main photo'),
            'image' => Yii::t('easyii', 'Upload background and main photo'),
            'description' => Yii::t('easyii', 'Description'),
            'available' => Yii::t('easyii/catalog', 'Available'),
            'price' => Yii::t('easyii/catalog', 'Price'),
            'discount' => Yii::t('easyii/catalog', 'Discount'),
            'time' => Yii::t('easyii', 'Date'),
            'time_to' => Yii::t('easyii', 'Date'),
            'slug' => Yii::t('easyii', 'Slug'),
            'tagNames' => Yii::t('easyii', 'Tags'),
            'owner' => Yii::t('easyii', 'Authorize yourself as the owner'),
            'gradient' => Yii::t('easyii/catalog', 'Choose Color'),
            'gradient_to' => Yii::t('easyii/catalog', 'To Color'),

            'country' => Yii::t('easyii/catalog','Country'),
            'region' => Yii::t('easyii/catalog','Region'),
            'city' => Yii::t('easyii/catalog','City'),
            'street' => Yii::t('easyii/catalog','Street'),
            'number' => Yii::t('easyii/catalog','Number'),
        ];
    }

    public function behaviors()
    {
        return [
            'seoBehavior' => SeoBehavior::className(),
            'taggabble' => Taggable::className(),
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'ensureUnique' => true
            ],
            'preview' => [
                'class' => CutterBehavior::className(),
                'attributes' => 'preview',
                'baseDir' => '/uploads/' . $this->module->name . '/previews',
                'basePath' => '@webroot/uploads/' . $this->module->name . '/previews',
            ],
            'image' => [
                'class' => CutterBehavior::className(),
                'attributes' => 'image',
                'baseDir' => '/uploads/' . $this->module->name . '/images',
                'basePath' => '@webroot/uploads/' . $this->module->name . '/images',
            ],
        ];
    }

    public function beforeSave($insert)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if($this->isAttributeChanged('category_id')){
            $CategoryAssign::deleteAll(['item_id' => $this->primaryKey]);
        }

        if($this->isAttributeChanged('city_id')){
            
            $city = \bin\admin\modules\geo\api\Geo::city($this->city_id);

            $this->country_id = $city->model->country_id;
            $this->region_id = $city->model->region_id;
        }

        if($this->isAttributeChanged('owner') && $this->owner == 1){
            $this->owner = Yii::$app->user->identity->id;
        }else{
            $this->owner = $this->oldAttributes['owner'];
        }

        if (parent::beforeSave($insert)) {

            if(!$this->data || (!is_object($this->data) && !is_array($this->data))){
                $this->data = new \stdClass();
            }

            $this->data = json_encode($this->data);

            if(!$this->contacts || (!is_object($this->contacts) && !is_array($this->contacts))){
                $this->contacts = new \stdClass();
            }

            $this->contacts = json_encode($this->contacts);

            if(!$insert && $this->preview != $this->oldAttributes['preview'] && $this->oldAttributes['preview']){
                @unlink(Yii::getAlias('@webroot').$this->oldAttributes['preview']);
            }

            if(!$insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']){
                @unlink(Yii::getAlias('@webroot').$this->oldAttributes['image']);
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $attributes)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        parent::afterSave($insert, $attributes);

        $this->parseData();

        $ItemData::deleteAll(['item_id' => $this->primaryKey]);

        $this->parseContacts();

        $ItemContacts::deleteAll(['item_id' => $this->primaryKey]);

        foreach($this->data as $name => $value){
            if(!is_array($value) && !is_object($value)){
                
                $this->insertDataValue($name, $value);
                
            } else {

                foreach($value as $arrayItem){
                    $this->insertDataValue($name, $arrayItem);
                }
            }
        }

        foreach($this->contacts as $name => $value){
            if(!is_array($value) && !is_object($value)){
                
                $this->insertContactsValue($name, $value);
                
            } else {

                foreach($value as $arrayItem){
                    $this->insertContactsValue($name, $arrayItem);
                }
            }
        }

        $post = Yii::$app->request->post('Item');

        if(is_array($post['category_id'])){
            foreach ($post['category_id'] as $item) {
                $assign = new $CategoryAssign;
                $assign->category_id = $item;
                $assign->item_id = $this->primaryKey;
                $assign->save();

                $this->assignParents($item);
            }
        }else{
            $assign = new $CategoryAssign;
            $assign->category_id = $post['category_id'];
            $assign->item_id = $this->primaryKey;
            $assign->save();

            $this->assignParents($post['category_id']);
        }
    }

    public function assignParents($id)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if($parent = $Category::parent($id)){
            if(!$CategoryAssign::findAll(['category_id' => $parent->category_id, 'item_id' => $this->primaryKey])){
                $assign = new $CategoryAssign;
                $assign->category_id = $parent->category_id;
                $assign->item_id = $this->primaryKey;
                $assign->save();

                if($parent->category_id){
                    return $this->assignParents($parent->category_id);
                }
            }
        }else{
            return false;
        }
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->parseData();
        $this->parseContacts();
    }

    public function afterDelete()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        parent::afterDelete();

        foreach($this->getPhotos()->all() as $photo){
            $photo->delete();
        }

        foreach($this->getVideos()->all() as $video){
            $video->delete();
        }

        if($this->preview) {
            @unlink(Yii::getAlias('@webroot') . $this->preview);
        }

        if($this->image) {
            @unlink(Yii::getAlias('@webroot') . $this->image);
        }

        $ItemData::deleteAll(['item_id' => $this->primaryKey]);

        $ItemContacts::deleteAll(['item_id' => $this->primaryKey]);

        $CategoryAssign::deleteAll(['item_id' => $this->primaryKey]);
    }

    private function insertDataValue($name, $value)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        Yii::$app->db->createCommand()->insert($ItemData::tableName(), [
            'item_id' => $this->primaryKey,
            'name' => $name,
            'value' => $value
        ])->execute();
    }

    private function parseData(){
        $this->data = $this->data !== '' ? json_decode($this->data) : [];
    }

    private function insertContactsValue($name, $value)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        Yii::$app->db->createCommand()->insert($ItemContacts::tableName(), [
            'item_id' => $this->primaryKey,
            'name' => $name,
            'value' => $value
        ])->execute();
    }

    private function parseContacts(){
        $this->contacts = $this->contacts !== '' ? json_decode($this->contacts) : [];
    }

    public function getCategory()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        return $Category::find()->where(['category_id' => ($this->category_id ? $this->category_id : Yii::$app->request->get('id'))])->one();
    }

    public function getCategories()
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

    public function get_Categories()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        $categories = $Category::find()->where(['category_id' => ArrayHelper::getColumn($CategoryAssign::findAll(['item_id' => $this->primaryKey]),'category_id')])->all();
       
        return $categories;
    }

    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->sort();
    }

    public function getVideos()
    {
        return $this->hasMany(Video::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->sort();
    }

    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->sort();
    }

    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getType()
    {
        return $this->hasMany(CType::className(), ['type_id' => 'type_id'])->where(['class' => self::className()])->sort();
    }

    public function getFields()
    {

        $fields = [];

        // foreach (self::getCategories() as $key => $category) {
        //     foreach ($parents as $parent) {
        //         $fields = CField::find()->where(['or',['category_id' => $parent->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $categoryClass])->all();
        //         foreach ($fields as $field) {
        //             $this->_fields[$field->field_id] = $field;
        //         }
        //     }
        // }

    //     if($this->parent){
    //         $parents = $this->getParents($this->category_id);

            
    //     }

    //     $fields = CField::find()->where(['or',['category_id' => $this->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $categoryClass])->all();
    //     foreach ($fields as $field) {
    //         $this->_fields[$field->field_id] = $field;
    //     }
        
    //     usort($this->_fields, function($a, $b){
    //         return ($a['category_id'] - $b['category_id']);
    //     });
        return $fields;
    }
}