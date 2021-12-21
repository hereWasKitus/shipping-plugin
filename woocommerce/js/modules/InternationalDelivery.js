export const InternationalDelivery = (($) => {
  const dateInputSelector = '.sp-wc-calendar .input-text';
  const timeSelectSelector = '.sp-wc-time select';
  const countrySelectSelector = 'select#billing_country';
  const citySelectSelector = '.sp-wc-city select';
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  let internationalDeliveryTime = [];
  let israelDeliveryTime = [];
  let localPickupDeliveryTime = [];

  let lastSelectedCountry = 'israel';
  let currentCountry = 'israel';
  let layoutName = 'international_delivery';

  let internationalDeliveryHolidays = [];
  let israelDeliveryHolidays = [];
  let localPickupDeliveryHolidays = [];
  let tooltipText = '';

  async function init() {
    setDatepickerDefaults();
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

  function setDatepickerDefaults () {
    $.datepicker.regional.he = {
      closeText: "סגור",
      prevText: "&#x3C;הקודם",
      nextText: "הבא&#x3E;",
      currentText: "היום",
      monthNames: [ "ינואר", "פברואר", "מרץ", "אפריל", "מאי", "יוני",
      "יולי", "אוגוסט", "ספטמבר", "אוקטובר", "נובמבר", "דצמבר" ],
      monthNamesShort: [ "ינו", "פבר", "מרץ", "אפר", "מאי", "יוני",
      "יולי", "אוג", "ספט", "אוק", "נוב", "דצמ" ],
      dayNames: [ "ראשון", "שני", "שלישי", "רביעי", "חמישי", "שישי", "שבת" ],
      dayNamesShort: [ "א'", "ב'", "ג'", "ד'", "ה'", "ו'", "שבת" ],
      dayNamesMin: [ "א'", "ב'", "ג'", "ד'", "ה'", "ו'", "שבת" ],
      weekHeader: "Wk",
      dateFormat: "dd/mm/yy",
      firstDay: 0,
      isRTL: true,
      showMonthAfterYear: false,
      yearSuffix: "" };
    $.datepicker.setDefaults( $.datepicker.regional[ "he" ] );
  }

  async function getToolTipText() {
    const body = new FormData();
    body.append('action', 'get_option');
    body.append('name', 'sp_international_tooltip');

    const resp = await fetch(wpdata.ajaxUrl, {
      method: 'POST',
      body
    });

    const tooltip = await resp.text();
    return tooltip;
  }

  function updateLayoutOnSelectValue() {
    if ($(countrySelectSelector).val() === 'Israel') {
      lastSelectedCountry = 'israel';
      switchCheckoutFields('Israel');
    } else {
      $(document.body).trigger('update_checkout');
    };
  }

  async function getDeliveryTime(type = 'international') {
    const fd = new FormData();
    fd.append('action', 'get_option');
    fd.append('name', `sp_${type}_delivery_time`);
    const resp = await fetch(wpdata.ajaxUrl, {
      method: 'POST',
      body: fd
    });
    const data = await resp.json();
    return data;
  }

  function initDatePicker(selector, holidays) {
    let minDate = 1;
    let deliveryTime = israelDeliveryTime;
    let days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    if (layoutName === 'local_pickup') {
      minDate = 0;
      deliveryTime = localPickupDeliveryTime;
    }

    if (layoutName === 'international_delivery') {
      minDate = 1;
      deliveryTime = internationalDeliveryTime;
    }

    if (layoutName === 'international_delivery' && currentCountry.toLowerCase() === 'israel') {
      minDate = 0;
      deliveryTime = israelDeliveryTime;
    }

    let currentDate = new Date();
    let currentDayName = days[currentDate.getDay()];

    let nextDayDeliveryDate = new Date();

    if (deliveryTime[currentDayName].nextDayDelivery) {
      nextDayDeliveryDate.setHours(deliveryTime[currentDayName].nextDayDelivery.split(':')[0])
      nextDayDeliveryDate.setMinutes(deliveryTime[currentDayName].nextDayDelivery.split(':')[1])
    }

    minDate = currentDate.getTime() > nextDayDeliveryDate.getTime() ? 1 : 0;

    if (layoutName === 'international_delivery' && currentCountry.toLowerCase() !== 'israel') {
      minDate = 1;
    }

    let currentDaySlots = deliveryTime[currentDayName].slots;

    // if it's local pickup and it past delivery time - hide current day
    if ( layoutName === 'local_pickup' && currentDaySlots.length ) {
      let maxTime = currentDaySlots[currentDaySlots.length - 1][1];
      let tempDate = new Date();
      tempDate.setHours(maxTime.split(':')[0]);
      tempDate.setMinutes(maxTime.split(':')[1]);

      if ( currentDate.getTime() > tempDate.getTime() ) {
        minDate = 1;
      }
    }

    $(selector).datepicker({
      dateFormat: 'dd/mm/y',
      minDate,
      beforeShowDay(date) {
        let string = jQuery.datepicker.formatDate('mm/dd/yy', date);
        let tooltip = holidays.indexOf(string) > -1 || date.getTime() < currentDate.getTime()
          ? tooltipText
          : '';

        let currentIterationDayName = days[date.getDay()];
        let filledDays = [];

        for (const key in deliveryTime) {
          if ( deliveryTime[key].slots.length ) filledDays.push(key);
        }

        let toShowDay =
          (holidays.indexOf(string) === -1) &&
          (filledDays.indexOf(currentIterationDayName) >= 0);

        return [toShowDay, "", tooltip];
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
    $(citySelectSelector).select2();

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

    let res = await fetch(wpdata.ajaxUrl, {
      method: 'POST',
      body: fd
    });

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
   * @param {String} dateString Date string in format dd/mm/yyyy
   * @returns {NodeList} Options HTML list
   */
  function getTimeOptionsHTML(dateString) {
    let dateArray = dateString.split('/');
    const targetDate = new Date(`${dateArray[1]}/${dateArray[0]}/${dateArray[2]}`);
    const currentDate = new Date();
    const dayName = days[targetDate.getDay()];

    // add condition to use israel delivery time
    let daySlots = lastSelectedCountry.toLowerCase() === 'israel'
      ? israelDeliveryTime[dayName].slots
      : internationalDeliveryTime[dayName].slots;

    daySlots = layoutName === 'local_pickup' ? localPickupDeliveryTime[dayName].slots : daySlots;
    let optionsHTML = '<option disabled>set time</option>';

    if ( layoutName === 'local_pickup' ) {
      daySlots.forEach(([dateFrom, dateTo]) => {
        let hourFrom = +dateFrom.split(':')[0];
        let hourTo = +dateTo.split(':')[0];

        for (let index = hourFrom; index <= hourTo; index++) {

          let target = new Date();
          target.setHours(index);
          target.setMinutes(+dateTo.split(':')[1]);

          if (
            (currentDate.getDate() === targetDate.getDate()) &&
            (currentDate.getTime() > target.getTime()) ) {
            continue;
          }

          let text = `${index}`.length < 2 ? `0${index}` : index;
          optionsHTML += `<option>${text}:00</option>`;
        }
      });
    } else {
      daySlots.forEach(([dateFrom, dateTo]) => {
        let target = new Date();
        target.setHours(+dateTo.split(':')[0]);
        target.setMinutes(+dateTo.split(':')[1]);

        if (
          (currentCountry.toLocaleLowerCase() === 'israel') &&
          (currentDate.getDate() === targetDate.getDate()) &&
          (currentDate.getTime() > target.getTime()) ) {
          return;
        }

        optionsHTML += `<option>${dateFrom} - ${dateTo}</option>`;
      });
    }

    return optionsHTML;
  }

  async function handleCountryChange(country) {
    // it will trigger fee change hook on the backend
    currentCountry = country;
    await switchCheckoutFields(country);
    lastSelectedCountry = country;
    $(document.body).trigger('update_checkout');
    $(countrySelectSelector).select2();
    $(citySelectSelector).select2();
  }

  async function handleCityChange(city) {
    $(document.body).trigger('update_checkout');
  }

  async function switchCheckoutFields(country) {
    const fd = new FormData();

    if (country.toLowerCase() === 'israel') {
      fd.set('action', 'sp_layout_change');
      fd.set('template', 'israel_delivery');

      const resp = await fetch(wpdata.ajaxUrl, {
        method: 'POST',
        body: fd
      });
      const data = await resp.json();

      if (data['data']) {
        document.querySelector('.js-delivery-fields-container').innerHTML = data['data'];
        initDatePicker(dateInputSelector, israelDeliveryHolidays);
        bindEvents();
        $(countrySelectSelector).select2();
        $(citySelectSelector).select2();
      }
    }

    if (lastSelectedCountry.toLowerCase() === 'israel' && country.toLowerCase() !== 'israel') {
      fd.set('action', 'sp_layout_change');
      fd.set('template', 'international_delivery');
      fd.set('country', country);

      const resp = await fetch(wpdata.ajaxUrl, {
        method: 'POST',
        body: fd
      });
      const data = await resp.json();

      if (data['data']) {
        document.querySelector('.js-delivery-fields-container').innerHTML = data['data'];
        initDatePicker(dateInputSelector, internationalDeliveryHolidays);
        bindEvents();
        $(countrySelectSelector).select2();
        $(citySelectSelector).select2();
      }
    }
  }

  return {
    init
  }

})(jQuery);