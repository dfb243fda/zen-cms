zen.contact_forms = zen.contact_forms || {};

zen.apply(zen.contact_forms, {
    renderTagGeneratorResult : function($el) {
        var resultTagName = 'undefined',
            resultTagIsRequired = false,
            resultTagAttribs = [],
            tagType = $el.attr('data-tag'); 
        $el.find('input, textarea, select').each(function(i, v) {
            var $v = $(v),
                nameAttr = $v.attr('name'),
                tagName = $v.prop("tagName").toLowerCase(),
                tagVal = $v.val();
            if ('name' == nameAttr) {
                resultTagName = tagVal;
            } else if ('is_required' == nameAttr) {
                if ($v.is(':checked')) {
                    resultTagIsRequired = true;
                }
            } else {
                if (tagName == 'textarea') {
                    var tagVal = tagVal.trim();                    
                    
                    if (tagVal != '') {
                        tagVal = tagVal.split("\n").join(',');
                    }
                    if (tagVal != '') {
                        resultTagAttribs.push(nameAttr + '="' + tagVal + '"');
                    }                    
                } else if (tagName == 'input' && $v.attr('type') == 'text') {
                    if (tagVal != '') {
                        resultTagAttribs.push(nameAttr + '="' + tagVal + '"');
                    }                    
                } else if (tagName == 'input' && $v.attr('type') == 'checkbox') {
                    if ($v.is(':checked')) {
                        resultTagAttribs.push(nameAttr + '="' + tagVal + '"');
                    }
                }
            }
        });
        
        if (resultTagIsRequired) {
            resultTagIsRequired = '*';
        } else {
            resultTagIsRequired = '';
        }
        
        var attribsStr = '';
        if (!zen.isEmpty(resultTagAttribs)) {
            attribsStr = ' ' + resultTagAttribs.join(' ');
        }
                
        var result1 = '[' + tagType + resultTagIsRequired + ' ' + resultTagName +  attribsStr + ']';
        
        var result2 = '[' + resultTagName + ']';
                
        $el.parent().find('.tag-generator__item-res1 input').val(result1);
        $el.parent().find('.tag-generator__item-res2 input').val(result2);
    },
    initContactFormEditing : function() {
        var me = this,
            useRecCheckbox = $('#contact-forms__block-use-this input[type=checkbox]');
        
        useRecCheckbox.change(function() {
            if ($(this).is(':checked')) {
                $('#contact-forms__block-use-this').next().show();
            } else {
                $('#contact-forms__block-use-this').next().hide();
            }
        });
        if (useRecCheckbox.is(':checked')) {
            $('#contact-forms__block-use-this').next().show();
        } else {
            $('#contact-forms__block-use-this').next().hide();            
        }
        
        $('#tag-generator__items .tag-generator__item-body').each(function(i, v) {
            me.renderTagGeneratorResult($(v));
        });
        $('#tag-generator__select').change(function() {
            var val = $(this).val(),
                items = $('#tag-generator__items');
            $(this).val('');
            
            items.find('.tag-generator__item').hide();
            if (val) {
                items.find('#tag-generator__item-' + val).show();
            }            
        });
        
        $('#tag-generator__items .tag-generator__item-body input, textarea, select').change(function() {
           var $container = $(this).closest('.tag-generator__item-body');
           me.renderTagGeneratorResult($container);
        });
    },
    delForm: function(url, id) {
        jConfirm('Вы собираетесь удалить контактную форму <strong>после удаления её нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить форму?', 
            function(r) {
                if (r) {
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'post',
                        data: {
                            'id': id
                        }, 
                        success: function(data) {
                            $('#easyui-tree-grid').treegrid('reload');
                            if (zen.isDefined(data.page.msg)) {
                                $().toastmessage('showSuccessToast', data.page.msg);
                            }
                            else if (zen.isDefined(data.page.errMsg)) {
                                $().toastmessage('showErrorToast', data.page.errMsg);
                            }
                        }
                    });
                }
            }
       );
    },
});