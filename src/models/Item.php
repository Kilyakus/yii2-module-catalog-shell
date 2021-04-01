<?php
namespace kilyakus\shell\directory\models;

use Yii;
use bin\admin\components\API;
use yii\db\Expression;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use bin\admin\models\Album;
use bin\admin\models\Photo;
use bin\admin\models\Comment;
use bin\admin\models\CType;
use bin\admin\models\CField;
use bin\admin\models\CContact;
use kilyakus\package\favorite\models\FavoriteAssign;
use bin\user\models\User;
use bin\user\models\UserSearch;
use yii\data\ActiveDataProvider;
use kilyakus\helper\media\Image;

use kilyakus\web\widgets as Widget;

class Item extends \kilyakus\shell\directory\models\db\ItemActiveRecord
{
	public function init()
	{
		$this->module = API::getModule($this::MODULE_NAME);

		$moduleDirectory = 'bin\admin\modules\\';

		foreach ($this->transferClasses as $group => $items) {
			if(is_array($items)){
				foreach ($items as $class) {
					self::put($class);
					$this->{$class} = $moduleDirectory . $this->module->name . '\\' . $group . '\\' . $class;
					// class_alias($this->{$class}, $class);
				}
			}
		}

		parent::init();
	}

	// public $category;
	// public $storageFrom;
	// public $storageTo;
	// public $text;

	// public $filters = [];

	// public $sort;

	// public $_fields = [];

	public function __construct()
	{
		parent::__construct();

		static::put('date_range');

		static::put('priceFrom');
		static::put('priceTo');

		static::put('nearby');
		static::put('radius');
		static::put('distance');

		$cache = Yii::$app->cache;
		$key = static::tableName().'_tree';

		$fields = $cache->get($key);
		if(!$fields){
			$fields = CField::find()->where(['class' => $this->categoryClass, 'status' => 1])->all();
			$fields = ArrayHelper::getColumn($fields,'name');
			$fields = array_unique($fields);
			$cache->set($key, $fields, 3600);
		}

		foreach ($fields as $field) {
			static::put($field);
		}
	}

	public function rules()
	{
		$rules = parent::rules();

		$cache = Yii::$app->cache;
		$key = static::tableName().'_tree';

		$fields = $cache->get($key);

		foreach ($fields as $field) {
			$rules[] = [$field, 'safe'];
		}

		return $rules;
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

	public function archive()
	{
		if($this->status == static::STATUS_ARCHIVE){
			$this->status = static::STATUS_OFF;
		}else{
			$this->status = static::STATUS_ARCHIVE;
		}
		$this->update();
	}

	public function beforeSave($insert)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		if($this->isNewRecord)
		{
			if(empty($this->time)){
				$this->time = time();
			}
			if(empty($this->time_to) && time() >= $this->time){
				$this->time_to = time();
			}
		}

		if(!IS_MODER){
			if($this->isAttributeChanged('owner') && $this->owner == 1){
				$this->owner = Yii::$app->user->identity->id;
			}else{
				$this->owner = $this->oldAttributes['owner'];
			}
		}

		if (parent::beforeSave($insert))
		{
			$this->time		=	$this->validDate($this->time) ?
								$this->time :
								(new \DateTime($this->time))->format('U');
			$this->time_to	=	$this->validDate($this->time_to) ?
								$this->time_to :
								(new \DateTime($this->time_to))->format('U');

			if(!empty($this->module->settings['submoduleClass'])){
				$this->parent_class = $this->module->settings['submoduleClass'];
			}elseif(!empty($this->module->settings['subcategoryClass']) && empty($this->module->settings['submoduleClass'])){
				$this->parent_class = $this->module->settings['subcategoryClass'];
			}

			if(!$this->category_id || (!is_object($this->category_id) && !is_array($this->category_id))){
				if(!$this->category_id){
					$this->category_id = new \stdClass();
				}else{
					$this->category_id = [$this->category_id];
				}
			}

			$this->category_id = json_encode($this->category_id);

			if($this->isNewRecord || !$this->created_by){

				$this->created_by = Yii::$app->user->identity->id;
			}

			$this->updated_by = Yii::$app->user->identity->id;

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

		foreach($this->data as $name => $value){
			if(!is_array($value) && !is_object($value)){

				$this->insertDataValue($name, $value);

			} else {

				foreach($value as $arrayItem){
					$this->insertDataValue($name, $arrayItem);
				}
			}
		}

		$this->parseContacts();

		$ItemContacts::deleteAll(['item_id' => $this->primaryKey]);

		foreach($this->contacts as $name => $value){
			if(!is_array($value) && !is_object($value)){

				$this->insertContactsValue($name, $value);

			} else {

				foreach($value as $arrayItem){
					$this->insertContactsValue($name, $arrayItem);
				}
			}
		}

		if(!$this->image && !empty($this->photos))
		{
			var_dump($this->photos);die;
			Yii::$app->db->createCommand(
				'UPDATE ' . static::tableName() . ' SET image=:image WHERE ' . static::primaryKey()[0] . '=:id',
				[
					'id' => $this->primaryKey,
					'image' => $this->photos[0]->image,
				]
			)->execute();
		}

	}

	public function afterFind()
	{
		parent::afterFind();

		$this->parseCategories();
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
	}

	private function insertDataValue($name, $value)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		$this->insertData($ItemData::tableName(), [
			'item_id'	=> $this->primaryKey,
			'name'		=> $name,
			'value'		=> $value,
		]);
	}

