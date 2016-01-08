    
    var minutes = 30;
    var seconds = 60;
    var test_ended = false;
    
    function testTimer() {
        seconds--;
        
        if(seconds < 0) {
            seconds = 59;
            
            if(minutes > 0) {
                minutes--;
            }
        }
        
        var min, sec;
        
        min = minutes + '';
        sec = seconds + '';
        
        if(sec.length == 1) {
            sec = '0' + sec;
        }
        
        if(min.length == 1) {
            min = '0' + min;
        }
        
        $('#status-timer').html(min + ':' + sec);
        
        if((seconds == 0) && (minutes == 0)) {
            endTest();
        }
        else {
            if(!test_ended) {
                setTimeout('testTimer()', 1000);
            }
        }
    }
    
    function beginTest() {
        $('#test-cover, #begin-test-block').hide();
        $('#ent-questions-list').addClass('no-margin');
        $('#status-panel').show();
        
        $('#status-timer').html(minutes + ':' + seconds);
        
        minutes--;
        
        setTimeout('testTimer()', 1000);
    }
    
    function endTest() {
        if($('.ent-question-block').length != $('.selected-test').length) {
            alert($('#notallquestions').val());
        }
        else {
            test_ended = true;
            
            $('#end-question').hide();
            $('#test-cover').show();
            
            var ent = new Array();
            
            $('.ent-question-title').each(function() {
                var id = $(this).attr('alt') * 1;
                var answer_id = $(this).parent().children('.selected-test').children('div').html() * 1;
                
                ent[id] = answer_id;
            });
            
            /*var s = 'Результаты тестирования:\n\n';
            
            for(var key in ent) {
                s += key + ' => ' + ent[key] + '\n';
            }
            
            alert(s);*/

            var s = '';

            for (var key in ent)
            {
                s += key + '.' + ent[key] + ',';
            }

            var url = $("#ajaxurl").val();
            $.ajax({
                url     : url,
                type    : "POST",
                dataType: "json",
                data    : { 'answers' : s },
                success : function (data)
                {
                    var total = data.total;
                    var right = data.right;

                    $('#status-result').empty();
                    $('#status-result').append('<span>' + right + ' / ' + total +'</span>');

                    for (var q = 0; q < data['points'].length; q++)
                    {


                        if (data['points'][q]['right']==1)
                        {
                            $("#question-"+data['points'][q]['quest']).addClass('answered-good');
                        }
                        else
                        {
                            $("#question-"+data['points'][q]['quest']).addClass('answered-bad');
                        }
                    }
                }
            });

        }
    }
    
    function scrollMenu() {
        var t = $(document).scrollTop() - 20;
        
        $('#status-panel').stop().animate({
            'top': t
        }, 300);
    }
    
    $(document).ready(function() {
        scrollMenu();
        
        $(window).scroll(function() {
            scrollMenu();
        });
        
        $('.ent-answer-variant div, .ent-answer-variant span').click(function() {
            $(this).parent().parent().children('.ent-answer-variant').removeClass('selected-test');
            $(this).parent().addClass('selected-test');
            
            var number = $(this).parent().parent().attr('id');
            number = number.substr(4, number.length - 1);
            
            $('#status-numbers span').each(function() {
                if(number == $(this).html()) {
                    $(this).addClass('active-number');
                }
            });
        });
        
        $('#status-numbers span').click(function() {
            $('html, body').animate({
                scrollTop: $('#ent-' + $(this).html()).offset().top
            }, 500);
        });
    });