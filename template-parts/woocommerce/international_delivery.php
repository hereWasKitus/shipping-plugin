<?php
  $checkout = WC() -> checkout;
  $fields = $checkout->get_checkout_fields( 'billing' );

  $included_fields = [
    'billing_country',
    'billing_city',
    'billing_address_1',
    'billing_postcode',
    'billing_delivery_day',
    'billing_delivery_timeset',
  ];

  foreach ( $included_fields as $field_name ) {
    if ( !isset( $fields[$field_name] ) ) continue;
    woocommerce_form_field(
      $field_name,
      $fields[$field_name],
      ($field_name === 'billing_country' && isset($_POST['country']) && $_POST['country']) ? $_POST['country'] : '' );
  }
?>