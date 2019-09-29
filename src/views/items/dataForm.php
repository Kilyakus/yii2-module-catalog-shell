<?php if(count($dataForm) == 1) : ?>
    <?= $dataForm; ?>
<?php else: ?>
    <?php foreach ($dataForm as $data) : ?>
        <?= $data; ?>
    <?php endforeach; ?>
<?php endif; ?>
<?php 

$js = <<< JS
$(document).ready(function(){
    $('.model-form select').on('change',function(){
        var items = $('.model-form select[data-append],.model-form input[data-append]');
        console.log()
        $('#item-title').val(null);
        var text = $('#category_id option:selected').text();
        for (var i = 0; i < items.length; i++) {
            if(items[i].tagName == 'SELECT'){
                if($(items[i]).val() != '' && items[i].id != 'category_id'){
                    text +=  ', ' + $("#" + items[i].id + " option:selected").text();
                }
            }else{
                if($(items[i]).value != '' && items[i].id){
                    if($('#'+items[i].id).val() != ''){
                        text += ', ' + $('#'+items[i].id).val();
                    }
                }
            }
        }
        $('#item-title').val(text);
        text = '';
    })
})
JS;
$this->registerJs($js, yii\web\View::POS_END); ?>