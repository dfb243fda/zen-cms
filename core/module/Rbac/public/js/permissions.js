zen.rbac = zen.rbac || {};

zen.apply(zen.rbac, {
    initPermForm: function() {
        var me = this,
            checkboxes = $('#b-permission_list input[type=checkbox]');
        
        
        checkboxes.change(function() {
            var $this = $(this),
                tmp = $this.attr('name').split('::'),
                data = {
                    'role' : tmp[0],
                    'resource' : tmp[1],
                    'privelege' : tmp[2],
                    'is_allowed' : $this.is(':checked')?1:0
                };
            
            checkboxes.attr('disabled', true);
                
            $.ajax({
                dataType: 'json',
                data: data,
                type: 'post',
                success: function(data) {
                    if (zen.isDefined(data.page.msg)) {
                        $().toastmessage('showSuccessToast', data.page.msg);
                    }
                    else if (zen.isDefined(data.page.errMsg)) {
                        $().toastmessage('showErrorToast', data.page.errMsg);
                    }
                    zen.history.refresh();
                }
            });
        });
    }
});