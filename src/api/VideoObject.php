<?php
namespace kilyakus\shell\directory\api;

use Yii;
use bin\admin\components\API;
use yii\helpers\Html;
use yii\helpers\Url;

class VideoObject extends \kilyakus\components\api\Object
{
    public $moduleName;
    public $file;
    public $title;
    public $description;

    public function box($width, $height){
        $img = Html::img($this->thumb($width, $height));
        $a = Html::a($img, $this->file, [
            'class' => 'easyii-box',
            'rel' => 'catalog-'.$this->model->item_id,
            'title' => $this->description
        ]);
        return LIVE_EDIT ? API::liveEdit($a, $this->editLink) : $a;
    }

    public function getEditLink(){
        return Url::to(['/admin/' . $this->moduleName . '/items/videos', 'id' => $this->model->item_id]).'#video-'.$this->id;
    }
}