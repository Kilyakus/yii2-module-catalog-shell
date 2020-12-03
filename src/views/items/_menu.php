<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kilyakus\web\widgets as Widget;

$action = $this->context->action->id;
$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
if($module == 'app'){
	$module = 'admin';
}
$settings = $this->context->module->settings;

$nav = [];

if($settings['enableCategory']){

	if($action === 'index'){
		$nav[] = [
			'label' => '<i class="fa fa-chevron-left"></i>&nbsp; ' . Yii::t('easyii', 'Categories'),
			'url' => Url::to(['/' . $module . '/'.$moduleName, 'class' => $class, 'parent' => $parent]),
		];
	}

	if(count($breadcrumbs) <= 1){
		foreach ($breadcrumbs as $key => $breadcrumb){
			$nav[] = [
				'label' => (($action !== 'index') ? '<i class="fa fa-chevron-left"></i>&nbsp; ' : '') . $breadcrumb,
				'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/index', 'id' => $key, 'class' => $class, 'parent' => $parent]),
			];
		}
	}else{
		$childrens = [];
		foreach ($breadcrumbs as $key => $breadcrumb){
			$childrens[] = [
				'label' => (($action !== 'index') ? '<i class="fa fa-chevron-left"></i>&nbsp; ' : '') . $breadcrumb,
				'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/index', 'id' => $key, 'class' => $class, 'parent' => $parent]),
			];
		}
		$nav[] = ['label' => Yii::t('easyii','Back to categories'),'items' => $childrens,];
	}
	$nav[] = [
		'label' => '<i class="fa fa-plus"></i>&nbsp; ' . Yii::t('easyii', 'Add'),
		'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/create', 'id' => $category->primaryKey, 'class' => $class, 'parent' => $parent]),
		'active' => ($action === 'create'),
	];
}else{
	$nav[] = [
		'label' => Yii::t('easyii', 'Add'),
		'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/create', 'class' => $class, 'parent' => $parent]),
		'active' => ($action === 'create'),
	];
}

?>

<?= Widget\NavPage::widget([
	'options' => [
		'class' => 'nav-pills',
	],
	'encodeLabels' => false,
	'items' => $nav,
]) ?>