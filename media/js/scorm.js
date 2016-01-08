
    $(document).ready(function() {
        if($('#scorm-menu').length) {
            var menu = $('#scorm-menu div');
            
            menu.css('left', Math.round((menu.parent().width() - menu.width()) / 2));
            $('#scorm-menu').css('visibility', 'visible');
        }
    });