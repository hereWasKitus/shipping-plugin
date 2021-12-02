(($) => $(document).ready(async () => {

  console.log('Shipping plugin script started');

  /**
   * Datepicker
   */
  const date = new Date();
  const targetDate = new Date(`${date.getMonth() + 1} ${date.getDate()} 2020`);
  const datesContainer = document.querySelector('#sp-public-holidays');

  const dateOptionName = document.querySelector('.sp-dates-input')?.name;
  let selectedDates = [];
  const datesFd = new FormData();
  datesFd.append('action', 'get_option');
  datesFd.append('name', dateOptionName);

  try {
    const selectedDatesResponse = await fetch(wp.ajaxUrl, {
      method: 'POST',
      body: datesFd
    });
    selectedDates = await selectedDatesResponse.json();
  } catch (error) {
    console.log('No date field!');
  }

  $('#sp-multi-datepicker').multiDatesPicker({
    defaultDate: targetDate,
    yearRange: `2020:2020`,
    onUpdateDatepicker() {
      datesContainer.innerHTML = '';

      let dates = $('#sp-multi-datepicker').multiDatesPicker('getDates');

      if (dates.length === 0) return;

      const formatedDates = dates.map(dateString => new Date(dateString));
      const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'Novermber', 'December'];

      formatedDates.forEach((date, index) => {
        const tag = document.createElement('div');
        tag.classList.add('sp-public-holiday');
        tag.innerHTML = `${months[date.getMonth()]} ${date.getDate()} <a href="#" data-date="${dates[index]}" class="sp-public-holiday__remove js-remove-date"><i class="gg-close"></i></a>`;

        datesContainer.append(tag);
      });
    }
  });

  if (selectedDates.length) {
    $('#sp-multi-datepicker').multiDatesPicker('addDates', selectedDates);
  }

  $('.sp-public-holidays-container').on('click', '.js-remove-date', e => {
    e.preventDefault();
    $('#sp-multi-datepicker').multiDatesPicker('removeDates', new Date(e.currentTarget.dataset.date));
  })

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
    from.required = true;

    let to = document.createElement('input');
    to.type = 'time';
    from.required = true;

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
      let inputs = dayEl.querySelectorAll('input[type="time"]:not(.next-day-delivery)');
      schedule[dayName] = {
        slots: [],
        nextDayDelivery: dayEl.querySelector('.next-day-delivery').value
      };

      if (!inputs.length) return;

      for (let index = 0; index < inputs.length; index += 2) {
        // as every slot have 2 time inputs I increase index by 2 to jump to next slot on next iteration

        schedule[dayName].slots = [
          ...schedule[dayName].slots,
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

    if (!locations[0]?.querySelector('input')) return '';

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
   * Another person delivery
   */
  function collectAnotherPersonDeliverySettings() {
    const settingsBlocks = document.querySelectorAll('.sp-field-settings');

    settingsBlocks.forEach(item => {
      const inputs = item.querySelectorAll('input:not([type="hidden"])');
      const resultInput = item.querySelector('input[type="hidden"]');
      const resObj = {
        label: inputs[0].value,
        placeholder: inputs[1].value,
        required: inputs[2].checked
      }
      resultInput.value = JSON.stringify(resObj);
    })
  }

  /**
   * Web components
   */
  const { BlessingList } = await import('./web-components/BlessingList.js');
  customElements.define('blessing-list', BlessingList);

  /**
   * Blessing
   */
  const blessingContainer = document.querySelector('.blessings-container');
  const addBlessingButton = document.getElementById('js-add-blessing');

  if (addBlessingButton) {
    addBlessingButton.addEventListener('click', e => {
      e.preventDefault();
      const blessing = document.createElement('blessing-list');
      blessingContainer.append(blessing);
    });
  }

  /**
   * Form submit
   */
  $('.js-options-form').on('submit', e => {
    e.preventDefault();

    // Schedule
    const scheduleInput = document.querySelector('.sp-schedule-input');

    if (scheduleInput) {
      let scheduleValues = collectScheduleValues();
      scheduleInput.value = JSON.stringify(scheduleValues);
      console.log(JSON.stringify(scheduleValues));
      console.log(scheduleInput, scheduleInput.value);
    }

    // Locations
    const locationInput = document.querySelector('.sp-locations-input');

    if (locationInput) {
      let locationValues = collectLocationValues();
      locationInput.value = JSON.stringify(locationValues);
    }

    // Datepicker
    const datesInput = document.querySelector('.sp-dates-input');

    if (datesInput) {
      const dates = $('#sp-multi-datepicker').multiDatesPicker('getDates');
      datesInput.value = JSON.stringify(dates);
    }

    // Another person
    const anotherPersonDelivery = document.querySelector('.sp-field-settings');

    if (anotherPersonDelivery) {
      collectAnotherPersonDeliverySettings();
    }

    const blessingNodes = document.querySelectorAll('blessing-list');

    if (blessingNodes.length) {
      const data = [...blessingNodes].map(node => node.getBlessings());
      console.log(data);
      document.querySelector('[name="another_person_blessing"]').value = JSON.stringify(data);
    }

    e.currentTarget.submit();
  });

}))(jQuery);