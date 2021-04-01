<?php
namespace kilyakus\package\taggable\actions;

use Yii;
use yii\web\Response;
use yii\helpers\Html;
use kilyakus\action\BaseAction as Action;
use kilyakus\package\taggable\models\Tag;

class SearchAction extends Action
{
    // public function run($id = null, $parent = null, $class = null)
    // {
    //     $categoryClass = $this->categoryClass;
    //     $categoryAssign = $this->categoryAssign;
    //     $itemClass = $this->itemClass;
    //     $chatClass = $this->chatClass;
    //     $chatClassAssign = $this->chatClassAssign;

    //     if(!($model = $categoryClass::findOne($id))){
    //         // return $this->redirect(['/' . $this->module->module->id . '/'.$this->module->id]);
    //     }

    //     if(Yii::$app->request->post()){

    //         if(Yii::$app->request->post('hasEditable'))
    //         {
    //             return self::update();
    //         }

    //         $item = Yii::$app->request->post('Item');

    //         if(!$chat = $chatClass::find()->where(['item_id' => $item['item_id']])->one()){
    //             $model = new \bin\admin\modules\chat\models\Group();
    //             if ($model->load(Yii::$app->request->post())) {
    //                 $model->adminId = Yii::$app->user->id;
    //                 $model->class = $itemClass;
    //                 if ($model->save()) {
    //                     self::chatAssign($model,$item['item_id']);

    //                     Yii::$app->session->setFlash('success','Комната создана');
    //                     return $this->redirect(['/admin/chat/message/groups', 'id' => $model->id]);
    //                 }
    //             }
    //         }else{
    //             return $this->redirect(['/admin/chat/message/groups','id' => $chat->chat_id]);
    //         }
    //     }

    //     if($id){
    //         $query['item_id'] = ArrayHelper::getColumn($categoryAssign::findAll(['category_id' => $id]),'item_id');
    //     }

    //     if($class){
    //         $query['parent_class'] = $this->module->settings['parentSubmodule'];
    //     }

    //     if($parent && Yii::$app->request->get('Item')['parent_id'] == 1){
    //         $query['parent_id'] = $parent;
    //     }

    //     if(Yii::$app->request->get('Item')['nearby'] == 1){
            
    //         $parentClass = $this->module->settings['parentSubmodule'];

    //         $parentModel = $parentClass::findOne($parent);

    //         $latitude = (float)substr($parentModel->latitude, 0, 5);
    //         $longitude = (float)substr($parentModel->longitude, 0, 5);

    //         if(substr($latitude, 0, 1) != '-'){
    //             $latPlus = ($latitude+0.9);
    //         }else{
    //             $latPlus = ($latitude-0.9);
    //         }

    //         if(substr($latitude, 0, 1) != '-'){
    //             $latMinus = ($latitude-0.9);
    //         }else{
    //             $latMinus = ($latitude+0.9);
    //         }

    //         if(substr($longitude, 0, 1) != '-'){
    //             $lngPlus = ($longitude+0.9);
    //         }else{
    //             $lngPlus = ($longitude-0.9);
    //         }

    //         if(substr($longitude, 0, 1) != '-'){
    //             $lngMinus = ($longitude-0.9);
    //         }else{
    //             $lngMinus = ($longitude+0.9);
    //         }

    //         $nearby = ArrayHelper::getColumn($itemClass::find()->where(['and',['parent_class' => $parentClass],['category_id' => $categoryAssign::findAll(['category_id' => $id])],['<=','latitude', $latPlus],['>=','latitude', $latMinus],['<=','longitude', $lngPlus],['>=','longitude', $lngMinus],])->all(),'item_id');
    //     }

    //     $searchModel  = \Yii::createObject($itemClass::className());
    //     $dataProvider = $searchModel->search(Yii::$app->request->get());
    //     if($query){
    //         $dataProvider->query->andFilterWhere($query);
    //     }
    //     if(count($nearby)){
    //         $dataProvider->query->andFilterWhere(['item_id' => $nearby]);
    //     }
    //     $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

    //     $data = new ActiveDataProvider([
    //         'query' => $itemClass::find()->orderBy(['status' => SORT_ASC, 'item_id' => SORT_DESC]),
    //     ]);

    //     $items = $data->models;
        
    //     return $this->render('@kilyakus/shell/directory/views/items/index', [
    //         'data' => $data,
    //         'dataProvider' => $dataProvider,
    //         'searchModel' => $searchModel,
    //         'model' => $model,
    //         'items' => $items,
    //         'breadcrumbs' => self::getBreadcrumbs($id),
    //         'parent' => $parent,
    //         'class' => $class,
    //     ]);
    // }
}