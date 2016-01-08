    
    function sendFaqMessage() {
        var e = 0;
        
        $('.overlay-success').hide();
        
        if($('#question-text').val().length <30) {
            $('.overlay-error').show();
            e++;
        }
        else {
            $('.overlay-error').hide();
        }
        
        if(e == 0) {
            $.ajax({
                type     : 'POST',
                data     : {
                    question   : $('#question-text').val()
                },
                dataType : 'json',
                async    : true,
                url      : '',
                error    : function() {
                    $('.overlay-success').show();
                    $('#question-text').val('');
                },
                success  : function(success) {
                    $('.overlay-success').show();
                    $('#question-text').val('');
                }
            });
        }
    }
    
    function overlayClose() {
        $('#faq-hideshow').hide();
    }
    
    function showFaqOverlay() {
        $('.overlay-error, .overlay-success').hide();
        $('#faq-hideshow').show();
    }
    
    $(document).ready(function() {
        $('#search').focus(function() {
            if($(this).val() == $(this).attr('alt')) {
                $(this).val('');
            }
        }).blur(function() {
            if($(this).val() == '') {
                $(this).val($(this).attr('alt'));
            }
        });
        
        $('.fade').click(function() {
            $('#faq-hideshow').hide();
        });
        
        $('#question-text').keypress(function() {
            $('.overlay-error').hide();
        });
    });