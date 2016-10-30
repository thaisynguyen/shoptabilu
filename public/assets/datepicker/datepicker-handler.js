
function commonLoader() {
  if ($('.datetimePicker').length > 0) {
    $('.datetimePicker').datepicker({
      format: 'dd/mm/yyyy'/*,
      todayBtn: true*/
    });
  }

  /*if ($('.datepicker-from').length > 0 && $('.datepicker-to').length > 0) {
    var currentDate = new Date();
    $('.datepicker-from').datepicker({
      format: 'yyyy/mm/dd',
      todayBtn: "linked",
      clearBtn: true,
      autoclose: true,
      todayHighlight: true
    });
    $('.datepicker-from').datepicker('update', currentDate);

    $('.datepicker-to').datepicker({
      format: 'yyyy/mm/dd',
      todayBtn: "linked",
      clearBtn: true,
      autoclose: true,
      todayHighlight: true
    });
    $('.datepicker-to').datepicker('update', currentDate);

    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    var checkin = $('#start-date').datepicker({
      onRender: function(date) {
        return date.valueOf() < now.valueOf() ? 'disabled' : '';
      }
    }).on('changeDate', function(ev) {
      if (ev.date.valueOf() > checkout.date.valueOf()) {
        var newDate = new Date(ev.date)
        newDate.setDate(newDate.getDate() + 1);
        checkout.setValue(newDate);
      }
      checkin.hide();
      $('#end-date')[0].focus();
    }).data('datepicker');

    var checkout = $('#end-date').datepicker({
      onRender: function(date) {
        return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
      }
    }).on('changeDate', function(ev) {
      checkout.hide();
    }).data('datepicker');*/

    /*$('.datepicker-from').each(function() {
      var dateTo = $(this).next('.datepicker-to');

      if (dateTo.length == 0) {
        dateTo = $(this).find('.datepicker-to');
      }

      $(this).datepicker({
        onSelect: function(selectedDate) {
          dateTo.datepicker("option", "minDate", selectedDate);
        }
      });
    });

    $('.datepicker-to').each(function() {
      var dateFrom = $(this).prev('.datepicker-from');

      if (dateFrom.length == 0) {
        dateFrom = $(this).find('.datepicker-from');
      }

      $(this).datepicker({
        onSelect: function(selectedDate) {
          dateFrom.datepicker("option", "maxDate", selectedDate);
        }
      });
    });
  }*/
}
