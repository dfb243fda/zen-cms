zen.multitext = zen.multitext || {};

zen.apply(zen.multitext, {
    add : function(obj) {
        var itemObj = $(obj).parent('.multitext-item'),
            cloneObj = itemObj.clone();
        
        cloneObj.find('input[type="text"]').attr('value', '');
        cloneObj.find('.icons__item-del').removeClass('hide');
        
        $(obj).closest('.multitext-items').append(cloneObj);
                
        itemObj.parent().find('.multitext-item').eq(0).find('.icons__item-del').removeClass('hide');
    },
    del : function(obj) {
        var itemObj = $(obj).parent('.multitext-item'),
            itemsObj = itemObj.parent();        
        itemObj.remove();
        
        if (itemsObj.find('.multitext-item').size() == 1) {
            itemsObj.find('.multitext-item').eq(0).find('.icons__item-del').addClass('hide');
        } else {
            itemsObj.find('.multitext-item').eq(0).find('.icons__item-del').removeClass('hide');
        }
    }
});