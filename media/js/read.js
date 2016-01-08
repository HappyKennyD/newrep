    
    function showParagraph(p_id) {
        if($('#paragraph-' + p_id).css('display') == 'none') {
            for(var i = 1; i <= p_id; i++) {
                $('#paragraph-' + i).show();
            }
        }
        
         $('html, body').animate({
            scrollTop: $('#paragraph-' + p_id).offset().top
        }, 500);
        
        var count = $('.book-text').length;
        
        if(count == p_id) {
            $('#read-more-lnk').hide();
        }
    }
    
    function readMore() {
        var count = $('.book-text').length;
        var visible_count = 0;
        var log = false;
        
        $('.book-text').each(function() {
            if(($(this).css('display') == 'none') && (!log)) {
                $(this).slideDown(500);
                
                log = true;
                
                visible_count++;
            }
            else if($(this).css('display') != 'none') {
                visible_count++;
            }
        });
        
        if(visible_count == count) {
            $('#read-more-lnk').hide();
        }
    }
    
    $(document).ready(function() {
        if($('#read-more-lnk').length) {
            $('#read-more-lnk').css('margin-left', Math.round(($('#read-more-lnk').parent().width() / 2) - ($('#read-more-lnk').width() / 2)));
            $('#book-navigation').css('visibility', 'visible');
        }
        
        $('.book-text').first().show();

        //$($('#read-more-lnk').click(function(){}));
    });