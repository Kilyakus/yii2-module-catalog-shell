$(function(){
    var table = $('#categoryFields > tbody');

    table.on('click', '.delete-field', function(){
        if(table.find('tr').length > 1) {
            $(this).closest('tr').remove();
        }
        return false;
    });

    table.on('click', '.move-up', function(){
        var current = $(this).closest('tr');
        var previos = current.prev();
        if(previos.get(0)){
            previos.before(current);
        }
        return false;
    });

    table.on('click', '.move-down', function(){
        var current = $(this).closest('tr');
        var next = current.next();
        if(next.get(0)){
            next.after(current);
        }
        return false;
    });

    table.on('change', '.field-type', function(){
        var $this = $(this);
        var type = $this.val();
        var options = $this.closest('tr').find('.field-options');

        if(optionsIsNeeded(type)){
            options.removeClass('hidden');
        }else{
            options.addClass('hidden');
        }
    });

    $('#addField').on('click', function(){
        // table.append(fieldTemplate);
        addField(table)
        var fO = $('.field-options');
        for (var i = 0; i < fO.length; i++) {
            if(fO[i].id == 'fo'){
                $(fO[i]).attr('id','fo'+[i]);
                jQuery('#'+fO[i].id).selectize({"plugins":["remove_button"],"valueField":"name","labelField":"name","searchField":["name"],"create":true,"load":function (query, callback) { if (!query.length) return callback(); $.getJSON("/admin/tags/list", { query: query }, function (data) { callback(data); }).fail(function () { callback(); }); }});
            // console.log(fO[i].id);
            }
        }
        
    });

    table.on('click', '.add', function(){
        var t = $($($(this).parents('tr')).get(0));
        var type = $.trim(t.find('.field-type').val());
        var id = $.trim(t.find('.field-name').val());

        if(!t.find('.options').hasClass('hidden') && type != 'table'){
            t.find('.options').addClass('hidden');
            t.find('[colspan=1]').attr('colspan','2');
        }
        if(!id){
            id = $($(this).parents('tr')).attr('data-field');
        }

        addSubField(this,id,type);
        var fO = $('.field-options');
        for (var i = 0; i < fO.length; i++) {
            if(fO[i].id == 'fo'){
                $(fO[i]).attr('id','fo'+[i]);
                jQuery('#'+fO[i].id).selectize({"plugins":["remove_button"],"valueField":"name","labelField":"name","searchField":["name"],"create":true,"load":function (query, callback) { if (!query.length) return callback(); $.getJSON("/admin/tags/list", { query: query }, function (data) { callback(data); }).fail(function () { callback(); }); }});
            }
        }
        
    });

    var fields = $('.table-field');

    function selectOptions(){
        for (var n = 0; n < fields.length; n++) {
            
            var find = $(fields[n]).find('tbody');
            var tr = find.find('tr[data-form="true"]');
            var line = find.find('tr[data-form="false"]');
            var select = find.find('select');
            var actSel;
            $(select).on('click',function(){
                actSel = $(this).val();
            });
            $(select).on('change',function(){
                counter = 0;
                var hidden, active, number = getNumber($(this).parent('td'));
                prevOff(number,select);
                for (var i = 0; i < select.length; i++) {
                    var parent = $(select[i]).parent('td');
                    
                    if(!$(parent).hasClass('hidden')){
                        active = $(select[i]);
                        hidden = 1;
                    }else{
                        hidden = hidden + 1;
                        $(select[i]).val(active.val());
                    }                  
                }
            })
            
            for (var s = 0; s < tr.length; s++) {
                var td = $(tr[s]).find('td');
                var tl = $(line[s]).find('td');
                
                for (var i = 0; i < td.length+1; i++) {
                    var active;
                    var me = $(td[i]);
                    var he = $(td[i+1]);
                    var ct = $($(me.find('select')).find('option')).text();
                    var cn = $($(he.find('select')).find('option')).text();
                    if(!ct){
                        ct = $(me.find('input')).val();
                    }
                    if(!cn){
                        cn = $(he.find('input')).val();
                    }
                    
                    if(me.find('select').val() == he.find('select').val() && ct == cn){
                        if(!me.hasClass('hidden')){
                            active = me;
                        }
                        active.attr('colspan',int(active.attr('colspan'))+1);
                        he.addClass('hidden');
                        moveClass($(tl[i]).find('.del-line'),'del-line','add-line');
                    }else{
                        // $($(tl[i]).find('.del-line')).remove();
                        me.attr('colspan',int(1));
                    }
                }
            }
        }
    }

    $(document).ready(function(){
        
        selectOptions();
        
    })

    $('.table-field select').on('change',function(){

        counter = 0;

        var cs = 'colspan',tr = getParent(this,'tr'),td = getParent(this,'td'),gn = getNumber(td),arr = $(tr).find('td'),td_this = $(arr[prevOff(gn,arr)]),td_next = $(arr[gn+1]);

        if(td_next.hasClass('hidden')){
            var csn = (int(td_this.attr(cs))+int(td_next.attr(cs)));
            td_this.attr(cs,csn);
            td_next.find('select').val(td_this.find('select').val());
            td_next.addClass('hidden');
        }else{
            td_this.attr(cs,1);
            td_next.removeClass('hidden');
        }

        var active;
        var hidden;
        for (var i = 0; i < arr.length; i++) {
            if(!$(arr[i]).hasClass('hidden')){
                active = $(arr[i]);
                hidden = 1;
            }else{
                hidden = hidden + 1;
                $(arr[i]).find('select').val(active.find('select').val());
                $(arr[i]).find('form').submit();
                $($(arr[i]).find('form')).on('submit', function() {
                  return false;
                });
                active.attr(cs,hidden);
                $(arr[i]).attr(cs,1);
            }
        }
    })

    $('.del-line').on('click',function(e){

        counter = 0;

        var cs = 'colspan',tr = getParent(this,'tr'),td = getParent(this,'td'),gn = getNumber(td),arr = $(getThis($(tr).next('tr'))).find('td'),td_this = $(arr[prevOff(gn,arr)]),td_next = $(arr[gn+1]);

        moveClass($(this),'del-line','add-line');

        if(!td_next.hasClass('hidden')){
            var csn = (int(td_this.attr(cs))+int(td_next.attr(cs)));
            td_this.attr(cs,csn);
            td_next.find('select').val(td_this.find('select').val());

            var chkn = td_next.find('[type=checkbox]');
            var chkt = td_this.find('[type=checkbox]');

            for (var i = 0; i < chkn.length; i++) {
                if($(chkn[i]).prop('checked')){
                    $(chkn[i]).click()
                }
            }

            for (var i = 0; i < chkn.length; i++) {
                if($(chkt[i]).prop('checked')){
                    if(!$(chkn[i]).prop('checked')){
                        $(chkn[i]).click()
                    }
                }
            }
            td_next.find('select').trigger('change');
            td_next.find('form').submit();
            $(td_next.find('form')).on('submit', function() {
              return false;
            });
            td_next.addClass('hidden');
            return false;
        }else{
            td_this.attr(cs,1);
            td_next.removeClass('hidden');
        }

        var active;
        var hidden;
        for (var i = 0; i < arr.length; i++) {
            if(!$(arr[i]).hasClass('hidden')){
                active = $(arr[i]);
                hidden = 1;
            }else{
                hidden = hidden + 1;
                $(arr[i]).find('select').val(active.find('select').val());
                active.attr(cs,hidden);
                $(arr[i]).attr(cs,1);
            }
        }

        
    })

    function prevOff(number,arr){
        if($(arr[number]).hasClass('hidden')){
            if(number > 0 && number <= $(arr).length){
                number = number - 1;
            }
            return prevOff(number,arr);
        }else{
            return number;
        }
    }

    function switchClass(el,cl){
        if(el.hasClass(cl)){
            el.removeClass(cl);
        }else{
            el.addClass(cl);
        }
    }

    function moveClass(el,c1,c2){
        if(el.hasClass(c1)){
            el.removeClass(c1);
            el.addClass(c2);
        }else{
            el.removeClass(c2);
            el.addClass(c1);
        }
    }

    $('#saveCategoryBtn').on('click', function(){
        var form = '<input type="hidden" name="save" value="1">';

        var subfields = [];

        table.find('tr').each(function(i, element) {
            var $this = $(element);
            var datasub = $(element).attr('data-sub');
            
            if(datasub){

                var type;
                if($($('[data-field='+datasub+']')).find('.field-type').val() == 'table'){
                    type = $this.find('.field-type').val();
                }else{
                    type = $($('[data-field='+datasub+']')).find('.field-type').val();
                }
                
                var child = {
                    image : $.trim($this.find('.field-image').attr('data-image')),
                    name : $.trim($this.find('.field-title').val()), //$.trim($this.find('.field-name').val())
                    title : $.trim($this.find('.field-title').val()),
                    type : type,
                    options : $this.find('.field-options').val(),
                };

                if(child.name != '') {
                    if(!subfields[datasub]){
                        subfields[datasub] = '';
                    }
                    subfields[datasub] += JSON.stringify(child)+',';
                }

            }

        });
        
        table.find('tr').each(function(i, element) {
            var $this = $(element);
            if(!$(element).attr('data-sub')){
                //).replace(/,\s*$/, "");
                var childs = subfields[$.trim($this.find('.field-name').val())];

                if(childs){
                    var data = {
                        image : $.trim($this.find('.field-image').attr('data-image')),
                        name : $.trim($this.find('.field-title').val()), //$.trim($this.find('.field-name').val())
                        title : $.trim($this.find('.field-title').val()),
                        type : $this.find('.field-type').val(),
                        options : $this.find('.field-options').val(),
                        child : JSON.parse('['+childs.replace(/,\s*$/, "")+']'),
                    };
                }else{
                    var data = {
                        image : $.trim($this.find('.field-image').attr('data-image')),
                        name : $.trim($this.find('.field-title').val()), //$.trim($this.find('.field-name').val())
                        title : $.trim($this.find('.field-title').val()),
                        type : $this.find('.field-type').val(),
                        options : $this.find('.field-options').val(),
                    };
                }
                

                if(data.name != '') {
                    form += '<input type="hidden" name="Field[' + i + ']" value=\'' + JSON.stringify(data) + '\'>';
                }
            }
        });

        // console.log(form);return;
        $('<form method="post">' + form + '</form>').appendTo('body').submit();

        return false;
    });
    
    function optionsIsNeeded(type)
    {
        return type == 'select' || type == 'checkbox' || type == 'radio' || type == 'table';
    }

    function int(number){return Number.parseInt(number);};

    function push(arr,value){if(!check(arr,value)){arr.push(value);}}

    function check(arr,value){let check = arr.find(function(el){return (value == el)});return check;}

    function getParent(el,parent){return getThis($(el).parents(parent));}

    function getThis(el){return $(el).get(0);};

    function getNumber(el){var prev=$(el).prev();if(prev.length!==0){counter++;getNumber(prev);};return counter;}

});