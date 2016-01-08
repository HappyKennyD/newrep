
    $(document).ready(function() {
        $('.select-click-area').click(function () {
            $(this).parent().children('.select-element-list').show();
        })

        $('.select-element-block').mouseleave(function () {
            $(this).children('.select-element-list').hide();
        });

        $('.select-element-list li').click(function () {
            $('.select-element-list').hide();
            $(this).parent().parent().parent().parent().children('.select-element-text').html($(this).html());
        });
        
        if($('.selected-lifetime').length) {
            $('#lifetime').val($('.selected-lifetime').attr('alt'));
            $('#end-period .select-element-text').html($('.selected-lifetime').html());
        }
        
        $('#end-period li').click(function () {
            $('#lifetime').val($(this).attr('alt'));
        });
    });