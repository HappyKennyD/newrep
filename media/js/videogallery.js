
    $(document).ready(function() {
        var i = 0;
        
        $('.gallery-article').each(function() {
            i++;

            if(i == 3) {
                i = 0;

                $(this).addClass('w-300');
            }
        });
    });