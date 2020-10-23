<?php
namespace kilyakus\shell\directory\models;

use Yii;
use bin\admin\components\API;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\behaviors\SluggableBehavior;
use kilyakus\package\gui\behaviors\GuiBehavior;
use kilyakus\package\seo\behaviors\SeoBehavior;
use kilyakus\package\translate\behaviors\TranslateBehavior;
use kilyakus\package\taggable\behaviors\Taggable;
use kilyakus\cutter\behaviors\CutterBehavior;
use bin\admin\models\Album;
use bin\admin\models\Photo;
use bin\admin\models\Video;
use bin\admin\models\Comment;
use bin\admin\models\CType;
use bin\admin\models\CField;
use bin\admin\models\Favorite;
use bin\admin\models\FavoriteAssign;
use bin\user\models\User;
use bin\user\models\UserSearch;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use kilyakus\helper\media\Image;

class Item extends \kilyakus\modules\components\ActiveRecord
{
	public $transferClasses = [];

	public $module;

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
		$this->module = API::getModule($this::MODULE_NAME);

		// $inflexive = str_replace('/', '\\', $_SERVER['DOCUMENT_ROOT'] . '/');
		// $throwaway = str_replace($inflexive, '',__DIR__);
		// $makeready = explode('\\',$throwaway);
		// $penetrate = [];
		// foreach ($makeready as $name) {
		//	 if($name == $this->module->name) break;
		//	 $penetrate[] = $name;
		// }
		// $moduleDirectory = implode('\\',$penetrate) . '\\';

		$moduleDirectory = 'bin\admin\modules\\';

		foreach ($this->transferClasses as $group => $items) {
			if(is_array($items)){
				foreach ($items as $class) {
					self::put($class);
					$this->{$class} = $moduleDirectory . $this->module->name . '\\' . $group . '\\' . $class;
				}
			}
		}

