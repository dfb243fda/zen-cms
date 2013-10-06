zen.bootstrapTheme = {};

(function($) {
    zen.apply(zen.bootstrapTheme, {
        params: {},
        init: function(params) {
            var me = this;
            
            me.params = params;
            
            $(document).ajaxStart(function() {
                $('#loading-msg').show();
            });
            $(document).ajaxStop(function() {
                $('#loading-msg').hide();
            });
            zen.init();            
            
            zen.baseUrl = params.baseUrl;
            
            zen.events.on('history.refresh', function() {
                var url = window.location.href;
                
                if (window.location.pathname.indexOf('.') == -1) {
                    url += '.json_html';
                }                
                me.getAjaxContent(url, false);
            });
        }
    });
})(jQuery);