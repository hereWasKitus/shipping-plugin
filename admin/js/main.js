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

}))(jQuery);