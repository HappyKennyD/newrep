
    $(document).ready(function() {
        var isCall = false;
        
        $('textarea').autosize();
        
        $('#expert-page-announce div').css('height', $('#expert-page-announce').height() - 19);
        
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