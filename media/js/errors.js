
    $(document).ready(function() {
        $('#searchInput').focus(function() {
            if($(this).val() == $(this).attr('data-alt')) {
                $(this).val('');
            }
        }).blur(function() {
            if($(this).val() == '') {
                $(this).val($(this).attr('data-alt'));
            }
        });
    });