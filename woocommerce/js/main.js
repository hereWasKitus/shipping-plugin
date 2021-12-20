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
  $('form.woocommerce-checkout input[type="number"]').on('input', e => {
    if ( e.target.value.match(/^-+/)?.length ) {
      e.target.value = e.target.value.slice(1);
    }
  });

}))(jQuery);