
    $(document).ready(function() {
        $('#mapping area').click(function() {
            $('#map-active, .map-label').fadeOut(400);
            $('#closemap').fadeIn(400);
        }).mouseover(function() {
            $('#' + $(this).attr('data-region') + '-hover').show();
        }).mouseout(function() {
            $('#' + $(this).attr('data-region') + '-hover').hide();
        });
        
        $('#closemap').click(function() {
            $('#area, #closemap').fadeOut(400);
            $('#map-active, .map-label').fadeIn(400);
        });
    });