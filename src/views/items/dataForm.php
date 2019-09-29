<?php
use yii\helpers\Html;
use kilyakus\web\widgets as Widget;
?>
<style type="text/css">
.input-group {position:relative;display:flex;flex-wrap:wrap;align-items:stretch;width:100%;}
  .input-group > .form-control,
  .input-group > .form-control-plaintext,
  .input-group > .custom-select,
  .input-group > .custom-file {position:relative;flex:1 1 auto;width:1%;margin-bottom:0;}
    .input-group > .form-control + .form-control,
    .input-group > .form-control + .custom-select,
    .input-group > .form-control + .custom-file,
    .input-group > .form-control-plaintext + .form-control,
    .input-group > .form-control-plaintext + .custom-select,
    .input-group > .form-control-plaintext + .custom-file,
    .input-group > .custom-select + .form-control,
    .input-group > .custom-select + .custom-select,
    .input-group > .custom-select + .custom-file,
    .input-group > .custom-file + .form-control,
    .input-group > .custom-file + .custom-select,
    .input-group > .custom-file + .custom-file {margin-left:-1px;}
  .input-group > .form-control:focus,
  .input-group > .custom-select:focus,
  .input-group > .custom-file .custom-file-input:focus ~ .custom-file-label {z-index:3;}
  .input-group > .custom-file .custom-file-input:focus {z-index:4;}
  .input-group > .form-control:not(:last-child),
  .input-group > .custom-select:not(:last-child) {border-top-right-radius:0;border-bottom-right-radius:0;}
  .input-group > .form-control:not(:first-child),
  .input-group > .custom-select:not(:first-child) {border-top-left-radius:0;border-bottom-left-radius:0;}
  .input-group > .custom-file {display:flex;align-items:center;}
    .input-group > .custom-file:not(:last-child) .custom-file-label,
    .input-group > .custom-file:not(:last-child) .custom-file-label::after {border-top-right-radius:0;border-bottom-right-radius:0;}
    .input-group > .custom-file:not(:first-child) .custom-file-label {border-top-left-radius:0;border-bottom-left-radius:0;}

.input-group-prepend,
.input-group-append {width:auto;display:flex;}
  .input-group-prepend .btn,
  .input-group-append .btn {position:relative;z-index:2;}
    .input-group-prepend .btn:focus,
    .input-group-append .btn:focus {z-index:3;}
  .input-group-prepend .btn + .btn,
  .input-group-prepend .btn + .input-group-text,
  .input-group-prepend .input-group-text + .input-group-text,
  .input-group-prepend .input-group-text + .btn,
  .input-group-append .btn + .btn,
  .input-group-append .btn + .input-group-text,
  .input-group-append .input-group-text + .input-group-text,
  .input-group-append .input-group-text + .btn {margin-left:-1px;}

.input-group-prepend {margin-right:-1px;}

.input-group-append {margin-left:-1px;}

