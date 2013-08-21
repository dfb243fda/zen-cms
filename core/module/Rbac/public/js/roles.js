zen.roles = zen.roles || {};

zen.apply(zen.roles, {
    delRole: function(url, id) {
        jConfirm('Вы собираетесь удалить роль <strong>после удаления её нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить роль?', 
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