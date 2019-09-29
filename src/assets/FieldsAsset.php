<?php
namespace kilyakus\shell\directory\assets;

class FieldsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bin/admin/relations/catalog/media';
    public $css = [
        'css/fields.css',
    ];
    public $js = [
        'js/fields.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}