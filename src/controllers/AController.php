<?php
namespace kilyakus\shell\directory\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use bin\admin\models\CField;
use bin\admin\models\Photo;
use kilyakus\helper\media\Image;
use bin\forum\src\models\Category as ForumCategory;
use bin\forum\src\models\Forum;

class AController extends \bin\admin\controllers\CategoryController
{
    public $transferClasses = [];

    public $categoryClass;
    public $categoryAssign;
    public $itemClass;
    public $moduleName;

    public $image;

    public function actionEdit($id)
    {
        $this->view->params['submenu'] = true;

        return parent::actionEdit($id);
    }

    public function actionFields($id = null)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!($model = $Category::findOne($id))){
            $model = new $Category;
        }
        
        Yii::$app->user->returnUrl = $_SERVER['REQUEST_URI'];

        return $this->render('@kilyakus/shell/directory/views/a/fields', [
            'model' => $model,
        ]);
    }

    public function actionEngine($id = null)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!($model = $Category::findOne($id))){
            $model = new $Category;
        }

        return $this->render('@kilyakus/shell/directory/views/a/engine', [
            'model' => $model,
        ]);
    }

    public function actionTypes($id)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!($model = $Category::findOne($id))){
            return $this->redirect(['/admin/'.$this->module->id]);
        }

        return $this->render('@kilyakus/shell/directory/views/a/types', [
            'model' => $model,
        ]);
    }

    public function actionContacts($id = null)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!($model = $Category::findOne($id))){
            $model = new $Category;
            // return $this->redirect(['/admin/'.$this->module->id]);
        }

        return $this->render('@kilyakus/shell/directory/views/a/contacts', [
            'model' => $model,
        ]);
    }

    public function actionForums($id,$forumid = null)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        if(!($model = $Category::findOne($id))){
            return $this->redirect(['/admin/'.$this->module->id]);
        }
        if(!class_exists(get_class(new ForumCategory))){
            return $this->redirect(['/admin/'.$this->module->id.'/a/edit/'.$id]);
        }
        $items = [];        
        $forums = ArrayHelper::map(ForumCategory::find()->all(),'id','name');
        foreach ($forums as $key => $forum) {
            $childrens = ArrayHelper::map(Forum::find()->where(['category_id' => $key])->all(),'id','name');
            $chitems = [];
            foreach ($childrens as $q => $children) {
                $chitems[$q] = ['name' => $children];
            }
            $items[$key] = ['name' => $forum, 'children' => $chitems];
        }

        if($forumid){

            $forums = explode(',',$model->forums);

            $key = array_search($forumid, $forums);

            if(in_array($forumid,$forums)){
                if ($key !== false)
                {
                    unset($forums[$key]);
                }
            }else{
                array_push($forums, $forumid);
            }        

            $model->forums = implode(',',array_unique($forums));
            $model->update();

            return $this->renderAjax('@kilyakus/shell/directory/views/a/forums', [
                'items' => $items,
                'model' => $model,
            ]);

        }

        return $this->render('@kilyakus/shell/directory/views/a/forums', [
            'items' => $items,
            'model' => $model,
        ]);
    }

    public function actionUpload($id,$slug = null)
    {
        foreach ($this->transferClasses as $item => $class){if(!is_array($class)){${$item} = $class;}}

        $success = null;

        $image = Photo::find()->where(['item_id' => $id,'description' => $slug])->one();

        if($image->image){
            Photo::deleteAll(['photo_id' => $image->photo_id]);
            @unlink(Yii::getAlias('@webroot').$image->image);
        }
        
        $photo = new Photo;
        $photo->class = $Category;
        $photo->item_id = $id;
        $photo->image = UploadedFile::getInstance($photo,'image');
        $photo->description = $slug;

        if($photo->image && $photo->validate(['image'])){

            $photo->image = Image::upload($photo->image, 'fields', Photo::PHOTO_MAX_WIDTH);

            if($photo->image){
                if($photo->save()){
                    $success = [
                        'message' => Yii::t('easyii', 'Photo uploaded'),
                        'photo' => [
                            'id' => $photo->primaryKey,
                            'image' => $photo->image,
                            'thumb' => Image::thumb($photo->image, Photo::PHOTO_THUMB_WIDTH, Photo::PHOTO_THUMB_HEIGHT),
                            'description' => ''
                        ]
                    ];
                }
                else{
                    @unlink(Yii::getAlias('@webroot') . str_replace(Url::base(true), '', $photo->image));
                    $this->error = Yii::t('easyii', 'Create error. {0}', $photo->formatErrors());
                }
            }
            else{
                $this->error = Yii::t('easyii', 'File upload error. Check uploads folder for write permissions');
            }
        }
        else{
            $this->error = Yii::t('easyii', 'File is incorrect');
        }

        return $this->formatResponse($success);
    }

    public function actionParentCategory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (class_exists($_POST['depdrop_parents'][0])) {
            $id = end($_POST['depdrop_parents']);
            $class = $_POST['depdrop_parents'][0];
            $list = $class::find()->all();
            $selected  = null;
            if ($class != null && count($list) > 0) {
                $selected = '';
                foreach ($list as $i => $module) {
                    $out[] = ['id' => $module['category_id'], 'name' => $module['title']];
                    if ($i == 0) {
                        $selected = $module['category_id'];
                    }
                }
                return ['output' => $out, 'selected' => $selected];
            }
        }
        return ['output' => '', 'selected' => ''];
    }
}