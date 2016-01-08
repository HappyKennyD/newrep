    
    var slider_id = 1;
    
    function showSlide(id) {
        if(slider_id != id) {
            $('#slider-nav a').removeClass('selected-slider');
            $('#slide-nav-' + id).addClass('selected-slider');
            $('#slider-nav-bg div').stop().animate({
                'top': 42
            }, 250, function() {
                var txt = '';
                
                if($('#slider-' + id + ' .slider-text').length) {
                    txt = $('#slider-' + id + ' .slider-text').html();
                }
                
                $('#slider-nav-bg div').html(txt);
                
                $('#slider-nav-bg div').stop().animate({
                    'top': 10
                }, 250);
            });
            
            $('#slider-cover').show();
            $('#slider-' + id).fadeIn(500);
            $('#slider-' + slider_id).fadeOut(700, function() {
                $('#slider-cover').hide();
                slider_id = id;
            });
        }
    }
    
    function showShezhire(id) {
        var lft = (id - 1) * -920;
        var i = 0;
        var h = 0;
        
        $('#shezhire-nav a').removeClass('selected-s-nav');
        $('#s-nav-' + id).addClass('selected-s-nav');
        
        $('.shezhire-slide-block').each(function() {
            i++;
            
            if(i == id) {
                h = $(this).height();
            }
        });
        
        $('#shezhire-slider').stop().animate({
            'height': h
        }, 500);
        
        $('#shezhire-slider-content').stop().animate({
            'left': lft
        }, 500);
    }
    
    $(document).ready(function() {
        $('#search-input').focus(function() {
            if($(this).val() == $(this).attr('data-alt')) {
                $(this).val('');
            }
        }).blur(function() {
            if($(this).val() == '') {
                $(this).val($(this).attr('data-alt'));
            }
        });
        
        if($('#shezhire-slider').length) {
            var i = 0;
            
            $('.shezhire-slide-block').each(function() {
                var t = this;
                var j = 0;
                
                i++;
                
                $(t).children('.shezhire-item').each(function() {
                    j++;
                    
                    $(this).addClass('s-item-' + j);
                });
                
                if(i == 1) {
                    $('#shezhire-slider').css('height', $(t).height());
                }
                
                $('#shezhire-nav div').append('<a id="s-nav-' + i + '"' + ((i == 1) ? ' class="selected-s-nav"' : '') + ' href="javascript:showShezhire(' + i + ')"></a>');
            });
            
            $('#shezhire-slider-content').css('width', i * 920);
            $('#shezhire-slider-content').css('visibility', 'visible');
            
            $('#shezhire-nav div').css('left', Math.round((920 - $('#shezhire-nav div').width()) / 2) - 6);
        }
        
        if($('.slider-image').length) {
            var count = $('.slider-image').length;
            
            $('#slider-1').show();
            
            if($('#slider-1 .slider-text').length) {
                $('#slider-nav-bg div').html($('#slider-1 .slider-text').html());
            }
            
            for(var i = count; i > 0; i--) {
                $('#slider-nav').append('<a href="javascript:showSlide(' + i + ')"' + ((i == 1) ? ' class="selected-slider"' : '') + ' id="slide-nav-' + i + '"></a>');
            }
        }
    });