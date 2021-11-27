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

}))(jQuery);