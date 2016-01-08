
    $(document).ready(function() {
        $('.jouele-play, .jouele-pause').click(function() {
            $('#audio-left .jouele').removeClass('active-audio-item');
            $(this).parent().parent().parent().parent().addClass('active-audio-item');
        });
    });