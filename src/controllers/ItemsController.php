<?php
namespace kilyakus\shell\directory\controllers;

use Yii;
use yii\db\Query;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use bin\user\filters\AccessRule;
use bin\admin\components\API;
use kilyakus\helper\media\Image;
use kilyakus\modules\behaviors\StatusController;
use bin\admin\behaviors\SortableDateController;
use bin\admin\models\Season;
use bin\admin\models\CField;
use bin\admin\models\Photo;
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

			if(Yii::$app->request->post('hasEditable'))
			{
				return self::update();
			}

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

		if($id){
			$query['item_id'] = ArrayHelper::getColumn($categoryAssign::findAll(['category_id' => $id]),'item_id');
		}

		if($class){
			$query['parent_class'] = $this->module->settings['parentSubmodule'];
		}

		if($parent && Yii::$app->request->get('Item')['parent_id'] == 1){
			$query['parent_id'] = $parent;
		}

		if(Yii::$app->request->get('Item')['nearby'] == 1){
			
			$parentClass = $this->module->settings['parentSubmodule'];

			$parentModel = $parentClass::findOne($parent);

			$latitude = (float)substr($parentModel->latitude, 0, 5);
			$longitude = (float)substr($parentModel->longitude, 0, 5);

			if(substr($latitude, 0, 1) != '-'){
				$latPlus = ($latitude+0.9);
			}else{
				$latPlus = ($latitude-0.9);
			}

			if(substr($latitude, 0, 1) != '-'){
				$latMinus = ($latitude-0.9);
			}else{
				$latMinus = ($latitude+0.9);
			}

			if(substr($longitude, 0, 1) != '-'){
				$lngPlus = ($longitude+0.9);
			}else{
				$lngPlus = ($longitude-0.9);
			}

			if(substr($longitude, 0, 1) != '-'){
				$lngMinus = ($longitude-0.9);
			}else{
				$lngMinus = ($longitude+0.9);
			}

			$nearby = ArrayHelper::getColumn($itemClass::find()->where(['and',['parent_class' => $parentClass],['category_id' => $categoryAssign::findAll(['category_id' => $id])],['<=','latitude', $latPlus],['>=','latitude', $latMinus],['<=','longitude', $lngPlus],['>=','longitude', $lngMinus],])->all(),'item_id');
		}

		$searchModel  = \Yii::createObject($itemClass::className());
		$dataProvider = $searchModel->search(Yii::$app->request->get());
		if($query){
			$dataProvider->query->andFilterWhere($query);
		}
		if(count($nearby)){
			$dataProvider->query->andFilterWhere(['item_id' => $nearby]);
		}
		$dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

		$data = new ActiveDataProvider([
			'query' => $itemClass::find()->orderBy(['status' => SORT_ASC, 'item_id' => SORT_DESC]),
		]);

		$items = $data->models;
		
		return $this->render('@kilyakus/shell/directory/views/items/index', [
			'data' => $data,
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
			'model' => $model,
			'items' => $items,
			'breadcrumbs' => self::getBreadcrumbs($id),
			'parent' => $parent,
			'class' => $class,
		]);
	}

	public function actionItemsList($q = null, $id = null)
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
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

		$category = $post['category_id'] ? (is_array($post['category_id']) ? $post['category_id'][0] : $post['category_id']) : $id;

		if(!($category = $categoryClass::findOne($category)) && $this->module->settings['enableCategory']){
			// return $this->redirect(['/admin/'.$this->module->id]);
		}

		$model = new $itemClass;

		// $model->category_id = $category->category_id;

		$assign = $category->category_id;

		$parents = $categoryClass::getParents($id) ? ArrayHelper::getColumn($categoryClass::getParents($id),'category_id') : [];

		array_push($parents,$category->category_id);

		$categories = $categoryClass::find()->where(['category_id' => $parents])->all();

		if ($model->load(Yii::$app->request->post())) {

			$model->data = Yii::$app->request->post('Data');

			$model->contacts = Yii::$app->request->post('Contacts');

			$model->status = 0;

			if ($model->save())
			{
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
			$cats = [];
			if($categories){
				foreach ($categories as $key => $cat) {
					$cats[] = $cat->category_id;
				}
			}

			return $this->rendering('create', [
				'model' => $model,
				'category' => $category,
				// 'categories' => self::getCategories(),
				'assign' => $assign,
				'breadcrumbs' => self::getBreadcrumbs($id),
				'dataForm' => self::getItemFields($cats),
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

			if(Yii::$app->request->post('Data')){
				$model->data = Yii::$app->request->post('Data');
			}

			if(Yii::$app->request->post('Contacts')){
				$model->contacts = Yii::$app->request->post('Contacts');
			}

			if($this->module->module->id != 'admin'){
				$model->status = $itemClass::STATUS_OFF;
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
				'assign' => $model->categoriesKeys, //$this->getCategories($id)
				'breadcrumbs' => $this->getBreadcrumbs($id),
				'dataForm' => self::getItemFields($model->categoriesKeys, $model->data), //$this->getCategories($id)
				'link' => self::generateLink($model),
				'parent' => $parent,
				'class' => $class,
			]);
		}
	}

	public function update()
	{
		$itemClass = $this->itemClass;

		$post = Yii::$app->request->post();
		$key = $post['editableKey'];
		$index = $post['editableIndex'];
		$attribute = $post['editableAttribute'];
		$attributes = $post['Item'];
		$model = $itemClass::findOne($key);

		if (isset($post['hasEditable'])) {

			Yii::$app->response->format = Response::FORMAT_JSON;

			if ($model->load($post)) {

				$model->{$attribute} = $attributes[$index][$attribute];
				$model->update();

				$success = [
					'message' => Yii::t('easyii', 'Update success'),
					'output' => [
						$attribute => $model->{$attribute},
					]
				];
			}
			else {

				$this->error = Yii::t('easyii', 'Update error. {0}', $model->formatErrors());

			}

			return $this->formatResponse($success);
		}
	}

	protected function getItemFields($categories, $data = null)
	{
		$fields = [];

		if(API::getModule($this->moduleName)->settings['enableCategory'] && count($categories)){

			$filter = ['category_id' => $categories, 'class' => get_class(new $this->categoryClass), 'status' => 1];

			foreach (CField::find()->where(['and',$filter,['depth' => 0]])->orderBy(['order_num' => SORT_DESC])->all() as $field) {
				$fields[] = $field;
			}

			usort($fields, function($a, $b){
				return ($a['category_id'] - $b['category_id']);
			});

			foreach ($fields as $key => $field) {
				$fields[$key] = $field;
			}

			return $this->renderPartial('@kilyakus/shell/directory/views/items/dataForm', ['fields' => $fields, 'filter' => $filter, 'data' => $data]);

		}else{

			return false;

		}
	}

	public function actionDataForm($id)
	{
		$categoryClass = $this->categoryClass;

		$category = $categoryClass::findOne($id);

		$parents = $categoryClass::getParents($id) ? ArrayHelper::getColumn($categoryClass::getParents($id),'category_id') : [];

		array_push($parents,$category->category_id);

		$categories = $categoryClass::find()->where(['category_id' => $parents])->all();

		$filter = ['category_id' => $categories, 'class' => get_class(new $categoryClass), 'status' => 1];

		foreach (CField::find()->where(['and',$filter,['depth' => 0]])->orderBy(['order_num' => SORT_DESC])->all() as $field) {
			$fields[] = $field;
		}

		if($fields){
			usort($fields, function($a, $b){
				return ($a['category_id'] - $b['category_id']);
			});

			foreach ($fields as $key => $field) {
				$fields[$key] = $field;
			}
		}

		return $this->renderAjax('@kilyakus/shell/directory/views/items/dataForm', ['fields' => $fields, 'filter' => $filter, 'data' => $data]);
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
		// return $this->redirect(['/' . $module . '/'.$this->module->id]);
		return $this->back();
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
		if($model->status == $itemClass::STATUS_ARCHIVE){
			$model->status = $itemClass::STATUS_OFF;
		}else{
			$model->status = $itemClass::STATUS_ARCHIVE;
		}
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
		$module = $this->module;
		$moduleName = $module->id;
		$settings = $module->settings;

		$select = ['' => Yii::t('easyii/'.$moduleName,'Select')];

		if($parents = explode(',',$settings["parentSubmodule"])){

			foreach ($parents as $className) {

				if(class_exists($className)){

					$moduleParent = (new $className())->module->name;

					$select[$className] = Yii::t('easyii/'.$moduleParent,ucfirst($moduleParent));
				}
			}
		}

		if($model->parent_class && class_exists($model->parent_class) && $model->parent_class != ''){
			$parent = $model->parent_class;
			$list = $parent::find()->all();

			if ($parent != null && count($list) > 0) {
				foreach ($list as $i => $module) {
					if(is_array($module['category_id']) || is_object($module['category_id'])){
						foreach ($module['category_id'] as $key => $value) {
							$children[$value] = $module['title'];
						}
					}else{
						$children[$module['category_id']] = $module['title'];
					}
				}
			}
		}else{
			if($model->parent_id != null){
				$model->parent_class = null;
				$model->parent_id = null;
				$model->update();
			}
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
		Yii::$app->response->format = Response::FORMAT_JSON;
		$out = [];
		if (isset($_POST['depdrop_parents'])) {
			$id = end($_POST['depdrop_parents']);
			$class = new $_POST['depdrop_all_params']['item-parent_class']();
			$query = $class::find();
			$list = $query->orderBy(['category_id' => SORT_ASC])->all();
			$selected  = null;
			if ($id != null && count($list) > 0) {
				$selected = '';
				foreach ($list as $i => $element) {
					$out[] = ['id' => $element->primaryKey, 'name' => $element->category->title . ': ' . $element->translate->title];
					if ($i == 0) {
						$selected = $element->primaryKey;
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

		$breadcrumbs = ArrayHelper::map($categoryClass::findAll(self::getCategories($id)),'category_id','translate.title');

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
				$title = implode(' > ',[$parent_title,$tree->translate->title]);
			}else{
				$title = $tree->translate->title;
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

	public function genContainer($html = null,$field = null,$label = true,$image = true)
	{
		if($label != false){
			$label = '<label for="data-'.$field->name.'">'. Yii::t('easyii', $field->title) .'</label>';
		}

		if($image != false){

			$image = Html::img(
				Image::thumb($field->image, 41,41),[
					'class' => 'img-responsive btn btn-icon',
				]
			);

			$image = Html::tag('label', $image, [
				'for' => 'data-' . $field->name,
				'class' => 'input-group-prepend'
			]);

			$html = Html::tag('div',$image.$html,[
				'class' => 'form-group input-group',
				'data-toggle' => 'kt-tooltip',
				'data-skin' => 'dark',
				'data-placement' => 'bottom',
				'data-html' => 'true',
				'data-original-title' => $field->text,
			]);

			return $label . $html;
		}else{
			return $label . $html;
		}
	}
}