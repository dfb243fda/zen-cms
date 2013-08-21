zen.pages = zen.pages || {};

zen.apply(zen.pages, {
    options: null,
    initPagesEdit: function(options) {
        var self = this;
        this.options = options;
        this.initMarkersSorting();
        
        if (zen.isDefined(options['updatePageContentUrl'])) {            
            $('#template').focus(function() {
                $(this).data('prevVal', $(this).val());
            }).change(function() {
                var me = this,
                    templateId = $(me).val();
                    
                $(me).val($(me).data('prevVal'));
                
                $.ajax({
                    url: options['updatePageContentUrl'].replace('--PAGE--', options['pageId']).replace('--TEMPLATE--', templateId),
                    dataType: 'json',
                    success: function(data) {
                        $('#markers-wrap').html(data.result);
                        self.initMarkersSorting();
                        $(me).val(templateId);
                    }
                });
            });
        }
        

        $('#page_type_id').focus(function() {
            $(this).data('prevVal', $(this).val());
        }).change(function() {
            var pageTypeId = $(this).val();
            $(this).val($(this).data('prevVal'));

            jConfirm('Вы собираетесь изменить тип страницы. Изменятся поля формы, все несохраненные данные будут потеряны. Продолжить?',
                'Подтверждение смены типа страницы', 
                function(r) {
                    if (r) {
                        window.location.href = options['changePageTypeUrl'].replace('--PAGE_TYPE--', pageTypeId);
//                        var url = window.location.href.split('?')[0];
//                        window.location.href = url + '?page_type_id=' + pageTypeId;
                    }
                }
            );     
        });

        $('#object_type_id').focus(function() {
            $(this).data('prevVal', $(this).val());
        }).change(function() {
            var pageTypeId = $('#page_type_id').val();
            var objectTypeId = $(this).val();
            $(this).val($(this).data('prevVal'));

            jConfirm('Вы собираетесь изменить тип данных. Изменятся поля формы, все несохраненные данные будут потеряны. Продолжить?',
                'Подтверждение смены типа данных', 
                function(r) {
                    if (r) {
                        window.location.href = options['changeObjectTypeUrl'].replace('--PAGE_TYPE--', pageTypeId).replace('--OBJECT_TYPE--', objectTypeId);
         //               var url = window.location.href.split('?')[0];
        //                window.location.href = url + '?object_type_id=' + objectTypeId + '&page_type_id=' + $('#page_type_id').val();;
                    }
                }
            );     
        });
    },
    
    initMarkersSorting: function() {
        var options = this.options;
        
        $('.markers__item-body').sortable({
            handle: '.marker-modul-cross',
//            containment: '.markers',
            placeholder: 'sortable-placeholder', 
            connectWith: '.markers__item-body',
            forcePlaceholderSize: true,

            start: function(e, ui) {
                var item = $(ui.item);

                if (item.parent().children('.marker-modul').size() == 1) {
                    item.closest('.markers__item').children('.markers__item-msg').removeClass('hidden');
                }
            },
            stop: function (e, ui) {
                var item = $(ui.item),
                    contentId = item.attr('data-id'),
                    markerId = item.closest('.markers__item').attr('data-id'),
                    beforeContentId = 0;

                if (0 == markerId) {
                    return false;
                }

                if (item.parent().children('.marker-modul').size() == 1) {
                    item.closest('.markers__item').children('.markers__item-msg').addClass('hidden');
                }

                if (item.prev().size()) {
                    beforeContentId = item.prev().attr('data-id');
                }

                $.ajax({
                    url: options.sortContentUrl,
                    dataType: 'json',
                    type: 'post',
                    data: {
                        contentId : contentId,
                        markerId : markerId,
                        beforeContentId : beforeContentId
                    },
                    success: function(data) {
                        if (zen.isDefined(data.result.msg)) {
                            $().toastmessage('showSuccessToast', data.page.msg);
                        }
                        else if (zen.isDefined(data.result.errMsg)) {
                            $().toastmessage('showErrorToast', data.page.errMsg);
                        }
                    }
                });
            },


            update: function(e, ui) {


                return;
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
                    url: initParams['sortGroupUrl'].replace('--GROUP_BEFORE--', data['groupBefore']).replace('--GROUP--', data['group'])
                });
            }
        });
    },

    initContentEdit: function(options) {
        $('#page_content_type_id').focus(function() {
            $(this).data('prevVal', $(this).val());
        }).change(function() {
            var pageContentTypeId = $(this).val();
            $(this).val($(this).data('prevVal'));

            jConfirm('Вы собираетесь изменить модуль содержимого. Изменятся поля формы, все несохраненные данные будут потеряны. Продолжить?',
                'Подтверждение смены модуля содержимого', 
                function(r) {
                    if (r) {                        
                        window.location.href = options['changeContentTypeUrl'].replace('--CONTENT_TYPE--', pageContentTypeId);
  //                      var url = window.location.href.split('?')[0];
  //                      window.location.href = url + '?page_content_type_id=' + pageContentTypeId;
                    }
                }
            );    
        });

        $('#object_type_id').focus(function() {
            $(this).data('prevVal', $(this).val());
        }).change(function() {
            var contentTypeId = $('#page_content_type_id').val();
            var objectTypeId = $(this).val();
            $(this).val($(this).data('prevVal'));

            jConfirm('Вы собираетесь изменить тип данных. Изменятся поля формы, все несохраненные данные будут потеряны. Продолжить?',
                'Подтверждение смены типа данных', 
                function(r) {
                    if (r) {
                        window.location.href = options['changeObjectTypeUrl'].replace('--CONTENT_TYPE--', contentTypeId).replace('--OBJECT_TYPE--', objectTypeId);
   //                     var url = window.location.href.split('?')[0];
     //                   window.location.href = url + '?object_type_id=' + objectTypeId + '&page_content_type_id=' + $('#page_content_type_id').val();
                    }
                }
            );    
        });
    },
    
    hideContent: function(contentId) {
        var me = this,
            options = me.options;
            
        $.ajax({
            url: options.deactivateContentUrl,
            dataType: 'json',
            type: 'post',
            data: {
                id : contentId
            },
            success: function(data) {
                if (zen.isDefined(data.result.msg)) {
                    $().toastmessage('showSuccessToast', data.result.msg);
                }
                else if (zen.isDefined(data.result.errMsg)) {
                    $().toastmessage('showErrorToast', data.result.errMsg);
                }
                
                if (data.result.success) {
                    $.ajax({
                        url: options['updatePageContentUrl'].replace('--PAGE--', options['pageId']).replace('--TEMPLATE--', $('#template').val()),
                        dataType: 'json',
                        success: function(data) {
                            $('#markers-wrap').html(data.result);
                            me.initMarkersSorting();
                        }
                    });
                }                
            }
        });
    },
    
    showContent: function(contentId) {
        var me = this,
            options = me.options;
            
        $.ajax({
            url: options.activateContentUrl,
            dataType: 'json',
            type: 'post',
            data: {
                id : contentId
            },
            success: function(data) {
                if (zen.isDefined(data.result.msg)) {
                    $().toastmessage('showSuccessToast', data.result.msg);
                }
                else if (zen.isDefined(data.result.errMsg)) {
                    $().toastmessage('showErrorToast', data.result.errMsg);
                }
                
                if (data.result.success) {
                    $.ajax({
                        url: options['updatePageContentUrl'].replace('--PAGE--', options['pageId']).replace('--TEMPLATE--', $('#template').val()),
                        dataType: 'json',
                        success: function(data) {
                            $('#markers-wrap').html(data.result);
                            me.initMarkersSorting();
                        }
                    });
                }                
            }
        });
    },
    
    delContent: function(contentId) {
        var me = this,
            options = me.options;
            
        jConfirm('Вы собираетесь удалить содержимое страницы. Продолжить?',
                'Подтверждение удаления содержимого страницы', 
            function(r) {
                if (r) {    
                    $.ajax({
                        url: options.delContentUrl,
                        dataType: 'json',
                        type: 'post',
                        data: {
                            id : contentId
                        },
                        success: function(data) {
                            if (zen.isDefined(data.result.msg)) {
                                $().toastmessage('showSuccessToast', data.result.msg);
                            }
                            else if (zen.isDefined(data.result.errMsg)) {
                                $().toastmessage('showErrorToast', data.result.errMsg);
                            }

                            if (data.result.success) {
                                $.ajax({
                                    url: options['updatePageContentUrl'].replace('--PAGE--', options['pageId']).replace('--TEMPLATE--', $('#template').val()),
                                    dataType: 'json',
                                    success: function(data) {
                                        $('#markers-wrap').html(data.result);
                                        me.initMarkersSorting();
                                    }
                                });
                            }                
                        }
                    });
                }
            }
        );
                
    },
    
    delPage: function(url, id) {
        var me = this,
            options = me.options;
            
        jConfirm('Вы собираетесь удалить страницу. Продолжить?',
                'Подтверждение удаления страницы', 
            function(r) {
                if (r) {    
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'post',
                        data: {
                            id : id
                        },
                        success: function(data) {
                            if (zen.isDefined(data.result.msg)) {
                                $().toastmessage('showSuccessToast', data.result.msg);
                            }
                            else if (zen.isDefined(data.result.errMsg)) {
                                $().toastmessage('showErrorToast', data.result.errMsg);
                            }

                            $('#easyui-tree-grid').treegrid('reload');    
                        }
                    });
                }
            }
        );
    },
    
    delDomain : function(url, id) {
        var me = this,
            options = me.options;
            
        jConfirm('Вы собираетесь удалить домен. Продолжить?',
                'Подтверждение удаления домена', 
            function(r) {
                if (r) {    
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'post',
                        data: {
                            id : id
                        },
                        success: function(data) {
                            if (zen.isDefined(data.page.msg)) {
                                $().toastmessage('showSuccessToast', data.page.msg);
                            }
                            else if (zen.isDefined(data.page.errMsg)) {
                                $().toastmessage('showErrorToast', data.page.errMsg);
                            }

                            $('#easyui-tree-grid').treegrid('reload');    
                        }
                    });
                }
            }
        );
    }
});