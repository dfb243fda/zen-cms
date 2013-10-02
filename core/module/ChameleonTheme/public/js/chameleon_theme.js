zen.chameleonTheme = {};

(function($) {
    zen.apply(zen.chameleonTheme, {
        params: {},
        init: function(params) {
            this.params = params;
            
            this.logo.init();
            $(document).ajaxStart(function() {
                $('#loading-msg').show();
            });
            $(document).ajaxStop(function() {
                $('#loading-msg').hide();
            });
            zen.currentTheme = this;      
            zen.history.init();
        },
        
        getAjaxContent: function(url, pushState) {
            if (!zen.isDefined(pushState)) {
                pushState = true;
            }
            
            if (pushState) {
                zen.history.add(url);
            }
            $.ajax({
                url: url,
                dataType: 'json',
     //           type: 'post',
     //           cache: false,
                success: function(data) {
                    if (zen.isDefined(data.page.msg)) {
                        $().toastmessage('showSuccessToast', data.page.msg);
                    }
                    else if (zen.isDefined(data.page.errMsg)) {
                        $().toastmessage('showErrorToast', data.page.errMsg);
                    }                    
                    $('#content .content__body').html(data.page.content);
                    zen.includeResources(data);
                }
            });
        },
        
        logo: {
            logo_id: 'logo',
            logo_prior_color_ratio: 4,
            logo_prior_colors: [
            [26, 16, 229],
            [120, 59, 198],
            [45, 8, 173],
            [162, 88, 4],
            [132, 26, 19],
            [58, 133, 202],
            [55, 10, 65],
            [141, 28, 91],
            [98, 128, 17],
            [17, 134, 179],
            [37, 8, 0],
            [139, 73, 6],
            [152, 151, 144],
            [145, 29, 165],
            [6, 67, 77],
            [31, 26, 23],
            [55, 21, 45],
            [98, 128, 17],
            [126, 158, 64]
            ],
            logo_colors: {
            },
            init: function() {
                var me = this;
                me.logo_img = $('#logo');
                me.getLogoColors();
                me.logo_img.mouseover(function() {
                    var logo_colors = me.chooseLogoColor();
                    me.changeLogoColor(logo_colors);
                });
                me.animateLogo();
            },
            animateLogo: function() {

                var interval1 = 15000;
                var interval2 = 3000;
                var interval3 = 30;

                var max_color = 255
                var color_interval = 1;

                var timer1;
                var timer2;
                var timer3;

                var color_enum = ['red', 'green', 'blue'];
                var me = this;

                timer1 = window.setInterval(function() {

                    var flag = 1;

                    timer2 = window.setInterval(function() {

                        if (flag == 1) {

                            var vector = {
                                red: zen.rand(0, 1),
                                green: zen.rand(0, 1),
                                blue: zen.rand(0, 1)
                            };

                            timer3 = window.setInterval(function() {

                                var colors = {};

                                $.each(color_enum, function(index, value) {
                                    if (vector[value] == 1) {
                                        if (me.logo_colors[value] > max_color - color_interval) {
                                            vector[value] = 0;
                                        }
                                    }
                                    else {
                                        if (me.logo_colors[value] <= color_interval) {
                                            vector[value] = 1;
                                        }
                                    }
                                    if (vector[value] == 1) {
                                        colors[value] = me.logo_colors[value] + color_interval;
                                    }
                                    else {
                                        colors[value] = me.logo_colors[value] - color_interval;
                                    }
                                });

                                me.changeLogoColor(colors);
                            }, interval3);

                            flag = 0;
                        }
                        else {
                            clearInterval(timer3);
                            clearInterval(timer2);
                        }

                    }, interval2);

                }, interval1);


            },
            getLogoColors: function() {
                var me = this;
                var css_color = me.logo_img.css('background-color');
                var tmp = css_color.substring(css_color.indexOf('(')+1, css_color.length - 1);
                var parts = tmp.split(',');
                
                me.logo_colors.red = parseInt(zen.trim(parts[0]));
                me.logo_colors.green = parseInt(zen.trim(parts[1]));
                me.logo_colors.blue = parseInt(zen.trim(parts[2]));
            },
            chooseLogoColor: function() {
                var logo_colors = {},
                me = this;

                if (zen.rand(1, me.logo_prior_color_ratio) == 1) {
                    var colors = me.logo_prior_colors[zen.rand(0, me.logo_prior_colors.length - 1)];
                    logo_colors.red = colors[0];
                    logo_colors.green = colors[1];
                    logo_colors.blue = colors[2];
                }
                else {
                    logo_colors.red = zen.rand(0, 255);
                    logo_colors.green = zen.rand(0, 255);
                    logo_colors.blue = zen.rand(0, 255);
                }

                return logo_colors;
            },
            changeLogoColor: function(logo_colors) {
                var me = this;
                
                me.logo_colors.red = logo_colors.red;
                me.logo_colors.green = logo_colors.green;
                me.logo_colors.blue = logo_colors.blue;
                me.logo_img.attr('style', 'background-color: rgb(' + logo_colors.red + ', ' + logo_colors.green + ', ' + logo_colors.blue + ')');
            }
        },
        
        showHideMainMenu : function(obj) {
            var obj = $(obj);
            var main_menu = $('#main_menu');
            if (main_menu.is(':hidden')) {			
//				$('#content').css('margin-left', '290px');
                main_menu.show('slide', {direction: 'left'}, 500, function() {
                    obj.attr('class', 'hide_frame_arrow link');		
                    obj.attr('alt', 'Скрыть меню');
                    obj.attr('title', 'Скрыть меню');
                    obj.tooltip({
                        track : true,
                        showURL: false
                    });
                });
            }			
            else {				
                main_menu.hide('slide', {direction: 'left'}, 500, function() {
//					$('#content').css('margin-left', '35px');
                    obj.attr('class', 'show_frame_arrow link');
                    obj.attr('alt', 'Показать меню');
                    obj.attr('title', 'Показать меню');
                    obj.tooltip({
                        track : true,
                        showURL: false
                    });
                });				
            }
        }    
        
    });
})(jQuery);
