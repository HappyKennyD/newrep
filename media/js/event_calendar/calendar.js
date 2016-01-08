    
    var selectedDay, selectedMonth, select_month;
    
    $.checkAnnounceLine = function() {
        $('.event-announce').each(function() {
            $(this).children('div').css('height', $(this).height() - 19);
        });
    }
    
    $.loadCalendar = function() {
        $('#calendar-block').calendarWidget({
            month: selectedMonth,
            day: selectedDay,
            month_constant: select_month
        });
    }
    
    function showEvent(id) {
        $('.event-content').hide();
        $('#event-' + id).show();
        $.checkAnnounceLine();
    }
    
    function prevMonth() {
        selectedMonth--;
        
        if(selectedMonth < 0) {
            selectedMonth = 11;
        }
        
        $.loadCalendar();
        show_event(selectedMonth+1);
    }
    
    function nextMonth() {
        selectedMonth++;
        
        if(selectedMonth > 11) {
            selectedMonth = 0;
        }
        
        $.loadCalendar();
        show_event(selectedMonth+1);
    }
    
    $(document).ready(function() {

        $('.event-content').first().show();


        $.checkAnnounceLine();
        
        $('#event-list a').click(function() {
            $('#event-list a').removeClass('selected-event');
            $(this).addClass('selected-event');
        });
        
        selectedDay = $('#selectedDay').html() * 1;
        selectedMonth = ($('#selectedMonth').html() * 1) - 1;
        select_month = ($('#selectedMonth').html() * 1) - 1;
        
        $.loadCalendar();
    });

