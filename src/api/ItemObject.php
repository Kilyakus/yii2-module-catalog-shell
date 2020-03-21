<?php
namespace kilyakus\shell\directory\api;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use bin\admin\components\API;
use bin\admin\models\User;
use bin\admin\models\Photo;
use bin\admin\models\Video;
use bin\admin\models\Comment;
use bin\admin\models\Season;
use bin\admin\models\CType;

class ItemObject extends \kilyakus\components\api\Object
{
    public $transferClasses = [];
    public $moduleDir;
    public $moduleName;

    public $slug;
    public $image;
    public $data;
    public $contacts;
    public $category_id;
    public $available;
    public $discount;
    public $time;
    public $time_to;
    public $country_id;
    public $region_id;
    public $city_id;
    public $street_id;
    public $street_number_id;
    private $_latitude;
    private $_longitude;

    private $_preview;
    private $_category;
    private $_categories;
    private $_type;
    private $_fields;
    private $_author;
    private $_owner;
    private $_photos;
    private $_videos;
    private $_places;
    private $_comments;
    private $_seasons;
    private $_webcams;
    private $_nearby;
    private $_members;
    private $_favorites;

    public function init()
    {
        foreach ($this->transferClasses as $group => $items) {
            if(is_array($items)){
                foreach ($items as $class) {
                    self::put($class);
                    $this->{$class} = $this->moduleDir . $this->moduleName . '\\' . $group . '\\' . $class;
                }
            }
        }
    }

    public function getTitle(){
        return LIVE_EDIT ? API::liveEdit($this->model->translate->title, $this->editLink) : $this->model->translate->title;
    }

    public function getPreview(){
        return $this->model->preview ? $this->model->preview : $this->model->image;
    }

    public function getImage(){
        return $this->model->image ? $this->model->image : $this->model->preview;
    }

    public function getDescription(){
        return LIVE_EDIT ? API::liveEdit($this->model->description, $this->editLink, 'div') : $this->model->description;
    }

    public function getCat()
    {
        foreach($this->transferClasses as $name=>$class){if(!is_array($class)){${$name}=$class;}};

        return $Category::findOne($this->category_id);
    }
    
    public function getPrice(){
        return $this->discount ? round($this->model->price * (1 - $this->discount / 100) ) : $this->model->price;
    }

    public function getOldPrice(){
        return $this->model->price;
    }

    public function getDate(){
        return Yii::$app->formatter->asDate($this->time);
    }

    public function getTags(){
        return $this->model->tagsArray;
    }

    public function getCategory()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_category){
            $this->_category = [];
            $this->_category = new $CategoryObject($Category::find()->where(['category_id' => $this->model->category_id])->one());
        }
        return $this->_category;
    }

    public function getCategories()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_categories){

            $categories = $Category::find()->where(['category_id' => $CategoryAssign::findAll(['item_id' => $this->id])])->all();

            foreach ($categories as $category) {
                $this->_categories[] = new $CategoryObject($category);
            }
        }
        return $this->_categories;
    }

    public function getFields()
    {
        return $this->data;
    }

    public function getType()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_type){
            $this->_type = CType::find()->where(['class' => $Category::className(), 'type_id' => $this->model->type_id])->one();
        }
        return $this->_type;
    }

    public function getAuthor()
    {
        if(!$this->_author){
            if($this->model->created_by){
                $this->_author = User::findOne($this->model->created_by);
            }else{
                $this->_author = [];
            }
        }
        return $this->_author;
    }

    public function getOwner()
    {
        if(!$this->_author){
            if($this->model->owner){
                $this->_author = User::findOne($this->model->owner);
            }else{
                $this->_author = [];
            }
        }
        return $this->_author;
    }

    public function getPhotos()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_photos){
            $this->_photos = [];

            $photos = Photo::find()->where(['and',['class' => $Item::className(), 'item_id' => $this->id], ['status' => Photo::STATUS_ON]]);

            foreach($photos->all() as $model){
                if($model->status == Photo::STATUS_ON){
                    $this->_photos[] = new $PhotoObject($model,['module' => $this->moduleName]);
                }
            }
        }
        return $this->_photos;
    }

    public function getVideos()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_videos){
            $this->_videos = [];

            foreach(Video::find()->where(['class' => $Item::className(), 'item_id' => $this->id])->sort()->all() as $model){
                $this->_videos[] = new $VideoObject($model);
            }
        }
        return $this->_videos;
    }

    public function getPlaces()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        $places = ArrayHelper::getColumn(\bin\admin\modules\geo\models\MapsStreetAssign::find()->where(['class' => $Item::className(), 'parent_id' => $this->id])->all(),'item_id');

        $this->_places = $Item::find()->where(['IN','item_id', $places])->all();

        return $this->_places;
    }

    public function getComments()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_comments){
            $this->_comments = Comment::tree(['class' => $Item::className(), 'item_id' => $this->id]);
        }
        return $this->_comments;
    }

    public function getSeasons()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!$this->_seasons){
            $this->_seasons = [];

            foreach(Season::find()->where(['class' => $Item::className(), 'item_id' => $this->id])->all() as $model){
                $this->_seasons[] = new $SeasonObject($model);
            }
        }
        return $this->_seasons;
    }

    public function getNearby()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        $categoryAssign = ArrayHelper::getColumn($CategoryAssign::find()->where(['item_id' => $this->id])->all(),'category_id');

        $latitude = (float)substr($this->model->latitude, 0, 5);
        $longitude = (float)substr($this->model->longitude, 0, 5);

        if(substr($latitude, 0, 1) != '-'){
            $latPlus = ($latitude+0.5);
        }else{
            $latPlus = ($latitude-0.5);
        }

        if(substr($latitude, 0, 1) != '-'){
            $latMinus = ($latitude-0.5);
        }else{
            $latMinus = ($latitude+0.5);
        }

        if(substr($longitude, 0, 1) != '-'){
            $lngPlus = ($longitude+0.5);
        }else{
            $lngPlus = ($longitude-0.5);
        }

        if(substr($longitude, 0, 1) != '-'){
            $lngMinus = ($longitude-0.5);
        }else{
            $lngMinus = ($longitude+0.5);
        }

        $places = ArrayHelper::getColumn(\bin\admin\modules\geo\models\MapsStreetAssign::find()->where(['class' => $Item::className(), 'parent_id' => $this->id])->all(),'item_id');

        $this->_nearby = $Item::find()->where(['and',
            ['category_id' => $categoryAssign],
            ['NOT IN','item_id', array_merge([$this->id],$places)],
            ['!=','latitude', $latitude],
            ['!=','longitude', $longitude],
            ['<=','latitude', $latPlus],
            ['>=','latitude', $latMinus],
            ['<=','longitude', $lngPlus],
            ['>=','longitude', $lngMinus],

        ])->all();

        return $this->_nearby;
    }

    public function getMembers()
    {
        return $this->model->members;
    }

    public function getFavorites()
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        $this->_favorites[] = $Favorite::findAll(['item_id' => $this->id]);

        return $this->_favorites;
    }

    public function getLatitude(){
        return (float)$this->model->latitude;
    }

    public function getLongitude(){
        return (float)$this->model->longitude;
    }

    public function getEditLink(){
        return Url::to(['/admin/' . $this->moduleName . '/items/edit/', 'id' => $this->id]);
    }

}