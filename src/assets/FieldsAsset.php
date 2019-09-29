<?php
namespace kilyakus\shell\directory\assets;

class FieldsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@kilyakus/shell/directory/media';
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