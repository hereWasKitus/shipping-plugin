export const LayoutController = ($ => {

  function bindEvents() {
    $('.js-layout-buttons button').on('click', handleButtonClick);
    $(document.body).on('sp_layout_change', handleLayoutChange)
  }

  async function handleButtonClick(e) {
    e.preventDefault();

    $('.js-layout-buttons button.is-active').removeClass('is-active');
    $(e.currentTarget).addClass('is-active');

    const templateName = e.currentTarget.dataset.layout;

    const body = new FormData();
    body.append('action', 'sp_layout_change');
    body.append('template', templateName);

    const resp = await fetch(wp.ajaxUrl, {
      method: 'POST',
      body
    });
    const data = await resp.json();

    $(document.body).trigger('sp_layout_change', {
      layout: data.data,
      layoutName: templateName
    });
  }

  function handleLayoutChange(e, data) {
    $('.js-delivery-fields-container').html(data.layout);
    $(document.body).trigger('sp_layout_change_finished', {
      layoutName: data.layoutName
    });
  }

  return {
    init() {
      bindEvents();
    }
  }

})(jQuery)