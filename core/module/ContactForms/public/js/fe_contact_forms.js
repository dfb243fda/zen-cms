zen.fe_contact_forms = zen.fe_contact_forms || {};

zen.apply(zen.fe_contact_forms, {
    init : function(formId, formAction) {
        var $form = $('#' + formId);
        $form.ajaxForm({
            url: formAction,
            success: function(data) {
                if (zen.isDefined(data.result.msg)) {
                    $().toastmessage('showSuccessToast', data.result.msg);
                }
                else if (zen.isDefined(data.result.errMsg)) {
                    $().toastmessage('showErrorToast', data.result.errMsg);
                }
                
                $form.replaceWith(data.result.form_html);
                zen.includeResources(data);
/*                
                if (zen.isDefined(data.result['formMsg'])) {
                    $.each(data.result['formMsg'], function(i, v) {
                        var html = '';
                        html += '<ul>';
                        $.each(v, function(i2, v2) {
                            html += '<li>' + v2 + '</li>';
                        });
                        html += '</ul>';
                        
                        $form.find('[name='+ i + ']').addClass('input-error').after(html);
                    });
                }
 */               
                if (data.result['success']) {
                    zen.fe_contact_forms.getForm(formId, formAction);
                }
                
            }
        });
    },
    
    getForm : function(formId, formAction) {
        var $form = $('#' + formId);
        $.ajax({
            url : formAction,
            dataType : 'json',
            success : function(data) {                
                $form.replaceWith(data.result.form_html);
                zen.includeResources(data);
            }
        });
    }
});