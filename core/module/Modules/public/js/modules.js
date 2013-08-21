zen.modules = zen.modules || {};

(function($) {
    zen.apply(zen.modules, {
        install : function(url, module) {
            $.ajax({
                url : url,
                type : 'post',
                data : {
                    module : module
                },
                dataType : 'json',
                success : function(data) {
                    if (zen.isDefined(data.page.msg)) {
                        $().toastmessage('showSuccessToast', data.page.msg);
                    }
                    else if (zen.isDefined(data.page.errMsg)) {
                        $().toastmessage('showErrorToast', data.page.errMsg);
                    }
                     window.setTimeout(function() {
                        window.location = window.location;
                     }, 1000);
                }
            });
        },
        uninstall : function(url, module) {
            jConfirm('Вы действительно хотите удалить модуль ' + module + '?', '',  function(r) {
                if (r) {
                    $.ajax({
                        url : url,
                        type : 'post',
                        data : {
                            module : module
                        },
                        dataType : 'json',
                        success : function(data) {
                            if (zen.isDefined(data.page.msg)) {
                                $().toastmessage('showSuccessToast', data.page.msg);
                            }
                            else if (zen.isDefined(data.page.errMsg)) {
                                $().toastmessage('showErrorToast', data.page.errMsg);
                            }
                             window.setTimeout(function() {
                                window.location = window.location;
                             }, 1000);
                        }
                    });
                }                
            });            
        },
        activate : function(url, module) {
            $.ajax({
                url : url,
                type : 'post',
                data : {
                    module : module
                },
                dataType : 'json',
                success : function(data) {
                    if (zen.isDefined(data.page.msg)) {
                        $().toastmessage('showSuccessToast', data.page.msg);
                    }
                    else if (zen.isDefined(data.page.errMsg)) {
                        $().toastmessage('showErrorToast', data.page.errMsg);
                    }
                     window.setTimeout(function() {
                        window.location = window.location;
                     }, 1000);
                }
            });
        },
        deactivate : function(url, module) {
            $.ajax({
                url : url,
                type : 'post',
                data : {
                    module : module
                },
                dataType : 'json',
                success : function(data) {
                    if (zen.isDefined(data.page.msg)) {
                        $().toastmessage('showSuccessToast', data.page.msg);
                    }
                    else if (zen.isDefined(data.page.errMsg)) {
                        $().toastmessage('showErrorToast', data.page.errMsg);
                    }
                     window.setTimeout(function() {
                        window.location = window.location;
                     }, 1000);
                }
            });
        }
    });
})(jQuery);