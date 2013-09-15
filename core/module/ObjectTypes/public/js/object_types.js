zen.objectTypes = zen.objectTypes || {};

(function($) {
    zen.apply(zen.objectTypes, {
        
        showFormMsg : function(msg, $block) {
            $.each(msg, function(i, v) {
                var errHtml = '';
                errHtml += '<ul class="form-element__errors">';
                $.each(v, function(i2, v2) {
                    errHtml += '<li>' + v2 + '</li>';
                });
                errHtml += '</ul>';
                $block.find('[name=' + i + ']').closest('.form-element').addClass('form-element__has_errors');
                $block.find('[name=' + i + ']').next('ul').remove();
                $block.find('[name=' + i + ']').after(errHtml);
            });
        },
        
        initParams: {},
        init: function(initParams) {
            var me = this;
            
            this.initParams = initParams;
            $('#btn-add-fields-group').click(function() {
                var $block = $('#block-example').clone();
                $block.removeClass('hidden').removeAttr('id').appendTo('#groups-wrap');
                $block.find('form').ajaxForm({
                    url: initParams['addFieldGroupUrl'],
                    success: function(data) {
                        if (zen.isDefined(data.page.msg)) {
                            $().toastmessage('showSuccessToast', data.page.msg);
                        }
                        else if (zen.isDefined(data.page.errMsg)) {
                            $().toastmessage('showErrorToast', data.page.errMsg);
                        }
                        
                        if (zen.isDefined(data.page.formMsg)) {
                            me.showFormMsg(data.page.formMsg, $block);
                        }

                        if (data.page.success) {
                            $block.find('.block__title').html(data.page.title + ' [ ' + data.page.name + ' ]');
                            $block.find('.group-block').removeClass('hidden');
                            $block.find('.group-edit-block').addClass('hidden');
                            $block.find('.icons').removeClass('hidden');
                            $block.attr('data-group-id', data.page.groupId);

                            $block.find('form button[name=cancel]').attr('onclick', 'zen.chameleonTheme.objectTypes.cancelGroupEditing(this)');

                            $block.find('form').ajaxForm({
                                url: initParams['editFieldGroupUrl'].replace('--GROUP_ID--', data.page.groupId),
                                success: function(data) {
                                    if (zen.isDefined(data.page.msg)) {
                                        $().toastmessage('showSuccessToast', data.page.msg);
                                    }
                                    else if (zen.isDefined(data.page.errMsg)) {
                                        $().toastmessage('showErrorToast', data.page.errMsg);
                                    }

                                    if (data.page.success) {
                                        $block.find('.block__title').html(data.page.title + ' [ ' + data.page.name + ' ]');
                                        $block.find('.group-block').removeClass('hidden');
                                        $block.find('.group-edit-block').addClass('hidden');
                                    }                            
                                }
                            });
                        }               
                    }
                });
            });

            $('#groups-wrap').sortable({
     //           axis: 'y',
                handle: '.block__title',
 //               containment: 'parent',
                placeholder: 'sortable-placeholder', 
                forcePlaceholderSize: true,
                stop: function(e, ui) {
                    if (!zen.isDefined(ui.item.attr('data-group-id'))) {
                        return false;
                    }

                    var data = {};
                    data['groupBefore'] = 0;
                    if (zen.isDefined(ui.item.prev().attr('data-group-id'))) {
                        data['groupBefore'] = ui.item.prev().attr('data-group-id');
                    } 

                    data['group'] = ui.item.attr('data-group-id');
                    $.ajax({
                        type: 'post',
                        data: data,
                        url: initParams['sortGroupUrl']
                    });
                }
            });

            $('#groups-wrap .group-block__list').sortable({
 //               axis: 'y',
                containment: '#groups-wrap',
                connectWith: '.group-block__list',
    //            placeholder: 'sortable-placeholder', 
                forcePlaceholderSize: true,
                stop: function(e, ui) {
       //             if (this === ui.item.parent()[0]) {

                        if (!zen.isDefined(ui.item.attr('data-field-id'))) {
                            return false;
                        }

                        var data = {};
                        data['fieldBefore'] = 0;
                        if (zen.isDefined(ui.item.prev().attr('data-field-id'))) {
                            data['fieldBefore'] = ui.item.prev().attr('data-field-id');
                        } 

                        data['field'] = ui.item.attr('data-field-id');
                        data['group'] = ui.item.attr('data-group-id');
                        data['groupTarget'] = ui.item.closest('.block').attr('data-group-id');
                        $.ajax({
                            type: 'post',
                            data: data,
                            url: initParams['sortFieldUrl'],
                            success: function() {
                                ui.item.attr('data-group-id', data['groupTarget']);
                            }
                        });
                    }
   //             }
            });



            $('#groups-wrap .group-edit-block form').each(function(i, v) {
                var $block = $(v).closest('.block');

                var groupId = $block.attr('data-group-id');
                var objectTypeId = $block.closest('.blocks').attr('data-object-type-id');

                $(v).ajaxForm({
                    url: initParams['editFieldGroupUrl'].replace('--GROUP_ID--', groupId).replace('--TYPE_ID--', objectTypeId),
                    success: function(data) {
                        if (zen.isDefined(data.page.msg)) {
                            $().toastmessage('showSuccessToast', data.page.msg);
                        }
                        else if (zen.isDefined(data.page.errMsg)) {
                            $().toastmessage('showErrorToast', data.page.errMsg);
                        }
                        
                        if (zen.isDefined(data.page.formMsg)) {
                            me.showFormMsg(data.page.formMsg, $(v));
                        }

                        if (data.page.success) {
                            $block.find('.block__title').html(data.page.title + ' [ ' + data.page.name + ' ]');
                            $block.find('.group-block').removeClass('hidden');
                            $block.find('.group-edit-block').addClass('hidden');
                        }                            
                    }
                });
            });

            $('#groups-wrap .group-block__item-edit form').each(function(i, v) {
                var $blockItem = $(v).closest('.group-block__item');

                var fieldId = $blockItem.attr('data-field-id');
                var groupId = $blockItem.attr('data-group-id');

                $(v).ajaxForm({
                    url: initParams['editFieldUrl'].replace('--FIELD_ID--', fieldId).replace('--GROUP_ID--', groupId),
                    success: function(data) {
                        if (zen.isDefined(data.page.msg)) {
                            $().toastmessage('showSuccessToast', data.page.msg);
                        }
                        else if (zen.isDefined(data.page.errMsg)) {
                            $().toastmessage('showErrorToast', data.page.errMsg);
                        }
                        
                        if (zen.isDefined(data.page.formMsg)) {
                            me.showFormMsg(data.page.formMsg, $(v));
                        }

                        if (data.page.success) {                                
                            var col = $blockItem.find('.group-block__item-block').find('.group-block__item-c1');
                            col.eq(0).html(data.page.title);
                            col.eq(1).html('[ ' + data.page.name + ' ]');
                            col.eq(2).html(data.page.fieldTypeName);
                            $blockItem.find('.group-block__item-block').removeClass('hidden');
                            $blockItem.find('.group-block__item-edit').addClass('hidden');
                        }                            
                    }
                });
            });
        },
        delObjectType: function(url, objectTypeId) {
            jConfirm('Вы собираетесь удалить тип данных. Вместе с типом данных удалятся все его объекты, <strong>После удаления его нельзя будет восстановить</strong>',
                'Вы действительно хотите удалить тип данных?', 
                function(r) {
                    if (r) {
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: {
                                id: objectTypeId
                            },
                            dataType: 'json',
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
        showEditGroupForm: function(obj) {
            var $block = $(obj).closest('.block');
            $block.find('.group-block').addClass('hidden');
            $block.find('.group-edit-block').removeClass('hidden');
        },
        delGroup: function(obj) {
            var $block = $(obj).closest('.block');
            var groupId = $block.attr('data-group-id');
            var objectTypeId = $(obj).closest('.blocks').attr('data-object-type-id');
            var me = this;

            jConfirm('Вы собираетесь удалить группу полей. <strong>После удаления её нельзя будет восстановить</strong>',
                'Вы действительно хотите удалить группу полей?', 
                function(r) {
                    if (r) {
                        $.ajax({
                            url: me.initParams['delGroupUrl'].replace('--GROUP_ID--', groupId),
                            'type': 'post',
                            'data': {
                                'groupId' : groupId,
                                'objectTypeId' : objectTypeId
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (zen.isDefined(data.page.msg)) {
                                    $().toastmessage('showSuccessToast', data.page.msg);
                                }
                                else if (zen.isDefined(data.page.errMsg)) {
                                    $().toastmessage('showErrorToast', data.page.errMsg);
                                }
                                if (data.page.success) {
                                    $block.remove();
                                }
                            }
                        });
                    }   
                });

        },

        cancelGroupAdding: function(obj) {
            var $block = $(obj).closest('.block');
            $block.remove();
        },

        cancelGroupEditing: function(obj) {
            var $block = $(obj).closest('.block');
            $block.find('.group-block').removeClass('hidden');
            $block.find('.group-edit-block').addClass('hidden');
        },
        showEditFieldForm: function(obj) {
            var $blockItem = $(obj).closest('.group-block__item');
            $blockItem.find('.group-block__item-block').addClass('hidden');
            $blockItem.find('.group-block__item-edit').removeClass('hidden');
        },

        cancelFieldAdding: function(obj) {
            var $blockItem = $(obj).closest('.group-block__item');
            $blockItem.remove();
        },

        cancelFieldEditing: function(obj) {
            var $blockItem = $(obj).closest('.group-block__item');

            $blockItem.find('.group-block__item-block').removeClass('hidden');
            $blockItem.find('.group-block__item-edit').addClass('hidden');
        },

        showAddFieldForm: function(obj) {
            var me = this,
            $groupBlock = $(obj).closest('.group-block'),                               
            groupId = $(obj).closest('.block').attr('data-group-id'),   
            $fieldExampleBlock = $('#field-example').clone().removeClass('hidden').removeAttr('id'),
            $blockItem = $fieldExampleBlock;

            $fieldExampleBlock.appendTo($groupBlock.find('.group-block__list'));

            $fieldExampleBlock.find('form').ajaxForm({
                url: me.initParams['addFieldUrl'].replace('--GROUP_ID--', groupId),
                success: function(data) {                        
                    if (zen.isDefined(data.page.msg)) {
                        $().toastmessage('showSuccessToast', data.page.msg);
                    }
                    else if (zen.isDefined(data.page.errMsg)) {
                        $().toastmessage('showErrorToast', data.page.errMsg);
                    }
                    
                    if (zen.isDefined(data.page.formMsg)) {
                        me.showFormMsg(data.page.formMsg, $fieldExampleBlock);
                    }

                    if (data.page.success) {
                        var col = $blockItem.find('.group-block__item-block').find('.group-block__item-c1');
                        col.eq(0).html(data.page.title);
                        col.eq(1).html('[ ' + data.page.name + ' ]');
                        col.eq(2).html(data.page.fieldTypeName);
                        $blockItem.find('.group-block__item-block').removeClass('hidden');
                        $blockItem.find('.group-block__item-edit').addClass('hidden');
                        $blockItem.attr('data-field-id', data.page.id);
                        $blockItem.attr('data-group-id', groupId);

                        $blockItem.find('button[name=cancel]').attr('onclick', 'zen.chameleonTheme.objectTypes.cancelFieldEditing(this)')

                        $fieldExampleBlock.find('form').ajaxForm({
                            url: me.initParams['editFieldUrl'].replace('--FIELD_ID--', data.page.id).replace('--GROUP_ID--', groupId),
                            success: function(data) {
                                if (zen.isDefined(data.page.msg)) {
                                    $().toastmessage('showSuccessToast', data.page.msg);
                                }
                                else if (zen.isDefined(data.page.errMsg)) {
                                    $().toastmessage('showErrorToast', data.page.errMsg);
                                }
                                
                                if (zen.isDefined(data.page.formMsg)) {
                                    me.showFormMsg(data.page.formMsg, $fieldExampleBlock);
                                }

                                if (data.page.success) {                                
                                    var col = $blockItem.find('.group-block__item-block').find('.group-block__item-c1');
                                    col.eq(0).html(data.page.title);
                                    col.eq(1).html('[ ' + data.page.name + ' ]');
                                    col.eq(2).html(data.page.fieldTypeName);
                                    $blockItem.find('.group-block__item-block').removeClass('hidden');
                                    $blockItem.find('.group-block__item-edit').addClass('hidden');
                                }                            
                            }
                        });
                    }                            
                }
            });
        },

        delField: function(obj) {
            var $block = $(obj).closest('.block');
            var groupId = $block.attr('data-group-id');
            var fieldId = $(obj).closest('.group-block__item').attr('data-field-id');
            var objectTypeId = $(obj).closest('.blocks').attr('data-object-type-id');
            
            var me = this;

            jConfirm('После удаления поле нельзя будет восстановить',
                'Вы действительно хотите удалить поле?', 
                function(r) {
                    if (r) {
                        $.ajax({
                            url: me.initParams['delFieldUrl'],
                            type: 'post',
                            data: {
                                'groupId' : groupId,
                                'fieldId' : fieldId,
                                'objectTypeId' : objectTypeId
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (zen.isDefined(data.page.msg)) {
                                    $().toastmessage('showSuccessToast', data.page.msg);
                                }
                                else if (zen.isDefined(data.page.errMsg)) {
                                    $().toastmessage('showErrorToast', data.page.errMsg);
                                }
                                if (data.page.success) {
                                    $(obj).closest('.group-block__item').remove();
                                }
                            }
                        });
                    }   
                });
        },

        delObject: function(url) {
            jConfirm('Вы собираетесь удалить объект. Действие необратимо',
                'Вы действительно хотите удалить объект?', 
                function(r) {
                    if (r) {
                        $.ajax({
                            url: url,
                            dataType: 'json',
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
        }           
        
    });
})(jQuery);    