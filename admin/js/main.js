(($) => $(document).ready(() => {

  console.log('Shipping plugin script started');

  /**
   * Datepicker
   */
  $('#sp-multi-datepicker').multiDatesPicker({
    dateFormat: "m d y"
  });

  $('#sp-public-holidays').on('click', (e) => {
    let dates = $('#sp-multi-datepicker').multiDatesPicker('getDates');

    if (dates.length === 0) return;

    e.currentTarget.innerHTML = '';
    const formatedDates = dates.map(dateString => new Date(dateString));
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'Novermber', 'December'];

    formatedDates.forEach(date => {
      const tag = document.createElement('div');
      tag.classList.add('sp-public-holiday');
      tag.innerHTML = `${months[date.getMonth()]} ${date.getDate()}`;

      e.currentTarget.append(tag);
    });
  });

  /**
   * Schedule block
   */
  $('.js-add-schedule').on('click', e => {
    e.preventDefault();
    const scheduleItem = createScheduleItem();
    e.currentTarget.closest('.sp-schedule-day').querySelector('.sp-schedule-day__slots').append(scheduleItem);
  });

  $('.sp-schedule').on('click', '.js-remove-slot', e => {
    e.preventDefault();
    e.currentTarget.closest('.sp-schedule-day__slot').remove();
  })

  function createScheduleItem() {
    let li = document.createElement('li');
    li.classList.add('sp-schedule-day__slot');

    let from = document.createElement('input');
    from.type = 'time';
    from.name = ''

    let to = document.createElement('input');
    to.type = 'time';

    let removeEl = document.createElement('a');
    removeEl.classList.add('js-remove-slot');

    let removeIcon = document.createElement('i');
    removeIcon.classList.add('gg-trash');

    removeEl.append(removeIcon);

    li.append(from, to, removeEl);
    return li;
  }

  function collectScheduleValues() {
    const schedule = {};
    const days = document.querySelectorAll('.sp-schedule-day');

    days.forEach(dayEl => {
      let dayName = dayEl.dataset.day;
      let inputs = dayEl.querySelectorAll('input[type="time"]');
      schedule[dayName] = [];

      if (!inputs.length) return;

      for (let index = 0; index < inputs.length; index += 2) {
        // as every slot have 2 time inputs I increase index by 2 to jump to next slot on next iteration
        schedule[dayName] = [
          ...schedule[dayName],
          [inputs[index].value, inputs[index + 1].value]
        ];
      }
    });

    return schedule;
  }

  /**
   * Form submit
   */
  $('.js-options-form').on('submit', e => {
    e.preventDefault();

    let scheduleValues = collectScheduleValues();
    document.querySelector('.sp-schedule-input').value = JSON.stringify(scheduleValues);

    e.currentTarget.submit();

  });

}))(jQuery);