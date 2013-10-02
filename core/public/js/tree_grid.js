zen.treeGrid = zen.treeGrid || {};

(function($) {
    zen.apply(zen.treeGrid, {
        renderIcons: function(items) {
            var baseUrl = zen.baseUrl;

            $.each(items, function(i, v) { 
                if (zen.isDefined(v['icons'])) {
                    var str = '';
                    str += '<div class="icons">';
                    $.each(v['icons'], function(i2, v2) {
                        if (i2 == 'editLink') {
                            str += '<a href="' + v2 + '"><img class="icons__item icons__item-edit" src="' + baseUrl + '/img/core/pixel.gif" alt="Редактировать" title="Редактировать" /></a>';
                        }
                        else if (i2 == 'addLink') {
                            str += '<a href="' + v2 + '"><img class="icons__item icons__item-add" src="' + baseUrl + '/img/core/pixel.gif" alt="Добавить" title="Добавить" /></a>';
                        }
                        else if (i2 == 'delLink') {
                            str += '<img onclick="' + v2 + '" class="icons__item icons__item-del" src="' + baseUrl + '/img/core/pixel.gif" alt="Удалить" title="Удалить" />';
                        }
                        else if (i2 == 'showLink') {
                            str += '<a href="' + v2 + '"><img class="icons__item icons__item-show" src="' + baseUrl + '/img/core/pixel.gif" alt="Показать" title="Показать" /></a>';
                        }
                    });
                    str += '</div>';
                    items[i]['icons'] = str;

                     if (zen.isDefined(v['children'])) {
                        $.each(v['children'], function(i2, v2) { 
                            if (zen.isDefined(v2['icons'])) {
                                var str = '';
                                str += '<div class="icons">';
                                $.each(v2['icons'], function(i3, v3) {
                                    if (i3 == 'editLink') {
                                        str += '<a href="' + v3 + '"><img class="icons__item icons__item-edit" src="' + baseUrl + '/img/core/pixel.gif" alt="Редактировать" title="Редактировать" /></a>';
                                    }
                                    else if (i3 == 'addLink') {
                                        str += '<a href="' + v3 + '"><img class="icons__item icons__item-add" src="' + baseUrl + '/img/core/pixel.gif" alt="Добавить" title="Добавить" /></a>';
                                    }
                                    else if (i3 == 'delLink') {
                                        str += '<img onclick="' + v3 + '" class="icons__item icons__item-del" src="' + baseUrl + '/img/core/pixel.gif" alt="Удалить" title="Удалить" />';
                                    }
                                    else if (i3 == 'showLink') {
                                        str += '<a href="' + v3 + '"><img class="icons__item icons__item-show" src="' + baseUrl + '/img/core/pixel.gif" alt="Показать" title="Показать" /></a>';
                                    }
                                });
                                str += '</div>';
                                v2['icons'] = str;
                            }
                        });
                    }                    
                }
            });

            return items;
        }
    });
})(jQuery);
