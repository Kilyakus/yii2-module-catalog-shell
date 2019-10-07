<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use bin\user\models\User;
use bin\admin\modules\catalog\models\Item;
use bin\admin\helpers\Image;
use bin\admin\modules\chat\models\Group;
use kilyakus\web\widgets as Widget;
use yii\web\JsExpression;

$settings = $this->context->module->settings;
$moduleName = $this->context->module->id;
$module = $this->context->module->module->id;
if($module == 'app'){
    $module = 'admin';
}
$chatClass = $this->context->chatClass;
$this->title = Yii::t('easyii/' . $moduleName, ucfirst($moduleName)) . (!$model->title ?: ': ' . $model->title);

foreach($items as $item){
    $status = $item->status == Item::STATUS_ON ? '' : 'kt-bg-light-warning';
    $chat = $chatClass::find()->where(['item_id' => $item['item_id']])->one();
    $categories = [];
    foreach ($item->_categories as $category) {
        $categories[] = [
            'label' => $category->title,
            'image' => Image::thumb($category->icon ? $category->icon : $category->image,30,30),
            'url' => Url::toRoute(['/' . $module . '/'.$moduleName.'/a/edit', 'id' => $category->primaryKey]),
        ];
    }
    $columns[] = [
        [
            'content' => $item->primaryKey,
            'options' => ['class' => $status],
            'visible' => IS_ROOT
        ],
        [
            'content' => Html::img(Image::thumb($item->preview, 40, 40),['class' => 'img-circle']),
            'options' => ['class' => $status],
            'visible' => $settings['itemThumb']
        ],
        [
            'content' => Html::a($item->title, Url::to(['/' . $module . '/'.$moduleName.'/items/edit', 'id' => $item->primaryKey])),
            'options' => ['class' => $status],
        ],
        [
            'content' => Widget\Peafowl::widget([
                'items' => $categories
            ]), 
            'options' => ['class' => $status],
        ],
        [
            'content' => \kilyakus\web\templates\UserCard\UserCard::widget(['id' => $item->created_by, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $item->created_by])]), 
            'options' => ['class' => $status],
        ],
        [
            'content' => \kilyakus\web\templates\UserCard\UserCard::widget(['id' => $item->updated_by, 'url' => Url::toRoute(['/user/admin/info/', 'id' => $item->updated_by])]), 
            'options' => ['class' => $status],
        ],
        [
            'content' => $item->views, 
            'options' => ['class' => $status],
        ],
        [
            'content' => Widget\Dropdown::widget([
                'button' => [
                    'icon' => 'fa fa-cog',
                    'iconPosition' => Widget\Button::ICON_POSITION_LEFT,
                    'type' => Widget\Button::TYPE_PRIMARY,
                    // 'size' => Widget\Button::SIZE_SMALL,
                    'disabled' => false,
                    'block' => false,
                    'outline' => true,
                    'hover' => false,
                    'circle' => true,
                    'options' => ['title' => Yii::t('easyii', 'Actions')]
                ],
                'options' => ['class' => 'dropdown-menu-right'],
                'items' => [
                    [
                        'label' => Yii::t('easyii', 'View'),
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'url' => Url::to(['/'.$moduleName.'/view/', 'slug' => $item->slug]),
                        'linkOptions' => ['target' => '_blank'],
                    ],
                    [
                        'divider' => true,
                    ],
                    !$chat ? [
                        'label' => Yii::t('easyii/chat', 'Создать чат'),
                        'icon' => 'fa fa-comment',
                        'url' => 'javascript://',
                        'options' => ['data-toggle' => 'modal', 'data-target' => '#modal-form' . $item->primaryKey]
                    ] : [
                        'label' => Yii::t('easyii/chat', 'Открыть чат'),
                        'icon' => 'fa fa-external-link-alt',
                        'url' => Url::to(['/' . $module . '/chat/message/groups/', 'id' => $chat->chat_id]),
                        'linkOptions' => ['target' => '_blank'],
                    ],
                    [
                        'label' => Yii::t('easyii', 'Edit'),
                        'icon' => 'fa fa-edit',
                        'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/edit', 'id' => $item->primaryKey]),
                    ],
                    [
                        'label' => Yii::t('easyii', 'Copy'),
                        'icon' => 'glyphicon glyphicon-link',
                        'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/copy', 'id' => $item->primaryKey]),
                    ],
                    [
                        'divider' => true,
                    ],
                    [
                        'label' => Yii::t('easyii', 'Move up'),
                        'icon' => 'fa fa-arrow-up',
                        'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/up', 'id' => $item->primaryKey, 'category_id' => $item->primaryKey]),
                    ],
                    [
                        'label' => Yii::t('easyii', 'Move down'),
                        'icon' => 'fa fa-arrow-down',
                        'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/down', 'id' => $item->primaryKey, 'category_id' => $item->primaryKey]),
                    ],
                    [
                        'divider' => true,
                    ],
                    [
                        'label' => $item->status == Item::STATUS_ON ? Yii::t('easyii', 'Turn Off') : Yii::t('easyii', 'Turn On'),
                        'icon' => $item->status == Item::STATUS_ON ? 'fa fa-eye-slash' : 'fa fa-eye',
                        'url' => $item->status == Item::STATUS_ON ? 
                            Url::to(['/' . $module . '/'.$moduleName.'/items/off', 'id' => $item->primaryKey]) : 
                            Url::to(['/' . $module . '/'.$moduleName.'/items/on', 'id' => $item->primaryKey]),
                    ],
                    [
                        'label' => Yii::t('easyii', 'Delete item'),
                        'icon' => 'fa fa-times',
                        'url' => Url::to(['/' . $module . '/'.$moduleName.'/items/delete', 'id' => $item->primaryKey]),
                        'linkOptions' => ['class' => 'confirm-delete', 'data-reload' => '0'],
                    ],
                ],]), 
            'options' => ['class' => $status], 
            'visible' => IS_ADMIN
        ],
    ];
}
?>

