<?php
namespace kilyakus\shell\directory\api;

use Yii;
use bin\admin\components\API;
use yii\helpers\Html;
use yii\helpers\Url;

class PhotoObject extends \kilyakus\components\api\Object
{
    public $moduleName;
    public $image;
    public $title;
    public $description;

    public function box($width, $height){
        $img = Html::img($this->thumb('image', $width, $height));
        $a = Html::a($img, $this->image, [
            'class' => 'easyii-box',
            'rel' => 'catalog-'.$this->model->item_id,
            'title' => $this->description
        ]);
        return LIVE_EDIT ? API::liveEdit($a, $this->editLink) : $a;
    }

    public function getEditLink(){
        return Url::to(['/admin/' . $this->moduleName . '/items/photos', 'id' => $this->model->item_id]).'#photo-'.$this->id;
    }
}