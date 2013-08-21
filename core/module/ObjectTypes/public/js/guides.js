zen.guides = zen.guides || {};

zen.apply(zen.guides, {
    delGuideItem: function(url, guideItemId) {
        jConfirm('Вы собираетесь удалить термин из справочника',
            'Вы действительно хотите удалить термин из справочника?', 
            function(r) {
                if (r) {
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            id: guideItemId
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
    
});