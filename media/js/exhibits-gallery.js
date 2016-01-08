
    $(document).ready(function() {
        $('#mycarousel').jcarousel({
            'scroll': 1,
            'wrap': 'circular'
        });
        
        $('#exhibit-gallery a[rel=fancy]').fancybox();
    });