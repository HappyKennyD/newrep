
    $(document).ready(function() {
        $('#block').append( $('#comments-block') );
console.log('eee');
        var isCall = false;
        //$('textarea').autosize();
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