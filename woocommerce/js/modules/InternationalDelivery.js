export const InternationalDelivery = (($) => {
  const dateInputSelector = '.sp-wc-calendar .input-text';
  const timeSelectSelector = '.sp-wc-time select';
  const countrySelectSelector = 'select#billing_country';
  const citySelectSelector = '.sp-wc-city select';
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  let internationalDeliveryTime = [];
  let israelDeliveryTime = [];
  let localPickupDeliveryTime = [];

  let lastSelectedCountry = '';
  let currentCountry = '';
  let layoutName = 'international_delivery';

  let internationalDeliveryHolidays = [];
  let israelDeliveryHolidays = [];
  let localPickupDeliveryHolidays = [];
  let tooltipText = '';

  async function init() {
    tooltipText = await getToolTipText();
    internationalDeliveryHolidays = await getPublicHolidays('international');
    israelDeliveryHolidays = await getPublicHolidays('israel');
    localPickupDeliveryHolidays = await getPublicHolidays('pickup');

    internationalDeliveryTime = await getDeliveryTime('international');
    israelDeliveryTime = await getDeliveryTime('israel');
    localPickupDeliveryTime = await getDeliveryTime('pickup');

    initDatePicker(dateInputSelector, internationalDeliveryHolidays);

    bindEvents();
    $(document.body).on('sp_layout_change_finished', handleLayoutChange);
    $(document).on('change', countrySelectSelector, e => handleCountryChange(e.currentTarget.value));

    updateLayoutOnSelectValue();
  }

  async function getToolTipText() {
    const body = new FormData();
    body.append('action', 'get_option');
    body.append('name', 'sp_international_tooltip');

    const resp = await fetch(wp.ajaxUrl, {
      method: 'POST',
      body
    });

    const tooltip = await resp.text();
    return tooltip;
  }

  function updateLayoutOnSelectValue() {
    if ($(countrySelectSelector).val() === 'Israel') {
      switchCheckoutFields('Israel');
    } else {
      $(document.body).trigger('update_checkout');
    };
  }

  /**
   *
   * @param {String} type international | israel | pickup
   * @returns
   */
  async function getDeliveryTime(type = 'international') {
    const fd = new FormData();
    fd.append('action', 'get_option');
    fd.append('name', `sp_${type}_delivery_time`);
    const resp = await fetch(wp.ajaxUrl, {
      method: 'POST',
      body: fd
    });
    const data = await resp.json();
    return data;
  }

  function initDatePicker(selector, holidays) {
    let minDate = 1;
    let deliveryTime = internationalDeliveryTime;

    if (layoutName === 'local_pickup') {
      minDate = 0;
      deliveryTime = localPickupDeliveryTime;
    }

    if (layoutName === 'international_delivery') {
      minDate = 1;
    }

    if (layoutName === 'international_delivery' && currentCountry.toLowerCase() === 'israel') {
      minDate = 0;
      deliveryTime = israelDeliveryTime;
    }

    let currentDate = new Date();
    let currentDayName = days[currentDate.getDay()];
    let nextDayDeliveryHour = deliveryTime[currentDayName].nextDayDelivery
      ? deliveryTime[currentDayName].nextDayDelivery.split(':')[0]
      : 999;

    minDate = currentDate.getHours() > nextDayDeliveryHour ? 1 : 0;

    if (layoutName === 'international_delivery' && currentCountry !== 'Israel') {
      minDate = 1;
    }

    $(selector).datepicker({
      // minDate: lastSelectedCountry.toLowerCase() !== 'israel' ? 1 : 0,
      minDate,
      beforeShowDay(date) {
        let string = jQuery.datepicker.formatDate('mm/dd/yy', date);
        let tooltip = holidays.indexOf(string) > -1 || date.getTime() < currentDate.getTime()
          ? tooltipText
          : '';

        return [holidays.indexOf(string) === -1, "", tooltip];
      }
    });
  }

  function bindEvents() {
    $(dateInputSelector).on('change', e => renderTimeSelect(e.currentTarget.value));
    $(citySelectSelector).on('change', e => handleCityChange(e.currentTarget.value));
  }

  function handleLayoutChange(e, data) {
    layoutName = data.layoutName;
    initDatePicker(dateInputSelector, localPickupDeliveryHolidays);
    bindEvents();
    $(countrySelectSelector).select2();

    $(document.body).trigger('update_checkout');
    if (layoutName !== 'local_pickup') {
      updateLayoutOnSelectValue();
    }

    if (layoutName === 'local_pickup') {
      document.querySelector('#billing_country_field').style.position = 'absolute';
      document.querySelector('#billing_country_field').style.opacity = '0';
      document.querySelector('#billing_country_field').style.zIndex = '-1';

      let anotherPersonDeliveryCheckbox = document.querySelector('.sp-another-person-delivery input[type="checkbox"]');

      if (anotherPersonDeliveryCheckbox) {
        anotherPersonDeliveryCheckbox.checked = false;
      }

      $('.sp-another-person-delivery').hide();
    } else {
      document.querySelector('#billing_country_field').style.position = 'relative';
      document.querySelector('#billing_country_field').style.opacity = '1';
      document.querySelector('#billing_country_field').style.zIndex = '1';

      $('.sp-another-person-delivery').show();
    }
  }

  /**
   *
   * @param {String} type international | israel | pickup - type of holidays to get
   * @returns
   */
  async function getPublicHolidays(type = 'international') {
    let fd = new FormData();
    fd.append('action', 'get_option');

    fd.append('name', `sp_${type}_public_holidays`);

    let res = await fetch(wp.ajaxUrl, {
      method: 'POST',
      body: fd
    });

    // error if no holiday - need to fix
    let internationalDeliveryHolidays = await res.json();

    return internationalDeliveryHolidays.map(dateString => {
      let dateArray = dateString.split('/');
      dateArray[dateArray.length - 1] = new Date().getFullYear();
      return dateArray.join('/');
    });
  }

  function renderTimeSelect(dateString) {
    $(timeSelectSelector).html(getTimeOptionsHTML(dateString));
  }

  /**
   *
   * @param {String} dateString Date string in format mm/dd/yy
   * @returns {NodeList} Options HTML list
   */
  function getTimeOptionsHTML(dateString) {
    const dayName = days[new Date(dateString).getDay()];
    // add condition to use israel delivery time
    let daySlots = lastSelectedCountry.toLowerCase() === 'israel'
      ? israelDeliveryTime[dayName].slots
      : internationalDeliveryTime[dayName].slots;

    daySlots = layoutName === 'local_pickup' ? localPickupDeliveryTime[dayName].slots : daySlots;

    if (daySlots) {
      daySlots = daySlots.map(([from, to]) => {
        let dateFrom = new Date();
        dateFrom.setHours(from.split(':')[0]);
        dateFrom.setMinutes(from.split(':')[1]);
        dateFrom.setSeconds(0);

        let dateTo = new Date();
        dateTo.setHours(to.split(':')[0]);
        dateTo.setMinutes(to.split(':')[1]);
        dateFrom.setSeconds(0);

        return [dateFrom, dateTo];
      })
    }

    let optionsHTML = '<option disabled>set time</option>';

    daySlots.forEach(([dateFrom, dateTo], index) => {
      const hoursFrom = dateFrom.getHours();
      const hoursTo = dateTo.getHours() === 0 ? 24 : dateTo.getHours();

      optionsHTML += `<optgroup label="Slot ${index + 1}">`;

      for (let index = hoursFrom; index <= hoursTo; index++) {
        optionsHTML += `<option value="${index}:00">${index}:00</option>`;
      }

      optionsHTML += '</optgroup>'
    });

    return optionsHTML;
  }

  async function handleCountryChange(country) {
    // it will trigger fee change hook on the backend
    currentCountry = country;
    await switchCheckoutFields(country);
    lastSelectedCountry = country;
    $(document.body).trigger('update_checkout');
    $(countrySelectSelector).select2();
  }

  async function handleCityChange(city) {
    $(document.body).trigger('update_checkout');
  }

  async function switchCheckoutFields(country) {
    const fd = new FormData();

    if (country.toLowerCase() === 'israel') {
      fd.set('action', 'sp_layout_change');
      fd.set('template', 'israel_delivery');

      const resp = await fetch(wp.ajaxUrl, {
        method: 'POST',
        body: fd
      });
      const data = await resp.json();

      if (data['data']) {
        document.querySelector('.js-delivery-fields-container').innerHTML = data['data'];
        initDatePicker(dateInputSelector, israelDeliveryHolidays);
        bindEvents();
        $(countrySelectSelector).select2();
      }
    }

    if (lastSelectedCountry.toLowerCase() === 'israel' && country.toLowerCase() !== 'israel') {
      fd.set('action', 'sp_layout_change');
      fd.set('template', 'international_delivery');
      fd.set('country', country);

      const resp = await fetch(wp.ajaxUrl, {
        method: 'POST',
        body: fd
      });
      const data = await resp.json();

      if (data['data']) {
        document.querySelector('.js-delivery-fields-container').innerHTML = data['data'];
        initDatePicker(dateInputSelector, internationalDeliveryHolidays);
        bindEvents();
        $(countrySelectSelector).select2();
      }
    }
  }

  return {
    init
  }

})(jQuery);