<?php
namespace kilyakus\shell\directory\controllers;

use Yii;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use bin\user\filters\AccessRule;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use bin\admin\components\API;
use kilyakus\imageprocessor\Image;
use kilyakus\modules\behaviors\StatusController;
use bin\admin\behaviors\SortableDateController;
use bin\admin\models\Season;
use bin\admin\models\CField;
use kilyakus\web\widgets\Select2;

class ItemsController extends \bin\admin\components\Controller
{
    public $settings;
    public $redirects;

    public function behaviors()
    {
        $itemClass = $this->itemClass;

        return [
            [
                'class' => StatusController::className(),
                'model' => $this->itemClass,
            ],
            [
                'class' => SortableDateController::className(),
                'model' => $this->itemClass,
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        
        $module = API::getModule($this->moduleName);

        $this->settings = $module->settings;
        $this->redirects = $module->redirects;
    }

    public function chatAssign($model, $ids)
    {
        $chatClass = $this->chatClass;

        if(is_array($ids)){
            foreach ($ids as $id) {
                self::chatAssign($model, $id);
            }
        }else{
            if(!$chat = $chatClass::find()->where(['item_id' => $ids])->one()){
                $chatAssign = new $chatClass;
                $chatAssign->item_id = $ids;
                $chatAssign->chat_id = $model->id;
                $chatAssign->save();
            }
        }
    }

    public function actionIndex($id = null, $parent = null, $class = null)
    {
        $categoryClass = $this->categoryClass;
        $categoryAssign = $this->categoryAssign;
        $itemClass = $this->itemClass;
        $chatClass = $this->chatClass;

        if(!($model = $categoryClass::findOne($id))){
            // return $this->redirect(['/' . $this->module->module->id . '/'.$this->module->id]);
        }

        if(Yii::$app->request->post()){

            $item = Yii::$app->request->post('Item');

            if(!$chat = $chatClass::find()->where(['item_id' => $item['item_id']])->one()){
                $model = new \bin\admin\modules\chat\models\Group();
                if ($model->load(Yii::$app->request->post())) {
                    $model->adminId = Yii::$app->user->id;
                    $model->class = $itemClass;
                    if ($model->save()) {
                        self::chatAssign($model,$item['item_id']);

                        Yii::$app->session->setFlash('success','Комната создана');
                        return $this->redirect(['/admin/chat/message/groups','id'=>$model->id]);
                    }
                }
            }else{
                return $this->redirect(['/admin/chat/message/groups','id'=>$chat->chat_id]);
            }
        }

        $query = ['item_id' => $categoryAssign::findAll(['category_id' => $id])];

        if($class){
            $query['parent_class'] = 'bin\admin\modules\\' . $class . '\models\Item';
        }

        if($parent){
            $query['parent_id'] = $parent;
        }
        
        // $items = $itemClass::find()->where($query)->orderBy(['status' => SORT_ASC])->all();

        $data = new ActiveDataProvider([
            'query' => $itemClass::find()->where($query)->orderBy(['status' => SORT_ASC, 'item_id' => SORT_DESC]),
        ]);

        $items = $data->models;
        
        return $this->rendering('index', [
            'data' => $data,
            'model' => $model,
            'items' => $items,
            'breadcrumbs' => self::getBreadcrumbs($id),
            'parent' => $parent,
            'class' => $class,
        ]);
    }

    public function actionItemsList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => ''],'parents' => []];
        $class = new $this->itemClass();

