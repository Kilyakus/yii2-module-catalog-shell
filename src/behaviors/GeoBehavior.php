<?php
namespace kilyakus\shell\directory\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use bin\admin\modules\geo\models\MapsContinent;
use bin\admin\modules\geo\models\MapsCountry;
use bin\admin\modules\geo\models\MapsRegion;
use bin\admin\modules\geo\models\MapsCity;
use bin\admin\modules\geo\models\MapsStreet;
use bin\admin\modules\geo\models\MapsStreetNumber;

class GeoBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }

    public function getContinent()
    {
        if(($country = $this->owner->country) && !$this->owner->continent_id)
        {
            $this->owner->continent_id = $this->owner->country->continent->primaryKey;
            $this->owner->update();
        }

        return $this->owner->hasOne(MapsContinent::className(), ['id' => 'continent_id']);
    }

    public function getCountry()
    {
        return $this->owner->hasOne(MapsCountry::className(), ['id' => 'country_id']);
    }

    public function getRegion()
    {
        return $this->owner->hasOne(MapsRegion::className(), ['id' => 'region_id']);
    }

    public function getCity()
    {
        return $this->owner->hasOne(MapsCity::className(), ['id' => 'city_id']);
    }

    public function getStreet()
    {
        return $this->owner->hasOne(MapsStreet::className(), ['id' => 'street_id']);
    }

    public function getStreetNumber()
    {
        return $this->owner->hasOne(MapsStreetNumber::className(), ['id' => 'street_number_id']);
    }

    public function getAddress()
    {
        $address = [];

        if($country = $this->owner->country)
        {
            $address[] = $country->name;
        }
        if($region = $this->owner->region)
        {
            $address[] = $region->name;
        }
        if($city = $this->owner->city)
        {
            $address[] = $city->name;
        }
        if($street = $this->owner->street)
        {
            $address[] = $street->name;
        }
        if($streetNumber = $this->owner->streetNumber)
        {
            $address[] = $streetNumber->name;
        }

        return implode(', ', $address);
    }

    public function beforeSave($insert)
    {
        if($this->owner->isAttributeChanged('city_id'))
        {
            $this->owner->country_id = $this->owner->city->country_id;
            $this->owner->region_id = $this->owner->city->region_id;
        }
    }
}