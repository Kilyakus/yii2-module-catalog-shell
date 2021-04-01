<?php
namespace kilyakus\shell\directory\widgets\DynamicFields;

use yii\web\AssetBundle;

class DynamicFieldsAsset extends AssetBundle
{
	public $depends = [
        'yii\web\YiiAsset',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';

        $this->js[] = 'vendor/leaflet/leaflet.js';
        $this->css[] = 'css/windy.css';
    }

    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];
}