.input-group-text {display:flex;align-items:center;padding:0.65rem 1rem;margin-bottom:0;font-size:1rem;font-weight:400;line-height:1.5;color:#74788d;text-align:center;white-space:nowrap;background-color:#f7f8fa;border:1px solid #e2e5ec;border-radius:4px;}
  .input-group-text input[type="radio"],
  .input-group-text input[type="checkbox"] {margin-top:0;}

.input-group-lg > .form-control:not(textarea),
.input-group-lg > .custom-select {height:calc(1.5em + 2.3rem + 2px);}

.input-group-lg > .form-control,
.input-group-lg > .custom-select,
.input-group-lg > .input-group-prepend > .input-group-text,
.input-group-lg > .input-group-append > .input-group-text,
.input-group-lg > .input-group-prepend > .btn,
.input-group-lg > .input-group-append > .btn {padding:1.15rem 1.65rem;font-size:1.25rem;line-height:1.5;border-radius:0.3rem;}

.input-group-sm > .form-control:not(textarea),
.input-group-sm > .custom-select {height:calc(1.5em + 1rem + 2px);}

.input-group-sm > .form-control,
.input-group-sm > .custom-select,
.input-group-sm > .input-group-prepend > .input-group-text,
.input-group-sm > .input-group-append > .input-group-text,
.input-group-sm > .input-group-prepend > .btn,
.input-group-sm > .input-group-append > .btn {padding:0.5rem 1rem;font-size:0.875rem;line-height:1.5;border-radius:0.2rem;}

.input-group-lg > .custom-select,
.input-group-sm > .custom-select {padding-right:2rem;}

.input-group > .input-group-prepend > .btn,
.input-group > .input-group-prepend > .input-group-text,
.input-group > .input-group-append:not(:last-child) > .btn,
.input-group > .input-group-append:not(:last-child) > .input-group-text,
.input-group > .input-group-append:last-child > .btn:not(:last-child):not(.dropdown-toggle),
.input-group > .input-group-append:last-child > .input-group-text:not(:last-child) {border-top-right-radius:0;border-bottom-right-radius:0;}

.input-group > .input-group-append > .btn,
.input-group > .input-group-append > .input-group-text,
.input-group > .input-group-prepend:not(:first-child) > .btn,
.input-group > .input-group-prepend:not(:first-child) > .input-group-text,
.input-group > .input-group-prepend:first-child > .btn:not(:first-child),
.input-group > .input-group-prepend:first-child > .input-group-text:not(:first-child) {border-top-left-radius:0;border-bottom-left-radius:0;}

.input-group-append .btn.btn-icon,
.input-group-prepend .btn.btn-icon {height:auto;}

.row {display:flex;flex-wrap:wrap;margin-right:-10px;margin-left:-10px;}

.select2-container.form-control {width:0px!important;}
</style>
<?php foreach ($fields as $key => $field) : ?>
    <?php if(count($field->children)) : ?>
        <?php if(!$field->parent) : ?>

            <?php Widget\Portlet::begin([
                'title' => $field->title,
                'options' => [
                    'class' => 'kt-portlet--bordered',
                ],
            ]); ?>

        <?php else: ?>
            
            <div class="col-xs-12">
                <?php Widget\Section::begin([
                    'title' => $field->title . ':',
                    'separator' => [
                        'class' => 'kt-separator kt-separator--border-dashed kt-separator--space-lg',
                    ],
                ]); ?>

        <?php endif; ?>
            <div class="row">
                <?= $this->render('dataForm',['fields' => $field->children, 'data' => $data]) ?>
            </div>

        <?php if(!$field->parent) : ?>

            <?php Widget\Portlet::end(); ?>

        <?php else: ?>

                <?php Widget\Section::end(); ?>
            </div>

        <?php endif; ?>

    <?php else: ?>

        <?php if(!$field->parent) : ?>

            <div class="row">

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

                $html = '<label class="v-align mt-10">'. Html::checkbox("Data[{$field->name}][]", $value, ['class' => 'switch','value' => $value,['uncheck' => 0]]).'<span class="ml-10">'.$field->title.'</span></label>';
                echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

            }
            elseif ($field->type === 'select') {
                if($field->options){
                    $options = ['' => Yii::t('easyii/' . $this->context->moduleName, 'Select')];
                    foreach(explode(',',$field->options) as $option){
                        $options[\yii\helpers\Inflector::slug($option)] = $option;
                    }
                    $html = Widget\Select2::widget(['id' => $field->name . '-' . $field->field_id, 'name' => 'Data['.$field->name.']','theme' => 'default', 'data' => $options, 'value' => $value, 'pluginOptions' => ['class' => 'form-control']]);
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                }else{

                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($alert,$field,true);

                }
            }
            elseif ($field->type === 'checkbox') {
                $options = '';
                if($field->options){

                    // foreach(explode(',',$field->options) as $option){
                    //     $checked = $value && (is_array($value) ? in_array($option, $value) : \yii\helpers\Inflector::slug($option) == $value);
                    //     $options .= '<label class="v-align mt-10">'. Html::checkbox("Data[{$field->name}][]", $checked, ['class' => 'switch','value' => $option,]) .'<span class="ml-10">'. $option .'</span></label>';
                    // }
                    // echo self::genContainer($options,$field,false);
                    foreach(explode(',',$field->options) as $option){
                        $options[\yii\helpers\Inflector::slug($option)] = $option;
                    }

                    $html = Widget\Select2::widget(['id' => $field->name . '-' . $field->field_id, 'name' => 'Data['.$field->name.']','theme' => 'default', 'data' => $options, 'value' => $value, 'options' => ['multiple' => true], 'pluginOptions' => ['class' => 'form-control', 'closeOnSelect' => false]]);
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,true);

                }else{

                    $html = '<label class="v-align p-2">'. Html::checkbox("Data[{$field->name}][]", $value, ['class' => 'switch','value' => $value]).'<span class="ml-10">'.$field->title.'</span></label>';
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,false);

                }
            }
            elseif ($field->type === 'radio') {
                if($field->options){

                    foreach(explode(',',$field->options) as $option){
                        $checked = $value && (is_array($value) ? in_array($option, $value) : \yii\helpers\Inflector::slug($option) == $value);
                        $options .= '<label class="v-align p-2">'. Html::radio("Data[{$field->name}][]", $checked, ['class' => 'switch','value' => $option,]) .'<span class="ml-10">'. $option .'</span></label>';
                    }
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($options,$field,true);

                }else{

                    $value = !empty($data->{$parent->name}) ? $data->{$parent->name} : null;
                    $checked = $value && $field->title == $value[0];
                    $html = '<label class="v-align p-2">'. Html::radio("Data[{$parent->name}][]", $checked, ['class' => 'switch','value' => $field->title]).'<span class="ml-10">'.$field->title.'</span></label>';
                    echo \kilyakus\shell\directory\controllers\ItemsController::genContainer($html,$field,false);

                }
            } ?>
        
        </div>

        <?php if(!$field->parent) : ?>
            
            </div>

        <?php endif; ?>

    <?php endif; ?>
