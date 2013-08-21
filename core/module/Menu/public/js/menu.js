zen.menu = zen.menu || {};

zen.apply(zen.menu, {
    delMenuItem: function(url, id) {
        jConfirm('Вы собираетесь удалить пункт меню <strong>после удаления его нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить пункт меню?', 
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
    delMenu: function(url, id) {
        jConfirm('Вы собираетесь удалить меню <strong>после удаления его нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить меню?', 
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
    initMenuForm : function(data) {
        $('#object_type_id').change(function() {
            var $this = $(this);
            jConfirm('Вы собираетесь изменить тип данных, поля формы изменятся',
                'Вы действительно хотите изменить тип данных?', 
                function(r) {
                    if (r) {
                        window.location.href = data['changeObjectTypeUrlTemplate'].replace('--OBJECT_TYPE--', $this.val());
                    }
                }
            );
        });
        
    }
});