(function($) { 
   
	function calendarWidget(el, params) { 
		
		var now   = new Date();
		var thismonth = now.getMonth();
		var thisyear  = now.getYear() + 1900;
        var today = now.getDate();
		var opts = {
			month: thismonth,
			year: thisyear,
            day: today
		};
		
		$.extend(opts, params);
		
		var monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
		var dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
		month = i = parseInt(opts.month);
		year = parseInt(opts.year);
        day = parseInt(opts.day);
		var m = 0;
		var table = '';
				
			table += ('<div class="calendar-nav"><a href="javascript:prevMonth()" id="prev-month" style="margin-left: 15px;"></a>'+monthNames[month]+'<a href="javascript:nextMonth()" id="next-month" style="margin-right: 15px;"></a></div>');
			// uncomment the following lines if you'd like to display calendar month based on 'month' and 'view' paramaters from the URL
			//table += ('<div class="nav-prev">'+ prev_month +'</div>');
			//table += ('<div class="nav-next">'+ next_month +'</div>');
			table += ('<div class="calendar-table"><table style="width: 100%;" class="calendar-month mange " ' +'id="calendar-month'+i+' " cellspacing="0">');
		
			table += '<tr>';
			
			for (d=0; d<7; d++) {
				table += '<th class="weekday">' + dayNames[d] + '</th>';
			}
			
			table += '</tr>';
		
			var days = getDaysInMonth(month,year);
            var firstDayDate=new Date(year,month,1);
            var firstDay=firstDayDate.getDay();
			
			var prev_days = getDaysInMonth(month,year);
            var firstDayDate=new Date(year,month,1);
            var firstDay=firstDayDate.getDay();
			
			var prev_m = month == 0 ? 11 : month-1;
			var prev_y = prev_m == 11 ? year - 1 : year;
			var prev_days = getDaysInMonth(prev_m, prev_y);
			firstDay = (firstDay == 0 && firstDayDate) ? 7 : firstDay;
	
			var i = 0;
            for (j=1;j<43;j++){
			  
              if ((j<firstDay)){
                table += ('<td class="current-month" style=" border: solid #808080 1px;width: 104px; min-height: 120px;vertical-align: top;"><span class="day">'+ (prev_days-firstDay+j+1) +'</span></td>');
			  } else if ((j>=firstDay+getDaysInMonth(month,year))) {
				i = i+1;
                table += ('<td class="current-month" style=" border: solid #808080 1px;width: 104px; min-height: 120px;vertical-align: top;"><span class="day">'+ i +'</span></td>');
              }else{
                table += ('<td class="current-month day'+(j-firstDay+1)+'" style=" border: solid #808080 1px;width: 104px; min-height: 120px;vertical-align: top;"><a style="padding: 1px;text-align: right; vertical-align:top; width: 104px;" title="Добавить событие" href="'+document.BASE_URL+'?m='+(params.month + 1)+'&d='+(j-firstDay+1)+'" id="event_'+(params.month + 1)+'_'+(j-firstDay+1)+'" class="day' + (((params.month == thismonth) && (today == (j-firstDay+1))) ? ' is-today' : '') + (((params.month_constant == month) && (day == (j-firstDay+1))) ? '' : '') + '"><i class="icon-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+(j-firstDay+1)+ '</a><br><div class="a-calendar-manage" id="div_day'+(j-firstDay+1)+'"></div></td>');
              }
              if ((j - 1)%7==6)  table += ('</tr>');
            }

            table += ('</table></div>');

		el.html(table);
	}
	
	function getDaysInMonth(month,year)  {
		var daysInMonth=[31,29,31,30,31,30,31,31,30,31,30,31];
		if ((month==1)&&(year%4==0)&&((year%100!=0)||(year%400==0))){
		  return 29;
		}else{
		  return daysInMonth[month];
		}
	}
	
	
	// jQuery plugin initialisation
	$.fn.calendarWidget = function(params) {    
		calendarWidget(this, params);		
		return this; 
	}; 

})(jQuery);
