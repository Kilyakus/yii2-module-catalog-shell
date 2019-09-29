<?php
namespace kilyakus\shell\directory\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use bin\admin\models\CField;

class FilterForm extends Model
{
    public $transferClasses = [];

    public $category;
    public $country;
    public $region;
    public $city;
    public $street;
    public $number;
    public $priceFrom;
    public $priceTo;
    public $storageFrom;
    public $storageTo;
    public $text;
    
    public $filters = [];

    public $sort;

    public $_fields = [];

    public function __get($name){
       if (array_key_exists($name, $this->_fields))
           return $this->_fields[$name];

       return parent::__get($name);
   }

   public function __set($name, $value){
       if (array_key_exists($name, $this->_fields))
           $this->_fields[$name] = $value;

       else parent::__set($name, $value);
   }

   public function put($attribute)
   {
        $this->_fields[$attribute] = null;
        $this->__set($attribute, null);
   }

   public function __construct(){
    
        parent::__construct();

        self::put('category_id');

        self::put('region_id');

        foreach (CField::find()->where(['class' => 'bin\admin\modules\catalog\models\Category'])->all() as $key => $field) {
            self::put($field->name);
        }
   }

    public static function tableName()
    {
        return 'catalog_item_filters';
    }

    public function rules()
    {
        return [
            [['category_id','country','region_id','city','street','number','priceFrom', 'priceTo', 'storageFrom', 'storageTo'], 'number', 'min' => 0],
            [['text'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'country' => Yii::t('easyii/catalog','Country'),
            'region' => Yii::t('easyii/catalog','Region'),
            'city' => Yii::t('easyii/catalog','City'),
            'street' => Yii::t('easyii/catalog','Street'),
            'number' => Yii::t('easyii/catalog','Number'),
            'priceFrom' => 'Price from',
            'priceTo' => 'Price to',
            'storageFrom' => 'Storage from',
            'storageTo' => 'Storage to',
        ];
    }

    public function parse()
    {
        $fields = ArrayHelper::getColumn(CField::find()->where(['class' => 'bin\admin\modules\catalog\models\Category'])->all(),'name');
        
        $get = Yii::$app->request->get('FilterForm');
        
        foreach ($fields as $field => $value) {
            $this->_fields[$value] = $get[$value];
            $this->filters[$value] = $get[$value];
        }

        foreach ($this->filters as $f => $filter) {
            if(!$filter){
                unset($this->filters[$f]);
            }
        }

        if ($this->category_id) {
            $this->filters['category'] = $this->category_id;
        }
       
        if ($this->country) {
            $this->filters['country'] = $this->country;
        }

        if ($this->region_id) {
            $this->filters['region'] = $this->region_id;
        }

        if ($this->city) {
            $this->filters['city'] = $this->city;
        }

        if ($this->street) {
            $this->filters['street'] = $this->street;
        }

        if ($this->number) {
            $this->filters['number'] = $this->number;
        }

        if ($this->priceFrom > 0 || $this->priceTo > 0) {
            $this->filters['price'] = [$this->priceFrom, $this->priceTo];
        }

        if ($this->storageFrom > 0 || $this->storageTo > 0) {
            $this->filters['storage'] = [$this->storageFrom, $this->storageTo];
        }

        return $this->filters;
    }
}