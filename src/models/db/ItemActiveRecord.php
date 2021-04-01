<?php
namespace kilyakus\shell\directory\models\db;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;
use yii\behaviors\SluggableBehavior;
use kilyakus\modules\behaviors\CacheFlush;
use kilyakus\shell\directory\behaviors\CategoriesBehavior;
use kilyakus\shell\directory\behaviors\FieldsBehavior;
use kilyakus\shell\directory\behaviors\GeoBehavior;
use kilyakus\shell\directory\behaviors\ParseBehavior;
use kilyakus\package\gui\behaviors\GuiBehavior;
use kilyakus\package\seo\behaviors\SeoBehavior;
use kilyakus\package\translate\behaviors\TranslateBehavior;
use kilyakus\package\taggable\behaviors\Taggable;
use kilyakus\cutter\behaviors\CutterBehavior;
use kilyakus\widget\daterange\DateRangeBehavior;
use bin\admin\modules\chat\behaviors\ChatBehavior;
use bin\admin\models\Album;
use bin\admin\models\Photo;
use bin\admin\models\CField;

class ItemActiveRecord extends \kilyakus\modules\components\ActiveRecord
{
	public $transferClasses = [];

	public $module;

	const STATUS_OFF = 0;
	const STATUS_ON = 1;
	const STATUS_ARCHIVE = 2;
	const STATUS_COPY = 3;

	public static function tableName()
	{
		return 'catalog_items';
	}

