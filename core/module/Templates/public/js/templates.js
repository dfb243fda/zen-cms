zen.templates = zen.templates || {};

zen.apply(zen.templates, {
    delTemplate : function(url, id) {
        jConfirm('Вы действительно хотите удалить шаблон?', '',  function(r) {
            if (r) {
                $.ajax({
                    url : url,
                    type : 'post',
                    data : {
                        id : id
                    },
                    dataType : 'json',
                    success : function(data) {
                        if (zen.isDefined(data.page.msg)) {
                            $().toastmessage('showSuccessToast', data.page.msg);
                        }
                        else if (zen.isDefined(data.page.errMsg)) {
                            $().toastmessage('showErrorToast', data.page.errMsg);
                        }
                        
                        zen.history.refresh();
                    }
                });
            }
        });
    }
});