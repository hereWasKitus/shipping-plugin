(($) => $(document).ready(async () => {

  // International delivery
  const { InternationalDelivery } = await import('./modules/InternationalDelivery.js');
  InternationalDelivery.init();

  // Layout
  const { LayoutController } = await import('./modules/LayoutController.js');
  LayoutController.init();

  // Blessing
  const { Blessing } = await import('./modules/Blessing.js');
  Blessing.init();

  // Disable negative values for digital inputs
  $(document.body).on('keypress', 'form.woocommerce-checkout input[type="number"]', evt => {
    if (evt.which != 8 && evt.which != 0 && evt.which < 48 || evt.which > 57) {
      evt.preventDefault();
    }
  });

}))(jQuery);