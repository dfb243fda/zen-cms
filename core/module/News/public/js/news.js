zen.news = zen.news || {};

zen.apply(zen.news, {
    delNewsItem: function(url, id) {
        jConfirm('Вы собираетесь удалить пункт новость <strong>после удаления её нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить новость?', 
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
    delNews: function(url, id) {
        jConfirm('Вы собираетесь удалить категорию новостей <strong>после удаления её нельзя будет восстановить</strong>',
            'Вы действительно хотите удалить категорию новостей?', 
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
    initNewsForm : function(data) {
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