
    $(document).ready(function() {
        $('.menu-text').each(function() {
            $(this).css('margin-top', Math.round((79 - $(this).height()) / 2) - 1);
            $(this).css('visibility', 'visible');
        });
        
        $('#language-name').css('margin-top', Math.round((79 - $('#language-name').height()) / 2) - 1);
        $('#language-name').css('visibility', 'visible');
        
        $('.article-block').mouseover(function() {
            $(this).addClass('article-hover');
        }).mouseout(function() {
            $(this).removeClass('article-hover');
        }).mousedown(function() {
            $(this).addClass('article-active');
        }).mouseup(function() {
            $(this).removeClass('article-active');
        });
        
        $('.menu-div').mouseover(function() {
            $(this).addClass('menu-over');
        }).mouseout(function() {
            $(this).removeClass('menu-over');
        });
        
        $('.nav-name a').click(function() {
            $(this).parent().hide();
            $(this).parent().parent().children('.nav-links').show();
        });
        
        $('.nav-title').click(function() {
            $(this).parent().hide();
            $(this).parent().parent().children('.nav-name').show();
        });
    });