<?php
use yii\helpers\Html;
use kilyakus\imageprocessor\Image;
use kilyakus\web\widgets as Widget;
?>
<style type="text/css">
.select2-container.form-control {width:0!important;}
.kt-portlet {width:100%;}
.form-group.input-group {display:flex;flex-wrap:nowrap;}
</style>

<?php if($this->context->module->module->id == 'page') : ?>
<style type="text/css">
.select2-container.form-control {width:100%!important;}
</style>
<?php endif; ?>
<?php if(count($fields)) : ?>
    <?php foreach ($fields as $key => $field) : ?>
        <?php if($field->children(1)->andFilterWhere($filter)->orderBy(['order_num' => SORT_DESC])->count()) : ?>
            <?php if(!$field->parents(1)->one()) : ?>

                <?php Widget\Portlet::begin([
                    'title' => Yii::t('easyii', $field->title),
                    'options' => [
                        'class' => 'kt-portlet--bordered',
                    ],
                ]); ?>

            <?php else: ?>
                
                <div class="col-xs-12">
                    <?php Widget\Section::begin([
                        'title' => Yii::t('easyii', $field->title) . ':',
                        'separator' => [
                            'class' => 'kt-separator kt-separator--border-dashed kt-separator--space-lg',
                        ],
                    ]); ?>

            <?php endif; ?>
                <div class="row">
                    <?= $this->render('dataForm',['fields' => $field->children(1)->andFilterWhere($filter)->orderBy(['lft' => $field->lft, 'rgt' => $field->rgt])->all(), 'data' => $data, 'filter' => $filter]) ?>
                </div>

            <?php if(!$field->parents(1)->one()) : ?>

                <?php Widget\Portlet::end(); ?>

            <?php else: ?>

                    <?php Widget\Section::end(); ?>
                </div>

            <?php endif; ?>

        <?php else: ?>

            <?php if(!$field->parents(1)->one()) : ?>

                <!-- <div class="row"> -->

            <?php endif; ?>

            <div class="col-xs-12 col-md-6 col-lg-4">

                <?php
                $value = !empty($data->{$field->name}) ? $data->{$field->name} : null;
                
                if ($field->type === 'string') {

                    $settings = ['id' => 'data-'.$field->name,'class' => 'form-control'];
                    if($field->required == true){
                        $settings = array_merge($settings,['required' => true]);
                    }
                    $html = Html::input('text', "Data[{$field->name}]", $value, $settings);
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                }
                elseif ($field->type === 'integer') {

                    $settings = ['id' => 'data-'.$field->name,'class' => 'form-control input-lg','min' => $field->min,'max' => $field->max, 'step' => ($field->step == 1 ? 'any' : $field->step)];
                    if($field->required == true){
                        $settings = array_merge($settings,['required' => true]);
                    }
                    $html = Html::input('number',"Data[{$field->name}]", $value, $settings);
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                }
                elseif ($field->type === 'text') {

                    $settings = ['id' => 'data-'.$field->name,'class' => 'form-control'];
                    if($field->required == true){
                        $settings = array_merge($settings,['required' => true]);
                    }

                    $html = Html::textarea("Data[{$field->name}]", $value, $settings);
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                }
                elseif ($field->type === 'boolean') {

                    $html = '<label class="v-align mt-10">'. Html::checkbox("Data[{$field->name}][]", $value, ['class' => 'switch','value' => $value,['uncheck' => 0]]).'<span class="ml-10">'.Yii::t('easyii', $field->title).'</span></label>';
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                }
                elseif ($field->type === 'select') {
                    if($field->options){
                        $options = ['' => Yii::t('easyii/' . $this->context->moduleName, 'Select')];
                        foreach(explode(',',$field->options) as $option){
                            $options[\yii\helpers\Inflector::slug($option)] = Yii::t('easyii', $option);
                        }

                        $html = Widget\Select2::widget(['name' => 'Data['.$field->name.']','theme' => 'default', 'data' => $options, 'value' => $value, 'options' => ['multiple' => false], 'pluginOptions' => ['class' => 'form-control', 'closeOnSelect' => true, 'allowClear' => true]]);
                        echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                    }else{

                        echo \kilyakus\shell\directory\controllers\ItemsController::genContainer('',$field,true);

                    }
                }
                elseif ($field->type === 'checkbox') {
                    $options = '';
                    if($field->options){

                        foreach(explode(',',$field->options) as $option){
                            $options[\yii\helpers\Inflector::slug($option)] = Yii::t('easyii', $option);
                        }

                        $html = Widget\Select2::widget(['name' => 'Data['.$field->name.']','theme' => 'default', 'data' => $options, 'value' => $value, 'options' => ['multiple' => true], 'pluginOptions' => ['class' => 'form-control', 'closeOnSelect' => false, 'allowClear' => true]]);
                        echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                    }else{

                        $html = '<label class="d-flex align-items-center m-0 p-2">'. Html::checkbox("Data[{$field->name}][]", $value, ['class' => 'switch','value' => $value]).'<span class="ml-10">'.Yii::t('easyii', $field->title).'</span></label>';
                        echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,false);

                    }
                }
                elseif ($field->type === 'radio') {
                    if($field->options){

                        foreach(explode(',',$field->options) as $option){
                            $checked = $value && (is_array($value) ? in_array($option, $value) : \yii\helpers\Inflector::slug($option) == $value);
                            $options .= '<label class="d-flex align-items-center m-0 p-2">'. Html::radio("Data[{$field->name}][]", $checked, ['class' => 'switch','value' => $option,]) .'<span class="ml-10">'. $option .'</span></label>';
                        }
                        echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($options,$field,true);

                    }else{

                        $value = !empty($data->{$parent->name}) ? $data->{$parent->name} : null;
                        $checked = $value && $field->title == $value[0];
                        $html = '<label class="d-flex align-items-center m-0 p-2">'. Html::radio("Data[{$parent->name}][]", $checked, ['class' => 'switch','value' => Yii::t('easyii', $field->title)]).'<span class="ml-10">'.Yii::t('easyii', $field->title).'</span></label>';
                        echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,false);

                    }
                } ?>
            
            </div>

            <?php if(!$field->parents(1)->one()) : ?>
                
                <!-- </div> -->

            <?php endif; ?>

        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>