        if (!is_null($q)) {
            $query = new Query;
            $query = $query->select('item_id, title AS text')->from($class->tableSchema->name);
            if($id != null){
                $query->where(['and',['title', $q],['item_id' => $id]]);
            }else{
                $query->where(['like', 'title', $q]);
            }
            $query = $query->limit(10)->createCommand()->queryAll();
            $out['results'] = array_values($query);
        }
        return $out;
    }

    public function actionCreate($id = null, $parent = null, $class = null)
    {
        $module = $this->module->module->id;
        $categoryClass = $this->categoryClass;
        $itemClass = $this->itemClass;

        $post = Yii::$app->request->post('Item');

        $category = $post['category_id'] ? (is_array($post['category_id']) ? $post['category_id'][0] : $post['category_id']) : ($id ? $id : (Yii::$app->request->getQueryParam('id') ? Yii::$app->request->getQueryParam('id') : null)); 

        if(!($category = $categoryClass::findOne($category)) && $this->module->settings['enableCategory']){
            // return $this->redirect(['/admin/'.$this->module->id]);
        }

        $model = new $itemClass;

        $parents = $categoryClass::getParents($id) ? ArrayHelper::getColumn($categoryClass::getParents($id),'category_id') : [];

        array_push($parents,$category->category_id);

        $categories = $categoryClass::find()->where(['category_id' => $parents])->all();

        if ($model->load(Yii::$app->request->post())) {

            $model->category_id = $category->category_id;

            $model->created_by = Yii::$app->user->identity->id;

            $model->data = Yii::$app->request->post('Data');

            $model->contacts = Yii::$app->request->post('Contacts');

            if(gettype($post['time']) == 'string'){
                
                $model->time = strtotime($post['time']);
                $model->time_to = strtotime($post['time_to']);

            }

            $model->status = 0;

            if ($model->save()) {
                $this->flash('success', Yii::t('easyii/' . $this->moduleName, 'Item created'));
                // if(!Yii::$app->request->isAjax){
                    if($this->module->module->id != 'admin' && $this->redirects['create']){

                        $id = ($this->redirects['create'] == 'create') ? $category->category_id : $model->primaryKey;

                        return $this->redirect(['/' . $module . '/' . $this->module->id.'/items/' . $this->redirects['create'], 'id' => $id, 'class' => $class, 'parent' => $parent]);

                    }else{
                        return $this->redirect(['/' . $module . '/' . $this->module->id . '/items/edit', 'id' => $model->primaryKey, 'class' => $class, 'parent' => $parent]);
                    }
                // }
            } else {
                $this->flash('error', Yii::t('easyii', 'Create error. {0}', $model->formatErrors()));
                if(!Yii::$app->request->isAjax){
                    return $this->refresh();
                }
            }
        }
        else {

            $dataForm = [];
            foreach (CField::tree($categories,get_class(new $categoryClass)) as $key => $data) {
                $dataForm[] = (array)$data;
            }

            usort($dataForm, function($a, $b){
                return ($a['category_id'] - $b['category_id']);
            });

            foreach ($dataForm as $key => $data) {
                $dataForm[$key] = (object)$data;
            }

            return $this->rendering('create', [
                'model' => $model,
                'category' => $category,
                'categories' => self::getCategories(),
                'assign' => $category->category_id,
                'breadcrumbs' => self::getBreadcrumbs($id),
                'dataForm' => $this->generateForm($dataForm),
                'link' => self::generateLink($model),
                'parent' => $parent,
                'class' => $class,
            ]);
        }
    }

    public function actionEdit($id, $parent = null, $class = null)
    {
        $module = $this->module->module->id;
        $categoryClass = $this->categoryClass;
        $itemClass = $this->itemClass;

        if(!($model = $itemClass::findOne($id))){
            return $this->redirect(['/' . $module . '/' . $this->module->id]);
        }

        $categories = $categoryClass::find()->where(['category_id' => $this->getCategories($id)])->all();
        
        if ($model->load(Yii::$app->request->post())) {

            $post = Yii::$app->request->post('Item');
            
            $model->category_id = $post['category_id'] ? (is_array($post['category_id']) ? $post['category_id'][0] : $post['category_id']) : $itemClass::findOne($id)->attributes['category_id'];

            if(Yii::$app->request->post('Data')){
                $model->data = Yii::$app->request->post('Data');
            }

            if(Yii::$app->request->post('Contacts')){
                $model->contacts = Yii::$app->request->post('Contacts');
            }

            if(!$model->created_by){
                $model->created_by = Yii::$app->user->identity->id;
            }

            $model->updated_by = Yii::$app->user->identity->id;

            if($this->module->module->id != 'admin'){
                $model->status = $itemClass::STATUS_OFF;
            }

            if(gettype($post['time']) == 'string'){
                
                $model->time = strtotime($post['time']);
                $model->time_to = strtotime($post['time_to']);

            }

            if ($model->save()) {

                $this->flash('success', Yii::t('easyii/' . $this->moduleName, 'Item updated'));

                if($this->module->module->id != 'admin'){
                    return $this->redirect(['/' . $module . '/'.$this->module->id.'/items/' . $this->redirects[Yii::$app->controller->action->id], 'id' => $model->primaryKey, 'class' => $class, 'parent' => $parent]);
                }else{
                    return $this->redirect(['/' . $module . '/'.$this->module->id.'/items/edit', 'id' => $model->primaryKey, 'class' => $class, 'parent' => $parent]);
                }

            } else {
                $this->flash('error', Yii::t('easyii', 'Update error. {0}', $model->formatErrors()));
                return $this->refresh();
            }
        }
        else {
            return $this->rendering('edit', [
                'model' => $model,
                'categories' => $model->categories,
                'assign' => $this->getCategories($id),
                'breadcrumbs' => $this->getBreadcrumbs($id),
                'dataForm' => $this->generateForm(CField::tree($this->getCategories($id),get_class(new $categoryClass),1), $model->data),
                'link' => $this->generateLink($model),
                'parent' => $parent,
                'class' => $class,
            ]);
        }
    }

    public function actionDataForm($id)
    {
        $categoryClass = $this->categoryClass;

        $category = $categoryClass::findOne($id);

        $parents = $categoryClass::getParents($id) ? ArrayHelper::getColumn($categoryClass::getParents($id),'category_id') : [];

        array_push($parents,$category->category_id);

        $categories = $categoryClass::find()->where(['category_id' => $parents])->all();

        $dataForm = [];
        foreach (CField::tree($categories,get_class(new $categoryClass)) as $key => $data) {
            $dataForm[] = (array)$data;
        }

        usort($dataForm, function($a, $b){
            return ($a['category_id'] - $b['category_id']);
        });

        foreach ($dataForm as $key => $data) {
            $dataForm[$key] = (object)$data;
        }

        return $this->renderAjax('@kilyakus/shell/directory/views/items/dataForm',['dataForm' => $this->generateForm($dataForm)]);
    }

    public function actionDelete($id)
    {
        $itemClass = $this->itemClass;

        if(($model = $itemClass::findOne($id))){
            if(IS_MODER || ($model->created_by == Yii::$app->user->identity->id) && $model->status != $itemClass::STATUS_ON){
                $model->delete();
            }else{
                $this->flash('danger', Yii::t('yii', 'You are not allowed to perform this action.'));
                return $this->back();
            }
        } else {
            $this->error = Yii::t('easyii', 'Not found');
        }
        
        $this->formatResponse(Yii::t('easyii/' . $this->moduleName, 'Item deleted'));
        return $this->redirect(['/' . $module . '/'.$this->module->id]);
    }

    public function actionCopy($id)
    {
        $module = $this->module->module->id;
        $itemClass = $this->itemClass;
        $categoryAssign = $this->categoryAssign;

        if(!($model = $itemClass::findOne($id))){
            return $this->redirect(['/' . $module . '/' . $this->module->id]);
        }

        $clone = self::cloneModel($itemClass,$model);
        if($clone->save()){
            foreach ($model->categories as $category) {
                if(!$categoryAssign::find()->where(['item_id' => $clone->item_id])->one()){
                    $nc = new $categoryAssign;
                    $nc->category_id = $category;
                    $nc->item_id = $clone->item_id;
                    $nc->save();
                }
            }
        }

        return $this->back();
    }

    public function cloneModel($className,$model) {
        $attributes = $model->attributes;
        if($attributes){
            $newObj = new $className;
            foreach($attributes as  $attribute => $val) {
                if($attribute != 'item_id' && $attribute != 'image' && $attribute != 'preview'){
                    $newObj->{$attribute} = $val;
                }
            }
            return $newObj;
        }else{
            return false;
        }
    }

    public function actionArchive($id = null)
    {
        $itemClass = $this->itemClass;
        
        if(!($model = $itemClass::findOne($id))){
            // return $this->redirect(['/' . $this->module->module->id . '/'.$this->module->id]);
        }

        $model->status = $itemClass::STATUS_ARCHIVE;
        $model->update();

        $this->flash('success', Yii::t('easyii/' . $this->moduleName, 'Your entry has been archived.'));

        return $this->back();
    }

    public function actionSeasonsDelete($id){

        $model = Season::findOne($id);

        if($model != null){

            $model->delete();
            $this->flash('success', Yii::t('easyii/catalog', 'Item deleted'));

        } else {

            $this->flash('error', Yii::t('easyii', 'Not found'));

        }

        return $this->back();
    }

    public function actionSeasonsForm($id){

        return $this->renderPartial('@bin/admin/widgets/views/seasons_forms');
    }

    public function actionCommentsDown($id)
    {
        return $this->move($id, 'down');
    }

    public function actionUp($id, $category_id)
    {
        return $this->move($id, 'up', ['category_id' => $category_id]);
    }

    public function actionDown($id, $category_id)
    {
        return $this->move($id, 'down', ['category_id' => $category_id]);
    }

    public function generateLink($model)
    {
        $module = $this->module->id;

        $select = ['' => Yii::t('easyii/'.$module,'Select')];
        foreach(Yii::$app->getModule('admin')->activeModules as $module){
            if($this->module->id != $module->name){
                $class = \bin\admin\components\API::getClass($module->name,'models','Item');
            }
        
            if($class){
                $select[$class] = Yii::t('easyii/'.$module->name,ucfirst($module->name));
            }
        }

        if($model->parent_class && $model->parent_class != ''){
            $parent = $model->parent_class;
            $list = $parent::find()->all();
            if ($parent != null && count($list) > 0) {
                foreach ($list as $i => $module) {
                    $children[$module['category_id']] = $module['title'];
                }
            }
        }else{
            $model->parent_class = null;
            $model->parent_id = null;
            $model->update();
        }

        return ['select' => $select, 'children' => $children];
    }

    public function rendering($layout,$options = null)
    {
        if(Yii::$app->request->isAjax){
            $layout = '_form.php';
        }
        if($this->module->module->id != 'admin'){
            $this->layout = '@app/views/layouts/' . $this->module->id . '.php';
            $layout = '@app/modules/' . $this->module->id . '/views/f/' . $layout;
        }else{
            $layout = '@kilyakus/shell/directory/views/items/' . $layout;
        }
        if(Yii::$app->request->isAjax){
            return $this->renderAjax($layout,$options);
        }
        return $this->render($layout,$options);
    }

    public function actionParentItem()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            $class = new $_POST['depdrop_all_params']['item-parent_class']();
            $query = $class::find();
            $list = $query->asArray()->all();
            $selected  = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                foreach ($list as $i => $element) {
                    $out[] = ['id' => $element['item_id'], 'name' => $element['title']];
                    if ($i == 0) {
                        $selected = $element['item_id'];
                    }
                }
                return ['output' => $out, 'selected' => $selected];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function getBreadcrumbs($id)
    {
        $categoryClass = $this->categoryClass;

        $breadcrumbs = ArrayHelper::map($categoryClass::findAll(self::getCategories($id)),'category_id','title');

        return $breadcrumbs;
    }

    public function getCategories($id = null)
    {
        $categoryClass = $this->categoryClass;
        $categoryAssign = $this->categoryAssign;
        $categories = $key = $val = array();

        if($id){
            if(is_array($id)){
                $cats = $categoryAssign::find()->where($id)->all();
            }else{
                $cats = $categoryAssign::find()->where(['item_id' => $id])->all();
            }
            foreach($cats as $cat) {
                array_push($categories, $cat->category_id);
            }
        }else{
            if($this->module->module->id != 'admin'){
                $status = false;
            }else{
                $status = true;
            }
            $trees = $categoryClass::tree($status);
            $categories = self::checkCategories($trees);
            $categories = $categories ? self::filterCategories($categories) : null;
        }
        return $categories;
    }

    public function checkCategories($trees,$parent_title = null)
    {
        foreach ($trees as $key => $tree) {
            if($parent_title){
                $title = implode(' > ',[$parent_title,$tree->title]);
            }else{
                $title = $tree->title;
            }
            if($childrens = $tree->children){
                $categories[$title] = self::checkCategories($tree->children,$title);
            }else{
                $categories[$tree->category_id] = $title;
            }
        }
        return $categories;
    }

    public function filterCategories($categories)
    {
        foreach ($categories as $key => $category) {
            if(is_array($category)){
                $filter = self::filterCategories($category);
                foreach ($filter as $kf => $f) {
                    $return[$kf] = $f;
                }
            }else{
                $return[$key] = $category;
            }
        }
        return $return;
    }

    public function generateForm($fields, $data = null)
    {
        if(API::getModule($this->moduleName)->settings['enableCategory'] && count($fields)){
            // return self::getFields($fields,$data);
            return parent::renderPartial('dataForm.php',['fields' => $fields,'data' => $data]);
        }else{
            return false;
        }
    }

    public function getFields($fields = null, $data = null, $parent = null)
    {
        $result = '';

        foreach($fields as $field)
        {
            if($field->children){
                $result .= self::getGroup($field->children,$data,$field);
            }else{
                $result .= self::getItems($field->children,$data,$field);
            }
        }
        return $result;
    }

    public function getGroup($children = null,$data = null,$field = null){

        $parent = CField::findOne($field->parent);

        if($field->type == 'table' || $parent->type == 'table'){
            $options = '';
            foreach (explode(',',$field->options) as $key => $option) {
                $options .= '<th>'.$option.'</th>';
            }
            $result .= '<table class="table table-hover table-bordered">'.
            '<thead><tr><th width="250" style="border-bottom:0px;">'.$field->title.'</th>'.$options.'</tr></thead>'.
            '<tbody>'.
            self::getFields($field->children,$data,$field).
            '</tbody>'.
            '</table>';
        }else{
            $result = '';
            $result .= !$field->parent ? '<div class="panel panel-default border">' : '<div class="form-group w-12 pull-left border-bottom border-darkgray"><div class="col-xs-12">';
            $result .= self::genHead($field);
            $result .= !$field->parent ? '<div id="collapse-'.$field->name.'" class="panel-body panel-collapse collapse in" role="tabpanel" aria-labelledby="collapse-'.$field->name.'">' : '';
            $result .= '<div class="row">';
            $result .= self::getFields($children,$data,$field);
            $result .= '</div>';
            $result .= '</div>';
            $result .= '</div>';
        }
        return $result;
    }

    public function genHead($field){

        $result .= !$field->parent 
            ? '<div class="panel-heading" role="tab" id="category-'.$field->name.'"><div class="panel-title"><a data-toggle="collapse" href="#collapse-'.$field->name.'" aria-expanded="true" aria-controls="collapse-'.$field->name.'" class="pull-right toggle"><div class="close-block"></div></a>'. $field->title .'</div></div>' 
            : '<label for="data-'.$field->name.'">'. $field->title .'</label>';

        return $result;

    }

    public function genContainer($html = null,$field = null,$label = true,$image = true){

        if($label != false){
            $label = '<label for="data-'.$field->name.'">'. $field->title .'</label>';
        }
        if($image != false){
            $image = Html::img(
                Image::thumb($field->image, 34,34),[
                    'class' => 'img-responsive btn btn-icon',
                    'style' => 'background:#F1F1F1;',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'right',
                    'data-html' => 'true',
                    'data-original-title' => $field->text,
                ]
            );
            return $label.'<div class="input-group"><label for="data-'.$field->name.'" class="input-group-prepend">'.$image.'</label>'.$html.'</div>';
        }else{
            return $label.$html;
        }
    }

    public function table($field){
        
        if($field->type == 'table'){
            return true;
        }else{
            if(isset($field->parent)){
                return self::table(CField::findOne($field->parent));
            }else{
                return false;
            }
        }
    }

    public function getItems($children = null,$data = null,$field = null)
    {
        $itemClass = $this->itemClass;

        $category_id = $itemClass::findOne(Yii::$app->controller->action->controller->actionParams['id'])->category_id;

        $alert = '<a href="'.Url::toRoute('/admin/catalog/a/fields/'.$category_id).'" target="_blank">'.Yii::t('easyii','You have not created options, please go to the field settings section').'</a>';

        $parent = CField::findOne($field->parent);

        $value = !empty($data->{$field->name}) ? $data->{$field->name} : null;
        $result .= $parent ? '<div class="form-group col-xs-12 col-md-6">' : '<div class="form-group">';
        if ($field->type === 'string' && self::table($field) != true) {

            $settings = ['id' => 'data-'.$field->name,'class' => 'form-control'];
            if($field->required == true){
                $settings = array_merge($settings,['required' => true]);
            }
            $html = Html::input('text', "Data[{$field->name}]", $value, $settings);
            $result .= self::genContainer($html,$field,true);

        }
        elseif ($field->type === 'integer' && ItemsController::table($field) != true) {

            $settings = ['id' => 'data-'.$field->name,'class' => 'form-control input-lg','min' => $field->min,'max' => $field->max, 'step' => ($field->step == 1 ? 'any' : $field->step)];
            if($field->required == true){
                $settings = array_merge($settings,['required' => true]);
            }
            $html = Html::input('number',"Data[{$field->name}]", $value, $settings);
            $result .= ItemsController::genContainer($html,$field,true);

        }
        elseif ($field->type === 'text' && self::table($field) != true) {

            $settings = ['id' => 'data-'.$field->name,'class' => 'form-control'];
            if($field->required == true){
                $settings = array_merge($settings,['required' => true]);
            }

            $html = Html::textarea("Data[{$field->name}]", $value, $settings);
            $result .= self::genContainer($html,$field,true);

        }
        elseif ($field->type === 'boolean' && self::table($field) != true) {

            $html = '<label class="v-align mt-10">'. Html::checkbox("Data[{$field->name}][]", $value, ['class' => 'switch','value' => $value,['uncheck' => 0]]).'<span class="ml-10">'.$field->title.'</span></label>';
            $result .= self::genContainer($html,$field,true);

        }
        elseif ($field->type === 'select' && self::table($field) != true) {
            if($field->options){
                $options = ['' => Yii::t('easyii/' . $this->moduleName, 'Select')];
                foreach(explode(',',$field->options) as $option){
                    $options[\yii\helpers\Inflector::slug($option)] = $option;
                }
                $html = Select2::widget(['id' => $field->name . '-' . $field->field_id, 'name' => 'Data['.$field->name.']','theme' => 'default', 'data' => $options, 'value' => $value, 'pluginOptions' => ['class' => 'form-control']]);
                $result .= self::genContainer($html,$field,true);

            }else{

                $result .= self::genContainer($alert,$field,true);

            }
        }
        elseif ($field->type === 'checkbox' && self::table($field) != true) {
            $options = '';
            if($field->options){

                // foreach(explode(',',$field->options) as $option){
                //     $checked = $value && (is_array($value) ? in_array($option, $value) : \yii\helpers\Inflector::slug($option) == $value);
                //     $options .= '<label class="v-align mt-10">'. Html::checkbox("Data[{$field->name}][]", $checked, ['class' => 'switch','value' => $option,]) .'<span class="ml-10">'. $option .'</span></label>';
                // }
                // $result .= self::genContainer($options,$field,false);
                foreach(explode(',',$field->options) as $option){
                    $options[\yii\helpers\Inflector::slug($option)] = $option;
                }

                $html = Select2::widget(['id' => $field->name . '-' . $field->field_id, 'name' => 'Data['.$field->name.']','theme' => 'default', 'data' => $options, 'value' => $value, 'options' => ['multiple' => true], 'pluginOptions' => ['class' => 'form-control', 'closeOnSelect' => false]]);
                $result .= self::genContainer($html,$field,true);

            }else{

                $html = '<label class="v-align mt-10">'. Html::checkbox("Data[{$field->name}][]", $value, ['class' => 'switch','value' => $value]).'<span class="ml-10">'.$field->title.'</span></label>';
                $result .= self::genContainer($html,$field,false);

            }
        }
        elseif ($field->type === 'radio' && self::table($field) != true) {
            if($field->options){

                foreach(explode(',',$field->options) as $option){
                    $checked = $value && (is_array($value) ? in_array($option, $value) : \yii\helpers\Inflector::slug($option) == $value);
                    $options .= '<label class="v-align mt-10">'. Html::radio("Data[{$field->name}][]", $checked, ['class' => 'switch','value' => $option,]) .'<span class="ml-10">'. $option .'</span></label>';
                }
                $result .= self::genContainer($options,$field,true);

            }else{

                $value = !empty($data->{$parent->name}) ? $data->{$parent->name} : null;
                $checked = $value && $field->title == $value[0];
                $html = '<label class="v-align mt-10">'. Html::radio("Data[{$parent->name}][]", $checked, ['class' => 'switch','value' => $field->title]).'<span class="ml-10">'.$field->title.'</span></label>';
                $result .= self::genContainer($html,$field,false);

            }
        }
        elseif ($field->type === 'table' || self::table($field) == true) {
            $options = '';
            foreach (explode(',',$parent->options) as $option) {
                if($field->children){

                }else{

                    $value = !empty($data->{$option.'-'.$field->name}) ? $data->{$option.'-'.$field->name} : null;

                    $foptions = '';
                    if($field->options && !count($field->children)){
                        if ($field->type === 'string') {

                            $html = Html::input('text', 'Data['.$option.'-'.$field->name.']', $value, ['id' => 'data-'.$option.'-'.$field->name,'class' => 'form-control']);

                        }elseif($field->type === 'text') {

                            $html = Html::textarea("Data['.$option.'-'.$field->name.']", $value, ['id' => 'data-'.$option.'-'.$field->name,'class' => 'form-control']);

                        }elseif($field->type === 'boolean') {

                            $html = Html::checkbox('Data['.$option.'-'.$field->name.'][]', $value, ['class' => 'switch','value' => $value,['uncheck' => 0]]);

                        }elseif($field->type == 'select'){
                            $foptions = ['' => Yii::t('easyii/' . $this->moduleName, 'Clear')];
                            foreach(explode(',',$field->options) as $foption){
                                $foptions[\yii\helpers\Inflector::slug($foption)] = $foption;
                            }
                            $html = '<select name="Data['.$option.'-'.$field->name.']" class="form-control">'. Html::renderSelectOptions($value, $foptions) .'</select>';

                        }elseif($field->type === 'checkbox') {
                            $foptions = '';
                            foreach(explode(',',$field->options) as $foption){
                                $checked = $value && in_array($foption, $value);
                                $foptions .= '<label class="v-align mt-10">'. Html::checkbox('Data['.$option.'-'.$field->name.'][]', $checked, ['class' => 'switch','value' => $foption,]) .'<span class="ml-10">'. $foption .'</span></label>';
                            }
                            $html = $foptions;

                        }elseif($field->type === 'radio') {
                            $foptions = '';
                            foreach(explode(',',$field->options) as $foption){
                                $checked = $value && in_array($foption, $value);
                                $foptions .= '<label class="v-align mt-10">'. Html::radio('Data['.$option.'-'.$field->name.'][]', $checked, ['class' => 'switch','value' => $foption,]) .'<span class="ml-10">'. $foption .'</span></label>';
                            }
                            $html = $foptions;

                        }else{
                            $html = 'in process';
                        }
                        $options .= '<td>'.self::genContainer($html,$field,false,false).'</td>';
                    }else{
                        $options = '<td colspan="'.count(explode(',',$parent->options)).'">'.$alert.'</td>';
                    }
                }
            }

            $result .= '<tr><td>'.self::genContainer($field->title,$field,false).'</td>'.$options.'</tr>';
        }
        $result .= '</div>';
        return $result;
    }
}