<?= $this->render('_menu', ['category' => $model,'breadcrumbs' => $breadcrumbs, 'class' => $class, 'parent' => $parent]) ?>

<?php Widget\Portlet::begin([
    'title' => $this->title,
    'icon' => $this->context->module->icon,
    // 'scroller' => [
    //     'max-height' => 50,
    //     'format' => 'vh',
    // ],
    'bodyOptions' => [
        'class' => (!count($items) ?: 'kt-portlet__body--fit'),
    ],
    'footerContent' => \yii\widgets\LinkPager::widget([
        'pagination' => $data->pagination
    ])
]); ?>
    <?php if(count($items)) : ?>

        <?= Widget\KtDataTable::widget([
            'tableOptions' => ['id' => 'tb-example'],
            'hover' => true, // Defaults to true
            'bordered' => false, // Defaults to false
            'striped' => false, // Defaults to true
            'condensed' => false, // Defaults to true
            'beforeHeader' => [
                [
                    'columns' => [
                        ['content' => '#', 'options' => ['width' => 50], 'visible' => IS_ROOT],
                        ['content' => '', 'options' => ['width' => 60], 'visible' => $settings['itemThumb']],
                        ['content' => Yii::t('easyii', 'Title')],
                        ['content' => Yii::t('easyii', 'Categories'), 'options' => ['width' => '200px']],
                        ['content' => Yii::t('easyii', 'Created by'), 'options' => ['width' => '160px']],
                        ['content' => Yii::t('easyii', 'Updated by'), 'options' => ['width' => '160px']],
                        ['content' => Yii::t('easyii', 'Views'), 'options' => ['width' => '120px']],
                        ['content' => '', 'options' => ['width' => 30], 'visible' => IS_ADMIN],
                    ],
                ],
            ],
            'showFooter' => true,
            'columns' => $columns
        ]); ?>

        <?php foreach($items as $item) : ?>
            <?php
            $itemClass = get_class($item);
            $selectItems = ArrayHelper::map($itemClass::findAll(['item_id' => $item->primaryKey]), 'item_id', 'title');
            $selectItems = ArrayHelper::merge($selectItems,ArrayHelper::map($itemClass::find()->where(['and',['!=', 'item_id', $item->primaryKey],['category_id' => array_keys($item->categories)]])->limit(50)->all(), 'item_id', 'title'));
            foreach ($selectItems as $item_id => $selectItem) {
                if($chatClass::find()->where(['item_id' => $item_id])->one()){
                    unset($selectItems[$item_id]);
                }
            }
            ?>
            <div class="modal fade" id="modal-form<?= $item->primaryKey ?>">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button class="close" type="button" data-dismiss="modal">
                                <i class="fa fa-close"></i>
                            </button>
                            <h4 class="modal-title">Создание новой комнаты</h4>
                        </div>
                        <div class="modal-body">
                            <?php $form = ActiveForm::begin(['id'=>'form-group-item']); ?>
                                <?= $form->field($item, 'item_id[]')->widget(Widget\Select2::classname(), [
                                    'data' => $item->primaryKey ? $selectItems : null,
                                    'theme' => 'default',
                                    'options' => [
                                        'id' => 'item-id'.$item->primaryKey,
                                        'value' => $item->primaryKey,
                                        'multiple' => true,
                                    ],
                                    'pluginOptions' => [
                                        // 'allowClear' => true,
                                        // 'minimumInputLength' => 1,
                                        // 'ajax' => [
                                        //     'url' => \yii\helpers\Url::to(['items-list']),
                                        //     'dataType' => 'json',
                                        //     'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        // ],
                                        // 'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        // 'templateResult' => new JsExpression('function(items) { return items.text; }'),
                                        // 'templateSelection' => new JsExpression('function (items) { return items.text; }'),
                                    ],
                                ])->label(false); ?>
                                <?= $form->field(new Group(), 'title')->textInput(['value' => $item->title]) ?>
                                <?= $form->field(new Group(), 'description')->textarea() ?>
                                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else : ?>
        <?= Yii::t('easyii', 'No records found') ?>
    <?php endif; ?>
<?php Widget\Portlet::end(); ?>