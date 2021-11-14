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
   * CSV
   */
  $('.js-file-upload').on('change', async ({ target }) => {
    const fd = new FormData();
    fd.append('file', target.files[0]);
    fd.append('action', 'sp_get_csv_content');

    const resp = await fetch(wp.ajaxUrl, {
      method: 'POST',
      body: fd
    });
    const data = await resp.json();

    const countriesContainer = document.querySelector('.sp-countries-list ul');
    const fragment = document.createDocumentFragment();

    countriesContainer.innerHTML = '';

    data.forEach(([sku, name, price]) => {
      const el = createLocationElement(sku, name, price);
      fragment.append(el);
    });

    countriesContainer.append(fragment);
  });

  $('.js-add-location').on('click', e => {
    e.preventDefault();
    const countriesContainer = document.querySelector('.sp-countries-list ul');

    if (!countriesContainer.querySelector('input')) {
      countriesContainer.innerHTML = '';
    }

    const el = createLocationElement();
    countriesContainer.append(el);
  })

  $('.sp-countries-container').on('click', '.js-remove-location', e => {
    e.preventDefault();
    e.currentTarget.parentElement.remove();
  })

  function createLocationElement(sku = '', name = '', price = '') {
    const li = document.createElement('li');
    const skuInput = document.createElement('input');
    skuInput.required = true;
    skuInput.placeholder = 'SKU';
    skuInput.type = 'text';
    skuInput.value = sku;
    skuInput.name = 'sku';

    const nameInput = document.createElement('input');
    nameInput.required = true;
    nameInput.placeholder = 'Name';
    nameInput.type = 'text';
    nameInput.value = name;
    nameInput.name = 'name';

    const priceInput = document.createElement('input');
    priceInput.required = true;
    priceInput.placeholder = 'Price';
    priceInput.type = 'number';
    priceInput.value = +price;
    priceInput.name = 'price';

    const deleteEl = document.createElement('a');
    deleteEl.classList.add('js-remove-location');

    const deleteIcon = document.createElement('i');
    deleteIcon.classList.add('gg-trash');

    deleteEl.append(deleteIcon);
    li.append(skuInput, nameInput, priceInput, deleteEl);

    return li;
  }

  function collectLocationValues() {
    const locations = document.querySelectorAll('.sp-countries-list li');
    const locationValue = {};

    if (!locations) return '';

    locations.forEach(li => {
      const skuVal = li.querySelector('[name="sku"]').value;

      locationValue[skuVal] = {
        name: li.querySelector('[name="name"]').value,
        price: li.querySelector('[name="price"]').value
      };
    });

    return locationValue;
  }

  /**
   * Form submit
   */
  $('.js-options-form').on('submit', e => {
    e.preventDefault();

    // Schedule
    let scheduleValues = collectScheduleValues();
    document.querySelector('.sp-schedule-input').value = JSON.stringify(scheduleValues);

    // Locations
    let locationValues = collectLocationValues();
    document.querySelector('.sp-locations-input').value = JSON.stringify(locationValues);

    e.currentTarget.submit();

  });

}))(jQuery);