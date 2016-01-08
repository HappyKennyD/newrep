
    $(document).ready(function() {
        var i = 0;
        
        $('.gallery-article').each(function() {
            i++;

            if(i == 3) {
                i = 0;

                $(this).addClass('w-300');
            }
        });
        
        $('#gallery-thumbs a[rel=fancy]').fancybox();
        
        $('.p-thumb a').mouseover(function() {
            if($(this).children('span').css('margin-top') == '0px') {
                $(this).children('span').css('margin-top', Math.round((184 - $(this).children('span').height()) / 2) - 10);
            }
        });
        
        $('.p-thumb').mouseover(function() {
            $(this).children('a').fadeIn(50);
        }).mouseleave(function() {
            $(this).children('a').fadeOut(50);
        });
    });