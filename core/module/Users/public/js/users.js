zen.users = zen.users || {};

zen.apply(zen.users, {
    delUser: function(url, id) {
        jConfirm('Вы собираетесь удалить пользователя <strong>после удаления его нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить пользователя?', 
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
    }
});