	public function rules()
	{
		$modelName = (new \ReflectionClass($this))->getShortName();

		$rules = [];

		if($this->module->settings['enableCategory']){
			$rules[] = ['category_id', 'required', 'message' => Yii::t('easyii', 'Select category')];
		}

		$rules[] = ['title', 'trim'];
		$rules[] = [['parent_class','title','permission','gradient','gradient_to','latitude','longitude'], 'string', 'max' => 255];
		$rules[] = ['parent_id', 'default'];
		$rules[] = [['preview','image',], 'image'];
		$rules[] = ['description', 'safe'];
		$rules[] = ['price', 'number'];
		$rules[] = ['discount', 'integer', 'max' => 100];
		$rules[] = [['type_id','views','country_id','region_id','city_id','street_id','street_number_id', 'available', 'created_by', 'updated_by', 'owner', 'status'], 'integer'];
		$rules[] = [['time','time_to'], 'default', 'value' => time()];
		// $rules[] = [['time','time_to'], 'safe'];
		$rules[] = ['slug', 'match', 'pattern' => self::$SLUG_PATTERN, 'message' => Yii::t('easyii', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')];
		$rules[] = ['slug', 'default', 'value' => null];
		$rules[] = ['status', 'default', 'value' => self::STATUS_ON];
		$rules[] = ['owner', 'integer'];
		$rules[] = ['webcams', 'safe'];
		// $rules[] = [['city_id'], 'required', 'when' => function ($model) {
		// 	return empty($model->city_id);
		// },'whenClient' => 'function(attribute,value){
		// 	if($("#' . Html::getInputId($this, 'locality_id') . '").val()==""){
		// 		$(".field-item-locality_id .alert.alert-danger").remove()
		// 		$(".field-item-locality_id").append("<div class=\"alert alert-danger mt-15 mb-0\">' . Yii::t('easyii', 'You must enter the marker on the map') . '<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">×</span></button></div>");
		// 	}
		// 	return $("#' . Html::getInputId($this, 'locality_id') . '").val()!=="";
		// }','message' => Yii::t('easyii', 'You must select a city to continue')];
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
			$rules[] = [['priceFrom', 'priceTo'], 'safe'];
		}

		$rules[] = [['date_range'], 'match', 'pattern' => '/^.+\s\-\s.+$/'];


		$rules[] = ['translations', 'safe'];

		$rules[] = ['tagNames', 'safe'];

		return $rules;
	}

	public function attributeLabels()
	{
		return [
			'parent_class'	=>	Yii::t('easyii', 'Module'),

			'category_id'	=>	Yii::t('easyii', 'Category'),
			'title'			=>	Yii::t('easyii', 'Title'),
			'preview'		=>	Yii::t('easyii', 'Change main photo'),
			'image'			=>	Yii::t('easyii', 'Upload background and main photo'),
			'description'	=>	Yii::t('easyii', 'Description'),
			'available'		=>	Yii::t('easyii/' . $this->module->name, 'Available'),
			'price'			=>	Yii::t('easyii/' . $this->module->name, 'Price'),
			'discount'		=>	Yii::t('easyii/' . $this->module->name, 'Discount'),
			'time'			=>	Yii::t('easyii', 'Date'),
			'time_to'		=>	Yii::t('easyii', 'Date'),
			'slug'			=>	Yii::t('easyii', 'Slug'),
			'created_by'	=>	Yii::t('easyii', 'Created by'),
			'updated_by'	=>	Yii::t('easyii', 'Updated by'),
			'owner'			=>	IS_MODER ? Yii::t('easyii/' . $this->module->name, 'Owner') : Yii::t('easyii', 'Authorize yourself as the owner'),
			'gradient'		=>	Yii::t('easyii/' . $this->module->name, 'Choose Color'),
			'gradient_to'	=>	Yii::t('easyii/' . $this->module->name, 'To Color'),

			'continent'		=>	Yii::t('easyii/' . $this->module->name, 'Continent'),
			'country'		=>	Yii::t('easyii/' . $this->module->name, 'Country'),
			'region'		=>	Yii::t('easyii/' . $this->module->name, 'Region'),
			'city'			=>	Yii::t('easyii/' . $this->module->name, 'City'),
			'street'		=>	Yii::t('easyii/' . $this->module->name, 'Street'),
			'number'		=>	Yii::t('easyii/' . $this->module->name, 'Number'),

			'latitude'		=>	Yii::t('easyii', 'Latitude'),
			'longitude'		=>	Yii::t('easyii', 'Longitude'),

			'tagNames'		=>	Yii::t('easyii', 'Tags'),
		];
	}

	public function behaviors()
	{
		foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

		return [
			'cacheflush' => [
                'class' => CacheFlush::className(),
                'key' => [static::tableName().'_tree']
            ],
			'categoriesBehavior'	=>	[
				'class'					=>	CategoriesBehavior::className(),
				'classCategory'			=>	$Category,
				'classCategoryAssign'	=>	$CategoryAssign,
			],
			'fieldsBehavior'		=>	[
				'class'					=>	FieldsBehavior::className(),
				'fieldsClass'			=>	CField::className(),
				'categoryClass'			=>	$Category,
			],
			'dataBehavior'		=>	[
				'class'					=>	ParseBehavior::className(),
				'attribute'				=> 'data',
				'categoryClass'			=>	$ItemData,
			],
			'contactsBehavior'		=>	[
				'class'					=>	ParseBehavior::className(),
				'attribute'				=> 'contacts',
				'categoryClass'			=>	$ItemContacts,
			],
			'geoBehavior'	=>	[
				'class'					=>	GeoBehavior::className(),
			],
			'albumBehavior'			=>	[
				'class'					=>	GuiBehavior::className(),
				'model'					=>	Album::className(),
				'isRoot'				=>	IS_USER,
				'identity'				=>	Yii::$app->user->identity->id,
			],
			'photoBehavior'			=>	[
				'class'					=>	GuiBehavior::className(),
				'model'					=>	Photo::className(),
				'isRoot'				=>	IS_MODER,
				'identity'				=>	Yii::$app->user->identity->id,
			],
			'dateRangeBehavior'		=>	[
				'class'					=>	DateRangeBehavior::className(),
				'attribute'				=>	'date_range',
				'dateStartAttribute'	=>	'time',
				'dateEndAttribute'		=>	'time_to',
				// 'dateStartFormat'	=>	Yii::$app->formatter->datetimeFormat,
				// 'dateEndFormat'		=>	Yii::$app->formatter->datetimeFormat,
			],
			'seoBehavior'			=>	SeoBehavior::className(),
			'translateBehavior'		=>	TranslateBehavior::className(),
			'taggabble'				=>	Taggable::className(),
			'sluggable'				=>	[
				'class'					=>	SluggableBehavior::className(),
				'attribute'				=>	'title',
				'ensureUnique'			=>	true
			],
			'chatsBehavior'	=>	[
				'class'					=>	ChatBehavior::className(),
				'classChat'				=>	\bin\admin\modules\chat\models\Group::className(),
				'classChatAssign'		=>	$ItemChats,
				'identity'				=>	Yii::$app->user->identity->id,
			],
		];
	}
}
