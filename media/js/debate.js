
    $(document).ready(function() {
        $('textarea').autosize();
        var isCall = false;
        $('#debate-page-announce div').css('height', $('#debate-page-announce').height() - 19);
        
        $('.vote-bad a, .vote-good a').click(function() {
            var vote_value = $(this).parent().children('span').html() * 1;
            
            $(this).parent().children('span').html(vote_value + 1);
        }).mousedown(function() {
            $(this).addClass('vote-active');
        }).mouseup(function() {
            $(this).removeClass('vote-active');
        });
        
        $('.add-comment-block textarea').focus(function() {
            if($(this).val() == $(this).attr('alt')) {
                $(this).val('');
            }
        }).blur(function() {
            if($(this).val() == '') {
                $(this).val($(this).attr('alt'));
            }
        }).keydown(function(e) {
            if((e.keyCode == 13) && (!isCall)) {
                $(this).parent().submit();
                
                isCall = true;
            }
        });
    });