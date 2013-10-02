zen.forms = zen.forms || {};

zen.apply(zen.forms, {
    addCollectionItem : function(obj) {
        var $templateSpan = $(obj).prev(),                
            template = $templateSpan.attr('data-template'),
            currentCount = $(obj).parent().find('> fieldset').length;

        template = template.replace(/__index__/g, currentCount);    
        $templateSpan.before(template);

        return false;
    },
    delCollectionItem : function(obj) {
        if ($(obj).parent().find('> fieldset').length > 1) {
            $(obj).parent().find('> fieldset:last').remove();
        }
        return false;
    }
});