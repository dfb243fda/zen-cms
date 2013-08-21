zen.image_gallery = zen.image_gallery || {};

zen.apply(zen.image_gallery, {
    delImage: function(url, id) {
        jConfirm('Вы собираетесь удалить изображение',
            'Вы действительно хотите удалить изображение?', 
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
    delGallery: function(url, id) {
        jConfirm('Вы собираетесь удалить галерею',
            'Вы действительно хотите удалить галерею?', 
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
    initGalleryForm : function(data) {
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