		parent::init();
	}

	protected $cacheCategory;

	// public $category;
	// public $country;
	// public $region;
	// public $city;
	// public $street;
	// public $number;
	// public $priceFrom;
	// public $priceTo;
	// public $storageFrom;
	// public $storageTo;
	// public $text;
	
	// public $filters = [];

	// public $sort;

	// public $_fields = [];

	// public function __get($name){
	// 	if(array_key_exists($name, $this->_fields))
	// 		return $this->_fields[$name];

	// 	return parent::__get($name);
	// }

	// public function __set($name, $value){
	// 	if(array_key_exists($name, $this->_fields))
	// 		$this->_fields[$name] = $value;

	// 	return parent::__set($name, $value);
	// }

	// public function put($attribute)
	// {
	// 	$this->_fields[$attribute] = null;
	// 	$this->__set($attribute, null);
	// }

	// public function __construct()
	// {
	// 	parent::__construct();

	// 	static::put('nearby');
	// 	static::put('radius');
	// 	static::put('distance');

	// 	$fields = CField::find()->where(['class' => $this->Category])->all();
	// 	$fields = ArrayHelper::getColumn($fields,'name');
	// 	$fields = array_unique($fields);

	// 	foreach ($fields as $field) {
	// 		static::put($field);
	// 	}
	// }

	public function __construct()
	{
		parent::__construct();

		static::put('date_range');
		
		static::put('nearby');
		static::put('radius');
		static::put('distance');
	}

	public function rules()
	{
		$modelName = (new \ReflectionClass($this))->getShortName();

		$rules = [];
		$rules[] = ['title', 'trim'];
		$rules[] = [['parent_class','title','permission','gradient','gradient_to','latitude','longitude'], 'string', 'max' => 255];
		$rules[] = ['parent_id', 'default'];
		$rules[] = [['preview','image',], 'image'];
		$rules[] = ['description', 'safe'];
		$rules[] = ['price', 'number'];
		$rules[] = ['discount', 'integer', 'max' => 100];
		$rules[] = [['type_id','views','country_id','region_id','city_id','street_id','street_number_id', 'available', 'created_by', 'updated_by', 'owner', 'status'], 'integer'];
		// $rules[] = [['time','time_to'], 'default', 'value' => time()];
		$rules[] = ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')];
		$rules[] = ['slug', 'default', 'value' => null];
		$rules[] = ['status', 'default', 'value' => self::STATUS_ON];
		$rules[] = ['owner', 'integer'];
		$rules[] = ['webcams', 'safe'];
		$rules[] = [['latitude','longitude'], 'required',
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
		];

		if(!Yii::$app->request->post($modelName)['translations']){
			$rules[] = ['title', 'required'];
		}

		if($this->module->settings['itemSale']){
			$rules[] = ['price', 'required'];
		}
		
		if($this->module->settings['enableCategory']){
			$rules[] = ['category_id', 'required', 'message' => Yii::t('easyii', 'Select category')];
		}

		$rules[] = [['date_range'], 'match', 'pattern' => '/^.+\s\-\s.+$/'];

		$rules[] = ['translations', 'safe'];
		
		$rules[] = ['tagNames', 'safe'];

		return $rules;
	}

	public function attributeLabels()
	{
		return [
			'parent_class' => Yii::t('easyii', 'Module'),

			'category_id' => Yii::t('easyii', 'Category'),
			'title' => Yii::t('easyii', 'Title'),
			'preview' => Yii::t('easyii', 'Change main photo'),
			'image' => Yii::t('easyii', 'Upload background and main photo'),
			'description' => Yii::t('easyii', 'Description'),
			'available' => Yii::t('easyii/' . $this->module->name, 'Available'),
			'price' => Yii::t('easyii/' . $this->module->name, 'Price'),
			'discount' => Yii::t('easyii/' . $this->module->name, 'Discount'),
			'time' => Yii::t('easyii', 'Date'),
			'time_to' => Yii::t('easyii', 'Date'),
			'slug' => Yii::t('easyii', 'Slug'),
			'owner' => IS_MODER ? Yii::t('easyii/' . $this->module->name, 'Owner') : Yii::t('easyii', 'Authorize yourself as the owner'),
			'gradient' => Yii::t('easyii/' . $this->module->name, 'Choose Color'),
			'gradient_to' => Yii::t('easyii/' . $this->module->name, 'To Color'),

			'continent' => Yii::t('easyii/' . $this->module->name,'Continent'),
			'country' => Yii::t('easyii/' . $this->module->name,'Country'),
			'region' => Yii::t('easyii/' . $this->module->name,'Region'),
			'city' => Yii::t('easyii/' . $this->module->name,'City'),
			'street' => Yii::t('easyii/' . $this->module->name,'Street'),
			'number' => Yii::t('easyii/' . $this->module->name,'Number'),

			'latitude' => Yii::t('easyii', 'Latitude'),
			'longitude' => Yii::t('easyii', 'Longitude'),
			
			'tagNames' => Yii::t('easyii', 'Tags'),
		];
	}

	public function behaviors()
	{
		return [
			'dateRangeBehavior' => [
				'class' => \kilyakus\widget\daterange\DateRangeBehavior::className(),
				'attribute' => 'date_range',
				'dateStartAttribute' => 'time',
				'dateEndAttribute' => 'time_to',
			    // 'dateStartFormat' => Yii::$app->formatter->datetimeFormat,
			    // 'dateEndFormat' => Yii::$app->formatter->datetimeFormat,
			],
			'seoBehavior' => SeoBehavior::className(),
			'translateBehavior' => TranslateBehavior::className(),
			'taggabble' => Taggable::className(),
			'sluggable' => [
				'class' => SluggableBehavior::className(),
				'attribute' => 'title',
				'ensureUnique' => true
			],
			'albumBehavior' => [
                'class' => GuiBehavior::className(),
                'model' => Album::className(),
                'isRoot' => IS_USER,
                'identity' => Yii::$app->user->identity->id,
            ],
			'photoBehavior' => [
				'class' => GuiBehavior::className(),
				'model' => Photo::className(),
				'isRoot' => IS_MODER,
				'identity' => Yii::$app->user->identity->id,
			],
			// 'preview' => [
			// 	'class' => CutterBehavior::className(),
			// 	'attributes' => 'preview',
			// 	'baseDir' => '/uploads/' . $this->module->name . '/previews',
			// 	'basePath' => '@webroot/uploads/' . $this->module->name . '/previews',
			// ],
			// 'image' => [
			// 	'class' => CutterBehavior::className(),
			// 	'attributes' => 'image',
			// 	'baseDir' => '/uploads/' . $this->module->name . '/images',
			// 	'basePath' => '@webroot/uploads/' . $this->module->name . '/images',
			// ],
		];
	}

	public static function create($attributes = [])
	{
		if(!empty($attributes)){

			$modelClass = get_called_class();

			$model = new $modelClass;

			foreach ($attributes as $attribute => $value)
			{
				if(array_key_exists($attribute, $model->attributes))
				{
					$model->{$attribute} = $value;
				}
			}

			$model->save();

			return $model;

		}else{

			return false;

		}
	}

	public function beforeSave($insert)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}
		
		$this->cacheCategory = $this->category_id;

		if($this->isNewRecord){
			if(empty($this->time)){
				$this->time = time();
			}
			if(empty($this->time_to) && time() >= $this->time){
				$this->time_to = time();
			}
		}

		if($this->isAttributeChanged('category_id') || $this->isNewRecord)
		{
			if(is_array($this->category_id) || is_object($this->category_id))
			{
				$this->category_id = $this->category_id[0];
			}
		}

		if($this->isAttributeChanged('city_id')){
			
			$city = \bin\admin\modules\geo\api\Geo::city($this->city_id);

			$this->country_id = $city->model->country_id;
			$this->region_id = $city->model->region_id;
		}
		
		if(!IS_MODER){
			if($this->isAttributeChanged('owner') && $this->owner == 1){
				$this->owner = Yii::$app->user->identity->id;
			}else{
				$this->owner = $this->oldAttributes['owner'];
			}
		}

		if (parent::beforeSave($insert)) {	

			if(!empty($this->module->settings['submoduleClass'])){
				$this->parent_class = $this->module->settings['submoduleClass'];
			}elseif(!empty($this->module->settings['subcategoryClass']) && empty($this->module->settings['submoduleClass'])){
				$this->parent_class = $this->module->settings['subcategoryClass'];
			}

			if($this->isNewRecord || !$this->created_by){

				$this->created_by = Yii::$app->user->identity->id;
			}

			$this->updated_by = Yii::$app->user->identity->id;

			if(!$this->data || (!is_object($this->data) && !is_array($this->data))){
				$this->data = new \stdClass();
			}

			$this->data = json_encode($this->data);

			if(!$this->contacts || (!is_object($this->contacts) && !is_array($this->contacts))){
				$this->contacts = new \stdClass();
			}

			$this->contacts = json_encode($this->contacts);

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

		$CategoryAssign::deleteAll(['item_id' => $this->primaryKey]);

		if(is_array($this->cacheCategory) || is_object($this->cacheCategory))
		{
			foreach($this->cacheCategory as $categoryId){
				$this->insertCategoriesValue(trim($categoryId));
			}
		}else{
			$this->insertCategoriesValue($this->cacheCategory);
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

		$ItemData::deleteAll(['item_id' => $this->primaryKey]);

		$ItemContacts::deleteAll(['item_id' => $this->primaryKey]);

		$CategoryAssign::deleteAll(['item_id' => $this->primaryKey]);
	}

	private function insertDataValue($name, $value)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		$this->insertData($ItemData::tableName(), [
			'item_id' => $this->primaryKey,
			'name' => $name,
			'value' => $value,
		]);
	}

	private function parseData(){
		$this->data = $this->data !== '' ? json_decode($this->data) : [];
	}

	private function insertContactsValue($name, $value)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		$this->insertData($ItemContacts::tableName(), [
			'item_id' => $this->primaryKey,
			'name' => $name,
			'value' => $value,
		]);
	}

	private function parseContacts(){
		$this->contacts = $this->contacts !== '' ? json_decode($this->contacts) : [];
	}

	private function insertCategoriesValue($category)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		$this->insertData($CategoryAssign::tableName(), [
			'item_id' => $this->primaryKey,
			'category_id' => $category,
		]);

		$this->assignParents($category);
	}

	public function assignParents($id)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		if($parent = $Category::parent($id)){
			if(!$CategoryAssign::findAll(['category_id' => $parent->category_id, 'item_id' => $this->primaryKey]))
			{
				$this->insertData($CategoryAssign::tableName(), [
					'item_id' => $this->primaryKey,
					'category_id' => $parent->category_id,
				]);

				if($parent->category_id){
					return $this->assignParents($parent->category_id);
				}
			}
		}else{
			return false;
		}
	}

	private function insertData($dbname, $data = [])
	{
		return Yii::$app->db->createCommand()->insert($dbname, $data)->execute();
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

	public function getImage($width = null, $height = null)
	{
		if($width != null || $height != null){
			return Image::thumb($this->image, $width, $height);
		}
		
		return $this->image;
	}

	public function getPreview($width = null, $height = null)
	{
		if($width != null || $height != null){
			return Image::thumb($this->preview, $width, $height);
		}
		
		return $this->preview;
	}

	public function getAlbums()
	{
		return $this->hasMany(Album::className(), ['item_id' => 'item_id'])->where(['class' => self::className()]);
	}

	public function getPhotos()
	{
		return $this->hasMany(Photo::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->orderBy(['main' => SORT_DESC, 'order_num' => SORT_DESC]);
	}

	public function getVideos()
	{
		// return $this->hasMany(Video::className(), ['item_id' => 'item_id'])->where(['class' => self::className()]);
		
		return $this->hasMany(Photo::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->andWhere(['not', ['video' => 'null']])->orderBy(['main' => SORT_DESC, 'order_num' => SORT_DESC]);
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

	public function getContinent()
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		if(($country = $this->country) && !$this->continent_id)
		{
			$this->continent_id = $this->country->continent->primaryKey;
			$this->update();
		}

		return $this->hasOne(\bin\admin\modules\geo\models\MapsContinent::className(), ['id' => 'continent_id']);
	}

	public function getCountry()
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		return $this->hasOne(\bin\admin\modules\geo\models\MapsCountry::className(), ['id' => 'country_id']);
	}

	public function getRegion()
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		return $this->hasOne(\bin\admin\modules\geo\models\MapsRegion::className(), ['id' => 'region_id']);
	}

	public function getCity()
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		return $this->hasOne(\bin\admin\modules\geo\models\MapsCity::className(), ['id' => 'city_id']);
	}

	public function getAddress()
	{
		return \bin\admin\modules\geo\api\Geo::address($this);
	}

	public function getMembers()
	{
		$query = [
			'and',
			['class' => get_class($this)],
			['item_id' => $this->primaryKey],
			['owner_class' => get_class(new User)],
		];

		$searchModel  = \Yii::createObject(FavoriteAssign::className());
		$dataProvider = $searchModel->search(\Yii::$app->request->get());
		$dataProvider->query->andFilterWhere($query);

		$members = [];

		foreach ($dataProvider->query->all() as $key => $item) {
			$members[] = $item->owner->primaryKey;
		}
		
		$members = array_unique($members);

		$searchModel  = \Yii::createObject(UserSearch::className());
		$dataProvider = $searchModel->search(\Yii::$app->request->get());
		$dataProvider->query->where(['id' => $members]);

		return $dataProvider->query->all();
	}

	public function getFields()
	{

		$fields = [];

		// foreach (self::getCategories() as $key => $category) {
		// 	 foreach ($parents as $parent) {
		// 		 $fields = CField::find()->where(['or',['category_id' => $parent->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $categoryClass])->all();
		// 		 foreach ($fields as $field) {
		// 			 $this->_fields[$field->field_id] = $field;
		// 		 }
		// 	 }
		// }

		// if($this->parent){
		// 	$parents = $this->getParents($this->category_id);
		// }

		// $fields = CField::find()->where(['or',['category_id' => $this->category_id],['is','category_id', new \yii\db\Expression('null')]])->andFilterWhere(['class' => $categoryClass])->all();
		// foreach ($fields as $field) {
		// 	$this->_fields[$field->field_id] = $field;
		// }
		
		// usort($this->_fields, function($a, $b){
		// 	return ($a['category_id'] - $b['category_id']);
		// });
		return $fields;
	}

	public function getSubmodules()
	{
		$moduleName = $this->module->name;

		$modules = [];

		foreach (Yii::$app->getModule('admin')->activeModules as $activeModule) {

			if($activeModule->settings["enableSubmodule"]) {

				$parents = explode(',',$activeModule->settings["submoduleClass"]);

				foreach ($parents as $className) {
					if(class_exists($className)){
						$moduleParent = (new $className())->module;
						$modules[$className] = Yii::t('easyii/'.$moduleParent->name, $moduleParent->title);
					}
				}
			}
		}
		return $modules;
	}

	public function getParent()
	{
		if($this->parent_id && ($pClassName = $this->parent_class) && ($parent = $pClassName::findOne($this->parent_id))){
			return $parent;
		}
	}

	public function search($params, $filters = null, $pagination = 20, $sort = null)
	{
		$query = static::find();

		if (!empty($filters)) {
			if (!empty($filters['where'])) {
				$query->andWhere($filters['where']);
			}
			if(!empty($filters['radius'])){
				$query->andWhere(['item_id' => static::searchByRadius($query, $filters['radius'])]);
				if($query->count()){
					$query->orderBy(new Expression('FIELD (item_id, ' . implode(',', static::searchByRadius($query, $filters['radius'])) . ')'));
				}
			}
		}

		$dataProvider = new ActiveDataProvider(['query' => $query]);
		$dataProvider->sort->defaultOrder = ['status' => SORT_ASC, 'item_id' => SORT_DESC];
		$dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', $pagination);

		if (!($this->load($params))) {
			return $dataProvider;
		}

		$dataProvider->query
			->andFilterWhere(['parent_class' => $this->parent_class])
			// ->andFilterWhere(['parent_id' => $this->parent_id])
			->andFilterWhere(['like', 'title', $this->title])
			->andFilterWhere(['like', 'description', $this->description])
			->andFilterWhere(['country_id' => $this->country_id])
			->andFilterWhere(['region_id' => $this->region_id])
			->andFilterWhere(['city_id' => $this->city_id])
			->andFilterWhere(['created_by' => $this->created_by])
			->andFilterWhere(['updated_by' => $this->updated_by])
			->andFilterWhere(['owner' => $this->owner])
			->andFilterWhere(['status' => $this->status])
			->andFilterWhere(['>=', 'time', $this->time])
			->andFilterWhere(['<', 'time_to', $this->time_to]);

		return $dataProvider;
	}

	protected function searchByRadius($query, $filters = [])
	{
		if(
			$filters['latitude'] &&
			$filters['longitude'] &&
			$filters['distance']
		){
			$radiusForCompare = ($filters['distance'] / 1000) / 111.111;

			$cosRadCityLat = cos($filters['latitude'] * pi() / 180);
			$sinRadCityLat = sin($filters['latitude'] * pi() / 180);

			$query->andWhere(new Expression("
				DEGREES(ACOS(LEAST(1.0, {$cosRadCityLat})
				* COS(RADIANS(" . static::tableName() . ".latitude))
				* COS(RADIANS({$filters['longitude']} - " . static::tableName() . ".longitude))
				+ {$sinRadCityLat}
				* SIN(RADIANS(" . static::tableName() . ".latitude)))) < {$radiusForCompare}
			"));

			$sortByDistance = [];

			foreach ($query->all() as $static) {

				$cache = Yii::$app->cache;
				$key = static::tableName() . '_' . $static->primaryKey;

				// $distance = $cache->get($key);
				// if(!$distance){
					$distance = self::calculateDistance($filters['latitude'], $filters['longitude'], $static->latitude, $static->longitude, true);
					$cache->set($key, $distance, 0);
				// }

				$sortByDistance[$distance] = $static->primaryKey;
			}

			ksort($sortByDistance);
		}

		return $sortByDistance;
	}

	protected static function calculateDistance($lat1, $lon1, $lat2, $lon2, $round = false) {

		$lat1=deg2rad($lat1);$lon1=deg2rad($lon1);$lat2=deg2rad($lat2);$lon2=deg2rad($lon2);

		$distance = 6378137 * acos( cos( $lat1 ) * cos( $lat2 ) * cos( $lon1 - $lon2 ) + sin( $lat1 ) * sin( $lat2 ) );

		return $round ? round($distance) : $distance;
	}

	public function distance()
	{
		$distance = Yii::$app->cache->get(static::tableName() . '_' . $this->primaryKey);
		$distance = $distance > 999
		? round($distance/1000,1) . 'км'
		: round($distance) . 'м';
		return $distance;
	}
}