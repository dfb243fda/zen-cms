zen.composite = zen.composite || {};

zen.apply(zen.composite, {
    add : function(obj) {
        var itemObj = $(obj).parent('.composite-item'),
            cloneObj = itemObj.clone();
        
        cloneObj.find('select').attr('value', '');
        cloneObj.find('input[type="text"]').attr('value', '');
        cloneObj.find('.icons__item-del').removeClass('hide');
        
        $(obj).closest('.composite-items').append(cloneObj);
                
        itemObj.parent().find('.composite-item').eq(0).find('.icons__item-del').removeClass('hide');
    },
    del : function(obj) {
        var itemObj = $(obj).parent('.composite-item'),
            itemsObj = itemObj.parent();        
        itemObj.remove();
        
        if (itemsObj.find('.composite-item').size() == 1) {
            itemsObj.find('.composite-item').eq(0).find('.icons__item-del').addClass('hide');
        } else {
            itemsObj.find('.composite-item').eq(0).find('.icons__item-del').removeClass('hide');
        }
    }
});