	private function parseData(){
		$this->data = $this->data !== '' ? json_decode($this->data) : [];
	}

	private function insertContactsValue($name, $value)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		$this->insertData($ItemContacts::tableName(), [
			'item_id'	=> $this->primaryKey,
			'name'		=> $name,
			'value'		=> $value,
		]);
	}

	private function parseContacts(){
		$this->contacts = $this->contacts !== '' ? json_decode($this->contacts) : [];
	}

	private function parseCategories(){
		if(ctype_digit($this->category_id))
		{
			Yii::$app->db->createCommand('UPDATE ' . $this::tableName() . ' SET category_id=:category WHERE ' . $this::primaryKey()[0] . '=:id', ['id' => $this->primaryKey, 'category' => '["' . $this->category_id . '"]'])->execute();
		}
		$this->category_id = $this->category_id !== '' ? json_decode($this->category_id) : [];
	}

	private function insertData($dbname, $data = [])
	{
		return Yii::$app->db->createCommand()->insert($dbname, $data)->execute();
	}

	public function getAllStatusesArray()
	{
		return [
			static::STATUS_OFF		=>	Yii::t('easyii', 'Pending moderation') . '&nbsp;' . Widget\Badge::widget(['label' => static::find()->where(['status' => static::STATUS_OFF])->count(), 'float' => 'right']),
			static::STATUS_ON		=>	Yii::t('easyii', 'Moderated') . '&nbsp;' . Widget\Badge::widget(['label' => static::find()->where(['status' => static::STATUS_ON])->count(), 'float' => 'right']),
			static::STATUS_ARCHIVE	=>	Yii::t('easyii', 'Archived') . '&nbsp;' . Widget\Badge::widget(['label' => static::find()->where(['status' => static::STATUS_ARCHIVE])->count(), 'float' => 'right']),
			static::STATUS_COPY		=>	Yii::t('easyii', 'Copied') . '&nbsp;' . Widget\Badge::widget(['label' => static::find()->where(['status' => static::STATUS_COPY])->count(), 'float' => 'right']),
		];
	}

	public function getCategory()
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		return $Category::find()->where(['category_id' => ($this->category_id ? $this->category_id : Yii::$app->request->get('id'))])->one();
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

	public function getPhotos($filters = null)
	{
		return $this->hasMany(Photo::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->orderBy(['main' => SORT_DESC, 'order_num' => SORT_DESC]);
	}

	public function getPhotosReview()
	{
		return $this->hasMany(Photo::className(), ['item_id' => 'item_id'])->where(['class' => self::className()])->andWhere(['is', 'field_instance', new \yii\db\Expression('null')])->orderBy(['main' => SORT_DESC, 'order_num' => SORT_DESC]);
	}

	public function getVideos()
	{
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

	public function getMembers()
	{
		$searchModel  = \Yii::createObject(FavoriteAssign::className());
		$dataProvider = $searchModel->search(\Yii::$app->request->get());
		$dataProvider->query->andFilterWhere([
			'and',
				['class'		=> $this::className()],
				['item_id'		=> $this->primaryKey],
				['owner_class'	=> User::className()],
		]);

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
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

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
		$dataProvider->pagination->pageSize = (Yii::$app->request->get('per-page') ?? $pagination);

		if (!($this->load($params))) {
			return $dataProvider;
		}

		// if(!empty($this->category_id))
		// {
		// 	$query->joinWith('categories')->andFilterWhere(['IN', $Category::tableName() . '.category_id', $this->category_id]);
		// }

		$this->filterFields($query, $params['Item']);

		$this->filterCategory($query);

		$this->filterWhereMultiple($query, 'country_id');
		$this->filterWhereMultiple($query, 'region_id');
		$this->filterWhereMultiple($query, 'city_id');

		$this->filterWhereMultiple($query, 'status');

		$this->time		=	$this->validDate($this->time) ?
							$this->time :
							(new \DateTime($this->time))->format('U');
		$this->time_to	=	$this->validDate($this->time_to) ?
							$this->time_to :
							(new \DateTime($this->time_to))->format('U');

		$dataProvider->query
			->andFilterWhere(['like', 'title', $this->title])
			->andFilterWhere(['like', 'description', $this->description])
			->andFilterWhere(['or', ['in', 'status', $this->status],['status' => $this->status]])

			->andFilterWhere(['parent_class'	=> $this->parent_class])
			// ->andFilterWhere(['parent_id'		=> $this->parent_id])
			->andFilterWhere(['created_by'		=> $this->created_by])
			->andFilterWhere(['updated_by'		=> $this->updated_by])
			->andFilterWhere(['owner'			=> $this->owner])
			->andFilterWhere(['price'			=> $this->price])

			->andFilterWhere(['>=',	'time',		$this->time])
			->andFilterWhere(['<=',	'time_to',	$this->time_to])
			->andFilterWhere(['>=',	'price',	$this->priceFrom])
			->andFilterWhere(['<=',	'price',	$this->priceTo])

			->andFilterWhere(['like', 'latitude', $this->latitude])
			->andFilterWhere(['like', 'longitude', $this->longitude]);

		// if(!$this->priceFrom){
		// 	$query->andFilterWhere(['<=', 'price', (int)$this->priceTo]);
		// } elseif(!$this->priceTo) {
		// 	$query->andFilterWhere(['>=', 'price', (int)$this->priceFrom]);
		// } else {
		// 	$query->andFilterWhere(['between', 'price', (int)$this->priceFrom, (int)$this->priceTo]);
		// }

		return $dataProvider;
	}

	protected function filterCategory($query)
	{
		if(!empty($this->category_id))
		{
			$filter = ['or'];
			foreach ($this->category_id as $categoryId)
			{
				array_push($filter, ['like', 'category_id', '"' . $categoryId . '"']);
			}
			$query->andFilterWhere($filter);
		}
		return $query;
	}

	protected function filterWhereMultiple($query, $field)
	{
		if(!empty($this->{$field}))
		{
			$filter = ['or'];
			if(is_array($this->{$field}) || is_object($this->{$field}))
			{
				foreach ($this->{$field} as $value) {
					array_push($filter, [$field => $value]);
				}
			}else{
				array_push($filter, [$field => $this->{$field}]);
			}
			$query->andFilterWhere($filter);
		}
		return $query;
	}

	protected function filterFields($query, $params)
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		if(!empty($params))
		{
			$unset = [
				'category_id',
				'title', 'description',
				'latitude', 'longitude', 'radius',
				'created_by', 'updated_by', 'owner',
				'status',
				'date_range', 'time', 'time_to',
				'country_id', 'region_id', 'city_id',
			];
			foreach ($params as $param => $values)
			{
				if(in_array($param, $unset))
				{
					unset($params[$param]);
				}
			}

			$subQuery = $ItemData::find();

			foreach($params as $field => $value)
			{
				if(empty($value))
				{
					unset($params[$field]);
				}
				elseif(!is_array($value))
				{
					$subQuery->where(['and', ['name' => $field], ['like', 'value', $value]]);
				}
				elseif($value['min'] && $value['max'])
				{

					$subQuery->where(['>=', 'value', (int)$value['min']])->andFilterWhere(['<=', 'value', (int)$value['max']])->andFilterWhere(['name' => $field]);
				}
				else
				{
					$subQuery->where(['and', ['name' => $field], ['in', 'value', $value]]);
				}

				$check = $subQuery->groupBy('item_id')->all();
				$query->andWhere(['item_id' => ArrayHelper::getColumn($check,'item_id')]);
			}
		}
		return $query;
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

	protected static function calculateDistance($lat1, $lon1, $lat2, $lon2, $round = false)
	{
		$lat1 = deg2rad($lat1);
		$lon1 = deg2rad($lon1);
		$lat2 = deg2rad($lat2);
		$lon2 = deg2rad($lon2);

		$distance = 6378137 *
			acos(
				cos( $lat1 ) *
				cos( $lat2 ) *
				cos( $lon1 - $lon2 ) +
				sin( $lat1 ) *
				sin( $lat2 )
			);

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

	public static function getMinTime()
	{
		return static::find()->orderBy('time ASC')->one()->time;
	}

	public static function getMaxTime()
	{
		return static::find()->orderBy('time_to DESC')->one()->time_to;
	}

	public static function getMinPrice()
	{
		return static::find()->orderBy('price ASC')->one()->price;
	}

	public static function getMaxPrice()
	{
		return static::find()->orderBy('price DESC')->one()->price;
	}
}