<?php endforeach; ?>

<?php 

$js = <<< JS
// $(document).ready(function(){
//     $('.model-form select').on('change',function(){
//         var items = $('.model-form select[data-append],.model-form input[data-append]');
//         console.log()
//         $('#item-title').val(null);
//         var text = $('#category_id option:selected').text();
//         for (var i = 0; i < items.length; i++) {
//             if(items[i].tagName == 'SELECT'){
//                 if($(items[i]).val() != '' && items[i].id != 'category_id'){
//                     text +=  ', ' + $("#" + items[i].id + " option:selected").text();
//                 }
//             }else{
//                 if($(items[i]).value != '' && items[i].id){
//                     if($('#'+items[i].id).val() != ''){
//                         text += ', ' + $('#'+items[i].id).val();
//                     }
//                 }
//             }
//         }
//         $('#item-title').val(text);
//         text = '';
//     })
// })
// $('.switch').switcher({copy: {en: {yes: '', no: ''}}}).on('change', function(){
//     var checkbox = $(this);
//     if(checkbox.data('link')){
//         checkbox.switcher('setDisabled', true);
//         $.getJSON(checkbox.data('link') + ((checkbox.data('sublink')) ? '/'+checkbox.data('sublink') : '/') +(checkbox.is(':checked') ? 'on' : 'off') + '/' + checkbox.data('id'), function(response){
//             if(response.result === 'error'){
//                 alert(response.error);
//             }
//             if(checkbox.data('reload')){
//                 location.reload();
//             }else{
//                 checkbox.switcher('setDisabled', false);
//             }
//         });
//     }
// });
JS;
$this->registerJs($js, yii\web\View::POS_END);
 ?>