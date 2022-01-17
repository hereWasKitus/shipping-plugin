jQuery(document).ready( async () => {
  const SP_EVENTS = {
    dateChange: 'date_change',
    layoutChange: 'sp_layout_change'
  };
  let SELECTED_COUNTRY = 'israel';
  let CURRENT_LAYOUT = 'delivery'; // delivery | local_pickup

  const changeLayoutHTML = async (templateName, country = '') => {
    const body = new FormData();
    body.append('action', 'sp_layout_change');
    body.append('template', templateName);
    body.append('country', country);

    const resp = await fetch(sp_data.ajaxUrl, {
      method: 'POST',
      body
    });
    const data = await resp.json();

    CURRENT_LAYOUT = templateName;

    jQuery('.js-delivery-fields-container').html(data.data);

    jQuery(document.body).trigger(SP_EVENTS.layoutChange, {
      layoutName: templateName
    });
  }

  /**
   * Datepicker
   */
  ($ => {

    const datepickerSelector = '.sp-wc-calendar .input-text';
    const holidays = sp_data.holidays;
    const deliveryTime = deepJSONParse(sp_data.deliveryTime);

    /**
     * Setup Hebrew language for datepicker
     */
    function setupDefaults () {
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

    /**
     * Setup datepicker
     * @param {String} selector DOM node selector
     * @param {Array<String>} holidays Date strings of holidays
     * @param {Array} deliveryTime Delivery time for every week day
     * @param {String} layoutName Current checkout fields layout
     */
    function setupDatepicker (selector, holidays, deliveryTime, layoutName) {
      // assume that delivery is available starting the next day
      let minDate = 1;
      let days = sp_data.weekDays;
      let curDate = new Date();
      let curDayName = days[curDate.getDay()];
      let timeSlots = deliveryTime[curDayName].slots;

      if (
        (layoutName === 'local_pickup') ||
        (layoutName === 'delivery' && SELECTED_COUNTRY.toLowerCase() === 'israel')
      ) {
        minDate = 0;
      }

      // if (layoutName === 'delivery' && SELECTED_COUNTRY.toLowerCase() === 'israel') {
      //   timeSlots.forEach(timeSlot => {
      //     let from = transformTime(timeSlot[0]);
      //     let to = transformTime(timeSlot[1]);


      //   });
      // }

      if ( deliveryTime[curDayName].nextDayDelivery ) {
        let nextDayDelivery = new Date();
        nextDayDelivery.setHours(deliveryTime[curDayName].nextDayDelivery.split(':')[0])
        nextDayDelivery.setMinutes(deliveryTime[curDayName].nextDayDelivery.split(':')[1])
        minDate = curDate.getTime() > nextDayDelivery.getTime() ? 1 : 0;
      }

      if ( layoutName === 'local_pickup' && timeSlots.length ) {
        let maxTime = timeSlots[timeSlots.length - 1][1];
        let tempDate = new Date();
        tempDate.setHours(maxTime.split(':')[0]);
        tempDate.setMinutes(maxTime.split(':')[1]);

        if ( curDate.getTime() > tempDate.getTime() ) {
          minDate = 1;
        }
      }

      $(selector).datepicker({
        dateFormat: 'dd/mm/yy',
        minDate,
        beforeShowDay ( date ) {
          let tooltipText = sp_data.tooltipText;
          let string = jQuery.datepicker.formatDate('mm/dd/yy', date);
          let tooltip = holidays.indexOf(string) > -1 || date.getTime() < curDate.getTime()
            ? tooltipText
            : '';

          let loopDayName = days[date.getDay()];
          let filledDays = [];

          for (const key in deliveryTime) {
            if ( deliveryTime[key].slots.length ) filledDays.push(key);
          }

          let toShowDay =
            (holidays.indexOf(string) === -1) &&
            (filledDays.indexOf(loopDayName) >= 0);

          return [toShowDay, "", tooltip];
        }
      })
    }

    function handleLayoutChange (e, {layoutName}) {
      if ( layoutName === 'local_pickup' ) {
        setupDatepicker(
          datepickerSelector,
          holidays['pickup'],
          deliveryTime['pickup'],
          layoutName
        );
      } else if ( layoutName === 'delivery' && SELECTED_COUNTRY.toLowerCase() === 'israel' ) {
        setupDatepicker(
          datepickerSelector,
          holidays['israel'],
          deliveryTime['israel'],
          layoutName
        );
      } else {
        setupDatepicker(
          datepickerSelector,
          holidays['international'],
          deliveryTime['international'],
          layoutName
        );
      }
    }

    setupDefaults();
    setupDatepicker(datepickerSelector, holidays['israel'], deliveryTime['israel'], CURRENT_LAYOUT);
    $(document.body).on('change', datepickerSelector, e => {
      $(document.body).trigger(SP_EVENTS.dateChange, [e.currentTarget.value]);
    });
    $(document.body).on(SP_EVENTS.layoutChange, handleLayoutChange);

  })(jQuery);

  /**
   * Delivery time
   */
  ($ => {
    const timeSelectSelector = '.sp-wc-time select';
    const deliveryTime = deepJSONParse(sp_data.deliveryTime);

    function getOptionsHTML ( dateString ) {
      let dateArray = dateString.split('/');
      const targetDate = new Date(`${dateArray[1]}/${dateArray[0]}/${dateArray[2]}`);
      const currentDate = new Date();
      const targetDayName = sp_data.weekDays[targetDate.getDay()];
      let preparationTime = false;
      let optionsHTML = '<option disabled>Choose time</option>';

      if (SELECTED_COUNTRY.toLowerCase() === 'israel' && CURRENT_LAYOUT !== 'local_pickup') {
        preparationTime = deliveryTime.israel[targetDayName].preparationTime;
      } else if (CURRENT_LAYOUT === 'local_pickup') {
        preparationTime = deliveryTime.pickup[targetDayName].preparationTime;
      }

      if (
        (SELECTED_COUNTRY.toLowerCase() === 'israel' && CURRENT_LAYOUT !== 'local_pickup' && sp_data.contactReceiver.israel ) ||
        (CURRENT_LAYOUT !== 'local_pickup' && SELECTED_COUNTRY.toLowerCase() !== 'israel' && sp_data.contactReceiver.international )
      ) {
        optionsHTML += '<option>Contact receiver</option>'
      }

      let slots = CURRENT_LAYOUT === 'local_pickup'
        ? deliveryTime.pickup[targetDayName].slots
        : SELECTED_COUNTRY.toLowerCase() === 'israel'
          ? deliveryTime.israel[targetDayName].slots
        : deliveryTime.international[targetDayName].slots

      if ( preparationTime && (SELECTED_COUNTRY.toLowerCase() || CURRENT_LAYOUT === 'local_pickup') ) {
        currentDate.setMinutes(+preparationTime + currentDate.getMinutes());
      }

      if ( CURRENT_LAYOUT === 'local_pickup' ) {
        slots.forEach(([dateFrom, dateTo]) => {
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
        slots.forEach(([dateFrom, dateTo]) => {
          let target = transformTime(dateTo);

          if (
            (SELECTED_COUNTRY.toLocaleLowerCase() === 'israel') &&
            (currentDate.getDate() === targetDate.getDate()) &&
            (currentDate.getTime() > target.getTime()) ) {
            return;
          }

          optionsHTML += `<option>${dateFrom} - ${dateTo}</option>`;
        });
      }

      return optionsHTML;
    }

    function handleDateChange (e, dateString) {
      $(timeSelectSelector).html( getOptionsHTML(dateString) );
    }

    $(document.body).on(SP_EVENTS.dateChange, handleDateChange);
  })(jQuery);

  /**
   * Address controller
   */
  ($ => {
    const countrySelector = 'select#billing_country';
    const citySelector = '.sp-wc-city select';

    // $(countrySelector).select2();
    $(citySelector).select2();

    function handleCountryChange (e) {
      SELECTED_COUNTRY = e.currentTarget.value;
      changeLayoutHTML(
        SELECTED_COUNTRY.toLowerCase() === 'israel' ? 'israel_delivery' : 'international_delivery' ,
        SELECTED_COUNTRY
      ).then(() => $(document.body).trigger('update_checkout'));
    }

    function handleCityChange (e) {
      $(document.body).trigger('update_checkout');
    }

    function handleLayoutChange () {
      $(countrySelector).select2();
      $(citySelector).select2();
    }

    $(document).on('change', countrySelector, handleCountryChange);
    $(document).on('change', citySelector, handleCityChange);
    $(document.body).on(SP_EVENTS.layoutChange, handleLayoutChange);
  })(jQuery);

  /**
   * Blessing
   */
  ($ => {
    const blessingCategorySelector = '.js-blessing-category';
    const blessingPopupSelector = '.blessing-popup';
    let _blessings = [];

    /**
     * Create html for blessing popup
     * @param {Array<String>} items
     */
    const createBlessingHTML = ( items ) => {
      let html = '';

      items.forEach( item => {
        html += `
        <li>
          <p>${item}</p>
          <button>בחר ברכה</button>
        </li>
        `
      } );

      return html;
    }

    /**
     * Fills popup blessing list with html of selected category's items
     * @param {String} category category of the items
     */
    const fillPopup = async category => {
      if ( !_blessings.length ) {
        const body = new FormData();
        body.append('action', 'get_option');
        body.append('name', 'another_person_blessing');

        const resp = await fetch(sp_data.ajaxUrl, {
          method: 'POST',
          body
        });

        const data = await resp.json();
        _blessings = data;
      }

      let blessings = _blessings.filter( obj => obj.categoryName === category )[0].items;
      const blessingHTML = createBlessingHTML( blessings );

      $(`${blessingPopupSelector} ul`).html( blessingHTML );
    }

    /**
     * Handle category select change
     */
    const handleChange = async e => {
      const { value } = e.target;
      await fillPopup( value );
      $(blessingPopupSelector).addClass('is-active');
    }

    /**
     * Handle blessing item select
     */
    const handleBlessingSelect = e => {
      e.preventDefault();
      const text = e.currentTarget.parentElement.querySelector('p').textContent;
      $('[name="billing_another_person_blessing"]').text( text );
      $(blessingPopupSelector).removeClass('is-active');
    }

    function init () {
      $(document).on('change', blessingCategorySelector, handleChange);
      $(document).on('click', `${blessingPopupSelector} .blessing-popup-list button`, handleBlessingSelect);
      $(document).on('click', `${blessingPopupSelector} [data-modal-close]`, e => {
        e.preventDefault();
        $(blessingPopupSelector).removeClass('is-active');
      });
      $(document).on('click', blessingPopupSelector, e => {
        if ( e.target.classList.contains('blessing-popup') ) {
          $(blessingPopupSelector).removeClass('is-active');
        }
      });
    }

    init();
  })(jQuery);

  /**
   * Layout controller
   */
  ($ => {
    async function handleButtonClick(e) {
      e.preventDefault();
      let layout = e.currentTarget.dataset.layout;

      if ( CURRENT_LAYOUT === layout ) return;

      $('.js-layout-buttons button.is-active').removeClass('is-active');
      $(e.currentTarget).addClass('is-active');

      let templateName = layout === 'delivery' ? 'israel_delivery' : layout;

      changeLayoutHTML(templateName)
        .then(() => $(document.body).trigger('update_checkout'));
    }

    $('.js-layout-buttons button').on('click', handleButtonClick);


    $(document).on(SP_EVENTS.layoutChange, (e, {layoutName}) => {
      $(document.body).attr('data-layout', layoutName);
    })
  })(jQuery);

  // ==================================================

  /**
   * Deep JSON parse
   * @param {String|Object} json - JSON string or Object that contains JSON strings
   */
  function deepJSONParse ( json ) {
    function isJsonString(str) {
      try {
        JSON.parse(str);
      } catch (e) {
        return false;
      }
      return true;
    }

    const object = typeof json === 'string' ? JSON.parse( json ) : {...json};

    for (const key in object) {
      if ( isJsonString(object[key]) ) {
        object[key] = deepJSONParse(object[key]);
      }
    }

    return object;
  }

  /**
   * Function that transforms hours and minutes to date object
   * @param {String} timeString String in format hh:mm
   * @returns {Date} date with minutes and hours provided by time string
   */
  function transformTime ( timeString ) {
    let hours = +timeString.split(':')[0];
    let minutes = +timeString.split(':')[1];
    let date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    return date;
  }

// end of script
})