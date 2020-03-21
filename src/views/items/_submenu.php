<?php
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kilyakus\web\widgets as Widget;

$action = $this->context->action->id;
$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
if($module == 'app'){
		$module = 'admin';
}
$settings = $this->context->module->settings;

$modules = [];
foreach (Yii::$app->getModule('admin')->activeModules as $key => $activeModule) {
	$parents = explode(',',$activeModule->settings["parentSubmodule"]);

	if($activeModule->settings["enableSubmodule"]) {

		foreach ($parents as $className) {
			if(class_exists($className)){
				$moduleParent = (new $className())->module->name;
				if($moduleName == $moduleParent){
					$modules[] = [
						'label' => '<span class="' . $activeModule->icon . '"></span>&nbsp; ' . Yii::t('easyii/'.$activeModule->name, $activeModule->title),
						'url' => Url::to(['/' . $module . '/' . $activeModule->name . '/a/index', 'parent' => $model->primaryKey,'class' => $moduleName]),
					];
				}
			}
		}
	}
}
?>

<?= Widget\Nav::widget([
	'options' => [
		'class' => 'nav-tabs nav-tabs-line nav-tabs-line-brand nav-tabs-line-2x',
	],
	'encodeLabels' => false,
	'items' => ArrayHelper::merge([
		[
			'label' => '<span class="fa fa-pen"></span>&nbsp; ' . Yii::t('easyii', 'Edit'),
			'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/edit', 'id' => $model->primaryKey]),
			'active' => ($action === 'edit'),
		],
		[
			'label' => '<span class="fa fa-tint"></span>&nbsp; ' . Yii::t('easyii', 'Seasons'),
			'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/seasons', 'id' => $model->primaryKey]),
			'active' => ($action === 'seasons'),
			'visible' => $settings['enableSeasons'],
		],
		// [
		// 	'label' => '<span class="fa fa-camera"></span>&nbsp; ' . Yii::t('easyii', 'Photos'),
		// 	'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/photos', 'id' => $model->primaryKey]),
		// 	'active' => ($action === 'photos'),
		// 	'visible' => $settings['enablePhotos'],
		// ],
		[
			'label' => '<span class="fa fa-film"></span>&nbsp; ' . Yii::t('easyii', 'Video'),
			'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/videos', 'id' => $model->primaryKey]),
			'active' => ($action === 'videos'),
			'visible' => $settings['enableVideo'],
		],
		[
			'label' => '<span class="fa fa-map-marker"></span>&nbsp; ' . Yii::t('easyii/'.$moduleName, 'Places nearby'),
			'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/maps', 'id' => $model->primaryKey]),
			'active' => ($action === 'maps'),
			'visible' => $settings['enableMaps'],
		],
		[
			'label' => '<span class="fa fa-comment"></span>&nbsp; ' . Yii::t('easyii/'.$moduleName, 'Comments'),
			'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/comments', 'id' => $model->primaryKey]),
			'active' => ($action === 'comments'),
			'visible' => $settings['enableComments'],
		],
		[
			'label' => '<span class="fa fa-video"></span>&nbsp; ' . Yii::t('easyii/'.$moduleName, 'Webcams'),
			'url' => Url::to(['/' . $module . '/' . $moduleName . '/items/webcams', 'id' => $model->primaryKey]),
			'active' => ($action === 'webcams'),
			'visible' => $settings['enableWebcams'],
		],
	],$modules),
]) ?>